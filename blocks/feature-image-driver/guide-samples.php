<?php
/**
 * Block Guide samples for Feature Image Driver.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p = 'ltt_dive_in_feature_image_driver_';

$items = array(
	array(
		'enabled'            => 1,
		'tag'                => __( 'Winter', 'ltt-dive-in' ),
		'image'              => ltt_dive_in_block_guide_photo( 0 ),
		'title'              => __( 'Ski season starts here', 'ltt-dive-in' ),
		'show_body'          => 1,
		'body'               => __( 'More than a dozen resorts, from big mountains to small local hills.', 'ltt-dive-in' ),
		'link'               => ltt_dive_in_block_guide_link( __( 'Plan a ski trip', 'ltt-dive-in' ) ),
		'short_primary_link' => ltt_dive_in_block_guide_link( __( 'See resorts', 'ltt-dive-in' ) ),
	),
	array(
		'enabled'   => 1,
		'tag'       => __( 'Summer', 'ltt-dive-in' ),
		'image'     => ltt_dive_in_block_guide_photo( 1 ),
		'title'     => __( 'Long days on the water', 'ltt-dive-in' ),
		'show_body' => 1,
		'body'      => __( 'Paddleboard, kayak, or just find a quiet beach.', 'ltt-dive-in' ),
		'link'      => ltt_dive_in_block_guide_link( __( 'Find a beach', 'ltt-dive-in' ) ),
	),
	array(
		'enabled'   => 1,
		'tag'       => __( 'Fall', 'ltt-dive-in' ),
		'image'     => ltt_dive_in_block_guide_photo( 2 ),
		'title'     => __( 'Golden aspens and empty trails', 'ltt-dive-in' ),
		'show_body' => 1,
		'body'      => __( 'The quietest and maybe the prettiest time of year.', 'ltt-dive-in' ),
		'link'      => ltt_dive_in_block_guide_link( __( 'See fall hikes', 'ltt-dive-in' ) ),
	),
	array(
		'enabled'   => 1,
		'image'     => ltt_dive_in_block_guide_photo( 3 ),
		'title'     => __( 'Spring skiing in a T-shirt', 'ltt-dive-in' ),
		'show_body' => 1,
		'body'      => __( 'Sunny days and soft snow through April.', 'ltt-dive-in' ),
		'link'      => ltt_dive_in_block_guide_link( __( 'Spring deals', 'ltt-dive-in' ) ),
	),
);

return array(
	'single'   => array(
		$p . 'variant' => 'single',
		$p . 'items'   => array_slice( $items, 0, 1 ),
	),
	'carousel' => array(
		$p . 'variant' => 'carousel',
		$p . 'items'   => array_slice( $items, 0, 3 ),
	),
	'short'    => array(
		$p . 'variant' => 'short',
		$p . 'items'   => array_slice( $items, 0, 1 ),
	),
	'stacked'  => array(
		$p . 'variant'                   => 'stacked',
		$p . 'stacked_tag'               => __( 'All year', 'ltt-dive-in' ),
		$p . 'stacked_heading'           => __( 'Tahoe in every season', 'ltt-dive-in' ),
		$p . 'stacked_intro'             => __( 'There is no bad time to visit. Here is what each season brings.', 'ltt-dive-in' ),
		$p . 'stacked_primary_link'      => ltt_dive_in_block_guide_link( __( 'Plan your trip', 'ltt-dive-in' ) ),
		$p . 'stacked_secondary_link'    => ltt_dive_in_block_guide_link( __( 'See events', 'ltt-dive-in' ) ),
		$p . 'stacked_mobile_background' => 'light',
		$p . 'items'                     => $items,
	),
);
