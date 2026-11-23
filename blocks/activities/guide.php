<?php
/**
 * Block Guide content for Activities.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'A section with a heading and intro on top and four photo cards underneath. Each card points to a page or a blog post you pick, and it fills itself in with that page\'s photo and title, so you never type the card content twice.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button in the editor, open <strong>LTT Blocks</strong>, and add <strong>Activities</strong>. You can also search for "activities".', 'ltt-dive-in' ),
		__( 'Write the heading and the introduction.', 'ltt-dive-in' ),
		__( 'In the <strong>Activities</strong> field, search for the four pages or posts you want and click them to add them. Drag them to change the order.', 'ltt-dive-in' ),
		__( 'Add buttons if you want, then update the page.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Heading', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The section title, like "Things to do this summer".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Introduction', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'A sentence or two under the heading that sets up the four cards.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Category tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the heading, like "Explore".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary button', 'ltt-dive-in' ),
			'description' => __( 'An optional button next to the heading. Good for linking to a full list, like "See all activities".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Secondary button', 'ltt-dive-in' ),
			'description' => __( 'An optional second button with a lighter style.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Activities', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Exactly four published pages or posts. Each one needs a featured image, because that is the card photo. The order here is the order on the page.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'The card shows the page title, so a clear title on the page itself gives you a clear card.', 'ltt-dive-in' ),
		__( 'For pages, the short text on the card comes from the <strong>Card content</strong> box on that page (the Description field). For blog posts it comes from the post excerpt. If they are empty, the card shows only the title.', 'ltt-dive-in' ),
		__( 'Every card gets an "Explore" button that opens the page. You do not need to add a link.', 'ltt-dive-in' ),
		__( 'The photo is the page\'s featured image. If a photo looks badly cropped on the card, choose a different featured image on that page.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'WordPress says "Select exactly four activities."', 'ltt-dive-in' ) => __( 'The block only works with four cards, not three and not five. Add or remove items until there are four.', 'ltt-dive-in' ),
		__( 'WordPress says "Every activity must be a published page or post with a featured image."', 'ltt-dive-in' ) => __( 'One of the items is a draft or has no featured image. Open that page, set a featured image, publish it, and then save this page again.', 'ltt-dive-in' ),
		__( 'The block disappeared from the live page.', 'ltt-dive-in' ) => __( 'This happens if one of the four pages was unpublished, trashed, or lost its featured image after you set up the block. Fix that page or replace it with another one.', 'ltt-dive-in' ),
	),
);
