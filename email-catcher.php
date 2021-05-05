<?php
/**
 * Plugin Name: Email Catcher
 * Description: Debug WordPress emails with ease. This plugin enables email logging and prevent email sending.
 * Author:      m1r0
 * Version:     1.0.0
 * License:     GPLv3
 * Text Domain: email-catcher
 *
 * @package m1r0\EmailCatcher
 */

defined( 'ABSPATH' ) || exit;

define( 'EMC_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
define( 'EMC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

register_uninstall_hook( EMC_PLUGIN_BASENAME, array( 'm1r0\EmailCatcher\EmailCatcher', 'uninstall' ) );

/**
 * Begins plugin initialization.
 *
 * @return void
 */
function emc_run_email_catcher() {
	m1r0\EmailCatcher\EmailCatcher::instance()->initialize();
}

emc_run_email_catcher();
