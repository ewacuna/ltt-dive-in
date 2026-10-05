<?php
/**
 * Page classification fields.
 *
 * Groups every page taxonomy into one ACF panel instead of one native editor
 * panel per taxonomy. The fields read and write real term relationships, so
 * Page Driver filters and other term queries keep working unchanged.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the taxonomies managed by the page classification panel.
 *
 * Uses the same list as the Page Driver filter so every filterable taxonomy
 * can be assigned from one place.
 *
 * @return WP_Taxonomy[]
 */
function ltt_dive_in_get_page_classification_taxonomies() {
	return function_exists( 'ltt_dive_in_get_page_driver_taxonomies' ) ? ltt_dive_in_get_page_driver_taxonomies() : array();
}

/**
 * Register one taxonomy field per page taxonomy in a single sidebar group.
 *
 * Registered in PHP because its fields are composed from the taxonomies that
 * exist at runtime.
 */
function ltt_dive_in_register_page_classification() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array();

	foreach ( ltt_dive_in_get_page_classification_taxonomies() as $taxonomy ) {
		$fields[] = array(
			'key'           => 'field_ltt_dive_in_classification_' . $taxonomy->name,
			'label'         => $taxonomy->labels->name,
			'name'          => 'ltt_dive_in_classification_' . $taxonomy->name,
			'type'          => 'taxonomy',
			'taxonomy'      => $taxonomy->name,
			'field_type'    => 'multi_select',
			'allow_null'    => 1,
			'add_term'      => 0,
			'save_terms'    => 1,
			'load_terms'    => 1,
			'return_format' => 'id',
			'ui'            => 1,
		);
	}

	if ( ! $fields ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_page_classification',
			'title'                 => __( 'Classification', 'ltt-dive-in' ),
			'description'           => __( 'Assign the terms used by Page Driver filters.', 'ltt-dive-in' ),
			'fields'                => $fields,
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'position'              => 'side',
			'style'                 => 'default',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_page_classification' );

/**
 * Hide the native taxonomy panels that the classification group replaces.
 *
 * Only runs when ACF is active, so editors never lose the ability to assign
 * terms.
 */
function ltt_dive_in_enqueue_page_classification_editor() {
	$screen = get_current_screen();

	if ( ! function_exists( 'acf_add_local_field_group' ) || ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	$taxonomies = array_keys( ltt_dive_in_get_page_classification_taxonomies() );

	if ( ! $taxonomies ) {
		return;
	}

	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/page-classification.js';

	wp_enqueue_script( 'ltt-dive-in-page-classification', LTT_DIVE_IN_URI . '/assets/js/admin/page-classification.js', array( 'wp-data', 'wp-dom-ready', 'wp-editor' ), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, true );
	wp_localize_script( 'ltt-dive-in-page-classification', 'ltt_dive_in_page_classification', array( 'taxonomies' => $taxonomies ) );
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_page_classification_editor' );
