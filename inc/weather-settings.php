<?php
/**
 * Weather settings page.
 *
 * PHP-registered so the OpenWeather API key keeps its existing field key and
 * stored option name after moving out of Header Settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Weather settings page and its field group.
 */
function ltt_dive_in_register_weather_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Weather Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Weather', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-weather-settings',
			'parent_slug' => 'options-general.php',
			'capability'  => 'manage_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_weather_settings',
			'title'    => __( 'LTT Dive In — OpenWeather', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'          => 'field_ltt_dive_in_openweather_api_key',
					'label'        => __( 'OpenWeather API key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_openweather_api_key',
					'type'         => 'password',
					'instructions' => __( 'Used only by the server to load One Call weather for the Weather page locations; the header shows the first location. The key is never included in front-end markup or JavaScript. Confirm and follow the attribution requirements for your OpenWeather plan.', 'ltt-dive-in' ),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-weather-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_weather_settings' );
