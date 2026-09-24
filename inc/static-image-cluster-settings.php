<?php
/**
 * Static Image Cluster validation.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the permitted image count for each cluster variant.
 *
 * The Inspired Gallery is not present in the supplied Figma node. It uses the
 * flexible editorial grid until a dedicated composition is approved.
 *
 * The display value limits rendering without discarding extra editor-selected
 * images. A value of zero renders every image after validation.
 *
 * @return array<string, array{min:int, max:int, display:int}>
 */
function ltt_dive_in_get_static_image_cluster_image_limits() {
	return array(
		'hero_caption'   => array( 'min' => 1, 'max' => 0, 'display' => 1 ),
		'one_up'         => array( 'min' => 1, 'max' => 0, 'display' => 1 ),
		'two_up'         => array( 'min' => 2, 'max' => 0, 'display' => 2 ),
		'three_up'       => array( 'min' => 3, 'max' => 0, 'display' => 3 ),
		'inline_four_up' => array( 'min' => 4, 'max' => 0, 'display' => 4 ),
		'staggered_four' => array( 'min' => 4, 'max' => 0, 'display' => 4 ),
		'five_plus'      => array( 'min' => 5, 'max' => 0, 'display' => 0 ),
		'side_by_side'   => array( 'min' => 1, 'max' => 0, 'display' => 1 ),
		'inspired'       => array( 'min' => 6, 'max' => 0, 'display' => 0 ),
	);
}

/**
 * Recursively find a submitted ACF value by field key.
 *
 * @param array  $values    Submitted values.
 * @param string $field_key Field key.
 * @return mixed
 */
function ltt_dive_in_find_static_image_cluster_submitted_value( $values, $field_key ) {
	foreach ( $values as $key => $value ) {
		if ( $field_key === $key ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$found = ltt_dive_in_find_static_image_cluster_submitted_value( $value, $field_key );

			if ( null !== $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Get a submitted block field value.
 *
 * @param string $field_key Field key.
 * @return mixed
 */
function ltt_dive_in_get_static_image_cluster_submitted_value( $field_key ) {
	if ( empty( $_POST['acf'] ) || ! is_array( $_POST['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return null;
	}

	return ltt_dive_in_find_static_image_cluster_submitted_value( wp_unslash( $_POST['acf'] ), $field_key ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}

/**
 * Validate image counts and Media Library alt text.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Gallery attachment IDs.
 * @return bool|string
 */
function ltt_dive_in_validate_static_image_cluster_images( $valid, $value ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$variant = ltt_dive_in_get_static_image_cluster_submitted_value( 'field_ltt_dive_in_static_image_cluster_variant' );
	$limits  = ltt_dive_in_get_static_image_cluster_image_limits();
	$images  = array_values( array_filter( array_map( 'absint', (array) $value ) ) );

	if ( ! is_string( $variant ) || ! isset( $limits[ $variant ] ) ) {
		return $valid;
	}

	$count   = count( $images );
	$minimum = $limits[ $variant ]['min'];
	if ( $count < $minimum ) {
		return sprintf(
			/* translators: %d: minimum image count. */
			_n( 'This variant requires at least %d image.', 'This variant requires at least %d images.', $minimum, 'ltt-dive-in' ),
			$minimum
		);
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_static_image_cluster_images', 'ltt_dive_in_validate_static_image_cluster_images', 10, 2 );

/** Validate the per-image editorial and alternative-text rows. */
function ltt_dive_in_validate_static_image_cluster_image_details( $valid, $value ) {
	if ( true !== $valid ) { return $valid; }
	$variant = ltt_dive_in_get_static_image_cluster_submitted_value( 'field_ltt_dive_in_static_image_cluster_variant' );
	$limits  = ltt_dive_in_get_static_image_cluster_image_limits();
	$images = ltt_dive_in_get_static_image_cluster_submitted_value( 'field_ltt_dive_in_static_image_cluster_images' );
	$images = array_values( array_filter( array_map( 'absint', (array) $images ) ) );
	if ( ! is_string( $variant ) || ! isset( $limits[ $variant ] ) ) { return $valid; }
	$required_count = $limits[ $variant ]['display'] ? $limits[ $variant ]['display'] : count( $images );
	if ( count( (array) $value ) < $required_count ) { return __( 'Add Image details for every image displayed by this variant, in the same order.', 'ltt-dive-in' ); }
	foreach ( array_slice( (array) $value, 0, $required_count ) as $index => $detail ) {
		if ( ! is_array( $detail ) || '' === trim( (string) ( $detail['alt'] ?? '' ) ) ) { return sprintf( __( 'Image %d needs alternative text.', 'ltt-dive-in' ), $index + 1 ); }
		if ( 'inspired' === $variant && '' === trim( (string) ( $detail['title'] ?? '' ) ) ) { return sprintf( __( 'Inspired Gallery image %d needs a title.', 'ltt-dive-in' ), $index + 1 ); }
	}
	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_static_image_cluster_image_details', 'ltt_dive_in_validate_static_image_cluster_image_details', 10, 2 );

/**
 * Require a section heading for the variants that display editorial content.
 *
 * Hero With Caption uses the shared Introduction as its caption, and Inspired
 * Gallery intentionally keeps its heading optional.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted heading.
 * @return bool|string
 */
function ltt_dive_in_validate_static_image_cluster_heading( $valid, $value ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$variant = ltt_dive_in_get_static_image_cluster_submitted_value( 'field_ltt_dive_in_static_image_cluster_variant' );
	$optional_heading_variants = array( 'hero_caption', 'inspired' );

	if ( ! is_string( $variant ) || in_array( $variant, $optional_heading_variants, true ) ) {
		return $valid;
	}

	if ( '' === trim( (string) $value ) ) {
		return __( 'Section heading is required for this variant.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_static_image_cluster_heading', 'ltt_dive_in_validate_static_image_cluster_heading', 10, 2 );

/**
 * Require the shared Introduction field for Hero With Caption.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted introduction.
 * @return bool|string
 */
function ltt_dive_in_validate_static_image_cluster_intro( $valid, $value ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$variant = ltt_dive_in_get_static_image_cluster_submitted_value( 'field_ltt_dive_in_static_image_cluster_variant' );

	if ( 'hero_caption' === $variant && '' === trim( (string) $value ) ) {
		return __( 'Introduction is required for Hero With Caption.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_static_image_cluster_intro', 'ltt_dive_in_validate_static_image_cluster_intro', 10, 2 );

/**
 * Return links saved in the legacy CTA repeater.
 *
 * @param string|int $post_id ACF post or block ID.
 * @return array<int, array>
 */
function ltt_dive_in_get_legacy_static_image_cluster_ctas( $post_id ) {
	if ( ! function_exists( 'acf_get_value' ) ) {
		return array();
	}

	$rows = acf_get_value(
		$post_id,
		array(
			'key'        => 'field_ltt_dive_in_static_image_cluster_ctas',
			'name'       => 'ltt_dive_in_static_image_cluster_ctas',
			'type'       => 'repeater',
			'sub_fields' => array(
				array(
					'key'  => 'field_ltt_dive_in_static_image_cluster_cta_enabled',
					'name' => 'enabled',
					'type' => 'true_false',
				),
				array(
					'key'  => 'field_ltt_dive_in_static_image_cluster_cta_link',
					'name' => 'link',
					'type' => 'link',
				),
			),
		)
	);
	$links = array();

	foreach ( (array) $rows as $row ) {
		if ( ! is_array( $row ) || empty( $row['enabled'] ) || ! is_array( $row['link'] ?? null ) ) {
			continue;
		}

		$link = $row['link'];

		if ( ! empty( $link['title'] ) && ! empty( $link['url'] ) ) {
			$links[] = $link;
		}
	}

	return array_slice( $links, 0, 3 );
}

/**
 * Populate new CTA fields from the legacy repeater until the block is saved.
 *
 * @param mixed      $value   Current field value.
 * @param string|int $post_id ACF post or block ID.
 * @param array      $field   Field configuration.
 * @return mixed
 */
function ltt_dive_in_load_static_image_cluster_cta( $value, $post_id, $field ) {
	if ( is_array( $value ) && ! empty( $value['title'] ) && ! empty( $value['url'] ) ) {
		return $value;
	}

	$positions = array(
		'field_ltt_dive_in_static_image_cluster_primary_link'   => 0,
		'field_ltt_dive_in_static_image_cluster_secondary_link' => 1,
		'field_ltt_dive_in_static_image_cluster_third_link'     => 2,
	);
	$position  = $positions[ $field['key'] ] ?? null;

	if ( null === $position ) {
		return $value;
	}

	$legacy_links = ltt_dive_in_get_legacy_static_image_cluster_ctas( $post_id );

	return $legacy_links[ $position ] ?? $value;
}
add_filter( 'acf/load_value/key=field_ltt_dive_in_static_image_cluster_primary_link', 'ltt_dive_in_load_static_image_cluster_cta', 20, 3 );
add_filter( 'acf/load_value/key=field_ltt_dive_in_static_image_cluster_secondary_link', 'ltt_dive_in_load_static_image_cluster_cta', 20, 3 );
add_filter( 'acf/load_value/key=field_ltt_dive_in_static_image_cluster_third_link', 'ltt_dive_in_load_static_image_cluster_cta', 20, 3 );
