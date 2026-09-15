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

function ltt_dive_in_register_blocks() {
	$style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/page-drivers.css';
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/components/page-drivers.js';
	$block_path  = LTT_DIVE_IN_DIR . '/blocks/page-drivers';

	wp_register_style( 'ltt-dive-in-page-drivers', LTT_DIVE_IN_URI . '/assets/css/components/page-drivers.css', array( 'ltt-dive-in-buttons' ), file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_script( 'ltt-dive-in-page-drivers', LTT_DIVE_IN_URI . '/assets/js/components/page-drivers.js', array(), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'ltt-dive-in-page-drivers', 'ltt_dive_in_page_drivers', array( 'destinationSingular' => __( 'destination shown.', 'ltt-dive-in' ), 'destinationPlural' => __( 'destinations shown.', 'ltt-dive-in' ) ) );

	if ( file_exists( $block_path . '/block.json' ) ) {
		register_block_type( $block_path );
	}
}
add_action( 'init', 'ltt_dive_in_register_blocks' );
