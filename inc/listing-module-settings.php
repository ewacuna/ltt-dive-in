<?php
/**
 * Editor choices, card links, and FacetWP validation for the Listing Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the public post types supported by Listing Module cards.
 *
 * @return array Registered post type objects, keyed by name.
 */
function ltt_dive_in_listing_module_post_types() {
	$post_types = get_post_types( array( 'public' => true ), 'objects' );
	unset( $post_types['attachment'], $post_types['page'] );

	return $post_types;
}

/**
 * Offer public content types without tying the block to an unregistered CPT.
 *
 * @param array $field ACF field definition.
 * @return array
 */
function ltt_dive_in_listing_module_source_choices( $field ) {
	$field['choices'] = array();

	foreach ( ltt_dive_in_listing_module_post_types() as $post_type ) {
		$field['choices'][ $post_type->name ] = $post_type->labels->name;
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_listing_module_source', 'ltt_dive_in_listing_module_source_choices' );

/**
 * Register card-specific links on the same post types offered by the block.
 *
 * The location rules are composed after post types have registered, so this
 * code-driven field group can support future public custom post types.
 *
 * @return void
 */
function ltt_dive_in_register_listing_card_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$locations = array();

	foreach ( ltt_dive_in_listing_module_post_types() as $post_type ) {
		$locations[] = array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => $post_type->name,
			),
		);
	}

	if ( ! $locations ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_listing_card_links',
			'title'    => __( 'LTT Dive In — Listing Card Links', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'           => 'field_ltt_dive_in_listing_card_primary_destination',
					'label'         => __( 'Primary button destination', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_listing_card_primary_destination',
					'type'          => 'select',
					'instructions'  => __( 'Optional. Choose a destination to show the first button.', 'ltt-dive-in' ),
					'choices'       => array(
						'none'   => __( 'No primary button', 'ltt-dive-in' ),
						'post'   => __( 'This post', 'ltt-dive-in' ),
						'custom' => __( 'Custom link', 'ltt-dive-in' ),
					),
					'default_value' => 'none',
					'required'      => 0,
					'return_format' => 'value',
					'ui'            => 1,
				),
				array(
					'key'               => 'field_ltt_dive_in_listing_card_primary_label',
					'label'             => __( 'Primary button label', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_listing_card_primary_label',
					'type'              => 'text',
					'instructions'      => __( 'Shown when “This post” is selected. Leave blank for “See More”.', 'ltt-dive-in' ),
					'required'          => 0,
					'maxlength'         => 60,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_listing_card_primary_destination',
								'operator' => '==',
								'value'    => 'post',
							),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_listing_card_primary_link',
					'label'             => __( 'Primary custom link', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_listing_card_primary_link',
					'type'              => 'link',
					'instructions'      => __( 'Set the button label and destination. An incomplete link is not shown.', 'ltt-dive-in' ),
					'required'          => 0,
					'return_format'     => 'array',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_listing_card_primary_destination',
								'operator' => '==',
								'value'    => 'custom',
							),
						),
					),
				),
				array(
					'key'           => 'field_ltt_dive_in_listing_card_secondary_link',
					'label'         => __( 'Second button', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_listing_card_secondary_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. Add a label and destination, such as “Visit Website”. Cards show no more than two buttons.', 'ltt-dive-in' ),
					'required'      => 0,
					'return_format' => 'array',
				),
			),
			'location' => $locations,
			'position' => 'normal',
		)
	);
}
add_action( 'init', 'ltt_dive_in_register_listing_card_fields', 20 );

/**
 * Validate an ACF Link field before it becomes a card button.
 *
 * @param mixed $link ACF Link value.
 * @return array|false Button data, or false for an incomplete link.
 */
function ltt_dive_in_listing_card_link_button( $link ) {
	if ( ! is_array( $link ) ) {
		return false;
	}

	$title = isset( $link['title'] ) && is_string( $link['title'] ) ? trim( wp_strip_all_tags( $link['title'] ) ) : '';
	$url   = isset( $link['url'] ) && is_string( $link['url'] ) ? esc_url_raw( trim( $link['url'] ) ) : '';

	if ( '' === $title || '' === $url ) {
		return false;
	}

	return array(
		'title'  => $title,
		'url'    => $url,
		'target' => isset( $link['target'] ) && '_blank' === $link['target'] ? '_blank' : '',
	);
}

/**
 * Build the two approved card links from a post's own data.
 *
 * @param int|WP_Post $post Post or post ID.
 * @return array Up to two configured buttons.
 */
function ltt_dive_in_listing_card_buttons( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$buttons = array();

	if ( ! function_exists( 'get_field' ) ) {
		return $buttons;
	}

	$primary_destination = get_field( 'ltt_dive_in_listing_card_primary_destination', $post->ID );

	if ( 'post' === $primary_destination ) {
		$primary_url = get_permalink( $post );
		$label       = get_field( 'ltt_dive_in_listing_card_primary_label', $post->ID );
		$label       = is_string( $label ) ? trim( wp_strip_all_tags( $label ) ) : '';

		if ( $primary_url ) {
			$buttons[] = array(
				'title'   => '' !== $label ? $label : __( 'See More', 'ltt-dive-in' ),
				'url'     => $primary_url,
				'variant' => 'primary-outline',
			);
		}
	} elseif ( 'custom' === $primary_destination ) {
		$custom_button = ltt_dive_in_listing_card_link_button( get_field( 'ltt_dive_in_listing_card_primary_link', $post->ID ) );
		if ( $custom_button ) {
			$custom_button['variant'] = 'primary-outline';
			$buttons[]                 = $custom_button;
		}
	}

	$secondary_button = ltt_dive_in_listing_card_link_button( get_field( 'ltt_dive_in_listing_card_secondary_link', $post->ID ) );
	if ( $secondary_button ) {
		$secondary_button['variant'] = 'secondary-outline';
		$buttons[]                    = $secondary_button;
	}

	return array_slice( $buttons, 0, 2 );
}

/**
 * Get a configured Dropdown facet sourced from a taxonomy on the content type.
 *
 * @param string $facet_name FacetWP facet name.
 * @param string $post_type  Optional WordPress post type.
 * @return array|false
 */
function ltt_dive_in_listing_module_get_dropdown_facet( $facet_name, $post_type = '' ) {
	if ( ! function_exists( 'FWP' ) || ! is_string( $facet_name ) || '' === $facet_name ) {
		return false;
	}

	$facet = FWP()->helper->get_facet_by_name( sanitize_key( $facet_name ) );
	if ( ! is_array( $facet ) || 'dropdown' !== ( $facet['type'] ?? '' ) ) {
		return false;
	}

	$source = $facet['source'] ?? '';
	if ( ! is_string( $source ) || 0 !== strpos( $source, 'tax/' ) ) {
		return false;
	}

	$taxonomy = substr( $source, 4 );
	if ( ! get_taxonomy( $taxonomy ) || ( $post_type && ! is_object_in_taxonomy( $post_type, $taxonomy ) ) ) {
		return false;
	}

	return $facet;
}

/**
 * Offer only configured taxonomy Dropdown facets in the block editor.
 *
 * @param array $field ACF field definition.
 * @return array
 */
function ltt_dive_in_listing_module_facet_choices( $field ) {
	$field['choices'] = array();

	if ( ! function_exists( 'FWP' ) ) {
		return $field;
	}

	foreach ( FWP()->helper->get_facets() as $facet ) {
		$name = isset( $facet['name'] ) && is_string( $facet['name'] ) ? $facet['name'] : '';
		if ( ! ltt_dive_in_listing_module_get_dropdown_facet( $name ) ) {
			continue;
		}

		$taxonomy = get_taxonomy( substr( $facet['source'], 4 ) );
		$field['choices'][ $name ] = sprintf( '%s — %s', $facet['label'], $taxonomy->labels->singular_name );
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_listing_module_facet_one', 'ltt_dive_in_listing_module_facet_choices' );
add_filter( 'acf/load_field/key=field_ltt_dive_in_listing_module_facet_two', 'ltt_dive_in_listing_module_facet_choices' );

/**
 * Return a Search facet that uses WordPress' default search engine.
 *
 * @param string $facet_name FacetWP facet name.
 * @return array|false
 */
function ltt_dive_in_listing_module_get_search_facet( $facet_name ) {
	if ( ! function_exists( 'FWP' ) || ! is_string( $facet_name ) || '' === $facet_name ) {
		return false;
	}

	$facet = FWP()->helper->get_facet_by_name( sanitize_key( $facet_name ) );
	if ( ! is_array( $facet ) || 'search' !== ( $facet['type'] ?? '' ) ) {
		return false;
	}

	$engine = $facet['search_engine'] ?? '';
	if ( ! in_array( $engine, array( '', 'default', 'wp' ), true ) ) {
		return false;
	}

	if ( 'yes' !== ( $facet['auto_refresh'] ?? '' ) || 'no' === ( $facet['enable_relevance'] ?? 'yes' ) ) {
		return false;
	}

	return $facet;
}

/**
 * Return a Pager facet configured for Load More.
 *
 * @param string $facet_name FacetWP facet name.
 * @return array|false
 */
function ltt_dive_in_listing_module_get_load_more_facet( $facet_name ) {
	if ( ! function_exists( 'FWP' ) || ! is_string( $facet_name ) || '' === $facet_name ) {
		return false;
	}

	$facet = FWP()->helper->get_facet_by_name( sanitize_key( $facet_name ) );
	if ( ! is_array( $facet ) || 'pager' !== ( $facet['type'] ?? '' ) || 'load_more' !== ( $facet['pager_type'] ?? '' ) ) {
		return false;
	}

	return $facet;
}

/**
 * Offer configured facets of the type expected by each Listing Module field.
 *
 * @param array $field ACF field definition.
 * @return array
 */
function ltt_dive_in_listing_module_interactive_facet_choices( $field ) {
	$field['choices'] = array();

	if ( ! function_exists( 'FWP' ) ) {
		return $field;
	}

	$field_key = $field['key'] ?? '';

	foreach ( FWP()->helper->get_facets() as $facet ) {
		$name  = isset( $facet['name'] ) && is_string( $facet['name'] ) ? $facet['name'] : '';
		$label = isset( $facet['label'] ) && is_string( $facet['label'] ) ? $facet['label'] : $name;

		if ( 'field_ltt_dive_in_listing_module_search_facet' === $field_key ) {
			if ( ! ltt_dive_in_listing_module_get_search_facet( $name ) ) {
				continue;
			}
			$field['choices'][ $name ] = sprintf( '%s — %s', $label, __( 'Search · WP Default', 'ltt-dive-in' ) );
		} elseif ( 'field_ltt_dive_in_listing_module_pager_facet' === $field_key ) {
			if ( ! ltt_dive_in_listing_module_get_load_more_facet( $name ) ) {
				continue;
			}
			$field['choices'][ $name ] = sprintf( '%s — %s', $label, __( 'Pager · Load More', 'ltt-dive-in' ) );
		}
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_listing_module_search_facet', 'ltt_dive_in_listing_module_interactive_facet_choices' );
add_filter( 'acf/load_field/key=field_ltt_dive_in_listing_module_pager_facet', 'ltt_dive_in_listing_module_interactive_facet_choices' );
