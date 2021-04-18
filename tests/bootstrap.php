<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Email_Catcher
 */

$emc_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $emc_tests_dir ) {
	$emc_tests_dir = dirname( __FILE__ ) . '/tmp/wordpress-tests-lib';
}

if ( ! file_exists( $emc_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $emc_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $emc_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function emc_manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/email-catcher.php';
}
tests_add_filter( 'muplugins_loaded', 'emc_manually_load_plugin' );

// Start up the WP testing environment.
require $emc_tests_dir . '/includes/bootstrap.php';
