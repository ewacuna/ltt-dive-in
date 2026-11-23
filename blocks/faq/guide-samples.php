<?php
/**
 * Block Guide samples for Interactive Accordions.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$questions = array(
	array(
		'question' => __( 'Do I need a permit to visit Emerald Bay?', 'ltt-dive-in' ),
		'answer'   => __( '<p>No permit is needed for a day visit. Parking fills up early in summer, so plan to arrive before 9 a.m. or come by shuttle.</p>', 'ltt-dive-in' ),
		'link'     => ltt_dive_in_block_guide_link( __( 'See parking options', 'ltt-dive-in' ) ),
	),
	array(
		'question' => __( 'Are dogs allowed on the beaches?', 'ltt-dive-in' ),
		'answer'   => __( '<p>Some beaches welcome dogs on a leash and others do not allow them at all. Check the signs at each entrance.</p>', 'ltt-dive-in' ),
	),
	array(
		'question' => __( 'When is the best time to see fall colors?', 'ltt-dive-in' ),
		'answer'   => __( '<p>Aspens usually turn gold from late September to mid October, depending on the weather that year.</p>', 'ltt-dive-in' ),
	),
	array(
		'question' => __( 'Can I swim in the lake?', 'ltt-dive-in' ),
		'answer'   => __( '<p>Yes. The water stays cold even in August, so swim close to shore and never alone.</p>', 'ltt-dive-in' ),
	),
);

return array(
	'global'       => array(
		'ltt_dive_in_home_faq_variant'     => 'global',
		'ltt_dive_in_home_faq_heading'     => __( 'Good to know before you go', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_description' => __( 'Quick answers to the questions visitors ask us most.', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_cta'         => ltt_dive_in_block_guide_link( __( 'See all FAQs', 'ltt-dive-in' ) ),
		'ltt_dive_in_home_faq_items'       => $questions,
	),
	'side_by_side' => array(
		'ltt_dive_in_home_faq_variant'     => 'side_by_side',
		'ltt_dive_in_home_faq_heading'     => __( 'Planning a summer trip?', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_description' => __( 'A few things worth knowing before you pack.', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_cta'         => ltt_dive_in_block_guide_link( __( 'Contact us', 'ltt-dive-in' ) ),
		'ltt_dive_in_home_faq_items'       => array_slice( $questions, 0, 3 ),
	),
	'toggle'       => array(
		'ltt_dive_in_home_faq_variant'       => 'toggle',
		'ltt_dive_in_home_faq_heading'       => __( 'Frequently asked questions', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_description'   => __( 'Pick a topic to see the questions about it.', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_toggle_tagline' => __( 'Plan your trip', 'ltt-dive-in' ),
		'ltt_dive_in_home_faq_toggle_topics' => array(
			array(
				'label' => __( 'Summer', 'ltt-dive-in' ),
				'items' => array_slice( $questions, 0, 2 ),
			),
			array(
				'label' => __( 'Winter', 'ltt-dive-in' ),
				'items' => array(
					array(
						'question' => __( 'Do I need chains to drive to Tahoe?', 'ltt-dive-in' ),
						'answer'   => __( '<p>Chain controls are common during storms. Carry chains from November to April and check road conditions before you leave.</p>', 'ltt-dive-in' ),
					),
					array(
						'question' => __( 'Which ski resort is best for beginners?', 'ltt-dive-in' ),
						'answer'   => __( '<p>Most resorts have a good beginner area and ski school. Smaller resorts tend to be quieter and cheaper for a first day.</p>', 'ltt-dive-in' ),
					),
				),
			),
			array(
				'label' => __( 'Getting here', 'ltt-dive-in' ),
				'items' => array(
					array(
						'question' => __( 'What is the closest airport?', 'ltt-dive-in' ),
						'answer'   => __( '<p>Reno Tahoe International Airport is about an hour from the north and south shores.</p>', 'ltt-dive-in' ),
					),
				),
			),
		),
	),
);
