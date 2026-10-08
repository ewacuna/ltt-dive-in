<?php
/**
 * Content Module ACF field choices and validation.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find a submitted ACF value by field key, including nested repeaters.
 *
 * @param array  $values    Submitted values.
 * @param string $field_key Field key.
 * @return mixed
 */
function ltt_dive_in_find_content_module_submitted_value( $values, $field_key ) {
	foreach ( $values as $key => $value ) {
		if ( $field_key === $key ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$found = ltt_dive_in_find_content_module_submitted_value( $value, $field_key );

			if ( null !== $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Read the selected variant from a submitted ACF form.
 *
 * @return string
 */
function ltt_dive_in_get_submitted_content_module_variant() {
	if ( empty( $_POST['acf'] ) || ! is_array( $_POST['acf'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return '';
	}

	$submitted = wp_unslash( $_POST['acf'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$variant   = ltt_dive_in_find_content_module_submitted_value( $submitted, 'field_ltt_dive_in_content_module_variant' );

	return is_string( $variant ) ? sanitize_key( $variant ) : '';
}

/**
 * Validate that a repeater contains the required number of rows for its variant.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted repeater rows.
 * @param array       $field Field definition.
 * @param string      $input Field input name.
 * @param int         $min   Required minimum count.
 * @param int         $max   Required maximum count.
 * @param string      $variant_key Variant that uses the repeater.
 * @param string      $message Validation message.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_repeater( $valid, $value, $field, $input, $min, $max, $variant_key, $message ) {
	if ( true !== $valid || $variant_key !== ltt_dive_in_get_submitted_content_module_variant() ) {
		return $valid;
	}

	$count = is_array( $value ) ? count( $value ) : 0;

	if ( $count < $min || $count > $max ) {
		return $message;
	}

	return $valid;
}

/**
 * Validate the Icon Block's two to four blurbs.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted repeater rows.
 * @param array       $field Field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_icon_blurbs( $valid, $value, $field, $input ) {
	return ltt_dive_in_validate_content_module_repeater(
		$valid,
		$value,
		$field,
		$input,
		2,
		4,
		'icon_block',
		__( 'Icon Block needs between two and four blurbs.', 'ltt-dive-in' )
	);
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_content_module_icon_blurbs', 'ltt_dive_in_validate_content_module_icon_blurbs', 10, 4 );

/**
 * Validate the Stats variant's two to four values.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted repeater rows.
 * @param array       $field Field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_stats( $valid, $value, $field, $input ) {
	return ltt_dive_in_validate_content_module_repeater(
		$valid,
		$value,
		$field,
		$input,
		2,
		4,
		'stats',
		__( 'Stats needs between two and four items.', 'ltt-dive-in' )
	);
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_content_module_stats', 'ltt_dive_in_validate_content_module_stats', 10, 4 );

/**
 * Clear the retired Photo selector value while keeping its image field active.
 *
 * @param mixed      $value   Saved selector value.
 * @param string|int $post_id ACF post or block ID.
 * @param array      $field   ACF field definition.
 * @return mixed
 */
function ltt_dive_in_load_content_module_stats_background_color( $value, $post_id, $field ) {
	return 'photo' === $value ? '' : $value;
}
add_filter( 'acf/load_value/key=field_ltt_dive_in_content_module_stats_background_color', 'ltt_dive_in_load_content_module_stats_background_color', 20, 3 );

/**
 * Validate the Comparison Table's two to four headers.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted repeater rows.
 * @param array       $field Field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_comparison_columns( $valid, $value, $field, $input ) {
	return ltt_dive_in_validate_content_module_repeater(
		$valid,
		$value,
		$field,
		$input,
		2,
		4,
		'comparison_table',
		__( 'Comparison Table needs between two and four column headings.', 'ltt-dive-in' )
	);
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_content_module_comparison_columns', 'ltt_dive_in_validate_content_module_comparison_columns', 10, 4 );

/**
 * Validate the Comparison Table's one to five rows.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted repeater rows.
 * @param array       $field Field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_comparison_rows( $valid, $value, $field, $input ) {
	return ltt_dive_in_validate_content_module_repeater(
		$valid,
		$value,
		$field,
		$input,
		1,
		5,
		'comparison_table',
		__( 'Comparison Table needs between one and five rows.', 'ltt-dive-in' )
	);
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_content_module_comparison_rows', 'ltt_dive_in_validate_content_module_comparison_rows', 10, 4 );

/**
 * Populate Content Module form selectors with active Gravity Forms.
 *
 * @param array $field ACF select field.
 * @return array
 */
function ltt_dive_in_load_content_module_form_choices( $field ) {
	$field['choices'] = array();

	if ( ! is_admin() || ! class_exists( 'GFAPI' ) ) {
		return $field;
	}

	$forms = GFAPI::get_forms( true, false, 'title', 'ASC' );

	if ( ! is_array( $forms ) ) {
		return $field;
	}

	foreach ( $forms as $form ) {
		if ( ! empty( $form['id'] ) && ! empty( $form['title'] ) ) {
			$field['choices'][ (string) absint( $form['id'] ) ] = sanitize_text_field( $form['title'] );
		}
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_content_module_newsletter_form', 'ltt_dive_in_load_content_module_form_choices' );
add_filter( 'acf/load_field/key=field_ltt_dive_in_content_module_meetings_form', 'ltt_dive_in_load_content_module_form_choices' );

/**
 * Show a saved single testimonial as the first repeater row until it is saved.
 *
 * The legacy block attributes remain intact. A saved empty repeater takes
 * precedence so removing all rows cannot restore the old testimonial.
 *
 * @param mixed      $value   Repeater value.
 * @param string|int $post_id ACF block or post ID.
 * @param array      $field   Repeater field definition.
 * @return mixed
 */
function ltt_dive_in_load_content_module_testimonials( $value, $post_id, $field ) {
	if ( null !== $value && false !== $value ) {
		return $value;
	}

	if ( null !== acf_get_metadata_by_field( $post_id, $field ) ) {
		return $value;
	}

	$legacy_fields = array(
		'quote' => array(
			'key'  => 'field_ltt_dive_in_content_module_testimonial_quote',
			'name' => 'ltt_dive_in_content_module_testimonial_quote',
			'type' => 'textarea',
		),
		'photo' => array(
			'key'  => 'field_ltt_dive_in_content_module_testimonial_photo',
			'name' => 'ltt_dive_in_content_module_testimonial_photo',
			'type' => 'image',
		),
		'name'  => array(
			'key'  => 'field_ltt_dive_in_content_module_testimonial_name',
			'name' => 'ltt_dive_in_content_module_testimonial_name',
			'type' => 'text',
		),
		'role'  => array(
			'key'  => 'field_ltt_dive_in_content_module_testimonial_role',
			'name' => 'ltt_dive_in_content_module_testimonial_role',
			'type' => 'text',
		),
	);
	$row         = array();
	$has_content = false;

	foreach ( $legacy_fields as $name => $legacy_field ) {
		$legacy_value = acf_get_value( $post_id, $legacy_field );
		$row[ 'field_ltt_dive_in_content_module_testimonial_item_' . $name ] = $legacy_value;

		if ( 'photo' === $name ? absint( $legacy_value ) > 0 : is_string( $legacy_value ) && '' !== trim( $legacy_value ) ) {
			$has_content = true;
		}
	}

	return $has_content ? array( $row ) : $value;
}
add_filter( 'acf/load_value/key=field_ltt_dive_in_content_module_testimonials', 'ltt_dive_in_load_content_module_testimonials', 20, 3 );

/**
 * Ensure Icon Block file fields reference a real SVG attachment.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted attachment ID.
 * @param array       $field ACF field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_icon_file( $valid, $value, $field, $input ) {
	if ( true !== $valid || 'icon_block' !== ltt_dive_in_get_submitted_content_module_variant() ) {
		return $valid;
	}

	$attachment_id = absint( $value );

	if ( ! $attachment_id || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return __( 'Choose an SVG file from the Media Library for this icon.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_content_module_icon_blurb_icon', 'ltt_dive_in_validate_content_module_icon_file', 10, 4 );

/**
 * Ensure Comparison Table header files reference real SVG attachments.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted attachment ID.
 * @param array       $field ACF field definition.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_content_module_comparison_icon_file( $valid, $value, $field, $input ) {
	if ( true !== $valid || 'comparison_table' !== ltt_dive_in_get_submitted_content_module_variant() ) {
		return $valid;
	}

	$attachment_id = absint( $value );

	if ( ! $attachment_id || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return __( 'Choose an SVG file from the Media Library for this comparison column.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_content_module_comparison_column_icon', 'ltt_dive_in_validate_content_module_comparison_icon_file', 10, 4 );

/**
 * Load the Comparison Table editor behavior only in the block editor.
 *
 * @return void
 */
function ltt_dive_in_enqueue_content_module_comparison_editor_assets() {
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/content-module-comparison-fields.js';

	wp_enqueue_script(
		'ltt-dive-in-content-module-comparison-editor',
		LTT_DIVE_IN_URI . '/assets/js/admin/content-module-comparison-fields.js',
		array( 'acf-input', 'jquery' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		true
	);
	wp_localize_script(
		'ltt-dive-in-content-module-comparison-editor',
		'ltt_dive_in_content_module_comparison_editor',
		array(
			'moveUpShort'   => __( 'Up', 'ltt-dive-in' ),
			'moveDownShort' => __( 'Down', 'ltt-dive-in' ),
			'moveUpLabel'   => __( 'Move up', 'ltt-dive-in' ),
			'moveDownLabel' => __( 'Move down', 'ltt-dive-in' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_content_module_comparison_editor_assets' );
