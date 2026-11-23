<?php
/**
 * Site Guide: Listing Card Links box.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Listing Card Links', 'ltt-dive-in' ),
	'group'           => 'posts',
	'order'           => 20,
	'icon'            => 'admin-links',
	'where'           => __( 'Post editor, "Listing Card Links" box below the content', 'ltt-dive-in' ),
	'applies'         => __( 'Blog posts.', 'ltt-dive-in' ),
	'short'           => __( 'The buttons on this post\'s card in the Listing Module.', 'ltt-dive-in' ),
	'summary'         => __( 'When a post shows up as a card in the Listing Module block, the card can have up to two buttons. This box decides what those buttons say and where they go.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'This post\'s card in every Listing Module block.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Primary button destination', 'ltt-dive-in' ),
			'description' => __( '<strong>No primary button</strong>, <strong>This post</strong> (opens the post), or <strong>Custom link</strong> (any other page or site).', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary button label', 'ltt-dive-in' ),
			'description' => __( 'With This post. The button text. Leave it empty for "See More".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary custom link', 'ltt-dive-in' ),
			'description' => __( 'With Custom link. The button text and where it goes, like a booking page.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Second button', 'ltt-dive-in' ),
			'description' => __( 'An optional second button with its own text and destination.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'The rest of the card comes from the post: title, featured image, and excerpt.', 'ltt-dive-in' ),
		__( 'Use button labels that say what happens, like "Book a table" or "See the menu".', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
