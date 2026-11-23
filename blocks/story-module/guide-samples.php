<?php
/**
 * Block Guide samples for Story Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p = 'ltt_dive_in_story_module_';

$sample = static function ( $variant, $values ) use ( $p ) {
	return array(
		$p . 'variant' => $variant,
		$p . $variant  => $values,
	);
};

$guide_list = array(
	array( 'text' => __( 'Sunset cruise on Emerald Bay', 'ltt-dive-in' ) ),
	array( 'text' => __( 'Dinner with a lake view in Tahoe City', 'ltt-dive-in' ) ),
	array( 'text' => __( 'Morning paddle at Sand Harbor', 'ltt-dive-in' ) ),
	array( 'text' => __( 'A slow afternoon at a lakeside spa', 'ltt-dive-in' ) ),
);

$guide_content = __( '<p>Whether it is your first trip together or your tenth anniversary, the North Shore makes it easy to slow down. Here are a few ideas to start with.</p>', 'ltt-dive-in' );

return array(
	'listicle'         => $sample(
		'listicle',
		array(
			'number'         => 1,
			'title'          => __( 'Lone Eagle Grille', 'ltt-dive-in' ),
			'location'       => __( 'Incline Village', 'ltt-dive-in' ),
			'body'           => __( '<p>Big windows, a stone fireplace, and a deck right on the water. Come at sunset and order the trout.</p><p>Reservations are a good idea on weekends.</p>', 'ltt-dive-in' ),
			'images'         => ltt_dive_in_block_guide_photos( 3, 4 ),
			'image_position' => 'right',
		)
	),
	'numbered_heading' => $sample(
		'numbered_heading',
		array(
			'number' => 2,
			'title'  => __( 'Holiday markets worth the drive', 'ltt-dive-in' ),
			'intro'  => __( 'Local makers, hot cider, and twinkle lights in small towns around the lake.', 'ltt-dive-in' ),
		)
	),
	'event'            => $sample(
		'event',
		array(
			'title'    => __( 'Tahoe City Winter Festival', 'ltt-dive-in' ),
			'date'     => __( 'December 12 to 14', 'ltt-dive-in' ),
			'location' => __( 'Commons Beach, Tahoe City', 'ltt-dive-in' ),
			'content'  => __( '<p>Ice skating, a tree lighting, and live music by the lake. Free for everyone.</p>', 'ltt-dive-in' ),
			'image'    => ltt_dive_in_block_guide_photo( 7 ),
		)
	),
	'side_by_side'     => $sample(
		'side_by_side',
		array(
			'eyebrow'        => __( 'Meet the chef', 'ltt-dive-in' ),
			'title'          => __( 'Cooking with what the lake gives', 'ltt-dive-in' ),
			'content'        => __( '<p>Chef Ana Ruiz changes her menu every few weeks, based on what local farms bring in.</p><ul><li>Trout from nearby streams</li><li>Mushrooms from the Sierra</li><li>Berries in late summer</li></ul>', 'ltt-dive-in' ),
			'cta'            => ltt_dive_in_block_guide_link( __( 'See the menu', 'ltt-dive-in' ) ),
			'image'          => ltt_dive_in_block_guide_photo( 8 ),
			'image_position' => 'right',
		)
	),
	'travel_tip'       => $sample(
		'travel_tip',
		array(
			'label' => __( 'Responsible Travel Tip', 'ltt-dive-in' ),
			'title' => __( 'Leave the beach better than you found it', 'ltt-dive-in' ),
			'body'  => __( '<p>Pack out all trash, including food scraps. Even small things like orange peels take years to break down at this altitude.</p>', 'ltt-dive-in' ),
		)
	),
	'explainer'        => $sample(
		'explainer',
		array(
			'question' => __( 'Do I need a car at Lake Tahoe?', 'ltt-dive-in' ),
			'answer'   => __( '<p>Not always. In summer, free shuttles connect most towns and beaches. A car helps if you want to explore the East Shore or ski at different resorts.</p>', 'ltt-dive-in' ),
		)
	),
	'itinerary_day'    => $sample(
		'itinerary_day',
		array(
			'number' => 1,
			'title'  => __( 'Arrive and settle in on the North Shore', 'ltt-dive-in' ),
		)
	),
	'itinerary_item'   => $sample(
		'itinerary_item',
		array(
			'time'  => 'morning',
			'stops' => array(
				array(
					'title'   => __( '<p>Breakfast at <a href="/">Fire Sign Cafe</a></p>', 'ltt-dive-in' ),
					'content' => __( '<p>Get there early. The line forms fast on weekends.</p>', 'ltt-dive-in' ),
				),
				array(
					'title'   => __( '<p>Walk the Tahoe City shoreline</p>', 'ltt-dive-in' ),
					'content' => __( '<p>An easy, flat path with lake views the whole way.</p>', 'ltt-dive-in' ),
				),
			),
			'image' => ltt_dive_in_block_guide_photo( 9 ),
		)
	),
	'numbers_guide'    => $sample(
		'numbers_guide',
		array(
			'title'        => __( 'A couple\'s day on the North Shore', 'ltt-dive-in' ),
			'content'      => $guide_content,
			'list_heading' => __( 'Our top picks', 'ltt-dive-in' ),
			'items'        => $guide_list,
			'images'       => ltt_dive_in_block_guide_photos( 3, 10 ),
		)
	),
	'bullet_guide'     => $sample(
		'bullet_guide',
		array(
			'title'        => __( 'What to pack for a winter weekend', 'ltt-dive-in' ),
			'content'      => __( '<p>Mountain weather changes fast. These are the things we always bring.</p>', 'ltt-dive-in' ),
			'list_heading' => __( 'Do not forget', 'ltt-dive-in' ),
			'items'        => array(
				array( 'text' => __( 'Warm layers and a waterproof jacket', 'ltt-dive-in' ) ),
				array( 'text' => __( 'Snow chains for the car', 'ltt-dive-in' ) ),
				array( 'text' => __( 'Sunglasses and sunscreen', 'ltt-dive-in' ) ),
			),
			'images'       => ltt_dive_in_block_guide_photos( 1, 13 ),
		)
	),
	'guide_1up'        => $sample(
		'guide_1up',
		array(
			'title'   => __( 'Incline Village in one afternoon', 'ltt-dive-in' ),
			'content' => $guide_content,
			'images'  => ltt_dive_in_block_guide_photos( 2, 14 ),
		)
	),
	'comparison_table' => $sample(
		'comparison_table',
		array(
			'caption'    => __( 'Comparison of North Shore restaurants by view, price, and kid friendliness', 'ltt-dive-in' ),
			'row_heading' => __( 'Restaurant', 'ltt-dive-in' ),
			'columns'    => array(
				array( 'heading' => __( 'Lake view', 'ltt-dive-in' ) ),
				array( 'heading' => __( 'Kid friendly', 'ltt-dive-in' ) ),
				array( 'heading' => __( 'Price', 'ltt-dive-in' ) ),
			),
			'rows'       => array(
				array(
					'name'  => __( 'Lone Eagle Grille', 'ltt-dive-in' ),
					'cells' => array(
						array( 'value' => 'yes', 'text' => '' ),
						array( 'value' => 'no', 'text' => '' ),
						array( 'value' => 'text', 'text' => '$$$' ),
					),
				),
				array(
					'name'  => __( 'Fire Sign Cafe', 'ltt-dive-in' ),
					'cells' => array(
						array( 'value' => 'no', 'text' => '' ),
						array( 'value' => 'yes', 'text' => '' ),
						array( 'value' => 'text', 'text' => '$' ),
					),
				),
			),
		)
	),
	'resource_links'   => $sample(
		'resource_links',
		array(
			'title' => __( 'Plan your visit', 'ltt-dive-in' ),
			'intro' => __( 'Useful links before you go.', 'ltt-dive-in' ),
			'links' => array(
				array( 'link' => ltt_dive_in_block_guide_link( __( 'Road conditions', 'ltt-dive-in' ) ) ),
				array( 'link' => ltt_dive_in_block_guide_link( __( 'Parking and permits', 'ltt-dive-in' ) ) ),
				array( 'link' => ltt_dive_in_block_guide_link( __( 'Where to stay', 'ltt-dive-in' ) ) ),
			),
		)
	),
	'image_pair'       => $sample(
		'image_pair',
		array(
			'images' => ltt_dive_in_block_guide_photos( 2, 16 ),
		)
	),
	'conclusion'       => $sample(
		'conclusion',
		array(
			'title'   => __( 'Ready for your trip?', 'ltt-dive-in' ),
			'content' => __( '<p>Lake Tahoe is just as good in winter as it is in summer. Pick a few ideas from this list, pack layers, and let the lake do the rest.</p>', 'ltt-dive-in' ),
			'cta'     => ltt_dive_in_block_guide_link( __( 'Plan your trip', 'ltt-dive-in' ) ),
		)
	),
);
