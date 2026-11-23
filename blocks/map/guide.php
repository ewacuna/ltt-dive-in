<?php
/**
 * Block Guide content for Map.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'An interactive map of Lake Tahoe. You add two kinds of points: destinations, which visitors click to see a photo, description, and link, and attractions, which are small icons that mark things like trails, beaches, or lighthouses. There is an optional snow effect too.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Map</strong>.', 'ltt-dive-in' ),
		__( 'Add your destinations and attractions with their coordinates.', 'ltt-dive-in' ),
		__( 'Update the page and open it to see the map. The editor only shows a summary box, not the real map.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Destinations', 'ltt-dive-in' ),
			'description' => __( 'The main places on the map. Up to 12. When a visitor selects one, its details open.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Name', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The place name, like "Emerald Bay". Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Image', 'ltt-dive-in' ),
			'description' => __( 'An optional landscape photo shown in the details.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Region', 'ltt-dive-in' ),
			'description' => __( 'An optional area name, like "South Shore". Up to 60 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Description', 'ltt-dive-in' ),
			'description' => __( 'A few sentences about the place. Up to 600 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Latitude', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The first number of the coordinates, like 38.9533. See the tips below for how to get it.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Longitude', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The second number, like -120.1083. Around Lake Tahoe it always starts with a minus sign.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Link', 'ltt-dive-in' ),
			'description' => __( 'An optional button in the details, usually to the page about that place.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Attractions', 'ltt-dive-in' ),
			'description' => __( 'Smaller points marked with an icon. Up to 30. Each one has a <strong>Name</strong> (up to 60 characters), an <strong>Icon</strong> (Lodge, Lighthouse, Waterfall, Hiking trail, Airport, and so on), and its Latitude and Longitude.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Snow effect', 'ltt-dive-in' ),
			'description' => __( 'Shows falling snow over the map. It is on by default. Visitors can pause it, and it stays off for people who have reduced motion turned on in their device settings.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'To get coordinates, open Google Maps, right-click the exact spot, and click the numbers at the top of the menu. That copies something like <code>38.9533, -120.1083</code>. The first number goes in Latitude and the second in Longitude.', 'ltt-dive-in' ),
		__( 'If a point shows up in the wrong place (or in the middle of the ocean), the two numbers are probably swapped or the minus sign is missing.', 'ltt-dive-in' ),
		__( 'Pick the icon that best matches the attraction, so visitors can tell at a glance what each one is.', 'ltt-dive-in' ),
		__( 'Visitors who cannot use the map still get a plain list of the places, so write descriptions that make sense without the map.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "No Mapbox token is configured."', 'ltt-dive-in' ) => __( 'The map service is not connected. An administrator needs to add the Mapbox token under Settings → Map. Until then visitors see only the list of places.', 'ltt-dive-in' ),
		__( 'I do not see the map in the editor.', 'ltt-dive-in' ) => __( 'That is expected. The editor shows a summary with how many destinations and attractions you added. Preview or view the page to see the map.', 'ltt-dive-in' ),
	),
);
