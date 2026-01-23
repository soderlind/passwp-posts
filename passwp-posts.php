<?php
/**
 * Plugin Name: PassWP Posts
 * Plugin URI: https://developer.suspended.no/passwp-posts
 * Description: Password protects all pages and posts except the front page. Logged-in users bypass the password.
 * Version: 1.4.1
 * Author: Per Soderlind
 * Author URI: https://soderlind.no
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: passwp-posts
 * Domain Path: /languages
 * Requires at least: 6.8
 * Requires PHP: 8.3
 *
 * @package PassWP\Posts
 */

declare(strict_types=1);

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'PASSWP_POSTS_VERSION', '1.4.1' );
define( 'PASSWP_POSTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PASSWP_POSTS_URL', plugin_dir_url( __FILE__ ) );
define( 'PASSWP_POSTS_BASENAME', plugin_basename( __FILE__ ) );

// Composer autoloader.
if ( file_exists( PASSWP_POSTS_PATH . 'vendor/autoload.php' ) ) {
	require_once PASSWP_POSTS_PATH . 'vendor/autoload.php';
}

use PassWP\Posts\AdminSettings;
use PassWP\Posts\CookieHandler;
use PassWP\Posts\Protection;
use PassWP\Posts\GitHubPluginUpdater;
use PassWP\Posts\Shortcodes;

/**
 * Initialize the plugin.
 */
function passwp_posts_init(): void {
	// Load text domain.
	load_plugin_textdomain( 'passwp-posts', false, dirname( PASSWP_POSTS_BASENAME ) . '/languages' );

	// Set up GitHub plugin updater.
	GitHubPluginUpdater::create_with_assets(
		'https://github.com/soderlind/passwp-posts',
		__FILE__,
		'passwp-posts',
		'/passwp-posts\.zip/',
		'main'
	);
	// Initialize components.
	new AdminSettings();
	new Protection();
	new Shortcodes();
}
add_action( 'plugins_loaded', passwp_posts_init( ... ) );

/**
 * Plugin activation hook.
 */
function passwp_posts_activate(): void {
	// Set default options if not exists.
	if ( get_option( 'passwp_posts_settings' ) === false ) {
		add_option(
			option: 'passwp_posts_settings',
			value: [
				'password_hash'      => '',
				'cookie_expiry_days' => 30,
				'excluded_posts'     => [],
				'enabled'            => false,
			]
		);
	}
}
register_activation_hook( __FILE__, passwp_posts_activate( ... ) );

/**
 * Plugin deactivation hook.
 */
function passwp_posts_deactivate(): void {
	// Clear any cookies by setting them to expire.
	( new CookieHandler() )->clear_cookie();
}
register_deactivation_hook( __FILE__, passwp_posts_deactivate( ... ) );
