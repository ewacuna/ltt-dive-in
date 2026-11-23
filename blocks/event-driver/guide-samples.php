<?php
/**
 * Block Guide samples for Event Driver.
 *
 * Events are live from Seeker. The hero uses the next upcoming events so it
 * always has something to show.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$p      = 'ltt_dive_in_event_driver_';
$events = function_exists( 'ltt_dive_in_get_seeker_events' ) ? ltt_dive_in_get_seeker_events( array( 'limit' => 6 ) ) : null;
$ids    = ( is_array( $events ) && ! empty( $events['events'] ) ) ? array_slice( array_column( $events['events'], 'id' ), 0, 3 ) : array();

$header = array(
	$p . 'tag'            => __( 'Events', 'ltt-dive-in' ),
	$p . 'heading'        => __( 'What\'s happening at the lake', 'ltt-dive-in' ),
	$p . 'intro'          => __( 'Concerts, festivals, and races coming up around Lake Tahoe.', 'ltt-dive-in' ),
	$p . 'primary_link'   => ltt_dive_in_block_guide_link( __( 'See all events', 'ltt-dive-in' ) ),
	$p . 'secondary_link' => ltt_dive_in_block_guide_link( __( 'Submit an event', 'ltt-dive-in' ) ),
	$p . 'limit_month'    => 0,
);

$samples = array(
	'listed'   => array_merge(
		array( $p . 'variant' => 'listed' ),
		$header,
		array(
			$p . 'initial_count'        => 6,
			$p . 'load_more_count'      => 6,
			$p . 'show_category_filter' => 1,
			$p . 'show_date_filter'     => 1,
			$p . 'show_search'          => 1,
		)
	),
	'carousel' => array_merge(
		array( $p . 'variant' => 'carousel' ),
		$header,
		array(
			$p . 'carousel_count'    => 8,
			$p . 'carousel_sort'     => 'soonest',
			$p . 'carousel_featured' => 0,
		)
	),
);

if ( count( $ids ) >= 3 ) {
	$samples = array(
		'hero' => array(
			$p . 'variant'         => 'hero',
			$p . 'hero_source'     => 'manual',
			$p . 'hero_events'     => $ids,
			$p . 'primary_label'   => __( 'Get Tickets', 'ltt-dive-in' ),
			$p . 'secondary_label' => __( 'Learn More', 'ltt-dive-in' ),
		),
	) + $samples;
}

return $samples;
