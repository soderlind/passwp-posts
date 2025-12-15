<?php
/**
 * Protection class for PassWP Posts.
 *
 * Handles password protection logic for pages and posts.
 *
 * @package PassWP\Posts
 */

declare(strict_types=1);

namespace PassWP\Posts;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class Protection
 *
 * Intercepts page requests and shows password form when needed.
 */
final class Protection {

	/**
	 * Plugin settings.
	 *
	 * @var array<string, mixed>
	 */
	private readonly array $settings;

	/**
	 * Constructor with property promotion.
	 */
	public function __construct(
		private readonly CookieHandler $cookie_handler = new CookieHandler(),
	) {
		$this->settings = get_option( 'passwp_posts_settings', [] );

		// Hook into template redirect to check protection.
		add_action( 'template_redirect', $this->check_protection( ... ) );

		// Handle password form submission.
		add_action( 'admin_post_nopriv_passwp_posts_auth', $this->handle_form_submission( ... ) );
		add_action( 'admin_post_passwp_posts_auth', $this->handle_form_submission( ... ) );

		// Enqueue frontend styles.
		add_action( 'wp_enqueue_scripts', $this->enqueue_styles( ... ) );
	}

	/**
	 * Check if current page should be protected.
	 */
	public function check_protection(): void {
		// Check if protection is enabled.
		if ( empty( $this->settings[ 'enabled' ] ) ) {
			return;
		}

		// Check if password is set.
		if ( empty( $this->settings[ 'password_hash' ] ) ) {
			return;
		}

		// Allow front page.
		if ( is_front_page() ) {
			return;
		}

		// Allow logged-in users.
		if ( is_user_logged_in() ) {
			return;
		}

		// Allow admin, login, and registration pages.
		if ( is_admin() || $this->is_login_page() ) {
			return;
		}

		// Get current post ID.
		$current_post_id = get_queried_object_id();

		// Handle protection based on mode.
		$protection_mode = $this->settings[ 'protection_mode' ] ?? 'all';

		if ( $protection_mode === 'selected' ) {
			// Only protect selected posts.
			$protected_posts = (array) ( $this->settings[ 'protected_posts' ] ?? [] );
			if ( ! in_array( $current_post_id, array_map( 'intval', $protected_posts ), true ) ) {
				return;
			}
		} else {
			// Protect all, but allow excluded posts.
			$excluded_posts = (array) ( $this->settings[ 'excluded_posts' ] ?? [] );
			if ( in_array( $current_post_id, array_map( 'intval', $excluded_posts ), true ) ) {
				return;
			}
		}

		// Check for valid authentication cookie.
		if ( $this->cookie_handler->is_valid_cookie( $this->settings[ 'password_hash' ] ) ) {
			return;
		}

		// Show password form.
		$this->show_password_form();
	}

	/**
	 * Check if current page is the login page.
	 */
	private function is_login_page(): bool {
		return in_array(
			$GLOBALS[ 'pagenow' ] ?? '',
			[ 'wp-login.php', 'wp-register.php' ],
			true
		);
	}

	/**
	 * Display the password form.
	 */
	private function show_password_form(): never {
		// Get error message from query string.
		$error = isset( $_GET[ 'passwp_error' ] )
			? sanitize_text_field( wp_unslash( $_GET[ 'passwp_error' ] ) )
			: '';

		// Get current URL for redirect after authentication.
		$redirect_url = $this->get_current_url();

		// Load the template.
		include PASSWP_POSTS_PATH . 'templates/password-form.php';
		exit;
	}

	/**
	 * Get the current URL.
	 */
	private function get_current_url(): string {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host     = sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_HOST' ] ?? '' ) );
		$uri      = sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ?? '' ) );

		return $protocol . $host . $uri;
	}

	/**
	 * Handle password form submission.
	 */
	public function handle_form_submission(): never {
		// Verify nonce.
		$nonce = isset( $_POST[ 'passwp_posts_nonce' ] )
			? sanitize_text_field( wp_unslash( $_POST[ 'passwp_posts_nonce' ] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'passwp_posts_auth' ) ) {
			wp_die(
				esc_html__( 'Security check failed.', 'passwp-posts' ),
				'',
				[ 'response' => 403 ]
			);
		}

		// Get submitted password.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password should not be sanitized before verification.
		$password = isset( $_POST[ 'passwp_password' ] )
			? wp_unslash( $_POST[ 'passwp_password' ] )
			: '';

		// Get redirect URL.
		$redirect_url = isset( $_POST[ 'passwp_redirect' ] )
			? esc_url_raw( wp_unslash( $_POST[ 'passwp_redirect' ] ) )
			: home_url();

		// Get remember me checkbox.
		$remember = ( $_POST[ 'passwp_remember' ] ?? '' ) === '1';

		// Verify password.
		$settings = get_option( 'passwp_posts_settings', [] );

		if ( empty( $settings[ 'password_hash' ] ) ) {
			wp_safe_redirect( add_query_arg( 'passwp_error', 'no_password', $redirect_url ) );
			exit;
		}

		if ( ! wp_check_password( $password, $settings[ 'password_hash' ] ) ) {
			wp_safe_redirect( add_query_arg( 'passwp_error', 'invalid', $redirect_url ) );
			exit;
		}

		// Set authentication cookie.
		$expiry_days = (int) ( $settings[ 'cookie_expiry_days' ] ?? 30 );

		if ( $remember ) {
			$this->cookie_handler->set_cookie( $settings[ 'password_hash' ], $expiry_days );
		} else {
			// Session cookie (expires when browser closes).
			$this->cookie_handler->set_cookie( $settings[ 'password_hash' ], 0 );
		}

		// Redirect back to original page.
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Enqueue frontend styles for password form.
	 */
	public function enqueue_styles(): void {
		// Only enqueue if needed (will be loaded in template anyway).
		wp_register_style(
			'passwp-posts-form',
			PASSWP_POSTS_URL . 'assets/css/password-form.css',
			[],
			PASSWP_POSTS_VERSION
		);
	}
}
