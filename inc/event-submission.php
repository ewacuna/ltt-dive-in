<?php
/**
 * Event Submission page template: Seeker submission form.
 *
 * Seeker cannot accept events through its API, so visitors submit events with
 * Seeker's hosted form widget. The widget renders inside its own shadow root;
 * the theme styles it through the widget's `customCss` option.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seeker widget script, loaded only on the Event Submission template.
 */
const LTT_DIVE_IN_SEEKER_WIDGET_SCRIPT = 'https://calendarwidget.seeker.io/widget/calendar-widget.js';

/**
 * Whether the current request renders the Event Submission template.
 *
 * @return bool
 */
function ltt_dive_in_is_event_submission_page() {
	return is_page_template( 'templates/event-submission.php' );
}

/**
 * Register the Event Submission page fields.
 */
function ltt_dive_in_register_event_submission_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'         => 'group_ltt_dive_in_event_submission',
			'title'       => __( 'LTT Dive In — Event Submission', 'ltt-dive-in' ),
			'description' => __( 'The page title, excerpt, and content are edited in WordPress. The form fields are managed in Seeker.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'key'          => 'field_ltt_dive_in_event_submission_calendar_id',
					'label'        => __( 'Calendar ID', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_event_submission_calendar_id',
					'type'         => 'text',
					'instructions' => __( 'The calendarId value from the Seeker embed code.', 'ltt-dive-in' ),
					'placeholder'  => '00000000-0000-0000-0000-000000000000',
				),
				array(
					'key'          => 'field_ltt_dive_in_event_submission_form_id',
					'label'        => __( 'Form ID', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_event_submission_form_id',
					'type'         => 'text',
					'instructions' => __( 'The formId value from the Seeker embed code.', 'ltt-dive-in' ),
					'placeholder'  => '00000000-0000-0000-0000-000000000000',
				),
			),
			'location'    => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/event-submission.php',
					),
				),
			),
			'position'    => 'acf_after_title',
			'style'       => 'default',
			'active'      => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_event_submission_settings' );

/**
 * Require Seeker IDs to be UUIDs when they are filled in.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function ltt_dive_in_validate_event_submission_id( $valid, $value ) {
	if ( true !== $valid || '' === trim( (string) $value ) ) {
		return $valid;
	}

	return ltt_dive_in_sanitize_seeker_uuid( $value ) ? $valid : __( 'Paste the ID exactly as it appears in the Seeker embed code, for example 348a4634-7c41-417e-ab1e-c7b0da2de235.', 'ltt-dive-in' );
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_event_submission_calendar_id', 'ltt_dive_in_validate_event_submission_id', 10, 2 );
add_filter( 'acf/validate_value/key=field_ltt_dive_in_event_submission_form_id', 'ltt_dive_in_validate_event_submission_id', 10, 2 );

/**
 * Return the Event Submission page settings.
 *
 * @param int $post_id Page ID.
 * @return array{title: string, text: string, calendar_id: string, form_id: string}
 */
function ltt_dive_in_get_event_submission_settings( $post_id ) {
	$post_id = absint( $post_id );
	$get     = static function ( $name ) use ( $post_id ) {
		$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : '';

		return is_string( $value ) ? trim( $value ) : '';
	};

	return array(
		'title'       => get_the_title( $post_id ),
		'text'        => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '',
		'calendar_id' => ltt_dive_in_sanitize_seeker_uuid( $get( 'ltt_dive_in_event_submission_calendar_id' ) ),
		'form_id'     => ltt_dive_in_sanitize_seeker_uuid( $get( 'ltt_dive_in_event_submission_form_id' ) ),
	);
}

/**
 * Enqueue the Event Submission template assets.
 *
 * templates/event-submission.php → templates/event-submission.css (with the
 * Current Conditions hero component) and event-submission.js, plus the Seeker
 * widget script when the form IDs are set.
 * The widget styles in assets/css/embeds/seeker-submission-form.css are passed
 * to the widget, not loaded as a page stylesheet.
 */
function ltt_dive_in_enqueue_event_submission_assets() {
	$version  = static function ( $relative ) {
		$path = LTT_DIVE_IN_DIR . $relative;

		return file_exists( $path ) ? (string) filemtime( $path ) : LTT_DIVE_IN_VERSION;
	};
	$settings = ltt_dive_in_get_event_submission_settings( get_queried_object_id() );

	wp_enqueue_style(
		'ltt-dive-in-event-submission',
		LTT_DIVE_IN_URI . '/assets/css/templates/event-submission.css',
		array( 'ltt-dive-in-page', ltt_dive_in_register_current_conditions_hero_style() ),
		$version( '/assets/css/templates/event-submission.css' )
	);

	if ( ! $settings['calendar_id'] || ! $settings['form_id'] ) {
		return;
	}

	// Seeker hosts the widget and does not version its URL.
	wp_enqueue_script( 'ltt-dive-in-seeker-widget', LTT_DIVE_IN_SEEKER_WIDGET_SCRIPT, array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_script(
		'ltt-dive-in-event-submission',
		LTT_DIVE_IN_URI . '/assets/js/templates/event-submission.js',
		array( 'ltt-dive-in-seeker-widget' ),
		$version( '/assets/js/templates/event-submission.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$css_path = LTT_DIVE_IN_DIR . '/assets/css/embeds/seeker-submission-form.css';

	wp_localize_script(
		'ltt-dive-in-event-submission',
		'ltt_dive_in_event_submission',
		array(
			'customCss' => file_exists( $css_path ) ? (string) file_get_contents( $css_path ) : '', // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
		)
	);
}
