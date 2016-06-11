<?php
/**
 * OverWrite It - WordPress Plugin.
 *
 * 'OverWrite It' overwrites to existing files when you use Media Library.
 *
 * @package OverWriteIt
 * @since 0.0.1
 */

/**
 * Plugin Name: OverWrite It
 * Plugin URI: https://www.wordpress.org/plugins/overwrite-it
 * Description: 'OverWrite It' is a WordPress plugin that overwrites to existing files when using Media Library.
 * Version: 1.0.3
 * Author: Türker YILDIRIM
 * Author URI: http://turkeryildirim.com
 * Text Domain: overwrite-it
 * Domain Path: /languages/
 * License: http://www.gnu.org/licenses/gpl-3.0.html
 */


// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


require_once ( plugin_dir_path( __FILE__ ) . 'overwrite-it.php' );
$GLOBALS['OverWriteIt'] = OverWriteIt::getInstance();

?>