<?php
/**
 * Plugin Name: PassWP Posts
 * Plugin URI: https://developer.suspended.no/passwp-posts
 * Description: Password protects all pages and posts except the front page. Logged-in users bypass the password.
 * Version: 1.0.0
 * Author: Per Soderlind
 * Author URI: https://soderlind.no
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: passwp-posts
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package PassWP_Posts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'PASSWP_POSTS_VERSION', '1.0.0' );
define( 'PASSWP_POSTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PASSWP_POSTS_URL', plugin_dir_url( __FILE__ ) );
define( 'PASSWP_POSTS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload plugin classes.
 */
spl_autoload_register(
	function ( $class ) {
		$prefix   = 'PassWP_Posts_';
		$base_dir = PASSWP_POSTS_PATH . 'includes/';

		// Check if class uses our prefix.
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		// Get relative class name and convert to file path.
		$relative_class = substr( $class, $len );
		$file           = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

/**
 * Initialize the plugin.
 *
 * @return void
 */
function passwp_posts_init() {
	// Load text domain.
	load_plugin_textdomain( 'passwp-posts', false, dirname( PASSWP_POSTS_BASENAME ) . '/languages' );

	// Initialize components.
	new PassWP_Posts_Admin_Settings();
	new PassWP_Posts_Protection();
}
add_action( 'plugins_loaded', 'passwp_posts_init' );

/**
 * Plugin activation hook.
 *
 * @return void
 */
function passwp_posts_activate() {
	// Set default options if not exists.
	if ( false === get_option( 'passwp_posts_settings' ) ) {
		add_option(
			'passwp_posts_settings',
			array(
				'password_hash'      => '',
				'cookie_expiry_days' => 30,
				'excluded_posts'     => array(),
				'enabled'            => false,
			)
		);
	}
}
register_activation_hook( __FILE__, 'passwp_posts_activate' );

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function passwp_posts_deactivate() {
	// Clear any cookies by setting them to expire.
	$cookie_handler = new PassWP_Posts_Cookie_Handler();
	$cookie_handler->clear_cookie();
}
register_deactivation_hook( __FILE__, 'passwp_posts_deactivate' );
