<?php
/**
 * Admin Settings class for PassWP Posts.
 *
 * Handles the plugin settings page using WordPress Settings API.
 *
 * @package PassWP_Posts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class PassWP_Posts_Admin_Settings
 *
 * Creates and manages the plugin settings page.
 */
class PassWP_Posts_Admin_Settings {

	/**
	 * Option name for plugin settings.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'passwp_posts_settings';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'passwp-posts-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// AJAX handler for post search.
		add_action( 'wp_ajax_passwp_posts_search', array( $this, 'ajax_search_posts' ) );
	}

	/**
	 * Add settings page under Settings menu.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'PassWP Posts Settings', 'passwp-posts' ),
			__( 'PassWP Posts', 'passwp-posts' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'passwp_posts_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'password_hash'      => '',
					'cookie_expiry_days' => 30,
					'excluded_posts'     => array(),
					'enabled'            => false,
				),
			)
		);

		// Main settings section.
		add_settings_section(
			'passwp_posts_main_section',
			__( 'Password Protection Settings', 'passwp-posts' ),
			array( $this, 'render_section_description' ),
			self::PAGE_SLUG
		);

		// Enable protection field.
		add_settings_field(
			'passwp_posts_enabled',
			__( 'Enable Protection', 'passwp-posts' ),
			array( $this, 'render_enabled_field' ),
			self::PAGE_SLUG,
			'passwp_posts_main_section'
		);

		// Password field.
		add_settings_field(
			'passwp_posts_password',
			__( 'Password', 'passwp-posts' ),
			array( $this, 'render_password_field' ),
			self::PAGE_SLUG,
			'passwp_posts_main_section'
		);

		// Cookie expiry field.
		add_settings_field(
			'passwp_posts_cookie_expiry',
			__( 'Remember Me Duration', 'passwp-posts' ),
			array( $this, 'render_cookie_expiry_field' ),
			self::PAGE_SLUG,
			'passwp_posts_main_section'
		);

		// Excluded posts field.
		add_settings_field(
			'passwp_posts_excluded',
			__( 'Excluded Pages/Posts', 'passwp-posts' ),
			array( $this, 'render_excluded_posts_field' ),
			self::PAGE_SLUG,
			'passwp_posts_main_section'
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our settings page.
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		// Select2 CSS.
		wp_enqueue_style(
			'select2',
			PASSWP_POSTS_URL . 'assets/vendor/select2/select2.min.css',
			array(),
			'4.1.0'
		);

		// Select2 JS.
		wp_enqueue_script(
			'select2',
			PASSWP_POSTS_URL . 'assets/vendor/select2/select2.min.js',
			array( 'jquery' ),
			'4.1.0',
			true
		);

		// Admin CSS.
		wp_enqueue_style(
			'passwp-posts-admin',
			PASSWP_POSTS_URL . 'assets/css/admin.css',
			array( 'select2' ),
			PASSWP_POSTS_VERSION
		);

		// Admin JS.
		wp_enqueue_script(
			'passwp-posts-admin',
			PASSWP_POSTS_URL . 'assets/js/admin.js',
			array( 'jquery', 'select2' ),
			PASSWP_POSTS_VERSION,
			true
		);

		// Localize script for AJAX.
		wp_localize_script(
			'passwp-posts-admin',
			'passwpPostsAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'passwp_posts_search' ),
				'placeholder' => __( 'Search for pages or posts...', 'passwp-posts' ),
			)
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show settings saved message.
		if ( isset( $_GET[ 'settings-updated' ] ) ) {
			add_settings_error(
				'passwp_posts_messages',
				'passwp_posts_message',
				__( 'Settings saved.', 'passwp-posts' ),
				'updated'
			);
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( 'passwp_posts_messages' ); ?>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'passwp_posts_settings_group' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'passwp-posts' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render section description.
	 *
	 * @return void
	 */
	public function render_section_description() {
		echo '<p>' . esc_html__( 'Configure password protection for your site. The front page and logged-in users are always allowed.', 'passwp-posts' ) . '</p>';
	}

	/**
	 * Render enabled checkbox field.
	 *
	 * @return void
	 */
	public function render_enabled_field() {
		$settings = get_option( self::OPTION_NAME, array() );
		$enabled  = isset( $settings[ 'enabled' ] ) ? (bool) $settings[ 'enabled' ] : false;
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]" value="1" <?php checked( $enabled ); ?> />
			<?php esc_html_e( 'Enable password protection for all pages and posts', 'passwp-posts' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'When enabled, visitors must enter the password to view any page except the front page.', 'passwp-posts' ); ?>
		</p>
		<?php
	}

	/**
	 * Render password field.
	 *
	 * @return void
	 */
	public function render_password_field() {
		$settings     = get_option( self::OPTION_NAME, array() );
		$has_password = ! empty( $settings[ 'password_hash' ] );
		?>
		<input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[password]" id="passwp_posts_password"
			class="regular-text"
			placeholder="<?php echo $has_password ? esc_attr__( 'Leave blank to keep current password', 'passwp-posts' ) : esc_attr__( 'Enter password', 'passwp-posts' ); ?>"
			autocomplete="new-password" />
		<?php if ( $has_password ) : ?>
			<p class="description">
				<?php esc_html_e( 'A password is currently set. Enter a new password to change it, or leave blank to keep the current password.', 'passwp-posts' ); ?>
			</p>
		<?php else : ?>
			<p class="description">
				<?php esc_html_e( 'Enter the password visitors will use to access protected content.', 'passwp-posts' ); ?>
			</p>
		<?php endif; ?>
	<?php
	}

	/**
	 * Render cookie expiry field.
	 *
	 * @return void
	 */
	public function render_cookie_expiry_field() {
		$settings    = get_option( self::OPTION_NAME, array() );
		$expiry_days = isset( $settings[ 'cookie_expiry_days' ] ) ? absint( $settings[ 'cookie_expiry_days' ] ) : 30;
		?>
		<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cookie_expiry_days]"
			id="passwp_posts_cookie_expiry" class="small-text" value="<?php echo esc_attr( $expiry_days ); ?>" min="1"
			max="365" />
		<span><?php esc_html_e( 'days', 'passwp-posts' ); ?></span>
		<p class="description">
			<?php esc_html_e( 'How long the "Remember Me" authentication lasts. Default is 30 days.', 'passwp-posts' ); ?>
		</p>
		<?php
	}

	/**
	 * Render excluded posts field with Select2.
	 *
	 * @return void
	 */
	public function render_excluded_posts_field() {
		$settings       = get_option( self::OPTION_NAME, array() );
		$excluded_posts = isset( $settings[ 'excluded_posts' ] ) ? (array) $settings[ 'excluded_posts' ] : array();

		// Get the currently excluded posts for display.
		$selected_posts = array();
		if ( ! empty( $excluded_posts ) ) {
			$selected_posts = get_posts(
				array(
					'post_type'      => array( 'post', 'page' ),
					'post__in'       => array_map( 'intval', $excluded_posts ),
					'posts_per_page' => -1,
					'orderby'        => 'post__in',
					'post_status'    => 'any',
				)
			);
		}
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[excluded_posts][]" id="passwp_posts_excluded"
			class="passwp-posts-select2" multiple="multiple" style="width: 100%; max-width: 400px;">
			<?php foreach ( $selected_posts as $post ) : ?>
				<option value="<?php echo esc_attr( $post->ID ); ?>" selected>
					<?php echo esc_html( $post->post_title . ' (' . ucfirst( $post->post_type ) . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select pages or posts that should be accessible without a password (in addition to the front page).', 'passwp-posts' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input from form.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Get existing settings to preserve password if not changed.
		$existing = get_option( self::OPTION_NAME, array() );

		// Sanitize enabled.
		$sanitized[ 'enabled' ] = isset( $input[ 'enabled' ] ) && '1' === $input[ 'enabled' ];

		// Sanitize and hash password.
		if ( ! empty( $input[ 'password' ] ) ) {
			$sanitized[ 'password_hash' ] = wp_hash_password( $input[ 'password' ] );
		} elseif ( ! empty( $existing[ 'password_hash' ] ) ) {
			// Keep existing password if field was left blank.
			$sanitized[ 'password_hash' ] = $existing[ 'password_hash' ];
		} else {
			$sanitized[ 'password_hash' ] = '';
		}

		// Sanitize cookie expiry days.
		$sanitized[ 'cookie_expiry_days' ] = isset( $input[ 'cookie_expiry_days' ] ) ? absint( $input[ 'cookie_expiry_days' ] ) : 30;
		$sanitized[ 'cookie_expiry_days' ] = max( 1, min( 365, $sanitized[ 'cookie_expiry_days' ] ) );

		// Sanitize excluded posts.
		$sanitized[ 'excluded_posts' ] = array();
		if ( isset( $input[ 'excluded_posts' ] ) && is_array( $input[ 'excluded_posts' ] ) ) {
			$sanitized[ 'excluded_posts' ] = array_map( 'absint', $input[ 'excluded_posts' ] );
			$sanitized[ 'excluded_posts' ] = array_filter( $sanitized[ 'excluded_posts' ] );
		}

		return $sanitized;
	}

	/**
	 * AJAX handler for searching posts.
	 *
	 * @return void
	 */
	public function ajax_search_posts() {
		// Verify nonce.
		check_ajax_referer( 'passwp_posts_search', 'nonce' );

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'passwp-posts' ) ) );
		}

		// Get search term and pagination.
		$search   = isset( $_GET[ 'search' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ) : '';
		$page     = isset( $_GET[ 'page' ] ) ? absint( $_GET[ 'page' ] ) : 1;
		$per_page = 20;

		// Query posts and pages.
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			's'              => $search,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$query = new WP_Query( $args );

		$results = array();
		foreach ( $query->posts as $post ) {
			$results[] = array(
				'id'   => $post->ID,
				'text' => $post->post_title . ' (' . ucfirst( $post->post_type ) . ')',
			);
		}

		wp_send_json(
			array(
				'results' => $results,
				'more'    => $page < $query->max_num_pages,
			)
		);
	}
}
