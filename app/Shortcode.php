<?php

namespace GGallery;

class Shortcode
{
    public static function render(array $attributes = [], string $content = ''): string
    {
        // Default attributes
        $attributes = shortcode_atts([
            'directory' => 'demo',
            'perPage' => 20,
            'startPage' => 0,
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
        $paginator = new FilePaginator($fileDirectory, $fileDirectoryUrl, (int) $attributes['perPage']);
        $paginationData = $paginator->paginate((int) $attributes['startPage']);

        $output = '<div id="ggallery" class="ggallery-container" data-directory="' . esc_attr($attributes['directory']) . '">';

        foreach ($paginationData['images'] as $image) {
            $output .= '<a class="ggallery-item" href="' . esc_url($image['url']) . '" data-width="' . esc_attr($image['width']) . '" data-height="' . esc_attr($image['height']) . '" data-name="' . esc_attr($image['name']) . '" data-pswp-width="' . esc_attr($image['width']) . '" data-pswp-height="' . esc_attr($image['height']) . '">';
            $output .= '<img src="' . esc_url($image['thumbnail']) . '" width="' . $image['width'] . '" height="' . $image['height'] . '" alt="' . esc_attr($image['name']) . '" loading="lazy" />';
            $output .= '</a>';
        }

        $output .= '</div>';  

        // Remove images from pagination data to avoid duplication and store the pagination metadata in a script tag
        unset($paginationData['images']); 
        $output .= '<script type="application/json" id="ggallery-data">'
            . json_encode($paginationData) . '</script>';

        return $output;
    }
}