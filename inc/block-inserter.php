<?php
/**
 * Block inserter adjustments.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove the Block Directory ("Available to install") from the inserter.
 *
 * Installing plugins from the editor bypasses the project's approved
 * dependencies.
 */
function ltt_dive_in_disable_block_directory() {
	remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
}
add_action( 'init', 'ltt_dive_in_disable_block_directory' );
