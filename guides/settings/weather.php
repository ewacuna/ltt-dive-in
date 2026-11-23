<?php
/**
 * Site Guide: Weather settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Weather', 'ltt-dive-in' ),
	'group'           => 'settings',
	'icon'            => 'cloud',
	'menu'            => __( 'Settings → Weather', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'options-general.php',
		'slug'       => 'ltt-dive-in-weather-settings',
		'capability' => 'manage_options',
	),
	'short'           => __( 'Connects the site to OpenWeather for the header temperature and the Weather page.', 'ltt-dive-in' ),
	'summary'         => __( 'The temperature in the header and the forecast on the Weather page come from OpenWeather, an outside weather service. This screen holds the key that lets the site get that data. The site checks the weather in the background, so visitors never wait for it.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The temperature and icon in the header.', 'ltt-dive-in' ),
		__( 'The Weather page under Current Conditions.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'OpenWeather API key', 'ltt-dive-in' ),
			'description' => __( 'The key from your OpenWeather account. It must be a key for the <strong>One Call</strong> API. Once saved it is hidden. Check your OpenWeather plan\'s terms in case it asks you to credit OpenWeather on the site.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'OpenWeather key', 'ltt-dive-in' ),
			'check' => static function () {
				$key = function_exists( 'ltt_dive_in_get_header_option' ) ? (string) ltt_dive_in_get_header_option( 'ltt_dive_in_openweather_api_key' ) : '';

				return ltt_dive_in_settings_guide_key_check( $key, false, __( 'The header temperature and the Weather page stay empty until a key is added.', 'ltt-dive-in' ) );
			},
		),
	),
	'tips'            => array(
		__( 'After you add or change the key, give the site a few minutes to get the first report before checking the header.', 'ltt-dive-in' ),
		__( 'Local, staging, and live sites each need the key saved on their own Settings → Weather screen.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The weather does not appear even though the key is set.', 'ltt-dive-in' ) => __( 'New OpenWeather keys can take up to a couple of hours to start working. Also check that your OpenWeather plan includes the One Call API.', 'ltt-dive-in' ),
	),
);
