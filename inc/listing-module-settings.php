<?php
/**
 * Editor choices and FacetWP facet validation for the Listing Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Offer public content types without tying the block to an unregistered CPT.
 *
 * @param array $field ACF field definition.
 * @return array
 */
function ltt_dive_in_listing_module_source_choices( $field ) {
	$field['choices'] = array();
	$post_types       = get_post_types( array( 'public' => true ), 'objects' );

	foreach ( $post_types as $post_type ) {
		if ( in_array( $post_type->name, array( 'attachment', 'page' ), true ) ) {
			continue;
		}

		$field['choices'][ $post_type->name ] = $post_type->labels->name;
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_listing_module_source', 'ltt_dive_in_listing_module_source_choices' );

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
