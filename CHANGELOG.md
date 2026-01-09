# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.1] - 2026-01-09

### Added

- New minimal-style CSS for shortcode form with gray input and black button

### Changed

- Shortcode form now automatically enqueues its own stylesheet

## [1.3.0] - 2026-01-08

### Added

- New `[passwp_login]` shortcode for rendering a password form on public pages
- New Customize option for the password input placeholder

### Changed

- Unified default texts: "Enter password" placeholder and "Login" button
- Shortcode output uses common WordPress login form CSS classes

## [1.2.2] - 2025-12-15

### Security & Fixed

- Hardened redirect handling: all posted redirect URLs are now validated and external/hostile URLs are rejected, always falling back to the site home if unsafe
- Session cookie fix: session cookies now omit the expires option for correct browser behavior
- Footer link customize key is now consistent and backward compatible
- Added PHPUnit test to ensure external redirect URLs are always rejected



## [1.2.1] - 2024-12-15

### Changed

- Improved documentation with clearer explanation of single-password concept
- Added detailed use case examples (staging sites, client portals, photography galleries, etc.)
- Added "When NOT to use" guidance for users needing membership features

## [1.2.0] - 2024-12-15

### Changed

- Refactored to PSR-4 autoloading with Composer
- Renamed class files to PascalCase convention:
  - `class-admin-settings.php` → `AdminSettings.php`
  - `class-cookie-handler.php` → `CookieHandler.php`
  - `class-github-plugin-updater.php` → `GitHubPluginUpdater.php`
  - `class-protection.php` → `Protection.php`
- Renamed classes from underscore to PascalCase (e.g., `Admin_Settings` → `AdminSettings`)
- Removed custom autoloader in favor of Composer autoloader

## [1.1.2] - 2024-12-15

### Fixed

- Select2 tag remove button (×) positioning and styling

## [1.1.1] - 2024-12-15

### Fixed

- Fatal error when Plugin Update Checker library is not available (added class_exists check)
- Namespace error in password form template (PassWP_Posts vs PassWP\Posts)
- JavaScript XSS vulnerabilities with proper URL and HTML escaping in customize.js
- CUSTOMIZE_DEFAULTS field names aligned with template expectations

### Added

- Production vendor files (yahnis-elsts/plugin-update-checker) included in repository

## [1.1.0] - 2024-12-15

### Added

- Customize tab with live preview for password form styling
- Preset themes: Default Purple, Business Blue, Dark Mode
- Background customization: color, gradient, and background image
- Card styling options: background color, border radius, shadow
- Logo upload with adjustable width
- Typography settings: heading text, heading color, text color, font family
- Button customization: text, background color, text color, border radius
- Form options: show/hide "Remember Me" checkbox, input border radius
- Footer customization: text and link URL
- Updated Norwegian Bokmål translations for all new strings

## [1.0.4] - 2024-12-15

### Added

- GitHub Plugin Updater class for automatic updates from GitHub releases
- Plugin Update Checker library (yahnis-elsts/plugin-update-checker) as dependency
- GitHub Actions workflow for building release zips on publish
- GitHub Actions workflow for manually building release zips
- GitHub FUNDING.yml for sponsor information

## [1.0.3] - 2024-12-12

### Changed

- Housekeeping

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

[Unreleased]: https://github.com/soderlind/passwp-posts/compare/v1.0.4...HEAD
[1.0.4]: https://github.com/soderlind/passwp-posts/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/soderlind/passwp-posts/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/soderlind/passwp-posts/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/soderlind/passwp-posts/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/soderlind/passwp-posts/releases/tag/v1.0.0
