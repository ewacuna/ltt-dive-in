<?php
/**
 * Road conditions data for the Current Conditions roads tab.
 *
 * California data comes from the public Caltrans CWWP2 feeds (chain controls,
 * lane closures, and CCTV). Nevada data comes from the Nevada 511 API, which
 * requires a developer key (Settings → Road Conditions).
 *
 * Every source is fetched by WP-Cron, normalized to a compact shape, and
 * cached. Page loads only read the cache: the lane-closure feed alone is
 * several megabytes, so a visitor never waits on a provider. When the cache is
 * empty the page receives a `pending` state and a refresh is queued.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LTT_DIVE_IN_ROADS_DEFAULT_LATITUDE', 39.0968 );
define( 'LTT_DIVE_IN_ROADS_DEFAULT_LONGITUDE', -120.0324 );
define( 'LTT_DIVE_IN_ROADS_DEFAULT_RADIUS', 40 );

/**
 * Seconds a refreshed source is considered current.
 */
define( 'LTT_DIVE_IN_ROADS_FRESH_TTL', 10 * MINUTE_IN_SECONDS );

/**
 * Longest a source may be shown after its last successful refresh.
 *
 * Road conditions change quickly; old chain-control data is misleading, so
 * the stale window is deliberately short.
 */
define( 'LTT_DIVE_IN_ROADS_STALE_TTL', HOUR_IN_SECONDS );

/**
 * Road data sources and the states they belong to.
 *
 * @return array<string, string> Source ID => state slug.
 */
function ltt_dive_in_get_road_sources() {
	return array(
		'ca_chain_controls' => 'california',
		'ca_closures'       => 'california',
		'ca_cameras'        => 'california',
		'nv_conditions'     => 'nevada',
		'nv_events'         => 'nevada',
		'nv_alerts'         => 'nevada',
		'nv_cameras'        => 'nevada',
	);
}

/**
 * Caltrans districts whose feeds are read.
 *
 * District 3 covers Placer, Nevada, El Dorado, and Alpine (SR-89 Luther Pass);
 * District 10 covers SR-88 and SR-89 south of the lake.
 *
 * @return int[]
 */
function ltt_dive_in_get_caltrans_districts() {
	/**
	 * Filter the Caltrans districts whose CWWP2 feeds are loaded.
	 *
	 * @param int[] $districts District numbers.
	 */
	$districts = apply_filters( 'ltt_dive_in_caltrans_districts', array( 3, 10 ) );

	return array_values( array_unique( array_filter( array_map( 'absint', (array) $districts ) ) ) );
}

/*
 * -------------------------------------------------------------------------
 * Public read API.
 * -------------------------------------------------------------------------
 */

/**
 * Get cached road data for one state, limited to an area.
 *
 * Never contacts a provider. Missing sources are queued for WP-Cron.
 *
 * @param string $state California or Nevada slug.
 * @param array  $area  Area from ltt_dive_in_get_road_conditions_area().
 * @return array{
 *     status: string,
 *     updated: int,
 *     sources: array<string, array{status: string, updated: int, items: array}>
 * } Status is `ok`, `pending`, `missing_key`, or `unavailable`.
 */
function ltt_dive_in_get_road_conditions( $state, $area ) {
	$result = array(
		'status'  => 'unavailable',
		'updated' => 0,
		'sources' => array(),
	);
	$queue  = false;

	foreach ( ltt_dive_in_get_road_sources() as $source => $source_state ) {
		if ( $source_state !== $state ) {
			continue;
		}

		$cached = get_transient( ltt_dive_in_road_source_cache_key( $source ) );
		$entry  = array(
			'status'  => 'pending',
			'updated' => 0,
			'items'   => array(),
		);

		if ( is_array( $cached ) && isset( $cached['items'] ) ) {
			$entry = array(
				'status'  => 'ok',
				'updated' => (int) $cached['updated'],
				'items'   => ltt_dive_in_filter_road_items( $cached['items'], $area ),
			);

			if ( time() - $entry['updated'] > LTT_DIVE_IN_ROADS_FRESH_TTL ) {
				$queue = true;
			}
		} elseif ( is_array( $cached ) && ! empty( $cached['error'] ) ) {
			$entry['status'] = 'missing_key' === $cached['error'] ? 'missing_key' : 'unavailable';
		} else {
			$queue = true;
		}

		$result['sources'][ $source ] = $entry;
	}

	if ( $queue ) {
		ltt_dive_in_queue_road_conditions_refresh();
	}

	$statuses = wp_list_pluck( $result['sources'], 'status' );

	if ( in_array( 'ok', $statuses, true ) ) {
		$result['status']  = 'ok';
		$result['updated'] = (int) min( array_filter( wp_list_pluck( $result['sources'], 'updated' ) ) );
	} elseif ( in_array( 'missing_key', $statuses, true ) ) {
		$result['status'] = 'missing_key';
	} elseif ( in_array( 'pending', $statuses, true ) ) {
		$result['status'] = 'pending';
	}

	return $result;
}

/**
 * Get the area configured on a roads tab.
 *
 * @param int $post_id Tab page ID.
 * @return array{latitude: float, longitude: float, radius: float} Radius in miles.
 */
function ltt_dive_in_get_road_conditions_area( $post_id ) {
	$get = static function ( $name, $default, $min, $max ) use ( $post_id ) {
		$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : null;

		return is_numeric( $value ) && (float) $value >= $min && (float) $value <= $max ? (float) $value : (float) $default;
	};

	return array(
		'latitude'  => $get( 'ltt_dive_in_roads_area_center_latitude', LTT_DIVE_IN_ROADS_DEFAULT_LATITUDE, -90, 90 ),
		'longitude' => $get( 'ltt_dive_in_roads_area_center_longitude', LTT_DIVE_IN_ROADS_DEFAULT_LONGITUDE, -180, 180 ),
		'radius'    => $get( 'ltt_dive_in_roads_area_radius', LTT_DIVE_IN_ROADS_DEFAULT_RADIUS, 5, 150 ),
	);
}

/**
 * Keep the items with at least one point inside the area, nearest first.
 *
 * Items without coordinates (such as Nevada advisories) are kept; the
 * presentation layer decides whether they apply.
 *
 * @param array $items Normalized items, each with a `points` list of [lat, lng].
 * @param array $area  Area from ltt_dive_in_get_road_conditions_area().
 * @return array Items with an added `distance` in miles (null without points).
 */
function ltt_dive_in_filter_road_items( $items, $area ) {
	$kept = array();

	foreach ( (array) $items as $item ) {
		$distance = null;

		foreach ( $item['points'] ?? array() as $point ) {
			$miles    = ltt_dive_in_get_distance_miles( $area['latitude'], $area['longitude'], $point[0], $point[1] );
			$distance = null === $distance ? $miles : min( $distance, $miles );
		}

		if ( null !== $distance && $distance > $area['radius'] ) {
			continue;
		}

		$item['distance'] = null === $distance ? null : round( $distance, 1 );
		$kept[]           = $item;
	}

	usort(
		$kept,
		static function ( $a, $b ) {
			return ( $a['distance'] ?? PHP_INT_MAX ) <=> ( $b['distance'] ?? PHP_INT_MAX );
		}
	);

	return $kept;
}

/**
 * Great-circle distance between two coordinates.
 *
 * @param float $lat1 First latitude.
 * @param float $lng1 First longitude.
 * @param float $lat2 Second latitude.
 * @param float $lng2 Second longitude.
 * @return float Miles.
 */
function ltt_dive_in_get_distance_miles( $lat1, $lng1, $lat2, $lng2 ) {
	$d_lat = deg2rad( $lat2 - $lat1 );
	$d_lng = deg2rad( $lng2 - $lng1 );
	$a     = sin( $d_lat / 2 ) ** 2 + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $d_lng / 2 ) ** 2;

	return 3958.8 * 2 * asin( min( 1, sqrt( $a ) ) );
}

/**
 * Normalize a highway name to a canonical route ID.
 *
 * Handles Caltrans (`I-80`, `US-50`, `SR-89`) and Nevada 511 variants such as
 * `I 80`, `IR-80`, `US 50`, `NV-28`, or `SR 28`.
 *
 * @param string $name Provider road name.
 * @return string Canonical ID such as `I-80`, `US-50`, or `SR-28`; empty when unrecognized.
 */
function ltt_dive_in_normalize_route( $name ) {
	if ( ! preg_match( '/^\s*(I|IR|IH|INTERSTATE|US|USA|SR|NV|CA|STATE ROUTE|HWY|HIGHWAY)?[\s\-]*(\d{1,3})\b(?![\s\-]*(?:ALT|BUS|BYP)\b)/i', (string) $name, $matches ) ) {
		return '';
	}

	$prefix = strtoupper( $matches[1] ?? '' );
	$number = (int) $matches[2];

	if ( in_array( $prefix, array( 'I', 'IR', 'IH', 'INTERSTATE' ), true ) ) {
		return 'I-' . $number;
	}

	if ( in_array( $prefix, array( 'US', 'USA' ), true ) ) {
		return 'US-' . $number;
	}

	return 'SR-' . $number;
}

/*
 * -------------------------------------------------------------------------
 * Cache and WP-Cron.
 * -------------------------------------------------------------------------
 */

/**
 * Transient key for a source.
 *
 * @param string $source Source ID.
 * @return string
 */
function ltt_dive_in_road_source_cache_key( $source ) {
	return 'ltt_dive_in_roads_' . $source;
}

/**
 * Queue a background refresh unless one is already pending.
 */
function ltt_dive_in_queue_road_conditions_refresh() {
	if ( ! wp_next_scheduled( 'ltt_dive_in_refresh_road_conditions' ) ) {
		wp_schedule_single_event( time(), 'ltt_dive_in_refresh_road_conditions' );
	}
}

/**
 * Refresh every road source and store the normalized results.
 *
 * A failed refresh keeps the last good data until it expires; when there is
 * none, the failure is cached briefly so providers are not retried on every
 * page load.
 */
function ltt_dive_in_refresh_road_conditions() {
	if ( get_transient( 'ltt_dive_in_roads_refreshing' ) ) {
		return;
	}

	set_transient( 'ltt_dive_in_roads_refreshing', 1, 2 * MINUTE_IN_SECONDS );

	foreach ( array_keys( ltt_dive_in_get_road_sources() ) as $source ) {
		$items = ltt_dive_in_fetch_road_source( $source );
		$key   = ltt_dive_in_road_source_cache_key( $source );

		if ( ! is_wp_error( $items ) ) {
			set_transient(
				$key,
				array(
					'updated' => time(),
					'items'   => $items,
				),
				LTT_DIVE_IN_ROADS_STALE_TTL
			);
			continue;
		}

		$cached = get_transient( $key );

		if ( 'ltt_dive_in_nv511_missing_api_key' === $items->get_error_code() ) {
			set_transient( $key, array( 'error' => 'missing_key' ), LTT_DIVE_IN_ROADS_STALE_TTL );
		} elseif ( ! is_array( $cached ) || ! isset( $cached['items'] ) ) {
			set_transient( $key, array( 'error' => $items->get_error_code() ), 5 * MINUTE_IN_SECONDS );
		}
	}

	delete_transient( 'ltt_dive_in_roads_refreshing' );
}
add_action( 'ltt_dive_in_refresh_road_conditions', 'ltt_dive_in_refresh_road_conditions' );

/**
 * Discard cached Nevada data when the developer key changes.
 */
function ltt_dive_in_clear_nevada_road_cache() {
	foreach ( ltt_dive_in_get_road_sources() as $source => $state ) {
		if ( 'nevada' === $state ) {
			delete_transient( ltt_dive_in_road_source_cache_key( $source ) );
		}
	}

	ltt_dive_in_queue_road_conditions_refresh();
}
add_action( 'ltt_dive_in_nv511_api_key_changed', 'ltt_dive_in_clear_nevada_road_cache' );

/**
 * Add the five-minute WP-Cron interval used by the road refresh.
 *
 * @param array $schedules Registered cron schedules.
 * @return array
 */
function ltt_dive_in_roads_cron_schedules( $schedules ) {
	$schedules['ltt_dive_in_five_minutes'] = array(
		'interval' => 5 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every 5 minutes', 'ltt-dive-in' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'ltt_dive_in_roads_cron_schedules' );

/**
 * Keep the recurring refresh scheduled only while a published roads tab exists.
 */
function ltt_dive_in_schedule_road_conditions_refresh() {
	$scheduled = wp_next_scheduled( 'ltt_dive_in_refresh_road_conditions_recurring' );

	if ( ltt_dive_in_get_roads_tab_page_id() ) {
		if ( ! $scheduled ) {
			wp_schedule_event( time(), 'ltt_dive_in_five_minutes', 'ltt_dive_in_refresh_road_conditions_recurring' );
		}
	} elseif ( $scheduled ) {
		wp_clear_scheduled_hook( 'ltt_dive_in_refresh_road_conditions_recurring' );
	}
}
add_action( 'init', 'ltt_dive_in_schedule_road_conditions_refresh' );
add_action( 'ltt_dive_in_refresh_road_conditions_recurring', 'ltt_dive_in_refresh_road_conditions' );

/**
 * Remove the road refresh events when the theme is switched off.
 */
function ltt_dive_in_unschedule_road_conditions_refresh() {
	wp_clear_scheduled_hook( 'ltt_dive_in_refresh_road_conditions_recurring' );
	wp_clear_scheduled_hook( 'ltt_dive_in_refresh_road_conditions' );
}
add_action( 'switch_theme', 'ltt_dive_in_unschedule_road_conditions_refresh' );

/**
 * Get the published Current Conditions page that shows the roads module.
 *
 * @return int Page ID, or 0 when no roads tab is set up.
 */
function ltt_dive_in_get_roads_tab_page_id() {
	$page_id = get_transient( 'ltt_dive_in_roads_tab_page_id' );

	if ( false === $page_id ) {
		$pages   = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cached in a transient that is cleared whenever a page is saved.
				'meta_query'     => array(
					array(
						'key'   => '_wp_page_template',
						'value' => 'templates/current-conditions.php',
					),
					array(
						'key'   => 'ltt_dive_in_conditions_tab_module',
						'value' => 'roads',
					),
				),
			)
		);
		$page_id = $pages ? (int) $pages[0] : 0;

		set_transient( 'ltt_dive_in_roads_tab_page_id', $page_id, DAY_IN_SECONDS );
	}

	return (int) $page_id;
}

/**
 * Forget the cached roads tab whenever a page changes.
 */
function ltt_dive_in_clear_roads_tab_page_cache() {
	delete_transient( 'ltt_dive_in_roads_tab_page_id' );
}
add_action( 'save_post_page', 'ltt_dive_in_clear_roads_tab_page_cache' );
add_action( 'deleted_post', 'ltt_dive_in_clear_roads_tab_page_cache' );

/*
 * -------------------------------------------------------------------------
 * Fetching and normalization.
 * -------------------------------------------------------------------------
 */

/**
 * Fetch and normalize one source.
 *
 * @param string $source Source ID.
 * @return array|WP_Error Normalized items or an error.
 */
function ltt_dive_in_fetch_road_source( $source ) {
	switch ( $source ) {
		case 'ca_chain_controls':
			return ltt_dive_in_fetch_caltrans_feed( 'cc', 'ccStatus', 'ltt_dive_in_normalize_caltrans_chain_control' );
		case 'ca_closures':
			return ltt_dive_in_fetch_caltrans_feed( 'lcs', 'lcsStatus', 'ltt_dive_in_normalize_caltrans_closure' );
		case 'ca_cameras':
			return ltt_dive_in_fetch_caltrans_feed( 'cctv', 'cctvStatus', 'ltt_dive_in_normalize_caltrans_camera' );
		case 'nv_conditions':
			return ltt_dive_in_fetch_nv511( 'v3/get/roadconditions', 'ltt_dive_in_normalize_nv511_condition' );
		case 'nv_events':
			return ltt_dive_in_fetch_nv511( 'v2/get/event', 'ltt_dive_in_normalize_nv511_event' );
		case 'nv_alerts':
			return ltt_dive_in_fetch_nv511( 'v2/get/alerts', 'ltt_dive_in_normalize_nv511_alert' );
		case 'nv_cameras':
			return ltt_dive_in_fetch_nv511( 'v2/get/cameras', 'ltt_dive_in_normalize_nv511_camera' );
	}

	return new WP_Error( 'ltt_dive_in_roads_unknown_source' );
}

/**
 * Request a JSON document.
 *
 * @param string $url     URL.
 * @param int    $timeout Seconds.
 * @return array|WP_Error Decoded body, or an error whose data holds the HTTP status.
 */
function ltt_dive_in_request_road_json( $url, $timeout = 20 ) {
	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'     => $timeout,
			'redirection' => 2,
			'user-agent'  => 'LTT Dive In WordPress theme/' . LTT_DIVE_IN_VERSION . '; ' . home_url( '/' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'ltt_dive_in_roads_request_failed' );
	}

	$code = (int) wp_remote_retrieve_response_code( $response );

	if ( 200 !== $code ) {
		return new WP_Error( 'ltt_dive_in_roads_http_error', '', array( 'status' => $code ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	return is_array( $body ) ? $body : new WP_Error( 'ltt_dive_in_roads_invalid_response' );
}

/**
 * Fetch a CWWP2 feed for every configured district.
 *
 * A district that fails is skipped; the source fails only when every district does.
 *
 * @param string   $feed       Feed directory, such as `cc`.
 * @param string   $file       File prefix, such as `ccStatus`.
 * @param callable $normalizer Callback receiving one record; returns an item or null.
 * @return array|WP_Error
 */
function ltt_dive_in_fetch_caltrans_feed( $feed, $file, $normalizer ) {
	$items  = array();
	$failed = 0;
	$total  = 0;

	foreach ( ltt_dive_in_get_caltrans_districts() as $district ) {
		++$total;
		$body = ltt_dive_in_request_road_json(
			sprintf( 'https://cwwp2.dot.ca.gov/data/d%1$d/%2$s/%3$sD%4$02d.json', $district, $feed, $file, $district ),
			30
		);

		if ( is_wp_error( $body ) || ! isset( $body['data'] ) || ! is_array( $body['data'] ) ) {
			++$failed;
			continue;
		}

		foreach ( $body['data'] as $record ) {
			$item = is_array( $record ) ? call_user_func( $normalizer, $record ) : null;

			if ( $item ) {
				$items[ $item['id'] ] = $item;
			}
		}
	}

	return $total && $failed === $total ? new WP_Error( 'ltt_dive_in_roads_request_failed' ) : array_values( $items );
}

/**
 * Read a coordinate pair, rejecting empty or out-of-range values.
 *
 * @param mixed $latitude  Latitude.
 * @param mixed $longitude Longitude.
 * @return array|null [lat, lng] rounded to five decimals.
 */
function ltt_dive_in_road_point( $latitude, $longitude ) {
	if ( ! is_numeric( $latitude ) || ! is_numeric( $longitude ) ) {
		return null;
	}

	$latitude  = (float) $latitude;
	$longitude = (float) $longitude;

	if ( abs( $latitude ) > 90 || abs( $longitude ) > 180 || ( 0.0 === $latitude && 0.0 === $longitude ) ) {
		return null;
	}

	return array( round( $latitude, 5 ), round( $longitude, 5 ) );
}

/**
 * Convert a Caltrans local date and time (Pacific) to a timestamp.
 *
 * @param string $date Y-m-d.
 * @param string $time H:i:s.
 * @return int 0 when empty or invalid.
 */
function ltt_dive_in_caltrans_timestamp( $date, $time ) {
	if ( ! $date ) {
		return 0;
	}

	$datetime = date_create_immutable( trim( $date . ' ' . $time ), new DateTimeZone( 'America/Los_Angeles' ) );

	return $datetime ? $datetime->getTimestamp() : 0;
}

/**
 * Normalize one Caltrans chain-control record.
 *
 * @param array $record CWWP2 record.
 * @return array|null
 */
function ltt_dive_in_normalize_caltrans_chain_control( $record ) {
	$cc       = $record['cc'] ?? array();
	$location = $cc['location'] ?? array();
	$status   = $cc['statusData'] ?? array();
	$point    = ltt_dive_in_road_point( $location['latitude'] ?? null, $location['longitude'] ?? null );
	$route    = ltt_dive_in_normalize_route( $location['route'] ?? '' );

	if ( empty( $cc['index'] ) || ! $point || ! $route || 'true' !== ( $cc['inService'] ?? '' ) ) {
		return null;
	}

	return array(
		'id'          => sanitize_text_field( $cc['index'] ),
		'route'       => $route,
		'direction'   => sanitize_text_field( $location['direction'] ?? '' ),
		'location'    => sanitize_text_field( $location['locationName'] ?? '' ),
		'nearby'      => sanitize_text_field( $location['nearbyPlace'] ?? '' ),
		'county'      => sanitize_text_field( $location['county'] ?? '' ),
		'milepost'    => is_numeric( $location['milepost'] ?? null ) ? (float) $location['milepost'] : null,
		'status'      => strtoupper( sanitize_text_field( $status['status'] ?? '' ) ),
		'description' => sanitize_text_field( $status['statusDescription'] ?? '' ),
		'updated'     => ltt_dive_in_caltrans_timestamp( $status['statusTimestamp']['statusDate'] ?? '', $status['statusTimestamp']['statusTime'] ?? '' ),
		'points'      => array( $point ),
	);
}

/**
 * Normalize one Caltrans lane-closure record, keeping only closures in effect now.
 *
 * A closure is in effect when Caltrans has reported it started (code 1097)
 * and it has not ended (1098) or been cancelled (1022). Approved closures that
 * are only planned are skipped.
 *
 * @param array $record CWWP2 record.
 * @return array|null
 */
function ltt_dive_in_normalize_caltrans_closure( $record ) {
	$lcs     = $record['lcs'] ?? array();
	$closure = $lcs['closure'] ?? array();
	$begin   = $lcs['location']['begin'] ?? array();
	$end     = $lcs['location']['end'] ?? array();
	$times   = $closure['closureTimestamp'] ?? array();

	if ( 'true' !== ( $closure['code1097']['isCode1097'] ?? '' )
		|| 'true' === ( $closure['code1098']['isCode1098'] ?? '' )
		|| 'true' === ( $closure['code1022']['isCode1022'] ?? '' ) ) {
		return null;
	}

	$indefinite = 'true' === ( $times['isClosureEndIndefinite'] ?? '' );
	$ends_at    = $indefinite ? 0 : absint( $times['closureEndEpoch'] ?? 0 );

	if ( $ends_at && $ends_at < time() ) {
		return null;
	}

	$route  = ltt_dive_in_normalize_route( $begin['beginRoute'] ?? '' );
	$points = array_values(
		array_filter(
			array(
				ltt_dive_in_road_point( $begin['beginLatitude'] ?? null, $begin['beginLongitude'] ?? null ),
				ltt_dive_in_road_point( $end['endLatitude'] ?? null, $end['endLongitude'] ?? null ),
			)
		)
	);

	if ( empty( $lcs['index'] ) || ! $route || ! $points ) {
		return null;
	}

	$delay = $closure['estimatedDelay'] ?? '';

	return array(
		'id'           => sanitize_text_field( $lcs['index'] ),
		'route'        => $route,
		'direction'    => sanitize_text_field( $lcs['location']['travelFlowDirection'] ?? '' ),
		'from'         => sanitize_text_field( ( $begin['beginLocationName'] ?? '' ) ?: ( $begin['beginFreeFormDescription'] ?? '' ) ),
		'to'           => sanitize_text_field( ( $end['endLocationName'] ?? '' ) ?: ( $end['endFreeFormDescription'] ?? '' ) ),
		'nearby'       => sanitize_text_field( $begin['beginNearbyPlace'] ?? '' ),
		'county'       => sanitize_text_field( $begin['beginCounty'] ?? '' ),
		'type'         => sanitize_text_field( $closure['typeOfClosure'] ?? '' ),
		'work'         => sanitize_text_field( $closure['typeOfWork'] ?? '' ),
		'facility'     => sanitize_text_field( $closure['facility'] ?? '' ),
		'lanes_closed' => sanitize_text_field( $closure['lanesClosed'] ?? '' ),
		'total_lanes'  => sanitize_text_field( $closure['totalExistingLanes'] ?? '' ),
		'delay'        => is_numeric( $delay ) ? (int) $delay : null,
		'starts_at'    => absint( $times['closureStartEpoch'] ?? 0 ),
		'ends_at'      => $ends_at,
		'points'       => $points,
	);
}

/**
 * Normalize one Caltrans CCTV record.
 *
 * @param array $record CWWP2 record.
 * @return array|null
 */
function ltt_dive_in_normalize_caltrans_camera( $record ) {
	$cctv     = $record['cctv'] ?? array();
	$location = $cctv['location'] ?? array();
	$image    = esc_url_raw( $cctv['imageData']['static']['currentImageURL'] ?? '' );
	$point    = ltt_dive_in_road_point( $location['latitude'] ?? null, $location['longitude'] ?? null );

	if ( ! isset( $cctv['index'] ) || '' === (string) $cctv['index'] || ! $point || ! $image || 'true' !== ( $cctv['inService'] ?? '' ) ) {
		return null;
	}

	return array(
		'id'        => 'ca-' . sanitize_key( ( $location['district'] ?? '' ) . '-' . $cctv['index'] ),
		'route'     => ltt_dive_in_normalize_route( $location['route'] ?? '' ),
		'direction' => sanitize_text_field( $location['direction'] ?? '' ),
		'name'      => sanitize_text_field( $location['locationName'] ?? '' ),
		'nearby'    => sanitize_text_field( $location['nearbyPlace'] ?? '' ),
		'image'     => $image,
		'points'    => array( $point ),
	);
}

/**
 * Request a Nevada 511 endpoint and normalize every record.
 *
 * Records the outcome for the settings page status message.
 *
 * @param string   $endpoint   Path below `/api/`, such as `v3/get/roadconditions`.
 * @param callable $normalizer Callback receiving one record; returns an item or null.
 * @return array|WP_Error
 */
function ltt_dive_in_fetch_nv511( $endpoint, $normalizer ) {
	$key = ltt_dive_in_get_nv511_api_key();

	if ( '' === $key ) {
		return new WP_Error( 'ltt_dive_in_nv511_missing_api_key' );
	}

	$body = ltt_dive_in_request_road_json(
		add_query_arg(
			array(
				'key'    => rawurlencode( $key ),
				'format' => 'json',
			),
			'https://www.nvroads.com/api/' . $endpoint
		)
	);

	if ( is_wp_error( $body ) ) {
		$http   = (int) ( $body->get_error_data()['status'] ?? 0 );
		$status = 'request_failed';

		if ( in_array( $http, array( 400, 401, 403 ), true ) ) {
			$status = 'unauthorized';
		} elseif ( 429 === $http ) {
			$status = 'rate_limited';
		} elseif ( 'ltt_dive_in_roads_invalid_response' === $body->get_error_code() ) {
			$status = 'invalid_response';
		}

		ltt_dive_in_set_nv511_status( $status );

		return $body;
	}

	ltt_dive_in_set_nv511_status( 'ok' );

	$items = array();

	foreach ( $body as $record ) {
		$item = is_array( $record ) ? call_user_func( $normalizer, $record ) : null;

		if ( $item ) {
			$items[ $item['id'] ] = $item;
		}
	}

	return array_values( $items );
}

/**
 * Store the latest Nevada 511 request outcome without autoloading it.
 *
 * @param string $status Status code shown on the settings page.
 */
function ltt_dive_in_set_nv511_status( $status ) {
	update_option(
		'ltt_dive_in_nv511_last_status',
		array(
			'status' => $status,
			'time'   => time(),
		),
		false
	);
}

/**
 * Decode a Google encoded polyline and keep a small, evenly spaced sample.
 *
 * Only used to test whether a segment reaches the area, so a sample is enough.
 *
 * @param string|string[] $encoded Encoded polyline, or a list of polylines (Nevada 511 sends a list).
 * @param int             $limit   Maximum points kept, including both ends.
 * @return array<int, array{0: float, 1: float}>
 */
function ltt_dive_in_decode_polyline_sample( $encoded, $limit = 12 ) {
	if ( is_array( $encoded ) ) {
		$points = array();

		foreach ( $encoded as $part ) {
			if ( is_string( $part ) ) {
				$points = array_merge( $points, ltt_dive_in_decode_polyline_sample( $part, PHP_INT_MAX ) );
			}
		}

		return ltt_dive_in_sample_road_points( $points, $limit );
	}

	$points = array();
	$length = strlen( (string) $encoded );
	$index  = 0;
	$lat    = 0;
	$lng    = 0;

	while ( $index < $length ) {
		foreach ( array( 'lat', 'lng' ) as $axis ) {
			$result = 0;
			$shift  = 0;

			do {
				if ( $index >= $length ) {
					break 3;
				}

				$byte    = ord( $encoded[ $index++ ] ) - 63;
				$result |= ( $byte & 0x1f ) << $shift;
				$shift  += 5;
			} while ( $byte >= 0x20 );

			$delta = ( $result & 1 ) ? ~( $result >> 1 ) : ( $result >> 1 );

			if ( 'lat' === $axis ) {
				$lat += $delta;
			} else {
				$lng += $delta;
			}
		}

		$point = ltt_dive_in_road_point( $lat / 1e5, $lng / 1e5 );

		if ( $point ) {
			$points[] = $point;
		}
	}

	return ltt_dive_in_sample_road_points( $points, $limit );
}

/**
 * Keep an evenly spaced sample of points, including both ends.
 *
 * @param array $points Points.
 * @param int   $limit  Maximum points kept.
 * @return array
 */
function ltt_dive_in_sample_road_points( $points, $limit ) {
	$count = count( $points );

	if ( $count <= $limit ) {
		return $points;
	}

	$sample = array();

	for ( $i = 0; $i < $limit; $i++ ) {
		$sample[] = $points[ (int) round( $i * ( $count - 1 ) / ( $limit - 1 ) ) ];
	}

	return $sample;
}

/**
 * Normalize one Nevada 511 road-condition segment.
 *
 * @param array $record Nevada 511 record.
 * @return array|null
 */
function ltt_dive_in_normalize_nv511_condition( $record ) {
	$route  = ltt_dive_in_normalize_route( $record['RoadwayName'] ?? '' );
	$points = ltt_dive_in_decode_polyline_sample( $record['EncodedPolyline'] ?? '' );

	if ( ! isset( $record['Id'] ) || ! $route || ! $points || 'california' === strtolower( trim( (string) ( $record['AreaName'] ?? '' ) ) ) ) {
		return null;
	}

	$secondary = $record['Secondary Conditions'] ?? ( $record['SecondaryConditions'] ?? array() );

	return array(
		'id'        => 'nv-' . sanitize_key( (string) $record['Id'] ),
		'route'     => $route,
		'area'      => sanitize_text_field( $record['AreaName'] ?? '' ),
		'location'  => sanitize_text_field( $record['LocationDescription'] ?? '' ),
		'status'    => sanitize_text_field( $record['Overall Status'] ?? ( $record['OverallStatus'] ?? '' ) ),
		'secondary' => array_values( array_filter( array_map( 'sanitize_text_field', is_array( $secondary ) ? $secondary : array() ) ) ),
		'updated'   => absint( $record['LastUpdated'] ?? 0 ),
		'points'    => $points,
	);
}

/**
 * Normalize one Nevada 511 traffic event.
 *
 * @param array $record Nevada 511 record.
 * @return array|null
 */
function ltt_dive_in_normalize_nv511_event( $record ) {
	$route  = ltt_dive_in_normalize_route( $record['RoadwayName'] ?? '' );
	$points = array_values(
		array_filter(
			array(
				ltt_dive_in_road_point( $record['Latitude'] ?? null, $record['Longitude'] ?? null ),
				ltt_dive_in_road_point( $record['LatitudeSecondary'] ?? null, $record['LongitudeSecondary'] ?? null ),
			)
		)
	);

	if ( ! isset( $record['ID'] ) || ! $route || ! $points ) {
		return null;
	}

	$starts_at = absint( $record['StartDate'] ?? 0 );
	$ends_at   = absint( $record['PlannedEndDate'] ?? 0 );

	// Only events in effect now.
	if ( ( $starts_at && $starts_at > time() ) || ( $ends_at && $ends_at < time() ) ) {
		return null;
	}

	return array(
		'id'           => 'nv-' . sanitize_key( (string) $record['ID'] ),
		'route'        => $route,
		'direction'    => sanitize_text_field( $record['DirectionOfTravel'] ?? '' ),
		'description'  => sanitize_text_field( $record['Description'] ?? '' ),
		'type'         => sanitize_key( $record['EventType'] ?? '' ),
		'subtype'      => sanitize_text_field( $record['EventSubType'] ?? '' ),
		'full_closure' => ! empty( $record['IsFullClosure'] ),
		'severity'     => sanitize_text_field( $record['Severity'] ?? '' ),
		'lanes'        => sanitize_text_field( $record['LanesAffected'] ?? '' ),
		'starts_at'    => $starts_at,
		'ends_at'      => $ends_at,
		'updated'      => absint( $record['LastUpdated'] ?? 0 ),
		'points'       => $points,
	);
}

/**
 * Normalize one Nevada 511 advisory.
 *
 * Advisories have regions instead of coordinates, so they carry no points.
 *
 * @param array $record Nevada 511 record.
 * @return array|null
 */
function ltt_dive_in_normalize_nv511_alert( $record ) {
	$message = sanitize_text_field( $record['Message'] ?? '' );

	if ( ! isset( $record['Id'] ) || '' === $message ) {
		return null;
	}

	$ends_at = absint( $record['EndTime'] ?? 0 );

	if ( $ends_at && $ends_at < time() ) {
		return null;
	}

	return array(
		'id'        => 'nv-' . sanitize_key( (string) $record['Id'] ),
		'message'   => $message,
		'notes'     => sanitize_text_field( $record['Notes'] ?? '' ),
		'regions'   => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $record['Regions'] ?? array() ) ) ) ),
		'important' => ! empty( $record['HighImportance'] ),
		'starts_at' => absint( $record['StartTime'] ?? 0 ),
		'ends_at'   => $ends_at,
		'points'    => array(),
	);
}

/**
 * Normalize one Nevada 511 camera, keeping its first working still image.
 *
 * @param array $record Nevada 511 record.
 * @return array|null
 */
function ltt_dive_in_normalize_nv511_camera( $record ) {
	$point = ltt_dive_in_road_point( $record['Latitude'] ?? null, $record['Longitude'] ?? null );
	$image = '';

	foreach ( (array) ( $record['Views'] ?? array() ) as $view ) {
		if ( is_array( $view ) && ! empty( $view['Url'] ) && 'disabled' !== strtolower( (string) ( $view['Status'] ?? '' ) ) ) {
			$image = esc_url_raw( $view['Url'] );
			break;
		}
	}

	if ( ! isset( $record['Id'] ) || ! $point || ! $image ) {
		return null;
	}

	return array(
		'id'        => 'nv-' . sanitize_key( (string) $record['Id'] ),
		'route'     => ltt_dive_in_normalize_route( $record['Roadway'] ?? '' ),
		'direction' => sanitize_text_field( $record['Direction'] ?? '' ),
		'name'      => sanitize_text_field( $record['Location'] ?? '' ),
		'nearby'    => '',
		'image'     => $image,
		'points'    => array( $point ),
	);
}
