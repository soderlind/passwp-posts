<?php
/**
 * Cookie Handler class for PassWP Posts.
 *
 * Handles authentication cookie operations.
 *
 * @package PassWP\Posts
 */

declare(strict_types=1);

namespace PassWP\Posts;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class CookieHandler
 *
 * Manages authentication cookies for password-protected access.
 */
final class CookieHandler {

	/**
	 * Cookie name prefix.
	 */
	private const COOKIE_PREFIX = 'passwp_posts_auth_';

	/**
	 * Get the full cookie name.
	 */
	public function get_cookie_name(): string {
		return self::COOKIE_PREFIX . COOKIEHASH;
	}

	/**
	 * Generate the cookie value (hashed token).
	 *
	 * @param string $password_hash The stored password hash.
	 */
	public function generate_cookie_value( string $password_hash ): string {
		return hash( 'sha256', $password_hash . wp_salt( 'auth' ) );
	}

	/**
	 * Set the authentication cookie.
	 *
	 * @param string $password_hash The stored password hash.
	 * @param int    $expiry_days   Number of days until expiry (0 for session cookie).
	 */
	public function set_cookie( string $password_hash, int $expiry_days = 30 ): void {
		$cookie_name  = $this->get_cookie_name();
		$cookie_value = $this->generate_cookie_value( $password_hash );

		// Get cookie path and domain from WordPress constants.
		$cookie_path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		$cookie_options = [
			'path'     => $cookie_path,
			'domain'   => $cookie_domain,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		];

		// Calculate expiry time. For session cookies, omit the expires option.
		if ( $expiry_days !== 0 ) {
			$cookie_options['expires'] = time() + ( $expiry_days * DAY_IN_SECONDS );
		}

		// Set the cookie.
		setcookie(
			name: $cookie_name,
			value: $cookie_value,
			expires_or_options: $cookie_options
		);

		// Also set in $_COOKIE for immediate use.
		$_COOKIE[ $cookie_name ] = $cookie_value;
	}

	/**
	 * Check if the authentication cookie is valid.
	 *
	 * @param string $password_hash The stored password hash.
	 */
	public function is_valid_cookie( string $password_hash ): bool {
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
	 */
	public function clear_cookie(): void {
		$cookie_name   = $this->get_cookie_name();
		$cookie_path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		// Set cookie to expire in the past.
		setcookie(
			name: $cookie_name,
			value: '',
			expires_or_options: [
				'expires'  => time() - YEAR_IN_SECONDS,
				'path'     => $cookie_path,
				'domain'   => $cookie_domain,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			]
		);

		// Remove from $_COOKIE.
		unset( $_COOKIE[ $cookie_name ] );
	}
}
