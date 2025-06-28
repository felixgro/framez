<?php

namespace GGallery\Utils;

class View
{
    private static string $viewPath = 'resources/views/';

    public static function render(string $viewName, array $data = []): string
    {
        $viewFile = Path::abs(self::$viewPath . $viewName . '.view.php');

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: " . $viewFile);
        }

        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }
}