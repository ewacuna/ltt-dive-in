<?php
/**
 * Story Module ACF block renderer.
 *
 * Validates the variant and hands its field group to the variant's own
 * template part in template-parts/blocks/story-module/.
 *
 * @package LTT_Dive_In
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview Whether the block is being rendered in the editor.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) || ! function_exists( 'ltt_dive_in_story_module_variants' ) ) {
	return;
}

$is_preview = ! empty( $is_preview );
$variants   = ltt_dive_in_story_module_variants();
$variant    = (string) get_field( 'ltt_dive_in_story_module_variant' );

if ( ! isset( $variants[ $variant ] ) ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Choose a Story Module variant to begin.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$slug   = $variants[ $variant ]['slug'];
$fields = get_field( 'ltt_dive_in_story_module_' . $variant );

if ( ! $is_preview ) {
	wp_enqueue_style( ltt_dive_in_story_module_style_handle( $variant ) );
}

get_template_part(
	'template-parts/blocks/story-module/' . $slug,
	null,
	array(
		'id'         => ! empty( $block['anchor'] ) ? sanitize_title( $block['anchor'] ) : wp_unique_id( 'story-module-' ),
		'classes'    => array( 'story-module', 'story-module--' . $slug, 'alignfull' ),
		'fields'     => is_array( $fields ) ? $fields : array(),
		'is_preview' => $is_preview,
	)
);
