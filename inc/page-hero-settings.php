<?php
/**
 * Internal page hero settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the internal page hero button fields.
 */
function ltt_dive_in_register_page_hero_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'         => 'group_ltt_dive_in_page_hero',
			'title'       => __( 'LTT Dive In — Page Hero', 'ltt-dive-in' ),
			'description' => __( 'The hero uses the title, excerpt, and featured image. Add up to two optional buttons here.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'key'           => 'field_ltt_dive_in_page_hero_primary_link',
					'label'         => __( 'Primary link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_hero_primary_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. The button is hidden until both a label and destination are provided.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
				array(
					'key'           => 'field_ltt_dive_in_page_hero_secondary_link',
					'label'         => __( 'Secondary link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_hero_secondary_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. The button is hidden until both a label and destination are provided.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
			),
			'location'    => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
					array(
						'param'    => 'page_type',
						'operator' => '!=',
						'value'    => 'front_page',
					),
					array(
						'param'    => 'page_template',
						'operator' => '!=',
						'value'    => 'templates/blank-canvas.php',
					),
				),
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
			),
			'position'    => 'acf_after_title',
			'style'       => 'default',
			'active'      => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_page_hero_settings' );

/**
 * Build the internal page hero arguments for the current post.
 *
 * @param string $scroll_target Fragment ID of the content following the hero.
 * @return array
 */
function ltt_dive_in_get_page_hero_args( $scroll_target = 'page-content' ) {
	$links = array();

	if ( function_exists( 'get_field' ) ) {
		$links = array(
			get_field( 'ltt_dive_in_page_hero_primary_link' ),
			get_field( 'ltt_dive_in_page_hero_secondary_link' ),
		);
	}

	return array(
		'title'         => get_the_title(),
		'heading_level' => 'h1',
		'text'          => has_excerpt() ? get_the_excerpt() : '',
		'image_id'      => get_post_thumbnail_id(),
		'links'         => $links,
		'scroll_target' => $scroll_target,
	);
}
