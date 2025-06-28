# GGallery

Simple auto-optimizing mansonry galleries for your wordpress website.

* Lightweight, fast & easy-to-use
* Automatic preview and thumbail generation for better performance while keeping the originals downloadable

## Requirements

* PHP 8 or newer
* Imagick (preferred) or GD PHP Extension
* WP 6.7 or newer

## Getting Started

Just register all directories using the `ggallery_file_directories` hook:

```php
add_filter('ggallery_file_directories', function () {
    return [
        'directory1' => [
            'path' => '/path/to/directory',
            'url' => 'https://domain.com/path/to/directory',
        ],
        'directory2' => [
            'path' => '/path/to/directory',
            'url' => 'https://domain.com/path/to/directory',
        ],
        // ...
    ];
});
```

Now, you can render a registered image gallery using the `[ggallery]` shortcode with the related directory key:

```
[ggallery directory="directory1" perpage="20" startpage="0" loadmore="true"]
```
> [!NOTE]
> `directory` is the only required attribute on this shortcode, the others have defaults which are shown in the example above