<?php
/**
 * Site Guide: Events (Seeker) settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Events', 'ltt-dive-in' ),
	'group'           => 'settings',
	'icon'            => 'calendar-alt',
	'menu'            => __( 'Settings → Events', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'options-general.php',
		'slug'       => 'ltt-dive-in-events-settings',
		'capability' => 'manage_options',
	),
	'short'           => __( 'Connects the site to the Seeker event calendar.', 'ltt-dive-in' ),
	'summary'         => __( 'All events on the site come from Seeker, the event calendar service. This screen holds the key that lets the site read events from Seeker and shows whether the connection is working. Event details themselves (names, dates, photos, tickets) are always edited in Seeker.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'Every Event Driver block (hero, list, and carousel).', 'ltt-dive-in' ),
		__( 'The Events page and each event\'s detail page.', 'ltt-dive-in' ),
		__( 'Events chosen in an End Page Cluster Content Trail.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Connection status', 'ltt-dive-in' ),
			'description' => __( 'Not something you fill in. It tells you whether a key is saved and whether the last request to Seeker worked.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Seeker API key', 'ltt-dive-in' ),
			'description' => __( 'The key from Seeker\'s API Keys settings. Once saved, it is hidden and the box shows "Saved". To change it, paste a new key and save. If the key was set up by your developer in the server configuration, this box does not appear.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Remove saved key', 'ltt-dive-in' ),
			'description' => __( 'Turn it on and save to delete the stored key. Events stop showing everywhere on the site until a new key is added.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Seeker API key', 'ltt-dive-in' ),
			'check' => static function () {
				if ( ! function_exists( 'ltt_dive_in_get_seeker_api_key' ) || '' === ltt_dive_in_get_seeker_api_key() ) {
					return array(
						'state' => 'warning',
						'text'  => __( 'Missing. Event blocks and the Events page show nothing until a key is added.', 'ltt-dive-in' ),
					);
				}

				return array(
					'state' => 'ok',
					'text'  => function_exists( 'ltt_dive_in_seeker_api_key_is_constant' ) && ltt_dive_in_seeker_api_key_is_constant()
						? __( 'Set in the server configuration', 'ltt-dive-in' )
						: __( 'Set', 'ltt-dive-in' ),
				);
			},
		),
		array(
			'label' => __( 'Last request to Seeker', 'ltt-dive-in' ),
			'check' => static function () {
				return ltt_dive_in_settings_guide_last_request_check( 'ltt_dive_in_seeker_last_status' );
			},
		),
	),
	'tips'            => array(
		__( 'The site keeps a copy of the events for about 15 minutes, so changes in Seeker can take a few minutes to show up.', 'ltt-dive-in' ),
		__( 'Use a key that can only read events. The site never needs to change anything in Seeker.', 'ltt-dive-in' ),
		__( 'Local, staging, and live sites each need the key saved on their own Settings → Events screen.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The status says Seeker rejected the API key.', 'ltt-dive-in' ) => __( 'The key is wrong, was turned off, or cannot read events. Create a new key in Seeker and paste it here.', 'ltt-dive-in' ),
		__( 'The status says the hourly request limit was reached.', 'ltt-dive-in' ) => __( 'Seeker limits how often the site can ask for events. The site keeps showing the copy it already has, and it fixes itself when the limit resets.', 'ltt-dive-in' ),
		__( 'Events are missing on the site but the key is set.', 'ltt-dive-in' ) => __( 'Check that the events are published and not cancelled in Seeker. Past events are hidden in most places.', 'ltt-dive-in' ),
	),
);
