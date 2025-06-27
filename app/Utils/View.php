<?php

namespace GGallery\Utils;

class View
{
    private static string $viewPath = 'resources/views/';

    public static function render(string $viewName, array $data = []): void {
        $filePath = self::$viewPath . $viewName;
        if (!str_ends_with($filePath, '.php')) $filePath .= '.php';
        $filePath = Path::abs(Path::join(self::$viewPath, $filePath));

        if (!file_exists($filePath)) {
            throw new \Exception("View file not found: " . $filePath);
        }

        extract($data);
        include $filePath;
    }
}