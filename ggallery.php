<?php
/*
Plugin Name: GGallery
Description: Image Gallery Plugin for WordPress
Version: 1.0.0
Author: Felix Grohs
Author URI: https://www.komplizinnen.at/
Text Domain: ggallery
*/

defined('ABSPATH') or die('No access');

// Define plugin constants
define('GG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GG_STORAGE_PATH', wp_upload_dir()['basedir'] . '/ggallery');
define('GG_STORAGE_URL', wp_upload_dir()['baseurl'] . '/ggallery');
define('GG_DATA_PATH', GG_STORAGE_PATH . '/data');
define('GG_PLUGIN_VERSION', '1.0.0');

// Init class autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Init plugin
GGallery\Plugin::getInstance();