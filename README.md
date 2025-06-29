# FrameZ

Simple auto-optimizing masonry galleries for your wordpress website.

* Free & Open Source (MIT License)
* Lightweight, fast & easy-to-use
* Automatic preview and thumbail generation for better performance while keeping the originals downloadable

## Requirements

* PHP 8 or newer
* Imagick (preferred) or GD PHP Extension
* WP 6.7 or newer

## Getting Started

Register all directories using the `framez_directories` hook:

```php
add_filter('framez_directories', function () {
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

Now, you can render an image gallery using the `[framez]` shortcode with the registered directory key:

```
[framez directory="directory1" perpage="20" startpage="0" loadmore="true"]
```
> [!NOTE]
> `directory` is the only required attribute on this shortcode, the others have defaults which are shown in the example above

## Development

For the development of this plugin, you need to have the following installed:
* [Node.js](https://nodejs.org/en/download/)
* [Composer](https://getcomposer.org/download/)
* [PHP](https://www.php.net/downloads.php) (8.0 or newer)

### Installation

1. Clone the repository:
   ```bash
   git clone TBA
   ```
2. Change into the plugin directory:
   ```bash
    cd framez
    ```
3. Install dependencies:
    ```bash
    composer install
    npm install
    ```
4. Build the assets:
    ```bash
    npm run build
    ```

### Development Server

For development, please host the plugin in a local WordPress installation, e.g. using [Laragon](https://laragon.org/).

> [!HINT]
> You may create a symlink if the plugin is not directly in the `wp-content/plugins` directory, e.g.:
```bash
ln -s /path/to/framez /path/to/wordpress/wp-content/plugins/framez
```

To start the development server, run:
```bash
npm run dev
```

This will start a [Vite](https://vite.dev/) development server that serves the assets with hot module replacement (hmr) in your local hosted WordPress installation.

> [!HINT]
> The Vite development server will ONLY host the hmr assets, you still need to have a local WordPress installation running to see the plugin in action. Hot module replacement will automatically update the assets in your browser when you make changes to the source files.



