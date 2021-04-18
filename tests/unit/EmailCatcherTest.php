<?php

use m1r0\EmailCatcher\EmailCatcher;
use m1r0\EmailCatcher\Settings;

/**
 * EmailCatcherTest
 *
 * @package m1r0\EmailCatcher
 */
class EmailCatcherTest extends WP_UnitTestCase {
	public function test_it_can_catch_multiple_emails() {
		/* Arrange */
		$emails_count = 3;

		/* Act */
		for ( $i = 0; $i < $emails_count; $i++ ) {
			wp_mail( 'test@example.com', 'hello', 'world' );
		}

		/* Assert */
		$this->assertEquals( $emails_count, wp_count_posts( EmailCatcher::POST_TYPE )->publish );
	}

	public function test_it_can_prevent_email_sending() {
		/* Arrange */
		$settings_mock = Mockery::mock( Settings::class );
		$settings_mock
			->shouldReceive( 'get_option' )
			->with( 'prevent_email', 'emc_settings', '' )
			->andReturn( 'yes' );

		EmailCatcher::instance()->settings = $settings_mock;

		/* Act */
		$success = wp_mail( 'test@example.com', 'hello', 'world' );

		/* Assert */
		$this->assertFalse( $success );
	}
}
