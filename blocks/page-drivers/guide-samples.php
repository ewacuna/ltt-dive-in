<?php
/**
 * Block Guide samples for Up Driver.
 *
 * Uses the site's published pages with featured images. The Monthly layout is
 * left out because it needs twelve pages classified by month.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p       = 'ltt_dive_in_page_driver_';
$pages   = ltt_dive_in_block_guide_posts( 'page', 8 );
$layouts = array(
	'one_up'      => 1,
	'two_up'      => 2,
	'three_up'    => 3,
	'four_up'     => 4,
	'five_up'     => 5,
	'six_plus_up' => 8,
);
$samples = array();

foreach ( $layouts as $layout => $count ) {
	if ( count( $pages ) < min( $count, 6 ) ) {
		continue;
	}

	$samples[ $layout ] = array(
		$p . 'layout'         => $layout,
		$p . 'heading'        => __( 'Plan your perfect day', 'ltt-dive-in' ),
		$p . 'surface'        => 'six_plus_up' === $layout ? 'dark' : 'light',
		$p . 'tag'            => __( 'Things to do', 'ltt-dive-in' ),
		$p . 'intro'          => __( 'Ideas for every kind of trip, from easy beach days to big mountain adventures.', 'ltt-dive-in' ),
		$p . 'button_text'    => __( 'Explore', 'ltt-dive-in' ),
		$p . 'show_excerpt'   => 1,
		$p . 'enable_toggles' => 0,
		$p . 'tiles'          => array_slice( $pages, 0, $count ),
	);
}

return $samples;
