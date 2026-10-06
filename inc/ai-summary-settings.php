<?php
/**
 * AI Summaries settings page and post summary fields.
 *
 * Both groups are PHP-registered: the API key handling and the editor
 * controls depend on prepare/update hooks that Local JSON cannot express.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the AI Summaries settings page and the post summary field group.
 */
function ltt_dive_in_register_ai_summary_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'AI Summaries Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'AI Summaries', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-ai-summaries-settings',
			'parent_slug' => 'options-general.php',
			'capability'  => 'manage_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_ai_summaries_settings',
			'title'    => __( 'LTT Dive In — Gemini AI Summaries', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'   => 'field_ltt_dive_in_gemini_status',
					'label' => __( 'Connection status', 'ltt-dive-in' ),
					'name'  => '',
					'type'  => 'message',
				),
				array(
					'key'          => 'field_ltt_dive_in_gemini_api_key',
					'label'        => __( 'Gemini API key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_gemini_api_key',
					'type'         => 'password',
					'instructions' => __( 'Create a key in Google AI Studio under API Keys. The key is used only by the server when an editor generates a summary and is never included in page markup or JavaScript. A saved key is not displayed again; enter a new key to replace it.', 'ltt-dive-in' ),
				),
				array(
					'key'          => 'field_ltt_dive_in_gemini_remove_api_key',
					'label'        => __( 'Remove saved key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_gemini_remove_api_key',
					'type'         => 'true_false',
					'instructions' => __( 'Delete the saved key when these settings are saved. Existing summaries stay on their posts; new ones cannot be generated until a key is added.', 'ltt-dive-in' ),
					'ui'           => 1,
				),
				array(
					'key'           => 'field_ltt_dive_in_gemini_model',
					'label'         => __( 'Gemini model', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_gemini_model',
					'type'          => 'text',
					'instructions'  => __( 'Model code from the Gemini API documentation. The default alias always points to the current Flash model. If it is overloaded, the current Flash-Lite model is tried once.', 'ltt-dive-in' ),
					'required'      => 1,
					'default_value' => 'gemini-flash-latest',
					'maxlength'     => 64,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-ai-summaries-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_ai_summary',
			'title'                 => __( 'AI Summary', 'ltt-dive-in' ),
			'fields'                => array(
				array(
					'key'       => 'field_ltt_dive_in_ai_summary_tools',
					'label'     => __( 'Generate', 'ltt-dive-in' ),
					'name'      => '',
					'type'      => 'message',
					'message'   => '<div class="ltt-dive-in-ai-summary-tools"></div>',
					'new_lines' => '',
				),
				array(
					'key'          => 'field_ltt_dive_in_ai_summary',
					'label'        => __( 'Summary', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_ai_summary',
					'type'         => 'textarea',
					'instructions' => __( 'Readers open it with “Show Summary” in the post header. Check every fact against the post before publishing. Leave empty to hide the control.', 'ltt-dive-in' ),
					'rows'         => 4,
					'maxlength'    => 650,
					'new_lines'    => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
			'description'           => __( 'AI-assisted summary readers can expand in the post header.', 'ltt-dive-in' ),
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_ai_summary_settings' );

/**
 * Show the configuration source and latest request outcome on the settings page.
 *
 * @param array $field Message field.
 * @return array
 */
function ltt_dive_in_prepare_gemini_status_field( $field ) {
	$status   = get_option( 'ltt_dive_in_gemini_last_status', array() );
	$messages = array(
		'ok'               => __( 'Connected. The latest request to Gemini succeeded.', 'ltt-dive-in' ),
		'unauthorized'     => __( 'Gemini rejected the API key. Check that it is correct and that the Generative Language API is enabled for it.', 'ltt-dive-in' ),
		'rate_limited'     => __( 'The Gemini usage limit was reached. Check the project quota or billing in Google AI Studio.', 'ltt-dive-in' ),
		'unavailable'      => __( 'Gemini was overloaded during the latest request. This is usually temporary.', 'ltt-dive-in' ),
		'model_not_found'  => __( 'Gemini does not recognize the selected model. Check the model code.', 'ltt-dive-in' ),
		'request_failed'   => __( 'Gemini could not be reached.', 'ltt-dive-in' ),
		'invalid_response' => __( 'Gemini returned a response that could not be read.', 'ltt-dive-in' ),
	);
	$lines    = array();

	if ( ltt_dive_in_gemini_api_key_is_constant() ) {
		$lines[] = __( 'The API key is defined by LTT_DIVE_IN_GEMINI_API_KEY in wp-config.php and cannot be changed here.', 'ltt-dive-in' );
	} elseif ( '' !== (string) get_option( 'options_ltt_dive_in_gemini_api_key', '' ) ) {
		$lines[] = __( 'An API key is saved.', 'ltt-dive-in' );
	} elseif ( '' !== ltt_dive_in_get_gemini_api_key() ) {
		$lines[] = __( 'No API key is saved here; a key is supplied by the ltt_dive_in_gemini_api_key filter.', 'ltt-dive-in' );
	} else {
		$lines[] = __( 'No API key is saved. Editors cannot generate summaries until a key is added.', 'ltt-dive-in' );
	}

	if ( is_array( $status ) && ! empty( $status['status'] ) && '' !== ltt_dive_in_get_gemini_api_key() ) {
		$code = (string) $status['status'];
		/* translators: %s: error code. */
		$message = $messages[ $code ] ?? sprintf( __( 'The latest request failed (%s).', 'ltt-dive-in' ), $code );

		if ( ! empty( $status['time'] ) ) {
			/* translators: 1: status message, 2: date and time. */
			$message = sprintf( __( '%1$s Last checked %2$s.', 'ltt-dive-in' ), $message, wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $status['time'] ) );
		}

		$lines[] = $message;
	}

	$field['message'] = implode( "\n\n", array_map( 'esc_html', $lines ) );

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_gemini_status', 'ltt_dive_in_prepare_gemini_status_field' );

/**
 * Never print the saved key back into the settings form.
 *
 * @param array|false $field API key field.
 * @return array|false
 */
function ltt_dive_in_prepare_gemini_api_key_field( $field ) {
	if ( ! is_array( $field ) || ltt_dive_in_gemini_api_key_is_constant() ) {
		return false;
	}

	if ( '' !== (string) $field['value'] ) {
		$field['placeholder'] = __( 'Saved — enter a new key to replace it', 'ltt-dive-in' );
	}

	$field['value'] = '';

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_gemini_api_key', 'ltt_dive_in_prepare_gemini_api_key_field' );

/**
 * Hide the removal toggle when there is no saved key to remove.
 *
 * @param array|false $field Removal field.
 * @return array|false
 */
function ltt_dive_in_prepare_gemini_remove_api_key_field( $field ) {
	if ( ltt_dive_in_gemini_api_key_is_constant() || '' === (string) get_option( 'options_ltt_dive_in_gemini_api_key', '' ) ) {
		return false;
	}

	if ( is_array( $field ) ) {
		$field['value'] = 0;
	}

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_gemini_remove_api_key', 'ltt_dive_in_prepare_gemini_remove_api_key_field' );

/**
 * Keep the saved key when the empty field is submitted, and apply removal.
 *
 * @param mixed      $value   Submitted key.
 * @param string|int $post_id ACF post ID.
 * @return string
 */
function ltt_dive_in_update_gemini_api_key( $value, $post_id ) {
	$saved  = (string) get_option( 'options_ltt_dive_in_gemini_api_key', '' );
	$value  = trim( sanitize_text_field( (string) $value ) );
	$remove = isset( $_POST['acf']['field_ltt_dive_in_gemini_remove_api_key'] ) && '1' === $_POST['acf']['field_ltt_dive_in_gemini_remove_api_key']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- ACF verifies the options-page nonce before saving.

	if ( 'options' !== $post_id || ! current_user_can( 'manage_options' ) ) {
		return $saved;
	}

	if ( $remove ) {
		$value = '';
	} elseif ( '' === $value ) {
		return $saved;
	}

	if ( $value !== $saved ) {
		delete_option( 'ltt_dive_in_gemini_last_status' );
	}

	return $value;
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_gemini_api_key', 'ltt_dive_in_update_gemini_api_key', 10, 2 );

/**
 * Do not store the one-time removal toggle.
 *
 * @return int
 */
function ltt_dive_in_reset_gemini_remove_api_key() {
	return 0;
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_gemini_remove_api_key', 'ltt_dive_in_reset_gemini_remove_api_key' );

/**
 * Accept only well-formed model codes.
 *
 * @param bool|string $valid Current validation result.
 * @param mixed       $value Submitted model code.
 * @return bool|string
 */
function ltt_dive_in_validate_gemini_model( $valid, $value ) {
	if ( true === $valid && ! preg_match( '/^[a-z0-9][a-z0-9.\-]*$/', (string) $value ) ) {
		return __( 'Use a model code such as gemini-flash-latest: lowercase letters, numbers, dots, and hyphens only.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_gemini_model', 'ltt_dive_in_validate_gemini_model', 10, 2 );

/**
 * Keep the saved key out of autoloaded options; it is only needed when generating.
 *
 * @param string $option Option name.
 * @return void
 */
function ltt_dive_in_disable_gemini_key_autoload( $option ) {
	if ( 'options_ltt_dive_in_gemini_api_key' === $option && function_exists( 'wp_set_option_autoload' ) ) {
		wp_set_option_autoload( $option, false );
	}
}
add_action( 'added_option', 'ltt_dive_in_disable_gemini_key_autoload' );
add_action( 'updated_option', 'ltt_dive_in_disable_gemini_key_autoload' );
