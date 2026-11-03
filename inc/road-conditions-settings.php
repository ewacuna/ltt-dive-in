<?php
/**
 * Road Conditions settings page.
 *
 * PHP-registered: the API key handling depends on prepare/update hooks that
 * Local JSON cannot express. California (Caltrans CWWP2) feeds need no key;
 * only Nevada 511 requires a developer key.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the Nevada 511 developer key used for server-side requests.
 *
 * A `LTT_DIVE_IN_NV511_API_KEY` constant in wp-config.php takes precedence
 * over the key saved in Settings → Road Conditions.
 *
 * @return string
 */
function ltt_dive_in_get_nv511_api_key() {
	if ( ltt_dive_in_nv511_api_key_is_constant() ) {
		$key = LTT_DIVE_IN_NV511_API_KEY;
	} else {
		$key = get_option( 'options_ltt_dive_in_nv511_api_key', '' );
	}

	/**
	 * Filter the Nevada 511 developer key used for server-side requests.
	 *
	 * @param string $key Nevada 511 developer key.
	 */
	$key = apply_filters( 'ltt_dive_in_nv511_api_key', is_string( $key ) ? trim( $key ) : '' );

	return is_string( $key ) ? trim( $key ) : '';
}

/**
 * Whether the Nevada 511 key is supplied by wp-config.php.
 *
 * @return bool
 */
function ltt_dive_in_nv511_api_key_is_constant() {
	return defined( 'LTT_DIVE_IN_NV511_API_KEY' ) && is_string( LTT_DIVE_IN_NV511_API_KEY ) && '' !== trim( LTT_DIVE_IN_NV511_API_KEY );
}

/**
 * Register the Road Conditions settings page and its field group.
 */
function ltt_dive_in_register_road_conditions_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Road Conditions Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Road Conditions', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-road-conditions-settings',
			'parent_slug' => 'options-general.php',
			'capability'  => 'manage_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_road_conditions_settings',
			'title'    => __( 'LTT Dive In — Nevada 511', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'   => 'field_ltt_dive_in_nv511_status',
					'label' => __( 'Connection status', 'ltt-dive-in' ),
					'name'  => '',
					'type'  => 'message',
				),
				array(
					'key'          => 'field_ltt_dive_in_nv511_api_key',
					'label'        => __( 'Nevada 511 developer key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_nv511_api_key',
					'type'         => 'password',
					'instructions' => __( 'Sign in to nvroads.com and request a developer key on its Developers page. The key is used only by the server to load Nevada road conditions and is never included in page markup or JavaScript. California data comes from public Caltrans feeds and needs no key. A saved key is not displayed again; enter a new key to replace it.', 'ltt-dive-in' ),
				),
				array(
					'key'          => 'field_ltt_dive_in_nv511_remove_api_key',
					'label'        => __( 'Remove saved key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_nv511_remove_api_key',
					'type'         => 'true_false',
					'instructions' => __( 'Delete the saved key when these settings are saved. The Nevada tab shows an unavailable message until a key is added.', 'ltt-dive-in' ),
					'ui'           => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-road-conditions-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_road_conditions_settings' );

/**
 * Show the configuration source and latest Nevada 511 request outcome.
 *
 * The status option is written by the Nevada 511 client after each request.
 *
 * @param array $field Message field.
 * @return array
 */
function ltt_dive_in_prepare_nv511_status_field( $field ) {
	$status   = get_option( 'ltt_dive_in_nv511_last_status', array() );
	$messages = array(
		'ok'               => __( 'Connected. The latest request to Nevada 511 succeeded.', 'ltt-dive-in' ),
		'unauthorized'     => __( 'Nevada 511 rejected the developer key. Check that it is correct and active.', 'ltt-dive-in' ),
		'rate_limited'     => __( 'Nevada 511 throttled the request (limit: ten calls per minute). Cached data is shown meanwhile.', 'ltt-dive-in' ),
		'request_failed'   => __( 'Nevada 511 could not be reached.', 'ltt-dive-in' ),
		'invalid_response' => __( 'Nevada 511 returned a response that could not be read.', 'ltt-dive-in' ),
	);
	$lines    = array();

	if ( ltt_dive_in_nv511_api_key_is_constant() ) {
		$lines[] = __( 'The developer key is defined by LTT_DIVE_IN_NV511_API_KEY in wp-config.php and cannot be changed here.', 'ltt-dive-in' );
	} elseif ( '' !== (string) get_option( 'options_ltt_dive_in_nv511_api_key', '' ) ) {
		$lines[] = __( 'A developer key is saved.', 'ltt-dive-in' );
	} elseif ( '' !== ltt_dive_in_get_nv511_api_key() ) {
		$lines[] = __( 'No developer key is saved here; a key is supplied by the ltt_dive_in_nv511_api_key filter.', 'ltt-dive-in' );
	} else {
		$lines[] = __( 'No developer key is saved. Nevada road conditions cannot load until a key is added.', 'ltt-dive-in' );
	}

	if ( is_array( $status ) && ! empty( $status['status'] ) && '' !== ltt_dive_in_get_nv511_api_key() ) {
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
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_nv511_status', 'ltt_dive_in_prepare_nv511_status_field' );

/**
 * Never print the saved key back into the settings form.
 *
 * @param array|false $field API key field.
 * @return array|false
 */
function ltt_dive_in_prepare_nv511_api_key_field( $field ) {
	if ( ! is_array( $field ) || ltt_dive_in_nv511_api_key_is_constant() ) {
		return false;
	}

	if ( '' !== (string) $field['value'] ) {
		$field['placeholder'] = __( 'Saved — enter a new key to replace it', 'ltt-dive-in' );
	}

	$field['value'] = '';

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_nv511_api_key', 'ltt_dive_in_prepare_nv511_api_key_field' );

/**
 * Hide the removal toggle when there is no saved key to remove.
 *
 * @param array|false $field Removal field.
 * @return array|false
 */
function ltt_dive_in_prepare_nv511_remove_api_key_field( $field ) {
	if ( ltt_dive_in_nv511_api_key_is_constant() || '' === (string) get_option( 'options_ltt_dive_in_nv511_api_key', '' ) ) {
		return false;
	}

	if ( is_array( $field ) ) {
		$field['value'] = 0;
	}

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_nv511_remove_api_key', 'ltt_dive_in_prepare_nv511_remove_api_key_field' );

/**
 * Keep the saved key when the empty field is submitted, and apply removal.
 *
 * @param mixed      $value   Submitted key.
 * @param string|int $post_id ACF post ID.
 * @return string
 */
function ltt_dive_in_update_nv511_api_key( $value, $post_id ) {
	$saved  = (string) get_option( 'options_ltt_dive_in_nv511_api_key', '' );
	$value  = trim( sanitize_text_field( (string) $value ) );
	$remove = isset( $_POST['acf']['field_ltt_dive_in_nv511_remove_api_key'] ) && '1' === $_POST['acf']['field_ltt_dive_in_nv511_remove_api_key']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- ACF verifies the options-page nonce before saving.

	if ( 'options' !== $post_id || ! current_user_can( 'manage_options' ) ) {
		return $saved;
	}

	if ( $remove ) {
		$value = '';
	} elseif ( '' === $value ) {
		return $saved;
	}

	if ( $value !== $saved ) {
		delete_option( 'ltt_dive_in_nv511_last_status' );

		/**
		 * Fires when the saved Nevada 511 key changes, so cached Nevada data can be discarded.
		 */
		do_action( 'ltt_dive_in_nv511_api_key_changed' );
	}

	return $value;
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_nv511_api_key', 'ltt_dive_in_update_nv511_api_key', 10, 2 );

/**
 * Do not store the one-time removal toggle.
 *
 * @return int
 */
function ltt_dive_in_reset_nv511_remove_api_key() {
	return 0;
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_nv511_remove_api_key', 'ltt_dive_in_reset_nv511_remove_api_key' );

/**
 * Keep the saved key out of autoloaded options; it is only needed when refreshing data.
 *
 * @param string $option Option name.
 * @return void
 */
function ltt_dive_in_disable_nv511_key_autoload( $option ) {
	if ( 'options_ltt_dive_in_nv511_api_key' === $option && function_exists( 'wp_set_option_autoload' ) ) {
		wp_set_option_autoload( $option, false );
	}
}
add_action( 'added_option', 'ltt_dive_in_disable_nv511_key_autoload' );
add_action( 'updated_option', 'ltt_dive_in_disable_nv511_key_autoload' );
