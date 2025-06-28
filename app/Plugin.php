<?php

namespace GGallery;

use GGallery\Utils\Path;
use GGallery\Utils\Vite;

class Plugin
{
    public array $fileDirectories;

    // Singleton instance handling
    private static $instance = null;
    private function __construct() {}
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->registerHooks();
        }
        return self::$instance;
    }

    // Register wp hooks for the plugin
    public function registerHooks()
    {
        add_action('init', function () {
            $this->fileDirectories = apply_filters('ggallery_file_directories', [
                'demo' => [
                    'path' => Path::abs('resources/images/demo/'),
                    'url' => Path::url('resources/images/demo/'),
                ]
            ]);
        });

        add_action('rest_api_init', function () {
            // /wp-json/ggallery/v1/images?page=X&perPage=X
            register_rest_route('ggallery/v1', '/images', array(
                'methods' => 'GET',
                'callback' => [FileController::class, 'handleRequest'],
            ));
        });

        add_shortcode('ggallery', [Shortcode::class, 'render']);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', function () {
            Vite::enqueueMainModule();
            Vite::enqueueScript('ggallery', Vite::asset('resources/scripts/main.js'));
            Vite::enqueueStyle('ggallery', Vite::asset('resources/styles/main.scss'));
        });
    }

    public function getDirectory(string $key)
    {
        if (!isset($this->fileDirectories[$key])) {
            return null;
        }
        return $this->fileDirectories[$key];
    }
}
