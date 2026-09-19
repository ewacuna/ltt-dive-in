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
	$search_bar_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/search-bar.css';
	$footer_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/footer.css';
	$buttons_style_path     = LTT_DIVE_IN_DIR . '/assets/css/components/buttons.css';
	$select_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/select.css';
	$activities_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/home-activities.css';
	$front_page_style_path  = LTT_DIVE_IN_DIR . '/assets/css/templates/front-page.css';
	$page_hero_style_path   = LTT_DIVE_IN_DIR . '/assets/css/components/page-hero.css';
	$page_style_path        = LTT_DIVE_IN_DIR . '/assets/css/templates/page.css';
	$select_script_path     = LTT_DIVE_IN_DIR . '/assets/js/components/select.js';
	$script_path            = LTT_DIVE_IN_DIR . '/assets/js/main.js';

	wp_enqueue_style(
		'ltt-dive-in-style',
		LTT_DIVE_IN_URI . '/assets/css/main.css',
		array(),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-search-bar',
		LTT_DIVE_IN_URI . '/assets/css/components/search-bar.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $search_bar_style_path ) ? (string) filemtime( $search_bar_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-header',
		LTT_DIVE_IN_URI . '/assets/css/components/header.css',
		array( 'ltt-dive-in-style', 'ltt-dive-in-search-bar' ),
		file_exists( $header_style_path ) ? (string) filemtime( $header_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-footer',
		LTT_DIVE_IN_URI . '/assets/css/components/footer.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $footer_style_path ) ? (string) filemtime( $footer_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-buttons',
		LTT_DIVE_IN_URI . '/assets/css/components/buttons.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $buttons_style_path ) ? (string) filemtime( $buttons_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-select',
		LTT_DIVE_IN_URI . '/assets/css/components/select.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $select_style_path ) ? (string) filemtime( $select_style_path ) : LTT_DIVE_IN_VERSION
	);

	if ( is_front_page() ) {
		wp_enqueue_style(
			'ltt-dive-in-front-page',
			LTT_DIVE_IN_URI . '/assets/css/templates/front-page.css',
			array( 'ltt-dive-in-style', 'ltt-dive-in-header' ),
			file_exists( $front_page_style_path ) ? (string) filemtime( $front_page_style_path ) : LTT_DIVE_IN_VERSION
		);

		wp_enqueue_style(
			'ltt-dive-in-home-activities',
			LTT_DIVE_IN_URI . '/assets/css/components/home-activities.css',
			array( 'ltt-dive-in-buttons' ),
			file_exists( $activities_style_path ) ? (string) filemtime( $activities_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	// Internal pages (page.php and Full Width); Blank Canvas owns its own layout.
	if ( is_page() && ! is_front_page() && ! is_page_template( 'templates/blank-canvas.php' ) ) {
		wp_enqueue_style(
			'ltt-dive-in-page',
			LTT_DIVE_IN_URI . '/assets/css/templates/page.css',
			array( 'ltt-dive-in-style' ),
			file_exists( $page_style_path ) ? (string) filemtime( $page_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ltt_dive_in_has_page_hero() ) {
		wp_enqueue_style(
			'ltt-dive-in-page-hero',
			LTT_DIVE_IN_URI . '/assets/css/components/page-hero.css',
			array( 'ltt-dive-in-style', 'ltt-dive-in-buttons' ),
			file_exists( $page_hero_style_path ) ? (string) filemtime( $page_hero_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	wp_enqueue_script(
		'ltt-dive-in-select',
		LTT_DIVE_IN_URI . '/assets/js/components/select.js',
		array(),
		file_exists( $select_script_path ) ? (string) filemtime( $select_script_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => false,
		)
	);

	wp_enqueue_script(
		'ltt-dive-in-alpine',
		LTT_DIVE_IN_URI . '/assets/js/vendor/alpine.min.js',
		array( 'ltt-dive-in-select' ),
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
 * Bold is intentionally left on demand. Big Caslon is limited to templates
 * whose hero uses it immediately.
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

	if ( is_front_page() || ltt_dive_in_has_page_hero() ) {
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
 * Whether the current view renders the internal page hero.
 *
 * @return bool
 */
function ltt_dive_in_has_page_hero() {
	return ( is_page() && ! is_front_page() && ! is_page_template( 'templates/blank-canvas.php' ) ) || is_singular( 'post' );
}

/**
 * Load foundations scoped to block-editor content, not the admin interface.
 */
function ltt_dive_in_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/main.css' );
}
add_action( 'after_setup_theme', 'ltt_dive_in_editor_styles' );
