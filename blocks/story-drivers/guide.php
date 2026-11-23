<?php
/**
 * Block Guide content for Story Drivers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'Shows blog posts as cards. It can pick the latest posts for you (and keep updating as you publish) or show the exact posts you choose. There are six layouts, from one big featured story to a full blog grid with filters.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Story Drivers</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Layout</strong>.', 'ltt-dive-in' ),
		__( 'Under <strong>Stories</strong>, choose <strong>Dynamic</strong> to show recent posts automatically or <strong>Manual</strong> to pick them yourself.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Choosing the stories', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Layout', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'How the stories are shown. See the layouts below.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Stories', 'ltt-dive-in' ),
			'description' => __( '<strong>Dynamic</strong> shows the most recent posts and updates by itself. <strong>Manual</strong> shows only the posts you pick, in your order.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Posts', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Manual only. Search and pick the posts, then drag to order them. Each one must be published and have a featured image. How many you need depends on the layout.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Categories / Tags', 'ltt-dive-in' ),
			'description' => __( 'Dynamic only. Limit the posts to some categories or tags, for example only "Winter" stories. Leave both empty to use all posts.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Number of stories', 'ltt-dive-in' ),
			'description' => __( 'Dynamic only, for 1 Up, Carousel, and Hero + Page Grid. The other layouts always use their fixed number.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Leave out the current post', 'ltt-dive-in' ),
			'description' => __( 'Dynamic only. On by default, so a blog post never recommends itself.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'one_up'     => array(
			'name'        => __( '1 Up (Feature Story)', 'ltt-dive-in' ),
			'description' => __( 'One big story across the full width. With more than one story it becomes a slideshow. This layout has no heading of its own.', 'ltt-dive-in' ),
			'notes'       => array( __( '1 to 5 stories.', 'ltt-dive-in' ) ),
		),
		'two_up'     => array(
			'name'        => __( '2 Up', 'ltt-dive-in' ),
			'description' => __( 'A heading and two story cards side by side.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 2 stories.', 'ltt-dive-in' ) ),
		),
		'three_up'   => array(
			'name'        => __( '3 Up', 'ltt-dive-in' ),
			'description' => __( 'A heading and three story cards in a row.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 3 stories.', 'ltt-dive-in' ) ),
		),
		'four_up'    => array(
			'name'        => __( '4 Up', 'ltt-dive-in' ),
			'description' => __( 'A heading and four story cards.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 4 stories.', 'ltt-dive-in' ) ),
		),
		'carousel'   => array(
			'name'        => __( '3+ Up Carousel', 'ltt-dive-in' ),
			'description' => __( 'A heading and a row of story cards that visitors slide through. You can add category buttons on top so visitors can filter the stories.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Enable category filters', 'ltt-dive-in' ),
					'description' => __( 'Shows a row of category buttons above the carousel.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Filter categories', 'ltt-dive-in' ),
					'description' => __( 'Pick 2 to 5 categories for the buttons. A category with no stories in the carousel is hidden.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array( __( '3 to 10 stories.', 'ltt-dive-in' ) ),
		),
		'hero_grid'  => array(
			'name'        => __( 'Hero + Page Grid', 'ltt-dive-in' ),
			'description' => __( 'A featured slideshow on top and a full grid of stories below, with a category filter, a Latest / Oldest sort, a search box, and Load More. This is the layout for the main blog page.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Grid stories per load', 'ltt-dive-in' ),
					'description' => __( 'How many stories the grid shows at first and adds with each Load More. Between 3 and 12.', 'ltt-dive-in' ),
				),
			),
			'notes'       => array(
				__( '1 to 5 stories in the slideshow. The Stories, Posts, and Number of stories settings apply to the slideshow only. The grid lists every published post with a featured image, leaving out the ones already in the slideshow.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'All layouts except 1 Up have a section heading (required), plus an optional category tag and introduction. 2 Up, 3 Up, and 4 Up can also have two buttons.', 'ltt-dive-in' ),
		__( 'Cards use the post\'s title, featured image, and category. To change a card, edit the post.', 'ltt-dive-in' ),
		__( 'Dynamic is the easier choice for most pages, since it stays fresh without anyone touching it. Use Manual for curated lists, like "Our favorite fall stories".', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'WordPress says "Choose between X and Y for this layout."', 'ltt-dive-in' ) => __( 'You picked more or fewer posts than the layout allows. Check the numbers in the layouts above.', 'ltt-dive-in' ),
		__( 'WordPress says "Every post must be published and have a featured image."', 'ltt-dive-in' ) => __( 'One of the posts you picked is a draft or has no featured image. Fix that post or pick another one.', 'ltt-dive-in' ),
		__( 'A Dynamic block shows fewer stories than expected.', 'ltt-dive-in' ) => __( 'There are not enough published posts with a featured image in the categories or tags you chose. Remove a filter or publish more posts.', 'ltt-dive-in' ),
	),
);
