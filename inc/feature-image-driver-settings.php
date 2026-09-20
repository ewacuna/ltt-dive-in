<?php
/**
 * Feature Image Driver field migration and validation.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the permitted number of active items for each variant.
 *
 * @return array<string, array{min:int, max:int}>
 */
function ltt_dive_in_get_feature_image_driver_item_limits() {
	return array(
		'single'   => array( 'min' => 1, 'max' => 1 ),
		'short'    => array( 'min' => 1, 'max' => 1 ),
		'carousel' => array( 'min' => 2, 'max' => 3 ),
		'stacked'  => array( 'min' => 2, 'max' => 5 ),
	);
}

/**
 * Find a submitted field value, including values nested inside an ACF Block.
 *
 * @param array  $values    Submitted ACF values.
 * @param string $field_key Field key to locate.
 * @return mixed
 */
function ltt_dive_in_find_feature_image_driver_submitted_value( $values, $field_key ) {
	foreach ( $values as $key => $value ) {
		if ( $field_key === $key ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$found = ltt_dive_in_find_feature_image_driver_submitted_value( $value, $field_key );

			if ( null !== $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Get a submitted Feature Image Driver value by field key.
 *
 * @param string $field_key Field key to locate.
 * @return mixed
 */
function ltt_dive_in_get_feature_image_driver_submitted_value( $field_key ) {
	if ( empty( $_POST['acf'] ) || ! is_array( $_POST['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return null;
	}

	return ltt_dive_in_find_feature_image_driver_submitted_value( wp_unslash( $_POST['acf'] ), $field_key ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}

/**
 * Read a value from an old field that is no longer displayed in the editor.
 *
 * @param string|int $post_id ACF post or block ID.
 * @param array      $field   Legacy field definition.
 * @return mixed
 */
function ltt_dive_in_get_legacy_feature_image_driver_value( $post_id, $field ) {
	if ( ! function_exists( 'acf_get_value' ) ) {
		return null;
	}

	return acf_get_value( $post_id, $field );
}

/**
 * Convert one legacy repeater row to the shared item structure.
 *
 * @param array  $row     Legacy row.
 * @param string $prefix  Legacy sub-field key prefix: slide or row.
 * @param bool   $has_tag Whether the legacy row supplied a tag.
 * @return array
 */
function ltt_dive_in_migrate_feature_image_driver_row( $row, $prefix, $has_tag ) {
	if ( ! is_array( $row ) ) {
		return array();
	}

	$get = static function ( $name ) use ( $row, $prefix ) {
		$key = 'field_ltt_dive_in_feature_image_driver_' . $prefix . '_' . $name;

		if ( array_key_exists( $key, $row ) ) {
			return $row[ $key ];
		}

		return $row[ $name ] ?? null;
	};

	return array(
		'field_ltt_dive_in_feature_image_driver_item_enabled'   => 1,
		'field_ltt_dive_in_feature_image_driver_item_tag'       => $has_tag ? $get( 'tag' ) : '',
		'field_ltt_dive_in_feature_image_driver_item_image'     => $get( 'image' ),
		'field_ltt_dive_in_feature_image_driver_item_title'     => $get( 'title' ),
		'field_ltt_dive_in_feature_image_driver_item_show_body' => $get( 'show_body' ),
		'field_ltt_dive_in_feature_image_driver_item_body'      => $get( 'body' ),
		'field_ltt_dive_in_feature_image_driver_item_link'      => $get( 'link' ),
	);
}

/**
 * Populate the shared repeater from the previous variant-specific fields.
 *
 * This is a non-destructive compatibility layer. Once the block is saved, ACF
 * stores the returned rows in the new shared field while the old values remain
 * available to older revisions of the block.
 *
 * @param mixed      $value   Current field value.
 * @param string|int $post_id ACF post or block ID.
 * @param array      $field   Field configuration.
 * @return mixed
 */
function ltt_dive_in_load_feature_image_driver_items( $value, $post_id, $field ) {
	if ( is_array( $value ) && $value ) {
		return $value;
	}

	$variant_field = function_exists( 'acf_get_field' ) ? acf_get_field( 'field_ltt_dive_in_feature_image_driver_variant' ) : false;
	$variant       = $variant_field ? acf_get_value( $post_id, $variant_field ) : '';
	$items         = array();

	if ( in_array( $variant, array( 'single', 'short' ), true ) ) {
		$get_legacy = static function ( $name, $type = 'text' ) use ( $post_id ) {
			return ltt_dive_in_get_legacy_feature_image_driver_value(
				$post_id,
				array(
					'key'  => 'field_ltt_dive_in_feature_image_driver_' . $name,
					'name' => 'ltt_dive_in_feature_image_driver_' . $name,
					'type' => $type,
				)
			);
		};

		$image = $get_legacy( 'image', 'image' );
		$title = $get_legacy( 'title' );
		$link  = $get_legacy( 'link', 'link' );

		if ( $image || $title || $link ) {
			$show_body = $get_legacy( 'show_body', 'true_false' );
			$items[]   = array(
				'field_ltt_dive_in_feature_image_driver_item_enabled'   => 1,
				'field_ltt_dive_in_feature_image_driver_item_tag'       => $get_legacy( 'tag' ),
				'field_ltt_dive_in_feature_image_driver_item_image'     => $image,
				'field_ltt_dive_in_feature_image_driver_item_title'     => $title,
				'field_ltt_dive_in_feature_image_driver_item_show_body' => null === $show_body ? 1 : $show_body,
				'field_ltt_dive_in_feature_image_driver_item_body'      => $get_legacy( 'body', 'textarea' ),
				'field_ltt_dive_in_feature_image_driver_item_link'      => $link,
			);
		}
	} elseif ( in_array( $variant, array( 'carousel', 'stacked' ), true ) ) {
		$is_carousel = 'carousel' === $variant;
		$prefix      = $is_carousel ? 'slide' : 'row';
		$field_name  = $is_carousel ? 'slides' : 'rows';
		$sub_fields  = array();

		foreach ( array( 'image', 'title', 'show_body', 'body', 'link' ) as $name ) {
			$type = 'text';

			if ( 'image' === $name ) {
				$type = 'image';
			} elseif ( 'show_body' === $name ) {
				$type = 'true_false';
			} elseif ( 'body' === $name ) {
				$type = 'textarea';
			} elseif ( 'link' === $name ) {
				$type = 'link';
			}

			$sub_fields[] = array(
				'key'  => 'field_ltt_dive_in_feature_image_driver_' . $prefix . '_' . $name,
				'name' => $name,
				'type' => $type,
			);
		}

		if ( $is_carousel ) {
			array_unshift(
				$sub_fields,
				array(
					'key'  => 'field_ltt_dive_in_feature_image_driver_slide_tag',
					'name' => 'tag',
					'type' => 'text',
				)
			);
		}

		$legacy_rows = ltt_dive_in_get_legacy_feature_image_driver_value(
			$post_id,
			array(
				'key'        => 'field_ltt_dive_in_feature_image_driver_' . $field_name,
				'name'       => 'ltt_dive_in_feature_image_driver_' . $field_name,
				'type'       => 'repeater',
				'sub_fields' => $sub_fields,
			)
		);

		foreach ( (array) $legacy_rows as $row ) {
			$migrated = ltt_dive_in_migrate_feature_image_driver_row( $row, $prefix, $is_carousel );

			if ( $migrated ) {
				$items[] = $migrated;
			}
		}
	}

	return $items ? array_slice( $items, 0, 5 ) : $value;
}
add_filter( 'acf/load_value/key=field_ltt_dive_in_feature_image_driver_items', 'ltt_dive_in_load_feature_image_driver_items', 20, 3 );

/**
 * Validate the number of active shared items for the selected variant.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted repeater value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_feature_image_driver_items( $valid, $value, $field, $input ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$variant = ltt_dive_in_get_feature_image_driver_submitted_value( 'field_ltt_dive_in_feature_image_driver_variant' );
	$limits  = ltt_dive_in_get_feature_image_driver_item_limits();

	if ( ! is_string( $variant ) || ! isset( $limits[ $variant ] ) ) {
		return $valid;
	}

	$active_count = 0;
	$active_index = 0;
	$incomplete_active_item = 0;

	foreach ( (array) $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$enabled = $row['field_ltt_dive_in_feature_image_driver_item_enabled'] ?? 1;

		if ( $enabled ) {
			++$active_count;
			++$active_index;

			$image = $row['field_ltt_dive_in_feature_image_driver_item_image'] ?? ( $row['image'] ?? '' );
			$title = $row['field_ltt_dive_in_feature_image_driver_item_title'] ?? ( $row['title'] ?? '' );
			$link  = $row['field_ltt_dive_in_feature_image_driver_item_link'] ?? ( $row['link'] ?? array() );
			$show_body = $row['field_ltt_dive_in_feature_image_driver_item_show_body'] ?? ( $row['show_body'] ?? 0 );
			$body      = $row['field_ltt_dive_in_feature_image_driver_item_body'] ?? ( $row['body'] ?? '' );
			$has_link  = is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] );

			if ( ! $incomplete_active_item && ( ! $image || ! is_string( $title ) || '' === trim( $title ) || ! $has_link || ( $show_body && ( ! is_string( $body ) || '' === trim( $body ) ) ) ) ) {
				$incomplete_active_item = $active_index;
			}
		}
	}

	$minimum = $limits[ $variant ]['min'];
	$maximum = $limits[ $variant ]['max'];

	if ( $active_count < $minimum || $active_count > $maximum ) {
		if ( $minimum === $maximum ) {
			return sprintf(
				/* translators: %d: required number of active items. */
				_n( 'This variant requires exactly %d active item.', 'This variant requires exactly %d active items.', $minimum, 'ltt-dive-in' ),
				$minimum
			);
		}

		return sprintf(
			/* translators: 1: minimum active items, 2: maximum active items. */
			__( 'This variant requires between %1$d and %2$d active items.', 'ltt-dive-in' ),
			$minimum,
			$maximum
		);
	}

	if ( $incomplete_active_item ) {
		return sprintf(
			/* translators: %d: active item position. */
			__( 'Complete the image, title, call to action, and enabled body copy for active item %d.', 'ltt-dive-in' ),
			$incomplete_active_item
		);
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_feature_image_driver_items', 'ltt_dive_in_validate_feature_image_driver_items', 10, 4 );
