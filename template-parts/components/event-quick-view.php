<?php
/**
 * Event Quick View panel.
 *
 * Rendered inside a `template` element of its event card; events.js clones it
 * into the results when the card's Quick View button is pressed.
 *
 * Expected arguments:
 * - event: Normalized Seeker event.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event = isset( $args['event'] ) && is_array( $args['event'] ) ? $args['event'] : array();

if ( empty( $event['id'] ) || empty( $event['title'] ) ) {
	return;
}

$title_id = 'quick-view-' . $event['id'] . '-title';
$schedule = ltt_dive_in_get_event_schedule_labels( $event );
$excerpt  = ltt_dive_in_get_event_excerpt( $event, 30 );
$link     = ltt_dive_in_get_event_permalink( $event );
$tag      = ! empty( $event['categories'][0]['name'] ) ? $event['categories'][0]['name'] : '';
$address  = implode( ', ', array_filter( array( $event['address']['street'] ? $event['address']['street'] : $event['venue'], $event['address']['city'] ) ) );
$flags    = array_filter(
	array(
		'featured'  => ! empty( $event['featured'] ) ? array( __( 'Featured', 'ltt-dive-in' ), 'event-featured.svg', 26 ) : null,
		'recurring' => ! empty( $event['recurring'] ) ? array( __( 'Recurring', 'ltt-dive-in' ), 'event-recurring.svg', 32 ) : null,
	)
);
?>
<section class="ltt-event-quick-view<?php echo empty( $event['image']['src'] ) ? ' ltt-event-quick-view--no-image' : ''; ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" tabindex="-1" data-event-quick-view>
	<?php if ( ! empty( $event['image']['src'] ) ) : ?>
		<div class="ltt-event-quick-view__media">
			<?php echo ltt_dive_in_get_event_image( $event, '(max-width: 767.98px) 100vw, 380px' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by ltt_dive_in_get_event_image(). ?>
		</div>
	<?php endif; ?>

	<div class="ltt-event-quick-view__body">
		<div class="ltt-event-quick-view__top">
			<?php if ( $tag ) : ?>
				<p class="ltt-event-quick-view__tag ltt-tag"><?php echo esc_html( $tag ); ?></p>
			<?php endif; ?>
			<button class="ltt-expand-cta ltt-event-quick-view__close" type="button" data-event-quick-view-close>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-hide-quick-view.svg' ) ); ?>" alt="" width="29" height="24" />
				<?php esc_html_e( 'Hide Quick View', 'ltt-dive-in' ); ?>
			</button>
		</div>

		<h3 id="<?php echo esc_attr( $title_id ); ?>" class="ltt-event-quick-view__title"><?php echo esc_html( $event['title'] ); ?></h3>

		<?php if ( $flags ) : ?>
			<ul class="ltt-event-card__flags ltt-event-quick-view__flags" aria-label="<?php esc_attr_e( 'Event highlights', 'ltt-dive-in' ); ?>">
				<?php foreach ( $flags as $flag_key => $flag ) : ?>
					<li class="ltt-event-card__flag ltt-event-card__flag--<?php echo esc_attr( $flag_key ); ?>">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $flag[1] ) ); ?>" alt="" width="<?php echo esc_attr( (string) $flag[2] ); ?>" height="24" />
						<?php echo esc_html( $flag[0] ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $event['organizer'] ) : ?>
			<p class="ltt-event-quick-view__organizer">
				<?php
				/* translators: %s: event organizer. */
				echo esc_html( sprintf( __( 'Presented by: %s', 'ltt-dive-in' ), $event['organizer'] ) );
				?>
			</p>
		<?php endif; ?>

		<dl class="ltt-event-quick-view__schedule">
			<div>
				<dt><?php esc_html_e( 'Dates:', 'ltt-dive-in' ); ?></dt>
				<dd><time datetime="<?php echo esc_attr( ltt_dive_in_get_event_datetime_attribute( $event ) ); ?>"><?php echo esc_html( $schedule['dates'] ); ?></time></dd>
			</div>
			<div>
				<dt><?php esc_html_e( 'Time:', 'ltt-dive-in' ); ?></dt>
				<dd><?php echo esc_html( $schedule['time'] ); ?></dd>
			</div>
		</dl>

		<?php if ( $address ) : ?>
			<p class="ltt-event-quick-view__address">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-address-navy.svg' ) ); ?>" alt="" width="24" height="35" />
				<?php echo esc_html( $address ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $excerpt || $link ) : ?>
			<p class="ltt-event-quick-view__excerpt">
				<?php echo esc_html( $excerpt ); ?>
				<?php if ( $link ) : ?>
					<a class="ltt-event-quick-view__more" href="<?php echo esc_url( $link ); ?>">
						<?php esc_html_e( 'Read More', 'ltt-dive-in' ); ?>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: event name. */
							echo esc_html( sprintf( __( 'about %s', 'ltt-dive-in' ), $event['title'] ) );
							?>
						</span>
					</a>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<a class="ltt-event-quick-view__calendar" href="<?php echo esc_url( ltt_dive_in_get_event_reminder_url( $event ) ); ?>" download>
			<?php esc_html_e( 'Add to Calendar', 'ltt-dive-in' ); ?>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: event name. */
				echo esc_html( sprintf( __( ', add %s to your calendar', 'ltt-dive-in' ), $event['title'] ) );
				?>
			</span>
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-add-to-calendar-teal.svg' ) ); ?>" alt="" width="30" height="24" />
		</a>
	</div>
</section>
