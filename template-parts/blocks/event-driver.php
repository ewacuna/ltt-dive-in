<?php
/**
 * Event Driver ACF block renderer.
 *
 * Events are read from the Seeker API on the server. Missing configuration,
 * an unavailable API, or an empty result renders nothing on the front end and
 * an explanation in the editor preview.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = isset( $is_preview ) && $is_preview;
$block_id   = isset( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id( 'event-driver-' );
$anchor     = isset( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : '';
$alignment  = isset( $block['align'] ) && 'full' === $block['align'] ? 'alignfull' : '';
$block_data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
$get_value  = static function ( $name ) use ( $block_data ) {
	$value = get_field( $name );

	return ( false === $value || null === $value ) && array_key_exists( $name, $block_data ) ? $block_data[ $name ] : $value;
};
$config     = ltt_dive_in_get_event_driver_config( $get_value );
$preview_message = static function ( $message ) use ( $is_preview ) {
	if ( $is_preview ) {
		echo '<p class="event-driver__preview-message">' . esc_html( $message ) . '</p>';
	}
};

if ( ! $config['variant'] ) {
	$preview_message( __( 'Choose an Event Driver variant to preview this block.', 'ltt-dive-in' ) );
	return;
}

if ( '' === ltt_dive_in_get_seeker_api_key() ) {
	$preview_message( __( 'Add a Seeker API key in Settings → Events to display events.', 'ltt-dive-in' ) );
	return;
}

if ( in_array( $config['variant'], array( 'listed', 'carousel' ), true ) && ! $config['heading'] ) {
	$preview_message( __( 'Add a heading to preview this Event Driver.', 'ltt-dive-in' ) );
	return;
}

if ( 'hero' === $config['variant'] && 'manual' === $config['hero_source'] && ! $config['hero_events'] ) {
	$preview_message( __( 'Choose 3 to 6 events to preview Hero Featured Events.', 'ltt-dive-in' ) );
	return;
}

get_template_part(
	'template-parts/components/event-driver/' . $config['variant'],
	null,
	array(
		'config'          => $config,
		'section_id'      => $anchor ? $anchor : 'event-driver-' . $block_id,
		'class_name'      => $alignment,
		'is_preview'      => $is_preview,
		'post_id'         => $is_preview ? 0 : get_the_ID(),
		'block_hash'      => ltt_dive_in_get_event_driver_block_hash( $block_data ),
		'preview_message' => $preview_message,
	)
);
