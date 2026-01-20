=== PassWP Posts ===
Contributors: soderlind
Tags: password, protection, privacy, security, access control
Requires at least: 6.8
Tested up to: 6.9
Requires PHP: 8.3
Stable tag: 1.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple password protection plugin—no usernames, no accounts, just one shared password for quick, hassle-free access control.

== Description ==

PassWP Posts adds simple password protection to your WordPress site. **No usernames, no user accounts**—just share one password with those who need access. Visitors enter the password and they're in.

This is ideal when you need quick, shared access without the complexity of user management.

**How It Differs from WordPress Built-in Protection:**

* WordPress's per-post password protection requires a password for each individual post
* PassWP Posts uses one password for your entire site (or selected content)
* No need to share multiple passwords for multiple pages

**Key Features:**

* **One Password, No Username** - Visitors just enter the password—no accounts needed
* **Front Page Always Public** - Your homepage remains accessible to everyone
* **Logged-in User Bypass** - WordPress users (editors, admins) skip the prompt
* **Flexible Protection** - Protect all content (with exclusions) or only selected pages/posts
* **Customizable Form** - Match your brand with colors, typography, and logo
* **Preset Themes** - Quick-start with Default Purple, Business Blue, or Dark Mode
* **Live Preview** - See changes in real-time before saving
* **Remember Me** - Visitors stay authenticated for your configured duration
* **Secure** - Uses WordPress-native password hashing and secure cookies

**When to Use This Plugin:**

* **Staging sites** - Share a password with clients to preview before launch
* **Client portals** - Protect project files or deliverables
* **Pre-launch websites** - Keep "Coming Soon" sites private during development
* **Private blogs** - Share personal content with family or friends
* **Internal resources** - Protect company documentation from public access
* **Event websites** - Limit access to event details or member-only areas
* **Photography galleries** - Share client proofs without requiring login
* **Educational content** - Protect course materials for a class

**When NOT to Use This Plugin:**

If you need individual user accounts, different access levels, or tracking who accessed what—use a membership plugin instead. PassWP Posts is for simple shared access, not user management.

== Installation ==

1. Upload the `passwp-posts` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → PassWP Posts to configure

== Configuration ==

**General Tab:**

1. Navigate to **Settings → PassWP Posts**
2. Check **Enable Protection** to activate
3. Enter your desired **Password**
4. Set **Remember Me Duration** (how long to remember the password)
5. Choose **Protection Mode**:
   - *Protect all pages and posts* - then optionally exclude specific content
   - *Protect only selected* - then choose which pages/posts to protect
6. Click **Save Settings**

**Customize Tab:**

1. Click the **Customize** tab
2. Choose a **Preset Theme** or customize individual settings:
   - Background color, gradient, and image
   - Card styling (background, border radius, shadow)
   - Logo with adjustable width
   - Typography (heading, colors, font family)
   - Button appearance
   - Footer text and link
3. Use the **Live Preview** to see changes in real-time
4. Click **Save Settings**

== Frequently Asked Questions ==

= Does this protect the WordPress admin area? =

No, the WordPress admin area has its own login system. This plugin only protects the front-end content.

= Can I use different passwords for different pages? =

No, this plugin uses a single password for all protected content. For per-page passwords, use WordPress's built-in visibility settings.

= Will this affect SEO? =

Yes, protected content will not be accessible to search engine crawlers. Keep SEO-important pages (like your front page) unprotected.

= Is the password stored securely? =

Yes, passwords are hashed using `wp_hash_password()`, the same function WordPress uses for user passwords.

= Can I customize the password form? =

Yes! Use the **Customize** tab in Settings → PassWP Posts to change colors, typography, add a logo, and more—all with live preview. For advanced customization, copy `templates/password-form.php` to your theme and modify it. You can also use the `passwp_posts_form_template` filter.

= Does it work with caching plugins? =

You may need to exclude protected pages from caching or configure your caching plugin to respect cookies.

== Screenshots ==

1. Password form shown to visitors
2. Admin settings page
3. Select2 dropdown for excluding posts

== Changelog ==

= 1.3.3 =
* Fixed fatal error on plugin activation caused by early autoloading of PucFactory class
* GitHub updater now uses fully qualified class name to prevent autoload race condition
* GitHub Actions workflows now exclude test files and dev dependencies from release zip
* Added optimized autoloader to build process

= 1.3.2 =
* Auto-redirect authenticated users to redirect page when returning to login shortcode
* Added setting to enable/disable auto-redirect (default: enabled)

= 1.3.1 =
* New minimal-style CSS for shortcode form with gray input and black button
* Shortcode form now automatically enqueues its own stylesheet

= 1.3.0 =
* Added shortcode [passwp_login] to render a theme-styled password form on public pages
* Added Customize option for password placeholder text
* Unified default texts: "Enter password" placeholder and "Login" button
* Shortcode output uses common WordPress login form CSS classes

= 1.2.2 =
* Hardened redirect handling to reject unsafe external URLs
* Fixed session cookie expiration handling in browsers
* Improved footer link customize key compatibility
* Added PHPUnit coverage for redirect validation

= 1.2.1 =
* Improved documentation with clearer explanation of single-password concept
* Added detailed use case examples (staging sites, client portals, etc.)
* Added "When NOT to use" guidance for users needing membership features

= 1.2.0 =
* Refactored to PSR-4 autoloading with Composer
* Renamed class files to PascalCase (AdminSettings, CookieHandler, GitHubPluginUpdater, Protection)
* Removed custom autoloader in favor of Composer autoloader

= 1.1.2 =
* Fixed Select2 tag remove button positioning and styling

= 1.1.1 =
* Fixed fatal error when Plugin Update Checker library is not available
* Included production vendor files in repository
* Fixed JavaScript XSS vulnerabilities with proper URL and HTML escaping
* Fixed namespace error in password form template
* Aligned CUSTOMIZE_DEFAULTS with template field names

= 1.1.0 =
* Added Customize tab with live preview for password form styling
* Added preset themes: Default Purple, Business Blue, Dark Mode
* Added customizable background (color, gradient, image)
* Added card styling options (background, border radius, shadow)
* Added logo upload with adjustable width
* Added typography settings (heading, colors, font family)
* Added button customization (text, colors, border radius)
* Added footer text and link options
* Updated Norwegian Bokmål translations

= 1.0.4 =
* Added GitHub Plugin Updater for automatic updates from GitHub releases
* Added Plugin Update Checker library as dependency
* Added GitHub Actions workflows for building release zips

= 1.0.3 =
* Housekeeping

= 1.0.2 =
* Added protection mode selection: protect all (with exclusions) or protect only selected posts
* Improved settings page wording for clarity
* Updated Norwegian Bokmål translations

= 1.0.1 =
* Added password visibility toggle on settings page
* Added cache busting for assets when WP_DEBUG is enabled
* Improved PHP 8.3 compatibility with strict types

= 1.0.0 =
* Initial release
* Password protection for all pages and posts except front page
* Remember me functionality with configurable duration
* Logged-in user bypass
* Select2-powered post/page exclusion
* Secure cookie handling with SHA256 hashing
* Norwegian Bokmål translation included

== Upgrade Notice ==

= 1.2.1 =
Documentation improvements.

= 1.2.0 =
Code refactoring with PSR-4 autoloading.

= 1.1.2 =
Fixed Select2 tag styling.

= 1.1.1 =
Bug fixes for fatal errors and security improvements.

= 1.1.0 =
New Customize tab with preset themes and live preview for password form styling.

= 1.0.4 =
Added automatic plugin updates from GitHub releases.

= 1.0.3 =
Housekeeping.

= 1.0.2 =
New protection mode selection - choose to protect all content or only specific pages/posts.

= 1.0.1 =
Added password visibility toggle and improved asset cache busting.

= 1.0.0 =
Initial release of PassWP Posts.
