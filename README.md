# PassWP Posts

A WordPress plugin for password protecting your site's content. Protect all pages and posts or only selected ones—logged-in users always bypass.

## Features

- **Single Password Protection**: Set one password for your entire site (no username required)
- **Front Page Bypass**: The front page is always accessible without a password
- **Logged-in User Bypass**: Authenticated users skip the password prompt
- **Protection Modes**: Protect all content (with exclusions) or only selected pages/posts
- **Customizable Password Form**: Personalize colors, typography, logo, and more with live preview
- **Preset Themes**: Choose from built-in themes (Default Purple, Business Blue, Dark Mode)
- **Remember Me**: Visitors stay authenticated for a configurable duration
- **Secure Cookie Handling**: Uses SHA256 hashing with WordPress salts for cookie security
- **Native WordPress Methods**: Built using WordPress Settings API and password functions



## Installation


- Download [`passwp-posts.zip`](https://github.com/soderlind/passwp-posts/releases/latest/download/passwp-posts.zip)
- Upload via  Plugins > Add New > Upload Plugin
- Activate the plugin.


Plugin [updates are handled automatically](https://github.com/soderlind/wordpress-plugin-github-updater#readme) via GitHub. No need to manually download and install updates.

## Configuration

Go to **Settings → PassWP Posts** to configure the plugin.

### General Settings

| Setting | Description |
|---------|-------------|
| **Enable Protection** | Toggle password protection on/off |
| **Password** | The password visitors must enter to access protected content |
| **Remember Me Duration** | Number of days to remember the password (default: 30) |
| **Protection Mode** | Choose to protect all pages/posts or only selected ones |
| **Excluded Pages/Posts** | When protecting all: select pages/posts to exclude |
| **Protected Pages/Posts** | When protecting selected: choose which pages/posts to protect |

### Customize Settings

| Setting | Description |
|---------|-------------|
| **Preset Themes** | Quick-apply themes: Default Purple, Business Blue, Dark Mode |
| **Background** | Background color, gradient, and optional background image |
| **Card Styling** | Card background color, border radius, and shadow |
| **Logo** | Upload a custom logo with adjustable width |
| **Typography** | Heading text, colors, and font family |
| **Button** | Button text, colors, and border radius |
| **Form Options** | Show/hide "Remember Me" checkbox, input border radius |
| **Footer** | Custom footer text and link URL |

## How It Works

1. When a visitor tries to access a protected page or post, they are shown a password form
2. If they enter the correct password, a secure cookie is set
3. The cookie allows them to browse freely for the configured duration
4. Logged-in users are never prompted for a password (so **use incognito or private browsing mode to test protection**).
5. The front page is always public regardless of protection mode

## Security

- Passwords are stored using `wp_hash_password()` (same as WordPress user passwords)
- Cookies contain a SHA256 hash of the password hash combined with `wp_salt('auth')`
- All form submissions are protected with WordPress nonces
- Admin actions require `manage_options` capability

## Development

### Requirements

- PHP 8.3+
- WordPress 6.8+

### Running Tests

**PHP Tests (PHPUnit with Brain\Monkey):**

```bash
composer install
composer test
```

**JavaScript Tests (Vitest):**

```bash
npm install
npm test
```

### Building Translations

```bash
# Generate POT file
wp i18n make-pot . languages/passwp-posts.pot

# Generate MO file from PO
wp i18n make-mo languages/passwp-posts-nb_NO.po

# Generate PHP translation file (WordPress 6.5+)
wp i18n make-php languages
```

## Directory Structure

```
passwp-posts/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   ├── customize-admin.css
│   │   └── password-form.css
│   ├── js/
│   │   ├── admin.js
│   │   └── customize.js
│   └── vendor/
│       └── select2/
├── includes/
│   ├── class-admin-settings.php
│   ├── class-cookie-handler.php
│   └── class-protection.php
├── languages/
│   ├── passwp-posts.pot
│   ├── passwp-posts-nb_NO.po
│   ├── passwp-posts-nb_NO.mo
│   └── passwp-posts-nb_NO.l10n.php
├── templates/
│   └── password-form.php
├── tests/
│   ├── js/
│   └── php/
├── passwp-posts.php
├── composer.json
├── package.json
└── README.md
```

## Hooks

### Filters

```php
// Modify the password form template path
add_filter( 'passwp_posts_form_template', function( $template ) {
    return get_stylesheet_directory() . '/passwp-posts-form.php';
});
```

## Translations

The plugin is translation-ready with the text domain `passwp-posts`. Available translations:

- English (default)
- Norwegian Bokmål (nb_NO)

## License

GPL v2 or later

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed list of changes.
