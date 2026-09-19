<?php
/**
 * Testimonials block data adapters.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize testimonial repeater rows into renderable slides.
 *
 * Rows without a quote or a name are skipped so the block never outputs an
 * unattributed or empty slide. Photos are optional.
 *
 * @param mixed $rows Raw ACF repeater value.
 * @return array[] Prepared testimonials.
 */
function ltt_dive_in_prepare_testimonials( $rows ) {
	$testimonials = array();

	if ( ! is_array( $rows ) ) {
		return $testimonials;
	}

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$quote = isset( $row['quote'] ) && is_string( $row['quote'] ) ? trim( $row['quote'] ) : '';
		$name  = isset( $row['name'] ) && is_string( $row['name'] ) ? trim( $row['name'] ) : '';

		// Quotation marks are drawn by CSS; strip any the editor typed anyway.
		$quote = trim( preg_replace( '/^["\x{201C}\x{201D}\x{201E}]+|["\x{201C}\x{201D}\x{201E}]+$/u', '', $quote ) );

		if ( ! $quote || ! $name ) {
			continue;
		}

		$image_id = isset( $row['photo'] ) ? absint( $row['photo'] ) : 0;

		$testimonials[] = array(
			'quote'    => $quote,
			'name'     => $name,
			'role'     => isset( $row['role'] ) && is_string( $row['role'] ) ? trim( $row['role'] ) : '',
			'image_id' => $image_id && wp_attachment_is_image( $image_id ) ? $image_id : 0,
		);
	}

	return $testimonials;
}
