<?php
/**
 * Block Guide content for Static Image Cluster.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'Photo galleries. You pick images from the Media Library and choose one of nine arrangements, from a single big photo to a large grid. With three or more images, visitors can click any photo to see it full screen and flip through the rest.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Static Image Cluster</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong>.', 'ltt-dive-in' ),
		__( 'Click <strong>Add to gallery</strong> under Images and select your photos. Drag them to change the order.', 'ltt-dive-in' ),
		__( 'Make sure every photo has alt text in the Media Library. The block will not save without it.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Fields every layout has', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'How the photos are arranged. See the layouts below.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Section heading', 'ltt-dive-in' ),
			'description' => __( 'An optional title. Not used by Hero With Caption or Inspired Gallery. Up to 110 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Introduction', 'ltt-dive-in' ),
			'description' => __( 'Text next to or under the images. For Hero With Caption this is the caption, and it is required. Not used by Inspired Gallery.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary, Secondary, and Third button', 'ltt-dive-in' ),
			'description' => __( 'Up to three optional buttons. Not used by Inspired Gallery.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Images', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The photos. Each layout needs a minimum number (listed below). Every photo needs alt text in the Media Library.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'hero_caption'   => array(
			'name'        => __( 'Hero With Caption', 'ltt-dive-in' ),
			'description' => __( 'One large photo with a caption.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 1 image and the Introduction (used as the caption).', 'ltt-dive-in' ) ),
		),
		'one_up'         => array(
			'name'        => __( '1 Up Gallery', 'ltt-dive-in' ),
			'description' => __( 'One photo with an optional heading, text, and buttons.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 1 image.', 'ltt-dive-in' ) ),
		),
		'two_up'         => array(
			'name'        => __( '2 Up Gallery', 'ltt-dive-in' ),
			'description' => __( 'Two photos side by side.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 2 images. Extra images are not shown.', 'ltt-dive-in' ) ),
		),
		'three_up'       => array(
			'name'        => __( '3 Up Gallery', 'ltt-dive-in' ),
			'description' => __( 'Three photos in a row. Click any of them to open the full screen viewer.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 3 images. Extra images are not shown.', 'ltt-dive-in' ) ),
		),
		'inline_four_up' => array(
			'name'        => __( 'Inline 4 Up Gallery', 'ltt-dive-in' ),
			'description' => __( 'Four photos in a straight row.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 4 images. Extra images are not shown.', 'ltt-dive-in' ) ),
		),
		'staggered_four' => array(
			'name'        => __( 'Staggered 4 Up Gallery', 'ltt-dive-in' ),
			'description' => __( 'Four photos at different heights, for a more relaxed, magazine look.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 4 images. Extra images are not shown.', 'ltt-dive-in' ) ),
		),
		'five_plus'      => array(
			'name'        => __( '5+ Up Gallery', 'ltt-dive-in' ),
			'description' => __( 'A sliding row of square photos that runs to the edge of the screen. Visitors move through it with arrows and dots, and it holds every image you add.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs at least 5 images. There is no maximum, but keep it reasonable, since each photo adds weight to the page.', 'ltt-dive-in' ) ),
		),
		'side_by_side'   => array(
			'name'        => __( 'Side by Side 1 Up Image', 'ltt-dive-in' ),
			'description' => __( 'One photo next to the heading, text, and buttons.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Needs 1 image.', 'ltt-dive-in' ) ),
		),
		'inspired'       => array(
			'name'        => __( 'Inspired Gallery', 'ltt-dive-in' ),
			'description' => __( 'A photo grid where each image has its own title and short description, which appear when the visitor opens the photo. It shows six images first and a Load More button for the rest.', 'ltt-dive-in' ),
			'best_for'    => __( 'Photo collections where each picture tells its own small story.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Image details (display)', 'ltt-dive-in' ),
					'description' => __( '<strong>Lightbox</strong> opens the photo full screen with its title and description. <strong>Inline detail</strong> shows them in a panel on the page, next to the photos, instead of full screen.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Image details (list)', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'One row per image, in the same order as the gallery. Each row has a <strong>Title</strong> (up to 25 characters) and an optional <strong>Description</strong> (up to 150 characters).', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'Needs at least 6 images.', 'ltt-dive-in' ),
				__( 'If you reorder the gallery, reorder the Image details too. Row one always goes with image one.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'Alt text lives in the Media Library, not in the block. Go to Media, click the photo, and fill in "Alternative Text" with a short description of what is in it. Once it is set there, it works everywhere the photo is used.', 'ltt-dive-in' ),
		__( 'Upload photos that are big enough for a wide screen. WordPress creates the smaller versions for phones on its own.', 'ltt-dive-in' ),
		__( 'Use the Media Library caption and the Photo credit field on the image to credit the photographer.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'WordPress says "This variant requires at least X images."', 'ltt-dive-in' ) => __( 'Add more photos to the gallery, or pick a layout that needs fewer.', 'ltt-dive-in' ),
		__( 'The editor says "Every image needs alternative text in the Media Library."', 'ltt-dive-in' ) => __( 'At least one photo has no alt text. Open it in Media, add the Alternative Text, and save the page again.', 'ltt-dive-in' ),
		__( 'WordPress says "Add Image details for every Inspired Gallery image, in the same order."', 'ltt-dive-in' ) => __( 'There are more photos than Image details rows. Add a row with a title for each photo.', 'ltt-dive-in' ),
	),
);
