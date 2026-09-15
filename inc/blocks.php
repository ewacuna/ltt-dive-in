<?php
/**
 * Theme-owned block registration.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register assets referenced by dynamic block metadata.
 *
 * The public enqueue callback may enqueue these same handles. Registering them
 * on init makes them available when WordPress resolves conditional block assets.
 */
function ltt_dive_in_register_block_assets() {
	$style_path            = LTT_DIVE_IN_DIR . '/assets/css/main.css';
	$buttons_style_path    = LTT_DIVE_IN_DIR . '/assets/css/components/buttons.css';
	$accordion_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/accordions.css';
	$accordion_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/accordion.js';

	if ( ! wp_style_is( 'ltt-dive-in-style', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-style',
			LTT_DIVE_IN_URI . '/assets/css/main.css',
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_style_is( 'ltt-dive-in-buttons', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-buttons',
			LTT_DIVE_IN_URI . '/assets/css/components/buttons.css',
			array( 'ltt-dive-in-style' ),
			file_exists( $buttons_style_path ) ? (string) filemtime( $buttons_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_style_is( 'ltt-dive-in-accordions', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-accordions',
			LTT_DIVE_IN_URI . '/assets/css/components/accordions.css',
			array( 'ltt-dive-in-style', 'ltt-dive-in-buttons' ),
			file_exists( $accordion_style_path ) ? (string) filemtime( $accordion_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_script_is( 'ltt-dive-in-accordion', 'registered' ) ) {
		wp_register_script(
			'ltt-dive-in-accordion',
			LTT_DIVE_IN_URI . '/assets/js/components/accordion.js',
			array(),
			file_exists( $accordion_script_path ) ? (string) filemtime( $accordion_script_path ) : LTT_DIVE_IN_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'init', 'ltt_dive_in_register_block_assets', 5 );

/**
 * Add a dedicated category for theme blocks in the editor inserter.
 *
 * @param array[] $categories Existing block categories.
 * @return array[]
 */
function ltt_dive_in_block_categories( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'ltt-blocks',
			'title' => __( 'LTT Blocks', 'ltt-dive-in' ),
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'ltt_dive_in_block_categories' );

/**
 * Register theme-owned ACF blocks and their assets.
 */
function ltt_dive_in_register_blocks() {
	$style_path       = LTT_DIVE_IN_DIR . '/assets/css/components/page-drivers.css';
	$script_path      = LTT_DIVE_IN_DIR . '/assets/js/components/page-drivers.js';
	$page_driver_path = LTT_DIVE_IN_DIR . '/blocks/page-drivers';
	$faq_path         = LTT_DIVE_IN_DIR . '/blocks/faq';

	ltt_dive_in_register_block_assets();

	wp_register_style( 'ltt-dive-in-page-drivers', LTT_DIVE_IN_URI . '/assets/css/components/page-drivers.css', array( 'ltt-dive-in-buttons' ), file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_script( 'ltt-dive-in-page-drivers', LTT_DIVE_IN_URI . '/assets/js/components/page-drivers.js', array(), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'ltt-dive-in-page-drivers', 'ltt_dive_in_page_drivers', array( 'destinationSingular' => __( 'destination shown.', 'ltt-dive-in' ), 'destinationPlural' => __( 'destinations shown.', 'ltt-dive-in' ) ) );

	if ( file_exists( $page_driver_path . '/block.json' ) ) {
		register_block_type( $page_driver_path );
	}

	if ( file_exists( $faq_path . '/block.json' ) ) {
		register_block_type( $faq_path );
	}
}
add_action( 'init', 'ltt_dive_in_register_blocks' );
