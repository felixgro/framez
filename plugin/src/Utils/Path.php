<?php

namespace FrameZ\Utils;

class Path
{
    public static function abs(string $relativePath): string {
        $relativePath = ltrim($relativePath, '/'); // Ensure no leading slash
        $absPath = str_replace('/', DIRECTORY_SEPARATOR, FZ_PLUGIN_DIR . $relativePath);

        return $absPath;
    } 

    public static function join(string ...$paths): string {
        $joinedPath = implode(DIRECTORY_SEPARATOR, $paths);
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $joinedPath);
    }

    public static function joinUrl(string ...$paths): string {
        $joinedPath = implode('/', array_map(function ($p) {
            return rtrim(ltrim($p, '/'), '/'); // Ensure no slashes
        }, $paths));
        return $joinedPath;
    }

    public static function url(string $relativePath): string {
        $relativePath = ltrim($relativePath, '/'); // Ensure no leading slash
        $url = FZ_PLUGIN_URL . $relativePath;

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid URL: " . $url);
        }

        return $url;
    }

    public static function fileName(string $filePath): string {
        return basename($filePath);
    }
}