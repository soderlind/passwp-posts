<?php
/**
 * Admin Settings class for PassWP Posts.
 *
 * Handles the plugin settings page using WordPress Settings API.
 *
 * @package PassWP\Posts
 */

declare(strict_types=1);

namespace PassWP\Posts;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class Admin_Settings
 *
 * Creates and manages the plugin settings page.
 */
final class Admin_Settings {

	/**
	 * Option name for plugin settings.
	 */
	private const OPTION_NAME = 'passwp_posts_settings';

	/**
	 * Settings page slug.
	 */
	private const PAGE_SLUG = 'passwp-posts-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', $this->add_settings_page( ... ) );
		add_action( 'admin_init', $this->register_settings( ... ) );
		add_action( 'admin_enqueue_scripts', $this->enqueue_admin_assets( ... ) );

		// AJAX handler for post search.
		add_action( 'wp_ajax_passwp_posts_search', $this->ajax_search_posts( ... ) );
	}

	/**
	 * Add settings page under Settings menu.
	 */
	public function add_settings_page(): void {
		add_options_page(
			page_title: __( 'PassWP Posts Settings', 'passwp-posts' ),
			menu_title: __( 'PassWP Posts', 'passwp-posts' ),
			capability: 'manage_options',
			menu_slug: self::PAGE_SLUG,
			callback: $this->render_settings_page( ... )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings(): void {
		register_setting(
			option_group: 'passwp_posts_settings_group',
			option_name: self::OPTION_NAME,
			args: [
				'type'              => 'array',
				'sanitize_callback' => $this->sanitize_settings( ... ),
				'default'           => [
					'password_hash'      => '',
					'cookie_expiry_days' => 30,
					'excluded_posts'     => [],
					'enabled'            => false,
				],
			]
		);

		// Main settings section.
		add_settings_section(
			id: 'passwp_posts_main_section',
			title: __( 'Password Protection Settings', 'passwp-posts' ),
			callback: $this->render_section_description( ... ),
			page: self::PAGE_SLUG
		);

		// Enable protection field.
		add_settings_field(
			id: 'passwp_posts_enabled',
			title: __( 'Enable Protection', 'passwp-posts' ),
			callback: $this->render_enabled_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);

		// Password field.
		add_settings_field(
			id: 'passwp_posts_password',
			title: __( 'Password', 'passwp-posts' ),
			callback: $this->render_password_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);

		// Cookie expiry field.
		add_settings_field(
			id: 'passwp_posts_cookie_expiry',
			title: __( 'Remember Me Duration', 'passwp-posts' ),
			callback: $this->render_cookie_expiry_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);

		// Excluded posts field.
		add_settings_field(
			id: 'passwp_posts_excluded',
			title: __( 'Excluded Pages/Posts', 'passwp-posts' ),
			callback: $this->render_excluded_posts_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Only load on our settings page.
		if ( $hook !== 'settings_page_' . self::PAGE_SLUG ) {
			return;
		}

		// Use time() for cache busting in debug mode.
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? (string) time() : PASSWP_POSTS_VERSION;

		// Select2 CSS.
		wp_enqueue_style(
			handle: 'select2',
			src: PASSWP_POSTS_URL . 'assets/vendor/select2/select2.min.css',
			deps: [],
			ver: '4.1.0'
		);

		// Select2 JS.
		wp_enqueue_script(
			handle: 'select2',
			src: PASSWP_POSTS_URL . 'assets/vendor/select2/select2.min.js',
			deps: [ 'jquery' ],
			ver: '4.1.0',
			args: true
		);

		// Admin CSS.
		wp_enqueue_style(
			handle: 'passwp-posts-admin',
			src: PASSWP_POSTS_URL . 'assets/css/admin.css',
			deps: [ 'select2' ],
			ver: $version
		);

		// Admin JS.
		wp_enqueue_script(
			handle: 'passwp-posts-admin',
			src: PASSWP_POSTS_URL . 'assets/js/admin.js',
			deps: [ 'jquery', 'select2' ],
			ver: $version,
			args: true
		);

		// Localize script for AJAX.
		wp_localize_script(
			handle: 'passwp-posts-admin',
			object_name: 'passwpPostsAdmin',
			l10n: [
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'passwp_posts_search' ),
				'placeholder'  => __( 'Search for pages or posts...', 'passwp-posts' ),
				'showPassword' => __( 'Show password', 'passwp-posts' ),
				'hidePassword' => __( 'Hide password', 'passwp-posts' ),
			]
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( self::OPTION_NAME ); ?>

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
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__( 'Configure password protection for your site. The front page and logged-in users are always allowed.', 'passwp-posts' ) . '</p>';
	}

	/**
	 * Render enabled checkbox field.
	 */
	public function render_enabled_field(): void {
		$settings = get_option( self::OPTION_NAME, [] );
		$enabled  = (bool) ( $settings[ 'enabled' ] ?? false );
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
	 */
	public function render_password_field(): void {
		$settings     = get_option( self::OPTION_NAME, [] );
		$has_password = ! empty( $settings[ 'password_hash' ] );
		?>
		<div class="passwp-password-wrapper">
			<input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[password]" id="passwp_posts_password"
				class="regular-text"
				placeholder="<?php echo $has_password ? esc_attr__( 'Leave blank to keep current password', 'passwp-posts' ) : esc_attr__( 'Enter password', 'passwp-posts' ); ?>"
				autocomplete="new-password" />
			<button type="button" class="button passwp-toggle-password" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'passwp-posts' ); ?>">
				<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
			</button>
		</div>
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
	 */
	public function render_cookie_expiry_field(): void {
		$settings    = get_option( self::OPTION_NAME, [] );
		$expiry_days = (int) ( $settings[ 'cookie_expiry_days' ] ?? 30 );
		?>
		<input type="number" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[cookie_expiry_days]"
			id="passwp_posts_cookie_expiry" class="small-text" value="<?php echo esc_attr( (string) $expiry_days ); ?>" min="1"
			max="365" />
		<span><?php esc_html_e( 'days', 'passwp-posts' ); ?></span>
		<p class="description">
			<?php esc_html_e( 'How long the "Remember Me" authentication lasts. Default is 30 days.', 'passwp-posts' ); ?>
		</p>
		<?php
	}

	/**
	 * Render excluded posts field with Select2.
	 */
	public function render_excluded_posts_field(): void {
		$settings       = get_option( self::OPTION_NAME, [] );
		$excluded_posts = (array) ( $settings[ 'excluded_posts' ] ?? [] );

		// Get the currently excluded posts for display.
		$selected_posts = [];
		if ( $excluded_posts !== [] ) {
			$selected_posts = get_posts( [
				'post_type'      => [ 'post', 'page' ],
				'post__in'       => array_map( 'intval', $excluded_posts ),
				'posts_per_page' => -1,
				'orderby'        => 'post__in',
				'post_status'    => 'any',
			] );
		}
		?>
		<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[excluded_posts][]" id="passwp_posts_excluded"
			class="passwp-posts-select2" multiple="multiple" style="width: 100%; max-width: 400px;">
			<?php foreach ( $selected_posts as $post ) : ?>
				<option value="<?php echo esc_attr( (string) $post->ID ); ?>" selected>
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
	 * @param array<string, mixed>|null $input Raw input from form.
	 * @return array<string, mixed> Sanitized settings.
	 */
	public function sanitize_settings( ?array $input ): array {
		$input     = $input ?? [];
		$sanitized = [];

		// Get existing settings to preserve password if not changed.
		$existing = get_option( self::OPTION_NAME, [] );

		// Sanitize enabled.
		$sanitized[ 'enabled' ] = ( $input[ 'enabled' ] ?? '' ) === '1';

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
		$expiry_days                     = (int) ( $input[ 'cookie_expiry_days' ] ?? 30 );
		$sanitized[ 'cookie_expiry_days' ] = max( 1, min( 365, $expiry_days ) );

		// Sanitize excluded posts.
		$sanitized[ 'excluded_posts' ] = [];
		if ( isset( $input[ 'excluded_posts' ] ) && is_array( $input[ 'excluded_posts' ] ) ) {
			$sanitized[ 'excluded_posts' ] = array_values(
				array_filter(
					array_map( 'absint', $input[ 'excluded_posts' ] )
				)
			);
		}

		return $sanitized;
	}

	/**
	 * AJAX handler for searching posts.
	 */
	public function ajax_search_posts(): never {
		// Verify nonce.
		check_ajax_referer( 'passwp_posts_search', 'nonce' );

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'passwp-posts' ) ] );
		}

		// Get search term and pagination.
		$search   = isset( $_GET[ 'search' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ) : '';
		$page     = isset( $_GET[ 'page' ] ) ? absint( $_GET[ 'page' ] ) : 1;
		$per_page = 20;

		// Query posts and pages.
		$query = new \WP_Query( [
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'publish',
			's'              => $search,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		$results = array_map(
			static fn( \WP_Post $post ): array => [
				'id'   => $post->ID,
				'text' => $post->post_title . ' (' . ucfirst( $post->post_type ) . ')',
			],
			$query->posts
		);

		wp_send_json( [
			'results' => $results,
			'more'    => $page < $query->max_num_pages,
		] );
	}
}
