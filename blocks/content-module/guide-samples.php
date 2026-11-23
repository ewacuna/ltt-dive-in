<?php
/**
 * Block Guide samples for Content Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p = 'ltt_dive_in_content_module_';

$cell = static function ( $type, $text = '' ) {
	return array(
		'type' => $type,
		'text' => $text,
	);
};

return array(
	'side_by_side'      => array(
		$p . 'variant'         => 'side_by_side',
		$p . 'side_title'      => __( 'Why fall is the best kept secret at Tahoe', 'ltt-dive-in' ),
		$p . 'side_body'       => __( '<p>The summer crowds are gone, the aspens turn gold, and the lake is still warm enough for a paddle in the afternoon. Hotels drop their rates and you can get a table anywhere without a reservation.</p>', 'ltt-dive-in' ),
		$p . 'side_background' => ltt_dive_in_block_guide_photo( 0 ),
		$p . 'side_ctas'       => array(
			array( 'link' => ltt_dive_in_block_guide_link( __( 'Plan a fall trip', 'ltt-dive-in' ) ) ),
			array( 'link' => ltt_dive_in_block_guide_link( __( 'See fall events', 'ltt-dive-in' ) ) ),
		),
	),
	'copy_block'        => array(
		$p . 'variant'      => 'copy_block',
		$p . 'copy_content' => __( '<h2>Leave no trace</h2><p>Lake Tahoe stays clear because visitors take care of it. A few simple habits make a big difference.</p><ul><li>Pack out everything you bring in.</li><li>Stay on marked trails.</li><li>Keep your distance from wildlife.</li></ul><p>Read more about <a href="/">how to protect the lake</a>.</p>', 'ltt-dive-in' ),
	),
	'copy_block_list'   => array(
		$p . 'variant'           => 'copy_block_list',
		$p . 'copy_list_heading' => __( 'What to pack for a day on the lake', 'ltt-dive-in' ),
		$p . 'copy_lists'        => array(
			array(
				'list_type' => 'icon',
				'items'     => array(
					array( 'text' => __( 'Sunscreen and a hat. The sun is strong at this altitude.', 'ltt-dive-in' ) ),
					array( 'text' => __( 'A warm layer for the evening.', 'ltt-dive-in' ) ),
					array( 'text' => __( 'Plenty of water.', 'ltt-dive-in' ) ),
				),
			),
			array(
				'list_type' => 'numbered',
				'items'     => array(
					array( 'text' => __( 'Check the weather the night before.', 'ltt-dive-in' ) ),
					array( 'text' => __( 'Get to the beach early.', 'ltt-dive-in' ) ),
					array( 'text' => __( 'Head out before the afternoon wind picks up.', 'ltt-dive-in' ) ),
				),
			),
		),
	),
	'icon_block'        => array(
		$p . 'variant'                => 'icon_block',
		$p . 'icon_heading'           => __( 'Why stay on the North Shore', 'ltt-dive-in' ),
		$p . 'icon_intro'             => __( 'Quiet beaches, small towns, and easy access to the best trails.', 'ltt-dive-in' ),
		$p . 'icon_cta'               => ltt_dive_in_block_guide_link( __( 'Explore the North Shore', 'ltt-dive-in' ) ),
		$p . 'icon_background_color'  => 'navy',
		$p . 'icon_blurbs'            => array(
			array(
				'icon'  => ltt_dive_in_block_guide_icon( 0 ),
				'color' => 'blue',
				'title' => __( 'Free parking', 'ltt-dive-in' ),
				'copy'  => __( 'Most beaches have free lots that open at sunrise.', 'ltt-dive-in' ),
			),
			array(
				'icon'  => ltt_dive_in_block_guide_icon( 1 ),
				'color' => 'green',
				'title' => __( 'Dog friendly', 'ltt-dive-in' ),
				'copy'  => __( 'Several trails and beaches welcome dogs on a leash.', 'ltt-dive-in' ),
			),
			array(
				'icon'  => ltt_dive_in_block_guide_icon( 2 ),
				'color' => 'orange',
				'title' => __( 'Open all year', 'ltt-dive-in' ),
				'copy'  => __( 'Shops and restaurants stay open through the winter.', 'ltt-dive-in' ),
			),
		),
	),
	'newsletter_signup' => array(
		$p . 'variant'                  => 'newsletter_signup',
		$p . 'newsletter_heading'       => __( 'Get the Tahoe newsletter', 'ltt-dive-in' ),
		$p . 'newsletter_copy'          => __( 'One email a month with events, deals, and ideas for your next trip.', 'ltt-dive-in' ),
		$p . 'newsletter_aside_heading' => __( 'Planning a group trip?', 'ltt-dive-in' ),
		$p . 'newsletter_aside_copy'    => __( 'Our team can help with lodging, activities, and transportation.', 'ltt-dive-in' ),
		$p . 'newsletter_aside_cta'     => ltt_dive_in_block_guide_link( __( 'Contact the team', 'ltt-dive-in' ) ),
		$p . 'newsletter_background'    => ltt_dive_in_block_guide_photo( 1 ),
		$p . 'newsletter_form'          => ltt_dive_in_block_guide_form(),
		$p . 'form_disclosure'          => __( 'We never share your email. Read our [Privacy Policy](/).', 'ltt-dive-in' ),
	),
	'meetings_request'  => array(
		$p . 'variant'             => 'meetings_request',
		$p . 'meetings_heading'    => __( 'Plan your next meeting at Tahoe', 'ltt-dive-in' ),
		$p . 'meetings_copy'       => __( 'Tell us about your group and we will get back to you within two business days.', 'ltt-dive-in' ),
		$p . 'meetings_background' => ltt_dive_in_block_guide_photo( 2 ),
		$p . 'meetings_form'       => ltt_dive_in_block_guide_form(),
		$p . 'form_disclosure'     => __( 'Your details are only used to answer your request.', 'ltt-dive-in' ),
	),
	'stats'             => array(
		$p . 'variant'                => 'stats',
		$p . 'stats_tag'              => __( 'By the numbers', 'ltt-dive-in' ),
		$p . 'stats_heading'          => __( 'A lake like no other', 'ltt-dive-in' ),
		$p . 'stats_intro'            => __( 'Lake Tahoe is one of the largest and clearest alpine lakes in the world.', 'ltt-dive-in' ),
		$p . 'stats_cta'              => ltt_dive_in_block_guide_link( __( 'Learn about the lake', 'ltt-dive-in' ) ),
		$p . 'stats_background_color' => 'blue',
		$p . 'stats'                  => array(
			array(
				'number' => '72',
				'unit'   => __( 'miles', 'ltt-dive-in' ),
				'label'  => __( 'of shoreline around the lake', 'ltt-dive-in' ),
			),
			array(
				'number' => '1,645',
				'unit'   => __( 'ft', 'ltt-dive-in' ),
				'label'  => __( 'at its deepest point', 'ltt-dive-in' ),
			),
			array(
				'number' => '6,225',
				'unit'   => __( 'ft', 'ltt-dive-in' ),
				'label'  => __( 'above sea level', 'ltt-dive-in' ),
			),
		),
	),
	'comparison_table'  => array(
		$p . 'variant'                    => 'comparison_table',
		$p . 'comparison_heading'         => __( 'Compare ski passes', 'ltt-dive-in' ),
		$p . 'comparison_intro'           => __( 'Find the pass that fits how often you ski.', 'ltt-dive-in' ),
		$p . 'comparison_feature_heading' => __( 'Feature', 'ltt-dive-in' ),
		$p . 'comparison_columns'         => array(
			array(
				'label'             => __( 'Day pass', 'ltt-dive-in' ),
				'short_description' => __( 'For one day on the mountain', 'ltt-dive-in' ),
				'icon'              => ltt_dive_in_block_guide_icon( 0 ),
			),
			array(
				'label'             => __( 'Season pass', 'ltt-dive-in' ),
				'short_description' => __( 'For the whole winter', 'ltt-dive-in' ),
				'icon'              => ltt_dive_in_block_guide_icon( 1 ),
			),
		),
		$p . 'comparison_rows'            => array(
			array(
				'label'      => __( 'Price', 'ltt-dive-in' ),
				'column_one' => $cell( 'text', __( 'From $129', 'ltt-dive-in' ) ),
				'column_two' => $cell( 'text', __( 'From $899', 'ltt-dive-in' ) ),
			),
			array(
				'label'      => __( 'Holiday access', 'ltt-dive-in' ),
				'column_one' => $cell( 'included' ),
				'column_two' => $cell( 'included' ),
			),
			array(
				'label'      => __( 'Discounts on lessons', 'ltt-dive-in' ),
				'column_one' => $cell( 'not_included' ),
				'column_two' => $cell( 'included' ),
			),
		),
		$p . 'comparison_cta'             => ltt_dive_in_block_guide_link( __( 'Buy a pass', 'ltt-dive-in' ) ),
	),
	'testimonial_block' => array(
		$p . 'variant'           => 'testimonial_block',
		$p . 'testimonial_style' => 'default',
		$p . 'testimonials'      => array(
			array(
				'quote' => __( 'We came for a weekend and ended up staying a whole week. The lake is even bluer than in the photos.', 'ltt-dive-in' ),
				'name'  => __( 'Maria Lopez', 'ltt-dive-in' ),
				'role'  => __( 'Visitor from Sacramento', 'ltt-dive-in' ),
			),
			array(
				'quote' => __( 'The team helped us plan every detail of our retreat. Our group still talks about it.', 'ltt-dive-in' ),
				'name'  => __( 'James Carter', 'ltt-dive-in' ),
				'role'  => __( 'Event planner', 'ltt-dive-in' ),
			),
		),
	),
);
