<?php
/**
 * Block Guide samples for End Page Cluster.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p      = 'ltt_dive_in_end_page_cluster_';
$trail  = array();
$stories = ltt_dive_in_block_guide_posts( 'post', 4 );
$places  = ltt_dive_in_block_guide_posts( 'page', 3 );

foreach ( $stories as $index => $story_id ) {
	$trail[] = array(
		'type'  => 'story',
		'story' => $story_id,
	);

	if ( isset( $places[ $index ] ) ) {
		$trail[] = array(
			'type'  => 'place',
			'place' => $places[ $index ],
		);
	}
}

$samples = array(
	'copy_cta'    => array(
		$p . 'variant'          => 'copy_cta',
		$p . 'background_color' => '',
		$p . 'callouts'         => array(
			array(
				'tag'           => __( 'Stay', 'ltt-dive-in' ),
				'title'         => __( 'Find a place to stay', 'ltt-dive-in' ),
				'copy'          => __( 'Cabins, lakefront resorts, and cozy inns all around the lake.', 'ltt-dive-in' ),
				'primary_cta'   => ltt_dive_in_block_guide_link( __( 'Browse lodging', 'ltt-dive-in' ) ),
				'secondary_cta' => ltt_dive_in_block_guide_link( __( 'See deals', 'ltt-dive-in' ) ),
			),
			array(
				'tag'         => __( 'Plan', 'ltt-dive-in' ),
				'title'       => __( 'Get the newsletter', 'ltt-dive-in' ),
				'copy'        => __( 'One email a month with events and ideas for your trip.', 'ltt-dive-in' ),
				'primary_cta' => ltt_dive_in_block_guide_link( __( 'Sign up', 'ltt-dive-in' ) ),
			),
			array(
				'tag'         => __( 'Ask', 'ltt-dive-in' ),
				'title'       => __( 'Talk to a local', 'ltt-dive-in' ),
				'copy'        => __( 'Our team can help you plan every detail.', 'ltt-dive-in' ),
				'primary_cta' => ltt_dive_in_block_guide_link( __( 'Contact us', 'ltt-dive-in' ) ),
			),
		),
	),
	'page_driver' => array(
		$p . 'variant'                => 'page_driver',
		$p . 'page_driver_background' => 'white',
		$p . 'tag'                    => __( 'Keep exploring', 'ltt-dive-in' ),
		$p . 'title'                  => __( 'More ways to enjoy the lake', 'ltt-dive-in' ),
		$p . 'body'                   => __( 'A few more ideas before you go.', 'ltt-dive-in' ),
		$p . 'cards'                  => array(
			array(
				'image' => ltt_dive_in_block_guide_photo( 12 ),
				'title' => __( 'Where to stay', 'ltt-dive-in' ),
				'copy'  => __( 'From lakefront resorts to mountain cabins.', 'ltt-dive-in' ),
				'cta'   => ltt_dive_in_block_guide_link( __( 'Find lodging', 'ltt-dive-in' ) ),
			),
			array(
				'image' => ltt_dive_in_block_guide_photo( 13 ),
				'title' => __( 'Where to eat', 'ltt-dive-in' ),
				'copy'  => __( 'Lake views, local beer, and late night tacos.', 'ltt-dive-in' ),
				'cta'   => ltt_dive_in_block_guide_link( __( 'See restaurants', 'ltt-dive-in' ) ),
			),
			array(
				'image' => ltt_dive_in_block_guide_photo( 14 ),
				'title' => __( 'Getting around', 'ltt-dive-in' ),
				'copy'  => __( 'Shuttles, bike paths, and scenic drives.', 'ltt-dive-in' ),
				'cta'   => ltt_dive_in_block_guide_link( __( 'Plan your route', 'ltt-dive-in' ) ),
			),
		),
	),
);

if ( count( $trail ) >= 2 ) {
	$samples['content_trail'] = array(
		$p . 'variant'     => 'content_trail',
		$p . 'tag'         => __( 'Keep exploring', 'ltt-dive-in' ),
		$p . 'title'       => __( 'More to see around the lake', 'ltt-dive-in' ),
		$p . 'body'        => __( 'Stories and places picked for you.', 'ltt-dive-in' ),
		$p . 'cta'         => ltt_dive_in_block_guide_link( __( 'See all stories', 'ltt-dive-in' ) ),
		$p . 'trail_items' => $trail,
	);
}

return $samples;
