<?php
/**
 * Block Guide samples for Page Cluster.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cards = array(
	array( __( 'North Shore', 'ltt-dive-in' ), __( 'Quiet beaches, small towns, and easy access to the best trails.', 'ltt-dive-in' ) ),
	array( __( 'South Shore', 'ltt-dive-in' ), __( 'Casinos, live music, and the busiest nightlife around the lake.', 'ltt-dive-in' ) ),
	array( __( 'East Shore', 'ltt-dive-in' ), __( 'Clear coves and granite boulders along an undeveloped coast.', 'ltt-dive-in' ) ),
	array( __( 'West Shore', 'ltt-dive-in' ), __( 'Old forests, historic estates, and the road to Emerald Bay.', 'ltt-dive-in' ) ),
);

$build = static function ( $variant ) use ( $cards ) {
	$rows = array();

	foreach ( $cards as $index => $card ) {
		$rows[] = array(
			'image'         => ltt_dive_in_block_guide_photo( $index + 8 ),
			'title'         => $card[0],
			'copy'          => $card[1],
			/* translators: %s: area name. */
			'cta'           => ltt_dive_in_block_guide_link( sprintf( __( 'Explore %s', 'ltt-dive-in' ), $card[0] ) ),
			'see_more_link' => ltt_dive_in_block_guide_link( __( 'See More', 'ltt-dive-in' ) ),
		);
	}

	return array(
		'ltt_dive_in_page_cluster_variant'       => $variant,
		'ltt_dive_in_page_cluster_label'         => __( 'Where to go', 'ltt-dive-in' ),
		'ltt_dive_in_page_cluster_title'         => __( 'Four shores, four different trips', 'ltt-dive-in' ),
		'ltt_dive_in_page_cluster_body'          => __( 'Each side of the lake has its own personality. Pick the one that fits your trip.', 'ltt-dive-in' ),
		'ltt_dive_in_page_cluster_primary_cta'   => ltt_dive_in_block_guide_link( __( 'See all areas', 'ltt-dive-in' ) ),
		'ltt_dive_in_page_cluster_secondary_cta' => ltt_dive_in_block_guide_link( __( 'View the map', 'ltt-dive-in' ) ),
		'ltt_dive_in_page_cluster_cards'         => $rows,
	);
};

return array(
	'hub'      => $build( 'hub' ),
	'features' => $build( 'features' ),
);
