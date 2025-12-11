<?php
/**
 * Tests for the Protection class.
 *
 * @package PassWP_Posts\Tests
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use PassWP\Posts\Protection;

/**
 * Class ProtectionTest
 *
 * @covers \PassWP\Posts\Protection
 */
class ProtectionTest extends PassWP_Posts_TestCase {

	/**
	 * Test protection is skipped when disabled.
	 */
	public function test_protection_skipped_when_disabled(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => false,
				'password_hash' => 'some_hash',
			)
		);

		// These should never be called if protection is disabled.
		Functions\expect( 'is_front_page' )->never();
		Functions\expect( 'is_user_logged_in' )->never();

		$protection = new Protection();
		$protection->check_protection();

		// Test passes if no exception is thrown and we reach this point.
		$this->assertTrue( true );
	}

	/**
	 * Test protection is skipped when no password set.
	 */
	public function test_protection_skipped_when_no_password(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => '',
			)
		);

		Functions\expect( 'is_front_page' )->never();

		$protection = new Protection();
		$protection->check_protection();

		$this->assertTrue( true );
	}

	/**
	 * Test protection is skipped for front page.
	 */
	public function test_protection_skipped_for_front_page(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => 'some_hash',
			)
		);

		Functions\when( 'is_front_page' )->justReturn( true );
		Functions\expect( 'is_user_logged_in' )->never();

		$protection = new Protection();
		$protection->check_protection();

		$this->assertTrue( true );
	}

	/**
	 * Test protection is skipped for logged-in users.
	 */
	public function test_protection_skipped_for_logged_in_users(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => 'some_hash',
			)
		);

		Functions\when( 'is_front_page' )->justReturn( false );
		Functions\when( 'is_user_logged_in' )->justReturn( true );

		$protection = new Protection();
		$protection->check_protection();

		$this->assertTrue( true );
	}

	/**
	 * Test protection is skipped for admin pages.
	 */
	public function test_protection_skipped_for_admin_pages(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => 'some_hash',
			)
		);

		Functions\when( 'is_front_page' )->justReturn( false );
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'is_admin' )->justReturn( true );

		// Set pagenow global.
		$GLOBALS[ 'pagenow' ] = 'edit.php';

		$protection = new Protection();
		$protection->check_protection();

		$this->assertTrue( true );

		unset( $GLOBALS[ 'pagenow' ] );
	}

	/**
	 * Test protection is skipped when mode is 'selected' and post is not in protected list.
	 */
	public function test_protection_skipped_for_unprotected_posts_in_selected_mode(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'         => true,
				'password_hash'   => 'some_hash',
				'protection_mode' => 'selected',
				'protected_posts' => array( 456, 789 ),
			)
		);

		Functions\when( 'is_front_page' )->justReturn( false );
		Functions\when( 'is_user_logged_in' )->justReturn( false );
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'get_queried_object_id' )->justReturn( 123 );
		Functions\when( 'wp_salt' )->justReturn( 'test_salt' );

		$GLOBALS[ 'pagenow' ] = 'index.php';

		$protection = new Protection();
		$protection->check_protection();

		$this->assertTrue( true );

		unset( $GLOBALS[ 'pagenow' ] );
	}

	/**
	 * Test form submission fails without nonce.
	 */
	public function test_form_submission_fails_without_nonce(): void {
		Functions\when( 'wp_verify_nonce' )->justReturn( false );

		$this->expectException( \Exception::class);
		$this->expectExceptionMessage( 'Security check failed.' );

		$_POST = array();

		$protection = new Protection();
		$protection->handle_form_submission();
	}

	/**
	 * Test form submission redirects on invalid password.
	 */
	public function test_form_submission_redirects_on_invalid_password(): void {
		$_POST = array(
			'passwp_posts_nonce' => 'valid_nonce',
			'passwp_password'    => 'wrong_password',
			'passwp_redirect'    => 'https://example.com/test-page',
		);

		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'       => true,
				'password_hash' => '$2y$10$validhashhere',
			)
		);
		Functions\when( 'wp_check_password' )->justReturn( false );
		Functions\when( 'add_query_arg' )->justReturn( 'https://example.com/test-page?passwp_error=invalid' );

		$redirect_url = '';

		Functions\when( 'wp_safe_redirect' )->alias(
			function ( $url ) use ( &$redirect_url ) {
				$redirect_url = $url;
				throw new \Exception( 'redirect_called' );
			}
		);

		try {
			$protection = new Protection();
			$protection->handle_form_submission();
		} catch (\Exception $e) {
			if ( 'redirect_called' !== $e->getMessage() ) {
				throw $e;
			}
		}

		$this->assertStringContainsString( 'passwp_error=invalid', $redirect_url );

		$_POST = array();
	}

	/**
	 * Test successful form submission sets cookie and redirects.
	 */
	public function test_form_submission_succeeds_with_valid_password(): void {
		$_POST = array(
			'passwp_posts_nonce' => 'valid_nonce',
			'passwp_password'    => 'correct_password',
			'passwp_redirect'    => 'https://example.com/test-page',
			'passwp_remember'    => '1',
		);

		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn(
			array(
				'enabled'            => true,
				'password_hash'      => '$2y$10$validhashhere',
				'cookie_expiry_days' => 30,
			)
		);
		Functions\when( 'wp_check_password' )->justReturn( true );
		Functions\when( 'wp_salt' )->justReturn( 'test_salt' );

		$redirect_url = '';

		Functions\when( 'wp_safe_redirect' )->alias(
			function ( $url ) use ( &$redirect_url ) {
				$redirect_url = $url;
				throw new \Exception( 'redirect_called' );
			}
		);

		try {
			$protection = new Protection();
			$protection->handle_form_submission();
		} catch (\Exception $e) {
			if ( 'redirect_called' !== $e->getMessage() ) {
				throw $e;
			}
		}

		$this->assertEquals( 'https://example.com/test-page', $redirect_url );

		$_POST = array();
	}
}
