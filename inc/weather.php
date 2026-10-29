<?php
/**
 * Header weather indicator.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build display and accessible labels for the header weather indicator.
 *
 * Shows the default location of the Current Conditions weather tab from the
 * same cached One Call report, so the header and the weather page always agree.
 * The header never waits on OpenWeather: an uncached report is queued for
 * WP-Cron and the indicator stays hidden until it is ready.
 *
 * @return array{label: string, accessible_label: string, icon_url: string, icon_width: int, icon_height: int, icon_source: string}
 */
function ltt_dive_in_get_header_weather() {
	$empty    = array(
		'label'            => '',
		'accessible_label' => '',
		'icon_url'         => '',
		'icon_width'       => 0,
		'icon_height'      => 0,
		'icon_source'      => '',
	);
	$location = ltt_dive_in_get_default_weather_location();

	if ( ! $location ) {
		return $empty;
	}

	$report = ltt_dive_in_get_weather_report( $location['latitude'], $location['longitude'], false, false );

	if ( is_wp_error( $report ) ) {
		return $empty;
	}

	$current = $report['current'];
	$label   = sprintf( '%d°F', $current['temperature'] );
	$icon    = ltt_dive_in_get_weather_icon( $current['condition_id'], $current['icon'], 'dark-mode' );

	return array(
		'label'            => $label,
		'accessible_label' => sprintf(
			/* translators: 1: location name, 2: temperature, 3: weather description, 4: update time. */
			__( 'Current conditions in %1$s: %2$s, %3$s. Updated %4$s.', 'ltt-dive-in' ),
			$location['name'],
			$label,
			$current['description'] ? strtolower( $current['description'] ) : __( 'conditions unavailable', 'ltt-dive-in' ),
			wp_date( get_option( 'time_format' ), $report['observed_at'] )
		),
		'icon_url'         => $icon ? $icon['src'] : '',
		'icon_width'       => $icon ? $icon['width'] : 0,
		'icon_height'      => $icon ? $icon['height'] : 0,
		'icon_source'      => $icon ? $icon['source'] : '',
	);
}
