<?php
/**
 * Page Driver block fields and validation.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get public taxonomies that are available to pages.
 *
 * Content types and taxonomies must be registered by a site plugin, not the
 * theme, so the editorial model survives a theme change.
 *
 * @return WP_Taxonomy[]
 */
function ltt_dive_in_get_page_driver_taxonomies() {
	$taxonomies = get_object_taxonomies( 'page', 'objects' );

	return array_filter(
		$taxonomies,
		static function ( $taxonomy ) {
			return $taxonomy instanceof WP_Taxonomy && $taxonomy->public && $taxonomy->show_ui;
		}
	);
}

/**
 * Return terms for the selected Up Driver taxonomy in the block editor.
 */
function ltt_dive_in_get_page_driver_terms() {
	check_ajax_referer( 'ltt_dive_in_page_driver_terms', 'nonce' );

	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error();
	}

	$taxonomy  = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
	$taxonomies = ltt_dive_in_get_page_driver_taxonomies();

	if ( ! $taxonomy || ! isset( $taxonomies[ $taxonomy ] ) ) {
		wp_send_json_error();
	}

	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );

	if ( is_wp_error( $terms ) ) {
		wp_send_json_error();
	}

	wp_send_json_success( wp_list_pluck( $terms, 'name', 'term_id' ) );
}
add_action( 'wp_ajax_ltt_dive_in_page_driver_terms', 'ltt_dive_in_get_page_driver_terms' );

/**
 * Return selected filter values that are not assigned to selected hub pages.
 */
function ltt_dive_in_get_page_driver_filter_coverage() {
	check_ajax_referer( 'ltt_dive_in_page_driver_terms', 'nonce' );

	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error();
	}

	$taxonomy  = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';
	$tiles     = isset( $_POST['tiles'] ) && is_array( $_POST['tiles'] ) ? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['tiles'] ) ) ) ) : array();
	$term_ids  = isset( $_POST['terms'] ) && is_array( $_POST['terms'] ) ? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['terms'] ) ) ) ) : array();
	$taxonomies = ltt_dive_in_get_page_driver_taxonomies();

	if ( ! $taxonomy || ! isset( $taxonomies[ $taxonomy ] ) ) {
		wp_send_json_error();
	}

	$uncovered_terms = array();

	foreach ( $term_ids as $term_id ) {
		$term = get_term( $term_id, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$has_tile = false;

		foreach ( $tiles as $tile_id ) {
			if ( 'publish' === get_post_status( $tile_id ) && 'page' === get_post_type( $tile_id ) && has_term( $term_id, $taxonomy, $tile_id ) ) {
				$has_tile = true;
				break;
			}
		}

		if ( ! $has_tile ) {
			$uncovered_terms[ $term_id ] = $term->name;
		}
	}

	wp_send_json_success( array( 'uncoveredTerms' => $uncovered_terms ) );
}
add_action( 'wp_ajax_ltt_dive_in_page_driver_filter_coverage', 'ltt_dive_in_get_page_driver_filter_coverage' );

/**
 * Get a submitted ACF value by field key during validation.
 *
 * @param string $field_key ACF field key.
 * @return mixed
 */
function ltt_dive_in_get_page_driver_submitted_value( $field_key ) {
	if ( empty( $_POST['acf'] ) || ! is_array( $_POST['acf'] ) ) {
		return null;
	}

	return ltt_dive_in_find_page_driver_submitted_value( wp_unslash( $_POST['acf'] ), $field_key );
}

/**
 * Find a field value in an ACF request, including ACF Block request data.
 *
 * @param array  $values    Submitted ACF values.
 * @param string $field_key ACF field key.
 * @return mixed
 */
function ltt_dive_in_find_page_driver_submitted_value( $values, $field_key ) {
	foreach ( $values as $key => $value ) {
		if ( $field_key === $key ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$found = ltt_dive_in_find_page_driver_submitted_value( $value, $field_key );

			if ( null !== $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Register the code-driven Page Drivers group.
 */
function ltt_dive_in_register_page_driver_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$taxonomy_choices = array();

	foreach ( ltt_dive_in_get_page_driver_taxonomies() as $taxonomy ) {
		$taxonomy_choices[ $taxonomy->name ] = $taxonomy->labels->singular_name;
	}
	$default_taxonomy = $taxonomy_choices ? (string) array_key_first( $taxonomy_choices ) : 'category';

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_home_page_drivers',
			'title'                 => __( 'LTT Dive In — Up Driver Block', 'ltt-dive-in' ),
			'fields'                => array(
				array(
					'key'          => 'field_ltt_dive_in_page_driver_layout',
					'label'        => __( 'Layout', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_layout',
					'type'         => 'select',
						'instructions' => __( 'Choose the approved card-count variant. The selected page count is validated when this block is saved.', 'ltt-dive-in' ),
					'required'     => 1,
					'choices'      => array(
						'one_up'       => __( '1 Up', 'ltt-dive-in' ),
						'two_up'       => __( '2 Up', 'ltt-dive-in' ),
						'three_up'     => __( '3 Up', 'ltt-dive-in' ),
						'four_up'      => __( '4 Up', 'ltt-dive-in' ),
						'five_up'      => __( '5 Up', 'ltt-dive-in' ),
						'six_plus_up'  => __( '6+ Up', 'ltt-dive-in' ),
						'monthly'      => __( 'Monthly Up Driver', 'ltt-dive-in' ),
					),
					'allow_null'   => 1,
					'ui'           => 1,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_ltt_dive_in_page_driver_heading',
					'label'        => __( 'Heading', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_heading',
					'type'         => 'text',
					'instructions' => __( 'Required section heading.', 'ltt-dive-in' ),
					'required'     => 1,
					'maxlength'    => 90,
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_surface',
					'label'         => __( 'Background', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_surface',
					'type'          => 'select',
					'instructions'  => __( 'Choose the approved light or dark Up Driver surface.', 'ltt-dive-in' ),
					'required'      => 1,
					'choices'       => array(
						'light' => __( 'Light', 'ltt-dive-in' ),
						'dark'  => __( 'Dark', 'ltt-dive-in' ),
					),
					'default_value' => 'light',
					'allow_null'    => 0,
					'ui'            => 1,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_ltt_dive_in_page_driver_tag',
					'label'        => __( 'Category tag', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_tag',
					'type'         => 'text',
					'instructions' => __( 'Optional short label shown above the heading.', 'ltt-dive-in' ),
					'maxlength'    => 40,
				),
				array(
					'key'          => 'field_ltt_dive_in_page_driver_intro',
					'label'        => __( 'Introduction', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_intro',
					'type'         => 'textarea',
					'instructions' => __( 'Optional one- or two-sentence introduction.', 'ltt-dive-in' ),
					'rows'         => 3,
					'new_lines'    => '',
					'maxlength'    => 240,
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_show_excerpt',
					'label'         => __( 'Show tile excerpts', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_show_excerpt',
					'type'          => 'true_false',
					'instructions'  => __( 'When enabled, each tile may show its selected page excerpt.', 'ltt-dive-in' ),
					'default_value' => 1,
					'ui'            => 1,
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_tiles',
					'label'         => __( 'Hub pages', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_tiles',
					'type'          => 'relationship',
					'instructions'  => __( 'Choose and order published hub pages. Every selected page needs a featured image.', 'ltt-dive-in' ),
					'required'      => 1,
					'post_type'     => array( 'page' ),
					'post_status'   => array( 'publish' ),
					'filters'       => array( 'search' ),
					'return_format' => 'id',
					'min'           => 1,
					'max'           => 14,
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_enable_toggles',
					'label'         => __( 'Enable filters', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_enable_toggles',
					'type'          => 'true_false',
					'instructions'  => __( 'Let visitors narrow the displayed hub pages without reloading the page.', 'ltt-dive-in' ),
					'default_value' => 0,
					'ui'            => 1,
				),
				array(
					'key'               => 'field_ltt_dive_in_page_driver_taxonomy',
					'label'             => __( 'Filter taxonomy', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_page_driver_taxonomy',
					'type'              => 'select',
						'instructions'      => __( 'Choose a public taxonomy registered for pages.', 'ltt-dive-in' ),
					'required'          => 1,
					'choices'           => $taxonomy_choices,
					'allow_null'        => 1,
					'ui'                => 1,
					'return_format'     => 'value',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_page_driver_enable_toggles',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_page_driver_toggle_terms',
					'label'             => __( 'Filter values', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_page_driver_toggle_terms',
					'type'              => 'taxonomy',
					'instructions'      => __( 'Select two to five values from the chosen taxonomy. The available terms update when you change the taxonomy.', 'ltt-dive-in' ),
					'required'          => 1,
					'field_type'        => 'checkbox',
					'taxonomy'          => $default_taxonomy,
					'allow_null'        => 0,
					'add_term'          => 0,
					'save_terms'        => 0,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_page_driver_enable_toggles',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'ltt-dive-in/page-drivers',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
			'description'           => __( 'Ordered, feature-image page links with approved grid and client-side filtering variants.', 'ltt-dive-in' ),
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_page_driver_settings' );

/**
 * Require the month taxonomy and filters for the Monthly Page Driver.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_page_driver_layout( $valid, $value, $field, $input ) {
	if ( true !== $valid || 'monthly' !== $value ) {
		return $valid;
	}

	$filters_enabled = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_enable_toggles' );
	$taxonomy        = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_taxonomy' );

	/*
	 * ACF validates the Layout select independently while its value changes in
	 * the block editor. That request contains the Layout field only, not its
	 * sibling filter controls. Defer cross-field validation until the complete
	 * block submission includes those values.
	 */
	if ( null === $filters_enabled ) {
		return $valid;
	}

	if ( ! $filters_enabled ) {
		return __( 'Monthly Up Driver requires filters to be enabled.', 'ltt-dive-in' );
	}

	if ( null === $taxonomy ) {
		return $valid;
	}

	if ( 'month' !== $taxonomy || ! isset( ltt_dive_in_get_page_driver_taxonomies()['month'] ) ) {
		return __( 'Monthly Up Driver requires a public page taxonomy with the slug “month”.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_page_driver_layout', 'ltt_dive_in_validate_page_driver_layout', 10, 4 );

/**
 * Restrict the filter-value control to the taxonomy selected in this block.
 *
 * @param array $field ACF field configuration.
 * @return array
 */
function ltt_dive_in_prepare_page_driver_toggle_terms_field( $field ) {
	$taxonomy = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_taxonomy' );

	/* ACF Blocks load their current values into the active field context. */
	if ( ! is_string( $taxonomy ) || ! $taxonomy ) {
		$taxonomy = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_page_driver_taxonomy' ) : '';
	}

	/*
	 * Keep ACF's block post ID (for example, block_123abc) intact. Casting it
	 * to an integer loses the active block context and leaves this field empty.
	 */
	$post_id = function_exists( 'acf_get_form_data' ) ? acf_get_form_data( 'post_id' ) : 0;
	$post_id = is_string( $post_id ) || is_numeric( $post_id ) ? $post_id : 0;

	if ( ! $post_id && isset( $_GET['post'] ) ) {
		$post_id = absint( wp_unslash( $_GET['post'] ) );
	}

	if ( ( ! is_string( $taxonomy ) || ! $taxonomy ) && $post_id && function_exists( 'get_field' ) ) {
		$taxonomy = get_field( 'ltt_dive_in_page_driver_taxonomy', $post_id );
	}

	if ( is_string( $taxonomy ) && isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		$field['taxonomy'] = $taxonomy;
	}

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_page_driver_toggle_terms', 'ltt_dive_in_prepare_page_driver_toggle_terms_field' );

/**
 * Validate tile count and media requirements.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_page_driver_tiles( $valid, $value, $field, $input ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$tiles  = is_array( $value ) ? array_values( array_filter( array_map( 'absint', $value ) ) ) : array();
	$layout = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_layout' );
	$counts = array(
		'one_up'      => array( 1, 1 ),
		'two_up'      => array( 2, 2 ),
		'three_up'    => array( 3, 3 ),
		'four_up'     => array( 4, 4 ),
		'five_up'     => array( 5, 5 ),
		'six_plus_up' => array( 6, 12 ),
		'monthly'     => array( 8, 14 ),
	);

	if ( ! is_string( $layout ) || ! isset( $counts[ $layout ] ) ) {
		/*
		 * ACF can validate an individual relationship field before sending the
		 * sibling Block fields. The Layout field itself remains required, so
		 * defer the cross-field count check until its value is available.
		 */
		return $valid;
	}

	list( $minimum, $maximum ) = $counts[ $layout ];

	if ( count( $tiles ) < $minimum || count( $tiles ) > $maximum ) {
		return sprintf(
			/* translators: 1: minimum number of pages, 2: maximum number of pages. */
			__( 'This layout requires between %1$d and %2$d hub pages.', 'ltt-dive-in' ),
			$minimum,
			$maximum
		);
	}

	foreach ( $tiles as $tile_id ) {
		if ( 'publish' !== get_post_status( $tile_id ) || 'page' !== get_post_type( $tile_id ) || ! has_post_thumbnail( $tile_id ) ) {
			return __( 'Every hub page must be published, be a page, and have a featured image.', 'ltt-dive-in' );
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_page_driver_tiles', 'ltt_dive_in_validate_page_driver_tiles', 10, 4 );

/**
 * Validate selected filter values against the selected pages.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_page_driver_toggle_terms( $valid, $value, $field, $input ) {
	if ( true !== $valid || ! ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_enable_toggles' ) ) {
		return $valid;
	}

	$taxonomy = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_taxonomy' );
	$terms    = is_array( $value ) ? array_values( array_filter( array_map( 'absint', $value ) ) ) : array();
	$tiles    = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_tiles' );
	$tiles    = is_array( $tiles ) ? array_values( array_filter( array_map( 'absint', $tiles ) ) ) : array();

	if ( ! is_string( $taxonomy ) || ! isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		/* See the dependent-field note above: defer cross-field validation. */
		return $valid;
	}

	$layout = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_layout' );

	if ( 'monthly' !== $layout && ( count( $terms ) < 2 || count( $terms ) > 5 ) ) {
		return __( 'Choose between two and five filter values.', 'ltt-dive-in' );
	}

	foreach ( $terms as $term_id ) {
		$term = get_term( $term_id, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			return __( 'Every filter value must belong to the selected taxonomy.', 'ltt-dive-in' );
		}

		$has_tile = false;

		foreach ( $tiles as $tile_id ) {
			if ( has_term( $term_id, $taxonomy, $tile_id ) ) {
				$has_tile = true;
				break;
			}
		}

		if ( ! $has_tile ) {
			return __( 'Every selected filter value must be assigned to at least one selected hub page.', 'ltt-dive-in' );
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_page_driver_toggle_terms', 'ltt_dive_in_validate_page_driver_toggle_terms', 10, 4 );
