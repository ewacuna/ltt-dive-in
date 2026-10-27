<?php
/**
 * Meetings Hero settings and integration helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate that the optional Meetings Hero icon points to an SVG attachment.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted attachment ID.
 * @return bool|string
 */
function ltt_dive_in_validate_meetings_hero_icon( $valid, $value ) {
	if ( true !== $valid || empty( $value ) ) {
		return $valid;
	}

	$attachment_id = absint( $value );

	if ( ! $attachment_id || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return __( 'Choose an SVG file from the Media Library for this icon.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_meetings_hero_icon', 'ltt_dive_in_validate_meetings_hero_icon', 10, 2 );

/**
 * Build the Meetings Hero arguments from native post content and current hero CTAs.
 *
 * @param int         $post_id      Post used for title, excerpt, image and fields.
 * @param string|null $scroll_target Fragment ID following the hero. Defaults to the
 *                                    section navigation when present, otherwise page content.
 * @return array
 */
function ltt_dive_in_get_meetings_hero_args( $post_id = 0, $scroll_target = null ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	if ( null === $scroll_target ) {
		$scroll_target = ltt_dive_in_get_section_navigation() ? 'section-navigation' : 'page-content';
	}

	$links    = array();
	$icon_id  = 0;
	$title    = get_the_title( $post_id );
	$text     = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '';
	$image_id = get_post_thumbnail_id( $post_id );

	if ( function_exists( 'get_field' ) ) {
		$title_override = get_field( 'ltt_dive_in_meetings_hero_title', $post_id );
		$text_override  = get_field( 'ltt_dive_in_meetings_hero_text', $post_id );

		if ( is_string( $title_override ) && '' !== trim( $title_override ) ) {
			$title = $title_override;
		}

		if ( is_string( $text_override ) && '' !== trim( $text_override ) ) {
			$text = $text_override;
		}

		$links = array(
			get_field( 'ltt_dive_in_page_hero_primary_link', $post_id ),
			get_field( 'ltt_dive_in_page_hero_secondary_link', $post_id ),
		);
		$icon_id = absint( get_field( 'ltt_dive_in_meetings_hero_icon', $post_id ) );
	}

	return array(
		'title'         => $title,
		'heading_level' => 'h1',
		'text'          => $text,
		'image_id'      => $image_id,
		'links'         => $links,
		'scroll_target' => $scroll_target,
		'icon_id'       => $icon_id,
	);
}

/**
 * Enqueue the Meetings Hero component stylesheet.
 *
 * The regular theme enqueue registers shared scroll behavior for all page heroes.
 */
function ltt_dive_in_enqueue_meetings_hero_assets() {
	$style_path       = LTT_DIVE_IN_DIR . '/assets/css/components/meetings-hero.css';

	wp_enqueue_style(
		'ltt-dive-in-meetings-hero',
		LTT_DIVE_IN_URI . '/assets/css/components/meetings-hero.css',
		array( 'ltt-dive-in-style', 'ltt-dive-in-buttons' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);
}
