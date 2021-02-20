<?php

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

if ( !class_exists( 'EC_Email_Catcher' ) ) :

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

		if ( !is_a( $instance, __CLASS__ ) ) {
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

		# Actions
		add_action( 'phpmailer_init',                                       array( $this, 'catch_email' ),          1000, 1 );
		add_action( 'ec_store_email',                                       array( $this, 'store_email' ),            10, 1 );
		add_action( 'ec_prevent_email',                                     array( $this, 'prevent_email' ),          10, 1 );

		add_action( 'admin_enqueue_scripts',                                array( $this, 'enqueue_scripts' ),        10, 1 );
		add_action( 'init',                                                 array( $this, 'register_post_type' ),     10, 0 );
		add_action( 'admin_menu',                                           array( $this, 'register_settings_menu' ), 10, 0 );
		add_action( 'admin_init',                                           array( $this, 'register_settings' ),      10, 0 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE,                    array( $this, 'register_meta_boxes' ),    10, 1 );

		add_action( 'restrict_manage_posts',                                array( $this, 'column_filters' ),         10, 0 );
		add_action( 'pre_get_posts',                                        array( $this, 'column_query' ),           10, 1 );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column',   array( $this, 'column_output' ),          10, 2 );

		# Filters
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',         array( $this, 'set_columns'),             10, 1 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'set_sortable_columns'),    10, 1 );
		add_filter( 'plugin_action_links_' . EC_PLUGIN_BASENAME,            array( $this, 'set_action_links'),        10, 1 );
	}

	/**
	 * Intercept the email.
	 *
	 * @param  PHPMailer $phpmailer
	 * @return void
	 */
	public function catch_email( &$phpmailer ) {
		// Store the email
		do_action( 'ec_store_email', $phpmailer );

		// Prevent the email sending if the option is enabled
		$prevent_email = $this->settings_api->get_option( 'prevent_email' ) === 'yes';
		if ( $prevent_email ) {
			do_action_ref_array( 'ec_prevent_email', array( &$phpmailer ) );
		}
	}

	/**
	 * Store the email as a post.
	 *
	 * @param  PHPMailer $phpmailer
	 * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	public function store_email( $phpmailer ) {
		$post_id = wp_insert_post( array(
			'post_type'   => self::POST_TYPE,
			'post_status' => 'publish',
		) );

		if ( is_wp_error( $post_id ) || $post_id === 0 ) {
			return $post_id;
		}

		update_post_meta( $post_id, 'ec_subject',      $phpmailer->Subject );
		update_post_meta( $post_id, 'ec_body',         $phpmailer->Body );
		update_post_meta( $post_id, 'ec_content_type', $phpmailer->ContentType );
		update_post_meta( $post_id, 'ec_from',         $phpmailer->addrFormat( array( $phpmailer->From, $phpmailer->FromName ) ) );

		$email_recipients = array(
			'to'           => $phpmailer->getToAddresses(),
			'cc'           => $phpmailer->getCcAddresses(),
			'bcc'          => $phpmailer->getBccAddresses(),
			'reply_to'     => $phpmailer->getReplyToAddresses(),
		);

		// Store the email recipients
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
	 * @param  PHPMailer &$phpmailer
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

		if ( $post_type !== self::POST_TYPE ) {
			return;
		}

		// Post edit screen scripts
		if ( $hook === 'post.php' ) {
			wp_enqueue_script( 'ec-functions', $this->plugin_url( 'js/functions.js' ), array( 'jquery' ) );
			wp_enqueue_style( 'ec-style', $this->plugin_url( 'css/style.css' ) );
		}
	}

	/**
	 * Register the email catcher post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = apply_filters( 'ec_post_type_labels', array(
			'name'               => _x( 'Emails',  'post type general',    'email-catcher' ),
			'singular_name'      => _x( 'Email',   'post type singular',   'email-catcher' ),
			'menu_name'          => _x( 'Email Catcher', 'admin menu',     'email-catcher' ),
			'name_admin_bar'     => _x( 'Email',   'add new on admin bar', 'email-catcher' ),
			'edit_item'          => __( 'View Email',                      'email-catcher' ),
			'view_item'          => __( 'View Email',                      'email-catcher' ),
			'all_items'          => __( 'All Emails',                      'email-catcher' ),
			'search_items'       => __( 'Search Emails',                   'email-catcher' ),
			'not_found'          => __( 'No emails found.',                'email-catcher' ),
			'not_found_in_trash' => __( 'No emails found in Trash.',       'email-catcher' )
		) );

		$args = apply_filters( 'ec_post_type_args', array(
			// Restrict to administrators only
			'capabilities'    => array(
				'edit_post'          => 'manage_options',
				'read_post'          => 'manage_options',
				'delete_post'        => 'manage_options',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'delete_posts'       => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
				'create_posts'       => is_multisite() ? 'do_not_allow' : false, # disable create new post screen
			),
			'menu_icon'       => 'dashicons-email-alt',
			'labels'          => $labels,
			'show_ui'         => true,
			'rewrite'         => false,
			'supports'        => array( '' ),
		) );

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

			if ( !$has_value ) {
				continue;
			}

			add_meta_box(
				'ec-email-' . $type,
				$name,
				array( $this, 'print_meta_box' ),
				self::POST_TYPE,
				'normal',
				'default',
				array(
					'type' => $type
				)
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
		$type = $metabox[ 'args' ][ 'type' ];

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
			__('Email Catcher Settings', 'email-catcher'),
			__('Settings', 'email-catcher'),
			'manage_options',
			'settings' ,
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
				'id' => 'ec_settings',
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
						'no'  => 'No'
					)
				),
				array(
					'name'    => 'uninstall',
					'label'   => __( 'Uninstall', 'email-catcher' ),
					'desc'    => __( 'Remove all stored emails and settings when the plugin is removed.', 'email-catcher' ),
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'Yes',
						'no'  => 'No'
					)
				),
			),
		);

		// Set the settings
		$this->settings_api->set_sections( $sections );
		$this->settings_api->set_fields( $fields );

		// Initialize settings
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
			<h1><?php _e('Email Catcher Settings', 'email-catcher'); ?></h1>
			<?php $this->settings_api->show_forms(); ?>
		</div>
		<?php
	}

	/**
	 * Add select filters to the post listing table.
	 *
	 * @global $post_status
	 * @return void
	 */
	public function column_filters(){
		if ( empty( $_GET[ 'post_type' ] ) || $_GET[ 'post_type' ] !== self::POST_TYPE ) {
			return;
		}

		global $post_status;

		$from     = $this->get_meta_values( 'ec_from',     self::POST_TYPE, $post_status );
		$to       = $this->get_meta_values( 'ec_to',       self::POST_TYPE, $post_status );
		$cc       = $this->get_meta_values( 'ec_cc',       self::POST_TYPE, $post_status );
		$bcc      = $this->get_meta_values( 'ec_bcc',      self::POST_TYPE, $post_status );
		$reply_to = $this->get_meta_values( 'ec_reply_to', self::POST_TYPE, $post_status );

		$filters = array(
			array(
				'name'    => 'ec_from',
				'title'   => __( 'From', 'email-catcher' ),
				'options' => array_combine($from, $from),
			),
			array(
				'name'    => 'ec_to',
				'title'   => __( 'To', 'email-catcher' ),
				'options' => array_combine($to, $to),
			),
			array(
				'name'    => 'ec_cc',
				'title'   => __( 'CC', 'email-catcher' ),
				'options' => array_combine($cc, $cc),
			),
			array(
				'name'    => 'ec_bcc',
				'title'   => __( 'BCC', 'email-catcher' ),
				'options' => array_combine($bcc, $bcc),
			),
			array(
				'name'    => 'ec_reply_to',
				'title'   => __( 'Reply To', 'email-catcher' ),
				'options' => array_combine($reply_to, $reply_to),
			),
		);

		foreach ($filters as $filter): ?>
			<?php $current = isset( $_GET[ $filter[ 'name' ] ] ) ? $_GET[ $filter[ 'name' ] ] : null; ?>

			<select name="<?php echo $filter['name']; ?>">
				<option value="">
					<?php echo $filter[ 'title' ]; ?>
				</option>

				<?php foreach ( $filter[ 'options' ] as $option ): ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $current, $option ); ?>>
						<?php echo esc_html( $option ); ?>
					</option>
				<?php endforeach ?>
			</select>
		<?php endforeach;
	}

	/**
	 * Enable the columns sorting and filtering by altering the query.
	 *
	 * @param  object $query
	 * @return void
	 */
	public function column_query( $query ) {
		if ( !is_admin() || $query->get( 'post_type' ) !== self::POST_TYPE ) {
			return;
		}

		$filters = array(
			'ec_from',
			'ec_to',
			'ec_cc',
			'ec_bcc',
			'ec_reply_to',
		);

		$orderby    = $query->get( 'orderby' );
		$meta_query = $query->get( 'meta_query' );

		// Filtering
		foreach ( $filters as $filter) {
			if ( empty( $_GET[ $filter ] ) ) {
				continue;
			}

			$meta_query[] = array(
				'key'   => $filter,
				'value' => $_GET[ $filter ],
			);
		}

		$query->set( 'meta_query', $meta_query );

		// Sorting
		if ( in_array( $orderby, $filters ) ) {
			$query->set( 'meta_key', $orderby );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Output the value for the new columns.
	 *
	 * @param  string $column
	 * @param  int    $post_id
	 * @return void
	 */
	public function column_output( $column, $post_id ) {
		$primary_column = 'subject';

		$output = '';
		if ( function_exists( 'ec_print_' . $column ) ) {
			$output = call_user_func( 'ec_print_' . $column, $post_id, false );
		}

		if ($column === $primary_column) {
			echo '<a class="row-title" href="' . get_edit_post_link( $post_id ) . '" title="' . __( 'View more details', 'email-catcher' ) . '">' . $output . '</a>';
		} else {
			echo $output;
		}
	}

	/**
	 * Set the post type columns.
	 *
	 * @param  array $columns
	 * @return array $columns
	 */
	public function set_columns( $columns ) {
		$columns[ 'subject' ]  = __( 'Subject',  'email-catcher' );
		$columns[ 'from' ]     = __( 'From',     'email-catcher' );
		$columns[ 'to' ]       = __( 'To',       'email-catcher' );
		$columns[ 'cc' ]       = __( 'CC',       'email-catcher' );
		$columns[ 'bcc' ]      = __( 'BCC',      'email-catcher' );
		$columns[ 'reply_to' ] = __( 'Reply To', 'email-catcher' );

		// Remove the title column
		unset( $columns[ 'title' ] );

		// Make the date column last
		$date_column = $columns[ 'date'] ;
		unset( $columns[ 'date' ] );
		$columns[ 'date' ] = $date_column;

		return $columns;
	}

	/**
	 * Set the sortable post type columns.
	 *
	 * @param  array $columns
	 * @return array $columns
	 */
	public function set_sortable_columns( $columns ) {
		$columns[ 'subject' ]  = 'ec_subject';
		$columns[ 'from' ]     = 'ec_from';
		$columns[ 'to' ]       = 'ec_to';
		$columns[ 'cc' ]       = 'ec_cc';
		$columns[ 'bcc' ]      = 'ec_bcc';
		$columns[ 'reply_to' ] = 'ec_reply_to';

		return $columns;
	}

	/**
	 * Set the plugin action links.
	 *
	 * @param  array $links
	 * @return array $links
	 */
	public function set_action_links( $links ) {
		$links[ 'settings' ] = '<a href="' . admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&page=settings' ) . '">' . __( 'Settings' ) . '</a>';

		return $links;
	}

	/**
	 * Retrieves the absolute URL to the plugins directory (without the trailing slash) or,
	 * when using the $file argument, to a specific file under that directory.
	 *
	 * @param  string $file relative to the plugin path
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
	 * Returns all meta key values from all posts.
	 *
	 * @param  string $meta_key
	 * @param  string $post_type
	 * @param  string $post_status
	 * @global $wpdb
	 * @return array
	 */
	public function get_meta_values( $meta_key, $post_type = self::POST_TYPE, $post_status = null ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"
			SELECT
				DISTINCT( pm.meta_value )
			FROM
				$wpdb->posts AS p
			INNER JOIN
				$wpdb->postmeta AS pm
			ON
				p.ID = pm.post_id
			WHERE
				pm.meta_key = %s
			AND
				p.post_type = %s
			",
			$meta_key,
			$post_type
		);

		if ( $post_status ) {
			$query .= $wpdb->prepare(
				"
				AND
					p.post_status = %s
				",
				$post_status
			);
		} else {
			$query .= $wpdb->prepare(
				"
				AND
					p.post_status <> %s
				",
				'trash'
			);
		}

		return $wpdb->get_col( $query );
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

		// Check if uninstall is enabled and the user permissions
		if ( $uninstall !== 'yes' || !current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Remove all stored email posts
		$emails = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => array( 'any', 'trash', 'auto-draft' ),
			'posts_per_page' => -1,
		) );

		foreach ( $emails as $email ) {
			wp_delete_post( $email->ID, true );
		}

		// Remove plugin options
		delete_site_option( 'ec_settings' );
	}

} // Email_Catcher

endif;
