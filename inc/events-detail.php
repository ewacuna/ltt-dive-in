<?php
/**
 * Event detail view of the Events page template.
 *
 * Each Seeker event has a URL below the Events page, such as
 * `/events/music-on-the-beach-<uuid>/`. Events are not stored in WordPress:
 * the view reads the event from Seeker when it is requested.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the published pages that use the Events template, as `ID => path`.
 *
 * Cached in an option that is cleared whenever a page is saved or deleted.
 *
 * @return array<int, string>
 */
function ltt_dive_in_get_events_pages() {
	$pages = get_option( 'ltt_dive_in_events_pages' );

	if ( is_array( $pages ) ) {
		return $pages;
	}

	$pages = array();
	$ids   = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'posts_per_page'   => 10,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => 'templates/events.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'suppress_filters' => true,
		)
	);

	foreach ( $ids as $id ) {
		$pages[ $id ] = get_page_uri( $id );
	}

	update_option( 'ltt_dive_in_events_pages', $pages, true );

	return $pages;
}

/**
 * Clear the Events page cache and rebuild the detail rewrite rules.
 */
function ltt_dive_in_reset_events_pages() {
	delete_option( 'ltt_dive_in_events_pages' );
	update_option( 'ltt_dive_in_events_rewrite_flush', 1, true );
}
add_action( 'save_post_page', 'ltt_dive_in_reset_events_pages' );
add_action( 'deleted_post', 'ltt_dive_in_reset_events_pages' );
add_action( 'after_switch_theme', 'ltt_dive_in_reset_events_pages' );

/**
 * Register the event detail rewrite rules and query variable.
 */
function ltt_dive_in_register_event_detail_rewrites() {
	add_rewrite_tag( '%ltt_event%', '([a-z0-9-]+)' );

	foreach ( ltt_dive_in_get_events_pages() as $page_id => $path ) {
		add_rewrite_rule( '^' . preg_quote( $path, '#' ) . '/([a-z0-9-]+-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/?$', 'index.php?page_id=' . absint( $page_id ) . '&ltt_event=$matches[1]', 'top' );
	}

	if ( get_option( 'ltt_dive_in_events_rewrite_flush' ) ) {
		delete_option( 'ltt_dive_in_events_rewrite_flush' );
		flush_rewrite_rules( false );
	}
}
add_action( 'init', 'ltt_dive_in_register_event_detail_rewrites' );

/**
 * Return the URL of an event's own page.
 *
 * Falls back to the Seeker event website when no Events page is published.
 *
 * @param array $event Normalized event.
 * @return string
 */
function ltt_dive_in_get_event_permalink( $event ) {
	$pages = ltt_dive_in_get_events_pages();
	$uuid  = ltt_dive_in_sanitize_seeker_uuid( $event['id'] ?? '' );

	if ( ! $pages || ! $uuid ) {
		return isset( $event['url'] ) ? (string) $event['url'] : '';
	}

	$slug = trim( substr( sanitize_title( (string) ( $event['title'] ?? '' ) ), 0, 60 ), '-' );
	$slug = ( '' !== $slug ? $slug . '-' : 'event-' ) . $uuid;
	$base = get_permalink( array_key_first( $pages ) );

	if ( ! get_option( 'permalink_structure' ) ) {
		$url = add_query_arg( 'ltt_event', $slug, $base );
	} else {
		$url = user_trailingslashit( trailingslashit( $base ) . $slug );
	}

	$date = ltt_dive_in_sanitize_event_date( $event['date_context'] ?? '' );
	return $date ? add_query_arg( 'event_date', $date, $url ) : $url;
}

/**
 * Return the event shown by the current request.
 *
 * @return array{event: array, details: array}|null
 */
function ltt_dive_in_get_current_event() {
	static $current = false;

	if ( false !== $current ) {
		return $current;
	}

	$current = null;
	$slug    = (string) get_query_var( 'ltt_event' );

	if ( '' !== $slug && is_page() && ltt_dive_in_is_events_page() && preg_match( '/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/', $slug, $match ) ) {
		// Read-only date context must identify a real occurrence of this event.
		$date = isset( $_GET['event_date'] ) ? wp_unslash( $_GET['event_date'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current = ltt_dive_in_get_seeker_event_with_details( $match[1], $date );
	}

	return $current;
}

/**
 * Whether the current request is an event detail view.
 *
 * @return bool
 */
function ltt_dive_in_is_event_detail() {
	return null !== ltt_dive_in_get_current_event();
}

/**
 * Send unknown events to a 404 and outdated slugs to the current URL.
 * Historical occurrences require a valid event_date context.
 */
function ltt_dive_in_event_detail_redirect() {
	if ( '' === (string) get_query_var( 'ltt_event' ) || ! is_page() ) {
		return;
	}

	// Core would redirect an unknown event to its best guess, the Events page.
	remove_action( 'template_redirect', 'redirect_canonical' );

	$current = ltt_dive_in_get_current_event();

	if ( ! $current ) {
		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}

	$canonical = ltt_dive_in_get_event_permalink( $current['event'] );
	$requested = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';

	if ( get_option( 'permalink_structure' ) && untrailingslashit( $requested ) !== untrailingslashit( (string) wp_parse_url( $canonical, PHP_URL_PATH ) ) ) {
		wp_safe_redirect( $canonical, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'ltt_dive_in_event_detail_redirect', 5 );

/**
 * Use the event name as the document title.
 *
 * @param array $parts Title parts.
 * @return array
 */
function ltt_dive_in_event_detail_title( $parts ) {
	$current = ltt_dive_in_get_current_event();

	if ( $current ) {
		$parts['title'] = $current['event']['title'];
	}

	return $parts;
}
add_filter( 'document_title_parts', 'ltt_dive_in_event_detail_title' );

/**
 * Point the canonical link at the event URL instead of the Events page.
 *
 * @param string $url Canonical URL.
 * @return string
 */
function ltt_dive_in_event_detail_canonical( $url ) {
	$current = ltt_dive_in_get_current_event();

	return $current ? ltt_dive_in_get_event_permalink( $current['event'] ) : $url;
}
add_filter( 'get_canonical_url', 'ltt_dive_in_event_detail_canonical' );

/**
 * Print a description for search results and link previews.
 */
function ltt_dive_in_event_detail_meta() {
	$current = ltt_dive_in_get_current_event();

	if ( ! $current ) {
		return;
	}

	$description = ltt_dive_in_get_event_excerpt( $current['event'], 30 );

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'ltt_dive_in_event_detail_meta', 1 );

/**
 * Mark the detail view on the body element.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function ltt_dive_in_event_detail_body_class( $classes ) {
	if ( ltt_dive_in_is_event_detail() ) {
		$classes[] = 'is-event-detail';
	}

	return $classes;
}
add_filter( 'body_class', 'ltt_dive_in_event_detail_body_class' );

/**
 * Return the Events page blocks that also appear below an event.
 *
 * The detail view repeats the page's Page Drivers and End Page Cluster
 * blocks, such as the “See More” experiences; Event Drivers are left out
 * because the view shows its own related events.
 *
 * @param int $page_id Events page ID.
 * @return string Rendered blocks.
 */
function ltt_dive_in_get_event_detail_page_blocks( $page_id ) {
	$output = '';

	foreach ( parse_blocks( (string) get_post_field( 'post_content', $page_id ) ) as $block ) {
		if ( in_array( $block['blockName'] ?? '', array( 'ltt-dive-in/page-drivers', 'ltt-dive-in/end-page-cluster' ), true ) ) {
			$output .= render_block( $block );
		}
	}

	return $output;
}

/**
 * Return the Event Driver carousel settings for related events.
 *
 * Related events share the event's first category; events without a category
 * show other upcoming events.
 *
 * @param array $event Normalized event.
 * @return array
 */
function ltt_dive_in_get_event_detail_related_config( $event ) {
	$category = $event['categories'][0] ?? null;
	$config   = ltt_dive_in_get_event_driver_config(
		static function () {
			return null;
		}
	);

	return array_merge(
		$config,
		array(
			'variant'             => 'carousel',
			'tag'                 => $category ? $category['name'] : __( 'Events', 'ltt-dive-in' ),
			/* translators: %s: event category. */
			'heading'             => $category ? sprintf( __( 'See More %s Events', 'ltt-dive-in' ), $category['name'] ) : __( 'See More Events', 'ltt-dive-in' ),
			'carousel_count'      => 8,
			'carousel_categories' => $category && $category['id'] ? array( $category['id'] ) : array(),
			'exclude'             => $event['id'],
		)
	);
}
