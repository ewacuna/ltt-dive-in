<?php
/**
 * Activities block validation and tile data adapters.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ltt_dive_in_get_activity_tile_from_post( $post_id ) {
	$post_id = absint( $post_id );

	if ( ! $post_id || 'publish' !== get_post_status( $post_id ) || ! in_array( get_post_type( $post_id ), array( 'page', 'post' ), true ) || ! has_post_thumbnail( $post_id ) ) {
		return array();
	}

	return array(
		'title'       => get_the_title( $post_id ),
		'description' => get_the_excerpt( $post_id ),
		'url'         => get_permalink( $post_id ),
		'image_id'    => get_post_thumbnail_id( $post_id ),
		'cta_label'   => __( 'Explore', 'ltt-dive-in' ),
	);
}

function ltt_dive_in_validate_activity_items( $valid, $value ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$items = is_array( $value ) ? array_values( array_filter( array_map( 'absint', $value ) ) ) : array();

	if ( 4 !== count( $items ) ) {
		return __( 'Select exactly four activities.', 'ltt-dive-in' );
	}

	foreach ( $items as $item_id ) {
		if ( ! ltt_dive_in_get_activity_tile_from_post( $item_id ) ) {
			return __( 'Every activity must be a published page or post with a featured image.', 'ltt-dive-in' );
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_activities_items', 'ltt_dive_in_validate_activity_items', 10, 2 );
