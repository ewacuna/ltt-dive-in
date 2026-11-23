<?php
/**
 * Block Guide content for Page Cluster.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'summary'         => __( 'A heading followed by a set of photo cards that you write by hand: your own image, title, text, and link for each card. It has two layouts: the Hub Driver, with text over the photos, and the Features List Grid, with text under the photos.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Page Cluster</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong> and write the heading.', 'ltt-dive-in' ),
		__( 'Add at least four cards.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Fields every layout has', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Hub Driver or Features List Grid. Both share the same cards, so you can switch between them without losing anything.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Heading', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The section title.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Category Tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the heading.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary button / Secondary button', 'ltt-dive-in' ),
			'description' => __( 'Optional buttons for the whole section, like "See all neighborhoods".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Cards', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'At least four. Each card needs an <strong>Image</strong>, a <strong>Title</strong> (up to 50 characters), and <strong>Short copy</strong> (up to 250 characters).', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'hub'      => array(
			'name'        => __( 'Hub Driver', 'ltt-dive-in' ),
			'description' => __( 'Four photo cards side by side, with the title, text, and an optional button on top of each photo. On phones they show as a two by two grid. The section can also have an introduction.', 'ltt-dive-in' ),
			'best_for'    => __( 'The main sections of a hub page, like the four regions of the lake.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Introduction', 'ltt-dive-in' ),
					'description' => __( 'A short paragraph under the heading. Only this layout shows it. Up to 300 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Button (on each card)', 'ltt-dive-in' ),
					'description' => __( 'An optional button on the card, like "Explore North Shore".', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'On phones the card text is cut to three lines, so put the important words first.', 'ltt-dive-in' ),
				__( 'The photos are darkened behind the text and treated as decoration.', 'ltt-dive-in' ),
			),
		),
		'features' => array(
			'name'        => __( 'Features List Grid', 'ltt-dive-in' ),
			'description' => __( 'Cards with the photo on top and the title, text, and a text link under it. On phones the cards become a slider.', 'ltt-dive-in' ),
			'best_for'    => __( 'A list of features or services where people need to read a bit more on each card.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Card link', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Where the card goes and the link text. If you leave the text empty it says "See More".', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'The Introduction field is not used in this layout. If you wrote one for the Hub Driver, it is kept in case you switch back.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'Use photos with the same shape and a similar feel across all cards. Mixed photos make the grid look uneven.', 'ltt-dive-in' ),
		__( 'Want cards that update themselves from existing pages? Use the Up Driver instead. Page Cluster is for cards you write yourself.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Add a title and cards to preview Page Cluster."', 'ltt-dive-in' ) => __( 'Fill in the Heading and add the cards.', 'ltt-dive-in' ),
	),
);
