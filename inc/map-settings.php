<?php
/**
 * Map settings page and Map block data adapters.
 *
 * The settings group is PHP-registered so the access-token validation hook
 * stays next to the field it protects. The block's place fields are Local JSON
 * in acf-json/group_ltt_dive_in_map_block.json.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mapbox Studio style published by the laketahoetravel account.
 */
const LTT_DIVE_IN_MAPBOX_DEFAULT_STYLE = 'mapbox://styles/laketahoetravel/cmug2m8yg001w01r8c7c51bkb';

/**
 * Mapbox GL JS version declared by the Mapbox Studio style export.
 */
const LTT_DIVE_IN_MAPBOX_GL_VERSION = '3.29.0';

/**
 * Register the Map settings page and its field group.
 */
function ltt_dive_in_register_map_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Map Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Map', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-map-settings',
			'parent_slug' => 'options-general.php',
			'capability'  => 'manage_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_map_settings',
			'title'    => __( 'LTT Dive In — Mapbox', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'          => 'field_ltt_dive_in_mapbox_access_token',
					'label'        => __( 'Mapbox public access token', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_mapbox_access_token',
					'type'         => 'text',
					'instructions' => __( 'A public token (starts with “pk.”) from the laketahoetravel Mapbox account; the map style uses that account’s private tilesets. The token is visible in page source, so restrict it to this site’s URLs in Mapbox. Never use a secret “sk.” token.', 'ltt-dive-in' ),
					'placeholder'  => 'pk.',
				),
				array(
					'key'           => 'field_ltt_dive_in_mapbox_style_url',
					'label'         => __( 'Mapbox style URL', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_mapbox_style_url',
					'type'          => 'text',
					'instructions'  => __( 'The published Mapbox Studio style. Changes published in Studio appear on the site without code changes.', 'ltt-dive-in' ),
					'required'      => 1,
					'default_value' => LTT_DIVE_IN_MAPBOX_DEFAULT_STYLE,
					'placeholder'   => LTT_DIVE_IN_MAPBOX_DEFAULT_STYLE,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-map-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_map_settings' );

/**
 * Reject tokens that are not public Mapbox tokens.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function ltt_dive_in_validate_mapbox_access_token( $valid, $value ) {
	if ( true !== $valid || ! is_string( $value ) || '' === trim( $value ) ) {
		return $valid;
	}

	if ( 0 !== strpos( trim( $value ), 'pk.' ) ) {
		return __( 'Use a public Mapbox token that starts with “pk.”. Secret tokens must never be placed in a web page.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/name=ltt_dive_in_mapbox_access_token', 'ltt_dive_in_validate_mapbox_access_token', 10, 2 );

/**
 * Reject style URLs that are not Mapbox style URLs.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function ltt_dive_in_validate_mapbox_style_url( $valid, $value ) {
	if ( true !== $valid || ! is_string( $value ) || '' === trim( $value ) ) {
		return $valid;
	}

	if ( ! ltt_dive_in_is_mapbox_style_url( trim( $value ) ) ) {
		return __( 'Enter a Mapbox style URL in the form mapbox://styles/account/style-id.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/name=ltt_dive_in_mapbox_style_url', 'ltt_dive_in_validate_mapbox_style_url', 10, 2 );

/**
 * Whether a value is a Mapbox style URL.
 *
 * @param string $value Style URL.
 * @return bool
 */
function ltt_dive_in_is_mapbox_style_url( $value ) {
	return (bool) preg_match( '#^mapbox://styles/[A-Za-z0-9_-]+/[A-Za-z0-9_-]+$#', $value );
}

/**
 * Get the saved public Mapbox token, or an empty string when none is usable.
 *
 * @return string
 */
function ltt_dive_in_get_mapbox_access_token() {
	$token = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_mapbox_access_token', 'option' ) : '';
	$token = is_string( $token ) ? trim( $token ) : '';

	return 0 === strpos( $token, 'pk.' ) ? $token : '';
}

/**
 * Get the configured Mapbox style URL, falling back to the published style.
 *
 * @return string
 */
function ltt_dive_in_get_mapbox_style_url() {
	$style = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_mapbox_style_url', 'option' ) : '';
	$style = is_string( $style ) ? trim( $style ) : '';

	return ltt_dive_in_is_mapbox_style_url( $style ) ? $style : LTT_DIVE_IN_MAPBOX_DEFAULT_STYLE;
}

/**
 * Normalize Map block place rows into renderable markers.
 *
 * Rows without a name or valid coordinates are skipped so the map never shows
 * an unlabeled or misplaced marker. A link is kept only when it has both a
 * label and a valid URL.
 *
 * @param mixed $rows Raw ACF repeater value.
 * @return array[] Prepared places.
 */
function ltt_dive_in_prepare_map_places( $rows ) {
	$places = array();

	if ( ! is_array( $rows ) ) {
		return $places;
	}

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$name      = isset( $row['name'] ) && is_string( $row['name'] ) ? trim( $row['name'] ) : '';
		$latitude  = isset( $row['latitude'] ) && is_numeric( $row['latitude'] ) ? (float) $row['latitude'] : null;
		$longitude = isset( $row['longitude'] ) && is_numeric( $row['longitude'] ) ? (float) $row['longitude'] : null;

		if ( ! $name || null === $latitude || null === $longitude || abs( $latitude ) > 90 || abs( $longitude ) > 180 ) {
			continue;
		}

		$link = array();

		if ( ! empty( $row['link'] ) && is_array( $row['link'] ) ) {
			$url   = isset( $row['link']['url'] ) ? esc_url_raw( $row['link']['url'] ) : '';
			$title = isset( $row['link']['title'] ) && is_string( $row['link']['title'] ) ? trim( $row['link']['title'] ) : '';

			if ( $url && $title ) {
				$link = array(
					'url'    => $url,
					'title'  => $title,
					'target' => isset( $row['link']['target'] ) && '_blank' === $row['link']['target'] ? '_blank' : '',
				);
			}
		}

		$places[] = array(
			'name'        => $name,
			'region'      => isset( $row['region'] ) && is_string( $row['region'] ) ? trim( $row['region'] ) : '',
			'description' => isset( $row['description'] ) && is_string( $row['description'] ) ? trim( $row['description'] ) : '',
			'lngLat'      => array( $longitude, $latitude ),
			'link'        => $link,
		);
	}

	return $places;
}
