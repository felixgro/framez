<?php

namespace FrameZ\Minifier;

interface MinifierInterface
{
    public function generateThumbnail(string $inputfile, string $outputfile, int $width, int $height): string;
    public function generatePreview(string $inputfile, string $outputfile, int $width, int $height): string;
}