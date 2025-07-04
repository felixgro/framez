<?php

namespace FrameZ;

use FrameZ\Models\Gallery;
use FrameZ\Utils\Path;
use FrameZ\Utils\Vite;

class Plugin
{
    private array $galleries;

    /**
     * Associative array of models used in the plugin.
     */
    private array $models = [
        'gallery' => \FrameZ\Models\Gallery::class,
        'settings' => \FrameZ\Models\Settings::class,
    ];

    /**
     * Singleton instance handling.
     */
    private static $instance = null;
    private function __construct() {}
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->bootstrap();
        }
        return self::$instance;
    }

    /**
     * Register all hooks and filters for the plugin.
     */
    public function bootstrap()
    {
        // Register all models
        foreach ($this->models as $modelKey => $model) {
            if (!class_exists($model)) {
                throw new \Exception("Model class {$model} does not exist.");
            }
            $instance = new $model();
            if (method_exists($instance, 'register')) $instance->register();
            $this->models[$modelKey] = $instance;
        }

        // Load custom galleries
        add_action('init', function () {
            $this->galleries = apply_filters('framez_galleries', [
                'demo' => [
                    'path' => Path::abs('resources/images/demo/'),
                    'url' => Path::url('resources/images/demo/'),
                ]
            ]);
        });

        // /wp-json/framez/v1/images?page=X&perPage=X
        add_action('rest_api_init', function () {
            register_rest_route('framez/v1', '/images', array(
                'methods' => 'GET',
                'callback' => [FileController::class, 'handleRequest'],
            ));
        });

        // Register the shortcode
        add_shortcode('framez', [Shortcode::class, 'render']);

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', function () {
            global $post;

            if (empty($post) || !has_shortcode($post->post_content, 'framez'))
                return;

            Vite::enqueueMainModule();
            Vite::enqueueScript('framez', Vite::asset('resources/scripts/main.js'));
            Vite::enqueueStyle('framez', Vite::asset('resources/styles/main.scss'));
        });

        // Enqueue backend styles
        add_action('admin_enqueue_scripts', function () {
            global $post;

            if (empty($post) || !is_admin() || $post->post_type !== 'framez-gallery') 
                return;

            Vite::enqueueMainModule();
            Vite::enqueueStyle('framez-backend', Vite::asset('resources/styles/admin.scss'));
            Vite::enqueueStyle('framez-styles', Vite::asset('resources/styles/main.scss'));
            Vite::enqueueScript('framez-script', Vite::asset('resources/scripts/main.js'));
            Vite::enqueueScript('framez-backend', Vite::asset('resources/scripts/admin.js'));
        });
    }


    /**
     * Get the directory path and URL for a given key.
     */
    public function getGallery(string $key)
    {

        if (!isset($this->galleries[$key])) {
            // Try to find the gallery by key in the db
            $gallery = Gallery::getByKey($key);
            if (!empty($gallery)) {
                $this->galleries[$key] = [
                    'path' => $gallery['directory_path'],
                    'url' => $gallery['directory_url'],
                ];
            }
        }

        if (!isset($this->galleries[$key])) {
            return null;
        }

        return $this->galleries[$key];
    }
}
