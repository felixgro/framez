<?php

namespace GGallery;

use FilesystemIterator;
use GGallery\Utils\Minifier;
use GGallery\Utils\Path;

class FilePaginator
{
    private ?int $totalPageCache = null;

    private Minifier $minifier;

    public function __construct(
        private string $directory,
        private string $directoryUrl,
        private int $perPage = 10,
    ) {
        $this->minifier = new Minifier(
            $directory,
            Path::abs('storage/thumbnails/'),
            Path::url('storage/thumbnails/')
        );

        if (!str_ends_with($this->directoryUrl, '/')) {
            $this->directoryUrl = $this->directoryUrl . '/';
        }
    }

    public function paginate(int $page = 1)
    {
        $totalPages = $this->getTotalPages();
        $page = max(1, min($page, $totalPages)); // Clamp page between 1 and total
        $images = $this->getImageFilesPaginated($page);
        return [
            'page' => $page,
            'perPage' => $this->perPage,
            'totalPages' => $totalPages,
            'totalImages' => count($images),
            'hasNextPage' => $page < $totalPages,
            'hasPreviousPage' => $page > 1,
            'images' => $images,
        ];
    }

    public function getTotalPages()
    {
        if ($this->totalPageCache !== null) {
            return $this->totalPageCache;
        }

        $count = 0;

        $iterator = new FilesystemIterator(
            $this->directory,
            FilesystemIterator::SKIP_DOTS
        );

        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) continue;

            $ext = strtolower($fileinfo->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $count++;
            }
        }
        $this->totalPageCache = (int) ceil($count / $this->perPage);
        return $this->totalPageCache;
    }

    private function getImageFilesPaginated($page = 1)
    {
        $files = [];
        $start = ($page - 1) * $this->perPage;
        $end = $start + $this->perPage;

        $iterator = new FilesystemIterator($this->directory, FilesystemIterator::SKIP_DOTS);
        $i = 0;

        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) continue;

            // Optionally filter image types
            $ext = strtolower($fileinfo->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) continue;

            if ($i >= $start && $i < $end) {
                // Get image size
                $imageSize = getimagesize($fileinfo->getPathname());

                // Ensure thumbnail exists and get its URL
                $this->minifier->ensureThumbnailExists($fileinfo->getFilename());
                $thumbnailUrl = $this->minifier->getThumbnailUrl($fileinfo->getFilename());

                // Store file metadata in the array
                $files[] = [
                    'name' => $fileinfo->getFilename(),
                    'url' => $this->directoryUrl . $fileinfo->getFilename(),
                    'thumbnail' => $thumbnailUrl,
                    'width' => $imageSize[0] ?? 0,
                    'height' => $imageSize[1] ?? 0,
                    'date' => date('d.m.Y H:i', $fileinfo->getMTime()),
                    // 'path' => $fileinfo->getPathname(),
                ];
            }

            if ($i >= $end) break;

            $i++;
        }

        return $files;
    }
}
