<?php
/**
 * Optional, ordered card selections per Up Driver filter.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the per-filter limit; carousel variants deliberately have no limit here.
 *
 * @param string $layout Layout identifier.
 * @return int
 */
function ltt_dive_in_page_driver_group_limit( $layout ) {
	$limits = array( 'one_up' => 1, 'two_up' => 2, 'three_up' => 3, 'four_up' => 4, 'five_up' => 5 );
	return is_string( $layout ) && isset( $limits[ $layout ] ) ? $limits[ $layout ] : 0;
}

/**
 * Read either serialized block names or submitted ACF field keys.
 *
 * @param array  $data   Block data.
 * @param string $suffix Field suffix.
 * @return mixed
 */
function ltt_dive_in_page_driver_data_value( $data, $suffix ) {
	$name = 'ltt_dive_in_page_driver_' . $suffix;
	return $data[ 'field_' . $name ] ?? $data[ $name ] ?? null;
}

/**
 * Check effective mode without changing dormant selections.
 *
 * @param array $data Block data.
 * @return bool
 */
function ltt_dive_in_page_driver_group_mode( $data ) {
	return ltt_dive_in_page_driver_group_limit( ltt_dive_in_page_driver_data_value( $data, 'layout' ) )
		&& ltt_dive_in_page_driver_data_value( $data, 'enable_toggles' )
		&& ltt_dive_in_page_driver_data_value( $data, 'cards_per_filter' );
}

/**
 * Normalize both ACF repeater submissions and flattened saved block data.
 *
 * @param array $data Block data.
 * @return array
 */
function ltt_dive_in_page_driver_read_groups( $data ) {
	$rows = ltt_dive_in_page_driver_data_value( $data, 'filter_groups' );
	if ( ! is_array( $rows ) ) {
		$count = is_numeric( $rows ) ? min( 6, absint( $rows ) ) : 0;
		$rows  = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$prefix = 'ltt_dive_in_page_driver_filter_groups_' . $i . '_';
			$rows[] = array( 'term' => $data[ $prefix . 'term' ] ?? 0, 'pages' => $data[ $prefix . 'pages' ] ?? array() );
		}
	}
	unset( $rows['acfcloneindex'] );
	return array_map(
		static function ( $row ) {
			$row   = is_array( $row ) ? $row : array();
			$term  = $row['field_ltt_dive_in_page_driver_filter_term'] ?? $row['term'] ?? 0;
			$pages = $row['field_ltt_dive_in_page_driver_filter_pages'] ?? $row['pages'] ?? array();
			return array(
				'term'  => is_scalar( $term ) ? absint( $term ) : 0,
				'pages' => is_array( $pages ) ? array_map( static function ( $id ) { return is_scalar( $id ) ? absint( $id ) : 0; }, array_values( $pages ) ) : array(),
			);
		},
		array_values( $rows )
	);
}

/**
 * Define mode-specific visibility and the code-owned repeater.
 *
 * @param array $group Existing Up Driver field group.
 * @return array
 */
function ltt_dive_in_page_driver_add_group_fields( $group ) {
	$prefix   = 'field_ltt_dive_in_page_driver_';
	$enabled  = array( 'field' => $prefix . 'enable_toggles', 'operator' => '==', 'value' => '1' );
	$active   = array();
	$inactive = array(
		array( array( 'field' => $prefix . 'cards_per_filter', 'operator' => '!=', 'value' => '1' ) ),
		array( array( 'field' => $prefix . 'enable_toggles', 'operator' => '!=', 'value' => '1' ) ),
	);
	$available = array();
	foreach ( array( 'one_up', 'two_up', 'three_up', 'four_up', 'five_up' ) as $layout ) {
		$rule        = array( 'field' => $prefix . 'layout', 'operator' => '==', 'value' => $layout );
		$available[] = array( $rule );
		$active[]    = array( $enabled, $rule, array( 'field' => $prefix . 'cards_per_filter', 'operator' => '==', 'value' => '1' ) );
	}
	foreach ( array( 'six_plus_up', 'monthly', '' ) as $layout ) {
		$inactive[] = array( array( 'field' => $prefix . 'layout', 'operator' => '==', 'value' => $layout ) );
	}
	foreach ( $group['fields'] as &$field ) {
		if ( $prefix . 'tiles' === $field['key'] ) {
			$field['conditional_logic'] = $inactive;
		}
		if ( $prefix . 'toggle_terms' === $field['key'] ) {
			$field['conditional_logic'] = array_map( static function ( $rules ) use ( $enabled ) { return array_merge( array( $enabled ), $rules ); }, $inactive );
		}
	}
	unset( $field );
	$cards_per_filter = array(
		'key' => $prefix . 'cards_per_filter', 'name' => 'ltt_dive_in_page_driver_cards_per_filter',
		'label' => __( 'Cards per filter', 'ltt-dive-in' ), 'type' => 'true_false', 'ui' => 1, 'default_value' => 0,
		'instructions' => __( 'Choose which pages appear under each filter.', 'ltt-dive-in' ),
		'conditional_logic' => $available,
	);
	$pages_index = array_search( $prefix . 'tiles', array_column( $group['fields'], 'key' ), true );
	array_splice( $group['fields'], false === $pages_index ? count( $group['fields'] ) : $pages_index, 0, array( $cards_per_filter ) );
	$group['fields'][] = array(
		'key' => $prefix . 'filter_groups', 'name' => 'ltt_dive_in_page_driver_filter_groups',
		'label' => __( 'Filters', 'ltt-dive-in' ), 'type' => 'repeater',
		'instructions' => __( 'Add 2–5 filters. The first one is shown when the page opens.', 'ltt-dive-in' ),
		'layout' => 'block', 'min' => 0, 'max' => 5, 'collapsed' => $prefix . 'filter_term',
		'button_label' => __( 'Add filter', 'ltt-dive-in' ), 'conditional_logic' => $active,
		'sub_fields' => array(
			array(
				'key' => $prefix . 'filter_term', 'name' => 'term', 'label' => __( 'Filter', 'ltt-dive-in' ),
				'type' => 'select', 'choices' => array(), 'allow_null' => 1, 'ui' => 0, 'return_format' => 'value',
				'instructions' => '',
			),
			array(
				'key' => $prefix . 'filter_pages', 'name' => 'pages', 'label' => __( 'Cards', 'ltt-dive-in' ),
				'type' => 'relationship', 'post_type' => array( 'page' ), 'post_status' => array( 'publish' ),
				'filters' => array( 'search' ), 'return_format' => 'id', 'min' => 0, 'max' => 0,
				'instructions' => __( 'Each page needs a featured image.', 'ltt-dive-in' ),
			),
		),
	);
	return $group;
}

/**
 * Keep saved term labels visible before the editor loads available choices.
 *
 * @param array $field ACF field.
 * @return array
 */
function ltt_dive_in_prepare_page_driver_group_term( $field ) {
	$id = ! empty( $field['value'] ) && is_scalar( $field['value'] ) ? absint( $field['value'] ) : 0;
	if ( $id ) {
		$term = get_term( $id );
		$field['choices'][ $id ] = $term && ! is_wp_error( $term ) ? $term->name : __( 'Filter no longer available', 'ltt-dive-in' );
	}
	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_page_driver_filter_term', 'ltt_dive_in_prepare_page_driver_group_term' );

/**
 * Validate complete groups, shared by ACF, REST saves and editor feedback.
 *
 * @param array $data      Complete data for exactly one block.
 * @param bool  $with_rows Include row-level feedback.
 * @return array Error messages or a report with errors and rows.
 */
function ltt_dive_in_page_driver_group_errors( $data, $with_rows = false ) {
	$report = static function ( $errors, $rows = array() ) use ( $with_rows ) {
		return $with_rows ? array( 'errors' => $errors, 'rows' => $rows ) : $errors;
	};
	if ( ! ltt_dive_in_page_driver_group_mode( $data ) ) {
		return $report( array() );
	}
	$taxonomy = ltt_dive_in_page_driver_data_value( $data, 'taxonomy' );
	if ( ! is_string( $taxonomy ) || ! isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		return $report( array( __( 'Choose what to filter by.', 'ltt-dive-in' ) ) );
	}
	$groups = ltt_dive_in_page_driver_read_groups( $data );
	if ( count( $groups ) < 2 || count( $groups ) > 5 ) {
		return $report( array( __( 'Choose 2–5 filters.', 'ltt-dive-in' ) ) );
	}
	$limit  = ltt_dive_in_page_driver_group_limit( ltt_dive_in_page_driver_data_value( $data, 'layout' ) );
	$seen   = array();
	$errors = array();
	$row_errors = array();
	foreach ( $groups as $index => $group ) {
		$start = count( $errors );
		$term = get_term( $group['term'], $taxonomy );
		if ( ! $term || is_wp_error( $term ) ) {
			/* translators: %d: filter position. */
			$errors[] = sprintf( __( 'Filter %d: choose an option.', 'ltt-dive-in' ), $index + 1 );
			$row_errors[ $index ] = array_slice( $errors, $start );
			continue;
		}
		if ( in_array( $term->term_id, $seen, true ) ) {
			/* translators: %s: filter name. */
			$errors[] = sprintf( __( '“%s” is already used. Choose another filter.', 'ltt-dive-in' ), $term->name );
		}
		$seen[] = $term->term_id;
		$count  = count( $group['pages'] );
		if ( ! $count ) {
			/* translators: %s: filter name. */
			$errors[] = sprintf( __( 'Choose a card for “%s”.', 'ltt-dive-in' ), $term->name );
		} elseif ( $count > $limit ) {
			/* translators: 1: filter name, 2: card limit, 3: excess cards. */
			$errors[] = sprintf( __( '“%1$s” allows up to %2$d cards. Remove %3$d to continue.', 'ltt-dive-in' ), $term->name, $limit, $count - $limit );
		}
		if ( count( array_unique( $group['pages'] ) ) !== $count ) {
			/* translators: %s: filter name. */
			$errors[] = sprintf( __( 'Remove the repeated page from “%s”.', 'ltt-dive-in' ), $term->name );
		}
		foreach ( array_unique( $group['pages'] ) as $id ) {
			$title = $id ? get_the_title( $id ) : '';
			$title = $title ? $title : sprintf( '#%d', $id );
			if ( ! $id || 'page' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) || ! has_post_thumbnail( $id ) || ! wp_attachment_is_image( get_post_thumbnail_id( $id ) ) ) {
				/* translators: 1: filter name, 2: page title. */
				$errors[] = sprintf( __( '“%1$s”: check that “%2$s” is published and has a featured image.', 'ltt-dive-in' ), $term->name, $title );
			} elseif ( ! has_term( $term->term_id, $taxonomy, $id ) ) {
				/* translators: 1: page title, 2: filter name. */
				$errors[] = sprintf( __( '“%1$s” does not match “%2$s”. Choose another page.', 'ltt-dive-in' ), $title, $term->name );
			}
		}
		$row_errors[ $index ] = array_slice( $errors, $start );
	}
	return $report( $errors, $row_errors );
}

/**
 * Validate the active repeater using its block-local sibling values.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted groups.
 * @param array       $field ACF field.
 * @param string      $input Input path.
 * @return bool|string
 */
function ltt_dive_in_validate_page_driver_groups( $valid, $value, $field, $input ) {
	$data = array();
	foreach ( array( 'layout', 'taxonomy', 'enable_toggles', 'cards_per_filter' ) as $suffix ) {
		$data[ 'ltt_dive_in_page_driver_' . $suffix ] = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_' . $suffix, $input );
	}
	$data['ltt_dive_in_page_driver_filter_groups'] = $value;
	$errors = ltt_dive_in_page_driver_group_errors( $data );
	return $errors ? implode( ' ', $errors ) : true;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_page_driver_filter_groups', 'ltt_dive_in_validate_page_driver_groups', 10, 4 );

/**
 * Preserve an inactive taxonomy without requiring an unused value.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted taxonomy.
 * @param array       $field ACF field.
 * @param string      $input Input path.
 * @return bool|string
 */
function ltt_dive_in_validate_page_driver_taxonomy( $valid, $value, $field, $input ) {
	if ( ! ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_page_driver_enable_toggles', $input ) ) {
		return true;
	}
	return is_string( $value ) && isset( ltt_dive_in_get_page_driver_taxonomies()[ $value ] )
		? $valid : __( 'Choose what to filter by.', 'ltt-dive-in' );
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_page_driver_taxonomy', 'ltt_dive_in_validate_page_driver_taxonomy', 10, 4 );

/**
 * Prevent Gutenberg/REST saves from bypassing the group validation.
 *
 * @param stdClass|WP_Error $prepared_post Prepared page.
 * @param WP_REST_Request  $request       REST request.
 * @return stdClass|WP_Error
 */
function ltt_dive_in_validate_page_driver_rest_groups( $prepared_post, $request ) {
	if ( is_wp_error( $prepared_post ) || ! isset( $prepared_post->post_content ) ) {
		return $prepared_post;
	}
	$walk = static function ( $blocks ) use ( &$walk ) {
		foreach ( $blocks as $block ) {
			if ( 'ltt-dive-in/page-drivers' === $block['blockName'] ) {
				$errors = ltt_dive_in_page_driver_group_errors( $block['attrs']['data'] ?? array() );
				if ( $errors ) {
					return $errors;
				}
			}
			$errors = $walk( $block['innerBlocks'] ?? array() );
			if ( $errors ) {
				return $errors;
			}
		}
		return array();
	};
	$errors = $walk( parse_blocks( $prepared_post->post_content ) );
	return $errors ? new WP_Error( 'ltt_dive_in_invalid_filter_groups', implode( ' ', $errors ), array( 'status' => 400 ) ) : $prepared_post;
}
/** Register validation for every REST-editable post type that can contain blocks. */
function ltt_dive_in_register_page_driver_rest_validation() {
	foreach ( get_post_types( array( 'show_in_rest' => true ) ) as $post_type ) {
		add_filter( 'rest_pre_insert_' . $post_type, 'ltt_dive_in_validate_page_driver_rest_groups', 10, 2 );
	}
}
add_action( 'rest_api_init', 'ltt_dive_in_register_page_driver_rest_validation' );

/**
 * Return group feedback and non-destructive suggestions for first activation.
 */
function ltt_dive_in_page_driver_group_feedback() {
	check_ajax_referer( 'ltt_dive_in_page_driver_terms', 'nonce' );
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error( null, 403 );
	}
	$data = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array();
	$suggestions = array();
	$choices = array();
	$taxonomy = ltt_dive_in_page_driver_data_value( $data, 'taxonomy' );
	if ( is_string( $taxonomy ) && isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		$available = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
		if ( is_wp_error( $available ) ) {
			wp_send_json_error( null, 500 );
		}
		$choices = wp_list_pluck( $available, 'name', 'term_id' );
		$terms = ltt_dive_in_page_driver_data_value( $data, 'toggle_terms' );
		$pages = ltt_dive_in_page_driver_data_value( $data, 'tiles' );
		foreach ( is_array( $terms ) ? array_slice( $terms, 0, 5 ) : array() as $term_id ) {
			$term = is_scalar( $term_id ) ? get_term( absint( $term_id ), $taxonomy ) : null;
			if ( ! $term || is_wp_error( $term ) ) {
				continue;
			}
			$cards = array();
			foreach ( is_array( $pages ) ? $pages : array() as $id ) {
				$id = is_scalar( $id ) ? absint( $id ) : 0;
				if ( $id && 'publish' === get_post_status( $id ) && 'page' === get_post_type( $id ) && has_term( $term->term_id, $taxonomy, $id ) ) {
					$cards[] = array( 'id' => $id, 'text' => get_the_title( $id ) );
				}
			}
			$suggestions[] = array( 'term' => $term->term_id, 'pages' => $cards );
		}
	}
	wp_send_json_success( array_merge( ltt_dive_in_page_driver_group_errors( $data, true ), array( 'suggestions' => $suggestions, 'terms' => $choices ) ) );
}
add_action( 'wp_ajax_ltt_dive_in_page_driver_group_feedback', 'ltt_dive_in_page_driver_group_feedback' );

/**
 * Restrict a relationship search to the selected row's authorized taxonomy.
 *
 * @param array $args Relationship query arguments.
 * @return array
 */
function ltt_dive_in_page_driver_group_query( $args ) {
	$nonce = isset( $_POST['ltt_nonce'] ) && is_string( $_POST['ltt_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ltt_nonce'] ) ) : '';
	$taxonomy = isset( $_POST['ltt_taxonomy'] ) && is_string( $_POST['ltt_taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['ltt_taxonomy'] ) ) : '';
	$term_id = isset( $_POST['ltt_term'] ) && is_scalar( $_POST['ltt_term'] ) ? absint( $_POST['ltt_term'] ) : 0;
	if ( ! current_user_can( 'edit_pages' ) || ! wp_verify_nonce( $nonce, 'ltt_dive_in_page_driver_terms' ) || ! isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) || ! $term_id ) {
		$args['post__in'] = array( 0 );
		return $args;
	}
	// ACF counts the post types when formatting relationship search results.
	$args['post_type'] = array( 'page' );
	$args['post_status'] = array( 'publish' );
	$args['tax_query'] = array( array( 'taxonomy' => $taxonomy, 'terms' => array( $term_id ), 'include_children' => false ) );
	return $args;
}
add_filter( 'acf/fields/relationship/query/key=field_ltt_dive_in_page_driver_filter_pages', 'ltt_dive_in_page_driver_group_query' );

/**
 * Build valid editorial groups, priming page metadata and terms in bulk.
 *
 * @param array $data Block data.
 * @return array
 */
function ltt_dive_in_page_driver_render_groups( $data ) {
	$taxonomy = ltt_dive_in_page_driver_data_value( $data, 'taxonomy' );
	if ( ! is_string( $taxonomy ) || ! isset( ltt_dive_in_get_page_driver_taxonomies()[ $taxonomy ] ) ) {
		return array();
	}
	$limit = ltt_dive_in_page_driver_group_limit( ltt_dive_in_page_driver_data_value( $data, 'layout' ) );
	$rows  = array_slice( ltt_dive_in_page_driver_read_groups( $data ), 0, 5 );
	$ids   = array();
	foreach ( $rows as &$row ) {
		$row['pages'] = array_slice( array_values( array_unique( array_filter( $row['pages'] ) ) ), 0, $limit );
		$ids = array_merge( $ids, $row['pages'] );
	}
	unset( $row );
	$ids = array_values( array_unique( $ids ) );
	if ( ! $limit || ! $ids ) {
		return array();
	}
	$posts = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'post__in' => $ids, 'posts_per_page' => count( $ids ), 'no_found_rows' => true ) );
	update_object_term_cache( wp_list_pluck( $posts, 'ID' ), 'page' );
	$cards = array();
	foreach ( $posts as $post ) {
		$image = get_post_thumbnail_id( $post->ID );
		$title = get_the_title( $post );
		if ( $image && wp_attachment_is_image( $image ) && trim( $title ) ) {
			$cards[ $post->ID ] = array( 'id' => $post->ID, 'title' => $title, 'excerpt' => get_the_excerpt( $post ), 'image' => $image );
		}
	}
	$groups = array();
	foreach ( $rows as $row ) {
		$term = get_term( $row['term'], $taxonomy );
		if ( ! $term || is_wp_error( $term ) || isset( $groups[ $term->term_id ] ) ) {
			continue;
		}
		$tiles = array();
		foreach ( $row['pages'] as $id ) {
			if ( isset( $cards[ $id ] ) && has_term( $term->term_id, $taxonomy, $id ) ) {
				$tiles[] = array_merge( $cards[ $id ], array( 'terms' => array( $term->term_id ) ) );
			}
		}
		if ( $tiles ) {
			$groups[ $term->term_id ] = array( 'term' => $term, 'tiles' => $tiles );
		}
	}
	return $groups;
}

/**
 * Give identical block copies distinct but repeatable GET namespaces.
 *
 * @param array  $block  ACF block.
 * @param string $anchor Optional editor anchor.
 * @return string
 */
function ltt_dive_in_page_driver_group_instance( $block, $anchor ) {
	static $instances = array();
	$base = $anchor ? $anchor : 'page-drivers-' . substr( md5( wp_json_encode( $block['data'] ?? array() ) ), 0, 12 );
	$instances[ $base ] = ( $instances[ $base ] ?? 0 ) + 1;
	return 1 === $instances[ $base ] ? $base : $base . '-' . $instances[ $base ];
}

/**
 * Create progressively enhanced links while retaining other filter selections.
 *
 * @param array  $groups     Valid groups keyed by term ID.
 * @param string $section_id Block namespace.
 * @return array
 */
function ltt_dive_in_page_driver_group_navigation( $groups, $section_id ) {
	$query = array();
	$raw   = isset( $_GET['ltt_up_filter'] ) && is_array( $_GET['ltt_up_filter'] ) ? wp_unslash( $_GET['ltt_up_filter'] ) : array();
	foreach ( $raw as $key => $value ) {
		if ( is_scalar( $value ) ) {
			$query[ sanitize_html_class( $key ) ] = absint( $value );
		}
	}
	$requested = $query[ $section_id ] ?? 0;
	$selected  = isset( $groups[ $requested ] ) ? $requested : array_key_first( $groups );
	$links     = array();
	foreach ( $groups as $term_id => $group ) {
		$query[ $section_id ] = $term_id;
		$links[ $term_id ] = add_query_arg( 'ltt_up_filter', $query ) . '#' . $section_id;
	}
	return array( 'selected' => $selected, 'label' => $groups[ $selected ]['term']->name ?? '', 'links' => $links );
}
