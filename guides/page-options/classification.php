<?php
/**
 * Site Guide: Classification box.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Classification', 'ltt-dive-in' ),
	'group'           => 'pages',
	'order'           => 40,
	'icon'            => 'tag',
	'where'           => __( 'Page editor, right sidebar, "Classification" box', 'ltt-dive-in' ),
	'applies'         => __( 'Every page.', 'ltt-dive-in' ),
	'short'           => __( 'Tags a page by activity, lodging type, place, and season so filters can find it.', 'ltt-dive-in' ),
	'summary'         => __( 'Classification describes what a page is about, using four lists: Activities, Lodging Types, Places, and When. Visitors never see this box, but the filter buttons in the Up Driver use it to decide which cards to show.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The filter buttons of the Up Driver block.', 'ltt-dive-in' ),
		__( 'The month selector of the Monthly Up Driver, which uses When.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Activities', 'ltt-dive-in' ),
			'description' => __( 'What people can do there, like Beaches & Parks, Eat & Drink, or Guided Adventures.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Lodging Types', 'ltt-dive-in' ),
			'description' => __( 'For places to stay, like Hotels, Resorts, or Vacation Rentals.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Places', 'ltt-dive-in' ),
			'description' => __( 'Where it is, like Tahoe City, Kings Beach, or Incline Village.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'When', 'ltt-dive-in' ),
			'description' => __( 'The months or seasons it is best for, like December or Fall.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Check every option that truly applies, but no more. A page tagged with everything shows up under every filter, which makes the filters useless.', 'ltt-dive-in' ),
		__( 'When you create a new page that should appear in a filtered Up Driver, remember to classify it, or it will only show under "All".', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'A page does not appear under a filter.', 'ltt-dive-in' ) => __( 'Open the page and check that the matching option is checked in Classification, then update the page.', 'ltt-dive-in' ),
	),
);
