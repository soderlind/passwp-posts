<?php
/**
 * Cookie Handler class for PassWP Posts.
 *
 * Handles authentication cookie operations.
 *
 * @package PassWP_Posts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class PassWP_Posts_Cookie_Handler
 *
 * Manages authentication cookies for password-protected access.
 */
class PassWP_Posts_Cookie_Handler {

	/**
	 * Cookie name prefix.
	 *
	 * @var string
	 */
	const COOKIE_PREFIX = 'passwp_posts_auth_';

	/**
	 * Get the full cookie name.
	 *
	 * @return string
	 */
	public function get_cookie_name() {
		return self::COOKIE_PREFIX . COOKIEHASH;
	}

	/**
	 * Generate the cookie value (hashed token).
	 *
	 * @param string $password_hash The stored password hash.
	 * @return string
	 */
	public function generate_cookie_value( $password_hash ) {
		return hash( 'sha256', $password_hash . wp_salt( 'auth' ) );
	}

	/**
	 * Set the authentication cookie.
	 *
	 * @param string $password_hash The stored password hash.
	 * @param int    $expiry_days   Number of days until expiry (0 for session cookie).
	 * @return void
	 */
	public function set_cookie( $password_hash, $expiry_days = 30 ) {
		$cookie_name  = $this->get_cookie_name();
		$cookie_value = $this->generate_cookie_value( $password_hash );

		// Calculate expiry time.
		$expire = 0 === $expiry_days ? 0 : time() + ( $expiry_days * DAY_IN_SECONDS );

		// Get cookie path and domain from WordPress constants.
		$cookie_path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		// Set the cookie.
		setcookie(
			$cookie_name,
			$cookie_value,
			array(
				'expires'  => $expire,
				'path'     => $cookie_path,
				'domain'   => $cookie_domain,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		// Also set in $_COOKIE for immediate use.
		$_COOKIE[ $cookie_name ] = $cookie_value;
	}

	/**
	 * Check if the authentication cookie is valid.
	 *
	 * @param string $password_hash The stored password hash.
	 * @return bool
	 */
	public function is_valid_cookie( $password_hash ) {
		$cookie_name = $this->get_cookie_name();

		// Check if cookie exists.
		if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
			return false;
		}

		// Get cookie value.
		$cookie_value = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );

		// Generate expected value.
		$expected_value = $this->generate_cookie_value( $password_hash );

		// Use hash_equals for timing-safe comparison.
		return hash_equals( $expected_value, $cookie_value );
	}

	/**
	 * Clear the authentication cookie.
	 *
	 * @return void
	 */
	public function clear_cookie() {
		$cookie_name   = $this->get_cookie_name();
		$cookie_path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		// Set cookie to expire in the past.
		setcookie(
			$cookie_name,
			'',
			array(
				'expires'  => time() - YEAR_IN_SECONDS,
				'path'     => $cookie_path,
				'domain'   => $cookie_domain,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);

		// Remove from $_COOKIE.
		unset( $_COOKIE[ $cookie_name ] );
	}
}
