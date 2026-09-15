<?php
/**
 * Page Drivers ACF block renderer.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = isset( $is_preview ) && $is_preview;
$block_id   = isset( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id( 'page-drivers-' );
$anchor     = isset( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : '';
$alignment  = isset( $block['align'] ) && 'full' === $block['align'] ? 'alignfull' : '';
$block_data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
$get_value  = static function ( $name ) use ( $block_data ) {
	$value = get_field( $name );

	return ( false === $value || null === $value ) && array_key_exists( $name, $block_data ) ? $block_data[ $name ] : $value;
};
$layout     = $get_value( 'ltt_dive_in_page_driver_layout' );
$heading    = $get_value( 'ltt_dive_in_page_driver_heading' );
$tile_ids   = $get_value( 'ltt_dive_in_page_driver_tiles' );
$layouts    = array( 'one_up', 'two_up', 'three_up', 'four_up', 'five_up', 'six_plus_up', 'monthly' );

if ( ! is_string( $layout ) || ! in_array( $layout, $layouts, true ) || ! is_string( $heading ) || ! trim( $heading ) || ! is_array( $tile_ids ) ) {
	if ( $is_preview ) { echo '<p>' . esc_html__( 'Choose a layout, heading, and hub pages to preview this Up Driver block.', 'ltt-dive-in' ) . '</p>'; }
	return;
}

$tiles = array();
foreach ( array_slice( array_values( array_filter( array_map( 'absint', $tile_ids ) ) ), 0, 12 ) as $tile_id ) {
	if ( 'publish' === get_post_status( $tile_id ) && 'page' === get_post_type( $tile_id ) && has_post_thumbnail( $tile_id ) ) {
		$tiles[] = array( 'id' => $tile_id, 'title' => get_the_title( $tile_id ), 'excerpt' => get_the_excerpt( $tile_id ), 'image' => get_post_thumbnail_id( $tile_id ) );
	}
}
if ( ! $tiles ) { return; }

$has_filters = (bool) $get_value( 'ltt_dive_in_page_driver_enable_toggles' );
$taxonomy    = $get_value( 'ltt_dive_in_page_driver_taxonomy' );
$term_ids    = $get_value( 'ltt_dive_in_page_driver_toggle_terms' );
$filters     = array();
if ( $has_filters && is_string( $taxonomy ) && taxonomy_exists( $taxonomy ) && is_array( $term_ids ) ) {
	foreach ( array_slice( array_values( array_filter( array_map( 'absint', $term_ids ) ) ), 0, 5 ) as $term_id ) { $term = get_term( $term_id, $taxonomy ); if ( $term && ! is_wp_error( $term ) ) { $filters[] = $term; } }
}
foreach ( $tiles as $index => $tile ) {
	$terms = $filters ? wp_get_object_terms( $tile['id'], $taxonomy, array( 'fields' => 'ids' ) ) : array();
	$tiles[ $index ]['terms'] = is_wp_error( $terms ) ? array() : array_map( 'absint', $terms );
}

get_template_part( 'template-parts/components/page-drivers/grid', null, array( 'id' => $anchor ? $anchor : 'page-drivers-' . $block_id, 'class_name' => $alignment, 'layout' => $layout, 'heading' => trim( $heading ), 'tag' => (string) $get_value( 'ltt_dive_in_page_driver_tag' ), 'intro' => (string) $get_value( 'ltt_dive_in_page_driver_intro' ), 'show_excerpt' => (bool) $get_value( 'ltt_dive_in_page_driver_show_excerpt' ), 'tiles' => $tiles, 'filters' => $filters ) );
