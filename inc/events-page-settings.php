<?php
/**
 * Events page template fields.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Events page fields.
 */
function ltt_dive_in_register_events_page_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'         => 'group_ltt_dive_in_events_page',
			'title'       => __( 'LTT Dive In — Events Page', 'ltt-dive-in' ),
			'description' => __( 'Configure how events from Seeker appear on this page.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'key'          => 'field_ltt_dive_in_events_page_tag',
					'label'        => __( 'Tag', 'ltt-dive-in' ),
					'name'         => 'ltt_dive_in_events_page_tag',
					'type'         => 'text',
					'instructions' => __( 'Optional. Shown above the title.', 'ltt-dive-in' ),
					'maxlength'    => 40,
				),
				array(
					'key'           => 'field_ltt_dive_in_events_page_submit_link',
					'label'         => __( 'Submit link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_events_page_submit_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. Enter a descriptive label and choose a destination for the button below the filters.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
			),
			'location'    => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/events.php',
					),
				),
			),
			'position'    => 'acf_after_title',
			'style'       => 'default',
			'active'      => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_events_page_settings' );

/**
 * Return the Events page intro and link settings.
 *
 * @param int $post_id Page ID.
 * @return array{tag: string, title: string, text: string, submit_link: array}
 */
function ltt_dive_in_get_events_page_settings( $post_id ) {
	$post_id = absint( $post_id );
	$tag     = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_events_page_tag', $post_id ) : '';
	$link    = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_events_page_submit_link', $post_id ) : array();

	if ( ! is_array( $link ) || empty( $link['url'] ) || ! is_string( $link['title'] ?? null ) || '' === trim( $link['title'] ) ) {
		$link = array();
	} else {
		$link = array(
			'title'  => trim( $link['title'] ),
			'url'    => (string) $link['url'],
			'target' => ! empty( $link['target'] ) ? '_blank' : '',
		);
	}

	return array(
		'tag'         => is_string( $tag ) ? trim( $tag ) : '',
		'title'       => get_the_title( $post_id ),
		'text'        => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '',
		'submit_link' => $link,
	);
}
