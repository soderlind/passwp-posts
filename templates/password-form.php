<?php
/**
 * Password form template.
 *
 * Displays the password entry form for protected pages.
 *
 * @package PassWP_Posts
 *
 * @var string $error        Error message code.
 * @var string $redirect_url URL to redirect after authentication.
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

// Get site info.
$site_name = get_bloginfo( 'name' );
$site_url  = home_url();

// Error messages.
$error_messages = array(
	'invalid'     => __( 'Incorrect password. Please try again.', 'passwp-posts' ),
	'no_password' => __( 'No password has been configured. Please contact the site administrator.', 'passwp-posts' ),
);

$error_message = isset( $error_messages[ $error ] ) ? $error_messages[ $error ] : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
		<?php echo esc_html( sprintf( /* translators: %s is the site name. */ __( 'Password Required - %s', 'passwp-posts' ), $site_name ) ); ?>
	</title>
	<link rel="stylesheet"
		href="<?php echo esc_url( PASSWP_POSTS_URL . 'assets/css/password-form.css?ver=' . PASSWP_POSTS_VERSION ); ?>">
	<?php wp_site_icon(); ?>
</head>

<body class="passwp-posts-body">
	<div class="passwp-posts-container">
		<div class="passwp-posts-card">
			<div class="passwp-posts-header">
				<a href="<?php echo esc_url( $site_url ); ?>" class="passwp-posts-site-link">
					<?php if ( has_site_icon() ) : ?>
						<img src="<?php echo esc_url( get_site_icon_url( 64 ) ); ?>" alt="" class="passwp-posts-site-icon">
					<?php endif; ?>
					<h1 class="passwp-posts-site-name"><?php echo esc_html( $site_name ); ?></h1>
				</a>
			</div>

			<div class="passwp-posts-content">
				<div class="passwp-posts-lock-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
						<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
					</svg>
				</div>

				<h2 class="passwp-posts-title"><?php esc_html_e( 'Password Required', 'passwp-posts' ); ?></h2>
				<p class="passwp-posts-description">
					<?php esc_html_e( 'This content is protected. Please enter the password to continue.', 'passwp-posts' ); ?>
				</p>

				<?php if ( $error_message ) : ?>
					<div class="passwp-posts-error" role="alert">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="8" x2="12" y2="12"></line>
							<line x1="12" y1="16" x2="12.01" y2="16"></line>
						</svg>
						<span><?php echo esc_html( $error_message ); ?></span>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
					class="passwp-posts-form">
					<input type="hidden" name="action" value="passwp_posts_auth">
					<input type="hidden" name="passwp_redirect" value="<?php echo esc_url( $redirect_url ); ?>">
					<?php wp_nonce_field( 'passwp_posts_auth', 'passwp_posts_nonce' ); ?>

					<div class="passwp-posts-field">
						<label for="passwp_password" class="screen-reader-text">
							<?php esc_html_e( 'Password', 'passwp-posts' ); ?>
						</label>
						<input type="password" id="passwp_password" name="passwp_password" class="passwp-posts-input"
							placeholder="<?php esc_attr_e( 'Enter password', 'passwp-posts' ); ?>" required autofocus
							autocomplete="current-password">
					</div>

					<div class="passwp-posts-remember">
						<label class="passwp-posts-checkbox-label">
							<input type="checkbox" name="passwp_remember" value="1" checked>
							<span><?php esc_html_e( 'Remember me', 'passwp-posts' ); ?></span>
						</label>
					</div>

					<button type="submit" class="passwp-posts-submit">
						<?php esc_html_e( 'Submit', 'passwp-posts' ); ?>
					</button>
				</form>
			</div>

			<div class="passwp-posts-footer">
				<a href="<?php echo esc_url( $site_url ); ?>">&larr;
					<?php esc_html_e( 'Back to home', 'passwp-posts' ); ?></a>
			</div>
		</div>
	</div>
</body>

</html>