<?php
/**
 * Compatibility for the original variant-specific Page Cluster cards.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load legacy cards into the shared repeater without rewriting saved content.
 *
 * The currently selected variant takes precedence if both old sets exist.
 * ACF stores the shared rows on the next editor save. Legacy values are not
 * deleted here; earlier post revisions retain their original content.
 *
 * @param mixed      $value   Shared repeater value.
 * @param string|int $post_id ACF block or post ID.
 * @param array      $field   Shared field definition.
 * @return mixed
 */
function ltt_dive_in_load_page_cluster_cards( $value, $post_id, $field ) {
	// An explicitly saved empty repeater must not resurrect legacy cards.
	if ( null !== $value && false !== $value ) {
		return $value;
	}
	if ( null !== acf_get_metadata_by_field( $post_id, $field ) ) {
		return $value;
	}
	$variant = acf_get_value( $post_id, array( 'key' => 'field_ltt_dive_in_page_cluster_variant', 'name' => 'ltt_dive_in_page_cluster_variant', 'type' => 'select' ) );
	$variants = 'features' === $variant ? array( 'features', 'hub' ) : array( 'hub', 'features' );
	foreach ( $variants as $legacy_variant ) {
		$prefix = 'ltt_dive_in_page_cluster_' . $legacy_variant . '_';
		$sub_fields = array();
		foreach ( array( 'image' => 'image', 'title' => 'text', 'copy' => 'textarea', 'cta' => 'link' ) as $name => $type ) {
			$sub_fields[] = array( 'key' => 'field_' . $prefix . $name, 'name' => $name, 'type' => $type );
		}
		$rows = acf_get_value( $post_id, array( 'key' => 'field_' . $prefix . 'cards', 'name' => $prefix . 'cards', 'type' => 'repeater', 'sub_fields' => $sub_fields ) );
		if ( ! is_array( $rows ) || ! $rows ) {
			continue;
		}
		$cards = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$card = array();
			foreach ( array( 'image', 'title', 'copy', 'cta' ) as $name ) {
				$card[ 'field_ltt_dive_in_page_cluster_card_' . $name ] = $row[ 'field_' . $prefix . $name ] ?? $row[ $name ] ?? null;
			}
			$cards[] = $card;
		}
		if ( $cards ) {
			return $cards;
		}
	}
	return $value;
}
add_filter( 'acf/load_value/key=field_ltt_dive_in_page_cluster_cards', 'ltt_dive_in_load_page_cluster_cards', 20, 3 );
