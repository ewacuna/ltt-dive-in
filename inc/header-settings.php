<?php
/**
 * Header and homepage hero settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register global header settings and homepage hero fields.
 */
function ltt_dive_in_register_header_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Header Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Header Settings', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-header-settings',
			'parent_slug' => 'themes.php',
			'capability'  => 'edit_theme_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_header_settings',
			'title'    => __( 'LTT Dive In — Header Settings', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'          => 'field_ltt_dive_in_openweather_api_key',
					'label'        => __( 'OpenWeather API key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_openweather_api_key',
					'type'         => 'password',
					'instructions' => __( 'Used only by the server to load Tahoe City weather. The key is never included in front-end markup or JavaScript. Confirm and follow the attribution requirements for your OpenWeather plan.', 'ltt-dive-in' ),
				),
				array(
					'key'           => 'field_ltt_dive_in_header_weather_link',
					'label'         => __( 'Weather link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_header_weather_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. When provided, the weather icon and label link to the selected current-conditions page.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-header-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_homepage_hero',
			'title'    => __( 'LTT Dive In — Homepage Hero', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'          => 'field_ltt_dive_in_home_hero_content_accordion',
					'label'        => __( 'Hero content', 'ltt-dive-in' ),
					'name'         => '',
					'type'         => 'accordion',
					'instructions' => __( 'Main heading and supporting copy displayed over the hero.', 'ltt-dive-in' ),
					'open'         => 1,
					'multi_expand' => 0,
				),
				array(
					'key'           => 'field_ltt_dive_in_home_hero_title',
					'label'         => __( 'Hero title', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_home_hero_title',
					'type'          => 'text',
					'instructions'  => __( 'Main homepage heading displayed over the hero video. Keep it short enough to fit on one line.', 'ltt-dive-in' ),
					'required'      => 1,
					'maxlength'     => 40,
				),
				array(
					'key'          => 'field_ltt_dive_in_home_hero_description',
					'label'        => __( 'Description', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_home_hero_description',
					'type'         => 'textarea',
					'instructions' => __( 'Keep this concise so it remains readable over video at mobile sizes.', 'ltt-dive-in' ),
					'rows'         => 5,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_ltt_dive_in_home_hero_media_accordion',
					'label'        => __( 'Background media', 'ltt-dive-in' ),
					'name'         => '',
					'type'         => 'accordion',
					'instructions' => __( 'Optional video and fallback image used by the homepage hero.', 'ltt-dive-in' ),
					'open'         => 0,
					'multi_expand' => 0,
				),
				array(
					'key'           => 'field_ltt_dive_in_home_hero_video',
					'label'         => __( 'Background video', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_home_hero_video',
					'type'          => 'file',
					'instructions'  => __( 'Optional MP4 replacement for the bundled homepage video. Use a silent, web-optimized landscape video.', 'ltt-dive-in' ),
					'return_format' => 'id',
					'library'      => 'all',
					'mime_types'   => 'mp4',
				),
				array(
					'key'           => 'field_ltt_dive_in_home_hero_poster',
					'label'         => __( 'Video poster', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_home_hero_poster',
					'type'          => 'image',
					'instructions'  => __( 'Optional still image shown while the video loads and when motion is reduced.', 'ltt-dive-in' ),
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
				array(
					'key'          => 'field_ltt_dive_in_home_hero_links_accordion',
					'label'        => __( 'Hero links', 'ltt-dive-in' ),
					'name'         => '',
					'type'         => 'accordion',
					'instructions' => __( 'Primary, secondary, and scroll navigation labels for the hero.', 'ltt-dive-in' ),
					'open'         => 0,
					'multi_expand' => 0,
				),
				array(
					'key'           => 'field_ltt_dive_in_home_hero_primary_link',
					'label'         => __( 'Primary link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_home_hero_primary_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. The button is hidden until both a label and destination are provided.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
				array(
					'key'           => 'field_ltt_dive_in_home_hero_secondary_link',
					'label'         => __( 'Secondary link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_home_hero_secondary_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. The button is hidden until both a label and destination are provided.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
				array(
					'key'           => 'field_ltt_dive_in_home_hero_scroll_label',
					'label'         => __( 'Scroll link label', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_home_hero_scroll_label',
					'type'          => 'text',
					'required'      => 1,
					'maxlength'     => 40,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_header_settings' );

/**
 * Get an optional global header value without requiring ACF at runtime.
 *
 * @param string $field_name ACF field name.
 * @param mixed  $default    Value returned when the field is empty or unavailable.
 * @return mixed
 */
function ltt_dive_in_get_header_option( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $field_name, 'option' );

	return empty( $value ) ? $default : $value;
}

/**
 * Get an optional homepage hero value without requiring ACF at runtime.
 *
 * @param string $field_name ACF field name.
 * @param mixed  $default    Value returned when the field is empty or unavailable.
 * @return mixed
 */
function ltt_dive_in_get_home_hero_field( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$front_page_id = (int) get_option( 'page_on_front' );
	$value         = get_field( $field_name, $front_page_id ?: get_queried_object_id() );

	return empty( $value ) ? $default : $value;
}
