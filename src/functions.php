<?php
/**
 * Helper functions.
 *
 * @package Email_Catcher
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ec_email_catcher' ) ) :

	/**
	 * The main function responsible for returning the Email_Catcher instance.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * @return  EC_Email_Catcher instance
	 */
	function ec_email_catcher() {
		return EC_Email_Catcher::instance();
	}

endif;


if ( ! function_exists( 'ec_get_meta' ) ) :

	/**
	 * Get an email post meta field.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $key     The meta key to retrieve.
	 * @param  string $single  Optional. Whether to return a single value or an array. Default false.
	 * @return mixed  Will be an array if $single is false. Will be value of meta data
	 *                field if $single is true.
	 */
	function ec_get_meta( $post_id, $key, $single = false ) {
		$value = get_post_meta( $post_id, 'ec_' . $key, $single );
		$value = apply_filters( 'ec_get_' . $key, $value, $post_id );
		$value = apply_filters( 'ec_get_meta', $value, $post_id, $key );

		return $value;
	}

endif;


if ( ! function_exists( 'ec_print_meta' ) ) :

	/**
	 * Print an email post meta field.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $key     The meta key to retrieve.
	 * @param  string $single  Optional. Whether to return a single value or an array. Default false.
	 * @param  bool   $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_meta( $post_id, $key, $single = false, $echo = true ) {
		$value = ec_get_meta( $post_id, $key, $single );

		if ( $single ) {
			$output = $value;
		} else {
			$output = nl2br( implode( "\n", $value ) );
		}

		$output = apply_filters( 'ec_print_' . $key, $output, $post_id );
		$output = apply_filters( 'ec_print_meta', $output, $post_id, $key );

		if ( ! $echo ) {
			return $output;
		}

		echo esc_html( $output );
	}

endif;


if ( ! function_exists( 'ec_get_subject' ) ) :

	/**
	 * Get the email subject.
	 *
	 * @param  int $post_id Post ID.
	 * @return string
	 */
	function ec_get_subject( $post_id ) {
		return get_the_title( $post_id );
	}

endif;


if ( ! function_exists( 'ec_print_subject' ) ) :

	/**
	 * Print the email subject.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_subject( $post_id, $echo = true ) {
		$output = ec_get_subject( $post_id );

		if ( ! $echo ) {
			return $output;
		}

		echo esc_html( $output );
	}

endif;


if ( ! function_exists( 'ec_get_from' ) ) :

	/**
	 * Get the email "From" address.
	 *
	 * @param  int $post_id Post ID.
	 * @return string
	 */
	function ec_get_from( $post_id ) {
		return ec_get_meta( $post_id, 'from', true );
	}

endif;


if ( ! function_exists( 'ec_print_from' ) ) :

	/**
	 * Print the email "From" address.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_from( $post_id, $echo = true ) {
		return ec_print_meta( $post_id, 'from', true, $echo );
	}

endif;


if ( ! function_exists( 'ec_get_to' ) ) :

	/**
	 * Get the email "To" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function ec_get_to( $post_id ) {
		return ec_get_meta( $post_id, 'to', false );
	}

endif;


if ( ! function_exists( 'ec_print_to' ) ) :

	/**
	 * Print the email "To" recipients.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_to( $post_id, $echo = true ) {
		return ec_print_meta( $post_id, 'to', false, $echo );
	}

endif;


if ( ! function_exists( 'ec_get_cc' ) ) :

	/**
	 * Get the email "CC" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function ec_get_cc( $post_id ) {
		return ec_get_meta( $post_id, 'cc', false );
	}

endif;


if ( ! function_exists( 'ec_print_cc' ) ) :

	/**
	 * Print the email "CC" recipients.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_cc( $post_id, $echo = true ) {
		return ec_print_meta( $post_id, 'cc', false, $echo );
	}

endif;


if ( ! function_exists( 'ec_get_bcc' ) ) :

	/**
	 * Get the email "BCC" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function ec_get_bcc( $post_id ) {
		return ec_get_meta( $post_id, 'bcc', false );
	}

endif;


if ( ! function_exists( 'ec_print_bcc' ) ) :

	/**
	 * Print the email "BCC" recipients.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_bcc( $post_id, $echo = true ) {
		return ec_print_meta( $post_id, 'bcc', false, $echo );
	}

endif;


if ( ! function_exists( 'ec_get_reply_to' ) ) :

	/**
	 * Get the email "Reply To" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function ec_get_reply_to( $post_id ) {
		return ec_get_meta( $post_id, 'reply_to', false );
	}

endif;


if ( ! function_exists( 'ec_print_reply_to' ) ) :

	/**
	 * Print the email "Reply To" recipients.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_reply_to( $post_id, $echo = true ) {
		return ec_print_meta( $post_id, 'reply_to', false, $echo );
	}

endif;


if ( ! function_exists( 'ec_get_body' ) ) :

	/**
	 * Get the email body.
	 *
	 * @param  int $post_id Post ID.
	 * @return string
	 */
	function ec_get_body( $post_id ) {
		return ec_get_meta( $post_id, 'body', true );
	}

endif;


if ( ! function_exists( 'ec_print_body' ) ) :

	/**
	 * Print the email body.
	 * If the email content type is HTML - use an iframe.
	 *
	 * @param  int  $post_id Post ID.
	 * @param  bool $echo    Print or return the output. Default print.
	 * @return mixed
	 */
	function ec_print_body( $post_id, $echo = true ) {
		$is_html = ec_is_html( $post_id );

		if ( $is_html ) {
			$email_catcher = ec_email_catcher();

			$api_url = $email_catcher->api_url(
				array(
					'request' => 'body',
					'post_id' => $post_id,
				)
			);

			$output = '<iframe src="' . $api_url . '" class="ec-iframe" sandbox="allow-same-origin"></iframe>';
		} else {
			$body   = ec_get_body( $post_id );
			$output = nl2br( $body );
		}

		$output = apply_filters( 'ec_print_body', $output, $post_id, $is_html );

		if ( ! $echo ) {
			return $output;
		}

		echo esc_html( $output );
	}

endif;


if ( ! function_exists( 'ec_is_html' ) ) :

	/**
	 * Checks if the email content type is HTML.
	 *
	 * @param  int $post_id Post ID.
	 * @return bool
	 */
	function ec_is_html( $post_id ) {
		$content_type = ec_get_meta( $post_id, 'content_type', true );
		$is_html      = 'text/html' === $content_type;

		return apply_filters( 'ec_is_html', $is_html, $post_id, $content_type );
	}

endif;
