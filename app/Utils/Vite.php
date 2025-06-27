<?php

namespace GGallery\Utils;

use Exception;

class Vite {

    /**
     * Flag to determine whether hot server is active.
     * Calculated when Vite::initialise() is called.
     *
     * @var bool
     */
    private static bool $isHot = false;

    /**
     * The URI to the hot server. Calculated when
     * Vite::initialise() is called.
     *
     * @var string
     */
    private static string $server;

    /**
     * The path where compiled assets will go.
     *
     * @var string
     */
    private static string $buildPath = 'build';

    /**
     * Manifest file contents. Initialised
     * when Vite::initialise() is called.
     *
     * @var array
     */
    private static array $manifest = [];

    /**
     * To be run in the header.php file, will check for the presence of a hot file.
     *
     * @param  string|null  $buildPath
     * @param  bool  $output  Whether to output the Vite client.
     *
     * @return string|null
     * @throws Exception
     */
    public static function init(string $buildPath = null, bool $output = true): string|null
    {

        static::$isHot = file_exists(static::hotFilePath());

        // have we got a build path override?
        if ($buildPath) {
            static::$buildPath = $buildPath;
        }

        // are we running hot?
        if (static::$isHot) {
            static::$server = file_get_contents(static::hotFilePath());
            $client = static::$server . '/@vite/client';

            // if output
            if ($output) {
                printf(/** @lang text */ '<script type="module" src="%s"></script>', $client);
            }

            return $client;
        }

        // we must have a manifest file...
        if (!file_exists($manifestPath = static::buildPath() . DIRECTORY_SEPARATOR . '.vite' . DIRECTORY_SEPARATOR . 'manifest.json')) {
            throw new Exception('No Vite Manifest exists. Should hot server be running?');
        }

        // store our manifest contents.
        static::$manifest = json_decode(file_get_contents($manifestPath), true);

        return null;
    }

    /**
     * Enqueue the module
     *
     * @param string|null $buildPath
     *
     * @return void
     * @throws Exception
     */
    public static function enqueueMainModule(string $buildPath = null): void
    {
        $client = Vite::init($buildPath, false);
        $manifest = static::$manifest;

        if ($manifest) {

            // if we have a manifest, we are in production mode and need to enqueue dynamic imports
            $dymanicImports = [];

            foreach ($manifest as $handle => $item) {
                // if the item is a dynamic import, we need to enqueue it
                if (isset($item['css']) && is_array($item['css'])) {
                    foreach ($item['css'] as $dynamicImport) {
                        $dymanicImports[] = $dynamicImport;
                    }
                }
            }

            foreach ($dymanicImports as $relPath) {
                $fileUrl = Path::url('build/' . $relPath);
                $fileExtenstion = pathinfo($relPath, PATHINFO_EXTENSION);
                if ($fileExtenstion === 'js' || $fileExtenstion === 'mjs' || $fileExtenstion === 'ts') {
                    // enqueue the script
                    Vite::enqueueScript($handle, $fileUrl, [], null);
                } elseif ($fileExtenstion === 'css' || $fileExtenstion === 'scss') {
                    // enqueue the style
                    Vite::enqueueStyle($handle, $fileUrl, [], null);
                }
            }
        }

        // we only want to continue if we have a client.
        if (!$client) {
            return;
        }

        Vite::enqueueScript('vite-client', $client, [], null);
    }

    public static function echoMainModule(string $buildPath = null): void
    {
        // we only want to continue if we have a client.
        if (!$client = Vite::init($buildPath, false)) {
            return;
        }

        echo "<script type='module' src='$client'></script>";
    }


    public static function enqueueScript(string $handle, string $src, array $deps = [], string|bool|null $ver = false)
    {
        wp_enqueue_script($handle, $src, $deps, $ver);
        Vite::scriptTypeModule($handle);
    }

    public static function enqueueStyle(string $handle, string $src, array $deps = [], string|bool|null $ver = false, string $media = 'all')
    {
        wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }


    /**
     * Return URI path to an asset.
     *
     * @param $asset
     *
     * @return string
     * @throws Exception
     */
    public static function asset($asset): string
    {
        if (static::$isHot) {
            return static::$server . '/' . ltrim($asset, '/');
        }

        if (!array_key_exists($asset, static::$manifest)) {
            throw new Exception('Unknown Vite build asset: ' . $asset);
        }

        return implode('/', [GG_PLUGIN_URL, '/' , static::$buildPath, static::$manifest[$asset]['file']]);
    }

    /**
     * Return a string of html tags for assets defined in the vite input array.
     */
    public static function assetTags(array $assets): string
    {
        $tags = '';

        foreach ($assets as $asset) {
            $tags .= static::tag($asset);
        }

        return $tags;
    }

    /**
     * Return a string of html tags for a single asset.
     */
    public static function tag(string $asset): string
    {
        if (substr($asset, -4) === '.css' || substr($asset, -5) === '.scss') {
            return sprintf('<link rel="stylesheet" href="%s">', static::asset($asset));
        }

        if (substr($asset, -3) === '.js') {
            return sprintf('<script type="module" src="%s"></script>', static::asset($asset));
        }

        return '';
    }

    /**
     * Internal method to determine hotFilePath.
     *
     * @return string
     */
    private static function hotFilePath(): string
    {
        return static::buildPath() . DIRECTORY_SEPARATOR . 'hot';
    }

    /**
     * Internal method to determine buildPath.
     *
     * @return string
     */
    private static function buildPath(): string
    {
        return Path::abs(static::$buildPath);
    }

    /**
     * Return URI path to an image.
     *
     * @param $img
     *
     * @return string|null
     * @throws Exception
     */
    public static function img($img): ?string
    {

        try {

            // set the asset path to the image.
            $asset = 'resources/img/' . ltrim($img, '/');

            // if we're not running hot, return the asset.
            return static::asset($asset);

        } catch (Exception $e) {

            // handle the exception here or log it if needed.
            // you can also return a default image or null in case of an error.
            return $e->getMessage(); // optionally, you can retrieve the error message

        }

    }

    /**
     * Update html script type to module wp hack.
     *
     * @param $scriptHandle bool|string
     * @return mixed
     */
    public static function scriptTypeModule(bool|string $scriptHandle = false): string
    {

        // change the script type to module
        add_filter('script_loader_tag', function ($tag, $handle, $src) use ($scriptHandle) {

            if ($scriptHandle !== $handle) {
                return $tag;
            }

            // return the new script module type tag
            return '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '-js"></script>';

        }, 10, 3);

        // return false
        return false;
    }

}