<?php
/**
 * Functions hooked into WordPress templates.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep the optional page card description as plain text when saved by ACF.
 *
 * @param mixed $value Submitted description.
 * @return string
 */
function ltt_dive_in_sanitize_page_card_description( $value ) {
	return is_string( $value ) ? sanitize_textarea_field( $value ) : '';
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_page_card_description', 'ltt_dive_in_sanitize_page_card_description' );

/**
 * Add useful classes to the body element.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function ltt_dive_in_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'ltt_dive_in_body_classes' );

/**
 * Add a pingback header for singular posts and pages.
 */
function ltt_dive_in_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'ltt_dive_in_pingback_header' );

/**
 * Remove responsive sources from SVG attachments.
 *
 * SVG plugins may store raster-style size metadata whose width descriptors all
 * point to the same vector file. Browsers then treat the SVG as a high-density
 * candidate and shrink its natural size, so vectors render without a srcset.
 *
 * @param array|false $sources       Source data keyed by width descriptor.
 * @param array       $size_array    Requested width and height values.
 * @param string      $image_src     Image source URL.
 * @param array       $image_meta    Attachment metadata.
 * @param int         $attachment_id Attachment ID.
 * @return array|false
 */
function ltt_dive_in_disable_svg_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	if ( 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
		return false;
	}

	return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'ltt_dive_in_disable_svg_srcset', 10, 5 );
