# LTT Dive In

A PHP-based WordPress theme starter with reusable template parts and modern block-editor support.

## Requirements

- WordPress 6.6 or newer
- PHP 8.0 or newer
- Advanced Custom Fields Pro (ACF Pro)
- Gravity Forms paid version

## Install

1. Put this directory at `wp-content/themes/ltt-dive-in` in a WordPress installation.
2. Install and activate ACF Pro. Field groups used by implemented templates must be synchronized from the version-controlled Local JSON or PHP definitions included with the theme.
3. Install and activate the paid version of Gravity Forms. Import or create the approved forms and review their notifications, confirmations, consent, retention, spam protection, and integrations for the current environment.
4. In WordPress Admin, open **Appearance → Themes** and activate **LTT Dive In**.
5. Create and assign the Primary Menu, Top Header, Footer Navigation, and Footer Legal locations under **Appearance → Menus**.
6. Set a homepage and posts page under **Settings → Reading** if desired.
7. Configure the logo and footer text under **Appearance → Customize**.
8. Configure the weather label and optional current-conditions link under **Appearance → Header Settings**.
9. Edit the front page to update the homepage title, video, poster, description, CTA links, and scroll label in the **LTT Dive In — Homepage Hero** field group.
10. Configure the FAQ link, newsletter copy, and social URLs under **Appearance → Footer Settings**.
11. Select the production Gravity Form under **Appearance → Footer Settings**. A widget or the `ltt_dive_in_footer_newsletter_form` hook remains available as a fallback for another provider.

## Structure

```text
ltt-dive-in/
├── assets/
│   ├── css/main.css          # Global front-end and editor foundations
│   ├── css/components/       # Shared component styles, when needed
│   ├── css/templates/        # Template-specific styles, when needed
│   ├── fonts/                # Self-hosted webfonts
│   ├── js/main.js            # Small non-Alpine progressive enhancements
│   └── js/vendor/            # Versioned third-party browser libraries
├── acf-json/                 # Versioned ACF field groups, when introduced
├── inc/
│   ├── setup.php             # Theme support, menus, widget areas
│   ├── enqueue.php           # Scripts and styles
│   ├── header-settings.php    # Versioned ACF header and homepage hero settings
│   ├── template-tags.php     # Reusable display helpers
│   ├── template-functions.php
│   ├── footer-settings.php    # Versioned ACF footer settings and helpers
│   └── customizer.php
├── template-parts/
│   ├── content/              # Post/list states
│   └── page/                 # Page content sections
├── templates/                # Selectable page templates
├── functions.php             # Theme bootstrap
├── header.php / footer.php   # Shared site chrome
├── index.php                 # Required WordPress fallback
└── theme.json                # Editor settings and design tokens
```

## Adding a template part

The theme includes `template-parts/sections/hero.php`. Render it from any template:

```php
<?php
get_template_part(
	'template-parts/sections/hero',
	null,
	array(
		'title' => __( 'Dive in', 'ltt-dive-in' ),
	)
);
```

Inside the part, values are available from `$args`:

```php
<section class="hero">
	<h2><?php echo esc_html( $args['title'] ?? '' ); ?></h2>
</section>
```

## Adding a page template

Copy `templates/full-width.php`, change its `Template Name` header, and customize its markup. It will become selectable in the page editor.

## Development notes

- Prefix PHP functions, option names, handles, and hooks with `ltt_dive_in_`.
- Escape values when outputting them and sanitize saved values.
- Keep only shared foundations in `assets/css/main.css`; put unique page layouts in `assets/css/templates/` and reusable component styles in `assets/css/components/`.
- Build responsive CSS desktop-first using the documented Bootstrap 5.3 breakpoint values and descending `max-width` queries; Bootstrap itself is not a theme dependency.
- Use WordPress APIs for assets, menus, URLs, content, and translations.
- Prefer native WordPress content fields where they fit. Use ACF Pro for structured design modules, and version every field group consumed by the theme.
- Use Gravity Forms for visitor submissions. Keep its form configuration portable, never commit entries or secrets, and preserve its validation and accessible markup when styling it.
- Use Alpine for small, markup-owned interaction states. Keep content and links in the server-rendered HTML, provide a useful pre-initialization state, and do not move substantial business logic into `x-data` expressions.
- See `AGENTS.md` for the complete ACF, Gravity Forms, accessibility, CSS ownership, and implementation rules.

## Alpine.js

The theme includes Alpine.js `3.17.2` at `assets/js/vendor/alpine.min.js` with its MIT license. WordPress loads the local file from the document head with the non-render-blocking `defer` strategy recommended by Alpine; do not add an additional `unpkg` or other CDN script tag to `header.php`.

The local copy avoids an extra third-party connection, prevents an unpinned CDN URL from changing unexpectedly, and keeps page content server-rendered for crawling and no-JavaScript fallbacks. Alpine currently owns the mobile-navigation and header-search states, plus the homepage video's reduced-motion safeguard. The remaining `assets/js/main.js` behavior is kept for footer DOM ordering because it is not a simple component-local state.

When upgrading Alpine:

1. Confirm the desired production version in the official Alpine release notes.
2. Replace `assets/js/vendor/alpine.min.js` with that exact version's `dist/cdn.min.js` and update `assets/js/vendor/ALPINE-LICENSE.md` if needed.
3. Update the version passed to the `ltt-dive-in-alpine` handle in `inc/enqueue.php`.
4. Retest the mobile menu with keyboard and Escape, focus restoration, viewport changes, header-search states, and the homepage video's reduced-motion behavior.
