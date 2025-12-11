=== PassWP Posts ===
Contributors: starter
Tags: password, protection, privacy, security, access control
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Password protect all pages and posts except the front page. Logged-in users bypass protection automatically.

== Description ==

PassWP Posts is a simple yet powerful plugin that adds password protection to your entire WordPress site. Unlike WordPress's built-in password protection which works per-post, this plugin protects everything with a single password.

**Key Features:**

* **Single Password** - One password protects your entire site (no username needed)
* **Front Page Always Accessible** - Your homepage remains public
* **Logged-in User Bypass** - Authenticated users skip the password prompt
* **Remember Me** - Visitors can save their password for configurable durations
* **Exclude Content** - Choose specific pages or posts to remain public
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

1. Navigate to **Settings → PassWP Posts**
2. Check **Enable Protection** to activate
3. Enter your desired **Password**
4. Set **Cookie Expiry** (how long to remember the password)
5. Optionally select pages/posts to exclude from protection
6. Click **Save Settings**

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

Yes, copy `templates/password-form.php` to your theme and modify it. You can also use the `passwp_posts_form_template` filter.

= Does it work with caching plugins? =

You may need to exclude protected pages from caching or configure your caching plugin to respect cookies.

== Screenshots ==

1. Password form shown to visitors
2. Admin settings page
3. Select2 dropdown for excluding posts

== Changelog ==

= 1.0.0 =
* Initial release
* Password protection for all pages and posts except front page
* Remember me functionality with configurable duration
* Logged-in user bypass
* Select2-powered post/page exclusion
* Secure cookie handling with SHA256 hashing
* Norwegian Bokmål translation included

== Upgrade Notice ==

= 1.0.0 =
Initial release of PassWP Posts.
