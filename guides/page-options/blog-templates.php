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

foreach ( ltt_dive_in_get_blog_templates() as $pattern ) {
	$number = preg_match( '/(\d+)\./', get_the_title( $pattern ), $match ) ? (int) $match[1] : 0;

	$templates[ ltt_dive_in_blog_template_name( $pattern ) ] = array(
		'text'    => $descriptions[ $number ] ?? __( 'A starter layout for a blog post.', 'ltt-dive-in' ),
		'pattern' => (int) $pattern->ID,
	);
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
	'summary'         => __( 'Blog templates are complete example articles. Each one already has the right sections, Story Modules, and spacing for one type of article, filled with example text and photos. Start a post from a template, replace the example content with your own, and delete the parts you do not need.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Find the template that matches your article in <strong>Available templates</strong> below.', 'ltt-dive-in' ),
		__( 'Click <strong>Start a post with this template</strong>. A new draft opens in the editor with the whole template already in it.', 'ltt-dive-in' ),
		__( 'Add a title, then replace the example text and photos with your own. Delete sections you do not need, or copy one (Ctrl+Shift+D, or Cmd+Shift+D on Mac) to add more.', 'ltt-dive-in' ),
		__( 'Set the featured image, categories, and excerpt as usual, then click <strong>Preview</strong> to check it and <strong>Publish</strong> when it is ready.', 'ltt-dive-in' ),
	),
	'items_heading'   => __( 'Available templates', 'ltt-dive-in' ),
	'items_intro'     => $templates ? sprintf(
		/* translators: %d: number of days. */
		__( 'These are the templates on this site right now. <strong>Start a post with this template</strong> creates a real draft that stays until you delete it. <strong>Try this template</strong> creates a practice draft to look around and experiment. It moves to the trash on its own %d days after your last edit, so do not write a real article in it.', 'ltt-dive-in' ),
		ltt_dive_in_block_guide_practice_lifetime_days()
	) : __( 'There are no blog templates on this site yet.', 'ltt-dive-in' ),
	'items'           => $templates,
	'tips'            => array(
		__( 'Already started a post? You can add a template from inside the editor too: click the <strong>+</strong> button at the top left, open the <strong>Patterns</strong> tab, choose <strong>Blog Templates</strong>, and click a template. It is added where your cursor is, so use an empty post or delete what is there first.', 'ltt-dive-in' ),
		__( 'A post made from a template is a copy. Your changes only affect that post, never the template or other posts that used it.', 'ltt-dive-in' ),
		__( 'Replace every example photo, so the same image does not end up in many articles.', 'ltt-dive-in' ),
		__( 'Each section of a template is a Story Module, a Content Module, or another theme block. Open its guide in <strong>Site Guide → Blocks</strong> to see what each field does.', 'ltt-dive-in' ),
		__( 'Administrators can change a template for everyone with <strong>Edit the template</strong>, or in <strong>Appearance → Design → Patterns</strong> under Blog Templates. Posts that already used it do not change.', 'ltt-dive-in' ),
		__( 'Practice drafts from this page also show up in <strong>Site Guide → Blocks</strong>, where you can move them all to the trash at once.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The new post has no title.', 'ltt-dive-in' ) => __( 'That is expected. Type your article title at the top of the editor before you save.', 'ltt-dive-in' ),
		__( 'I do not see Blog Templates in the Patterns tab.', 'ltt-dive-in' ) => __( 'Make sure you are editing a blog post, not a page. If it is still missing, ask an administrator to check that the templates are in the "Blog Templates" category in Appearance → Design → Patterns.', 'ltt-dive-in' ),
	),
);
