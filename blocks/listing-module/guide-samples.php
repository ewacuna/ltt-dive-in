<?php
/**
 * Block Guide samples for Listing Module.
 *
 * Picks the content type and the FacetWP facets this site has, using the same
 * checks the block uses, so the preview works in every environment.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ltt_dive_in_listing_module_post_types' ) ) {
	return array();
}

$facets = function_exists( 'FWP' ) ? (array) FWP()->helper->get_facets() : array();
$best   = null;

foreach ( array_keys( ltt_dive_in_listing_module_post_types() ) as $post_type ) {
	if ( ! wp_count_posts( $post_type )->publish ) {
		continue;
	}

	$dropdowns = array();

	foreach ( $facets as $facet ) {
		$name = isset( $facet['name'] ) ? (string) $facet['name'] : '';

		if ( ltt_dive_in_listing_module_get_dropdown_facet( $name, $post_type ) ) {
			$dropdowns[] = $name;
		}
	}

	// Prefer the content type with the most filters; on a tie keep the first.
	if ( null === $best || count( $dropdowns ) > count( $best['dropdowns'] ) ) {
		$best = array(
			'post_type' => $post_type,
			'dropdowns' => $dropdowns,
		);
	}
}

if ( ! $best ) {
	return array();
}

$search = '';
$pager  = '';

foreach ( $facets as $facet ) {
	$name = isset( $facet['name'] ) ? (string) $facet['name'] : '';

	if ( ! $search && ltt_dive_in_listing_module_get_search_facet( $name ) ) {
		$search = $name;
	}

	if ( ! $pager && ltt_dive_in_listing_module_get_load_more_facet( $name ) ) {
		$pager = $name;
	}
}

$p = 'ltt_dive_in_listing_module_';

return array(
	'default' => array(
		$p . 'eyebrow'         => __( 'Explore', 'ltt-dive-in' ),
		$p . 'heading'         => __( 'Find your next stop', 'ltt-dive-in' ),
		$p . 'intro'           => __( 'Filter by area or type, or search by name.', 'ltt-dive-in' ),
		$p . 'source'          => $best['post_type'],
		$p . 'facet_one'       => $best['dropdowns'][0] ?? '',
		$p . 'facet_two'       => $best['dropdowns'][1] ?? '',
		$p . 'facet_three'     => $best['dropdowns'][2] ?? '',
		$p . 'search_facet'    => $search,
		$p . 'pager_facet'     => $pager,
		$p . 'initial_count'   => 6,
		$p . 'load_more_step'  => 3,
	),
);
