# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.2] - 2024-12-11

### Added

- Protection mode selection: protect all (with exclusions) or protect only selected posts
- Separate dropdowns for excluded posts and protected posts based on mode

### Changed

- Improved settings page wording for clarity
- Instant field visibility toggle (no animation delay)
- Updated Norwegian Bokmål translations

## [1.0.1] - 2024-12-11

### Added

- Password visibility toggle button on admin settings page
- Cache busting for CSS/JS assets when `WP_DEBUG` is enabled (uses `time()` as version)

### Changed

- Improved dashicon centering in toggle button

## [1.0.0] - 2024-12-11

### Added

- Initial release
- Password protection for all pages and posts except front page
- Single password authentication (no username required)
- "Remember me" functionality with configurable cookie duration
- Automatic bypass for logged-in WordPress users
- Front page always accessible without password
- Select2-powered dropdown for excluding specific pages/posts from protection
- Admin settings page under Settings → PassWP Posts
- Secure cookie handling using SHA256 hashing with WordPress salts
- Password storage using WordPress native `wp_hash_password()`
- Nonce verification on all form submissions
- Translation-ready with text domain `passwp-posts`
- Norwegian Bokmål (nb_NO) translation included
- PHP translation files for WordPress 6.5+ performance
- PHPUnit test suite with Brain\Monkey for WordPress mocking
- Vitest test suite for JavaScript testing
- Bundled Select2 4.1.0 (no external CDN dependencies)

### Security

- Passwords hashed using `wp_hash_password()` (bcrypt)
- Cookie values use SHA256 hash of password hash + `wp_salt('auth')`
- Admin capabilities checked with `manage_options`
- All forms protected with WordPress nonces

[Unreleased]: https://github.com/soderlind/passwp-posts/compare/v1.0.2...HEAD
[1.0.2]: https://github.com/soderlind/passwp-posts/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/soderlind/passwp-posts/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/soderlind/passwp-posts/releases/tag/v1.0.0
