<?php
/**
 * Block Guide samples for Map.
 *
 * Real places around Lake Tahoe with approximate coordinates.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$place = static function ( $name, $region, $description, $latitude, $longitude, $photo ) {
	return array(
		'name'        => $name,
		'image'       => ltt_dive_in_block_guide_photo( $photo ),
		'region'      => $region,
		'description' => $description,
		'latitude'    => $latitude,
		'longitude'   => $longitude,
		/* translators: %s: place name. */
		'link'        => ltt_dive_in_block_guide_link( sprintf( __( 'Explore %s', 'ltt-dive-in' ), $name ) ),
	);
};

$attraction = static function ( $name, $icon, $latitude, $longitude ) {
	return array(
		'name'      => $name,
		'icon'      => $icon,
		'latitude'  => $latitude,
		'longitude' => $longitude,
	);
};

return array(
	'default' => array(
		'ltt_dive_in_map_places'      => array(
			$place( __( 'Tahoe City', 'ltt-dive-in' ), __( 'North Shore', 'ltt-dive-in' ), __( 'A small lakeside town with shops, restaurants, and the start of the Truckee River.', 'ltt-dive-in' ), 39.1677, -120.1452, 0 ),
			$place( __( 'Kings Beach', 'ltt-dive-in' ), __( 'North Shore', 'ltt-dive-in' ), __( 'A wide sandy beach with calm, shallow water, popular with families.', 'ltt-dive-in' ), 39.2377, -120.0263, 1 ),
			$place( __( 'Sand Harbor', 'ltt-dive-in' ), __( 'East Shore', 'ltt-dive-in' ), __( 'Clear coves and granite boulders, perfect for kayaking and snorkeling.', 'ltt-dive-in' ), 39.1990, -119.9310, 2 ),
			$place( __( 'Emerald Bay', 'ltt-dive-in' ), __( 'West Shore', 'ltt-dive-in' ), __( 'The most photographed spot on the lake, with a small island in the middle of the bay.', 'ltt-dive-in' ), 38.9540, -120.1060, 3 ),
			$place( __( 'South Lake Tahoe', 'ltt-dive-in' ), __( 'South Shore', 'ltt-dive-in' ), __( 'The busiest town on the lake, with casinos, live music, and the gondola.', 'ltt-dive-in' ), 38.9399, -119.9772, 4 ),
		),
		'ltt_dive_in_map_attractions' => array(
			$attraction( __( 'Vikingsholm', 'ltt-dive-in' ), 'vikingsholm', 38.9526, -120.1062 ),
			$attraction( __( 'Eagle Falls', 'ltt-dive-in' ), 'waterfall', 38.9520, -120.1130 ),
			$attraction( __( 'Thunderbird Lodge', 'ltt-dive-in' ), 'thunderbird', 39.2186, -119.9295 ),
			$attraction( __( 'Rubicon Point Lighthouse', 'ltt-dive-in' ), 'rubicon', 39.0036, -120.1015 ),
			$attraction( __( 'Lake Tahoe Airport', 'ltt-dive-in' ), 'airport', 38.8939, -119.9953 ),
		),
		'ltt_dive_in_map_snow'        => 0,
	),
);
