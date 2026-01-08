<?php
/**
 * Tests for the Shortcodes class.
 *
 * @package PassWP_Posts\Tests
 */

use Brain\Monkey\Functions;
use PassWP\Posts\CookieHandler;
use PassWP\Posts\Shortcodes;

/**
 * Class ShortcodesTest
 *
 * @covers \PassWP\Posts\Shortcodes
 */
class ShortcodesTest extends PassWP_Posts_TestCase {

	public function test_shortcode_returns_empty_when_disabled(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => false,
				'password_hash' => 'hash',
			)
		);
		Functions\when( 'is_user_logged_in' )->justReturn( false );

		$shortcodes = new Shortcodes();
		$this->assertSame( '', $shortcodes->render_passwp_login() );
	}

	public function test_shortcode_renders_form_with_default_texts_and_referer_redirect(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => 'hash',
				'customize'     => array(
					'show_remember_me' => true,
				),
			)
		);
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'wp_get_raw_referer' )->justReturn( 'https://example.com/front' );
		Functions\when( 'wp_nonce_field' )->alias(
			static function ( $action, $name, $referer = true, $echo = true ) {
				return '<input type="hidden" name="' . $name . '" value="nonce" />';
			}
		);

		$_GET    = array();
		$_COOKIE = array();

		$shortcodes = new Shortcodes();
		$html       = $shortcodes->render_passwp_login();

		$this->assertStringContainsString( 'class="passwp-login"', $html );
		$this->assertStringContainsString( 'action="https://example.com/wp-admin/admin-post.php"', $html );
		$this->assertStringContainsString( 'name="action" value="passwp_posts_auth"', $html );
		$this->assertStringContainsString( 'name="passwp_redirect" value="https://example.com/front"', $html );
		$this->assertStringContainsString( 'name="passwp_password"', $html );
		$this->assertStringContainsString( 'placeholder="Enter password"', $html );
		$this->assertStringContainsString( '<button type="submit" class="button wp-element-button">Login</button>', $html );
		$this->assertStringContainsString( 'name="passwp_remember" value="1"', $html );
	}

	public function test_shortcode_returns_empty_when_cookie_is_valid(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => 'hash',
			)
		);
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'wp_salt' )->justReturn( 'test_salt' );
		Functions\when( 'wp_get_raw_referer' )->justReturn( 'https://example.com/front' );
		Functions\when( 'wp_nonce_field' )->alias(
			static function () {
				return '';
			}
		);

		$cookie_handler = new CookieHandler();
		$cookie_name    = $cookie_handler->get_cookie_name();
		$cookie_value   = $cookie_handler->generate_cookie_value( 'hash' );
		$_COOKIE        = array( $cookie_name => $cookie_value );

		$shortcodes = new Shortcodes();
		$this->assertSame( '', $shortcodes->render_passwp_login() );
	}

	public function test_shortcode_uses_custom_placeholder_and_button_text(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => 'hash',
				'customize'     => array(
					'password_placeholder' => 'Secret',
					'button_text'          => 'Sign in',
					'show_remember_me'     => false,
				),
			)
		);
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'wp_get_raw_referer' )->justReturn( '' );
		Functions\when( 'wp_nonce_field' )->alias(
			static function () {
				return '';
			}
		);

		$_GET    = array();
		$_COOKIE = array();

		$shortcodes = new Shortcodes();
		$html       = $shortcodes->render_passwp_login();

		$this->assertStringContainsString( 'placeholder="Secret"', $html );
		$this->assertStringContainsString( '<button type="submit" class="button wp-element-button">Sign in</button>', $html );
		$this->assertStringNotContainsString( 'name="passwp_remember"', $html );
		$this->assertStringContainsString( 'name="passwp_redirect" value="https://example.com/"', $html );
	}
}
