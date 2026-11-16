<?php
/**
 * Upcoming dates of a recurring event, grouped by year.
 *
 * A disclosure that starts open; it renders only when the event has more
 * than one upcoming date.
 *
 * Expected arguments:
 * - occurrences: Upcoming dates from ltt_dive_in_get_seeker_occurrences().
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$occurrences = isset( $args['occurrences'] ) && is_array( $args['occurrences'] ) ? $args['occurrences'] : array();

if ( count( $occurrences ) < 2 ) {
	return;
}

$years = array();

foreach ( $occurrences as $occurrence ) {
	$start    = $occurrence['start'];
	$timezone = $start->getTimezone();
	$label    = wp_date( __( 'l - F j, Y', 'ltt-dive-in' ), $start->getTimestamp(), $timezone );

	$years[ $start->format( 'Y' ) ][] = array(
		'datetime' => $start->format( DATE_W3C ),
		'label'    => $label,
		'time'     => wp_date( __( 'g:i A', 'ltt-dive-in' ), $start->getTimestamp(), $timezone ),
	);
}

$show_time = count( array_unique( wp_list_pluck( array_merge( ...array_values( $years ) ), 'time' ) ) ) > 1;
?>
<section class="event-detail__dates" aria-labelledby="event-detail-dates-title" data-event-dates>
	<h2 class="event-detail__dates-heading">
		<button id="event-detail-dates-title" class="event-detail__dates-toggle" type="button" aria-expanded="true" aria-controls="event-detail-dates-panel" data-event-dates-toggle>
			<?php esc_html_e( 'Upcoming Dates', 'ltt-dive-in' ); ?>
			<span class="event-detail__dates-icon" aria-hidden="true"></span>
		</button>
	</h2>
	<div id="event-detail-dates-panel" class="event-detail__dates-panel">
		<?php foreach ( $years as $year => $dates ) : ?>
			<div class="event-detail__dates-year">
				<h3 class="event-detail__dates-year-title"><?php echo esc_html( (string) $year ); ?></h3>
				<ul class="event-detail__dates-list">
					<?php foreach ( $dates as $date ) : ?>
						<li>
							<time datetime="<?php echo esc_attr( $date['datetime'] ); ?>"><?php echo esc_html( $date['label'] ); ?></time>
							<?php if ( $show_time ) : ?>
								<span class="event-detail__dates-time"><?php echo esc_html( $date['time'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
</section>
