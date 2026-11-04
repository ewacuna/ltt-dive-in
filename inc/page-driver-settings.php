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
 * @param string $input     Input path identifying the owning block.
 * @return mixed
 */
function ltt_dive_in_get_page_driver_submitted_value( $field_key, $input = '' ) {
	$values = isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ? wp_unslash( $_POST['acf'] ) : array();
	if ( $input && 0 === strpos( $input, 'acf[' ) ) {
		preg_match_all( '/\[([^\]]+)\]/', $input, $matches );
		$parts = $matches[1];
		array_pop( $parts );
		foreach ( $parts as $part ) {
			$values = isset( $values[ $part ] ) && is_array( $values[ $part ] ) ? $values[ $part ] : array();
		}
		return $values[ $field_key ] ?? null;
	}
	if ( preg_match( '/^acf-([^\[]+)/', $input, $matches ) ) {
		$block_id = $matches[1];
		if ( isset( $_POST['block'] ) && is_string( $_POST['block'] ) ) {
			$block = json_decode( wp_unslash( $_POST['block'] ), true );
			$submitted_id = is_array( $block ) && isset( $block['id'] ) && is_string( $block['id'] ) ? $block['id'] : '';
			if ( $submitted_id && 0 !== strpos( $submitted_id, 'block_' ) ) {
				$submitted_id = 'block_' . $submitted_id;
			}
			$expected_id = 0 === strpos( $block_id, 'block_' ) ? $block_id : 'block_' . $block_id;
			if ( is_array( $block ) && ( ! $submitted_id || $expected_id === $submitted_id ) ) {
				$data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
				$name = preg_replace( '/^field_/', '', $field_key );
				if ( array_key_exists( $field_key, $data ) || array_key_exists( $name, $data ) ) {
					return $data[ $field_key ] ?? $data[ $name ];
				}
			}
		}
		return function_exists( 'get_field' ) ? get_field( preg_replace( '/^field_/', '', $field_key ), $block_id, false ) : null;
	}
	return $values[ $field_key ] ?? null;
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

	$group = array(
			'key'                   => 'group_ltt_dive_in_home_page_drivers',
			'title'                 => __( 'LTT Dive In — Up Driver Block', 'ltt-dive-in' ),
			'fields'                => array(
				array(
					'key'          => 'field_ltt_dive_in_page_driver_layout',
					'label'        => __( 'Layout', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_layout',
					'type'         => 'select',
					'instructions' => __( 'Choose how many cards to show.', 'ltt-dive-in' ),
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
					'instructions' => '',
					'required'     => 1,
					'maxlength'    => 90,
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_surface',
					'label'         => __( 'Background', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_surface',
					'type'          => 'select',
					'instructions'  => '',
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
					'instructions' => __( 'Shown above the heading.', 'ltt-dive-in' ),
					'maxlength'    => 40,
				),
				array(
					'key'          => 'field_ltt_dive_in_page_driver_intro',
					'label'        => __( 'Introduction', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_intro',
					'type'         => 'textarea',
					'instructions' => '',
					'rows'         => 3,
					'new_lines'    => '',
					'maxlength'    => 240,
				),
				array(
					'key'          => 'field_ltt_dive_in_page_driver_button_text',
					'label'        => __( 'Button text', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_page_driver_button_text',
					'type'         => 'text',
					'instructions' => __( 'Used on every card.', 'ltt-dive-in' ),
					'placeholder'  => __( 'Explore', 'ltt-dive-in' ),
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_show_excerpt',
					'label'         => __( 'Show page descriptions', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_show_excerpt',
					'type'          => 'true_false',
					'instructions'  => '',
					'default_value' => 1,
					'ui'            => 1,
				),
				array(
					'key'           => 'field_ltt_dive_in_page_driver_tiles',
					'label'         => __( 'Pages', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_tiles',
					'type'          => 'relationship',
					'instructions'  => __( 'Choose pages with a featured image and arrange them in the order you want.', 'ltt-dive-in' ),
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
					'label'         => __( 'Show filters', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_page_driver_enable_toggles',
					'type'          => 'true_false',
					'instructions'  => '',
					'default_value' => 0,
					'ui'            => 1,
				),
				array(
					'key'               => 'field_ltt_dive_in_page_driver_taxonomy',
					'label'             => __( 'Filter by', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_page_driver_taxonomy',
					'type'              => 'select',
					'instructions'      => __( 'Choose how to group your pages.', 'ltt-dive-in' ),
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
					'label'             => __( 'Filters', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_page_driver_toggle_terms',
					'type'              => 'taxonomy',
					'instructions'      => __( 'Choose the options visitors can select.', 'ltt-dive-in' ),
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
			'description'           => __( 'Show selected pages as cards, with optional filters.', 'ltt-dive-in' ),
		);
	acf_add_local_field_group( ltt_dive_in_page_driver_add_group_fields( $group ) );
}
add_action( 'acf/init', 'ltt_dive_in_register_page_driver_settings' );

/**
 * Require filters and a public page taxonomy for the Monthly Page Driver.
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

	$filters_enabled = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_enable_toggles', $input );
	$taxonomy        = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_taxonomy', $input );

	/* ACF may validate the Layout field before including its sibling fields. */
	if ( null === $filters_enabled ) {
		return $valid;
	}

	if ( ! $filters_enabled ) {
		return __( 'Turn on filters to use Monthly.', 'ltt-dive-in' );
	}

	if ( null === $taxonomy ) {
		return $valid;
	}

	if ( ! is_string( $taxonomy ) || ! isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		return __( 'Choose what to filter by for Monthly.', 'ltt-dive-in' );
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
	$mode_data = array();
	foreach ( array( 'layout', 'enable_toggles', 'cards_per_filter' ) as $suffix ) {
		$mode_data[ 'ltt_dive_in_page_driver_' . $suffix ] = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_' . $suffix, $input );
	}
	if ( ltt_dive_in_page_driver_group_mode( $mode_data ) ) {
		return true;
	}

	if ( true !== $valid ) {
		return $valid;
	}

	$tiles  = is_array( $value ) ? array_values( array_filter( array_map( 'absint', $value ) ) ) : array();
	$layout = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_layout', $input );
	$counts = array(
		'one_up'      => array( 1, 1 ),
		'two_up'      => array( 2, 2 ),
		'three_up'    => array( 3, 3 ),
		'four_up'     => array( 4, 4 ),
		'five_up'     => array( 5, 5 ),
		'six_plus_up' => array( 6, 12 ),
		'monthly'     => array( 12, 12 ),
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
		if ( $minimum === $maximum ) {
			return sprintf(
				/* translators: %d: required page count. */
				_n( 'Choose exactly %d page.', 'Choose exactly %d pages.', $minimum, 'ltt-dive-in' ),
				$minimum
			);
		}
		return sprintf(
			/* translators: 1: minimum number of pages, 2: maximum number of pages. */
			__( 'Choose between %1$d and %2$d pages for this layout.', 'ltt-dive-in' ),
			$minimum,
			$maximum
		);
	}

	foreach ( $tiles as $tile_id ) {
		if ( 'publish' !== get_post_status( $tile_id ) || 'page' !== get_post_type( $tile_id ) || ! has_post_thumbnail( $tile_id ) ) {
			return __( 'Choose published pages with a featured image.', 'ltt-dive-in' );
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
	$mode_data = array();
	foreach ( array( 'layout', 'enable_toggles', 'cards_per_filter' ) as $suffix ) {
		$mode_data[ 'ltt_dive_in_page_driver_' . $suffix ] = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_' . $suffix, $input );
	}
	if ( ltt_dive_in_page_driver_group_mode( $mode_data ) ) {
		return true;
	}

	if ( ! ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_enable_toggles', $input ) ) {
		return true;
	}

	if ( true !== $valid ) {
		return $valid;
	}

	$taxonomy = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_taxonomy', $input );
	$terms    = is_array( $value ) ? array_values( array_filter( array_map( 'absint', $value ) ) ) : array();
	$tiles    = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_tiles', $input );
	$tiles    = is_array( $tiles ) ? array_values( array_filter( array_map( 'absint', $tiles ) ) ) : array();

	if ( ! is_string( $taxonomy ) || ! isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		/* See the dependent-field note above: defer cross-field validation. */
		return $valid;
	}

	$layout = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_layout', $input );

	if ( 'monthly' !== $layout && ( count( $terms ) < 2 || count( $terms ) > 5 ) ) {
		return __( 'Choose 2–5 filters.', 'ltt-dive-in' );
	}

	foreach ( $terms as $term_id ) {
		$term = get_term( $term_id, $taxonomy );

		if ( ! $term || is_wp_error( $term ) ) {
			return __( 'Choose filters from the selected group.', 'ltt-dive-in' );
		}

		$has_tile = false;

		foreach ( $tiles as $tile_id ) {
			if ( has_term( $term_id, $taxonomy, $tile_id ) ) {
				$has_tile = true;
				break;
			}
		}

		if ( ! $has_tile ) {
			return __( 'Choose a matching page for each filter.', 'ltt-dive-in' );
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_page_driver_toggle_terms', 'ltt_dive_in_validate_page_driver_toggle_terms', 10, 4 );
