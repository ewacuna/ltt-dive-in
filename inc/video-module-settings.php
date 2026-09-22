<?php
/**
 * Video Module field validation.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recursively find a submitted ACF value by field key.
 *
 * @param array  $values    Submitted values.
 * @param string $field_key Field key.
 * @return mixed
 */
function ltt_dive_in_find_video_module_submitted_value( $values, $field_key ) {
	foreach ( $values as $key => $value ) {
		if ( $field_key === $key ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$found = ltt_dive_in_find_video_module_submitted_value( $value, $field_key );

			if ( null !== $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Validate item counts and title requirements for the selected variant.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted repeater rows.
 * @param array       $field Field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_video_module_items( $valid, $value, $field, $input ) {
	if ( true !== $valid ) {
		return $valid;
	}

	if ( empty( $_POST['acf'] ) || ! is_array( $_POST['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $valid;
	}

	$submitted = wp_unslash( $_POST['acf'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$variant   = ltt_dive_in_find_video_module_submitted_value( $submitted, 'field_ltt_dive_in_video_module_variant' );
	$rows      = is_array( $value ) ? $value : array();
	$count     = count( $rows );

	if ( in_array( $variant, array( 'editorial', 'full_bleed', 'full_bleed_copy' ), true ) && 1 !== $count ) {
		return __( 'This variant requires exactly one video.', 'ltt-dive-in' );
	}

	if ( 'carousel' === $variant && ( 2 > $count || 8 < $count ) ) {
		return __( 'Video Carousel requires between two and eight videos.', 'ltt-dive-in' );
	}

	if ( 'episodic' === $variant && ( 2 > $count || 12 < $count ) ) {
		return __( 'Episodic requires between two and twelve videos; the first video is featured.', 'ltt-dive-in' );
	}

	if ( 'full_bleed' !== $variant ) {
		foreach ( $rows as $row ) {
			$title = is_array( $row ) ? ( $row['field_ltt_dive_in_video_module_item_title'] ?? '' ) : '';

			if ( ! trim( (string) $title ) ) {
				return __( 'Every video in this variant needs a title.', 'ltt-dive-in' );
			}
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_video_module_items', 'ltt_dive_in_validate_video_module_items', 10, 4 );
