<?php
/**
 * Site Guide: End-of-Post Page Driver box.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'End-of-Post Page Driver', 'ltt-dive-in' ),
	'group'           => 'posts',
	'order'           => 30,
	'icon'            => 'excerpt-view',
	'where'           => __( 'Post editor, "End-of-Post Page Driver" box below the content', 'ltt-dive-in' ),
	'applies'         => __( 'Blog posts.', 'ltt-dive-in' ),
	'short'           => __( 'Changes the "See More" cards at the end of this post.', 'ltt-dive-in' ),
	'summary'         => __( 'Every post ends with three cards pointing to related posts. By default they are picked automatically. This box lets you change that for one post: pick the posts yourself, write your own cards, or hide the section.', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Display', 'ltt-dive-in' ),
			'description' => __( '<strong>Related posts</strong> (automatic, the default), <strong>Choose posts</strong>, <strong>Custom cards</strong>, or <strong>Hide</strong>. The section heading for Related posts and Choose posts comes from Posts → Blog Settings.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Posts', 'ltt-dive-in' ),
			'description' => __( 'With Choose posts. Up to three published posts with featured images.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Category tag, Title, Body copy, Background', 'ltt-dive-in' ),
			'description' => __( 'With Custom cards. The heading for this post only. If the Title is empty, the section is hidden.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Cards', 'ltt-dive-in' ),
			'description' => __( 'With Custom cards. Three cards, each with a background image, a title (up to 50 characters), short copy (up to 150 characters), and an optional button.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Leave it on Related posts for most posts. Use Choose posts for series, where the next article matters.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
