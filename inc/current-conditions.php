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
