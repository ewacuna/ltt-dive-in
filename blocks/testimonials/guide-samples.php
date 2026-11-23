<?php
/**
 * Block Guide samples for Testimonials.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'default' => array(
		'ltt_dive_in_testimonials_style' => 'default',
		'ltt_dive_in_testimonials_items' => array(
			array(
				'quote' => __( 'We came for a weekend and stayed a whole week. The lake is even bluer than in the photos.', 'ltt-dive-in' ),
				'photo' => ltt_dive_in_block_guide_photo( 3 ),
				'name'  => __( 'Maria Lopez', 'ltt-dive-in' ),
				'role'  => __( 'Visitor from Sacramento', 'ltt-dive-in' ),
			),
			array(
				'quote' => __( 'The team helped us plan every detail of our retreat. Our group still talks about it.', 'ltt-dive-in' ),
				'photo' => ltt_dive_in_block_guide_photo( 4 ),
				'name'  => __( 'James Carter', 'ltt-dive-in' ),
				'role'  => __( 'Event planner', 'ltt-dive-in' ),
			),
			array(
				'quote' => __( 'First time skiing and I was on a blue run by day three. Cannot wait to come back.', 'ltt-dive-in' ),
				'name'  => __( 'Ana Ruiz', 'ltt-dive-in' ),
				'role'  => __( 'Visitor from San Jose', 'ltt-dive-in' ),
			),
		),
	),
);
