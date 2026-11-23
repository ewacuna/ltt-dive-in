<?php
/**
 * Block Guide content for Feature Image Driver.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'summary'         => __( 'A big photo with a title, a bit of text, and a button laid over it. You choose the photo and write everything yourself, so it can promote anything. It comes in four layouts: one large image, a slider of two or three, a shorter single image, and a row of cards that expand.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Feature Image Driver</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong>.', 'ltt-dive-in' ),
		__( 'Add your items under <strong>Feature items</strong>. Each item is one image with its text and button.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Fields every layout has', 'ltt-dive-in' ),
	'fields_intro'    => __( 'All four layouts use the same <strong>Feature items</strong> list (up to 5). What changes is how many items each layout needs.', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The layout. Single is the default.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Active item', 'ltt-dive-in' ),
			'description' => __( 'Turn an item off to hide it without deleting it. Handy for seasonal content you want to bring back later. Only active items count toward the layout\'s limit.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Category tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the title. Only the Single and Carousel layouts show it. Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Image', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The photo. Pick a wide, high quality image with a calm area where the text can sit.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Title', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The big text over the photo. Up to 60 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Show body copy', 'ltt-dive-in' ),
			'description' => __( 'Turn it off to show just the title and button.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Body copy', 'ltt-dive-in' ),
			'description' => __( 'A sentence or two under the title. Up to 250 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Call to action', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The button and where it goes. Every active item needs one.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Call to action white button', 'ltt-dive-in' ),
			'description' => __( 'A second, solid button. Only the Short Single layout shows it.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'single'   => array(
			'name'        => __( 'Single', 'ltt-dive-in' ),
			'description' => __( 'One large photo across the full width, with the tag, title, text, and button on top of it.', 'ltt-dive-in' ),
			'best_for'    => __( 'One big thing you want people to notice, like a seasonal campaign.', 'ltt-dive-in' ),
			'notes'       => array(
				__( 'Needs exactly 1 active item.', 'ltt-dive-in' ),
			),
		),
		'carousel' => array(
			'name'        => __( 'Carousel', 'ltt-dive-in' ),
			'description' => __( 'The same look as Single, but with two or three slides. Visitors move between them with arrows and dots.', 'ltt-dive-in' ),
			'best_for'    => __( 'Two or three features of equal weight.', 'ltt-dive-in' ),
			'notes'       => array(
				__( 'Needs 2 or 3 active items. Keep in mind many visitors only see the first slide, so put the most important one first.', 'ltt-dive-in' ),
			),
		),
		'short'    => array(
			'name'        => __( 'Short Single', 'ltt-dive-in' ),
			'description' => __( 'One photo, but not as tall as Single, with room for two buttons: the regular one and a solid white one.', 'ltt-dive-in' ),
			'best_for'    => __( 'A promo in the middle of a page that should not take over the whole screen.', 'ltt-dive-in' ),
			'notes'       => array(
				__( 'Needs exactly 1 active item. The category tag is not shown in this layout.', 'ltt-dive-in' ),
			),
		),
		'stacked'  => array(
			'name'        => __( 'Stacked', 'ltt-dive-in' ),
			'description' => __( 'A section heading and intro, followed by a row of two to five photo cards. On phones the cards stack and one is open at a time; tapping another card opens it.', 'ltt-dive-in' ),
			'best_for'    => __( 'A group of related places or ideas, like "Five beaches worth the drive".', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Section category tag', 'ltt-dive-in' ),
					'description' => __( 'A small label above the section heading. Up to 40 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Section heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The title of the whole section. Up to 90 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Section introduction', 'ltt-dive-in' ),
					'description' => __( 'A short paragraph under the heading. Up to 360 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Section primary / secondary call to action', 'ltt-dive-in' ),
					'description' => __( 'Optional buttons next to the heading.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Mobile Background', 'ltt-dive-in' ),
					'description' => __( 'Light or Dark. Changes the background behind the cards on phones only.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'Needs 2 to 5 active items.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'Text sits on top of the photo, so avoid images with lots of small detail behind where the title goes. The theme adds a dark layer to help, but a calmer photo always reads better.', 'ltt-dive-in' ),
		__( 'Keep titles short. A title that wraps to three lines on a phone covers most of the photo.', 'ltt-dive-in' ),
		__( 'If you change the layout and the block stops showing, count the active items. Each layout has its own number.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Adjust the active item count for this variant and complete every active image, title, and call to action."', 'ltt-dive-in' ) => __( 'Either there are too many or too few active items for this layout, or one active item is missing its image, title, or button. Fix that item or turn it off with Active item.', 'ltt-dive-in' ),
	),
);
