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
 * Class AdminSettings
 *
 * Creates and manages the plugin settings page.
 */
final class AdminSettings {

	/**
	 * Option name for plugin settings.
	 */
	private const OPTION_NAME = 'passwp_posts_settings';

	/**
	 * Settings page slug.
	 */
	private const PAGE_SLUG = 'passwp-posts-settings';

	/**
	 * Default customize settings.
	 *
	 * @var array<string, mixed>
	 */
	private const CUSTOMIZE_DEFAULTS = [
		'bg_color'             => '#667eea',
		'bg_gradient_end'      => '#764ba2',
		'bg_image'             => '',
		'card_bg_color'        => '#ffffff',
		'card_border_radius'   => 12,
		'card_shadow'          => true,
		'logo'                 => '',
		'logo_width'           => 120,
		'heading_text'         => '',
		'heading_color'        => '#1a1a2e',
		'text_color'           => '#4a5568',
		'font_family'          => 'system-ui, -apple-system, sans-serif',
		'button_text'          => '',
		'button_bg_color'      => '#667eea',
		'button_text_color'    => '#ffffff',
		'button_border_radius' => 8,
		'show_remember_me'     => true,
		'input_border_radius'  => 8,
		'footer_text'          => '',
		'footer_link'          => '',
	];

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
				'default'           => $this->get_default_settings(),
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

		// Protection mode field.
		add_settings_field(
			id: 'passwp_posts_protection_mode',
			title: __( 'Protection Mode', 'passwp-posts' ),
			callback: $this->render_protection_mode_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);

		// Excluded posts field (shown when mode is 'all').
		add_settings_field(
			id: 'passwp_posts_excluded',
			title: __( 'Excluded Pages/Posts', 'passwp-posts' ),
			callback: $this->render_excluded_posts_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);

		// Protected posts field (shown when mode is 'selected').
		add_settings_field(
			id: 'passwp_posts_protected',
			title: __( 'Protected Pages/Posts', 'passwp-posts' ),
			callback: $this->render_protected_posts_field( ... ),
			page: self::PAGE_SLUG,
			section: 'passwp_posts_main_section'
		);
	}

	/**
	 * Get default settings.
	 *
	 * @return array<string, mixed>
	 */
	private function get_default_settings(): array {
		return [
			'password_hash'      => '',
			'cookie_expiry_days' => 30,
			'protection_mode'    => 'all',
			'excluded_posts'     => [],
			'protected_posts'    => [],
			'enabled'            => false,
			'customize'          => self::CUSTOMIZE_DEFAULTS,
		];
	}

	/**
	 * Get customize settings with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_customize_settings(): array {
		$settings  = get_option( self::OPTION_NAME, [] );
		$customize = $settings[ 'customize' ] ?? [];

		// Backward-compat: prior versions used footer_link_url.
		if ( empty( $customize['footer_link'] ) && ! empty( $customize['footer_link_url'] ) ) {
			$customize['footer_link'] = $customize['footer_link_url'];
		}

		return array_merge( self::CUSTOMIZE_DEFAULTS, $customize );
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

		// Get current tab.
		$current_tab = isset( $_GET[ 'tab' ] ) ? sanitize_key( $_GET[ 'tab' ] ) : 'general';

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

		// Get current protection mode for initial visibility.
		$settings        = get_option( self::OPTION_NAME, [] );
		$protection_mode = $settings[ 'protection_mode' ] ?? 'all';

		// Localize script for AJAX.
		wp_localize_script(
			handle: 'passwp-posts-admin',
			object_name: 'passwpPostsAdmin',
			l10n: [
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'passwp_posts_search' ),
				'placeholder'    => __( 'Search for pages or posts...', 'passwp-posts' ),
				'showPassword'   => __( 'Show password', 'passwp-posts' ),
				'hidePassword'   => __( 'Hide password', 'passwp-posts' ),
				'protectionMode' => $protection_mode,
			]
		);

		// Customize tab assets.
		if ( 'customize' === $current_tab ) {
			// WordPress color picker.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			// WordPress media uploader.
			wp_enqueue_media();

			// Customize admin CSS.
			wp_enqueue_style(
				handle: 'passwp-posts-customize-admin',
				src: PASSWP_POSTS_URL . 'assets/css/customize-admin.css',
				deps: [ 'wp-color-picker' ],
				ver: $version
			);

			// Customize JS.
			wp_enqueue_script(
				handle: 'passwp-posts-customize',
				src: PASSWP_POSTS_URL . 'assets/js/customize.js',
				deps: [ 'jquery', 'wp-color-picker' ],
				ver: $version,
				args: true
			);

			// Localize customize script.
			wp_localize_script(
				handle: 'passwp-posts-customize',
				object_name: 'passwpCustomize',
				l10n: [
					'defaults'           => self::CUSTOMIZE_DEFAULTS,
					'resetConfirm'       => __( 'Are you sure you want to reset all customize settings to defaults?', 'passwp-posts' ),
					'selectImage'        => __( 'Select Image', 'passwp-posts' ),
					'useImage'           => __( 'Use this image', 'passwp-posts' ),
					'removeImage'        => __( 'Remove', 'passwp-posts' ),
					'previewPasswordUrl' => add_query_arg( 'passwp-preview', '1', home_url( '/' ) ),
				]
			);
		}
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_tab = isset( $_GET[ 'tab' ] ) ? sanitize_key( $_GET[ 'tab' ] ) : 'general';
		$page_url    = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( self::OPTION_NAME ); ?>

			<nav class="nav-tab-wrapper">
				<a href="<?php echo esc_url( $page_url ); ?>"
					class="nav-tab <?php echo 'general' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'General', 'passwp-posts' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'customize', $page_url ) ); ?>"
					class="nav-tab <?php echo 'customize' === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Customize', 'passwp-posts' ); ?>
				</a>
			</nav>

			<?php
			if ( 'customize' === $current_tab ) {
				$this->render_customize_tab();
			} else {
				$this->render_general_tab();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the general settings tab.
	 */
	private function render_general_tab(): void {
		?>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'passwp_posts_settings_group' );
			do_settings_sections( self::PAGE_SLUG );
			submit_button( __( 'Save Settings', 'passwp-posts' ) );
			?>
		</form>
		<?php
	}

	/**
	 * Render the customize settings tab.
	 */
	private function render_customize_tab(): void {
		$settings = self::get_customize_settings();
		?>
		<form action="options.php" method="post" id="passwp-customize-form">
			<?php settings_fields( 'passwp_posts_settings_group' ); ?>
			<input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[_customize_tab]" value="1" />

			<div class="passwp-customize-wrapper">
				<div class="passwp-customize-options">
					<!-- Preset Themes -->
					<div class="passwp-section">
						<h2><?php esc_html_e( 'Preset Themes', 'passwp-posts' ); ?></h2>
						<div class="passwp-presets">
							<button type="button" class="passwp-preset-card" data-preset="default">
								<span class="passwp-preset-preview"
									style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></span>
								<span class="passwp-preset-name"><?php esc_html_e( 'Default Purple', 'passwp-posts' ); ?></span>
							</button>
							<button type="button" class="passwp-preset-card" data-preset="business-blue">
								<span class="passwp-preset-preview"
									style="background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);"></span>
								<span class="passwp-preset-name"><?php esc_html_e( 'Business Blue', 'passwp-posts' ); ?></span>
							</button>
							<button type="button" class="passwp-preset-card" data-preset="dark-mode">
								<span class="passwp-preset-preview" style="background: #1a1a2e;"></span>
								<span class="passwp-preset-name"><?php esc_html_e( 'Dark Mode', 'passwp-posts' ); ?></span>
							</button>
						</div>
					</div>

					<div class="passwp-settings-grid">
						<!-- Background -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Background', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label for="passwp_bg_color"><?php esc_html_e( 'Background Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_bg_color"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][bg_color]"
									value="<?php echo esc_attr( $settings[ 'bg_color' ] ); ?>" class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'bg_color' ] ); ?>" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_bg_gradient_end"><?php esc_html_e( 'Gradient End Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_bg_gradient_end"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][bg_gradient_end]"
									value="<?php echo esc_attr( $settings[ 'bg_gradient_end' ] ); ?>"
									class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'bg_gradient_end' ] ); ?>" />
								<p class="description">
									<?php esc_html_e( 'Leave empty for solid color background.', 'passwp-posts' ); ?>
								</p>
							</div>

							<div class="passwp-form-row">
								<label for="passwp_bg_image"><?php esc_html_e( 'Background Image', 'passwp-posts' ); ?></label>
								<div class="passwp-media-upload" data-target="passwp_bg_image">
									<input type="hidden" id="passwp_bg_image"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][bg_image]"
										value="<?php echo esc_url( $settings[ 'bg_image' ] ); ?>" />
									<div class="passwp-media-preview">
										<?php if ( ! empty( $settings[ 'bg_image' ] ) ) : ?>
											<img src="<?php echo esc_url( $settings[ 'bg_image' ] ); ?>" alt="" />
										<?php endif; ?>
									</div>
									<button type="button"
										class="button passwp-media-select"><?php esc_html_e( 'Select Image', 'passwp-posts' ); ?></button>
									<button type="button" class="button passwp-media-remove" <?php echo empty( $settings[ 'bg_image' ] ) ? 'style="display:none;"' : ''; ?>><?php esc_html_e( 'Remove', 'passwp-posts' ); ?></button>
								</div>
							</div>
						</div>

						<!-- Card Styling -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Card Styling', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label
									for="passwp_card_bg_color"><?php esc_html_e( 'Card Background Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_card_bg_color"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][card_bg_color]"
									value="<?php echo esc_attr( $settings[ 'card_bg_color' ] ); ?>" class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'card_bg_color' ] ); ?>" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_card_border_radius"><?php esc_html_e( 'Card Border Radius', 'passwp-posts' ); ?></label>
								<div class="passwp-range-wrapper">
									<input type="range" id="passwp_card_border_radius"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][card_border_radius]"
										value="<?php echo esc_attr( $settings[ 'card_border_radius' ] ); ?>" min="0" max="50"
										step="1" />
									<span
										class="passwp-range-value"><?php echo esc_html( $settings[ 'card_border_radius' ] ); ?>px</span>
								</div>
							</div>

							<div class="passwp-form-row">
								<label for="passwp_card_shadow"><?php esc_html_e( 'Card Shadow', 'passwp-posts' ); ?></label>
								<label class="passwp-toggle">
									<input type="checkbox" id="passwp_card_shadow"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][card_shadow]" value="1"
										<?php checked( $settings[ 'card_shadow' ] ); ?> />
									<span class="passwp-toggle-slider"></span>
								</label>
							</div>
						</div>

						<!-- Logo -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Logo', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label for="passwp_logo"><?php esc_html_e( 'Logo Image', 'passwp-posts' ); ?></label>
								<div class="passwp-media-upload" data-target="passwp_logo">
									<input type="hidden" id="passwp_logo"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][logo]"
										value="<?php echo esc_url( $settings[ 'logo' ] ); ?>" />
									<div class="passwp-media-preview">
										<?php if ( ! empty( $settings[ 'logo' ] ) ) : ?>
											<img src="<?php echo esc_url( $settings[ 'logo' ] ); ?>" alt="" />
										<?php endif; ?>
									</div>
									<button type="button"
										class="button passwp-media-select"><?php esc_html_e( 'Select Image', 'passwp-posts' ); ?></button>
									<button type="button" class="button passwp-media-remove" <?php echo empty( $settings[ 'logo' ] ) ? 'style="display:none;"' : ''; ?>><?php esc_html_e( 'Remove', 'passwp-posts' ); ?></button>
								</div>
							</div>

							<div class="passwp-form-row">
								<label for="passwp_logo_width"><?php esc_html_e( 'Logo Width', 'passwp-posts' ); ?></label>
								<div class="passwp-range-wrapper">
									<input type="range" id="passwp_logo_width"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][logo_width]"
										value="<?php echo esc_attr( $settings[ 'logo_width' ] ); ?>" min="50" max="300"
										step="10" />
									<span
										class="passwp-range-value"><?php echo esc_html( $settings[ 'logo_width' ] ); ?>px</span>
								</div>
							</div>
						</div>

						<!-- Typography -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Typography', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label for="passwp_heading_text"><?php esc_html_e( 'Heading Text', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_heading_text"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][heading_text]"
									value="<?php echo esc_attr( $settings[ 'heading_text' ] ); ?>" class="regular-text" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_heading_color"><?php esc_html_e( 'Heading Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_heading_color"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][heading_color]"
									value="<?php echo esc_attr( $settings[ 'heading_color' ] ); ?>" class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'heading_color' ] ); ?>" />
							</div>

							<div class="passwp-form-row">
								<label for="passwp_text_color"><?php esc_html_e( 'Text Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_text_color"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][text_color]"
									value="<?php echo esc_attr( $settings[ 'text_color' ] ); ?>" class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'text_color' ] ); ?>" />
							</div>

							<div class="passwp-form-row">
								<label for="passwp_font_family"><?php esc_html_e( 'Font Family', 'passwp-posts' ); ?></label>
								<select id="passwp_font_family"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][font_family]">
									<option value="system-ui, -apple-system, sans-serif" <?php selected( $settings[ 'font_family' ], 'system-ui, -apple-system, sans-serif' ); ?>>
										<?php esc_html_e( 'System Default', 'passwp-posts' ); ?>
									</option>
									<option value="'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" <?php selected( $settings[ 'font_family' ], "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" ); ?>>
										<?php esc_html_e( 'Segoe UI', 'passwp-posts' ); ?>
									</option>
									<option value="Georgia, 'Times New Roman', serif" <?php selected( $settings[ 'font_family' ], "Georgia, 'Times New Roman', serif" ); ?>>
										<?php esc_html_e( 'Georgia', 'passwp-posts' ); ?>
									</option>
									<option value="'Courier New', Courier, monospace" <?php selected( $settings[ 'font_family' ], "'Courier New', Courier, monospace" ); ?>>
										<?php esc_html_e( 'Courier New', 'passwp-posts' ); ?>
									</option>
								</select>
							</div>
						</div>

						<!-- Button -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Button', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label for="passwp_button_text"><?php esc_html_e( 'Button Text', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_button_text"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][button_text]"
									value="<?php echo esc_attr( $settings[ 'button_text' ] ); ?>" class="regular-text" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_button_bg_color"><?php esc_html_e( 'Button Background Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_button_bg_color"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][button_bg_color]"
									value="<?php echo esc_attr( $settings[ 'button_bg_color' ] ); ?>"
									class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'button_bg_color' ] ); ?>" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_button_text_color"><?php esc_html_e( 'Button Text Color', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_button_text_color"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][button_text_color]"
									value="<?php echo esc_attr( $settings[ 'button_text_color' ] ); ?>"
									class="passwp-color-picker"
									data-default-color="<?php echo esc_attr( self::CUSTOMIZE_DEFAULTS[ 'button_text_color' ] ); ?>" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_button_border_radius"><?php esc_html_e( 'Button Border Radius', 'passwp-posts' ); ?></label>
								<div class="passwp-range-wrapper">
									<input type="range" id="passwp_button_border_radius"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][button_border_radius]"
										value="<?php echo esc_attr( $settings[ 'button_border_radius' ] ); ?>" min="0" max="30"
										step="1" />
									<span
										class="passwp-range-value"><?php echo esc_html( $settings[ 'button_border_radius' ] ); ?>px</span>
								</div>
							</div>
						</div>

						<!-- Form Options -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Form Options', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label
									for="passwp_show_remember_me"><?php esc_html_e( 'Show Remember Me', 'passwp-posts' ); ?></label>
								<label class="passwp-toggle">
									<input type="checkbox" id="passwp_show_remember_me"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][show_remember_me]"
										value="1" <?php checked( $settings[ 'show_remember_me' ] ); ?> />
									<span class="passwp-toggle-slider"></span>
								</label>
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_input_border_radius"><?php esc_html_e( 'Input Border Radius', 'passwp-posts' ); ?></label>
								<div class="passwp-range-wrapper">
									<input type="range" id="passwp_input_border_radius"
										name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][input_border_radius]"
										value="<?php echo esc_attr( $settings[ 'input_border_radius' ] ); ?>" min="0" max="20"
										step="1" />
									<span
										class="passwp-range-value"><?php echo esc_html( $settings[ 'input_border_radius' ] ); ?>px</span>
								</div>
							</div>
						</div>

						<!-- Footer -->
						<div class="passwp-section">
							<h2><?php esc_html_e( 'Footer', 'passwp-posts' ); ?></h2>

							<div class="passwp-form-row">
								<label for="passwp_footer_text"><?php esc_html_e( 'Footer Text', 'passwp-posts' ); ?></label>
								<input type="text" id="passwp_footer_text"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][footer_text]"
									value="<?php echo esc_attr( $settings[ 'footer_text' ] ); ?>" class="regular-text" />
							</div>

							<div class="passwp-form-row">
								<label
									for="passwp_footer_link"><?php esc_html_e( 'Footer Link URL', 'passwp-posts' ); ?></label>
								<input type="url" id="passwp_footer_link"
									name="<?php echo esc_attr( self::OPTION_NAME ); ?>[customize][footer_link]"
									value="<?php echo esc_url( $settings[ 'footer_link' ] ); ?>" class="regular-text" />
							</div>
						</div>
					</div><!-- .passwp-settings-grid -->

					<!-- Actions -->
					<div class="passwp-section passwp-actions">
						<?php submit_button( __( 'Save Settings', 'passwp-posts' ), 'primary', 'submit', false ); ?>
						<button type="button" id="passwp-reset-customize" class="button button-secondary">
							<?php esc_html_e( 'Reset to Defaults', 'passwp-posts' ); ?>
						</button>
					</div>
				</div>

				<div class="passwp-preview-wrapper">
					<h2><?php esc_html_e( 'Live Preview', 'passwp-posts' ); ?></h2>
					<div id="passwp-preview-container">
						<div class="passwp-preview-frame">
							<?php $this->render_preview_content( $settings ); ?>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Render the preview content for the customize tab.
	 *
	 * @param array<string, mixed> $settings The customize settings.
	 */
	private function render_preview_content( array $settings ): void {
		$bg_style = '';
		if ( ! empty( $settings[ 'bg_image' ] ) ) {
			$bg_style = sprintf( 'background-image: url(%s); background-size: cover; background-position: center;', esc_url( $settings[ 'bg_image' ] ) );
		} elseif ( ! empty( $settings[ 'bg_gradient_end' ] ) ) {
			$bg_style = sprintf( 'background: linear-gradient(135deg, %s 0%%, %s 100%%);', esc_attr( $settings[ 'bg_color' ] ), esc_attr( $settings[ 'bg_gradient_end' ] ) );
		} else {
			$bg_style = sprintf( 'background-color: %s;', esc_attr( $settings[ 'bg_color' ] ) );
		}

		$card_style = sprintf(
			'background-color: %s; border-radius: %dpx;%s',
			esc_attr( $settings[ 'card_bg_color' ] ),
			absint( $settings[ 'card_border_radius' ] ),
			$settings[ 'card_shadow' ] ? ' box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);' : ''
		);
		?>
		<div class="passwp-preview-bg"
			style="<?php echo esc_attr( $bg_style ); ?>; font-family: <?php echo esc_attr( $settings[ 'font_family' ] ); ?>;">
			<div class="passwp-preview-card" style="<?php echo esc_attr( $card_style ); ?>">
				<?php if ( ! empty( $settings[ 'logo' ] ) ) : ?>
					<img src="<?php echo esc_url( $settings[ 'logo' ] ); ?>" alt="" class="passwp-preview-logo"
						style="width: <?php echo absint( $settings[ 'logo_width' ] ); ?>px;" />
				<?php endif; ?>

				<h1 class="passwp-preview-heading" style="color: <?php echo esc_attr( $settings[ 'heading_color' ] ); ?>;">
					<?php echo esc_html( $settings[ 'heading_text' ] ?: __( 'Password Protected', 'passwp-posts' ) ); ?>
				</h1>

				<p class="passwp-preview-text" style="color: <?php echo esc_attr( $settings[ 'text_color' ] ); ?>;">
					<?php esc_html_e( 'Enter the password to access this content.', 'passwp-posts' ); ?>
				</p>

				<div class="passwp-preview-form">
					<input type="password" placeholder="<?php esc_attr_e( 'Password', 'passwp-posts' ); ?>"
						style="border-radius: <?php echo absint( $settings[ 'input_border_radius' ] ); ?>px;" readonly />

					<?php if ( $settings[ 'show_remember_me' ] ) : ?>
						<label class="passwp-preview-remember" style="color: <?php echo esc_attr( $settings[ 'text_color' ] ); ?>;">
							<input type="checkbox" disabled />
							<?php esc_html_e( 'Remember me', 'passwp-posts' ); ?>
						</label>
					<?php endif; ?>

					<button type="button"
						style="background-color: <?php echo esc_attr( $settings[ 'button_bg_color' ] ); ?>; color: <?php echo esc_attr( $settings[ 'button_text_color' ] ); ?>; border-radius: <?php echo absint( $settings[ 'button_border_radius' ] ); ?>px;">
						<?php echo esc_html( $settings[ 'button_text' ] ?: __( 'Submit', 'passwp-posts' ) ); ?>
					</button>
				</div>

				<p class="passwp-preview-footer" style="color: <?php echo esc_attr( $settings[ 'text_color' ] ); ?>;">
					<?php if ( ! empty( $settings[ 'footer_text' ] ) ) : ?>
						<?php if ( ! empty( $settings[ 'footer_link' ] ) ) : ?>
							<a href="<?php echo esc_url( $settings[ 'footer_link' ] ); ?>"
								style="color: <?php echo esc_attr( $settings[ 'button_bg_color' ] ); ?>;">
								<?php echo esc_html( $settings[ 'footer_text' ] ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $settings[ 'footer_text' ] ); ?>
						<?php endif; ?>
					<?php else : ?>
						<a href="#" style="color: <?php echo esc_attr( $settings[ 'button_bg_color' ] ); ?>;">
							&larr; <?php esc_html_e( 'Back to home', 'passwp-posts' ); ?>
						</a>
					<?php endif; ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render section description.
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__( 'Configure password protection for your site. The front page is always public. Logged-in users bypass the password.', 'passwp-posts' ) . '</p>';
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
			<?php esc_html_e( 'Enable password protection', 'passwp-posts' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'When enabled, visitors must enter a password to view protected content.', 'passwp-posts' ); ?>
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
			<button type="button" class="button passwp-toggle-password"
				aria-label="<?php esc_attr_e( 'Toggle password visibility', 'passwp-posts' ); ?>">
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
			<?php esc_html_e( 'How long visitors stay authenticated after entering the password.', 'passwp-posts' ); ?>
		</p>
		<?php
	}

	/**
	 * Render protection mode field.
	 */
	public function render_protection_mode_field(): void {
		$settings        = get_option( self::OPTION_NAME, [] );
		$protection_mode = $settings[ 'protection_mode' ] ?? 'all';
		?>
		<fieldset>
			<label>
				<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[protection_mode]" value="all" <?php checked( $protection_mode, 'all' ); ?> class="passwp-protection-mode" />
				<?php esc_html_e( 'Protect all pages and posts (except front page)', 'passwp-posts' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[protection_mode]" value="selected" <?php checked( $protection_mode, 'selected' ); ?> class="passwp-protection-mode" />
				<?php esc_html_e( 'Protect only selected pages and posts', 'passwp-posts' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Render excluded posts field with Select2 (shown when mode is 'all').
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
		<div id="passwp-excluded-posts-wrapper">
			<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[excluded_posts][]" id="passwp_posts_excluded"
				class="passwp-posts-select2" multiple="multiple" style="width: 100%; max-width: 400px;">
				<?php foreach ( $selected_posts as $post ) : ?>
					<option value="<?php echo esc_attr( (string) $post->ID ); ?>" selected>
						<?php echo esc_html( $post->post_title . ' (' . ucfirst( $post->post_type ) . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'These pages and posts will not require a password.', 'passwp-posts' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render protected posts field with Select2 (shown when mode is 'selected').
	 */
	public function render_protected_posts_field(): void {
		$settings        = get_option( self::OPTION_NAME, [] );
		$protected_posts = (array) ( $settings[ 'protected_posts' ] ?? [] );

		// Get the currently protected posts for display.
		$selected_posts = [];
		if ( $protected_posts !== [] ) {
			$selected_posts = get_posts( [
				'post_type'      => [ 'post', 'page' ],
				'post__in'       => array_map( 'intval', $protected_posts ),
				'posts_per_page' => -1,
				'orderby'        => 'post__in',
				'post_status'    => 'any',
			] );
		}
		?>
		<div id="passwp-protected-posts-wrapper">
			<select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[protected_posts][]" id="passwp_posts_protected"
				class="passwp-posts-select2" multiple="multiple" style="width: 100%; max-width: 400px;">
				<?php foreach ( $selected_posts as $post ) : ?>
					<option value="<?php echo esc_attr( (string) $post->ID ); ?>" selected>
						<?php echo esc_html( $post->post_title . ' (' . ucfirst( $post->post_type ) . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Only these pages and posts will require a password.', 'passwp-posts' ); ?>
			</p>
		</div>
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
		$expiry_days                       = (int) ( $input[ 'cookie_expiry_days' ] ?? 30 );
		$sanitized[ 'cookie_expiry_days' ] = max( 1, min( 365, $expiry_days ) );

		// Sanitize protection mode.
		$protection_mode                = $input[ 'protection_mode' ] ?? 'all';
		$sanitized[ 'protection_mode' ] = in_array( $protection_mode, [ 'all', 'selected' ], true ) ? $protection_mode : 'all';

		// Sanitize excluded posts.
		$sanitized[ 'excluded_posts' ] = [];
		if ( isset( $input[ 'excluded_posts' ] ) && is_array( $input[ 'excluded_posts' ] ) ) {
			$sanitized[ 'excluded_posts' ] = array_values(
				array_filter(
					array_map( 'absint', $input[ 'excluded_posts' ] )
				)
			);
		}

		// Sanitize protected posts.
		$sanitized[ 'protected_posts' ] = [];
		if ( isset( $input[ 'protected_posts' ] ) && is_array( $input[ 'protected_posts' ] ) ) {
			$sanitized[ 'protected_posts' ] = array_values(
				array_filter(
					array_map( 'absint', $input[ 'protected_posts' ] )
				)
			);
		}

		// Sanitize customize settings.
		if ( isset( $input[ '_customize_tab' ] ) || isset( $input[ 'customize' ] ) ) {
			$sanitized[ 'customize' ] = $this->sanitize_customize_settings( $input[ 'customize' ] ?? [] );

			// Preserve existing general settings when saving from customize tab.
			if ( isset( $input[ '_customize_tab' ] ) ) {
				$sanitized[ 'enabled' ]            = $existing[ 'enabled' ] ?? false;
				$sanitized[ 'password_hash' ]      = $existing[ 'password_hash' ] ?? '';
				$sanitized[ 'cookie_expiry_days' ] = $existing[ 'cookie_expiry_days' ] ?? 30;
				$sanitized[ 'protection_mode' ]    = $existing[ 'protection_mode' ] ?? 'all';
				$sanitized[ 'excluded_posts' ]     = $existing[ 'excluded_posts' ] ?? [];
				$sanitized[ 'protected_posts' ]    = $existing[ 'protected_posts' ] ?? [];
			}
		} elseif ( ! empty( $existing[ 'customize' ] ) ) {
			// Preserve existing customize settings when saving from general tab.
			$sanitized[ 'customize' ] = $existing[ 'customize' ];
		}

		return $sanitized;
	}

	/**
	 * Sanitize customize settings.
	 *
	 * @param array<string, mixed> $input Raw customize input.
	 * @return array<string, mixed> Sanitized customize settings.
	 */
	private function sanitize_customize_settings( array $input ): array {
		$sanitized = [];

		// Colors - sanitize as hex colors.
		$color_fields = [
			'bg_color',
			'bg_gradient_end',
			'card_bg_color',
			'heading_color',
			'text_color',
			'button_bg_color',
			'button_text_color',
		];

		foreach ( $color_fields as $field ) {
			$value               = $input[ $field ] ?? self::CUSTOMIZE_DEFAULTS[ $field ];
			$sanitized[ $field ] = $this->sanitize_hex_color( $value );
		}

		// URLs - sanitize as URLs.
		if ( empty( $input['footer_link'] ) && ! empty( $input['footer_link_url'] ) ) {
			$input['footer_link'] = $input['footer_link_url'];
		}

		$url_fields = [ 'bg_image', 'logo', 'footer_link' ];
		foreach ( $url_fields as $field ) {
			$sanitized[ $field ] = esc_url_raw( $input[ $field ] ?? '' );
		}

		// Integers - sanitize as integers with min/max.
		$sanitized[ 'card_border_radius' ]   = $this->sanitize_int_range( $input[ 'card_border_radius' ] ?? 16, 0, 50 );
		$sanitized[ 'button_border_radius' ] = $this->sanitize_int_range( $input[ 'button_border_radius' ] ?? 8, 0, 30 );
		$sanitized[ 'input_border_radius' ]  = $this->sanitize_int_range( $input[ 'input_border_radius' ] ?? 8, 0, 20 );
		$sanitized[ 'logo_width' ]           = $this->sanitize_int_range( $input[ 'logo_width' ] ?? 120, 50, 300 );

		// Booleans.
		$sanitized[ 'card_shadow' ]      = ! empty( $input[ 'card_shadow' ] );
		$sanitized[ 'show_remember_me' ] = ! empty( $input[ 'show_remember_me' ] );

		// Text fields.
		$sanitized[ 'heading_text' ] = sanitize_text_field( $input[ 'heading_text' ] ?? '' );
		$sanitized[ 'button_text' ]  = sanitize_text_field( $input[ 'button_text' ] ?? '' );
		$sanitized[ 'footer_text' ]  = sanitize_text_field( $input[ 'footer_text' ] ?? '' );

		// Font family - allow only safe values.
		$allowed_fonts              = [
			'system-ui, -apple-system, sans-serif',
			"'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
			"Georgia, 'Times New Roman', serif",
			"'Courier New', Courier, monospace",
		];
		$font_family                = $input[ 'font_family' ] ?? self::CUSTOMIZE_DEFAULTS[ 'font_family' ];
		$sanitized[ 'font_family' ] = in_array( $font_family, $allowed_fonts, true ) ? $font_family : self::CUSTOMIZE_DEFAULTS[ 'font_family' ];

		return $sanitized;
	}

	/**
	 * Sanitize hex color.
	 *
	 * @param string $color Color value.
	 * @return string Sanitized color or empty string.
	 */
	private function sanitize_hex_color( string $color ): string {
		if ( empty( $color ) ) {
			return '';
		}

		// Handle rgba format.
		if ( preg_match( '/^rgba?\([^)]+\)$/', $color ) ) {
			return $color;
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
			return $color;
		}

		return '';
	}

	/**
	 * Sanitize integer within a range.
	 *
	 * @param mixed $value Value to sanitize.
	 * @param int   $min   Minimum value.
	 * @param int   $max   Maximum value.
	 * @return int Sanitized integer.
	 */
	private function sanitize_int_range( mixed $value, int $min, int $max ): int {
		return max( $min, min( $max, absint( $value ) ) );
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
