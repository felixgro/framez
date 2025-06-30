<?php

namespace FrameZ\Minifier;

use Imagick;

class MinifierImagick implements MinifierInterface
{
    /**
     * Resize an image keeping the aspect ratio.
     */
    public function generateThumbnail(string $inputFile, string $outputFile, int $width, int $height): string
    {
        $imagick = new Imagick($inputFile);
        $imagick->thumbnailImage($width, $height, true);
        $imagick->writeImage($outputFile);
        $imagick->clear();
        return $outputFile;
    }

    /**
     * compress and resize an image
     */
    public function generatePreview(string $inputFile, string $outputFile, int $width, int $height): string
    {
        $imagick = new Imagick($inputFile);

        // Use high-quality Lanczos resampling
        $imagick->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);

        // Remove metadata
        $imagick->stripImage();

        // Set compression quality
        $imagick->setImageCompressionQuality(90);

        // Save output
        $imagick->writeImage($outputFile);
        $imagick->clear();
        return $outputFile;
    }
}
