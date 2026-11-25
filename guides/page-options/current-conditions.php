<?php
/**
 * Site Guide: Current Conditions template.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-25',
	'title'           => __( 'Current Conditions pages', 'ltt-dive-in' ),
	'group'           => 'templates',
	'order'           => 10,
	'icon'            => 'cloud',
	'where'           => __( 'Page editor, "Current Conditions Tab" box below the content, on pages using the Current Conditions template', 'ltt-dive-in' ),
	'applies'         => __( 'The Current Conditions page and its child pages (Weather, Roads, Webcam). The box only appears on the child pages.', 'ltt-dive-in' ),
	'short'           => __( 'The Weather, Roads, and Webcam pages with live information.', 'ltt-dive-in' ),
	'summary'         => __( 'Current Conditions is a parent page with child pages, one per tab: Weather, Roads, and Webcam. They share the same top section and a row of tabs. Each child page picks one live module (weather, roads, or cameras) and sets it up in the Current Conditions Tab box. Visitors can switch tabs without the whole page reloading.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Create the page as a child of Current Conditions (in the sidebar, <strong>Page → Parent</strong>) and choose the <strong>Current Conditions</strong> template.', 'ltt-dive-in' ),
		__( 'Write the page title and excerpt. They appear as the tab\'s heading and intro. The big title at the top comes from the parent page.', 'ltt-dive-in' ),
		__( 'In <strong>Page Header</strong>, set <strong>Section menu</strong> to the Current Conditions menu so the tabs appear, and add the page to that menu in Appearance → Menus.', 'ltt-dive-in' ),
		__( 'In the Current Conditions Tab box, pick the <strong>Live module</strong> and fill in its settings.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Live module', 'ltt-dive-in' ),
			'description' => __( '<strong>Current weather</strong>, <strong>Road conditions</strong>, <strong>Live cameras</strong>, or <strong>None</strong>. It appears under the tab intro. Each option shows its own settings, explained below.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Related conditions', 'ltt-dive-in' ),
			'description' => __( 'Up to three boxes at the bottom that link to the other conditions pages. Each has a heading, an optional sentence, and a button. Leave it empty to hide the section.', 'ltt-dive-in' ),
		),
	),
	'parts'           => array(
		array(
			'name'        => __( 'Current weather', 'ltt-dive-in' ),
			'description' => __( 'The forecast from OpenWeather for one or more locations around the lake. Visitors pick the location from a list.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Weather locations', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( '1 to 12 places, each with a <strong>Name</strong>, <strong>Latitude</strong>, <strong>Longitude</strong>, and an optional <strong>Elevation</strong> in feet. The first one is shown when the page opens, and it is also the one used for the temperature in the header.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'To get coordinates, right-click the spot in Google Maps and click the numbers at the top of the menu.', 'ltt-dive-in' ),
			),
		),
		array(
			'name'        => __( 'Road conditions', 'ltt-dive-in' ),
			'description' => __( 'Chain controls, closures, and road cameras for the highways you list, from Caltrans and Nevada 511.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Area center latitude / longitude', 'ltt-dive-in' ),
					'description' => __( 'The middle of the area to cover. It starts at the center of the lake; usually you do not need to change it.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Area radius (miles)', 'ltt-dive-in' ),
					'description' => __( 'How far from the center to include reports and cameras, from 5 to 150 miles. It starts at 40.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Nevada background', 'ltt-dive-in' ),
					'description' => __( 'Optional. The photo behind the section while visitors view Nevada. California uses the page\'s featured image. Leave it blank to use the featured image for both states.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Highways', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( '1 to 12 highways, in the order visitors see them. For each one: <strong>Route type</strong> and <strong>Route number</strong> (for example State Route 89), a <strong>Name</strong>, an optional <strong>Subtitle</strong> for the stretch covered, which states it runs through (<strong>Show under</strong>), optional custom route signs, and how to pick <strong>Cameras</strong>: Automatic (nearest camera), Choose, or None. Camera photos refresh every minute. Visitors can click a camera photo: California cameras with a live video show a play icon and open the video, and the others, including every Nevada camera, open a larger photo.', 'ltt-dive-in' ),
				),
			),
		),
		array(
			'name'        => __( 'Live cameras', 'ltt-dive-in' ),
			'description' => __( 'Up to four lake cameras, each in its own band, plus an optional section that groups ski resort cameras.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Live cameras', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( '1 to 4 cameras. Each needs a <strong>Heading</strong>, a <strong>Live feed link</strong>, and a <strong>Camera image</strong> (landscape, with alt text that describes the view). Choose <strong>Prism Cam</strong> as the Viewer to embed the live player, and enter its camera ID from the Prism embed code.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Ski cams heading / label / introduction', 'ltt-dive-in' ),
					'description' => __( 'The title and intro of the ski resort cameras section. Clear the heading to hide the whole section.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Featured ski cam', 'ltt-dive-in' ),
					'description' => __( 'Optional. One highlighted camera with a name, image, live feed URL, and button text.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Ski resorts', 'ltt-dive-in' ),
					'description' => __( 'Up to 8 resorts, each with up to 8 camera links (name, optional description, live feed URL). The first resort is shown when the page opens.', 'ltt-dive-in' ),
				),
			),
		),
	),
	'tips'            => array(
		__( 'The weather and road information refresh in the background, so the pages stay fast. If something looks old right after a change, wait a few minutes.', 'ltt-dive-in' ),
		__( 'The keys for these services are in Settings → Weather and Settings → Road Conditions. The Site Guide Settings section shows if they are working.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The tabs do not appear.', 'ltt-dive-in' ) => __( 'Check that the page has the Current Conditions menu set in Page Header, and that the page is in that menu.', 'ltt-dive-in' ),
	),
);
