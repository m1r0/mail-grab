<?php

defined( 'ABSPATH' ) || exit;

/**
 * Main Email Catcher class
 */
class EC_Email_Catcher {

	/**
	 * The email catcher post type.
	 */
	const POST_TYPE = 'ec_email';

	/**
	 * Settings API.
	 *
	 * @var EC_Settings_API
	 */
	private $settings_api;

	/**
	 * Singleton implementation.
	 *
	 * @return EC_Email_Catcher
	 */
	public static function instance() {
		static $instance;

		if ( ! is_a( $instance, __CLASS__ ) ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Silence is golden.
	 *
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Register actions, filters, etc...
	 *
	 * @return void
	 */
	private function setup() {
		$this->settings_api = new EC_Settings_API();

		// Actions.
		add_action( 'phpmailer_init',                                       array( $this, 'catch_email' ),          1000, 1 );
		add_action( 'ec_store_email',                                       array( $this, 'store_email' ),            10, 1 );
		add_action( 'ec_prevent_email',                                     array( $this, 'prevent_email' ),          10, 1 );

		add_action( 'init',                                                 array( $this, 'register_post_type' ),     10, 0 );
		add_action( 'admin_enqueue_scripts',                                array( $this, 'enqueue_scripts' ),        10, 1 );
		add_action( 'admin_menu',                                           array( $this, 'register_settings_menu' ), 10, 0 );
		add_action( 'admin_init',                                           array( $this, 'register_settings' ),      10, 0 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE,                    array( $this, 'register_meta_boxes' ),    10, 1 );
		add_filter( 'plugin_action_links_' . EC_PLUGIN_BASENAME,            array( $this, 'add_action_links' ),       10, 1 );

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',         array( $this, 'add_columns' ),            10, 1 );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column',   array( $this, 'print_column' ),           10, 2 );
	}

	/**
	 * Intercept the email.
	 *
	 * @param  PHPMailer $phpmailer instance.
	 * @return void
	 */
	public function catch_email( &$phpmailer ) {
		// Store the email.
		do_action( 'ec_store_email', $phpmailer );

		// Prevent the email sending if the option is enabled.
		$prevent_email = $this->settings_api->get_option( 'prevent_email' ) === 'yes';
		if ( $prevent_email ) {
			do_action_ref_array( 'ec_prevent_email', array( &$phpmailer ) );
		}
	}

	/**
	 * Store the email as a post.
	 *
	 * @param  PHPMailer $phpmailer instance.
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function store_email( $phpmailer ) {
		$post_id = wp_insert_post(
			array(
				'post_title'  => $phpmailer->Subject,
				'post_type'   => self::POST_TYPE,
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || 0 === $post_id ) {
			return $post_id;
		}

		update_post_meta( $post_id, 'ec_body',         $phpmailer->Body );
		update_post_meta( $post_id, 'ec_content_type', $phpmailer->ContentType );
		update_post_meta( $post_id, 'ec_from',         $phpmailer->addrFormat( array( $phpmailer->From, $phpmailer->FromName ) ) );

		$email_recipients = array(
			'to'       => $phpmailer->getToAddresses(),
			'cc'       => $phpmailer->getCcAddresses(),
			'bcc'      => $phpmailer->getBccAddresses(),
			'reply_to' => $phpmailer->getReplyToAddresses(),
		);

		// Store the email recipients.
		foreach ( $email_recipients as $key => $recipients ) {
			foreach ( $recipients as $recipient ) {
				update_post_meta( $post_id, 'ec_' . $key, $phpmailer->addrFormat( $recipient ) );
			}
		}

		return $post_id;
	}

	/**
	 * Prevent the email sending by clearing the mailer data.
	 *
	 * @param  PHPMailer $phpmailer instance.
	 * @return void
	 */
	public function prevent_email( &$phpmailer ) {
		$phpmailer->clearAllRecipients();
		$phpmailer->clearAttachments();
		$phpmailer->clearCustomHeaders();
		$phpmailer->clearReplyTos();
	}

	/**
	 * Back-end scripts.
	 *
	 * @param  string $hook
	 * @global $post_type
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		global $post_type;

		if ( self::POST_TYPE !== $post_type ) {
			return;
		}

		// Post edit screen scripts.
		if ( 'post.php' === $hook ) {
			wp_enqueue_script( 'ec-functions', $this->plugin_url( 'js/functions.js' ), array( 'jquery' ), '1.0.0', false );
			wp_enqueue_style( 'ec-style', $this->plugin_url( 'css/style.css' ), array(), '1.0.0' );
		}
	}

	/**
	 * Register the email catcher post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = apply_filters(
			'ec_post_type_labels',
			array(
				'name'               => _x( 'Emails',  'post type general',    'email-catcher' ),
				'singular_name'      => _x( 'Email',   'post type singular',   'email-catcher' ),
				'menu_name'          => _x( 'Email Catcher', 'admin menu',     'email-catcher' ),
				'name_admin_bar'     => _x( 'Email',   'add new on admin bar', 'email-catcher' ),
				'edit_item'          => __( 'View Email',                      'email-catcher' ),
				'view_item'          => __( 'View Email',                      'email-catcher' ),
				'all_items'          => __( 'All Emails',                      'email-catcher' ),
				'search_items'       => __( 'Search Emails',                   'email-catcher' ),
				'not_found'          => __( 'No emails found.',                'email-catcher' ),
				'not_found_in_trash' => __( 'No emails found in Trash.',       'email-catcher' ),
			)
		);

		$args = apply_filters(
			'ec_post_type_args',
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

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register post type meta boxes.
	 *
	 * @param  WP_Post $post
	 * @return void
	 */
	public function register_meta_boxes( WP_Post $post ) {
		$meta_boxes = array(
			'subject'  => __( 'Subject',  'email-catcher' ),
			'from'     => __( 'From',     'email-catcher' ),
			'to'       => __( 'To',       'email-catcher' ),
			'cc'       => __( 'CC',       'email-catcher' ),
			'bcc'      => __( 'BCC',      'email-catcher' ),
			'reply_to' => __( 'Reply To', 'email-catcher' ),
			'body'     => __( 'Body',     'email-catcher' ),
		);

		foreach ( $meta_boxes as $type => $name ) {
			$has_value = call_user_func( 'ec_get_' . $type, $post->ID );

			if ( ! $has_value ) {
				continue;
			}

			add_meta_box(
				'ec-email-' . $type,
				$name,
				array( $this, 'print_meta_box' ),
				self::POST_TYPE,
				'normal',
				'default',
				array( 'type' => $type )
			);
		}
	}

	/**
	 * Print the post type meta box content.
	 *
	 * @param  object $post
	 * @param  array  $metabox
	 * @return void
	 */
	public function print_meta_box( $post, $metabox ) {
		$type = $metabox['args']['type'];

		call_user_func( 'ec_print_' . $type, $post->ID );
	}

	/**
	 * Register the settings menu.
	 *
	 * @return void
	 */
	public function register_settings_menu() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Email Catcher Settings', 'email-catcher' ),
			__( 'Settings', 'email-catcher' ),
			'manage_options',
			'settings',
			array( $this, 'settings_menu_page' )
		);
	}

	/**
	 * Register all settings using the Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		$sections = array(
			array(
				'id'    => 'ec_settings',
				'title' => '',
			),
		);

		$fields = array(
			'ec_settings' => array(
				array(
					'name'    => 'prevent_email',
					'label'   => __( 'Prevent email sending', 'email-catcher' ),
					'desc'    => __( 'Prevent emails from being sent.', 'email-catcher' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
				),
				array(
					'name'    => 'uninstall',
					'label'   => __( 'Uninstall', 'email-catcher' ),
					'desc'    => __( 'Remove all stored emails and settings when the plugin is removed.', 'email-catcher' ),
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
		$this->settings_api->set_sections( $sections );
		$this->settings_api->set_fields( $fields );

		// Initialize settings.
		$this->settings_api->admin_init();
	}

	/**
	 * Render the settings menu page.
	 *
	 * @return void
	 */
	public function settings_menu_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Email Catcher Settings', 'email-catcher' ); ?></h1>
			<?php $this->settings_api->show_forms(); ?>
		</div>
		<?php
	}

	/**
	 * Print the value for the columns.
	 *
	 * @param  string $column
	 * @param  int    $post_id
	 * @return void
	 */
	public function print_column( $column, $post_id ) {
		if ( function_exists( 'ec_print_' . $column ) ) {
			echo call_user_func( 'ec_print_' . $column, $post_id, false );
		}
	}

	/**
	 * Add the post type columns.
	 *
	 * @param  array $columns
	 * @return array $columns
	 */
	public function add_columns( $columns ) {
		$columns['from'] = __( 'From', 'email-catcher' );
		$columns['to']   = __( 'To',   'email-catcher' );

		// Make the date column last.
		$date_column = $columns['date'];
		unset( $columns['date'] );
		$columns['date'] = $date_column;

		return $columns;
	}

	/**
	 * Add the plugin action links.
	 *
	 * @param  array $links
	 * @return array $links
	 */
	public function add_action_links( $links ) {
		$links['settings'] = '<a href="' . admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&page=settings' ) . '">' . __( 'Settings', 'email-catcher' ) . '</a>';

		return $links;
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
	 * Retrieves the absolute URL to the API with the arguments applied.
	 *
	 * @param  array $args
	 * @return string absolute URL
	 */
	public function api_url( $args ) {
		return add_query_arg( $args, $this->plugin_url( 'api.php' ) );
	}

	/**
	 * Removes all traces of the plugin if the "uninstall" option is enabled.
	 *
	 * @see    register_uninstall_hook() in the main file
	 * @return void
	 */
	public static function uninstall() {
		$email_catcher = ec_email_catcher();
		$uninstall     = $email_catcher->settings_api->get_option( 'uninstall' );

		// Check if uninstall is enabled and the user permissions.
		if ( 'yes' !== $uninstall || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Remove all stored email posts.
		$emails = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'any', 'trash', 'auto-draft' ),
				'posts_per_page' => -1,
			)
		);

		foreach ( $emails as $email ) {
			wp_delete_post( $email->ID, true );
		}

		// Remove plugin options.
		delete_site_option( 'ec_settings' );
	}

} // Email_Catcher