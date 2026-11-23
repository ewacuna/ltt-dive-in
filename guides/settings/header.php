<?php
/**
 * Site Guide: Header settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Header', 'ltt-dive-in' ),
	'group'           => 'appearance',
	'icon'            => 'table-row-before',
	'menu'            => __( 'Appearance → Header Settings', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'themes.php',
		'slug'       => 'ltt-dive-in-header-settings',
		'capability' => 'edit_theme_options',
	),
	'short'           => __( 'Where the weather in the header links to.', 'ltt-dive-in' ),
	'summary'         => __( 'The header shows the current Lake Tahoe temperature and a weather icon. This screen sets the page that opens when visitors click it. The header menus themselves are not edited here.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The weather indicator in the header, on every page.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Weather link', 'ltt-dive-in' ),
			'description' => __( 'The page the weather opens, usually the Weather page under Current Conditions. Leave it empty and the weather still shows, but it is not clickable.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Weather link', 'ltt-dive-in' ),
			'check' => static function () {
				$link = function_exists( 'ltt_dive_in_get_header_option' ) ? ltt_dive_in_get_header_option( 'ltt_dive_in_header_weather_link', array() ) : array();

				return is_array( $link ) && ! empty( $link['url'] )
					? array(
						'state' => 'ok',
						'text'  => __( 'Set', 'ltt-dive-in' ),
					)
					: array(
						'state' => 'off',
						'text'  => __( 'Not set. The weather in the header is not clickable.', 'ltt-dive-in' ),
					);
			},
		),
	),
	'tips'            => array(
		__( 'The header menus are edited in <strong>Appearance → Menus</strong>: <strong>Primary Menu</strong> is the main navigation and <strong>Top Header</strong> is the small row of links above it.', 'ltt-dive-in' ),
		__( 'The temperature comes from OpenWeather, which is connected in <strong>Settings → Weather</strong>. If the weather disappears from the header, check that screen first.', 'ltt-dive-in' ),
		__( 'The banner above the header is the Site Alert, which has its own screen in Appearance.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The weather is missing from the header.', 'ltt-dive-in' ) => __( 'The weather only appears once the site has a fresh report from OpenWeather. Right after a change or a cache clear it can take a few minutes. If it stays missing, check the key in Settings → Weather.', 'ltt-dive-in' ),
	),
);
