<?php
/**
 * Block Guide samples for Static Image Cluster.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p = 'ltt_dive_in_static_image_cluster_';

$build = static function ( $variant, $count, $with_header = true ) use ( $p ) {
	$sample = array(
		$p . 'variant' => $variant,
		$p . 'images'  => ltt_dive_in_block_guide_photos( $count, 10 ),
	);

	if ( $with_header ) {
		$sample[ $p . 'heading' ]        = __( 'A weekend on the West Shore', 'ltt-dive-in' );
		$sample[ $p . 'intro' ]          = __( '<p>Historic estates, quiet beaches, and the best sunsets on the lake.</p>', 'ltt-dive-in' );
		$sample[ $p . 'primary_link' ]   = ltt_dive_in_block_guide_link( __( 'Plan your visit', 'ltt-dive-in' ) );
		$sample[ $p . 'secondary_link' ] = ltt_dive_in_block_guide_link( __( 'See the map', 'ltt-dive-in' ) );
	}

	return $sample;
};

$inspired_titles = array(
	__( 'Sunset pier', 'ltt-dive-in' ),
	__( 'Tahoe City', 'ltt-dive-in' ),
	__( 'Flying in', 'ltt-dive-in' ),
	__( 'Holiday shops', 'ltt-dive-in' ),
	__( 'Lake view dinner', 'ltt-dive-in' ),
	__( 'Thunderbird Lodge', 'ltt-dive-in' ),
);

$inspired           = $build( 'inspired', 6, false );
$inspired[ $p . 'mobile_display' ] = 'lightbox';
$inspired[ $p . 'image_details' ]  = array();

foreach ( $inspired_titles as $title ) {
	$inspired[ $p . 'image_details' ][] = array(
		'title'       => $title,
		'description' => __( 'A short note about the photo, shown when it is opened.', 'ltt-dive-in' ),
	);
}

$hero                = $build( 'hero_caption', 1, false );
$hero[ $p . 'intro' ] = __( '<p>Emerald Bay from the Vikingsholm trail, early on a summer morning.</p>', 'ltt-dive-in' );

return array(
	'hero_caption'   => $hero,
	'one_up'         => $build( 'one_up', 1 ),
	'two_up'         => $build( 'two_up', 2 ),
	'three_up'       => $build( 'three_up', 3 ),
	'inline_four_up' => $build( 'inline_four_up', 4 ),
	'staggered_four' => $build( 'staggered_four', 4 ),
	'five_plus'      => $build( 'five_plus', 7 ),
	'side_by_side'   => $build( 'side_by_side', 1 ),
	'inspired'       => $inspired,
);
