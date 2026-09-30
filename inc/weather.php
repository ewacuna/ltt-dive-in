<?php
/**
 * OpenWeather integration for the global header.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete cached header weather data.
 *
 * @param mixed $value Value being saved by ACF.
 * @return mixed
 */
function ltt_dive_in_clear_header_weather_cache( $value ) {
	delete_transient( 'ltt_dive_in_header_weather' );

	if ( is_string( $value ) && '' !== trim( $value ) ) {
		ltt_dive_in_schedule_header_weather_refresh();
	} else {
		delete_option( 'ltt_dive_in_header_weather_last_success' );
	}

	return $value;
}
add_filter( 'acf/update_value/name=ltt_dive_in_openweather_api_key', 'ltt_dive_in_clear_header_weather_cache' );

/**
 * Schedule a single background refresh if one is not already pending.
 */
function ltt_dive_in_schedule_header_weather_refresh() {
	if ( ! wp_next_scheduled( 'ltt_dive_in_refresh_header_weather' ) ) {
		wp_schedule_single_event( time(), 'ltt_dive_in_refresh_header_weather' );
	}
}

/**
 * Refresh the header weather through WordPress Cron.
 */
function ltt_dive_in_refresh_header_weather() {
	ltt_dive_in_get_current_weather( true );
}
add_action( 'ltt_dive_in_refresh_header_weather', 'ltt_dive_in_refresh_header_weather' );

/**
 * Fetch the current Tahoe City weather from OpenWeather.
 *
 * Fresh responses are cached for 15 minutes. After that, the most recent valid
 * response is served immediately for up to six hours while WordPress refreshes
 * it in the background. Failures are cached briefly to protect the provider.
 *
 * @param bool $force_refresh Whether to bypass caches and contact OpenWeather.
 * @return array<string, int|string>|WP_Error Weather data or an error.
 */
function ltt_dive_in_get_current_weather( $force_refresh = false ) {
	$api_key = ltt_dive_in_get_header_option( 'ltt_dive_in_openweather_api_key' );

	if ( ! is_string( $api_key ) || '' === trim( $api_key ) ) {
		return new WP_Error( 'ltt_dive_in_weather_missing_api_key' );
	}

	if ( ! $force_refresh ) {
		$cached_weather = get_transient( 'ltt_dive_in_header_weather' );

		if ( is_array( $cached_weather ) && isset( $cached_weather['temperature'] ) ) {
			if ( ! isset( $cached_weather['fetched_at'] ) ) {
				$cached_weather['fetched_at'] = time();
				update_option( 'ltt_dive_in_header_weather_last_success', $cached_weather, false );
			}

			if ( ! array_key_exists( 'icon', $cached_weather ) ) {
				ltt_dive_in_schedule_header_weather_refresh();
			}

			return $cached_weather;
		}

		$last_success = get_option( 'ltt_dive_in_header_weather_last_success', array() );

		if (
			is_array( $last_success ) &&
			isset( $last_success['temperature'], $last_success['fetched_at'] ) &&
			is_numeric( $last_success['fetched_at'] ) &&
			time() - (int) $last_success['fetched_at'] <= 6 * HOUR_IN_SECONDS
		) {
			if ( 'unavailable' !== $cached_weather ) {
				ltt_dive_in_schedule_header_weather_refresh();
			}

			return $last_success;
		}

		if ( 'unavailable' !== $cached_weather ) {
			ltt_dive_in_schedule_header_weather_refresh();
		}

		return new WP_Error( 'ltt_dive_in_weather_unavailable' );
	}

	$request_url = add_query_arg(
		array(
			'q'     => 'Tahoe City,CA,US',
			'appid' => trim( $api_key ),
			'units' => 'imperial',
		),
		'https://api.openweathermap.org/data/2.5/weather'
	);

	$response = wp_safe_remote_get(
		$request_url,
		array(
			'timeout'     => 15,
			'redirection' => 2,
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		set_transient( 'ltt_dive_in_header_weather', 'unavailable', 5 * MINUTE_IN_SECONDS );

		return new WP_Error( 'ltt_dive_in_weather_request_failed' );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $body ) || ! isset( $body['main']['temp'] ) || ! is_numeric( $body['main']['temp'] ) ) {
		set_transient( 'ltt_dive_in_header_weather', 'unavailable', 5 * MINUTE_IN_SECONDS );

		return new WP_Error( 'ltt_dive_in_weather_invalid_response' );
	}

	$description = '';
	$icon        = '';

	if ( isset( $body['weather'][0]['description'] ) && is_string( $body['weather'][0]['description'] ) ) {
		$description = sanitize_text_field( $body['weather'][0]['description'] );
	}

	if ( isset( $body['weather'][0]['icon'] ) && is_string( $body['weather'][0]['icon'] ) ) {
		$icon_candidate = sanitize_key( $body['weather'][0]['icon'] );

		if ( 1 === preg_match( '/\A(?:01|02|03|04|09|10|11|13|50)[dn]\z/', $icon_candidate ) ) {
			$icon = $icon_candidate;
		}
	}

	$weather = array(
		'temperature' => (int) round( (float) $body['main']['temp'] ),
		'description' => $description,
		'icon'        => $icon,
		'observed_at' => isset( $body['dt'] ) && is_numeric( $body['dt'] ) ? (int) $body['dt'] : time(),
		'fetched_at'  => time(),
	);

	set_transient( 'ltt_dive_in_header_weather', $weather, 15 * MINUTE_IN_SECONDS );
	update_option( 'ltt_dive_in_header_weather_last_success', $weather, false );

	return $weather;
}

/**
 * Build display and accessible labels for the header weather indicator.
 *
 * @return array{label: string, accessible_label: string, icon_url: string}
 */
function ltt_dive_in_get_header_weather() {
	$weather = ltt_dive_in_get_current_weather();

	if ( ! is_wp_error( $weather ) ) {
		$label            = sprintf( '%d°F', $weather['temperature'] );
		$icon_url         = '';
		$accessible_label = sprintf(
			/* translators: 1: temperature, 2: weather description, 3: update time. */
			__( 'Current conditions in Tahoe City: %1$s, %2$s. Updated %3$s.', 'ltt-dive-in' ),
			$label,
			$weather['description'] ?: __( 'conditions unavailable', 'ltt-dive-in' ),
			wp_date( get_option( 'time_format' ), $weather['observed_at'] )
		);

		if ( ! empty( $weather['icon'] ) && is_string( $weather['icon'] ) ) {
			$icon_url = sprintf( 'https://openweathermap.org/img/wn/%s@2x.png', rawurlencode( $weather['icon'] ) );
		}

		return array(
			'label'            => $label,
			'accessible_label' => $accessible_label,
			'icon_url'         => $icon_url,
		);
	}

	return array(
		'label'            => '',
		'accessible_label' => '',
		'icon_url'         => '',
	);
}
