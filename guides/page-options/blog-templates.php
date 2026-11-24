<?php
/**
 * Site Guide: Blog Templates patterns.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// What each starter article is for, keyed by its number in the pattern title.
$descriptions = array(
	1 => __( 'A profile of one place or business: text sections, two Side by Side sections with a photo, a photo gallery, a Responsible Travel Tip, and an FAQ.', 'ltt-dive-in' ),
	2 => __( 'A numbered list of places, like "11 upscale restaurants": one Listicle section per place, a comparison table, an FAQ, and a closing.', 'ltt-dive-in' ),
	3 => __( 'A seasonal round-up: numbered sections, each with several Event entries, then resource links, an FAQ, and a closing.', 'ltt-dive-in' ),
	4 => __( 'A topic explained through questions and answers, with a comparison table and a closing.', 'ltt-dive-in' ),
	5 => __( 'A day-by-day trip: a header for each day, Morning, Midday, Afternoon, and Evening stops, a pair of photos, an FAQ, and a closing.', 'ltt-dive-in' ),
	6 => __( 'A guide for one kind of traveler: sections with numbered lists, bulleted lists, and photos, plus travel tips, an FAQ, and a closing.', 'ltt-dive-in' ),
	7 => __( 'A guide to one town or area: sections with numbered lists, bulleted lists, and photos, plus an FAQ and a closing.', 'ltt-dive-in' ),
);

$templates = array();
$term      = get_term_by( 'name', 'Blog Templates', 'wp_pattern_category' );

if ( $term && ! is_wp_error( $term ) ) {
	$patterns = get_posts(
		array(
			'post_type'      => 'wp_block',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_pattern_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
		)
	);

	foreach ( $patterns as $pattern ) {
		$title  = get_the_title( $pattern );
		$number = preg_match( '/(\d+)\./', $title, $match ) ? (int) $match[1] : 0;

		$templates[ preg_replace( '/^Blog Template:\s*\d+\.\s*/', '', $title ) ] = $descriptions[ $number ] ?? __( 'A starter layout for a blog post.', 'ltt-dive-in' );
	}
}

return array(
	'updated'         => '2026-11-24',
	'title'           => __( 'Blog templates', 'ltt-dive-in' ),
	'group'           => 'posts',
	'order'           => 5,
	'icon'            => 'welcome-write-blog',
	'where'           => __( 'Post editor, block inserter (the + button), Patterns tab, "Blog Templates"', 'ltt-dive-in' ),
	'applies'         => __( 'Blog posts.', 'ltt-dive-in' ),
	'short'           => __( 'Ready-made article layouts to start a new blog post.', 'ltt-dive-in' ),
	'summary'         => __( 'Blog templates are complete articles you can drop into a new post as a starting point. Each one already has the right sections, Story Modules, and spacing for a type of article, filled with example text and photos. You replace the example content with your own, and delete the parts you do not need.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Create a new post and give it a title.', 'ltt-dive-in' ),
		__( 'Click the <strong>+</strong> button at the top left of the editor and open the <strong>Patterns</strong> tab.', 'ltt-dive-in' ),
		__( 'Choose the <strong>Blog Templates</strong> category and click the template that matches your article.', 'ltt-dive-in' ),
		__( 'Replace the example text and photos with your own. Delete sections you do not need, or copy one (Ctrl+Shift+D, or Cmd+Shift+D on Mac) to add more.', 'ltt-dive-in' ),
		__( 'Set the featured image, categories, and excerpt as usual, then publish.', 'ltt-dive-in' ),
	),
	'items_heading'   => __( 'Available templates', 'ltt-dive-in' ),
	'items_intro'     => $templates ? __( 'These are the templates on this site right now. Pick the one closest to the article you want to write.', 'ltt-dive-in' ) : __( 'There are no blog templates on this site yet.', 'ltt-dive-in' ),
	'items'           => $templates,
	'tips'            => array(
		__( 'Inserting a template copies it into your post. Your changes only affect that post, never the template or other posts that used it.', 'ltt-dive-in' ),
		__( 'Replace every example photo, so the same image does not end up in many articles.', 'ltt-dive-in' ),
		__( 'Insert the template into an empty post. Inserting it into a post that already has content adds the whole article below what is there.', 'ltt-dive-in' ),
		__( 'Each section of a template is explained in the Story Module guide under Site Guide → Blocks.', 'ltt-dive-in' ),
		__( 'Administrators can change a template for everyone in <strong>Appearance → Design → Patterns</strong>, under Blog Templates. Posts that already used it do not change.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'I do not see Blog Templates in the Patterns tab.', 'ltt-dive-in' ) => __( 'Make sure you are editing a blog post, not a page. If it is still missing, ask an administrator to check that the templates are in the "Blog Templates" category in Appearance → Design → Patterns.', 'ltt-dive-in' ),
	),
);
