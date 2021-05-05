<?php
/**
 * Plugin Name: Mail Grab
 * Description: Debug WordPress emails with ease. This plugin enables email logging and can prevent email sending.
 * Author:      m1r0
 * Version:     1.0.0
 * License:     GPLv3
 * Text Domain: mail-grab
 *
 * @package m1r0\MailGrab
 */

defined( 'ABSPATH' ) || exit;

define( 'MLGB_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
define( 'MLGB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

register_uninstall_hook( MLGB_PLUGIN_BASENAME, array( 'm1r0\MailGrab\MailGrab', 'uninstall' ) );

/**
 * Begins plugin initialization.
 */
m1r0\MailGrab\MailGrab::instance()->initialize();
