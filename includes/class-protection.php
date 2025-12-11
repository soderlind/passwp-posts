<?php
/**
 * Protection class for PassWP Posts.
 *
 * Handles password protection logic for pages and posts.
 *
 * @package PassWP_Posts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class PassWP_Posts_Protection
 *
 * Intercepts page requests and shows password form when needed.
 */
class PassWP_Posts_Protection {

	/**
	 * Cookie handler instance.
	 *
	 * @var PassWP_Posts_Cookie_Handler
	 */
	private $cookie_handler;

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->cookie_handler = new PassWP_Posts_Cookie_Handler();
		$this->settings       = get_option( 'passwp_posts_settings', array() );

		// Hook into template redirect to check protection.
		add_action( 'template_redirect', array( $this, 'check_protection' ) );

		// Handle password form submission.
		add_action( 'admin_post_nopriv_passwp_posts_auth', array( $this, 'handle_form_submission' ) );
		add_action( 'admin_post_passwp_posts_auth', array( $this, 'handle_form_submission' ) );

		// Enqueue frontend styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Check if current page should be protected.
	 *
	 * @return void
	 */
	public function check_protection() {
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

		// Allow excluded posts/pages.
		$current_post_id = get_queried_object_id();
		$excluded_posts  = isset( $this->settings[ 'excluded_posts' ] ) ? (array) $this->settings[ 'excluded_posts' ] : array();

		if ( in_array( $current_post_id, array_map( 'intval', $excluded_posts ), true ) ) {
			return;
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
	 *
	 * @return bool
	 */
	private function is_login_page() {
		return in_array(
			$GLOBALS[ 'pagenow' ],
			array( 'wp-login.php', 'wp-register.php' ),
			true
		);
	}

	/**
	 * Display the password form.
	 *
	 * @return void
	 */
	private function show_password_form() {
		// Get error message from query string.
		$error = isset( $_GET[ 'passwp_error' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'passwp_error' ] ) ) : '';

		// Get current URL for redirect after authentication.
		$redirect_url = $this->get_current_url();

		// Load the template.
		include PASSWP_POSTS_PATH . 'templates/password-form.php';
		exit;
	}

	/**
	 * Get the current URL.
	 *
	 * @return string
	 */
	private function get_current_url() {
		$protocol = is_ssl() ? 'https://' : 'http://';
		return $protocol . sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_HOST' ] ?? '' ) ) . sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ?? '' ) );
	}

	/**
	 * Handle password form submission.
	 *
	 * @return void
	 */
	public function handle_form_submission() {
		// Verify nonce.
		if ( ! isset( $_POST[ 'passwp_posts_nonce' ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'passwp_posts_nonce' ] ) ), 'passwp_posts_auth' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'passwp-posts' ), '', array( 'response' => 403 ) );
		}

		// Get submitted password.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password should not be sanitized before verification.
		$password = isset( $_POST[ 'passwp_password' ] ) ? wp_unslash( $_POST[ 'passwp_password' ] ) : '';

		// Get redirect URL.
		$redirect_url = isset( $_POST[ 'passwp_redirect' ] ) ? esc_url_raw( wp_unslash( $_POST[ 'passwp_redirect' ] ) ) : home_url();

		// Get remember me checkbox.
		$remember = isset( $_POST[ 'passwp_remember' ] ) && '1' === $_POST[ 'passwp_remember' ];

		// Verify password.
		$settings = get_option( 'passwp_posts_settings', array() );

		if ( empty( $settings[ 'password_hash' ] ) ) {
			wp_safe_redirect( add_query_arg( 'passwp_error', 'no_password', $redirect_url ) );
			exit;
		}

		if ( ! wp_check_password( $password, $settings[ 'password_hash' ] ) ) {
			wp_safe_redirect( add_query_arg( 'passwp_error', 'invalid', $redirect_url ) );
			exit;
		}

		// Set authentication cookie.
		$expiry_days = isset( $settings[ 'cookie_expiry_days' ] ) ? absint( $settings[ 'cookie_expiry_days' ] ) : 30;

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
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		// Only enqueue if needed (will be loaded in template anyway).
		wp_register_style(
			'passwp-posts-form',
			PASSWP_POSTS_URL . 'assets/css/password-form.css',
			array(),
			PASSWP_POSTS_VERSION
		);
	}
}
