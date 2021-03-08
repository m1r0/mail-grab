<?php
/**
 * Helper functions.
 *
 * @package Email_Catcher
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'emc_email_catcher' ) ) :

	/**
	 * The main function responsible for returning the Email_Catcher instance.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * @return  EMC_Email_Catcher instance
	 */
	function emc_email_catcher() {
		return EMC_Email_Catcher::instance();
	}

endif;


if ( ! function_exists( 'emc_get_meta' ) ) :

	/**
	 * Get an email post meta field.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $key     The meta key to retrieve.
	 * @param  string $single  Optional. Whether to return a single value or an array. Default false.
	 * @return mixed  Will be an array if $single is false. Will be value of meta data
	 *                field if $single is true.
	 */
	function emc_get_meta( $post_id, $key, $single = false ) {
		$value = get_post_meta( $post_id, 'emc_' . $key, $single );
		$value = apply_filters( 'emc_get_' . $key, $value, $post_id );
		$value = apply_filters( 'emc_get_meta', $value, $post_id, $key );

		return $value;
	}

endif;


if ( ! function_exists( 'emc_print_meta' ) ) :

	/**
	 * Print an email post meta field.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $key     The meta key to retrieve.
	 * @param  string $single  Optional. Whether to return a single value or an array. Default false.
	 * @return void
	 */
	function emc_print_meta( $post_id, $key, $single = false ) {
		$value = emc_get_meta( $post_id, $key, $single );

		if ( $single ) {
			$output = $value;
		} else {
			$output = implode( "\n", $value );
		}

		$output = nl2br( esc_html( $output ) );
		$output = apply_filters( 'emc_print_' . $key, $output, $post_id );
		$output = apply_filters( 'emc_print_meta', $output, $post_id, $key );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

endif;


if ( ! function_exists( 'emc_get_subject' ) ) :

	/**
	 * Get the email subject.
	 *
	 * @param  int $post_id Post ID.
	 * @return string
	 */
	function emc_get_subject( $post_id ) {
		return get_the_title( $post_id );
	}

endif;


if ( ! function_exists( 'emc_print_subject' ) ) :

	/**
	 * Print the email subject.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_subject( $post_id ) {
		$output = emc_get_subject( $post_id );

		echo esc_html( $output );
	}

endif;


if ( ! function_exists( 'emc_get_from' ) ) :

	/**
	 * Get the email "From" address.
	 *
	 * @param  int $post_id Post ID.
	 * @return string
	 */
	function emc_get_from( $post_id ) {
		return emc_get_meta( $post_id, 'from', true );
	}

endif;


if ( ! function_exists( 'emc_print_from' ) ) :

	/**
	 * Print the email "From" address.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_from( $post_id ) {
		emc_print_meta( $post_id, 'from', true );
	}

endif;


if ( ! function_exists( 'emc_get_to' ) ) :

	/**
	 * Get the email "To" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function emc_get_to( $post_id ) {
		return emc_get_meta( $post_id, 'to', false );
	}

endif;


if ( ! function_exists( 'emc_print_to' ) ) :

	/**
	 * Print the email "To" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_to( $post_id ) {
		emc_print_meta( $post_id, 'to', false );
	}

endif;


if ( ! function_exists( 'emc_get_cc' ) ) :

	/**
	 * Get the email "CC" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function emc_get_cc( $post_id ) {
		return emc_get_meta( $post_id, 'cc', false );
	}

endif;


if ( ! function_exists( 'emc_print_cc' ) ) :

	/**
	 * Print the email "CC" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_cc( $post_id ) {
		emc_print_meta( $post_id, 'cc', false );
	}

endif;


if ( ! function_exists( 'emc_get_bcc' ) ) :

	/**
	 * Get the email "BCC" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function emc_get_bcc( $post_id ) {
		return emc_get_meta( $post_id, 'bcc', false );
	}

endif;


if ( ! function_exists( 'emc_print_bcc' ) ) :

	/**
	 * Print the email "BCC" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_bcc( $post_id ) {
		emc_print_meta( $post_id, 'bcc', false );
	}

endif;


if ( ! function_exists( 'emc_get_reply_to' ) ) :

	/**
	 * Get the email "Reply To" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return array
	 */
	function emc_get_reply_to( $post_id ) {
		return emc_get_meta( $post_id, 'reply_to', false );
	}

endif;


if ( ! function_exists( 'emc_print_reply_to' ) ) :

	/**
	 * Print the email "Reply To" recipients.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_reply_to( $post_id ) {
		emc_print_meta( $post_id, 'reply_to', false );
	}

endif;


if ( ! function_exists( 'emc_get_body' ) ) :

	/**
	 * Get the email body.
	 *
	 * @param  int $post_id Post ID.
	 * @return string
	 */
	function emc_get_body( $post_id ) {
		return get_the_content( null, false, $post_id );
	}

endif;


if ( ! function_exists( 'emc_print_body' ) ) :

	/**
	 * Print the email body.
	 * If the email content type is HTML - use an iframe.
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	function emc_print_body( $post_id ) {
		$is_html = emc_is_html( $post_id );

		if ( $is_html ) {
			$iframe_url = add_query_arg(
				'_wpnonce',
				wp_create_nonce( 'wp_rest' ),
				rest_url( "email-catcher/v1/emails/$post_id/body" )
			);

			$output = '<iframe
				src="' . $iframe_url . '"
				class="emc-iframe"
				sandbox="allow-popups-to-escape-sandbox allow-forms allow-pointer-lock allow-popups allow-presentation allow-orientation-lock allow-modals allow-same-origin"
			></iframe>';
		} else {
			$body   = emc_get_body( $post_id );
			$output = nl2br( esc_html( $body ) );
		}

		$output = apply_filters( 'emc_print_body', $output, $post_id, $is_html );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

endif;


if ( ! function_exists( 'emc_is_html' ) ) :

	/**
	 * Checks if the email content type is HTML.
	 *
	 * @param  int $post_id Post ID.
	 * @return bool
	 */
	function emc_is_html( $post_id ) {
		$content_type = emc_get_meta( $post_id, 'content_type', true );
		$is_html      = 'text/html' === $content_type;

		return apply_filters( 'emc_is_html', $is_html, $post_id, $content_type );
	}

endif;
