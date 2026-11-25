<?php
/**
 * Site Guide: Blog settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-24',
	'title'           => __( 'Blog Settings', 'ltt-dive-in' ),
	'group'           => 'posts',
	'icon'            => 'admin-post',
	'menu'            => __( 'Posts → Blog Settings', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'edit.php',
		'slug'       => 'ltt-dive-in-blog-settings',
		'capability' => 'edit_others_posts',
	),
	'short'           => __( 'The "See More" section at the end of every blog post.', 'ltt-dive-in' ),
	'summary'         => __( 'Every blog post ends with a section of three cards that point to related posts. The cards are picked automatically (same category first, then same tag, then the newest posts). This screen sets the heading above those cards for all posts at once.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The end of every blog post.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Category tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the title. It starts as "Experiences". Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Title', 'ltt-dive-in' ),
			'description' => __( 'The section title. It starts as "See More". If you clear it, the section disappears from every post. Up to 120 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Body copy', 'ltt-dive-in' ),
			'description' => __( 'An optional sentence under the title. Up to 300 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Background', 'ltt-dive-in' ),
			'description' => __( 'White or Navy behind the cards.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'End-of-post section', 'ltt-dive-in' ),
			'check' => static function () {
				$title = function_exists( 'get_field' ) ? trim( (string) get_field( 'field_ltt_dive_in_blog_driver_title', 'option' ) ) : '';

				return '' !== $title
					? array(
						'state' => 'ok',
						'text'  => __( 'Showing on posts', 'ltt-dive-in' ),
					)
					: array(
						'state' => 'off',
						'text'  => __( 'Hidden on all posts because the title is empty', 'ltt-dive-in' ),
					);
			},
		),
	),
	'tips'            => array(
		__( 'Each post can change its own end section in the <strong>End-of-Post Page Driver</strong> box of the post editor: pick up to three posts by hand, write custom cards, or hide the section on that post.', 'ltt-dive-in' ),
		__( 'Only published posts with a featured image are used as cards, so keep featured images on all posts.', 'ltt-dive-in' ),
		__( 'Looking for the ready-made article layouts? They are in <strong>Site Guide → Page options → Blog templates</strong>, with buttons to start a post from each one.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
