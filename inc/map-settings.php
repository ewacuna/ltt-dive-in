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
 * Mapbox GL JS version used by the client's map prototype.
 */
const LTT_DIVE_IN_MAPBOX_GL_VERSION = '3.30.0';

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
			'image'       => ltt_dive_in_get_map_image( isset( $row['image'] ) ? absint( $row['image'] ) : 0 ),
		);
	}

	return $places;
}

/**
 * Get the responsive image data used in a destination popup.
 *
 * The popup is built in the browser, so the attachment is passed as data
 * rather than markup. The image is decorative: the destination name follows it.
 *
 * @param int $image_id Attachment ID.
 * @return array Empty when the attachment is not an image.
 */
function ltt_dive_in_get_map_image( $image_id ) {
	if ( ! $image_id || ! wp_attachment_is_image( $image_id ) ) {
		return array();
	}

	$source = wp_get_attachment_image_src( $image_id, 'medium_large' );

	if ( ! $source ) {
		return array();
	}

	return array(
		'src'    => $source[0],
		'width'  => (int) $source[1],
		'height' => (int) $source[2],
		'srcset' => (string) wp_get_attachment_image_srcset( $image_id, 'medium_large' ),
		'sizes'  => '(max-width: 600px) calc(100vw - 32px), 350px',
	);
}

/**
 * Approved attraction icons, keyed by the value stored in the block.
 *
 * Sizes are the rendered icon sizes from the client's map prototype; each
 * icon sits inside a 44px target.
 *
 * @return array[] Icon label and size, keyed by icon slug.
 */
function ltt_dive_in_get_map_icons() {
	return array(
		'thunderbird' => array( 'label' => __( 'Lodge', 'ltt-dive-in' ), 'size' => 30 ),
		'mansion'     => array( 'label' => __( 'Mansion', 'ltt-dive-in' ), 'size' => 25 ),
		'rubicon'     => array( 'label' => __( 'Lighthouse', 'ltt-dive-in' ), 'size' => 24 ),
		'vikingsholm' => array( 'label' => __( 'Castle', 'ltt-dive-in' ), 'size' => 25 ),
		'boulder'     => array( 'label' => __( 'Boulders / beach', 'ltt-dive-in' ), 'size' => 30 ),
		'waterfall'   => array( 'label' => __( 'Waterfall', 'ltt-dive-in' ), 'size' => 28 ),
		'tree'        => array( 'label' => __( 'Forest / state park', 'ltt-dive-in' ), 'size' => 30 ),
		'bicycle'     => array( 'label' => __( 'Bike trail', 'ltt-dive-in' ), 'size' => 35 ),
		'trail'       => array( 'label' => __( 'Hiking trail', 'ltt-dive-in' ), 'size' => 30 ),
		'airport'     => array( 'label' => __( 'Airport', 'ltt-dive-in' ), 'size' => 18 ),
		'mountain'    => array( 'label' => __( 'Mountain', 'ltt-dive-in' ), 'size' => 30 ),
		'lake'        => array( 'label' => __( 'Lake', 'ltt-dive-in' ), 'size' => 28 ),
		'raft'        => array( 'label' => __( 'Rafting / river', 'ltt-dive-in' ), 'size' => 28 ),
		'park'        => array( 'label' => __( 'Park', 'ltt-dive-in' ), 'size' => 28 ),
		'historic'    => array( 'label' => __( 'Historic site', 'ltt-dive-in' ), 'size' => 28 ),
		'water'       => array( 'label' => __( 'Water activity', 'ltt-dive-in' ), 'size' => 28 ),
	);
}

/**
 * Populate the attraction icon choices from the approved icon set.
 *
 * @param array $field ACF field settings.
 * @return array
 */
function ltt_dive_in_load_map_icon_choices( $field ) {
	$field['choices'] = wp_list_pluck( ltt_dive_in_get_map_icons(), 'label' );

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_map_attraction_icon', 'ltt_dive_in_load_map_icon_choices' );

/**
 * Normalize Map block attraction rows into renderable icon markers.
 *
 * Rows without a name, an approved icon, or valid coordinates are skipped.
 *
 * @param mixed $rows Raw ACF repeater value.
 * @return array[] Prepared attractions.
 */
function ltt_dive_in_prepare_map_attractions( $rows ) {
	$attractions = array();
	$icons       = ltt_dive_in_get_map_icons();
	$icon_uri    = LTT_DIVE_IN_URI . '/assets/images/map/';

	if ( ! is_array( $rows ) ) {
		return $attractions;
	}

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$name      = isset( $row['name'] ) && is_string( $row['name'] ) ? trim( $row['name'] ) : '';
		$icon      = isset( $row['icon'] ) && is_string( $row['icon'] ) ? $row['icon'] : '';
		$latitude  = isset( $row['latitude'] ) && is_numeric( $row['latitude'] ) ? (float) $row['latitude'] : null;
		$longitude = isset( $row['longitude'] ) && is_numeric( $row['longitude'] ) ? (float) $row['longitude'] : null;

		if ( ! $name || ! isset( $icons[ $icon ] ) || null === $latitude || null === $longitude || abs( $latitude ) > 90 || abs( $longitude ) > 180 ) {
			continue;
		}

		$attractions[] = array(
			'name'      => $name,
			'lngLat'    => array( $longitude, $latitude ),
			'icon'      => $icon_uri . $icon . '.png',
			'hoverIcon' => $icon_uri . $icon . '-hover.png',
			'size'      => $icons[ $icon ]['size'],
		);
	}

	return $attractions;
}

/**
 * Theme-owned cartography drawn over the Mapbox style.
 *
 * Roads, trails, the river, route shields, town and gateway labels, and the
 * destination areas come from the client's map prototype. They are geography,
 * not editorial content, so editors do not manage them. Moving them into the
 * Mapbox Studio style later would let this function shrink to the view.
 *
 * @return array
 */
function ltt_dive_in_get_map_cartography() {
	$data_uri  = LTT_DIVE_IN_URI . '/assets/data/map/';
	$image_uri = LTT_DIVE_IN_URI . '/assets/images/map/';
	$road      = '#3c5e78';
	$lines     = array(
		array( 'us50', $road, 1.8 ),
		array( 'sr28', $road, 1.8 ),
		array( 'sr431', $road, 1.8 ),
		array( 'sr395', $road, 1.3 ),
		array( 'i80', $road, 1.8 ),
		array( 'sr267', $road, 1.8 ),
		array( 'sr89', $road, 1.8 ),
		array( 'sr88', $road, 1.3 ),
		array( 'truckee-river', '#61c2b7', 0.8 ),
		array( 'truckee-river-bike-trail', '#eeb040', 0.8 ),
		array( 'east-shore-trail', '#eeb040', 0.8 ),
	);
	$shields   = array(
		array( '431-nevada', -119.925330, 39.292867 ),
		array( '431-nevada', -119.816577, 39.382439 ),
		array( '28-nevada', -119.926018, 39.144938 ),
		array( '50-us', -119.845805, 39.117765 ),
		array( '50-us', -119.985363, 38.926983 ),
		array( '50-us', -119.574541, 39.269231 ),
		array( '50-us', -120.055746, 38.825162 ),
		array( '89-california', -120.182764, 39.381142 ),
		array( '89-california', -120.210829, 39.255543 ),
		array( '89-california', -120.116906, 39.008925 ),
		array( '28-california', -120.096560, 39.203994 ),
		array( '267-california', -120.098613, 39.272876 ),
		array( 'i-80', -120.219945, 39.323618 ),
		array( 'up-arrow', -119.972572, 39.319363, 30 ),
		array( 'left-arrow', -120.365833, 39.319363, 30 ),
	);

	return array(
		// The map opens fitted to the lake plus every destination and
		// attraction, and that view is the furthest visitors can zoom out.
		// maxBounds keeps panning near Tahoe, including the route shields.
		'view'       => array(
			'bounds'    => array( array( -120.17, 38.92 ), array( -119.92, 39.26 ) ),
			'maxZoom'   => 11,
			'maxBounds' => array( array( -121, 38.6 ), array( -119.1, 39.65 ) ),
		),
		'lines'      => array_map(
			static function ( $line ) use ( $data_uri ) {
				return array( 'id' => $line[0], 'url' => $data_uri . $line[0] . '.geojson', 'color' => $line[1], 'width' => $line[2] );
			},
			$lines
		),
		'areas'      => $data_uri . 'location-boundaries.geojson',
		'shields'    => array_map(
			static function ( $shield ) use ( $image_uri ) {
				return array( 'image' => $image_uri . $shield[0] . '.png', 'lngLat' => array( $shield[1], $shield[2] ), 'size' => isset( $shield[3] ) ? $shield[3] : 25 );
			},
			$shields
		),
		'towns'      => array(
			array( 'name' => 'Tahoe Vista', 'lngLat' => array( -120.047206, 39.237776 ) ),
			array( 'name' => 'Carnelian Bay', 'lngLat' => array( -120.070865, 39.229934 ) ),
			array( 'name' => 'Tahoma', 'lngLat' => array( -120.129614, 39.067759 ) ),
			array( 'name' => 'Homewood', 'lngLat' => array( -120.155455, 39.078207 ) ),
			array( 'name' => 'Meeks Bay', 'lngLat' => array( -120.122652, 39.037878 ) ),
		),
		'gateways'   => array(
			array(
				'lines'  => array( 'Sacramento,', 'Oakland,', '& San Francisco', 'International Airports' ),
				'lngLat' => array( -120.356068, 39.303 ),
				'anchor' => 'right',
			),
			array(
				'lines'    => array( 'Reno Tahoe', 'International', 'Airport' ),
				'subtitle' => __( '45 mins', 'ltt-dive-in' ),
				'lngLat'   => array( -119.9791, 39.303 ),
				'anchor'   => 'left',
			),
		),
	);
}
