<?php
/**
 * Block Guide samples for Activities.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = ltt_dive_in_block_guide_posts( 'page', 4 );

if ( count( $items ) < 4 ) {
	$items = array_slice( array_merge( $items, ltt_dive_in_block_guide_posts( 'post', 4 ) ), 0, 4 );
}

if ( count( $items ) < 4 ) {
	return array();
}

return array(
	'default' => array(
		'ltt_dive_in_activities_heading'        => __( 'Things to do this season', 'ltt-dive-in' ),
		'ltt_dive_in_activities_copy'           => __( 'A few of our favorite ways to spend a day at the lake.', 'ltt-dive-in' ),
		'ltt_dive_in_activities_tag'            => __( 'Explore', 'ltt-dive-in' ),
		'ltt_dive_in_activities_primary_link'   => ltt_dive_in_block_guide_link( __( 'See all activities', 'ltt-dive-in' ) ),
		'ltt_dive_in_activities_secondary_link' => ltt_dive_in_block_guide_link( __( 'Plan your trip', 'ltt-dive-in' ) ),
		'ltt_dive_in_activities_items'          => $items,
	),
);
