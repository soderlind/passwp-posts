<?php
/**
 * Tests for the Cookie Handler class.
 *
 * @package PassWP_Posts\Tests
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PassWP\Posts\CookieHandler;

/**
 * Class CookieHandlerTest
 *
 * @covers \PassWP\Posts\CookieHandler
 */
class CookieHandlerTest extends PassWP_Posts_TestCase {

	/**
	 * Cookie handler instance.
	 *
	 * @var CookieHandler
	 */
	private CookieHandler $handler;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->handler = new CookieHandler();
	}

	/**
	 * Test cookie name generation.
	 */
	public function test_get_cookie_name(): void {
		$cookie_name = $this->handler->get_cookie_name();

		$this->assertStringStartsWith( 'passwp_posts_auth_', $cookie_name );
		$this->assertStringContainsString( COOKIEHASH, $cookie_name );
	}

	/**
	 * Test cookie value generation is consistent.
	 */
	public function test_generate_cookie_value_is_consistent(): void {
		Functions\when( 'wp_salt' )->justReturn( 'test_salt_value' );

		$password_hash = '$2y$10$abcdefghijklmnopqrstuv';

		$value1 = $this->handler->generate_cookie_value( $password_hash );
		$value2 = $this->handler->generate_cookie_value( $password_hash );

		$this->assertSame( $value1, $value2 );
		$this->assertEquals( 64, strlen( $value1 ) ); // SHA256 produces 64 hex chars.
	}

	/**
	 * Test cookie value changes with different password hash.
	 */
	public function test_generate_cookie_value_differs_for_different_hashes(): void {
		Functions\when( 'wp_salt' )->justReturn( 'test_salt_value' );

		$hash1 = '$2y$10$abcdefghijklmnopqrstuv';
		$hash2 = '$2y$10$zyxwvutsrqponmlkjihgfe';

		$value1 = $this->handler->generate_cookie_value( $hash1 );
		$value2 = $this->handler->generate_cookie_value( $hash2 );

		$this->assertNotSame( $value1, $value2 );
	}

	/**
	 * Test valid cookie verification.
	 */
	public function test_is_valid_cookie_returns_true_for_valid_cookie(): void {
		Functions\when( 'wp_salt' )->justReturn( 'test_salt_value' );

		$password_hash = '$2y$10$abcdefghijklmnopqrstuv';
		$cookie_name   = $this->handler->get_cookie_name();
		$cookie_value  = $this->handler->generate_cookie_value( $password_hash );

		// Simulate cookie being set.
		$_COOKIE[ $cookie_name ] = $cookie_value;

		$result = $this->handler->is_valid_cookie( $password_hash );

		$this->assertTrue( $result );

		// Clean up.
		unset( $_COOKIE[ $cookie_name ] );
	}

	/**
	 * Test invalid cookie verification.
	 */
	public function test_is_valid_cookie_returns_false_for_invalid_cookie(): void {
		Functions\when( 'wp_salt' )->justReturn( 'test_salt_value' );

		$password_hash = '$2y$10$abcdefghijklmnopqrstuv';
		$cookie_name   = $this->handler->get_cookie_name();

		// Set invalid cookie value.
		$_COOKIE[ $cookie_name ] = 'invalid_cookie_value';

		$result = $this->handler->is_valid_cookie( $password_hash );

		$this->assertFalse( $result );

		// Clean up.
		unset( $_COOKIE[ $cookie_name ] );
	}

	/**
	 * Test missing cookie verification.
	 */
	public function test_is_valid_cookie_returns_false_when_cookie_missing(): void {
		Functions\when( 'wp_salt' )->justReturn( 'test_salt_value' );

		$password_hash = '$2y$10$abcdefghijklmnopqrstuv';
		$cookie_name   = $this->handler->get_cookie_name();

		// Ensure cookie is not set.
		unset( $_COOKIE[ $cookie_name ] );

		$result = $this->handler->is_valid_cookie( $password_hash );

		$this->assertFalse( $result );
	}

	/**
	 * Test that cookie value is not the same as password hash.
	 */
	public function test_cookie_value_is_not_password_hash(): void {
		Functions\when( 'wp_salt' )->justReturn( 'test_salt_value' );

		$password_hash = '$2y$10$abcdefghijklmnopqrstuv';
		$cookie_value  = $this->handler->generate_cookie_value( $password_hash );

		$this->assertNotEquals( $password_hash, $cookie_value );
	}
}
