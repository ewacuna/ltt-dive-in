<?php
/**
 * Block Guide content for Testimonials.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'summary'         => __( 'Shows what people say about Lake Tahoe or about you. With one quote it is a single testimonial. With two or more it becomes a carousel that visitors move through with arrows and dots.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Testimonials</strong>.', 'ltt-dive-in' ),
		__( 'Pick a <strong>Style</strong>.', 'ltt-dive-in' ),
		__( 'Add your quotes under <strong>Testimonials</strong>.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Style', 'ltt-dive-in' ),
			'description' => __( '<strong>Default</strong> uses the regular font. <strong>Light italic quote</strong> uses a lighter, slanted font that feels more personal.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Testimonials', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Add 1 to 10 quotes, in the order you want them shown.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Quote', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The words themselves. Leave out the quotation marks, the theme adds them. Up to 200 characters, so pick the best line rather than the whole review.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Photo', 'ltt-dive-in' ),
			'description' => __( 'An optional square headshot of the person.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Name', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Who said it. Up to 60 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Role or location', 'ltt-dive-in' ),
			'description' => __( 'A short line under the name, like "Visitor from Reno" or "Event planner". Up to 60 characters.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'In the editor all quotes are listed one under the other so you can check them. On the live page they turn into a carousel.', 'ltt-dive-in' ),
		__( 'Use real quotes from real people, and get their OK before using their name and photo.', 'ltt-dive-in' ),
		__( 'The Content Module has a Testimonial Block layout that does the same thing. Either one works.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Add at least one testimonial with a quote and a name."', 'ltt-dive-in' ) => __( 'Every testimonial needs both a quote and a name. Fill in whichever one is missing.', 'ltt-dive-in' ),
	),
);
