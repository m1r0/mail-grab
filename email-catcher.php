<?php

/**
 * Plugin Name: Email Catcher
 * Description: Catch all emails before they are sent.
 * Author:      Miroslav Mitev
 * Version:     1.0
 * License:     GPL2+
 * Text Domain: email-catcher
*/

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

// Constants
define('EC_PLUGIN_DIR',      plugin_dir_path( __FILE__ ) );
define('EC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Includes
require_once( EC_PLUGIN_DIR . 'src/class.email-catcher.php' );
require_once( EC_PLUGIN_DIR . 'src/class.ec-settings-api.php' );
require_once( EC_PLUGIN_DIR . 'src/functions.php' );

// Hooks
register_uninstall_hook( EC_PLUGIN_BASENAME, array( 'Email_Catcher', 'uninstall' ) );

// Initialization
add_action( 'plugins_loaded', 'email_catcher' );
