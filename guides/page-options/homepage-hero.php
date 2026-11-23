<?php
/**
 * Site Guide: Homepage Hero.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Homepage Hero', 'ltt-dive-in' ),
	'group'           => 'pages',
	'order'           => 5,
	'icon'            => 'admin-home',
	'where'           => __( 'Editing the home page, in the "Homepage Hero" box below the content', 'ltt-dive-in' ),
	'applies'         => __( 'The home page only.', 'ltt-dive-in' ),
	'short'           => __( 'The full-screen video, title, and buttons at the top of the home page.', 'ltt-dive-in' ),
	'summary'         => __( 'The big opening section of the home page: a background video (or photo), a title, a short description, up to two buttons, and a small link that scrolls down to the rest of the page.', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Hero title', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The main heading of the home page. Keep it short enough to fit on one line. Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Description', 'ltt-dive-in' ),
			'description' => __( 'A sentence or two under the title. Keep it short so it stays readable over the video.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Background video', 'ltt-dive-in' ),
			'description' => __( 'A silent, landscape MP4 from the Media Library. It plays muted and on a loop. Keep the file small (ideally under 10 MB) so the page loads fast.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Video poster', 'ltt-dive-in' ),
			'description' => __( 'A still photo shown while the video loads, and instead of the video for visitors who turn off motion on their device. Use a frame from the video so the switch is smooth.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary link / Secondary link', 'ltt-dive-in' ),
			'description' => __( 'Up to two buttons under the description, like "Plan your trip".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Scroll link label', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The small text at the bottom of the hero that scrolls down to the content, like "Explore". Up to 40 characters.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Pick a video without fast cuts or flashing. Calm footage looks better behind text and is easier on visitors.', 'ltt-dive-in' ),
		__( 'Always set a video poster. Without it, visitors on slow connections or with reduced motion see an empty background.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
