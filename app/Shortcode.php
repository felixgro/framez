<?php

namespace GGallery;

use GGallery\Utils\View;

class Shortcode
{
    public static function render(array $attributes = [], string $content = ''): string
    {
        // Default attributes
        $attributes = shortcode_atts([
            'directory' => 'demo',
            'perpage' => 20,
            'startpage' => 0,
            'loadmore' => true,
        ], $attributes);

        // Get the plugin instance to access file directory and URL
        $plugin = Plugin::getInstance();
        $dir = $plugin->getDirectory($attributes['directory']);
        if (empty($dir)) {
            return '<div class="ggallery-error">Invalid directory specified.</div>';
        }

        $fileDirectory = $dir['path'];
        $fileDirectoryUrl = $dir['url'];

        // Create paginator instance
        $paginator = new FilePaginator($fileDirectory, $fileDirectoryUrl, (int) $attributes['perpage']);
        $paginationData = $paginator->paginate((int) $attributes['startpage']);

        // Render the gallery grid view
        $output = View::render('ggallery', [
            'images' => $paginationData['images'],
            'directory' => $attributes['directory'],
            'loadmore' => $attributes['loadmore'],
        ]);

        // Remove images from pagination data to avoid duplication and store the pagination metadata in a script tag
        unset($paginationData['images']); 
        $output .= '<script type="application/json" id="ggallery-data">'
            . json_encode($paginationData) . '</script>';

        return $output;
    }
}