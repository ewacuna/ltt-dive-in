<?php
/**
 * Site Guide: Page Hero.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Page Hero', 'ltt-dive-in' ),
	'group'           => 'pages',
	'order'           => 10,
	'icon'            => 'cover-image',
	'where'           => __( 'Page editor, in the "Page Hero" box below the title, plus the page\'s title, excerpt, and featured image', 'ltt-dive-in' ),
	'applies'         => __( 'Regular pages. Not the home page, Blank Canvas pages, or the Current Conditions, Events, and Event Submission templates, which have their own top section.', 'ltt-dive-in' ),
	'short'           => __( 'The photo, title, and buttons at the top of every page.', 'ltt-dive-in' ),
	'summary'         => __( 'Every regular page opens with a hero: a large photo with the page title and a short description on top. Most of it comes from things the page already has, so you only fill in the buttons here.', 'ltt-dive-in' ),
	'fields_intro'    => __( 'The hero is built from these parts of the page:', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Page title', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The title at the top of the editor becomes the big heading of the hero, and the main heading of the page for search engines and screen readers.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Excerpt', 'ltt-dive-in' ),
			'description' => __( 'The short text under the title. Find it in the right sidebar under <strong>Page → Excerpt</strong>. Leave it empty to show only the title.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Featured image', 'ltt-dive-in' ),
			'description' => __( 'The background photo, set in the right sidebar under <strong>Page → Featured image</strong>. It is also the photo used when this page shows up as a card in other blocks.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Primary link / Secondary link', 'ltt-dive-in' ),
			'description' => __( 'From the Page Hero box. Up to two buttons in the hero. Optional.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'The design of the hero (Standard or Immersive) and the header color are chosen in the <strong>Page Header</strong> box in the sidebar.', 'ltt-dive-in' ),
		__( 'Use a wide, high quality photo with a calm area where the title sits, so the white text stays readable.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The hero has no photo.', 'ltt-dive-in' ) => __( 'The page has no featured image. Set one in the right sidebar.', 'ltt-dive-in' ),
	),
);
