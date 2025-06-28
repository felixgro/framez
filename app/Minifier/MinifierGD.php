<?php

namespace GGallery\Minifier;

class MinifierGD implements MinifierInterface
{
    /**
     * Resize an image keeping the aspect ratio.
     */
    public function generateThumbnail(string $inputFile, string $outputFile, int $width, int $height): string
    {
        $image = imagecreatefromjpeg($inputFile);
        [$origWidth, $origHeight] = getimagesize($inputFile);

        // Compute new dimensions preserving aspect ratio
        [$newWidth, $newHeight] = $this->calculateAspectRatioFit($origWidth, $origHeight, $width, $height);

        // Scale image
        $thumb = imagescale($image, $newWidth, $newHeight, IMG_BILINEAR_FIXED);

        // Save
        imagejpeg($thumb, $outputFile);

        imagedestroy($image);
        imagedestroy($thumb);

        return $outputFile;
    }

    /**
     * compress and resize an image
     */
    public function generatePreview(string $inputFile, string $outputFile, int $width, int $height): string
    {
        $image = imagecreatefromjpeg($inputFile);
        [$origWidth, $origHeight] = getimagesize($inputFile);

        // Compute new dimensions preserving aspect ratio
        [$newWidth, $newHeight] = $this->calculateAspectRatioFit($origWidth, $origHeight, $width, $height);

        // Scale image
        $preview = imagescale($image, $newWidth, $newHeight, IMG_BILINEAR_FIXED);

        // Save with specified quality
        imagejpeg($preview, $outputFile, 90);

        imagedestroy($image);
        imagedestroy($preview);

        return $outputFile;
    }

    /**
     * Helper to compute target dimensions maintaining aspect ratio
     */
    private function calculateAspectRatioFit(int $srcWidth, int $srcHeight, int $maxWidth, int $maxHeight): array
    {
        $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight);
        $newWidth = (int)round($srcWidth * $ratio);
        $newHeight = (int)round($srcHeight * $ratio);
        return [$newWidth, $newHeight];
    }
}