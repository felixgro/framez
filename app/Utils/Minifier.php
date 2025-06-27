<?php

namespace GGallery\Utils;

use Imagick;
use InvalidArgumentException;
use RuntimeException;

class Minifier
{
    public function __construct(
        private string $inputDirectory,
        private string $outputDirectory,
        private string $outputDirectoryUrl = ''
    ) {
        if (!is_dir($this->inputDirectory)) {
            throw new InvalidArgumentException("Input directory does not exist: " . $this->inputDirectory);
        }
        if (!is_dir($this->outputDirectory)) {
            mkdir($this->outputDirectory, 0755, true);
        }
    }

    public function ensureThumbnailExists(string $fileName): void
    {
        // make a reduced size thumbnail using imagick
        $inputPath = Path::join($this->inputDirectory, $fileName);
        if (!file_exists($inputPath)) {
            throw new \RuntimeException("Input file does not exist: " . $inputPath);
        }
        $outputPath = Path::join($this->outputDirectory, $fileName);
        if (file_exists($outputPath)) {
            return; // Thumbnail already exists
        }

        if (class_exists('Imagick')) {
            $imagick = new Imagick($inputPath);

            $imagick->setImageFormat('jpg'); // Ensure the format is JPG
            $imagick->thumbnailImage(350, 350, true); // Resize to a width of 350px
            $imagick->setImageFormat('jpg'); // Ensure the format is JPG
            $imagick->writeImage($outputPath);
            $imagick->clear();
        } else if (function_exists('imagecreatefromjpeg')) {
            $image = imagecreatefromjpeg($inputPath);
            if (!$image) {
                throw new RuntimeException("Failed to create image from: " . $inputPath);
            }

            // Resize the image
            $width = imagesx($image);
            $height = imagesy($image);
            $newWidth = 350;
            $newHeight = (int) (($height / $width) * $newWidth);

            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save the thumbnail
            imagejpeg($thumbnail, $outputPath);
            imagedestroy($thumbnail);
            imagedestroy($image);
        } else {
            throw new RuntimeException("Imagick or GD library is required for thumbnail generation.");
        }

        if (!file_exists($outputPath)) {
            throw new RuntimeException("Failed to create thumbnail: " . $outputPath);
        }

        return;
    }

    public function getThumbnailUrl(string $fileName): string
    {
        return $this->outputDirectoryUrl . $fileName;
    }
}