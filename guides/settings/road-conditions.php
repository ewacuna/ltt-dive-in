<?php
/**
 * Site Guide: Road Conditions settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Road Conditions', 'ltt-dive-in' ),
	'group'           => 'settings',
	'icon'            => 'car',
	'menu'            => __( 'Settings → Road Conditions', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'options-general.php',
		'slug'       => 'ltt-dive-in-road-conditions-settings',
		'capability' => 'manage_options',
	),
	'short'           => __( 'Connects the Roads page to Nevada 511.', 'ltt-dive-in' ),
	'summary'         => __( 'The Roads page under Current Conditions shows chain controls, closures, and road cameras around the lake. California information comes from Caltrans and needs no setup. Nevada information comes from Nevada 511, which needs the key saved on this screen.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The Roads page under Current Conditions (Nevada roads).', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Connection status', 'ltt-dive-in' ),
			'description' => __( 'Not something you fill in. It tells you whether a key is saved and whether the last request to Nevada 511 worked.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Nevada 511 developer key', 'ltt-dive-in' ),
			'description' => __( 'The key from the Nevada 511 Developers page. Once saved it is hidden. To change it, paste a new key and save.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Remove saved key', 'ltt-dive-in' ),
			'description' => __( 'Turn it on and save to delete the key. Nevada roads stop showing until a new key is added; California roads keep working.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Nevada 511 key', 'ltt-dive-in' ),
			'check' => static function () {
				return ltt_dive_in_settings_guide_key_check(
					function_exists( 'ltt_dive_in_get_nv511_api_key' ) ? ltt_dive_in_get_nv511_api_key() : '',
					function_exists( 'ltt_dive_in_nv511_api_key_is_constant' ) && ltt_dive_in_nv511_api_key_is_constant(),
					__( 'Nevada roads do not show on the Roads page.', 'ltt-dive-in' )
				);
			},
		),
		array(
			'label' => __( 'Last request to Nevada 511', 'ltt-dive-in' ),
			'check' => static function () {
				return ltt_dive_in_settings_guide_last_request_check( 'ltt_dive_in_nv511_last_status' );
			},
		),
	),
	'tips'            => array(
		__( 'Road information refreshes in the background about every 10 minutes, so the page is always fast for visitors.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'Road information is missing on the Roads page.', 'ltt-dive-in' ) => __( 'Right after a change or a cache clear, the site needs a few minutes to download the information again. Reload later. If only Nevada roads are missing, check the key and the Current status above.', 'ltt-dive-in' ),
	),
);
