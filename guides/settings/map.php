<?php
/**
 * Site Guide: Map settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Map', 'ltt-dive-in' ),
	'group'           => 'settings',
	'icon'            => 'location-alt',
	'menu'            => __( 'Settings → Map', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'options-general.php',
		'slug'       => 'ltt-dive-in-map-settings',
		'capability' => 'manage_options',
	),
	'short'           => __( 'Connects the interactive map to Mapbox and sets its look.', 'ltt-dive-in' ),
	'summary'         => __( 'The interactive Map block is drawn by Mapbox, an outside map service. This screen holds the key that lets the site use Mapbox and the map style it shows. The places on each map are added in the Map block itself, not here.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'Every Map block on the site.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Mapbox public access token', 'ltt-dive-in' ),
			'description' => __( 'The key from your Mapbox account that allows the site to load maps. It must be a <strong>public</strong> token, which starts with <code>pk.</code>. Never paste a secret token (starting with <code>sk.</code>) here, because this key is visible in the page. In Mapbox, restrict the token to this site\'s address so nobody else can use it.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Mapbox style URL', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Which map design to use, from Mapbox Studio. It looks like <code>mapbox://styles/account/style-id</code>. It comes filled in with the site\'s approved style, so only change it if the design team publishes a new one.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Mapbox token', 'ltt-dive-in' ),
			'check' => static function () {
				return function_exists( 'ltt_dive_in_get_mapbox_access_token' ) && '' !== ltt_dive_in_get_mapbox_access_token()
					? array(
						'state' => 'ok',
						'text'  => __( 'Set', 'ltt-dive-in' ),
					)
					: array(
						'state' => 'warning',
						'text'  => __( 'Missing. Map blocks show only a list of places until a token is added.', 'ltt-dive-in' ),
					);
			},
		),
		array(
			'label' => __( 'Map style', 'ltt-dive-in' ),
			'check' => static function () {
				if ( ! function_exists( 'ltt_dive_in_get_mapbox_style_url' ) || ! defined( 'LTT_DIVE_IN_MAPBOX_DEFAULT_STYLE' ) ) {
					return null;
				}

				return LTT_DIVE_IN_MAPBOX_DEFAULT_STYLE === ltt_dive_in_get_mapbox_style_url()
					? array(
						'state' => 'ok',
						'text'  => __( 'Using the approved site style', 'ltt-dive-in' ),
					)
					: array(
						'state' => 'ok',
						'text'  => __( 'Using a custom style', 'ltt-dive-in' ),
					);
			},
		),
	),
	'tips'            => array(
		__( 'Mapbox counts every map load against your account. Restricting the token to the site\'s address keeps other sites from using your quota.', 'ltt-dive-in' ),
		__( 'Local, staging, and live sites each need the token saved on their own Settings → Map screen.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'WordPress says "Use a public Mapbox token that starts with pk."', 'ltt-dive-in' ) => __( 'The key you pasted is a secret token. Create or copy a public token in your Mapbox account instead.', 'ltt-dive-in' ),
		__( 'The map is gray or does not load on the live site.', 'ltt-dive-in' ) => __( 'The token may be restricted to a different address, expired, or deleted in Mapbox. Check the token\'s URL restrictions in your Mapbox account.', 'ltt-dive-in' ),
	),
);
