# FrameZ – Image Galleries for WordPress

Create beautiful, auto-optimizing masonry galleries on your WordPress website with ease.

* Free & Open Source
* Lightweight, fast & easy-to-use
* Fully accessible and responsive
* Automatic preview and thumbail generation for better performance while keeping the originals downloadable

## Requirements

* PHP 8 or newer
* WP 6.7 or newer
* Imagick (recommended) or GD PHP Extension

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

## Roadmap
* [ ] Add support for custom storage locations (custom server directory)
* [ ] Add settings pages for configuring the plugin in the dashboard 
* [ ] Add support for custom image sizes

## Development

For the development of this plugin, you need to have the following installed:

* [All requirements mentioned above](#requirements)
* [Node.js v24 or newer](https://nodejs.org/en/download/)
* [Composer](https://getcomposer.org/download/)
* Any local server running WordPress

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/felixgro/framez.git
   ```
2. Change into the plugin directory:
   ```bash
    cd framez/plugin
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
5. Create a symlink to the `wp-content/plugins` directory of your WordPress installation:
    ```bash
    ln -s $(pwd) /path/to/your/wordpress/wp-content/plugins/framez
    ```
    Windows users can create a symlink using the `mklink` command in the Command Prompt:
    ```cmd
    mklink /J "C:\path\to\your\wordpress\wp-content\plugins\framez" "C:\path\to\framez\plugin"
    ```

### Development Server

For development, you need to have a WordPress installation hosted locally.

> [!TIP]
> You can use tools like [Local by Flywheel](https://localwp.com/), [Laragon](https://laragon.org) or [DevKinsta](https://kinsta.com/devkinsta/) to set up a local WordPress environment.

Before starting the development server, you have to configure the local wordpress url in the `vite.config.js` file as `DEV_ORIGIN`:
```javascript
//...
const DEV_ORIGIN = 'http://your-local-wordpress-url.test';
//...
```

Then, to start the development server, run:
```bash
npm run dev
```

This will start a [Vite](https://vite.dev/) development server that serves the assets with hot module replacement (hmr) in your local hosted WordPress installation.

> [!NOTE]
> The Vite development server will ONLY host the hmr assets, you still need to have a local WordPress installation running to see the plugin in action. Hot module replacement will automatically update the assets in your browser when you make changes to the source files.

### Building for Production

This project uses Docker to build the plugin package for distribution, as well as to publish the documentation website.

#### Prerequisites

Make sure you have [Docker](https://www.docker.com/get-started) installed and running on your machine.

#### Build the Docker Image & Run the Container

To build the image, run the following command in your project root:

```bash
docker build -f docker/Dockerfile -t framez . --no-cache
```

Then, start the container using the `framez` image we've just built:

```bash
docker run --rm -p 3000:3000 framez
```

Now, the plugin is available in the `/srv/http` directory inside the container, and will be served at `http://localhost:3000` on your host machine. You may download the freshly baked `framez.zip` plugin package directly from `http://localhost:3000/framez.zip`. 

>[!TIP]
> If port `3000` is already in use, you can change the port mapping in the `docker run` command to any other available port, e.g. `-p 8080:3000`

### Contributions

Contributions are welcome! If you have any ideas, suggestions or issues, feel free to open an issue or a pull request on GitHub.


