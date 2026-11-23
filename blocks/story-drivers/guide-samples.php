<?php
/**
 * Block Guide samples for Story Drivers.
 *
 * Every sample uses Dynamic mode, so the previews show the site's latest posts.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p = 'ltt_dive_in_story_driver_';

$build = static function ( $layout, $extra = array() ) use ( $p ) {
	$sample = array(
		$p . 'layout'          => $layout,
		$p . 'mode'            => 'dynamic',
		$p . 'exclude_current' => 1,
	);

	if ( 'one_up' !== $layout ) {
		$sample[ $p . 'tag' ]     = __( 'Stories', 'ltt-dive-in' );
		$sample[ $p . 'heading' ] = __( 'Latest from the blog', 'ltt-dive-in' );
		$sample[ $p . 'intro' ]   = __( 'Trip ideas, local tips, and stories from around the lake.', 'ltt-dive-in' );
	}

	if ( in_array( $layout, array( 'two_up', 'three_up', 'four_up' ), true ) ) {
		$sample[ $p . 'primary_link' ]   = ltt_dive_in_block_guide_link( __( 'Read the blog', 'ltt-dive-in' ) );
		$sample[ $p . 'secondary_link' ] = ltt_dive_in_block_guide_link( __( 'Get the newsletter', 'ltt-dive-in' ) );
	}

	return array_merge( $sample, $extra );
};

return array(
	'one_up'    => $build( 'one_up', array( $p . 'count' => 3 ) ),
	'two_up'    => $build( 'two_up' ),
	'three_up'  => $build( 'three_up' ),
	'four_up'   => $build( 'four_up' ),
	'carousel'  => $build( 'carousel', array( $p . 'count' => 6 ) ),
	'hero_grid' => $build(
		'hero_grid',
		array(
			$p . 'count'      => 3,
			$p . 'grid_count' => 6,
		)
	),
);
