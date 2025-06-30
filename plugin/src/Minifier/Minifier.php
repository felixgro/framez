<?php

namespace FrameZ\Minifier;

use FrameZ\Utils\Path;
use RuntimeException;

class Minifier
{
    private MinifierInterface $baseMinifier;

    public function __construct(
        private string $inputDirectory,
        private string $outputDirectory,
        private string $outputDirectoryUrl,
        private string $previewDirectory,
        private string $previewDirectoryUrl
    ) {
        if (!is_dir($this->outputDirectory)) {
            mkdir($this->outputDirectory, 0755, true);
        }
        if (!is_dir($this->previewDirectory)) {
            mkdir($this->previewDirectory, 0755, true);
        }

        if (class_exists('Imagick')) {
            $this->baseMinifier = new MinifierImagick();
        } else if (function_exists('imagecreatefromjpeg')) {
            $this->baseMinifier = new MinifierGD();
        } else {
            throw new RuntimeException("No image processing library available. Please install Imagick or GD.");
        }
    }

    public function ensurePreviewExists(string $fileName): string
    {
        // make a reduced size preview using imagick
        $inputPath = Path::join($this->inputDirectory, $fileName);
        if (!file_exists($inputPath)) {
            throw new RuntimeException("Input file does not exist: " . $inputPath);
        }
        $outputPath = Path::join($this->previewDirectory, $fileName);
        $outputUrl = Path::joinUrl($this->previewDirectoryUrl, $fileName);

        if (file_exists($outputPath)) {
            return $outputUrl; // Preview already exists
        }

        $this->baseMinifier->generatePreview($inputPath, $outputPath, 1200, 1200);

        if (!file_exists($outputPath)) {
            throw new RuntimeException("Failed to create preview: " . $outputPath);
        }

        return $outputUrl;
    }

    public function ensureThumbnailExists(string $fileName): string
    {
        // make a reduced size thumbnail using imagick
        $inputPath = Path::join($this->inputDirectory, $fileName);
        if (!file_exists($inputPath)) {
            throw new \RuntimeException("Input file does not exist: " . $inputPath);
        }
        $outputPath = Path::join($this->outputDirectory, $fileName);
        $outputUrl = Path::joinUrl($this->outputDirectoryUrl, $fileName);
        if (file_exists($outputPath)) {
            return $outputUrl; // Thumbnail already exists
        }

        $this->baseMinifier->generateThumbnail($inputPath, $outputPath, 350, 350);

        if (!file_exists($outputPath)) {
            throw new RuntimeException("Failed to create thumbnail: " . $outputPath);
        }

        return $outputUrl;
    }
}