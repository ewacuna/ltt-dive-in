<?php
/**
 * Historical weather archive for the Current Conditions Historical Data view.
 *
 * Each location keeps a permanent archive of daily values for the three
 * calendar years before the current one, stored in a non-autoloaded option.
 * Past days never change, so each day is requested from OpenWeather once.
 * An hourly WP-Cron event fills missing days in small batches so the archive
 * does not compete with the current-weather refresh for the daily call quota.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Number of past calendar years shown in the Historical Data view.
 */
const LTT_DIVE_IN_WEATHER_HISTORY_YEARS = 3;

/**
 * Get the option name holding a location's archive.
 *
 * @param float $latitude  Location latitude.
 * @param float $longitude Location longitude.
 * @return string
 */
function ltt_dive_in_get_weather_history_option( $latitude, $longitude ) {
	return 'ltt_dive_in_weather_history_' . md5( round( (float) $latitude, 4 ) . ',' . round( (float) $longitude, 4 ) );
}

/**
 * Get the first and last archive dates for the current calendar year.
 *
 * @return array{0: string, 1: string} Start and end dates as `Y-m-d`.
 */
function ltt_dive_in_get_weather_history_window() {
	$year = (int) wp_date( 'Y' );

	return array(
		( $year - LTT_DIVE_IN_WEATHER_HISTORY_YEARS ) . '-01-01',
		( $year - 1 ) . '-12-31',
	);
}

/**
 * Find the first archive date that has not been requested yet.
 *
 * @param array $days Archived days keyed by `Y-m-d`.
 * @return string Date as `Y-m-d`, or empty when the archive is complete.
 */
function ltt_dive_in_get_weather_history_gap( $days ) {
	list( $start, $end ) = ltt_dive_in_get_weather_history_window();

	for ( $time = strtotime( $start . ' UTC' ); $time <= strtotime( $end . ' UTC' ); $time += DAY_IN_SECONDS ) {
		if ( ! array_key_exists( gmdate( 'Y-m-d', $time ), $days ) ) {
			return gmdate( 'Y-m-d', $time );
		}
	}

	return '';
}

/**
 * Read a precipitation amount, in millimeters, from a daily timeline entry.
 *
 * @param mixed $value `rain` or `snow` value: a number, or an object with `1h`.
 * @return float
 */
function ltt_dive_in_get_weather_history_precipitation( $value ) {
	if ( is_array( $value ) ) {
		$value = $value['1h'] ?? 0;
	}

	return is_numeric( $value ) ? max( 0.0, (float) $value ) : 0.0;
}

/**
 * Request up to ten archive days for a location, starting at its first gap.
 *
 * @param float $latitude  Location latitude.
 * @param float $longitude Location longitude.
 * @return bool|WP_Error True when days were added, false when the archive is complete.
 */
function ltt_dive_in_fill_weather_history( $latitude, $longitude ) {
	$option = ltt_dive_in_get_weather_history_option( $latitude, $longitude );
	$days   = get_option( $option, array() );
	$days   = is_array( $days ) ? $days : array();
	$gap    = ltt_dive_in_get_weather_history_gap( $days );

	if ( '' === $gap ) {
		return false;
	}

	$timeline = ltt_dive_in_request_one_call(
		'timeline/1day',
		$latitude,
		$longitude,
		array(
			'start' => strtotime( $gap . ' 00:00:00 UTC' ),
			'cnt'   => 10,
		)
	);

	if ( is_wp_error( $timeline ) ) {
		return $timeline;
	}

	if ( ! isset( $timeline['data'] ) || ! is_array( $timeline['data'] ) ) {
		return new WP_Error( 'ltt_dive_in_weather_history_invalid_response' );
	}

	list( $start, $end ) = ltt_dive_in_get_weather_history_window();

	foreach ( $timeline['data'] as $day ) {
		if ( ! isset( $day['dt'], $day['temp']['max'], $day['temp']['min'] ) ) {
			continue;
		}

		// Daily `dt` is midnight UTC of the entry's calendar date.
		$date = gmdate( 'Y-m-d', (int) $day['dt'] );

		if ( $date < $start || $date > $end ) {
			continue;
		}

		$rain = ltt_dive_in_get_weather_history_precipitation( $day['rain'] ?? 0 );
		$snow = ltt_dive_in_get_weather_history_precipitation( $day['snow'] ?? 0 );

		// High °F, low °F, precipitation mm, snow mm, wind mph.
		$days[ $date ] = array(
			round( (float) $day['temp']['max'], 1 ),
			round( (float) $day['temp']['min'], 1 ),
			round( $rain + $snow, 2 ),
			round( $snow, 2 ),
			isset( $day['wind_speed'] ) ? round( (float) $day['wind_speed'], 1 ) : null,
		);
	}

	// A day OpenWeather does not return is recorded as missing, so the gap moves on.
	if ( ! array_key_exists( $gap, $days ) ) {
		$days[ $gap ] = null;
	}

	foreach ( array_keys( $days ) as $date ) {
		if ( $date < $start ) {
			unset( $days[ $date ] );
		}
	}

	ksort( $days );
	update_option( $option, $days, false );

	return true;
}

/**
 * Fill every weather location's archive in small batches through WP-Cron.
 *
 * The default of eight requests an hour (192 a day) leaves room in the
 * 1,000 free daily calls beside the current-weather refresh. A new archive of
 * three years takes about 110 requests per location.
 */
function ltt_dive_in_backfill_weather_history() {
	$page_id = ltt_dive_in_get_weather_tab_page_id();

	if ( ! $page_id ) {
		return;
	}

	/**
	 * Filters the number of OpenWeather requests one archive run may make.
	 *
	 * @param int $requests Maximum requests per hourly run.
	 */
	$budget = (int) apply_filters( 'ltt_dive_in_weather_history_requests_per_run', 8 );

	foreach ( ltt_dive_in_get_weather_locations( $page_id ) as $location ) {
		while ( $budget > 0 ) {
			$result = ltt_dive_in_fill_weather_history( $location['latitude'], $location['longitude'] );

			if ( true !== $result ) {
				break;
			}

			--$budget;
		}

		if ( $budget <= 0 ) {
			return;
		}
	}
}
add_action( 'ltt_dive_in_backfill_weather_history', 'ltt_dive_in_backfill_weather_history' );

/**
 * Schedule the hourly archive backfill.
 */
function ltt_dive_in_schedule_weather_history_backfill() {
	if ( ! wp_next_scheduled( 'ltt_dive_in_backfill_weather_history' ) ) {
		wp_schedule_event( time(), 'hourly', 'ltt_dive_in_backfill_weather_history' );
	}
}
add_action( 'init', 'ltt_dive_in_schedule_weather_history_backfill' );

/**
 * Remove the archive backfill when the theme is switched off.
 */
function ltt_dive_in_unschedule_weather_history_backfill() {
	wp_clear_scheduled_hook( 'ltt_dive_in_backfill_weather_history' );
}
add_action( 'switch_theme', 'ltt_dive_in_unschedule_weather_history_backfill' );

/**
 * Summarize a list of archived days.
 *
 * @param array $days     Archived day values.
 * @param int   $per_year Number of years the totals are averaged over.
 * @return array{high: int|null, low: int|null, precipitation: float|null, wind: int|null, snow: float|null}
 */
function ltt_dive_in_summarize_weather_history( $days, $per_year = 1 ) {
	$days  = array_filter( $days );
	$count = count( $days );
	$wind  = array_filter(
		array_column( $days, 4 ),
		static function ( $value ) {
			return null !== $value;
		}
	);

	if ( ! $count ) {
		return array(
			'high'          => null,
			'low'           => null,
			'precipitation' => null,
			'wind'          => null,
			'snow'          => null,
		);
	}

	return array(
		'high'          => (int) round( array_sum( array_column( $days, 0 ) ) / $count ),
		'low'           => (int) round( array_sum( array_column( $days, 1 ) ) / $count ),
		'precipitation' => round( array_sum( array_column( $days, 2 ) ) / $per_year / 25.4, 2 ),
		'wind'          => $wind ? (int) round( array_sum( $wind ) / count( $wind ) ) : null,
		'snow'          => round( array_sum( array_column( $days, 3 ) ) / $per_year / 25.4, 2 ),
	);
}

/**
 * Get the Historical Data tables for a location.
 *
 * Past Seasons averages each meteorological season (winter: December to
 * February) over the archive years, starting with the current season. Its
 * precipitation and snowfall are average season totals. This date in past
 * years lists the same calendar date in each archive year, newest first;
 * February 29 falls back to February 28 in other years.
 *
 * @param float  $latitude  Location latitude.
 * @param float  $longitude Location longitude.
 * @param string $timezone  Location timezone identifier.
 * @return array{first_year: int, last_year: int, seasons: array, dates: array}|null Null until the archive is complete.
 */
function ltt_dive_in_get_weather_history( $latitude, $longitude, $timezone ) {
	$days = get_option( ltt_dive_in_get_weather_history_option( $latitude, $longitude ), array() );

	if ( ! is_array( $days ) || ! $days || '' !== ltt_dive_in_get_weather_history_gap( $days ) ) {
		return null;
	}

	list( $start, $end ) = ltt_dive_in_get_weather_history_window();

	$zone    = new DateTimeZone( $timezone );
	$month   = (int) wp_date( 'n', null, $zone );
	$names   = array(
		'winter' => __( 'Winter', 'ltt-dive-in' ),
		'spring' => __( 'Spring', 'ltt-dive-in' ),
		'summer' => __( 'Summer', 'ltt-dive-in' ),
		'fall'   => __( 'Fall', 'ltt-dive-in' ),
	);
	$keys    = array_keys( $names );
	$current = (int) floor( ( $month % 12 ) / 3 );
	$seasons = array();
	$dates   = array();

	for ( $offset = 0; $offset < 4; $offset++ ) {
		$season  = $keys[ ( $current - $offset + 4 ) % 4 ];
		$members = array_filter(
			$days,
			static function ( $date ) use ( $season, $keys ) {
				return $keys[ (int) floor( ( (int) substr( $date, 5, 2 ) % 12 ) / 3 ) ] === $season;
			},
			ARRAY_FILTER_USE_KEY
		);

		$seasons[] = array_merge(
			array( 'label' => $names[ $season ] ),
			ltt_dive_in_summarize_weather_history( $members, LTT_DIVE_IN_WEATHER_HISTORY_YEARS )
		);
	}

	$month_day = wp_date( 'm-d', null, $zone );

	for ( $year = (int) substr( $end, 0, 4 ); $year >= (int) substr( $start, 0, 4 ); $year-- ) {
		$date = $year . '-' . $month_day;

		if ( '02-29' === $month_day && ! isset( $days[ $date ] ) ) {
			$date = $year . '-02-28';
		}

		$dates[] = array_merge(
			array( 'label' => (string) $year ),
			ltt_dive_in_summarize_weather_history( isset( $days[ $date ] ) ? array( $days[ $date ] ) : array() )
		);
	}

	return array(
		'first_year' => (int) substr( $start, 0, 4 ),
		'last_year'  => (int) substr( $end, 0, 4 ),
		'seasons'    => $seasons,
		'dates'      => $dates,
	);
}
