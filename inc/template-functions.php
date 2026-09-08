<?php
/**
 * Functions hooked into WordPress templates.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add useful classes to the body element.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function ltt_dive_in_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'ltt_dive_in_body_classes' );

/**
 * Add a pingback header for singular posts and pages.
 */
function ltt_dive_in_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'ltt_dive_in_pingback_header' );
