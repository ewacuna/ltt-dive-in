<?php
/**
 * Seeker settings page and Event Driver block fields.
 *
 * Both groups are PHP-registered: the category and event choices are loaded
 * from the Seeker API at runtime and cannot be represented in Local JSON.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the approved number of events for each Event Driver variant.
 *
 * @return array<string, array{min:int, max:int, default:int}>
 */
function ltt_dive_in_get_event_driver_limits() {
	return array(
		'hero'      => array( 'min' => 3, 'max' => 6, 'default' => 3 ),
		'listed'    => array( 'min' => 3, 'max' => 12, 'default' => 9 ),
		'load_more' => array( 'min' => 3, 'max' => 12, 'default' => 6 ),
		'carousel'  => array( 'min' => 4, 'max' => 12, 'default' => 8 ),
	);
}

/**
 * Register the Events settings page and the Event Driver field groups.
 */
function ltt_dive_in_register_event_driver_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$limits = ltt_dive_in_get_event_driver_limits();

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Events Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Events', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-events-settings',
			'parent_slug' => 'options-general.php',
			'capability'  => 'manage_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_events_settings',
			'title'    => __( 'LTT Dive In — Seeker Events', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'   => 'field_ltt_dive_in_seeker_status',
					'label' => __( 'Connection status', 'ltt-dive-in' ),
					'name'  => '',
					'type'  => 'message',
				),
				array(
					'key'          => 'field_ltt_dive_in_seeker_api_key',
					'label'        => __( 'Seeker API key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_seeker_api_key',
					'type'         => 'password',
					'instructions' => __( 'Create a key in your Seeker dashboard under API Keys. The key is used only by the server and is never included in page markup or JavaScript. A saved key is not displayed again; enter a new key to replace it.', 'ltt-dive-in' ),
				),
				array(
					'key'          => 'field_ltt_dive_in_seeker_remove_api_key',
					'label'        => __( 'Remove saved key', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_seeker_remove_api_key',
					'type'         => 'true_false',
					'instructions' => __( 'Delete the saved key when these settings are saved. Event blocks stop rendering until a new key is added.', 'ltt-dive-in' ),
					'ui'           => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-events-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);

	$variant_is = static function ( $variants ) {
		$rules = array();

		foreach ( (array) $variants as $variant ) {
			$rules[] = array(
				array(
					'field'    => 'field_ltt_dive_in_event_driver_variant',
					'operator' => '==',
					'value'    => $variant,
				),
			);
		}

		return $rules;
	};
	$count_field = static function ( $key, $label, $instructions, $limit, $variants ) use ( $variant_is ) {
		return array(
			'key'               => 'field_ltt_dive_in_event_driver_' . $key,
			'label'             => $label,
			'name'              => 'ltt_dive_in_event_driver_' . $key,
			'type'              => 'number',
			'instructions'      => $instructions,
			'required'          => 1,
			'default_value'     => $limit['default'],
			'min'               => $limit['min'],
			'max'               => $limit['max'],
			'step'              => 1,
			'conditional_logic' => $variant_is( $variants ),
		);
	};

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_event_driver',
			'title'                 => __( 'LTT Dive In — Event Driver Block', 'ltt-dive-in' ),
			'fields'                => array(
				array(
					'key'           => 'field_ltt_dive_in_event_driver_variant',
					'label'         => __( 'Variant', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_event_driver_variant',
					'type'          => 'select',
					'instructions'  => __( 'Events come from the Seeker calendar and are not edited here.', 'ltt-dive-in' ),
					'required'      => 1,
					'choices'       => array(
						'hero'     => __( 'Hero Featured Events', 'ltt-dive-in' ),
						'listed'   => __( 'Listed Events', 'ltt-dive-in' ),
						'carousel' => __( 'Events Carousel', 'ltt-dive-in' ),
					),
					'default_value' => 'hero',
					'allow_null'    => 0,
					'ui'            => 1,
					'return_format' => 'value',
				),

				/* Hero Featured Events. */
				array(
					'key'               => 'field_ltt_dive_in_event_driver_hero_source',
					'label'             => __( 'Featured events', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_hero_source',
					'type'              => 'button_group',
					'instructions'      => __( 'Use the events flagged as featured in Seeker, or choose specific events.', 'ltt-dive-in' ),
					'choices'           => array(
						'featured' => __( 'Featured in Seeker', 'ltt-dive-in' ),
						'manual'   => __( 'Choose events', 'ltt-dive-in' ),
					),
					'default_value'     => 'featured',
					'return_format'     => 'value',
					'conditional_logic' => $variant_is( 'hero' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_hero_count',
					'label'             => __( 'Number of slides', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_hero_count',
					'type'              => 'number',
					'instructions'      => __( 'Between 3 and 6 upcoming featured events, soonest first.', 'ltt-dive-in' ),
					'required'          => 1,
					'default_value'     => $limits['hero']['default'],
					'min'               => $limits['hero']['min'],
					'max'               => $limits['hero']['max'],
					'step'              => 1,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_event_driver_variant',
								'operator' => '==',
								'value'    => 'hero',
							),
							array(
								'field'    => 'field_ltt_dive_in_event_driver_hero_source',
								'operator' => '!=',
								'value'    => 'manual',
							),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_hero_events',
					'label'             => __( 'Events', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_hero_events',
					'type'              => 'select',
					'instructions'      => __( 'Search upcoming Seeker events by name and choose 3 to 6, in display order. Events that have ended are skipped automatically.', 'ltt-dive-in' ),
					'required'          => 1,
					'choices'           => array(),
					'multiple'          => 1,
					'ui'                => 1,
					'ajax'              => 1,
					'allow_null'        => 0,
					'return_format'     => 'value',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_event_driver_variant',
								'operator' => '==',
								'value'    => 'hero',
							),
							array(
								'field'    => 'field_ltt_dive_in_event_driver_hero_source',
								'operator' => '==',
								'value'    => 'manual',
							),
						),
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_primary_label',
					'label'             => __( 'Tickets button label', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_primary_label',
					'type'              => 'text',
					'instructions'      => __( 'Links to the event’s ticket page. Hidden for events without a ticket link.', 'ltt-dive-in' ),
					'default_value'     => __( 'Get Tickets', 'ltt-dive-in' ),
					'maxlength'         => 24,
					'conditional_logic' => $variant_is( 'hero' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_secondary_label',
					'label'             => __( 'Details button label', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_secondary_label',
					'type'              => 'text',
					'instructions'      => __( 'Links to the event page. Hidden for events without an event link.', 'ltt-dive-in' ),
					'default_value'     => __( 'Learn More', 'ltt-dive-in' ),
					'maxlength'         => 24,
					'conditional_logic' => $variant_is( 'hero' ),
				),

				/* Section header shared by Listed Events and Events Carousel. */
				array(
					'key'               => 'field_ltt_dive_in_event_driver_tag',
					'label'             => __( 'Category tag', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_tag',
					'type'              => 'text',
					'instructions'      => __( 'Optional short label shown above the heading.', 'ltt-dive-in' ),
					'maxlength'         => 40,
					'conditional_logic' => $variant_is( array( 'listed', 'carousel' ) ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_heading',
					'label'             => __( 'Heading', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_heading',
					'type'              => 'text',
					'instructions'      => __( 'Required section heading.', 'ltt-dive-in' ),
					'required'          => 1,
					'maxlength'         => 90,
					'conditional_logic' => $variant_is( array( 'listed', 'carousel' ) ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_intro',
					'label'             => __( 'Introduction', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_intro',
					'type'              => 'textarea',
					'instructions'      => __( 'Optional introduction of up to three sentences.', 'ltt-dive-in' ),
					'rows'              => 3,
					'new_lines'         => '',
					'maxlength'         => 320,
					'conditional_logic' => $variant_is( array( 'listed', 'carousel' ) ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_primary_link',
					'label'             => __( 'Primary link', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_primary_link',
					'type'              => 'link',
					'instructions'      => __( 'Optional. Requires a meaningful label and destination.', 'ltt-dive-in' ),
					'return_format'     => 'array',
					'conditional_logic' => $variant_is( array( 'listed', 'carousel' ) ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_secondary_link',
					'label'             => __( 'Secondary link', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_secondary_link',
					'type'              => 'link',
					'instructions'      => __( 'Optional. Requires a meaningful label and destination.', 'ltt-dive-in' ),
					'return_format'     => 'array',
					'conditional_logic' => $variant_is( array( 'listed', 'carousel' ) ),
				),

				/* Listed Events. */
				$count_field( 'initial_count', __( 'Initial events', 'ltt-dive-in' ), __( 'Number of events shown before Load More. Multiples of 3 fill complete desktop rows.', 'ltt-dive-in' ), $limits['listed'], 'listed' ),
				$count_field( 'load_more_count', __( 'Load More step', 'ltt-dive-in' ), __( 'Number of events added each time a visitor presses Load More.', 'ltt-dive-in' ), $limits['load_more'], 'listed' ),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_filter_categories',
					'label'             => __( 'Category filter options', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_filter_categories',
					'type'              => 'select',
					'instructions'      => __( 'Seeker categories offered in the Categories dropdown. Leave empty to offer every category, or turn the dropdown off below.', 'ltt-dive-in' ),
					'choices'           => array(),
					'multiple'          => 1,
					'ui'                => 1,
					'allow_null'        => 1,
					'return_format'     => 'value',
					'conditional_logic' => $variant_is( 'listed' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_show_category_filter',
					'label'             => __( 'Show Categories dropdown', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_show_category_filter',
					'type'              => 'true_false',
					'default_value'     => 1,
					'ui'                => 1,
					'conditional_logic' => $variant_is( 'listed' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_show_date_filter',
					'label'             => __( 'Show date dropdown', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_show_date_filter',
					'type'              => 'true_false',
					'default_value'     => 1,
					'ui'                => 1,
					'conditional_logic' => $variant_is( 'listed' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_show_search',
					'label'             => __( 'Show keyword search', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_show_search',
					'type'              => 'true_false',
					'default_value'     => 1,
					'ui'                => 1,
					'conditional_logic' => $variant_is( 'listed' ),
				),

				/* Events Carousel. */
				$count_field( 'carousel_count', __( 'Number of events', 'ltt-dive-in' ), __( 'Between 4 and 12 upcoming events.', 'ltt-dive-in' ), $limits['carousel'], 'carousel' ),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_carousel_sort',
					'label'             => __( 'Sort', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_carousel_sort',
					'type'              => 'select',
					'choices'           => array(
						'soonest' => __( 'Soonest first', 'ltt-dive-in' ),
						'recent'  => __( 'Recently added', 'ltt-dive-in' ),
					),
					'default_value'     => 'soonest',
					'ui'                => 1,
					'return_format'     => 'value',
					'conditional_logic' => $variant_is( 'carousel' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_carousel_featured',
					'label'             => __( 'Featured events only', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_carousel_featured',
					'type'              => 'true_false',
					'default_value'     => 0,
					'ui'                => 1,
					'conditional_logic' => $variant_is( 'carousel' ),
				),
				array(
					'key'               => 'field_ltt_dive_in_event_driver_carousel_categories',
					'label'             => __( 'Limit to categories', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_event_driver_carousel_categories',
					'type'              => 'select',
					'instructions'      => __( 'Optional. Show only events in these Seeker categories.', 'ltt-dive-in' ),
					'choices'           => array(),
					'multiple'          => 1,
					'ui'                => 1,
					'allow_null'        => 1,
					'return_format'     => 'value',
					'conditional_logic' => $variant_is( 'carousel' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'ltt-dive-in/event-driver',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
			'description'           => __( 'Hero, listed, and carousel presentations of upcoming Seeker events.', 'ltt-dive-in' ),
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_event_driver_settings' );

/**
 * Show the configuration source and latest request outcome on the settings page.
 *
 * @param array $field Message field.
 * @return array
 */
function ltt_dive_in_prepare_seeker_status_field( $field ) {
	$status   = get_option( 'ltt_dive_in_seeker_last_status', array() );
	$messages = array(
		'ok'               => __( 'Connected. The latest request to Seeker succeeded.', 'ltt-dive-in' ),
		'unauthorized'     => __( 'Seeker rejected the API key. Check that it is correct, active, and allowed to read events.', 'ltt-dive-in' ),
		'rate_limited'     => __( 'The hourly Seeker request limit was reached. Cached events are shown until the limit resets.', 'ltt-dive-in' ),
		'request_failed'   => __( 'Seeker could not be reached.', 'ltt-dive-in' ),
		'invalid_response' => __( 'Seeker returned a response that could not be read.', 'ltt-dive-in' ),
	);
	$lines    = array();

	if ( ltt_dive_in_seeker_api_key_is_constant() ) {
		$lines[] = __( 'The API key is defined by LTT_DIVE_IN_SEEKER_API_KEY in wp-config.php and cannot be changed here.', 'ltt-dive-in' );
	} elseif ( '' !== (string) get_option( 'options_ltt_dive_in_seeker_api_key', '' ) ) {
		$lines[] = __( 'An API key is saved.', 'ltt-dive-in' );
	} elseif ( '' !== ltt_dive_in_get_seeker_api_key() ) {
		$lines[] = __( 'No API key is saved here; a key is supplied by the ltt_dive_in_seeker_api_key filter.', 'ltt-dive-in' );
	} else {
		$lines[] = __( 'No API key is saved. Event blocks do not render until a key is added.', 'ltt-dive-in' );
	}

	if ( is_array( $status ) && ! empty( $status['status'] ) && '' !== ltt_dive_in_get_seeker_api_key() ) {
		$code    = (string) $status['status'];
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
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_seeker_status', 'ltt_dive_in_prepare_seeker_status_field' );

/**
 * Never print the saved key back into the settings form.
 *
 * @param array|false $field API key field.
 * @return array|false
 */
function ltt_dive_in_prepare_seeker_api_key_field( $field ) {
	if ( ! is_array( $field ) || ltt_dive_in_seeker_api_key_is_constant() ) {
		return false;
	}

	if ( '' !== (string) $field['value'] ) {
		$field['placeholder'] = __( 'Saved — enter a new key to replace it', 'ltt-dive-in' );
	}

	$field['value'] = '';

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_seeker_api_key', 'ltt_dive_in_prepare_seeker_api_key_field' );

/**
 * Hide the removal toggle when there is no saved key to remove.
 *
 * @param array|false $field Removal field.
 * @return array|false
 */
function ltt_dive_in_prepare_seeker_remove_api_key_field( $field ) {
	if ( ltt_dive_in_seeker_api_key_is_constant() || '' === (string) get_option( 'options_ltt_dive_in_seeker_api_key', '' ) ) {
		return false;
	}

	if ( is_array( $field ) ) {
		$field['value'] = 0;
	}

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_seeker_remove_api_key', 'ltt_dive_in_prepare_seeker_remove_api_key_field' );

/**
 * Keep the saved key when the empty field is submitted, and apply removal.
 *
 * @param mixed      $value   Submitted key.
 * @param string|int $post_id ACF post ID.
 * @return string
 */
function ltt_dive_in_update_seeker_api_key( $value, $post_id ) {
	$saved  = (string) get_option( 'options_ltt_dive_in_seeker_api_key', '' );
	$value  = trim( sanitize_text_field( (string) $value ) );
	$remove = isset( $_POST['acf']['field_ltt_dive_in_seeker_remove_api_key'] ) && '1' === $_POST['acf']['field_ltt_dive_in_seeker_remove_api_key']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- ACF verifies the options-page nonce before saving.

	if ( 'options' !== $post_id || ! current_user_can( 'manage_options' ) ) {
		return $saved;
	}

	if ( $remove ) {
		$value = '';
	} elseif ( '' === $value ) {
		return $saved;
	}

	if ( $value !== $saved ) {
		ltt_dive_in_flush_seeker_cache();
		delete_option( 'ltt_dive_in_seeker_last_status' );
	}

	return $value;
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_seeker_api_key', 'ltt_dive_in_update_seeker_api_key', 10, 2 );

/**
 * Do not store the one-time removal toggle.
 *
 * @return int
 */
function ltt_dive_in_reset_seeker_remove_api_key() {
	return 0;
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_seeker_remove_api_key', 'ltt_dive_in_reset_seeker_remove_api_key' );

/**
 * Keep the saved key out of autoloaded options; it is only needed on cache misses.
 *
 * @param string $option Option name.
 * @return void
 */
function ltt_dive_in_disable_seeker_key_autoload( $option ) {
	if ( 'options_ltt_dive_in_seeker_api_key' === $option && function_exists( 'wp_set_option_autoload' ) ) {
		wp_set_option_autoload( $option, false );
	}
}
add_action( 'added_option', 'ltt_dive_in_disable_seeker_key_autoload' );
add_action( 'updated_option', 'ltt_dive_in_disable_seeker_key_autoload' );

/**
 * Load Seeker categories into the category select fields.
 *
 * @param array $field Select field.
 * @return array
 */
function ltt_dive_in_load_event_driver_category_choices( $field ) {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		$field['choices'] = ltt_dive_in_get_seeker_categories();

		if ( ! $field['choices'] ) {
			$field['instructions'] .= ' ' . __( 'Categories appear here once a working Seeker API key is saved in Settings → Events.', 'ltt-dive-in' );
		}
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_event_driver_filter_categories', 'ltt_dive_in_load_event_driver_category_choices' );
add_filter( 'acf/load_field/key=field_ltt_dive_in_event_driver_carousel_categories', 'ltt_dive_in_load_event_driver_category_choices' );

/**
 * Format an event as an editor choice label.
 *
 * @param array $event Normalized event.
 * @return string
 */
function ltt_dive_in_get_event_choice_label( $event ) {
	/* translators: 1: event name, 2: event date. */
	return sprintf( __( '%1$s — %2$s', 'ltt-dive-in' ), $event['title'], wp_date( 'M j, Y', $event['start']->getTimestamp(), $event['start']->getTimezone() ) );
}

/**
 * Search upcoming Seeker events for the manual Hero selection.
 *
 * @param array $response Shortcut response.
 * @param array $options  AJAX query options.
 * @return array
 */
function ltt_dive_in_query_event_driver_hero_events( $response, $options ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return array( 'results' => array() );
	}

	$page   = max( 1, absint( $options['paged'] ?? 1 ) );
	$result = ltt_dive_in_get_seeker_events(
		array(
			'limit'  => 20,
			'offset' => ( $page - 1 ) * 20,
			'search' => sanitize_text_field( wp_unslash( (string) ( $options['s'] ?? '' ) ) ),
		)
	);

	if ( is_wp_error( $result ) ) {
		return array(
			'results' => array(
				array(
					'id'       => '',
					'text'     => __( 'Events could not be loaded. Check Settings → Events.', 'ltt-dive-in' ),
					'disabled' => true,
				),
			),
		);
	}

	$results = array();

	foreach ( $result['events'] as $event ) {
		$results[] = array(
			'id'   => $event['id'],
			'text' => ltt_dive_in_get_event_choice_label( $event ),
		);
	}

	return array(
		'results' => $results,
		'more'    => $page * 20 < $result['total'],
	);
}
add_filter( 'acf/fields/select/query/key=field_ltt_dive_in_event_driver_hero_events', 'ltt_dive_in_query_event_driver_hero_events', 10, 2 );

/**
 * Label already-selected Hero events when the editor form renders.
 *
 * @param array|false $field Select field.
 * @return array|false
 */
function ltt_dive_in_prepare_event_driver_hero_events( $field ) {
	if ( ! is_array( $field ) || empty( $field['value'] ) ) {
		return $field;
	}

	$choices = array();

	foreach ( (array) $field['value'] as $uuid ) {
		$choices[ (string) $uuid ] = (string) $uuid;
	}

	foreach ( ltt_dive_in_get_seeker_events_by_id( array_keys( $choices ) ) as $event ) {
		$choices[ $event['id'] ] = ltt_dive_in_get_event_choice_label( $event );
	}

	$field['choices'] = $choices;

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_event_driver_hero_events', 'ltt_dive_in_prepare_event_driver_hero_events' );

/**
 * Store only valid Seeker UUIDs for manual Hero events.
 *
 * @param mixed $value Submitted value.
 * @return string[]
 */
function ltt_dive_in_update_event_driver_hero_events( $value ) {
	return array_values( array_unique( array_filter( array_map( 'ltt_dive_in_sanitize_seeker_uuid', (array) $value ) ) ) );
}
add_filter( 'acf/update_value/key=field_ltt_dive_in_event_driver_hero_events', 'ltt_dive_in_update_event_driver_hero_events' );

/**
 * Require 3 to 6 manually selected Hero events.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function ltt_dive_in_validate_event_driver_hero_events( $valid, $value ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$limits = ltt_dive_in_get_event_driver_limits();
	$count  = count( array_filter( (array) $value ) );

	if ( $count && ( $count < $limits['hero']['min'] || $count > $limits['hero']['max'] ) ) {
		/* translators: 1: minimum events, 2: maximum events, 3: selected events. */
		return sprintf( __( 'Choose between %1$d and %2$d events. Currently selected: %3$d.', 'ltt-dive-in' ), $limits['hero']['min'], $limits['hero']['max'], $count );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_event_driver_hero_events', 'ltt_dive_in_validate_event_driver_hero_events', 10, 2 );
