<?php
/**
 * Shortcodes for PassWP Posts.
 *
 * @package PassWP\Posts
 */

declare(strict_types=1);

namespace PassWP\Posts;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Class Shortcodes
 */
final class Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct(
		private readonly CookieHandler $cookie_handler = new CookieHandler(),
	) {
		add_action( 'init', $this->register_shortcodes( ... ) );
	}

	/**
	 * Register plugin shortcodes.
	 */
	public function register_shortcodes(): void {
		add_shortcode( 'passwp_login', $this->render_passwp_login( ... ) );
	}

	/**
	 * Render the [passwp_login] shortcode.
	 *
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 */
	public function render_passwp_login( array|string $atts = [] ): string {
		// Enqueue shortcode form styles.
		wp_enqueue_style(
			'passwp-posts-shortcode',
			PASSWP_POSTS_URL . 'assets/css/shortcode-form.css',
			[],
			PASSWP_POSTS_VERSION
		);

		$settings = get_option( 'passwp_posts_settings', [] );

		if ( empty( $settings[ 'enabled' ] ) || empty( $settings[ 'password_hash' ] ) ) {
			return '';
		}

		if ( is_user_logged_in() ) {
			return '';
		}

		$password_hash = (string) $settings[ 'password_hash' ];
		if ( $this->cookie_handler->is_valid_cookie( $password_hash ) ) {
			// User already authenticated - redirect to the redirect page if auto-redirect is enabled.
			$auto_redirect = (bool) ( $settings[ 'auto_redirect' ] ?? true );
			if ( $auto_redirect ) {
				$redirect_url = $this->get_redirect_url( $atts );
				if ( $redirect_url !== '' ) {
					wp_safe_redirect( $redirect_url );
					exit;
				}
			}
			return '';
		}

		$customize = AdminSettings::get_customize_settings();

		$placeholder = ! empty( $customize[ 'password_placeholder' ] )
			? (string) $customize[ 'password_placeholder' ]
			: __( 'Enter password', 'passwp-posts' );

		$button_text = ! empty( $customize[ 'button_text' ] )
			? (string) $customize[ 'button_text' ]
			: __( 'Login', 'passwp-posts' );

		$redirect_attr = '';
		if ( is_array( $atts ) && isset( $atts[ 'redirect' ] ) ) {
			$redirect_attr = (string) $atts[ 'redirect' ];
		}

		$default_redirect = home_url( '/' );
		$raw_referer      = wp_get_raw_referer();
		$redirect_url_raw = $redirect_attr !== '' ? $redirect_attr : ( $raw_referer ?: $default_redirect );
		$redirect_url_raw = esc_url_raw( $redirect_url_raw );
		$redirect_url     = $redirect_url_raw !== '' ? $redirect_url_raw : $default_redirect;
		if ( function_exists( '\\wp_validate_redirect' ) ) {
			$redirect_url = wp_validate_redirect( $redirect_url, $default_redirect );
		}

		$error = isset( $_GET[ 'passwp_error' ] )
			? sanitize_text_field( wp_unslash( $_GET[ 'passwp_error' ] ) )
			: '';

		$error_messages = [
			'invalid'     => __( 'Incorrect password. Please try again.', 'passwp-posts' ),
			'no_password' => __( 'No password has been configured. Please contact the site administrator.', 'passwp-posts' ),
		];
		$error_message  = isset( $error_messages[ $error ] ) ? $error_messages[ $error ] : '';

		$nonce_field = wp_nonce_field( 'passwp_posts_auth', 'passwp_posts_nonce', true, false );
		if ( ! is_string( $nonce_field ) ) {
			$nonce_field = '';
		}

		$show_remember_me = ! empty( $customize[ 'show_remember_me' ] );

		$html = '<div class="passwp-login">';
		if ( $error_message !== '' ) {
			$html .= '<p role="alert">' . esc_html( $error_message ) . '</p>';
		}
		$html .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="loginform">';
		$html .= '<input type="hidden" name="action" value="passwp_posts_auth" />';
		$html .= '<input type="hidden" name="passwp_redirect" value="' . esc_url( $redirect_url ) . '" />';
		$html .= $nonce_field;
		$html .= '<p class="login-password">';
		$html .= '<label for="passwp_password">' . esc_html__( 'Password', 'passwp-posts' ) . '</label>';
		$html .= '<input type="password" id="passwp_password" name="passwp_password" class="input" required';
		$html .= ' placeholder="' . esc_attr( $placeholder ) . '" autocomplete="current-password" />';
		$html .= '</p>';

		if ( $show_remember_me ) {
			$html .= '<p class="login-remember">';
			$html .= '<label>';
			$html .= '<input type="checkbox" name="passwp_remember" value="1" checked /> ';
			$html .= esc_html__( 'Remember me', 'passwp-posts' );
			$html .= '</label>';
			$html .= '</p>';
		}

		$html .= '<p class="login-submit">';
		$html .= '<button type="submit" class="button wp-element-button">' . esc_html( $button_text ) . '</button>';
		$html .= '</p>';
		$html .= '</form>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the redirect URL from shortcode attributes.
	 *
	 * @param array<string, mixed>|string $atts Shortcode attributes.
	 */
	private function get_redirect_url( array|string $atts = [] ): string {
		$redirect_attr = '';
		if ( is_array( $atts ) && isset( $atts[ 'redirect' ] ) ) {
			$redirect_attr = (string) $atts[ 'redirect' ];
		}

		if ( $redirect_attr === '' ) {
			return '';
		}

		$default_redirect = home_url( '/' );
		$redirect_url_raw = esc_url_raw( $redirect_attr );
		$redirect_url     = $redirect_url_raw !== '' ? $redirect_url_raw : $default_redirect;

		if ( function_exists( '\\wp_validate_redirect' ) ) {
			$redirect_url = wp_validate_redirect( $redirect_url, $default_redirect );
		}

		return $redirect_url;
	}
}
