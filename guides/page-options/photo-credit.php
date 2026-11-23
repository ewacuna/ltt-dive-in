<?php
/**
 * Site Guide: Photo credit and image text in the Media Library.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Photo credit and image text', 'ltt-dive-in' ),
	'group'           => 'media',
	'order'           => 10,
	'icon'            => 'camera',
	'where'           => __( 'Media Library, when you open an image', 'ltt-dive-in' ),
	'applies'         => __( 'Every image in the Media Library.', 'ltt-dive-in' ),
	'short'           => __( 'Alt text, caption, and photographer credit for each image.', 'ltt-dive-in' ),
	'summary'         => __( 'The text you save with an image in the Media Library is reused everywhere that image appears, so it only needs to be written once. The theme adds a Photo credit field to the standard WordPress fields.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'Alt text: read by screen readers and search engines wherever the image is used.', 'ltt-dive-in' ),
		__( 'Caption and Photo credit: under the featured image at the top of blog posts, shown as "Photography: name".', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Alternative Text', 'ltt-dive-in' ),
			'description' => __( 'A short description of what is in the photo, for people who cannot see it. For example, "Kayakers paddling in Emerald Bay on a sunny morning". The Static Image Cluster block will not save until every image in it has alt text.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Caption', 'ltt-dive-in' ),
			'description' => __( 'Optional text shown under the image in some places, like the top of blog posts.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Photo credit', 'ltt-dive-in' ),
			'description' => __( 'The photographer\'s name. Up to 100 characters. Leave it empty if no credit is needed.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Do not start alt text with "Image of" or "Photo of". Screen readers already announce that it is an image.', 'ltt-dive-in' ),
		__( 'Logos only need the organization\'s name as alt text.', 'ltt-dive-in' ),
		__( 'Only use photos you have the rights to, and credit the photographer when the license asks for it.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
