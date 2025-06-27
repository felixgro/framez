<?php

namespace GGallery;

class FileController
{
    // /wp-json/ggallery/v1/images?page=X&perPage=X&directory=demo&raw=true
    public static function handleRequest()
    {
        $directoryKey = $_GET['directory'] ?? 'demo';

        $plugin = Plugin::getInstance();
        $dir = $plugin->getDirectory($directoryKey);
        if (empty($dir)) {
            return new \WP_Error('invalid_directory', 'Invalid directory specified.', ['status' => 404]);
        }

        $paginator = new FilePaginator(
            $dir['path'],
            $dir['url'],
            perPage: (int) ($_GET['perPage'] ?? 20),
        );

        $paginationRes = $paginator->paginate(
            page: (int) ($_GET['page'] ?? 1)
        );

        if (array_key_exists('raw', $_GET) && (bool) $_GET['raw']) {
            return $paginationRes;
        };

        $output = '';

        if ($_GET['page'] > $paginationRes['totalPages']) {
            return '';
        }

        foreach ($paginationRes['images'] as $image) {
            $output .= '<a class="ggallery-item" href="' . esc_url($image['url']) . '" data-width="' . esc_attr($image['width']) . '" data-height="' . esc_attr($image['height']) . '" data-name="' . esc_attr($image['name']) . '" data-pswp-width="' . esc_attr($image['width']) . '" data-pswp-height="' . esc_attr($image['height']) . '">';
            $output .= '<img src="' . esc_url($image['thumbnail']) . '" width="' . $image['width'] . '" height="' . $image['height'] . '" alt="' . esc_attr($image['name']) . '" />';
            $output .= '</a>';
        }

        return $output;
    } 

    private static function logPayload()
    {
        error_log(print_r($_GET, true));
    }
}