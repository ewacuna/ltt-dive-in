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
 * Register the versioned SVG setting for the reusable Meetings Hero.
 *
 * This group intentionally has no location rules until the component is
 * assigned to a page template.
 */
function ltt_dive_in_register_meetings_hero_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'          => 'group_ltt_dive_in_meetings_hero',
			'title'        => __( 'LTT Dive In — Meetings Hero', 'ltt-dive-in' ),
			'description'  => __( 'Reserved for the reusable Meetings Hero. This field group will appear after the component is assigned to a page template.', 'ltt-dive-in' ),
			'fields'       => array(
				array(
					'key'           => 'field_ltt_dive_in_meetings_hero_icon',
					'label'         => __( 'Hero icon (SVG)', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_meetings_hero_icon',
					'type'          => 'file',
					'instructions'  => __( 'Optional. Choose a transparent SVG from the Media Library. Its own colors and transparency are preserved.', 'ltt-dive-in' ),
					'required'      => 0,
					'return_format' => 'id',
					'library'       => 'all',
					'mime_types'    => 'svg',
				),
			),
			'location'     => array(),
			'menu_order'   => 0,
			'position'     => 'normal',
			'style'        => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'active'       => true,
			'show_in_rest' => 0,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_meetings_hero_settings' );

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

	$links   = array();
	$icon_id = 0;

	if ( function_exists( 'get_field' ) ) {
		$links = array(
			get_field( 'ltt_dive_in_page_hero_primary_link', $post_id ),
			get_field( 'ltt_dive_in_page_hero_secondary_link', $post_id ),
		);
		$icon_id = absint( get_field( 'ltt_dive_in_meetings_hero_icon', $post_id ) );
	}

	return array(
		'title'         => get_the_title( $post_id ),
		'heading_level' => 'h1',
		'text'          => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '',
		'image_id'      => get_post_thumbnail_id( $post_id ),
		'links'         => $links,
		'scroll_target' => $scroll_target,
		'icon_id'       => $icon_id,
	);
}

/**
 * Enqueue component styles and its shared scroll-link behavior.
 *
 * Call conditionally before the theme's global asset enqueue when a template
 * that renders the Meetings Hero is selected.
 */
function ltt_dive_in_enqueue_meetings_hero_assets() {
	$style_path       = LTT_DIVE_IN_DIR . '/assets/css/components/meetings-hero.css';
	$scroll_link_path = LTT_DIVE_IN_DIR . '/assets/js/components/scroll-link.js';

	wp_enqueue_style(
		'ltt-dive-in-meetings-hero',
		LTT_DIVE_IN_URI . '/assets/css/components/meetings-hero.css',
		array( 'ltt-dive-in-style', 'ltt-dive-in-buttons' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_script(
		'ltt-dive-in-scroll-link',
		LTT_DIVE_IN_URI . '/assets/js/components/scroll-link.js',
		array(),
		file_exists( $scroll_link_path ) ? (string) filemtime( $scroll_link_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => false,
		)
	);
}
