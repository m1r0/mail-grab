<?php
/**
 * Plugin Name: Email Catcher
 * Description: Catch all emails before they are sent.
 * Author:      Miroslav Mitev
 * Version:     1.0
 * License:     GPL2+
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
 * Returns the main instance of the EmailCatcher.
 *
 * @return m1r0\EmailCatcher\EmailCatcher
 */
function emc_email_catcher() {
	return m1r0\EmailCatcher\EmailCatcher::instance();
}

emc_email_catcher();
