<?php
/**
 * Seeker event submission form.
 *
 * Expected arguments:
 * - calendar_id: Seeker calendar UUID.
 * - form_id: Seeker submission form UUID.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$calendar_id = isset( $args['calendar_id'] ) ? (string) $args['calendar_id'] : '';
$form_id     = isset( $args['form_id'] ) ? (string) $args['form_id'] : '';

if ( ! $calendar_id || ! $form_id ) {
	if ( current_user_can( 'edit_post', get_the_ID() ) ) {
		echo '<p class="event-submission__notice">' . esc_html__( 'Add the Calendar ID and Form ID from the Seeker embed code to show the submission form.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<section class="event-submission" aria-labelledby="event-submission-title">
	<h2 id="event-submission-title" class="screen-reader-text"><?php esc_html_e( 'Event submission form', 'ltt-dive-in' ); ?></h2>
	<div class="event-submission__form" data-event-submission data-calendar-id="<?php echo esc_attr( $calendar_id ); ?>" data-form-id="<?php echo esc_attr( $form_id ); ?>" data-error-message="<?php esc_attr_e( 'The submission form could not be loaded. Please try again later.', 'ltt-dive-in' ); ?>">
		<p class="event-submission__loading" data-event-submission-status role="status"><?php esc_html_e( 'Loading the submission form…', 'ltt-dive-in' ); ?></p>
		<noscript><p class="event-submission__notice"><?php esc_html_e( 'The event submission form needs JavaScript. Please enable it in your browser to submit an event.', 'ltt-dive-in' ); ?></p></noscript>
	</div>
</section>
