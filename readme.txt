=== PassWP Posts ===
Contributors: soderlind
Tags: password, protection, privacy, security, access control
Requires at least: 6.8
Tested up to: 6.9
Requires PHP: 8.3
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Password protect your site's content. Protect all pages and posts or only selected ones—logged-in users always bypass.

== Description ==

PassWP Posts is a simple yet powerful plugin that adds password protection to your entire WordPress site. Unlike WordPress's built-in password protection which works per-post, this plugin protects everything with a single password.

**Key Features:**

* **Single Password** - One password protects your entire site (no username needed)
* **Front Page Always Accessible** - Your homepage remains public
* **Logged-in User Bypass** - Authenticated users skip the password prompt
* **Protection Modes** - Protect all content (with exclusions) or only selected pages/posts
* **Customizable Password Form** - Personalize colors, typography, logo, and more
* **Preset Themes** - Choose from Default Purple, Business Blue, or Dark Mode
* **Live Preview** - See your customizations in real-time before saving
* **Remember Me** - Visitors stay authenticated for configurable durations
* **Secure** - Uses WordPress native password hashing and secure cookies

**Perfect For:**

* Staging sites that need client access
* Private blogs or journals
* Member-only content areas
* Pre-launch websites
* Internal company resources

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
