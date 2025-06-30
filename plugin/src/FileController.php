<?php

namespace FrameZ;

use FrameZ\Utils\View;
use WP_Error;

class FileController
{
    // /wp-json/framez/v1/images?page=X&perPage=X&directory=demo&raw=true
    public static function handleRequest()
    {
        $directoryKey = $_GET['directory'] ?? 'demo';

        $plugin = Plugin::getInstance();
        $dir = $plugin->getDirectory($directoryKey);
        if (empty($dir)) {
            return new WP_Error('invalid_directory', 'Invalid directory specified.', ['status' => 404]);
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

        if ($_GET['page'] > $paginationRes['totalPages']) {
            return ''; // Return empty string to notify no more images to load
        }

        $output = '';
        foreach ($paginationRes['images'] as $image) {
            $output .= View::render('framez-item', [
                'image' => $image,
            ]);
        }
        return $output;
    }
}