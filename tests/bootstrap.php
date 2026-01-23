<?php
/**
 * PHPUnit bootstrap file.
 *
 * Sets up WordPress function mocks using Brain\Monkey.
 *
 * @package PassWP_Posts\Tests
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

use Brain\Monkey;

/**
 * WordPress constants.
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'COOKIEHASH' ) ) {
	define( 'COOKIEHASH', 'test_cookie_hash_123' );
}

if ( ! defined( 'COOKIE_DOMAIN' ) ) {
	define( 'COOKIE_DOMAIN', 'example.com' );
}

if ( ! defined( 'COOKIEPATH' ) ) {
	define( 'COOKIEPATH', '/' );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'YEAR_IN_SECONDS' ) ) {
	define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
}

if ( ! defined( 'PASSWP_POSTS_VERSION' ) ) {
	define( 'PASSWP_POSTS_VERSION', '1.0.0' );
}

if ( ! defined( 'PASSWP_POSTS_PATH' ) ) {
	define( 'PASSWP_POSTS_PATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'PASSWP_POSTS_URL' ) ) {
	define( 'PASSWP_POSTS_URL', 'https://example.com/wp-content/plugins/passwp-posts/' );
}

/**
 * Minimal WordPress redirect validator for tests.
 *
 * Brain\Monkey does not define this function by default, but the plugin conditionally
 * calls it when present. Defining a minimal compatible implementation here allows
 * us to test redirect hardening logic.
 */
if ( ! function_exists( 'wp_validate_redirect' ) ) {
	function wp_validate_redirect( $location, $default = '' ) {
		$location = is_string( $location ) ? trim( $location ) : '';
		$default  = is_string( $default ) ? $default : '';

		if ( $location === '' ) {
			return $default;
		}

		$allowed_host = parse_url( home_url( '/' ), PHP_URL_HOST );
		$host         = parse_url( $location, PHP_URL_HOST );
		$scheme       = parse_url( $location, PHP_URL_SCHEME );

		// Allow relative URLs.
		if ( $host === null ) {
			return $location;
		}

		// Only allow http(s) to the site's host.
		if ( $host !== $allowed_host ) {
			return $default;
		}
		if ( $scheme !== null && ! in_array( strtolower( (string) $scheme ), array( 'http', 'https' ), true ) ) {
			return $default;
		}

		return $location;
	}
}

/**
 * Base test case class with Brain\Monkey setup.
 */
abstract class PassWP_Posts_TestCase extends \PHPUnit\Framework\TestCase {

	/**
	 * Set up Brain\Monkey before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Common WordPress function stubs.
		$this->setup_common_stubs();
	}

	/**
	 * Tear down Brain\Monkey after each test.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Setup common WordPress function stubs.
	 */
	protected function setup_common_stubs(): void {
		// Escaping functions.
		Monkey\Functions\stubs(
			array(
				'esc_html'            => function ( $text ) {
					return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},
				'esc_attr'            => function ( $text ) {
					return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},
				'esc_url'             => function ( $url ) {
					return filter_var( $url, FILTER_SANITIZE_URL );
				},
				'esc_url_raw'         => function ( $url ) {
					return filter_var( $url, FILTER_SANITIZE_URL );
				},
				'esc_html__'          => function ( $text, $domain = 'default' ) {
					return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},
				'esc_html_e'          => function ( $text, $domain = 'default' ) {
					echo htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},
				'__'                  => function ( $text, $domain = 'default' ) {
					return $text;
				},
				'_e'                  => function ( $text, $domain = 'default' ) {
					echo $text;
				},
				'esc_attr__'          => function ( $text, $domain = 'default' ) {
					return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
				},

				// Sanitization functions.
				'sanitize_text_field' => function ( $str ) {
					return trim( strip_tags( $str ) );
				},
				'sanitize_key'        => function ( $key ) {
					return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
				},
				'absint'              => function ( $value ) {
					return abs( (int) $value );
				},
				'wp_unslash'          => function ( $value ) {
					return is_array( $value ) ? array_map( 'stripslashes_deep', $value ) : stripslashes( $value );
				},

				// URL functions.
				'home_url'            => function ( $path = '' ) {
					return 'https://example.com' . $path;
				},
				'admin_url'           => function ( $path = '' ) {
					return 'https://example.com/wp-admin/' . $path;
				},
				'plugin_dir_url'      => function ( $file ) {
					return PASSWP_POSTS_URL;
				},
				'plugin_dir_path'     => function ( $file ) {
					return PASSWP_POSTS_PATH;
				},

				// Option functions (default stubs).
				'get_option'          => function ( $option, $default = false ) {
					return $default;
				},
				'update_option'       => '__return_true',
				'add_option'          => '__return_true',
				'delete_option'       => '__return_true',

				// Hook functions.
				'add_action'          => '__return_true',
				'add_filter'          => '__return_true',
				'do_action'           => '__return_null',
				'apply_filters'       => function ( $tag, $value ) {
					return $value;
				},

				// Conditional functions.
				'is_admin'            => '__return_false',
				'is_ssl'              => '__return_true',

				// Misc functions.
				'wp_die'              => function ( $message = '', $title = '', $args = array () ) {
					throw new \Exception( $message );
				},
				'current_user_can'    => '__return_true',

				// Asset functions.
				'wp_enqueue_style'    => '__return_null',
				'wp_enqueue_script'   => '__return_null',

				// Header functions.
				'nocache_headers'     => '__return_null',
			)
		);
	}
}

// Load plugin classes via PSR-4 autoloader.
require_once PASSWP_POSTS_PATH . 'includes/CookieHandler.php';
require_once PASSWP_POSTS_PATH . 'includes/Protection.php';
require_once PASSWP_POSTS_PATH . 'includes/AdminSettings.php';

// Import namespaced classes for tests.
use PassWP\Posts\AdminSettings;
use PassWP\Posts\CookieHandler;
use PassWP\Posts\Protection;
