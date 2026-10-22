<?php
/**
 * Editor behavior for the End Page Cluster Content Trail event picker.
 *
 * The field group itself is versioned in acf-json/. These hooks reuse the
 * Event Driver's Seeker search so editors pick events the same way in both
 * blocks.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'ltt_dive_in_query_event_driver_hero_events' ) ) {
	add_filter( 'acf/fields/select/query/key=field_ltt_dive_in_end_page_cluster_trail_event', 'ltt_dive_in_query_event_driver_hero_events', 10, 2 );
}

if ( function_exists( 'ltt_dive_in_prepare_event_driver_hero_events' ) ) {
	add_filter( 'acf/prepare_field/key=field_ltt_dive_in_end_page_cluster_trail_event', 'ltt_dive_in_prepare_event_driver_hero_events' );
}

/**
 * Store only a valid Seeker UUID for a Content Trail event.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function ltt_dive_in_update_end_page_cluster_trail_event( $value ) {
	return is_scalar( $value ) ? ltt_dive_in_sanitize_seeker_uuid( (string) $value ) : '';
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_end_page_cluster_trail_event', 'ltt_dive_in_update_end_page_cluster_trail_event' );
