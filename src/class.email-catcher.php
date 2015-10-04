<?php

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

if ( !class_exists( 'Email_Catcher' ) ) :

/**
 * Main Email Catcher class
 */
class Email_Catcher {

	/**
	 * The email catcher post type.
	 */
	const POST_TYPE = 'ec_email';

	/**
	 * Settings API
	 *
	 * @var EC_Settings_API
	 */
	private $settings_api;

	/**
	 * Singleton implementation.
	 *
	 * @return Email_Catcher
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
		add_action( 'phpmailer_init',                                       array( $this, 'store_email' ),            900, 1 );
		add_action( 'phpmailer_init',                                       array( $this, 'prevent_email' ),          910, 1 );

		add_action( 'admin_enqueue_scripts',                                array( $this, 'enqueue_scripts' ),        10,  1 );
		add_action( 'init',                                                 array( $this, 'register_post_type' ),     10,  0 );
		add_action( 'admin_menu',                                           array( $this, 'register_settings_menu' ), 10,  0 );
		add_action( 'admin_init',                                           array( $this, 'register_settings' ),      10,  0 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE,                    array( $this, 'register_meta_boxes' ),    10,  0 );

		add_action( 'restrict_manage_posts',                                array( $this, 'column_filters' ),         10,  0 );
		add_action( 'pre_get_posts',                                        array( $this, 'column_query' ),           10,  1 );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column',   array( $this, 'column_output' ),          10,  2 );

		# Filters
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',         array( $this, 'set_columns'),             10,  1 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'set_sortable_columns'),    10,  1 );
	}

	/**
	 * Store the email as a post.
	 *
	 * @param  PHPMailer $phpmailer
	 * @return void
	 */
	public function store_email( PHPMailer $phpmailer ) {
		$sender       = $phpmailer->From;
		$recipients   = $phpmailer->getAllRecipientAddresses();
		$subject      = $phpmailer->Subject;
		$body         = $phpmailer->Body;
		$content_type = $phpmailer->ContentType;

		$post_id = wp_insert_post( array(
			'post_type'   => self::POST_TYPE,
			'post_title'  => $subject,
			'post_status' => 'publish',
		) );

		if ( !$post_id ) {
			return false;
		}

		update_post_meta( $post_id, 'ec_email_sender',       $sender );
		update_post_meta( $post_id, 'ec_email_body',         $body );
		update_post_meta( $post_id, 'ec_email_content_type', $content_type );

		foreach ( array_keys($recipients) as $recipient ) {
			add_post_meta( $post_id, 'ec_email_recipients', $recipient );
		}
	}

	/**
	 * Prevent the email sending (if enabled).
	 *
	 * @param  PHPMailer &$phpmailer
	 * @return void
	 */
	public function prevent_email( PHPMailer &$phpmailer ) {
		$prevent_email = $this->settings_api->get_option( 'prevent_email' );

		// Check the prevent email option
		if ( $prevent_email !== 'yes' ) {
			return false;
		}

		// Prevent the email sending by clearing the mailer data.
		$phpmailer->ClearAllRecipients();
		$phpmailer->ClearAttachments();
		$phpmailer->ClearCustomHeaders();
		$phpmailer->ClearReplyTos();
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
			wp_enqueue_script( 'ec-functions', $this->plugin_url( 'js/functions.js' ), 'jquery' );
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
			'add_new'            => _x( 'Add New', 'email',                'email-catcher' ),
			'add_new_item'       => __( 'Add New Email',                   'email-catcher' ),
			'new_item'           => __( 'New Email',                       'email-catcher' ),
			'edit_item'          => __( 'View Email',                      'email-catcher' ),
			'view_item'          => __( 'View Email',                      'email-catcher' ),
			'all_items'          => __( 'All Emails',                      'email-catcher' ),
			'search_items'       => __( 'Search Emails',                   'email-catcher' ),
			'parent_item_colon'  => __( 'Parent Emails:',                  'email-catcher' ),
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
				'create_posts'       => false, # disable create new post screen
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
	 * @return void
	 */
	public function register_meta_boxes() {
		$email_meta_boxes = array(
			'subject'    => __( 'Subject', 'email-catcher' ),
			'sender'     => __( 'From',    'email-catcher' ),
			'recipients' => __( 'To',      'email-catcher' ),
			'body'       => __( 'Body',    'email-catcher' ),
		);

		foreach ( $email_meta_boxes as $id => $title ) {
			add_meta_box( 
				'ec-email-' . $id,
				$title,
				'ec_the_email_' . $id,
				self::POST_TYPE,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Register the settings menu.
	 *
	 * @return void
	 */
	public function register_settings_menu() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE, 
			'Email Catcher Settings', 
			'Settings', 
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
					'desc'    => __( 'Prevent emails from being sent', 'email-catcher' ),
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

		$senders    = $this->get_meta_values( 'ec_email_sender',     self::POST_TYPE, $post_status );
		$recipients = $this->get_meta_values( 'ec_email_recipients', self::POST_TYPE, $post_status );

		$filters = array(
			array(
				'name'    => 'ec_email_sender',
				'title'   => __( 'Senders', 'email-catcher' ),
				'options' => array_combine($senders, $senders),
			),
			array(
				'name'    => 'ec_email_recipients',
				'title'   => __( 'Recipients', 'email-catcher' ),
				'options' => array_combine($recipients, $recipients),
			),
		);

		foreach ($filters as $filter): ?>
			<select name="<?php echo $filter['name']; ?>">
				<option value="">
					<?php echo _x( 'All', 'emails filter', 'email-catcher' ) . ' ' . $filter[ 'title' ]; ?>
				</option>

				<?php $current = isset( $_GET[ $filter[ 'name' ] ] ) ? $_GET[ $filter[ 'name' ] ] : null; ?>

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
			'ec_email_sender',
			'ec_email_recipients',
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
		switch ( $column ) {
			case 'sender':
				ec_the_email_sender( $post_id );
				break;

			case 'recipients':
				ec_the_email_recipients( $post_id );
				break;
		}
	}

	/**
	 * Set the post type columns.
	 *
	 * @param  array $
	 * @return array $columns
	 */
	public function set_columns( $columns ) {
		$columns[ 'title' ]      = __( 'Subject', 'email-catcher' );
		$columns[ 'sender' ]     = __( 'From',    'email-catcher' );
		$columns[ 'recipients' ] = __( 'To',      'email-catcher' );

		// Make the date column last
		$date_column       = $columns[ 'date'] ;
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
		$columns[ 'sender' ]     = 'ec_email_sender';
		$columns[ 'recipients' ] = 'ec_email_recipients';

		return $columns;
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

} // Email_Catcher

endif;
