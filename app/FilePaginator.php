<?php

namespace GGallery;

use FilesystemIterator;
use GGallery\Utils\Path;
use GGallery\Minifier\Minifier;

class FilePaginator
{
    private ?int $totalPageCache = null;

    private Minifier $minifier;

    private string $indexPath;
    private array $indexCache = [];

    public function __construct(
        private string $directory,
        private string $directoryUrl,
        private int $perPage = 10,
    ) {
        $this->minifier = new Minifier(
            $directory,
            Path::join(GG_STORAGE_PATH, 'thumbnails/'),
            Path::joinUrl(GG_STORAGE_URL, 'thumbnails/'),
            Path::join(GG_STORAGE_PATH, 'previews/'),
            Path::joinUrl(GG_STORAGE_URL, 'previews/')
        );

        if (!str_ends_with($this->directoryUrl, '/')) {
            $this->directoryUrl = $this->directoryUrl . '/';
        }

        $this->ensureIndexExists();
    }

    public function ensureIndexExists()
    {
        $this->indexPath = Path::join(GG_DATA_PATH, md5($this->directory) . '.json');

        if (!is_dir(dirname($this->indexPath))) {
            mkdir(dirname($this->indexPath), 0755, true);
        }

        if (file_exists($this->indexPath)) {
            $this->indexCache = json_decode(file_get_contents($this->indexPath), true);
        } else {
            // Create the index based on the files in the directory
            $this->indexCache = [];
            $files = glob($this->directory . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $filenames = array_map('basename', $files);
            natsort($filenames);
            file_put_contents(
                $this->indexPath,
                json_encode($filenames, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
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
        $this->ensureIndexExists();
        $index = $this->indexCache;
        $start = ($page - 1) * $this->perPage;
        $filenames = array_slice($index, $start, $this->perPage);

        $files = [];
        foreach ($filenames as $filename) {
            $fileinfo = new \SplFileInfo($this->directory . '/' . $filename);
            $imageSize = getimagesize($fileinfo->getPathname());

            $thumbnailUrl = $this->minifier->ensureThumbnailExists($filename);
            $previewUrl = $this->minifier->ensurePreviewExists($filename);

            $files[] = [
                'name' => $filename,
                'url' => $this->directoryUrl . $filename,
                'thumbnail' => $thumbnailUrl,
                'preview' => $previewUrl,
                'width' => $imageSize[0] ?? 0,
                'height' => $imageSize[1] ?? 0,
                'date' => date('d.m.Y H:i', $fileinfo->getMTime()),
            ];
        }

        return $files;
    }
}
