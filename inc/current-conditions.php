<?php
/**
 * Current Conditions template helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a page uses the Current Conditions template.
 *
 * @param int $post_id Page ID. Defaults to the current queried page.
 * @return bool
 */
function ltt_dive_in_is_current_conditions_page( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_queried_object_id();

	return $post_id && 'templates/current-conditions.php' === get_page_template_slug( $post_id );
}

/**
 * Resolve the page that owns the shared Current Conditions hero.
 *
 * @param int $post_id Page ID. Defaults to the current queried page.
 * @return int Parent page ID for a tab, otherwise the page itself.
 */
function ltt_dive_in_get_current_conditions_hub_id( $post_id = 0 ) {
	$post_id   = $post_id ? absint( $post_id ) : get_queried_object_id();
	$parent_id = (int) wp_get_post_parent_id( $post_id );

	return $parent_id && ltt_dive_in_is_current_conditions_page( $parent_id ) ? $parent_id : $post_id;
}

/**
 * Whether a page is the Current Conditions parent that owns the shared hero.
 *
 * @param int $post_id Page ID.
 * @return bool
 */
function ltt_dive_in_is_current_conditions_hub( $post_id ) {
	$post_id = absint( $post_id );

	return ltt_dive_in_is_current_conditions_page( $post_id ) && ltt_dive_in_get_current_conditions_hub_id( $post_id ) === $post_id;
}

/**
 * Hide the Featured image panel on the Current Conditions parent page.
 */
function ltt_dive_in_enqueue_current_conditions_editor_assets() {
	if ( ! ltt_dive_in_is_current_conditions_hub( get_the_ID() ) ) {
		return;
	}

	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/current-conditions-editor.js';

	wp_enqueue_script(
		'ltt-dive-in-current-conditions-editor',
		LTT_DIVE_IN_URI . '/assets/js/admin/current-conditions-editor.js',
		array( 'wp-data', 'wp-editor', 'wp-edit-post' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_current_conditions_editor_assets' );

/**
 * Hide hero design fields that the compact Current Conditions hero does not use.
 *
 * @param array|false $field ACF field prepared for rendering.
 * @return array|false
 */
function ltt_dive_in_hide_current_conditions_hero_fields( $field ) {
	return ltt_dive_in_is_current_conditions_page( get_the_ID() ) ? false : $field;
}
foreach ( array(
	'field_ltt_dive_in_page_header_hero_variant',
	'field_ltt_dive_in_meetings_hero_title',
	'field_ltt_dive_in_meetings_hero_text',
	'field_ltt_dive_in_meetings_hero_icon',
) as $ltt_dive_in_hidden_field_key ) {
	add_filter( 'acf/prepare_field/key=' . $ltt_dive_in_hidden_field_key, 'ltt_dive_in_hide_current_conditions_hero_fields' );
}
unset( $ltt_dive_in_hidden_field_key );

/**
 * Register the live-module fields for Current Conditions tabs.
 */
function ltt_dive_in_register_current_conditions_tab_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$weather_only = array(
		array(
			array(
				'field'    => 'field_ltt_dive_in_conditions_tab_module',
				'operator' => '==',
				'value'    => 'weather',
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'          => 'group_ltt_dive_in_current_conditions_tab',
			'title'        => __( 'LTT Dive In — Current Conditions Tab', 'ltt-dive-in' ),
			'description'  => __( 'The featured image is the background behind the tab intro and its live module.', 'ltt-dive-in' ),
			'fields'       => array(
				array(
					'key'           => 'field_ltt_dive_in_conditions_tab_module',
					'label'         => __( 'Live module', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_conditions_tab_module',
					'type'          => 'select',
					'instructions'  => __( 'Shown below the tab intro.', 'ltt-dive-in' ),
					'choices'       => array(
						''        => __( 'None', 'ltt-dive-in' ),
						'weather' => __( 'Current weather', 'ltt-dive-in' ),
					),
					'default_value' => '',
					'return_format' => 'value',
				),
				array(
					'key'               => 'field_ltt_dive_in_weather_locations',
					'label'             => __( 'Weather locations', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_weather_locations',
					'type'              => 'repeater',
					'instructions'      => __( 'Each location becomes a city tile; selecting a tile shows its weather in the main panel. The first location is shown when the page loads. Drag rows to reorder.', 'ltt-dive-in' ),
					'required'          => 1,
					'min'               => 1,
					'max'               => 12,
					'layout'            => 'block',
					'button_label'      => __( 'Add location', 'ltt-dive-in' ),
					'collapsed'         => 'field_ltt_dive_in_weather_location_row_name',
					'conditional_logic' => $weather_only,
					'sub_fields'        => array(
						array(
							'key'       => 'field_ltt_dive_in_weather_location_row_name',
							'label'     => __( 'Name', 'ltt-dive-in' ),
							'name'      => 'name',
							'type'      => 'text',
							'required'  => 1,
							'maxlength' => 40,
							'wrapper'   => array( 'width' => '40' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_weather_location_latitude',
							'label'        => __( 'Latitude', 'ltt-dive-in' ),
							'name'         => 'latitude',
							'type'         => 'number',
							'instructions' => __( 'Decimal degrees, for example 39.1677.', 'ltt-dive-in' ),
							'required'     => 1,
							'min'          => -90,
							'max'          => 90,
							'step'         => 'any',
							'wrapper'      => array( 'width' => '20' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_weather_location_longitude',
							'label'        => __( 'Longitude', 'ltt-dive-in' ),
							'name'         => 'longitude',
							'type'         => 'number',
							'instructions' => __( 'Decimal degrees, for example -120.1452.', 'ltt-dive-in' ),
							'required'     => 1,
							'min'          => -180,
							'max'          => 180,
							'step'         => 'any',
							'wrapper'      => array( 'width' => '20' ),
						),
						array(
							'key'          => 'field_ltt_dive_in_weather_location_elevation',
							'label'        => __( 'Elevation (ft)', 'ltt-dive-in' ),
							'name'         => 'elevation',
							'type'         => 'number',
							'instructions' => __( 'Optional. Hidden when empty.', 'ltt-dive-in' ),
							'min'          => -300,
							'max'          => 15000,
							'step'         => 1,
							'wrapper'      => array( 'width' => '20' ),
						),
					),
				),
			),
			'location'     => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/current-conditions.php',
					),
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'child',
					),
				),
			),
			'position'     => 'normal',
			'style'        => 'default',
			'active'       => true,
			'show_in_rest' => 1,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_current_conditions_tab_fields' );

/**
 * Resolve the live module and its settings for a Current Conditions tab.
 *
 * @param int $post_id Tab page ID.
 * @return array{module: string, locations?: array, active?: string, view?: string} Empty module when nothing should render.
 */
function ltt_dive_in_get_current_conditions_tab_module( $post_id ) {
	if ( ! function_exists( 'get_field' ) || 'weather' !== get_field( 'ltt_dive_in_conditions_tab_module', $post_id ) ) {
		return array( 'module' => '' );
	}

	$locations = ltt_dive_in_get_weather_locations( $post_id );

	if ( ! $locations ) {
		return array( 'module' => '' );
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display choices, validated against allowed values.
	$requested = isset( $_GET['location'] ) ? sanitize_title( wp_unslash( $_GET['location'] ) ) : '';
	$view      = isset( $_GET['weather_view'] ) ? sanitize_key( wp_unslash( $_GET['weather_view'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	return array(
		'module'    => 'weather',
		'locations' => $locations,
		'active'    => isset( $locations[ $requested ] ) ? $requested : array_key_first( $locations ),
		'view'      => in_array( $view, array( 'forecast', 'history' ), true ) ? $view : 'current',
	);
}

/**
 * Get a weather tab's valid locations, keyed by slug, in editor order.
 *
 * @param int $post_id Tab page ID.
 * @return array<string, array{slug: string, name: string, latitude: float, longitude: float, elevation: int|null}>
 */
function ltt_dive_in_get_weather_locations( $post_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows      = get_field( 'ltt_dive_in_weather_locations', $post_id );
	$locations = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 12 ) : array() as $row ) {
		$name = isset( $row['name'] ) && is_string( $row['name'] ) ? trim( $row['name'] ) : '';
		$slug = sanitize_title( $name );

		if (
			'' === $slug || isset( $locations[ $slug ] ) ||
			! isset( $row['latitude'], $row['longitude'] ) || ! is_numeric( $row['latitude'] ) || ! is_numeric( $row['longitude'] ) ||
			abs( (float) $row['latitude'] ) > 90 || abs( (float) $row['longitude'] ) > 180
		) {
			continue;
		}

		$locations[ $slug ] = array(
			'slug'      => $slug,
			'name'      => $name,
			'latitude'  => (float) $row['latitude'],
			'longitude' => (float) $row['longitude'],
			'elevation' => isset( $row['elevation'] ) && is_numeric( $row['elevation'] ) ? (int) $row['elevation'] : null,
		);
	}

	return $locations;
}

/**
 * Get the published Current Conditions page that shows the weather module.
 *
 * @return int Page ID, or 0 when no weather tab is set up.
 */
function ltt_dive_in_get_weather_tab_page_id() {
	$page_id = get_transient( 'ltt_dive_in_weather_tab_page_id' );

	if ( false === $page_id ) {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'no_found_rows'  => true,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cached in a transient that is cleared whenever a page is saved.
				'meta_query'     => array(
					array(
						'key'   => '_wp_page_template',
						'value' => 'templates/current-conditions.php',
					),
					array(
						'key'   => 'ltt_dive_in_conditions_tab_module',
						'value' => 'weather',
					),
				),
			)
		);
		$page_id = $pages ? (int) $pages[0] : 0;

		set_transient( 'ltt_dive_in_weather_tab_page_id', $page_id, DAY_IN_SECONDS );
	}

	return (int) $page_id;
}

/**
 * Get the default (first) location of the published Current Conditions weather tab.
 *
 * The header weather indicator uses it so both show the same report.
 *
 * @return array Location from ltt_dive_in_get_weather_locations(), or empty when no weather tab is set up.
 */
function ltt_dive_in_get_default_weather_location() {
	static $location = null;

	if ( null === $location ) {
		$page_id   = ltt_dive_in_get_weather_tab_page_id();
		$locations = $page_id ? ltt_dive_in_get_weather_locations( $page_id ) : array();
		$location  = $locations ? reset( $locations ) : array();
	}

	return $location;
}

/**
 * Forget the cached weather tab whenever a page changes.
 */
function ltt_dive_in_clear_weather_tab_page_cache() {
	delete_transient( 'ltt_dive_in_weather_tab_page_id' );
}
add_action( 'save_post_page', 'ltt_dive_in_clear_weather_tab_page_cache' );
add_action( 'deleted_post', 'ltt_dive_in_clear_weather_tab_page_cache' );

/**
 * Build the compact hero arguments from the parent page's title and excerpt.
 *
 * @return array
 */
function ltt_dive_in_get_current_conditions_hero_args() {
	$hub_id = ltt_dive_in_get_current_conditions_hub_id();

	return array(
		'title'         => get_the_title( $hub_id ),
		'heading_level' => get_queried_object_id() === $hub_id ? 'h1' : 'p',
		'text'          => has_excerpt( $hub_id ) ? get_the_excerpt( $hub_id ) : '',
	);
}
