# LTT Dive In

The classic PHP WordPress theme for the Lake Tahoe Travel site, with block-editor support through `theme.json` and dynamic ACF blocks. It has no build pipeline: WordPress loads the source files in `assets/` directly.

## Requirements

- WordPress 6.6 or newer
- PHP 8.0 or newer
- Advanced Custom Fields Pro (ACF Pro)
- FacetWP (for Listing Module dropdown filters, search, and Load More)
- Gravity Forms paid version

External services, configured in WordPress Admin:

- [OpenWeather](https://openweathermap.org/) for the header weather and the Current Conditions weather pages
- [Seeker Events API](https://seeker.io/apidocs/events/) for the Event Driver block
- [Gemini API](https://ai.google.dev/) for editor-generated AI summaries on blog posts

API keys are used only by the server and are never sent to the browser.

## Install

1. Put this directory at `wp-content/themes/ltt-dive-in` in a WordPress installation.
2. Install and activate ACF Pro. Field groups used by the theme must be synchronized from the version-controlled Local JSON in `acf-json/` or the PHP definitions in `inc/`.
3. Install and activate FacetWP and configure the Listing Module facets.
4. Install and activate the paid version of Gravity Forms. Import or create the approved forms and review their notifications, confirmations, consent, retention, spam protection, and integrations for the current environment.
5. In WordPress Admin, open **Appearance → Themes** and activate **LTT Dive In**.
6. Create and assign the Primary Menu, Top Header, Footer Navigation, and Footer Legal locations under **Appearance → Menus**.
7. Set a homepage and posts page under **Settings → Reading** if desired.
8. Configure the logo and footer text under **Appearance → Customize**.
9. Add the API keys under **Settings → Weather** (OpenWeather), **Settings → Events** (Seeker), and **Settings → AI Summaries** (Gemini). The Seeker key may instead be defined as `LTT_DIVE_IN_SEEKER_API_KEY` in `wp-config.php`.
10. Set the optional current-conditions link for the header weather under **Appearance → Header Settings**.
11. Configure the FAQ link, newsletter copy and form, footer logos, partner-logo columns, and social URLs under **Appearance → Footer Settings**.
12. Edit the front page to update the **LTT Dive In — Homepage Hero** field group.

Before launch, review the attribution terms for the active OpenWeather plan. Self-service plans may require visible OpenWeather text, a hyperlink, and the provider logo.

## Structure

```text
ltt-dive-in/
├── acf-json/          # Versioned ACF field groups (Local JSON)
├── assets/
│   ├── css/           # main.css foundations, components/, templates/, vendor/
│   ├── fonts/         # Self-hosted Gotham webfonts
│   ├── js/            # main.js, components/, admin/, vendor/ (Alpine, Swiper)
│   └── vendor/        # PhotoSwipe
├── blocks/            # Dynamic ACF blocks and their block.json metadata
├── inc/               # Theme setup, enqueueing, settings, integrations, and helpers
├── template-parts/    # Reusable markup for blocks, components, content, pages, and sections
├── templates/         # Selectable page templates
├── functions.php      # Theme bootstrap
├── header.php / footer.php
├── theme.json         # Editor settings and design tokens
└── style.css          # Theme metadata only
```
