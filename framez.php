<?php
/*
Plugin Name: FrameZ
Description: Image Gallery Plugin for WordPress
Version: 1.0.0
Author: Felix Grohs
Author URI: https://www.komplizinnen.at/
Text Domain: framez
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined('ABSPATH') or die('No access');

// Define plugin constants
define('FZ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FZ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FZ_STORAGE_PATH', wp_upload_dir()['basedir'] . '/framez');
define('FZ_STORAGE_URL', wp_upload_dir()['baseurl'] . '/framez');
define('FZ_DATA_PATH', FZ_STORAGE_PATH . '/data');
define('FZ_PLUGIN_VERSION', '1.0.0');

// Init class autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Init plugin
framez();

