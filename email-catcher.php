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

// Includes
require_once( dirname( __FILE__ ) . '/src/class.email-catcher.php' );
require_once( dirname( __FILE__ ) . '/src/class.ec-settings-api.php' );
require_once( dirname( __FILE__ ) . '/src/functions.php' );

// Initialize
email_catcher();
