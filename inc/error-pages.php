<?php
/**
 * Error-page helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a site integration is rendering the maintenance template.
 *
 * @return bool
 */
function ltt_dive_in_is_503_view() {
	return (bool) apply_filters( 'ltt_dive_in_is_503_view', false );
}

/**
 * Get a configured, approved external camera URL.
 *
 * The embed stays unavailable until a site integration supplies an exact
 * allowed host and an HTTPS URL. Site administrators must verify that the
 * approved host belongs to the external camera provider.
 *
 * @param string $kind Either embed or public.
 * @return string
 */
function ltt_dive_in_get_maintenance_camera_url( $kind = 'embed' ) {
	$filter = 'public' === $kind
		? 'ltt_dive_in_maintenance_camera_public_url'
		: 'ltt_dive_in_maintenance_camera_embed_url';
	$url    = trim( (string) apply_filters( $filter, '' ) );
	$hosts  = (array) apply_filters( 'ltt_dive_in_maintenance_camera_allowed_hosts', array() );
	$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
	$user   = (string) wp_parse_url( $url, PHP_URL_USER );
	$pass   = (string) wp_parse_url( $url, PHP_URL_PASS );
	$port   = wp_parse_url( $url, PHP_URL_PORT );
	$hosts  = array_map( 'strtolower', array_filter( array_map( 'strval', $hosts ) ) );

	if ( 'https' !== $scheme || ! $host || ! in_array( $host, $hosts, true ) || $user || $pass || ( $port && 443 !== $port ) ) {
		return '';
	}

	return esc_url_raw( $url );
}
