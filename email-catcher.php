<?php
/**
 * Plugin Name: Email Catcher
 * Description: Catch all emails before they are sent.
 * Author:      Miroslav Mitev
 * Version:     1.0
 * License:     GPL2+
 * Text Domain: email-catcher
 *
 * @package Email_Catcher
 */

defined( 'ABSPATH' ) || exit;

// Constants.
define( 'EC_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
define( 'EC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Includes.
require_once EC_PLUGIN_DIR . 'src/class-ec-email-catcher.php';
require_once EC_PLUGIN_DIR . 'src/class-ec-settings-api.php';
require_once EC_PLUGIN_DIR . 'src/functions.php';

// Hooks.
register_uninstall_hook( EC_PLUGIN_BASENAME, array( 'EC_Email_Catcher', 'uninstall' ) );

// Initialization.
add_action( 'plugins_loaded', 'ec_email_catcher' );
