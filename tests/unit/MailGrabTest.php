<?php

use m1r0\MailGrab\MailGrab;
use m1r0\MailGrab\Settings;

/**
 * MailGrabTest
 *
 * @package m1r0\MailGrab
 */
class MailGrabTest extends WP_UnitTestCase {
	public function test_it_can_catch_multiple_emails() {
		/* Arrange */
		$emails_count = 3;

		/* Act */
		for ( $i = 0; $i < $emails_count; $i++ ) {
			wp_mail( 'test@example.com', 'hello', 'world' );
		}

		/* Assert */
		$this->assertEquals( $emails_count, wp_count_posts( MailGrab::POST_TYPE )->publish );
	}

	public function test_it_can_prevent_email_sending() {
		/* Arrange */
		$settings_mock = Mockery::mock( Settings::class );
		$settings_mock
			->shouldReceive( 'get_option' )
			->with( 'prevent_email', 'mlgb_settings', '' )
			->andReturn( 'yes' );

		MailGrab::instance()->settings = $settings_mock;

		/* Act */
		$success = wp_mail( 'test@example.com', 'hello', 'world' );

		/* Assert */
		$this->assertFalse( $success );
	}
}
