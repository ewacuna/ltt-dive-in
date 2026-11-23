<?php
/**
 * Block Guide sample content.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the registered block type for a theme block folder.
 *
 * @param string $slug Block folder slug.
 * @return WP_Block_Type|null
 */
function ltt_dive_in_block_guide_block_type( $slug ) {
	$slug          = sanitize_key( $slug );
	$metadata_file = LTT_DIVE_IN_DIR . '/blocks/' . $slug . '/block.json';

	if ( ! $slug || ! file_exists( $metadata_file ) ) {
		return null;
	}

	$metadata = wp_json_file_decode( $metadata_file, array( 'associative' => true ) );

	if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
		return null;
	}

	return WP_Block_Type_Registry::get_instance()->get_registered( $metadata['name'] );
}

/**
 * Get every sample defined for a block, keyed by variant.
 *
 * Samples live in blocks/{slug}/guide-samples.php and use field names.
 * Blocks without variants use the "default" key.
 *
 * @param string $slug Block folder slug.
 * @return array[]
 */
function ltt_dive_in_block_guide_samples( $slug ) {
	static $cache = array();

	$slug = sanitize_key( $slug );

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$file    = LTT_DIVE_IN_DIR . '/blocks/' . $slug . '/guide-samples.php';
	$samples = $slug && file_exists( $file ) ? include $file : array();
	$samples = is_array( $samples ) ? $samples : array();

	$cache[ $slug ] = $samples;

	return $cache[ $slug ];
}

/**
 * Whether a block has a sample for a variant.
 *
 * @param string $slug    Block folder slug.
 * @param string $variant Variant key, or "default".
 * @return bool
 */
function ltt_dive_in_block_guide_has_sample( $slug, $variant = 'default' ) {
	$samples = ltt_dive_in_block_guide_samples( $slug );

	return isset( $samples[ $variant ] ) && is_array( $samples[ $variant ] );
}

/**
 * Convert name-keyed sample values into ACF field-key values.
 *
 * @param array   $values Sample values keyed by field name.
 * @param array[] $fields ACF field definitions.
 * @return array
 */
function ltt_dive_in_block_guide_to_field_keys( $values, $fields ) {
	$keyed = array();

	foreach ( (array) $fields as $field ) {
		if ( empty( $field['name'] ) || ! array_key_exists( $field['name'], $values ) ) {
			continue;
		}

		$value = $values[ $field['name'] ];

		if ( 'repeater' === $field['type'] && is_array( $value ) ) {
			$rows = array();

			foreach ( array_values( $value ) as $index => $row ) {
				$rows[ 'row-' . $index ] = ltt_dive_in_block_guide_to_field_keys( (array) $row, $field['sub_fields'] ?? array() );
			}

			$value = $rows;
		} elseif ( 'group' === $field['type'] && is_array( $value ) ) {
			$value = ltt_dive_in_block_guide_to_field_keys( $value, $field['sub_fields'] ?? array() );
		}

		$keyed[ $field['key'] ] = $value;
	}

	return $keyed;
}

/**
 * Build the parsed block array for a sample.
 *
 * The values are run through ACF's own save routine so the stored data has
 * exactly the shape the block editor produces.
 *
 * @param string $slug    Block folder slug.
 * @param string $variant Variant key, or "default".
 * @return array|null Parsed block, or null when unavailable.
 */
function ltt_dive_in_block_guide_sample_block( $slug, $variant = 'default' ) {
	$block_type = ltt_dive_in_block_guide_block_type( $slug );
	$samples    = ltt_dive_in_block_guide_samples( $slug );

	if ( ! $block_type || ! isset( $samples[ $variant ] ) || ! function_exists( 'acf_get_block_fields' ) || ! function_exists( 'acf_setup_meta' ) ) {
		return null;
	}

	$fields = acf_get_block_fields( array( 'name' => $block_type->name ) );
	$keyed  = ltt_dive_in_block_guide_to_field_keys( $samples[ $variant ], $fields );

	if ( ! $keyed ) {
		return null;
	}

	$storage_id = 'ltt_dive_in_block_guide_' . $slug . '_' . $variant;
	$data       = acf_setup_meta( $keyed, $storage_id );

	acf_reset_meta( $storage_id );

	return array(
		'blockName'    => $block_type->name,
		'attrs'        => array(
			'name' => $block_type->name,
			'data' => is_array( $data ) ? $data : array(),
			'mode' => 'preview',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
}

/**
 * Collect usable sample photos from the Media Library.
 *
 * Prefers large, landscape images that already have alt text.
 *
 * @return int[]
 */
function ltt_dive_in_block_guide_photo_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$candidates = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/webp' ),
			'posts_per_page' => 60,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$preferred  = array();
	$fallback   = array();

	foreach ( $candidates as $id ) {
		$meta    = wp_get_attachment_metadata( $id );
		$width   = isset( $meta['width'] ) ? (int) $meta['width'] : 0;
		$height  = isset( $meta['height'] ) ? (int) $meta['height'] : 0;
		$has_alt = '' !== trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );

		if ( $has_alt && $width >= 1200 && $width > $height ) {
			$preferred[] = (int) $id;
		} elseif ( $has_alt ) {
			$fallback[] = (int) $id;
		}
	}

	$ids = array_merge( $preferred, $fallback );

	return $ids;
}

/**
 * Get one sample photo ID.
 *
 * @param int $index Zero-based position; wraps around the available photos.
 * @return int Attachment ID, or 0 when the library has no usable photo.
 */
function ltt_dive_in_block_guide_photo( $index = 0 ) {
	$ids = ltt_dive_in_block_guide_photo_ids();

	return $ids ? $ids[ absint( $index ) % count( $ids ) ] : 0;
}

/**
 * Get several distinct sample photo IDs.
 *
 * @param int $count  Number of photos.
 * @param int $offset First position.
 * @return int[]
 */
function ltt_dive_in_block_guide_photos( $count, $offset = 0 ) {
	$photos = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$photos[] = ltt_dive_in_block_guide_photo( $offset + $i );
	}

	return array_values( array_filter( $photos ) );
}

/**
 * Get one sample SVG attachment ID for icon fields.
 *
 * @param int $index Zero-based position; wraps around the available icons.
 * @return int Attachment ID, or 0 when none exist.
 */
function ltt_dive_in_block_guide_icon( $index = 0 ) {
	static $ids = null;

	if ( null === $ids ) {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image/svg+xml',
				'posts_per_page' => 30,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		$ids = array_values( array_map( 'intval', $ids ) );
	}

	return $ids ? $ids[ absint( $index ) % count( $ids ) ] : 0;
}

/**
 * Get one MP4 from the Media Library for video samples.
 *
 * @return int Attachment ID, or 0 when the library has no MP4.
 */
function ltt_dive_in_block_guide_video() {
	static $id = null;

	if ( null === $id ) {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'video/mp4',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$id  = $ids ? (int) $ids[0] : 0;
	}

	return $id;
}

/**
 * Build a sample link field value that points to the home page.
 *
 * @param string $label Link label.
 * @return array
 */
function ltt_dive_in_block_guide_link( $label ) {
	return array(
		'title'  => $label,
		'url'    => home_url( '/' ),
		'target' => '',
	);
}

/**
 * Get the first active Gravity Forms form ID for lead-capture samples.
 *
 * @return string Form ID, or an empty string.
 */
function ltt_dive_in_block_guide_form() {
	if ( ! class_exists( 'GFAPI' ) ) {
		return '';
	}

	$forms = GFAPI::get_forms( true, false, 'id', 'ASC' );

	return is_array( $forms ) && ! empty( $forms[0]['id'] ) ? (string) absint( $forms[0]['id'] ) : '';
}

/**
 * Get published posts or pages with a featured image for relationship samples.
 *
 * @param string $post_type Post type.
 * @param int    $count     Number of items.
 * @return int[]
 */
function ltt_dive_in_block_guide_posts( $post_type, $count ) {
	$count      = absint( $count );
	$candidates = array_map(
		'intval',
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => max( 40, $count * 4 ),
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				),
			)
		)
	);

	// Prefer items whose visible card text is real copy, so previews do not
	// show placeholder text; fall back to the rest when there are not enough.
	$real        = array();
	$placeholder = array();

	foreach ( $candidates as $id ) {
		$text = 'page' === get_post_type( $id ) && function_exists( 'ltt_dive_in_get_page_card_description' )
			? ltt_dive_in_get_page_card_description( $id ) . ' ' . get_the_excerpt( $id )
			: get_the_excerpt( $id );

		if ( ltt_dive_in_block_guide_is_placeholder( get_the_title( $id ) . ' ' . $text ) ) {
			$placeholder[] = $id;
		} else {
			$real[] = $id;
		}
	}

	return array_slice( array_merge( $real, $placeholder ), 0, $count );
}

/**
 * Whether text looks like placeholder copy.
 *
 * @param string $text Text to check.
 * @return bool
 */
function ltt_dive_in_block_guide_is_placeholder( $text ) {
	return (bool) preg_match( '/\blorem ipsum\b|\bdolor sit amet\b/i', (string) $text );
}
