<?php
/**
 * Current Conditions template helpers: one shared hero and in-place tab navigation.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a page uses the Current Conditions template.
 *
 * @param int $post_id Page ID. Defaults to the current queried page.
 * @return bool
 */
function ltt_dive_in_is_current_conditions_page( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_queried_object_id();

	return $post_id && 'templates/current-conditions.php' === get_page_template_slug( $post_id );
}

/**
 * Resolve the page that owns the shared Current Conditions hero.
 *
 * A child of another Current Conditions page shows its parent's hero, so every tab keeps the same hero.
 *
 * @param int $post_id Page ID. Defaults to the current queried page.
 * @return int Parent page ID for a tab, otherwise the page itself.
 */
function ltt_dive_in_get_current_conditions_hub_id( $post_id = 0 ) {
	$post_id   = $post_id ? absint( $post_id ) : get_queried_object_id();
	$parent_id = (int) wp_get_post_parent_id( $post_id );

	return $parent_id && ltt_dive_in_is_current_conditions_page( $parent_id ) ? $parent_id : $post_id;
}

/**
 * Build the hero arguments for a Current Conditions page.
 *
 * Tabs reuse the parent's hero content as a non-heading title; their own title is the page H1 in the intro.
 *
 * @return array
 */
function ltt_dive_in_get_current_conditions_hero_args() {
	$args   = ltt_dive_in_get_page_hero_args();
	$hub_id = ltt_dive_in_get_current_conditions_hub_id();

	if ( get_queried_object_id() === $hub_id ) {
		return $args;
	}

	$links = array();
	if ( function_exists( 'get_field' ) ) {
		$links = array(
			get_field( 'ltt_dive_in_page_hero_primary_link', $hub_id ),
			get_field( 'ltt_dive_in_page_hero_secondary_link', $hub_id ),
		);
	}

	return array_merge(
		$args,
		array(
			'title'         => get_the_title( $hub_id ),
			'heading_level' => 'p',
			'text'          => has_excerpt( $hub_id ) ? get_the_excerpt( $hub_id ) : '',
			'image_id'      => get_post_thumbnail_id( $hub_id ),
			'links'         => $links,
		)
	);
}
