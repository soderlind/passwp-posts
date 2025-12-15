<?php
/**
 * Tests for the Admin Settings class.
 *
 * @package PassWP_Posts\Tests
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PassWP\Posts\AdminSettings;

/**
 * Class AdminSettingsTest
 *
 * @covers \PassWP\Posts\AdminSettings
 */
class AdminSettingsTest extends PassWP_Posts_TestCase {

	/**
	 * Test settings sanitization preserves enabled state.
	 */
	public function test_sanitize_settings_enabled(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array(
			'enabled' => '1',
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertTrue( $result[ 'enabled' ] );
	}

	/**
	 * Test settings sanitization disabled state.
	 */
	public function test_sanitize_settings_disabled(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array();

		$result = $settings->sanitize_settings( $input );

		$this->assertFalse( $result[ 'enabled' ] );
	}

	/**
	 * Test password is hashed when provided.
	 */
	public function test_sanitize_settings_hashes_new_password(): void {
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'wp_hash_password' )->alias(
			function ( $password ) {
				return 'hashed_' . $password;
			}
		);

		$settings = new AdminSettings();

		$input = array(
			'password' => 'my_secret_password',
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertEquals( 'hashed_my_secret_password', $result[ 'password_hash' ] );
	}

	/**
	 * Test existing password is preserved when field is empty.
	 */
	public function test_sanitize_settings_preserves_existing_password(): void {
		$existing_hash = '$2y$10$existinghash';

		Functions\when( 'get_option' )->justReturn(
			array(
				'password_hash' => $existing_hash,
			)
		);

		$settings = new AdminSettings();

		$input = array(
			'password' => '',
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertEquals( $existing_hash, $result[ 'password_hash' ] );
	}

	/**
	 * Test cookie expiry days sanitization.
	 */
	public function test_sanitize_settings_cookie_expiry_days(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array(
			'cookie_expiry_days' => '45',
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertEquals( 45, $result[ 'cookie_expiry_days' ] );
	}

	/**
	 * Test cookie expiry days minimum bound.
	 */
	public function test_sanitize_settings_cookie_expiry_minimum(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array(
			'cookie_expiry_days' => '0',
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertEquals( 1, $result[ 'cookie_expiry_days' ] );
	}

	/**
	 * Test cookie expiry days maximum bound.
	 */
	public function test_sanitize_settings_cookie_expiry_maximum(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array(
			'cookie_expiry_days' => '500',
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertEquals( 365, $result[ 'cookie_expiry_days' ] );
	}

	/**
	 * Test excluded posts sanitization.
	 */
	public function test_sanitize_settings_protected_posts(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array(
			'protected_posts' => array( '123', '456', '789' ),
		);

		$result = $settings->sanitize_settings( $input );

		$this->assertEquals( array( 123, 456, 789 ), $result[ 'protected_posts' ] );
	}

	/**
	 * Test protected posts filters invalid values.
	 */
	public function test_sanitize_settings_protected_posts_filters_invalid(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input = array(
			'protected_posts' => array( '123', 'invalid', '0', '456' ),
		);

		$result = $settings->sanitize_settings( $input );

		// array_filter preserves keys, so use array_values for comparison.
		$this->assertEquals( array( 123, 456 ), array_values( $result[ 'protected_posts' ] ) );
	}

	/**
	 * Test AJAX search requires capability.
	 */
	public function test_ajax_search_requires_capability(): void {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		$json_error_called = false;

		Functions\when( 'wp_send_json_error' )->alias(
			function ( $data ) use ( &$json_error_called ) {
				$json_error_called = true;
				throw new \Exception( 'json_error_sent' );
			}
		);

		try {
			$settings = new AdminSettings();
			$settings->ajax_search_posts();
		} catch (\Exception $e) {
			if ( 'json_error_sent' !== $e->getMessage() ) {
				throw $e;
			}
		}

		$this->assertTrue( $json_error_called );
	}

	/**
	 * Test default settings values.
	 */
	public function test_default_settings_values(): void {
		Functions\when( 'get_option' )->justReturn( array() );

		$settings = new AdminSettings();

		$input  = array();
		$result = $settings->sanitize_settings( $input );

		$this->assertFalse( $result[ 'enabled' ] );
		$this->assertEmpty( $result[ 'password_hash' ] );
		$this->assertEquals( 30, $result[ 'cookie_expiry_days' ] );
		$this->assertEquals( 'all', $result[ 'protection_mode' ] );
		$this->assertEquals( array(), $result[ 'protected_posts' ] );
	}
}
