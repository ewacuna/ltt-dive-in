<?php
/**
 * Block Guide samples for Video Module.
 *
 * A video needs a real source, so previews use the first MP4 in the Media
 * Library. Without one, this block shows no preview.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video = ltt_dive_in_block_guide_video();

if ( ! $video ) {
	return array();
}

$p = 'ltt_dive_in_video_module_';

$item = static function ( $index, $title, $tag, $body ) use ( $video ) {
	return array(
		'source'   => 'self_hosted',
		'mp4'      => $video,
		'poster'   => ltt_dive_in_block_guide_photo( $index ),
		'tag'      => $tag,
		'duration' => __( '2 min', 'ltt-dive-in' ),
		'title'    => $title,
		'body'     => $body,
		'cta'      => ltt_dive_in_block_guide_link( __( 'Plan your trip', 'ltt-dive-in' ) ),
	);
};

$items = array(
	$item( 0, __( 'A morning on Emerald Bay', 'ltt-dive-in' ), __( 'Summer', 'ltt-dive-in' ), __( 'Paddle out before the wind picks up and have the bay almost to yourself.', 'ltt-dive-in' ) ),
	$item( 1, __( 'First tracks at sunrise', 'ltt-dive-in' ), __( 'Winter', 'ltt-dive-in' ), __( 'What a powder day looks like from the top of the mountain.', 'ltt-dive-in' ) ),
	$item( 2, __( 'Golden aspens on the Tahoe Rim Trail', 'ltt-dive-in' ), __( 'Fall', 'ltt-dive-in' ), __( 'An easy hike with some of the best fall colors around the lake.', 'ltt-dive-in' ) ),
	$item( 3, __( 'Dinner with a view', 'ltt-dive-in' ), __( 'Summer', 'ltt-dive-in' ), __( 'Three lakefront restaurants worth booking ahead.', 'ltt-dive-in' ) ),
);

return array(
	'editorial'       => array(
		$p . 'variant' => 'editorial',
		$p . 'items'   => array_slice( $items, 0, 1 ),
	),
	'full_bleed'      => array(
		$p . 'variant' => 'full_bleed',
		$p . 'items'   => array_slice( $items, 0, 1 ),
	),
	'full_bleed_copy' => array(
		$p . 'variant' => 'full_bleed_copy',
		$p . 'items'   => array_slice( $items, 0, 1 ),
	),
	'carousel'        => array(
		$p . 'variant' => 'carousel',
		$p . 'items'   => $items,
	),
	'episodic'        => array(
		$p . 'variant'  => 'episodic',
		$p . 'eyebrow'  => __( 'Video series', 'ltt-dive-in' ),
		$p . 'heading'  => __( 'Tahoe in four seasons', 'ltt-dive-in' ),
		$p . 'intro'    => __( 'Short videos about what makes each season at the lake special.', 'ltt-dive-in' ),
		$p . 'items'    => $items,
	),
);
