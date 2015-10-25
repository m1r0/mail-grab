<?php

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

if ( !function_exists( 'email_catcher' ) ) :

/**
 * The main function responsible for returning the Email_Catcher instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @example $email_catcher = email_catcher();
 * @return  Email_Catcher instance
 */
function email_catcher() {
	return Email_Catcher::instance();
}

endif; // email_catcher()



if ( !function_exists( 'ec_get_meta' ) ) :

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

endif; // ec_get_meta()



if ( !function_exists( 'ec_print_meta' ) ) :

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
	$value  = ec_get_meta( $post_id, $key, $single );

	if ( $single ) {
		$output = esc_html( $value );
	} else {
		$output = nl2br( esc_html( implode( "\n", $value ) ) );
	}

	$output = apply_filters( 'ec_print_' . $key, $output, $post_id );
	$output = apply_filters( 'ec_print_meta', $output, $post_id, $key );

	if ( !$echo ) {
		return $output;
	}

	echo $output;
}

endif; // ec_print_meta()



if ( !function_exists( 'ec_get_subject' ) ) :

/**
 * Get the email subject.
 *
 * @param  int    $post_id Post ID.
 * @return string
 */
function ec_get_subject( $post_id ) {
	return ec_get_meta( $post_id, 'subject', true );
}

endif; // ec_get_subject()



if ( !function_exists( 'ec_print_subject' ) ) :

/**
 * Print the email subject.
 *
 * @param  int  $post_id Post ID.
 * @param  bool $echo    Print or return the output. Default print.
 * @return mixed
 */
function ec_print_subject( $post_id, $echo = true ) {
	return ec_print_meta( $post_id, 'subject', true, $echo );
}

endif; // ec_print_subject()



if ( !function_exists( 'ec_get_from' ) ) :

/**
 * Get the email "From" address.
 *
 * @param  int    $post_id Post ID.
 * @return string
 */
function ec_get_from( $post_id ) {
	return ec_get_meta( $post_id, 'from', true );
}

endif; // ec_get_from()



if ( !function_exists( 'ec_print_from' ) ) :

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

endif; // ec_print_from()



if ( !function_exists( 'ec_get_to' ) ) :

/**
 * Get the email "To" recipients.
 *
 * @param  int   $post_id Post ID.
 * @return array
 */
function ec_get_to( $post_id ) {
	return ec_get_meta( $post_id, 'to', false );
}

endif; // ec_get_to()



if ( !function_exists( 'ec_print_to' ) ) :

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

endif; // ec_print_to()



if ( !function_exists( 'ec_get_cc' ) ) :

/**
 * Get the email "CC" recipients.
 *
 * @param  int  $post_id Post ID.
 * @return array
 */
function ec_get_cc( $post_id ) {
	return ec_get_meta( $post_id, 'cc', false );
}

endif; // ec_get_cc()



if ( !function_exists( 'ec_print_cc' ) ) :

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

endif; // ec_print_cc()



if ( !function_exists( 'ec_get_bcc' ) ) :

/**
 * Get the email "BCC" recipients.
 *
 * @param  int  $post_id Post ID.
 * @return array
 */
function ec_get_bcc( $post_id ) {
	return ec_get_meta( $post_id, 'bcc', false );
}

endif; // ec_get_bcc()



if ( !function_exists( 'ec_print_bcc' ) ) :

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

endif; // ec_print_bcc()



if ( !function_exists( 'ec_get_reply_to' ) ) :

/**
 * Get the email "Reply To" recipients.
 *
 * @param  int  $post_id Post ID.
 * @return array
 */
function ec_get_reply_to( $post_id ) {
	return ec_get_meta( $post_id, 'reply_to', false );
}

endif; // ec_get_reply_to()



if ( !function_exists( 'ec_print_reply_to' ) ) :

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

endif; // ec_print_reply_to()



if ( !function_exists( 'ec_get_body' ) ) :

/**
 * Get the email body.
 *
 * @param  int  $post_id Post ID.
 * @return string
 */
function ec_get_body( $post_id ) {
	return ec_get_meta( $post_id, 'body', true );
}

endif; // ec_get_body()



if ( !function_exists( 'ec_print_body' ) ) :

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

	if ($is_html) {
		$email_catcher = email_catcher();

		$api_url = $email_catcher->api_url( array(
			'request' => 'body',
			'post_id' => $post_id,
		) );

		$output = '<iframe src="' . $api_url . '" class="ec-iframe" sandbox="allow-same-origin"></iframe>';
	} else {
		$body   = ec_get_body( $post_id );
		$output = nl2br( esc_html( $body ) );
	}

	$output = apply_filters( 'ec_print_body', $output, $post_id, $is_html );

	if ( !$echo ) {
		return $output;
	}

	echo $output;
}

endif; // ec_print_body()



if ( !function_exists( 'ec_is_html' ) ) :

/**
 * Checks if the email content type is HTML.
 *
 * @param  int  $post_id Post ID.
 * @return bool
 */
function ec_is_html( $post_id ) {
	$content_type = ec_get_meta( $post_id, 'content_type', true );
	$is_html      = $content_type === 'text/html';

	return  apply_filters( 'ec_is_html', $is_html, $post_id, $content_type );
}

endif; // ec_is_html()
