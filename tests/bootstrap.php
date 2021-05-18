<?php
/**
 * PHPUnit bootstrap file
 *
 * @package m1r0\MailGrab
 */

$mlgb_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $mlgb_tests_dir ) {
	$mlgb_tests_dir = dirname( __FILE__ ) . '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $mlgb_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $mlgb_tests_dir/includes/functions.php, have you run tests/bin/install.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $mlgb_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function mlgb_manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/mail-grab.php';
}
tests_add_filter( 'muplugins_loaded', 'mlgb_manually_load_plugin' );

// Start up the WP testing environment.
require $mlgb_tests_dir . '/includes/bootstrap.php';
