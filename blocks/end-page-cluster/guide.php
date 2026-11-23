<?php
/**
 * Block Guide content for End Page Cluster.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'The last section of a page. Its job is to give visitors somewhere to go next instead of leaving the site. It has three layouts: text callouts with buttons, three photo cards, or a sliding row of related stories, events, and places.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts. Place it at the very end of the content.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button at the bottom of your content, open <strong>LTT Blocks</strong>, and add <strong>End Page Cluster</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong>. The fields change for each one.', 'ltt-dive-in' ),
		__( 'Fill in the fields and update the page.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The layout. Copy/CTA Driver is the default.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'copy_cta'      => array(
			'name'        => __( 'Copy/CTA Driver', 'ltt-dive-in' ),
			'description' => __( 'Two or three short callouts in a row, each with a title, a line of text, and one or two buttons. There is no section heading here; each callout title stands on its own. On phones the callouts stack.', 'ltt-dive-in' ),
			'best_for'    => __( 'Clear next steps, like "Plan your stay", "Get the newsletter", "Contact us".', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Background color', 'ltt-dive-in' ),
					'description' => __( 'Deep Navy, Peak Gold, Burgundy, Red, Forest Green, Green, Blue, or Crystal Clear Cyan. Leave it empty for white. Button colors adjust on their own.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Callouts', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add two or three.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Category tag', 'ltt-dive-in' ),
					'description' => __( 'A small label above the callout title. Up to 40 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Title', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The callout title. Up to 80 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Short copy', 'ltt-dive-in' ),
					'description' => __( 'A line or two of text. Up to 200 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Primary call to action', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The main button for this callout.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Secondary call to action', 'ltt-dive-in' ),
					'description' => __( 'An optional second button.', 'ltt-dive-in' ),
				),
			),
		),
		'page_driver'   => array(
			'name'        => __( 'Page Driver', 'ltt-dive-in' ),
			'description' => __( 'A centered heading with three tall photo cards under it. Each card has a title, a short line, and its own button. You choose the photo and write the text for each card, so they can point anywhere.', 'ltt-dive-in' ),
			'best_for'    => __( 'Sending people to three related sections, like "Where to stay", "Where to eat", "Getting around".', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Background', 'ltt-dive-in' ),
					'description' => __( 'White or Navy for the area behind the cards.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Category tag', 'ltt-dive-in' ),
					'description' => __( 'A small label above the title. Up to 40 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Title', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section heading. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Body copy', 'ltt-dive-in' ),
					'description' => __( 'A short paragraph under the title. Up to 300 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Cards', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Exactly three cards. Each one has a background image (required), a title of up to 50 characters, short copy of up to 150 characters, and an optional button.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'The card photos are treated as decoration and darkened so the white text stays readable, so pick photos that still look good a bit darker.', 'ltt-dive-in' ),
			),
		),
		'content_trail' => array(
			'name'        => __( 'Content Trail', 'ltt-dive-in' ),
			'description' => __( 'A heading and a sliding row of cards that mixes blog stories, events, and places. Visitors scroll through it with arrows, dots, or by swiping. Place cards show the page excerpt when someone hovers over them.', 'ltt-dive-in' ),
			'best_for'    => __( 'A "keep exploring" row that mixes different kinds of content.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Category tag', 'ltt-dive-in' ),
					'description' => __( 'A small label above the title.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Title', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section heading. Up to 120 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Body copy', 'ltt-dive-in' ),
					'description' => __( 'A short paragraph under the title.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Call to action', 'ltt-dive-in' ),
					'description' => __( 'An optional button next to the heading.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Trail items', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Add 2 to 12 items, in the order you want them. For each one, pick a <strong>Type</strong>: Story (a blog post), Event (search the event calendar by name), or Place (a page). Then choose the item.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( 'Stories and places need a featured image to show up.', 'ltt-dive-in' ),
				__( 'Events drop out of the row on their own once they are over. Check the trail now and then and add new events so it does not get too short.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'Use only one End Page Cluster per page. It is meant to be the closing section.', 'ltt-dive-in' ),
		__( 'Write button labels that say where they go, like "Find a place to stay", not "Click here".', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Add two or three callouts to preview the Copy/CTA Driver."', 'ltt-dive-in' ) => __( 'There are no callouts with a title yet. Add at least two callouts and give each one a title.', 'ltt-dive-in' ),
		__( 'The editor says "Add a title and three cards to preview the Page Driver."', 'ltt-dive-in' ) => __( 'Fill in the section title and add three cards, each with its own title.', 'ltt-dive-in' ),
		__( 'The Content Trail shows fewer cards than I added.', 'ltt-dive-in' ) => __( 'Some items were skipped: an event that already ended, or a story or place that is unpublished or has no featured image.', 'ltt-dive-in' ),
	),
);
