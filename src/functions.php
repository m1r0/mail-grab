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

endif; // email_catcher



if ( !function_exists( 'ec_get_email_subject' ) ) :

/**
 * Returns the email subject.
 *
 * @param  int|object $post Post ID/Object
 * @return string
 */
function ec_get_email_subject( $post ) {
	$post = get_post( $post );

	return apply_filters( 'ec_email_subject', $post->post_title, $post );
}

endif; // ec_get_email_subject



if ( !function_exists( 'ec_the_email_subject' ) ) :

/**
 * Prints the email subject.
 *
 * @param  int|object $post Post ID/Object
 * @return void
 */
function ec_the_email_subject( $post ) {
	$post   = get_post( $post );
	$output = ec_get_email_subject( $post );

	echo apply_filters( 'ec_email_subject_output', $output, $post );
}

endif; // ec_the_email_subject



if ( !function_exists( 'ec_get_email_sender' ) ) :

/**
 * Returns the email sender.
 *
 * @param  int|object $post Post ID/Object
 * @return string
 */
function ec_get_email_sender( $post ) {
	$post   = get_post( $post );
	$sender = get_post_meta( $post->ID, 'ec_email_sender', true );

	return apply_filters( 'ec_email_sender', $sender, $post );
}

endif; // ec_get_email_sender



if ( !function_exists( 'ec_the_email_sender' ) ) :

/**
 * Prints the email sender.
 *
 * @param  int|object $post Post ID/Object
 * @return void
 */
function ec_the_email_sender( $post ) {
	$post   = get_post( $post );
	$output = ec_get_email_sender( $post );

	echo apply_filters( 'ec_email_sender_output', $output, $post );
}

endif; // ec_the_email_sender



if ( !function_exists( 'ec_get_email_recipients' ) ) :

/**
 * Returns the email recipients.
 *
 * @param  int|object $post Post ID/Object
 * @return array
 */
function ec_get_email_recipients( $post ) {
	$post       = get_post( $post );
	$recipients = get_post_meta( $post->ID, 'ec_email_recipients', false );

	return apply_filters( 'ec_email_recipients', $recipients, $post );
}

endif; // ec_get_email_recipients



if ( !function_exists( 'ec_the_email_recipients' ) ) :

/**
 * Prints the email recipients.
 *
 * @param  int|object $post Post ID/Object
 * @return void
 */
function ec_the_email_recipients( $post ) {
	$post   = get_post( $post );
	$output = nl2br( implode( "\n", ec_get_email_recipients( $post ) ) );

	echo apply_filters( 'ec_email_recipients_output', $output, $post );
}

endif; // ec_the_email_recipients



if ( !function_exists( 'ec_get_email_body' ) ) :

/**
 * Returns the email body.
 *
 * @param  int|object $post Post ID/Object
 * @return string
 */
function ec_get_email_body( $post ) {
	$post = get_post( $post );
	$body = get_post_meta( $post->ID, 'ec_email_body', true );

	return apply_filters( 'ec_email_body', $body, $post );
}

endif; // ec_get_email_body



if ( !function_exists( 'ec_the_email_body' ) ) :

/**
 * Prints the email body.
 * If the email content type is HTML - use an iframe.
 *
 * @param  int|object $post Post ID/Object
 * @return void
 */
function ec_the_email_body( $post ) {
	$post    = get_post( $post );
	$is_html = ec_email_is_html( $post );

	if ($is_html) {
		$email_catcher = email_catcher();
		$api_url       = $email_catcher->api_url( array( 
			'request' => 'email_body', 
			'post_id' => $post->ID 
		) );

		$output = '<iframe src="' . $api_url . '" class="ec-iframe" sandbox="allow-same-origin"></iframe>';
	} else {
		$body   = ec_get_email_body( $post );
		$output = nl2br( esc_html( $body ) );
	}

	echo apply_filters( 'ec_email_body_output', $output, $post, $is_html );
}

endif; // ec_the_email_body



if ( !function_exists( 'ec_email_is_html' ) ) :

/**
 * Checks if the email content type is HTML.
 *
 * @param  int|object $post Post ID/Object
 * @return bool
 */
function ec_email_is_html( $post ) {
	$post         = get_post( $post );
	$content_type = get_post_meta( $post->ID, 'ec_email_content_type', true );
	$is_html      = $content_type === 'text/html';

	return  apply_filters( 'ec_email_is_html', $is_html, $post, $content_type );
}

endif; // ec_email_is_html
