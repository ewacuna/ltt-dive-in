<?php
/**
 * Block Guide content for Listing Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'summary'         => __( 'A grid of cards with dropdown filters, a search box, and a Load More button. The cards build themselves from your posts, using each post\'s title, featured image, and excerpt. You set up the block once, and every new post shows up in it automatically.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts. It is usually the main content of a listing page.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Listing Module</strong>.', 'ltt-dive-in' ),
		__( 'Write the heading and choose the <strong>Content type</strong>.', 'ltt-dive-in' ),
		__( 'Pick the filters, the search, and the Load More from the dropdowns. These come from FacetWP, the plugin that runs the filtering.', 'ltt-dive-in' ),
		__( 'Set how many cards show at first and how many each Load More click adds.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Category tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the heading. Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Heading', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The section title. Up to 120 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Introduction', 'ltt-dive-in' ),
			'description' => __( 'A short paragraph under the heading. Up to 300 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Content type', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'What the cards list. Right now that is Posts.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'First dropdown filter', 'ltt-dive-in' ),
			'description' => __( 'The first filter visitors see, like Activity. Each filter must use a different category group.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Second dropdown filter', 'ltt-dive-in' ),
			'description' => __( 'The second filter, like Place.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Third dropdown filter', 'ltt-dive-in' ),
			'description' => __( 'Optional. A third filter for another category group.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Search facet', 'ltt-dive-in' ),
			'description' => __( 'Turns on the keyword search box. It searches titles, excerpts, and content while the visitor types.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Load More facet', 'ltt-dive-in' ),
			'description' => __( 'Turns on the Load More button under the cards.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Initial card count', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'How many cards show when the page opens. Between 1 and 12. Multiples of three fill the rows evenly on desktop.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Load More step', 'ltt-dive-in' ),
			'description' => __( 'How many cards each Load More click adds. Between 1 and 12.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'The cards come from each post: its title, featured image, and excerpt. To change what a card says, edit the post, not the block.', 'ltt-dive-in' ),
		__( 'Every post has a <strong>Listing Card Links</strong> box where you set up to two buttons for its card. By default the first button says "See More" and opens the post. You can change its label or point it to another page, and add a second button.', 'ltt-dive-in' ),
		__( 'A post only shows up under a filter if it has that category assigned. Check the categories on the post when something does not appear where you expect.', 'ltt-dive-in' ),
		__( 'Visitors can pick several options in one dropdown. Results match any of the options picked, and the dropdowns combine with each other and with the search.', 'ltt-dive-in' ),
		__( 'The filters, search, and Load More only work on the live page. In the editor you see a static preview with a note that says so.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor shows a warning about the filters, search, or Load More.', 'ltt-dive-in' ) => __( 'One of the FacetWP settings does not match. The warning says which one. The usual causes are two filters using the same category group, or a filter whose group is not used by the chosen content type. If the right option is not in the list, ask your developer to set it up in FacetWP.', 'ltt-dive-in' ),
		__( 'The editor says "FacetWP is not active".', 'ltt-dive-in' ) => __( 'The FacetWP plugin is turned off. The cards still show, but filters, search, and Load More will not work until it is activated again.', 'ltt-dive-in' ),
		__( 'A new post is not in the list.', 'ltt-dive-in' ) => __( 'Check that it is published. If filters still miss it, FacetWP may need to re-index, which your developer can do from Settings → FacetWP.', 'ltt-dive-in' ),
	),
);
