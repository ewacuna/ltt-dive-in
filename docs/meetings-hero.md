# Immersive Hero

The Immersive Hero is a page-hero variant for the Figma desktop reference (`1:10709`) and mobile review reference (`95:24749`). It can be selected on internal pages using the default page template or the Full Width template. Front page, posts page, and Blank Canvas do not use this variant.

## Editor setup

Choose **Immersive Hero** from **LTT Dive In — Page Header → Page hero design**. The page keeps its Standard hero unless the Immersive Hero option is explicitly selected. The choice reveals these optional fields:

- **Immersive Hero title**: uses the page title when blank. Plain text; line breaks are allowed, HTML is not.
- **Immersive Hero description**: uses the page excerpt when blank.
- **Immersive Hero icon (SVG)**: uses the supplied transparent SVG; otherwise the Figma artwork appears.

The page’s featured image always supplies the hero background.

The existing **LTT Dive In — Page Hero** primary and secondary links provide up to two optional glass buttons. The Scroll for more link targets the selected section navigation when one exists; otherwise it targets page content. The normal page title, excerpt, featured image, and button values remain unchanged when using the Standard hero.

## Rendering

`ltt_dive_in_get_meetings_hero_args( $post_id, $scroll_target )` resolves the override fields and fallbacks, and `template-parts/sections/meetings-hero.php` renders them. It validates the optional SVG attachment and limits buttons to two valid links. Both `page.php` and `templates/full-width.php` select this template part when the Page Header variant is Immersive Hero. Its stylesheet is enqueued only for that selection. Shared Alpine scroll-link behavior is already queued for internal page heroes.

```php
get_template_part( 'template-parts/sections/meetings-hero', null, ltt_dive_in_get_meetings_hero_args() );
```

The global theme setup supplies the shared glass buttons. Keep the hero selector and conditional fields in the PHP-owned Page Header field group; do not register the same fields in ACF Local JSON.

## SVG icon

The optional ACF field `ltt_dive_in_meetings_hero_icon` returns an SVG attachment ID and validates the MIME type. Safe SVG must be active to upload SVGs through the Media Library. Custom SVGs retain their own colors and transparency. Missing or invalid attachments use the desktop and mobile Figma artwork in `assets/images/hero/`.

## Layout

The desktop composition follows the 1440×844 reference; mobile follows the 375×757 composition and keeps the 301×449 vector dimensions. Both use the centered title, copy, optional glass CTAs, background photo, SVG art, radial navy wash, and optional scroll link. The default wash overlays the translucent Figma vector and sits behind the text, matching the exported layer order. A custom SVG stays above the wash so its supplied color and transparency are not altered. The grid reserves space for the scroll link and expands with longer copy so neither can overlap. `--meetings-hero-offset` accounts for the existing internal site-header height and the WordPress admin bar so the hero fills the remaining viewport height.
