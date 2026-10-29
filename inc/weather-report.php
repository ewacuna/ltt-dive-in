<?php
/**
 * OpenWeather One Call report for the Current Conditions weather panel.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the current conditions and daily forecast for a location.
 *
 * Each refresh makes two One Call 4.0 requests (current and daily timeline).
 * Fresh reports are cached for 20 minutes and refreshed every 15 minutes by
 * ltt_dive_in_refresh_weather_locations(). After that, the last valid report
 * is served for up to 24 hours while WordPress refreshes it in the background.
 * Failures are cached briefly to protect the provider.
 *
 * @param float $latitude      Location latitude.
 * @param float $longitude     Location longitude.
 * @param bool  $force_refresh Whether to bypass caches and contact OpenWeather.
 * @param bool  $allow_sync    Whether an uncached report may be requested during this page load.
 *                             When false, the request is deferred to WP-Cron.
 * @return array|WP_Error Normalized report or an error.
 */
function ltt_dive_in_get_weather_report( $latitude, $longitude, $force_refresh = false, $allow_sync = true ) {
	$latitude  = round( (float) $latitude, 4 );
	$longitude = round( (float) $longitude, 4 );
	$cache_key = 'ltt_dive_in_weather_report_' . md5( $latitude . ',' . $longitude );
	$stale_key = $cache_key . '_stale';

	if ( ! $force_refresh ) {
		$cached_report = get_transient( $cache_key );

		if ( is_array( $cached_report ) ) {
			return $cached_report;
		}

		$stale_report = get_transient( $stale_key );
		$schedule     = 'unavailable' !== $cached_report && ! wp_next_scheduled( 'ltt_dive_in_refresh_weather_report', array( $latitude, $longitude ) );

		if ( is_array( $stale_report ) ) {
			if ( $schedule ) {
				wp_schedule_single_event( time(), 'ltt_dive_in_refresh_weather_report', array( $latitude, $longitude ) );
			}

			return $stale_report;
		}

		if ( 'unavailable' === $cached_report ) {
			return new WP_Error( 'ltt_dive_in_weather_report_unavailable' );
		}

		if ( ! $allow_sync ) {
			if ( $schedule ) {
				wp_schedule_single_event( time(), 'ltt_dive_in_refresh_weather_report', array( $latitude, $longitude ) );
			}

			return new WP_Error( 'ltt_dive_in_weather_report_pending' );
		}
	}

	$report = ltt_dive_in_fetch_weather_report( $latitude, $longitude );

	if ( is_wp_error( $report ) ) {
		set_transient( $cache_key, 'unavailable', 5 * MINUTE_IN_SECONDS );

		return $report;
	}

	set_transient( $cache_key, $report, 20 * MINUTE_IN_SECONDS );
	set_transient( $stale_key, $report, DAY_IN_SECONDS );

	return $report;
}

/**
 * Refresh a cached weather report through WordPress Cron.
 *
 * @param float $latitude  Location latitude.
 * @param float $longitude Location longitude.
 */
function ltt_dive_in_refresh_weather_report( $latitude, $longitude ) {
	ltt_dive_in_get_weather_report( $latitude, $longitude, true );
}
add_action( 'ltt_dive_in_refresh_weather_report', 'ltt_dive_in_refresh_weather_report', 10, 2 );

/**
 * Add the fifteen-minute WP-Cron interval used by the weather refresh.
 *
 * @param array $schedules Registered cron schedules.
 * @return array
 */
function ltt_dive_in_weather_cron_schedules( $schedules ) {
	$schedules['ltt_dive_in_fifteen_minutes'] = array(
		'interval' => 15 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every 15 minutes', 'ltt-dive-in' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'ltt_dive_in_weather_cron_schedules' );

/**
 * Schedule the recurring refresh that keeps every weather location warm.
 */
function ltt_dive_in_schedule_weather_locations_refresh() {
	if ( ! wp_next_scheduled( 'ltt_dive_in_refresh_weather_locations' ) ) {
		wp_schedule_event( time(), 'ltt_dive_in_fifteen_minutes', 'ltt_dive_in_refresh_weather_locations' );
	}
}
add_action( 'init', 'ltt_dive_in_schedule_weather_locations_refresh' );

/**
 * Refresh every location of the Current Conditions weather tab through WP-Cron.
 */
function ltt_dive_in_refresh_weather_locations() {
	$page_id = ltt_dive_in_get_weather_tab_page_id();

	if ( ! $page_id ) {
		return;
	}

	foreach ( ltt_dive_in_get_weather_locations( $page_id ) as $location ) {
		ltt_dive_in_get_weather_report( $location['latitude'], $location['longitude'], true );
	}
}
add_action( 'ltt_dive_in_refresh_weather_locations', 'ltt_dive_in_refresh_weather_locations' );

/**
 * Remove the recurring weather refresh when the theme is switched off.
 */
function ltt_dive_in_unschedule_weather_locations_refresh() {
	wp_clear_scheduled_hook( 'ltt_dive_in_refresh_weather_locations' );
}
add_action( 'switch_theme', 'ltt_dive_in_unschedule_weather_locations_refresh' );

/**
 * Request one One Call 4.0 endpoint.
 *
 * @param string $endpoint  Path below `/data/4.0/onecall/`, such as `current`.
 * @param float  $latitude  Location latitude.
 * @param float  $longitude Location longitude.
 * @param array  $args      Additional query arguments.
 * @return array|WP_Error Decoded response body or an error.
 */
function ltt_dive_in_request_one_call( $endpoint, $latitude, $longitude, $args = array() ) {
	$api_key = ltt_dive_in_get_header_option( 'ltt_dive_in_openweather_api_key' );

	if ( ! is_string( $api_key ) || '' === trim( $api_key ) ) {
		return new WP_Error( 'ltt_dive_in_weather_missing_api_key' );
	}

	$response = wp_safe_remote_get(
		add_query_arg(
			array_merge(
				$args,
				array(
					'lat'   => $latitude,
					'lon'   => $longitude,
					'units' => 'imperial',
					'appid' => trim( $api_key ),
				)
			),
			'https://api.openweathermap.org/data/4.0/onecall/' . $endpoint
		),
		array(
			'timeout'     => 10,
			'redirection' => 2,
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'ltt_dive_in_weather_report_request_failed' );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	return is_array( $body ) ? $body : new WP_Error( 'ltt_dive_in_weather_report_invalid_response' );
}

/**
 * Request and normalize a One Call 4.0 report.
 *
 * Uses two calls: current weather, then the daily timeline starting at the
 * location's current date, which supplies today's high and low.
 *
 * @param float $latitude  Location latitude.
 * @param float $longitude Location longitude.
 * @return array|WP_Error Normalized report or an error.
 */
function ltt_dive_in_fetch_weather_report( $latitude, $longitude ) {
	$now = ltt_dive_in_request_one_call( 'current', $latitude, $longitude );

	if ( is_wp_error( $now ) ) {
		return $now;
	}

	if ( ! isset( $now['data'][0]['temp'] ) ) {
		return new WP_Error( 'ltt_dive_in_weather_report_invalid_response' );
	}

	$current  = $now['data'][0];
	$timezone = isset( $now['timezone'] ) && in_array( $now['timezone'], timezone_identifiers_list(), true ) ? $now['timezone'] : 'UTC';
	$today    = wp_date( 'Y-m-d', isset( $current['dt'] ) ? (int) $current['dt'] : time(), new DateTimeZone( $timezone ) );

	// Daily `dt` is midnight UTC of the entry's calendar date, not a local time.
	$timeline = ltt_dive_in_request_one_call( 'timeline/1day', $latitude, $longitude, array( 'start' => strtotime( $today . ' 00:00:00 UTC' ) ) );

	if ( is_wp_error( $timeline ) ) {
		return $timeline;
	}

	if ( ! isset( $timeline['data'] ) || ! is_array( $timeline['data'] ) ) {
		return new WP_Error( 'ltt_dive_in_weather_report_invalid_response' );
	}

	$condition = ltt_dive_in_normalize_weather_condition( $current['weather'][0] ?? array() );
	$daily     = array();
	$forecast  = array();

	foreach ( $timeline['data'] as $day ) {
		if ( isset( $day['dt'], $day['temp']['max'], $day['temp']['min'] ) ) {
			$daily[ gmdate( 'Y-m-d', (int) $day['dt'] ) ] = $day;
		}
	}

	if ( ! isset( $daily[ $today ] ) ) {
		return new WP_Error( 'ltt_dive_in_weather_report_invalid_response' );
	}

	foreach ( $daily as $date => $day ) {
		if ( $date < $today ) {
			continue;
		}

		$forecast[] = array_merge(
			array(
				'time' => ( new DateTimeImmutable( $date . ' 12:00', new DateTimeZone( $timezone ) ) )->getTimestamp(),
				'high' => (int) round( (float) $day['temp']['max'] ),
				'low'  => (int) round( (float) $day['temp']['min'] ),
			),
			ltt_dive_in_normalize_weather_condition( $day['weather'][0] ?? array() )
		);
	}

	return array(
		'timezone'    => $timezone,
		'observed_at' => isset( $current['dt'] ) ? (int) $current['dt'] : time(),
		'current'     => array_merge(
			array(
				'temperature' => (int) round( (float) $current['temp'] ),
				'feels_like'  => isset( $current['feels_like'] ) ? (int) round( (float) $current['feels_like'] ) : null,
				'humidity'    => isset( $current['humidity'] ) ? (int) $current['humidity'] : null,
				'wind_speed'  => isset( $current['wind_speed'] ) ? (int) round( (float) $current['wind_speed'] ) : null,
				'wind_from'   => isset( $current['wind_deg'] ) ? (int) $current['wind_deg'] : null,
				'wind_gust'   => isset( $current['wind_gust'] ) ? (int) round( (float) $current['wind_gust'] ) : null,
				'visibility'  => isset( $current['visibility'] ) ? (int) round( (float) $current['visibility'] / 1609.344 ) : null,
				'uv_index'    => isset( $current['uvi'] ) ? (int) round( (float) $current['uvi'] ) : null,
				'pressure'    => isset( $current['pressure'] ) ? round( (float) $current['pressure'] * 0.02953, 1 ) : null,
				'sunrise'     => isset( $current['sunrise'] ) ? (int) $current['sunrise'] : null,
				'sunset'      => isset( $current['sunset'] ) ? (int) $current['sunset'] : null,
				'high'        => (int) round( (float) $daily[ $today ]['temp']['max'] ),
				'low'         => (int) round( (float) $daily[ $today ]['temp']['min'] ),
			),
			$condition
		),
		// The Current view starts tomorrow; today's range is in the top row.
		'daily'       => array_slice( $forecast, 1, 5 ),
		// The 10-Day Forecast view starts today. One timeline call returns at most 10 days.
		'forecast'    => array_slice( $forecast, 0, 10 ),
	);
}

/**
 * Keep only the validated condition fields the panel renders.
 *
 * @param array $weather One OpenWeather `weather` entry.
 * @return array{condition_id: int, description: string, icon: string}
 */
function ltt_dive_in_normalize_weather_condition( $weather ) {
	$icon = isset( $weather['icon'] ) && is_string( $weather['icon'] ) ? sanitize_key( $weather['icon'] ) : '';

	return array(
		'condition_id' => isset( $weather['id'] ) ? (int) $weather['id'] : 0,
		'description'  => isset( $weather['description'] ) && is_string( $weather['description'] ) ? ucwords( sanitize_text_field( $weather['description'] ) ) : '',
		'icon'         => 1 === preg_match( '/\A(?:01|02|03|04|09|10|11|13|50)[dn]\z/', $icon ) ? $icon : '',
	);
}

/**
 * Resolve the Figma weather icon for an OpenWeather condition.
 *
 * Icons come from the Figma "ICON - PACKAGE 1" weather set: `full-color` and
 * `dark-mode` (white) for dark surfaces, `light-mode` (navy) for light surfaces.
 * Conditions without a Figma icon use OpenWeather's own icon:
 * - Mist, smoke, haze, dust, fog, sand, and ash (701–762); smoke and haze are
 *   the wildfire-season conditions and the highest-priority missing icons.
 * - Squalls (771) and tornadoes (781).
 * Rain, snow, and storms have no night variants; the day icons are used at night.
 * HIGH WINDS and EXTREME HEAT are exported but are not OpenWeather conditions;
 * they stay unused until the client defines wind-speed and temperature thresholds.
 *
 * @param int    $condition_id OpenWeather condition ID.
 * @param string $icon         OpenWeather icon code, such as `01d`.
 * @param string $variant      `full-color`, `light-mode`, or `dark-mode`.
 * @return array{src: string, width: int, height: int, source: string}|array{} Icon image data, or empty when unknown.
 */
function ltt_dive_in_get_weather_icon( $condition_id, $icon, $variant = 'full-color' ) {
	$is_night = 'n' === substr( $icon, -1 );
	$file     = '';

	if ( 800 === $condition_id ) {
		$file = $is_night ? 'clear-night' : 'clear-day';
	} elseif ( in_array( $condition_id, array( 801, 802 ), true ) ) {
		$file = $is_night ? 'partly-cloudy-night' : 'partly-cloudy-day';
	} elseif ( in_array( $condition_id, array( 803, 804 ), true ) ) {
		$file = 'cloudy';
	} elseif ( $condition_id >= 200 && $condition_id < 300 ) {
		$file = 'thunderstorms';
	} elseif ( ( $condition_id >= 300 && $condition_id < 400 ) || in_array( $condition_id, array( 500, 520 ), true ) ) {
		$file = 'light-rain';
	} elseif ( $condition_id >= 500 && $condition_id < 600 ) {
		$file = 'rain';
	} elseif ( in_array( $condition_id, array( 601, 602, 621, 622 ), true ) ) {
		$file = 'snow';
	} elseif ( $condition_id >= 600 && $condition_id < 700 ) {
		$file = 'light-snow';
	}

	$sizes = array(
		'clear-day'           => array( 24, 24 ),
		'clear-night'         => array( 24, 24 ),
		'partly-cloudy-day'   => array( 35, 24 ),
		'partly-cloudy-night' => array( 30, 24 ),
		'cloudy'              => array( 24, 24 ),
		'light-rain'          => array( 24, 28 ),
		'rain'                => array( 24, 27 ),
		'thunderstorms'       => array( 24, 25 ),
		'light-snow'          => array( 24, 27 ),
		'snow'                => array( 24, 24 ),
	);

	if ( $file ) {
		return array(
			'src'    => LTT_DIVE_IN_URI . '/assets/images/icons/weather/' . ( in_array( $variant, array( 'light-mode', 'dark-mode' ), true ) ? $variant : 'full-color' ) . '/' . $file . '.svg',
			'width'  => $sizes[ $file ][0],
			'height' => $sizes[ $file ][1],
			'source' => 'figma',
		);
	}

	if ( '' === $icon ) {
		return array();
	}

	return array(
		'src'    => sprintf( 'https://openweathermap.org/img/wn/%s@4x.png', rawurlencode( $icon ) ),
		'width'  => 200,
		'height' => 200,
		'source' => 'openweather',
	);
}

/**
 * Convert a wind bearing to an eight-point compass label.
 *
 * @param int $degrees Direction the wind blows from, in degrees.
 * @return string
 */
function ltt_dive_in_get_wind_direction( $degrees ) {
	$points = array(
		__( 'N', 'ltt-dive-in' ),
		__( 'NE', 'ltt-dive-in' ),
		__( 'E', 'ltt-dive-in' ),
		__( 'SE', 'ltt-dive-in' ),
		__( 'S', 'ltt-dive-in' ),
		__( 'SW', 'ltt-dive-in' ),
		__( 'W', 'ltt-dive-in' ),
		__( 'NW', 'ltt-dive-in' ),
	);

	return $points[ (int) round( ( ( $degrees % 360 ) + 360 ) % 360 / 45 ) % 8 ];
}
