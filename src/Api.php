<?php

namespace m1r0\MailGrab;

use WP_REST_Server;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * The REST API class.
 *
 * @package m1r0\MailGrab
 */
class Api {

	/**
	 * Register actions, filters, etc...
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ),   10, 0 );
	}

	/**
	 * Register the rest routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'mail-grab/v1',
			'/emails/(?P<id>\d+)/body',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'rest_get_email_body_html' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param );
						},
					),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}


	/**
	 * Output the email body html.
	 *
	 * @param  \WP_REST_Request $request Full details about the request.
	 * @return void
	 */
	public function rest_get_email_body_html( WP_REST_Request $request ) {
		header( 'Content-Type: text/html' );

		$email_post = new EmailPost( $request['id'] );

		echo $email_post->get_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// We're done.
		die();
	}

}
