<?php
/**
 * Customizer settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme Customizer options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function ltt_dive_in_customize_register( $wp_customize ) {
	$wp_customize->add_setting(
		'ltt_dive_in_footer_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ltt_dive_in_footer_text',
		array(
			'label'   => __( 'Footer text', 'ltt-dive-in' ),
			'section' => 'title_tagline',
			'type'    => 'text',
		)
	);
}
add_action( 'customize_register', 'ltt_dive_in_customize_register' );
