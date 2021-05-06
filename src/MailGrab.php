<?php

namespace m1r0\MailGrab;

use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Main MailGrab class.
 *
 * @package m1r0\MailGrab
 */
class MailGrab {

	/**
	 * The post type.
	 */
	const POST_TYPE = 'mlgb_email';

	/**
	 * API instance.
	 *
	 * @var Api
	 */
	public $api;

	/**
	 * Search instance.
	 *
	 * @var Search
	 */
	public $search;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	public $settings;

	/**
	 * Singleton implementation.
	 *
	 * @return MailGrab
	 */
	public static function instance() {
		static $instance;

		if ( ! is_a( $instance, __CLASS__ ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->api      = new Api();
		$this->search   = new Search();
		$this->settings = new Settings();
	}

	/**
	 * Register actions, filters, etc...
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'phpmailer_init',                                       array( $this, 'catch_email' ),          1000, 1 );
		add_action( 'mlgb_store_email',                                     array( $this, 'store_email' ),            10, 1 );
		add_action( 'mlgb_prevent_email',                                   array( $this, 'prevent_email' ),          10, 1 );

		add_action( 'init',                                                 array( $this, 'register_post_type' ),     10, 0 );
		add_action( 'admin_enqueue_scripts',                                array( $this, 'enqueue_scripts' ),        10, 1 );
		add_action( 'admin_menu',                                           array( $this, 'register_settings_menu' ), 10, 0 );
		add_action( 'admin_init',                                           array( $this, 'register_settings' ),      10, 0 );
		add_action( 'add_meta_boxes_' . static::POST_TYPE,                  array( $this, 'register_meta_boxes' ),    10, 1 );
		add_filter( 'plugin_action_links_' . MLGB_PLUGIN_BASENAME,          array( $this, 'add_action_links' ),       10, 1 );

		add_filter( 'manage_' . static::POST_TYPE . '_posts_columns',       array( $this, 'add_columns' ),            10, 1 );
		add_action( 'manage_' . static::POST_TYPE . '_posts_custom_column', array( $this, 'print_column' ),           10, 2 );

		$this->api->initialize();
		$this->search->initialize();
	}

	/**
	 * Intercept the email.
	 *
	 * @param  \PHPMailer\PHPMailer\PHPMailer $phpmailer instance.
	 * @return void
	 */
	public function catch_email( $phpmailer ) {
		// Store the email.
		do_action( 'mlgb_store_email', $phpmailer );

		// Prevent the email sending if the option is enabled.
		if ( $this->get_setting( 'prevent_email' ) === 'yes' ) {
			do_action( 'mlgb_prevent_email', $phpmailer );
		}
	}

	/**
	 * Store the email as a post.
	 *
	 * @param  \PHPMailer\PHPMailer\PHPMailer $phpmailer instance.
	 * @return int|\WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function store_email( $phpmailer ) {
		$post_id = wp_insert_post(
			array(
				'post_title'   => $phpmailer->Subject,
				'post_content' => $phpmailer->Body,
				'post_type'    => static::POST_TYPE,
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || 0 === $post_id ) {
			return $post_id;
		}

		update_post_meta( $post_id, 'mlgb_content_type', $phpmailer->ContentType );
		update_post_meta( $post_id, 'mlgb_from',         $phpmailer->addrFormat( array( $phpmailer->From, $phpmailer->FromName ) ) );

		$email_recipients = array(
			'to'       => $phpmailer->getToAddresses(),
			'cc'       => $phpmailer->getCcAddresses(),
			'bcc'      => $phpmailer->getBccAddresses(),
			'reply_to' => $phpmailer->getReplyToAddresses(),
		);

		// Store the email recipients.
		foreach ( $email_recipients as $key => $recipients ) {
			foreach ( $recipients as $recipient ) {
				add_post_meta( $post_id, 'mlgb_' . $key, $phpmailer->addrFormat( $recipient ) );
			}
		}

		// Store the custom headers (if any).
		foreach ( $phpmailer->getCustomHeaders() as $header ) {
			add_post_meta( $post_id, 'mlgb_custom_headers', trim( $header[0] ) . ': ' . trim( $header[1] ) );
		}

		return $post_id;
	}

	/**
	 * Prevent the email sending by clearing the mailer data.
	 *
	 * @param  \PHPMailer\PHPMailer\PHPMailer $phpmailer instance.
	 * @return void
	 */
	public function prevent_email( $phpmailer ) {
		$phpmailer->clearAllRecipients();
		$phpmailer->clearAttachments();
		$phpmailer->clearCustomHeaders();
		$phpmailer->clearReplyTos();
	}

	/**
	 * Back-end scripts.
	 *
	 * @param  string $hook The current admin page.
	 * @global $post_type
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		global $post_type;

		if ( static::POST_TYPE !== $post_type ) {
			return;
		}

		// Post edit screen scripts.
		if ( 'post.php' === $hook ) {
			wp_enqueue_script( 'mlgb-functions', $this->plugin_url( 'js/functions.js' ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'mlgb-style', $this->plugin_url( 'css/style.css' ), array(), '1.0.0' );
		}
	}

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = apply_filters(
			'mlgb_post_type_labels',
			array(
				'name'               => _x( 'Emails',    'post type general',    'mail-grab' ),
				'singular_name'      => _x( 'Email',     'post type singular',   'mail-grab' ),
				'menu_name'          => _x( 'Mail Grab', 'admin menu',           'mail-grab' ),
				'name_admin_bar'     => _x( 'Email',     'add new on admin bar', 'mail-grab' ),
				'edit_item'          => __( 'View Email',                        'mail-grab' ),
				'view_item'          => __( 'View Email',                        'mail-grab' ),
				'all_items'          => __( 'All Emails',                        'mail-grab' ),
				'search_items'       => __( 'Search Emails',                     'mail-grab' ),
				'not_found'          => __( 'No emails found.',                  'mail-grab' ),
				'not_found_in_trash' => __( 'No emails found in Trash.',         'mail-grab' ),
			)
		);

		$args = apply_filters(
			'mlgb_post_type_args',
			array(
				// Restrict to administrators only.
				'capabilities' => array(
					'edit_post'          => 'manage_options',
					'read_post'          => 'manage_options',
					'delete_post'        => 'manage_options',
					'edit_posts'         => 'manage_options',
					'edit_others_posts'  => 'manage_options',
					'delete_posts'       => 'manage_options',
					'publish_posts'      => 'manage_options',
					'read_private_posts' => 'manage_options',
					'create_posts'       => is_multisite() ? 'do_not_allow' : false, // Disable create new post screen.
				),
				'menu_icon'    => 'dashicons-email-alt',
				'labels'       => $labels,
				'show_ui'      => true,
				'rewrite'      => false,
				'supports'     => array( '' ),
			)
		);

		register_post_type( static::POST_TYPE, $args );
	}

	/**
	 * Register post type meta boxes.
	 *
	 * @param  \WP_Post $post Post object.
	 * @return void
	 */
	public function register_meta_boxes( WP_Post $post ) {
		$meta_boxes = array(
			'subject'        => __( 'Subject',        'mail-grab' ),
			'from'           => __( 'From',           'mail-grab' ),
			'to'             => __( 'To',             'mail-grab' ),
			'cc'             => __( 'CC',             'mail-grab' ),
			'bcc'            => __( 'BCC',            'mail-grab' ),
			'reply_to'       => __( 'Reply To',       'mail-grab' ),
			'custom_headers' => __( 'Custom Headers', 'mail-grab' ),
			'body'           => __( 'Body',           'mail-grab' ),
		);

		foreach ( $meta_boxes as $type => $name ) {
			$email_post = new EmailPost( $post->ID );
			$has_value  = call_user_func( array( $email_post, "get_$type" ) );

			if ( ! $has_value ) {
				continue;
			}

			add_meta_box(
				'mlgb-email-' . $type,
				$name,
				array( $this, 'print_meta_box' ),
				static::POST_TYPE,
				'normal',
				'default',
				array( 'type' => $type )
			);
		}
	}

	/**
	 * Print the post type meta box content.
	 *
	 * @param  \WP_Post $post    Post object.
	 * @param  array    $metabox Metabox data.
	 * @return void
	 */
	public function print_meta_box( WP_Post $post, $metabox ) {
		$type = $metabox['args']['type'];

		$email_post = new EmailPost( $post->ID );

		call_user_func( array( $email_post, "print_$type" ) );
	}

	/**
	 * Register the settings menu.
	 *
	 * @return void
	 */
	public function register_settings_menu() {
		add_submenu_page(
			'edit.php?post_type=' . static::POST_TYPE,
			__( 'Mail Grab Settings', 'mail-grab' ),
			__( 'Settings', 'mail-grab' ),
			'manage_options',
			'settings',
			array( $this, 'settings_menu_page' )
		);
	}

	/**
	 * Render the settings menu page.
	 *
	 * @return void
	 */
	public function settings_menu_page() {
		$this->settings->admin_enqueue_scripts();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Mail Grab Settings', 'mail-grab' ); ?></h1>
			<?php $this->settings->show_forms(); ?>
		</div>
		<?php
	}

	/**
	 * Register all settings using the Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		$sections = array(
			array(
				'id'    => 'mlgb_settings',
				'title' => '',
			),
		);

		$fields = array(
			'mlgb_settings' => array(
				array(
					'name'    => 'prevent_email',
					'label'   => __( 'Prevent email sending', 'mail-grab' ),
					'desc'    => __( 'Prevent emails from being sent.', 'mail-grab' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
				),
				array(
					'name'    => 'cleanup',
					'label'   => __( 'Cleanup', 'mail-grab' ),
					'desc'    => __( 'Remove all stored emails and settings when the plugin is removed.', 'mail-grab' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
				),
			),
		);

		// Set the settings.
		$this->settings->set_sections( $sections );
		$this->settings->set_fields( $fields );

		// Initialize settings.
		$this->settings->admin_init();
	}

	/**
	 * Get the value of a settings field.
	 *
	 * @param  string $setting Settings field name.
	 * @param  string $default Default text if it's not found.
	 * @return string
	 */
	public function get_setting( $setting, $default = '' ) {
		return $this->settings->get_option( $setting, 'mlgb_settings', $default );
	}

	/**
	 * Print the value for the columns.
	 *
	 * @param  string $column  The name of the column to display.
	 * @param  int    $post_id The current post ID.
	 * @return void
	 */
	public function print_column( $column, $post_id ) {
		$email_post = new EmailPost( $post_id );

		$email_post->print_meta( $column );
	}

	/**
	 * Add the post type columns.
	 *
	 * @param  array $columns An associative array of column headings.
	 * @return array $columns
	 */
	public function add_columns( $columns ) {
		$columns['from'] = __( 'From', 'mail-grab' );
		$columns['to']   = __( 'To',   'mail-grab' );

		// Make the date column last.
		$date_column = $columns['date'];
		unset( $columns['date'] );
		$columns['date'] = $date_column;

		return $columns;
	}

	/**
	 * Add the plugin action links.
	 *
	 * @param  array $links An array of plugin action links.
	 * @return array $links
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'edit.php?post_type=' . static::POST_TYPE . '&page=settings' ) . '">' . __( 'Settings', 'mail-grab' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Retrieves the absolute URL to the plugins directory (without the trailing slash) or,
	 * when using the $file argument, to a specific file under that directory.
	 *
	 * @param  string $file Relative to the plugin path.
	 * @return string absolute URL
	 */
	public function plugin_url( $file = '' ) {
		return plugins_url( $file, dirname( __FILE__ ) );
	}

	/**
	 * Removes all traces of the plugin if the "cleanup" option is enabled.
	 *
	 * @see    register_uninstall_hook() in the main file
	 * @return void
	 */
	public static function cleanup() {
		if ( 'yes' !== static::instance()->get_setting( 'cleanup' ) ) {
			return;
		}

		// Remove all stored email posts.
		$emails = get_posts(
			array(
				'post_type'      => static::POST_TYPE,
				'post_status'    => array( 'any', 'trash', 'auto-draft' ),
				'posts_per_page' => -1,
			)
		);

		foreach ( $emails as $email ) {
			wp_delete_post( $email->ID, true );
		}

		// Remove plugin options.
		delete_site_option( 'mlgb_settings' );
	}

}
