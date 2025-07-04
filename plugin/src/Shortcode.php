<?php

namespace FrameZ;

use FrameZ\Utils\View;

class Shortcode
{
    public static function render(array $attributes = [], string $content = ''): string
    {
        // Default attributes
        $attributes = shortcode_atts([
            'gallery' => 'demo',
            'perpage' => 20,
            'startpage' => 0,
            'loadmore' => true,
        ], $attributes);

        // Get the plugin instance to access file directory and URL
        $plugin = Plugin::getInstance();
        $dir = $plugin->getGallery($attributes['gallery']);
        if (empty($dir)) {
            return '<p class="framez-error empty">Invalid directory specified.</p>';
        }

        $fileDirectory = $dir['path'];
        $fileDirectoryUrl = $dir['url'];

        // Create paginator instance
        $paginator = new FilePaginator($fileDirectory, $fileDirectoryUrl, (int) $attributes['perpage']);
        $paginationData = $paginator->paginate((int) $attributes['startpage']);

        if (is_admin()) {
            // If in admin area, render the gallery preview
            $output = View::render('preview-header', [
                'gallery' => $attributes['gallery'],
                'images' => $paginationData['images'],
            ]);
        } else {
            $output = "";
        }

        // Render the gallery grid view
        $output .= View::render('gallery', [
            'images' => $paginationData['images'],
            'gallery' => $attributes['gallery'],
            'loadmore' => $attributes['loadmore'],
        ]);

        // Remove images from pagination data to avoid duplication and store the pagination metadata in a script tag
        unset($paginationData['images']); 
        $output .= '<script type="application/json" id="framez-data">'
            . json_encode($paginationData) . '</script>';

        return $output;
    }
}