# Meetings Hero

The Meetings Hero is a reusable template part for the Figma desktop reference (`1:10709`) and mobile review reference (`95:24749`). It is deliberately not assigned to a page or template yet.

## Rendering

Pass the template part `title`, `heading_level`, `text`, `image_id`, `links`, `scroll_target`, and optional `icon_id` arguments. `ltt_dive_in_get_meetings_hero_args( $post_id, $scroll_target )` builds these values from the post title, excerpt, featured image, the existing Page Hero primary/secondary link fields, and the optional Meetings Hero SVG field. Title text is escaped and may contain explicit line breaks; HTML is not supported. The renderer shows at most two valid links.

When the destination template is chosen, enqueue its stylesheet and the shared scroll behavior only from that template's condition. Use priority `5` so the scroll script is queued before Alpine initializes:

```php
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_page_template( 'templates/your-template.php' ) ) {
			ltt_dive_in_enqueue_meetings_hero_assets();
		}
	},
	5
);
```

The global theme setup supplies the shared glass buttons. The helper conditionally queues the shared scroll-link behavior; Alpine is already part of the theme's public assets.

## SVG icon

The optional ACF field `ltt_dive_in_meetings_hero_icon` returns an SVG attachment ID and validates the MIME type. Safe SVG must be active to upload SVGs through the Media Library. Custom SVGs retain their own colors and transparency. Missing or invalid attachments use the desktop and mobile Figma artwork in `assets/images/hero/`.

The field group has no location rule yet, so it is intentionally absent from editing screens. Add a narrow ACF location rule when the destination template is chosen. Keep the field group key and field key unchanged when doing so.

## Layout

The desktop composition follows the 1440×844 reference; mobile follows the 375×757 composition and keeps the 301×449 vector dimensions. Both use the centered title, copy, optional glass CTAs, background photo, SVG art, independent radial navy wash, and optional scroll link. The grid reserves space for the scroll link and expands with longer copy so neither can overlap. `--meetings-hero-offset` can account for a future header; its default is zero because no template is assigned.
