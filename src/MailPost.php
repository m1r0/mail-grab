<?php

namespace m1r0\MailGrab;

defined( 'ABSPATH' ) || exit;

/**
 * The mail post model class.
 *
 * @package m1r0\MailGrab
 */
class MailPost {

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
	 * Get the email attachments.
	 *
	 * @return array
	 */
	public function get_attachments() {
		return $this->get_meta( 'attachments' );
	}

	/**
	 * Print the email attachments.
	 *
	 * @return void
	 */
	public function print_attachments() {
		$attachments       = $this->get_attachments();
		$attachments_count = count( $attachments );

		foreach ( $attachments as $i => $attachment_path ) {
			$relative_path = _wp_relative_upload_path( $attachment_path );
			$attachment_id = $relative_path ? attachment_url_to_postid( $relative_path ) : null;

			if ( $attachment_id ) {
				echo '<a href="' . wp_get_attachment_url( $attachment_id ) . '" target="_blank">' . esc_html( $attachment_path ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo esc_html( $attachment_path );
			}

			echo $i < $attachments_count - 1 ? '<br />' : '';
		}
	}

	/**
	 * Get the email body.
	 *
	 * @return string
	 */
	public function get_body() {
		return get_post_field( 'post_content', $this->post_id );
	}

	/**
	 * Print the email body.
	 * If the email content type is HTML - use an iframe.
	 *
	 * @return void
	 */
	public function print_body() {
		$is_html = $this->is_html();
		$body    = $this->get_body();

		if ( $is_html ) {
			$output = ( new MailProcessor() )->parse( $body );
		} else {
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

	/**
	 * Checks if the email is prevented from sending.
	 *
	 * @return bool
	 */
	public function is_prevented() {
		$prevented = $this->get_meta( 'prevented', true );

		return apply_filters( 'mlgb_is_prevented', $prevented, $this->post_id );
	}

}
