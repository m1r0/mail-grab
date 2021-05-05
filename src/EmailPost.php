<?php

namespace m1r0\MailGrab;

defined( 'ABSPATH' ) || exit;

/**
 * The email post model class.
 *
 * @package m1r0\MailGrab
 */
class EmailPost {

	/**
	 * The post ID.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * Constructor.
	 *
	 * @param  int $post_id The post ID.
	 * @return void
	 */
	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * Get an email post meta field.
	 *
	 * @param  string $key    The meta key to retrieve.
	 * @param  string $single Optional. Whether to return a single value or an array. Default false.
	 *
	 * @return mixed  Will be an array if $single is false. Will be value of meta data
	 *                field if $single is true.
	 */
	public function get_meta( $key, $single = false ) {
		$value = get_post_meta( $this->post_id, 'mlgb_' . $key, $single );
		$value = apply_filters( 'mlgb_get_' . $key, $value, $this->post_id );

		return apply_filters( 'mlgb_get_meta', $value, $this->post_id, $key );
	}

	/**
	 * Print an email post meta field.
	 *
	 * @param  string $key    The meta key to retrieve.
	 * @param  string $single Optional. Whether to return a single value or an array. Default false.
	 *
	 * @return void
	 */
	public function print_meta( $key, $single = false ) {
		$value = $this->get_meta( $key, $single );

		if ( $single ) {
			$output = $value;
		} else {
			$output = implode( "\n", $value );
		}

		$output = nl2br( esc_html( $output ) );
		$output = apply_filters( 'mlgb_print_' . $key, $output, $this->post_id );
		$output = apply_filters( 'mlgb_print_meta', $output, $this->post_id, $key );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get the email subject.
	 *
	 * @return string
	 */
	public function get_subject() {
		return get_the_title( $this->post_id );
	}

	/**
	 * Print the email subject.
	 *
	 * @return void
	 */
	public function print_subject() {
		$output = $this->get_subject();

		echo esc_html( $output );
	}

	/**
	 * Get the email "From" address.
	 *
	 * @return string
	 */
	public function get_from() {
		return $this->get_meta( 'from', true );
	}

	/**
	 * Print the email "From" address.
	 *
	 * @return void
	 */
	public function print_from() {
		$this->print_meta( 'from', true );
	}

	/**
	 * Get the email "To" recipients.
	 *
	 * @return array
	 */
	public function get_to() {
		return $this->get_meta( 'to' );
	}

	/**
	 * Print the email "To" recipients.
	 *
	 * @return void
	 */
	public function print_to() {
		$this->print_meta( 'to' );
	}

	/**
	 * Get the email "CC" recipients.
	 *
	 * @return array
	 */
	public function get_cc() {
		return $this->get_meta( 'cc' );
	}

	/**
	 * Print the email "CC" recipients.
	 *
	 * @return void
	 */
	public function print_cc() {
		$this->print_meta( 'cc' );
	}

	/**
	 * Get the email "BCC" recipients.
	 *
	 * @return array
	 */
	public function get_bcc() {
		return $this->get_meta( 'bcc' );
	}

	/**
	 * Print the email "BCC" recipients.
	 *
	 * @return void
	 */
	public function print_bcc() {
		$this->print_meta( 'bcc' );
	}

	/**
	 * Get the email "Reply To" recipients.
	 *
	 * @return array
	 */
	public function get_reply_to() {
		return $this->get_meta( 'reply_to' );
	}

	/**
	 * Print the email "Reply To" recipients.
	 *
	 * @return void
	 */
	public function print_reply_to() {
		$this->print_meta( 'reply_to' );
	}

	/**
	 * Get the email custom headers.
	 *
	 * @return array
	 */
	public function get_custom_headers() {
		return $this->get_meta( 'custom_headers' );
	}

	/**
	 * Print the email custom headers.
	 *
	 * @return void
	 */
	public function print_custom_headers() {
		$this->print_meta( 'custom_headers' );
	}

	/**
	 * Get the email body.
	 *
	 * @return string
	 */
	public function get_body() {
		return get_the_content( null, false, $this->post_id );
	}

	/**
	 * Print the email body.
	 * If the email content type is HTML - use an iframe.
	 *
	 * @return void
	 */
	public function print_body() {
		$is_html = $this->is_html();

		if ( $is_html ) {
			$iframe_url = add_query_arg(
				'_wpnonce',
				wp_create_nonce( 'wp_rest' ),
				rest_url( "mail-grab/v1/emails/$this->post_id/body" )
			);

			$output = '<iframe
				src="' . $iframe_url . '"
				class="mlgb-body-iframe"
				sandbox="allow-popups-to-escape-sandbox allow-forms allow-pointer-lock allow-popups allow-presentation allow-orientation-lock allow-modals allow-same-origin"
			></iframe>';
		} else {
			$body   = $this->get_body();
			$output = nl2br( esc_html( $body ) );
		}

		$output = apply_filters( 'mlgb_print_body', $output, $this->post_id, $is_html );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Checks if the email content type is HTML.
	 *
	 * @return bool
	 */
	public function is_html() {
		$content_type = $this->get_meta( 'content_type', true );
		$is_html      = 'text/html' === $content_type;

		return apply_filters( 'mlgb_is_html', $is_html, $this->post_id, $content_type );
	}

}
