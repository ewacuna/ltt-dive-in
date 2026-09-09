<?php
/**
 * Theme scripts and styles.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue public assets.
 */
function ltt_dive_in_enqueue_assets() {
	$style_path             = LTT_DIVE_IN_DIR . '/assets/css/main.css';
	$header_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/header.css';
	$footer_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/footer.css';
	$front_page_style_path  = LTT_DIVE_IN_DIR . '/assets/css/templates/front-page.css';
	$script_path            = LTT_DIVE_IN_DIR . '/assets/js/main.js';

	wp_enqueue_style(
		'ltt-dive-in-style',
		LTT_DIVE_IN_URI . '/assets/css/main.css',
		array(),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-header',
		LTT_DIVE_IN_URI . '/assets/css/components/header.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $header_style_path ) ? (string) filemtime( $header_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-footer',
		LTT_DIVE_IN_URI . '/assets/css/components/footer.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $footer_style_path ) ? (string) filemtime( $footer_style_path ) : LTT_DIVE_IN_VERSION
	);

	if ( is_front_page() ) {
		wp_enqueue_style(
			'ltt-dive-in-front-page',
			LTT_DIVE_IN_URI . '/assets/css/templates/front-page.css',
			array( 'ltt-dive-in-style', 'ltt-dive-in-header' ),
			file_exists( $front_page_style_path ) ? (string) filemtime( $front_page_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	wp_enqueue_script(
		'ltt-dive-in-alpine',
		LTT_DIVE_IN_URI . '/assets/js/vendor/alpine.min.js',
		array(),
		'3.17.2',
		array(
			'strategy'  => 'defer',
			'in_footer' => false,
		)
	);

	wp_enqueue_script(
		'ltt-dive-in-script',
		LTT_DIVE_IN_URI . '/assets/js/main.js',
		array( 'ltt-dive-in-alpine' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_enqueue_assets' );

/**
 * Preload fonts used above the fold.
 *
 * Bold is intentionally left on demand. Big Caslon is limited to the front
 * page, where the hero uses it immediately.
 *
 * @param array[] $preload_resources Resources and attributes to preload.
 * @return array[]
 */
function ltt_dive_in_preload_fonts( $preload_resources ) {
	$preload_resources[] = array(
		'href'        => LTT_DIVE_IN_URI . '/assets/fonts/gotham/Gotham-Book.woff2',
		'as'          => 'font',
		'type'        => 'font/woff2',
		'crossorigin' => 'anonymous',
	);

	if ( is_front_page() ) {
		$preload_resources[] = array(
			'href'        => LTT_DIVE_IN_URI . '/assets/fonts/big-caslon/Big-Caslon-Medium.woff2',
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $preload_resources;
}
add_filter( 'wp_preload_resources', 'ltt_dive_in_preload_fonts' );

/**
 * Load the main stylesheet in the block editor.
 */
function ltt_dive_in_editor_styles() {
	add_editor_style( 'assets/css/main.css' );
}
add_action( 'after_setup_theme', 'ltt_dive_in_editor_styles' );
