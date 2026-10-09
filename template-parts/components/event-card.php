<?php
/**
 * Reusable event card.
 *
 * Styles:
 * - `card`: navy card with the image above its content (Figma “Card with Date”).
 * - `tile`: full-image Story Card – Event on wider screens that reveals its
 *   details on hover or keyboard focus, and the `card` presentation on small
 *   screens.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event   = isset( $args['event'] ) && is_array( $args['event'] ) ? $args['event'] : array();
$style   = isset( $args['style'] ) && 'tile' === $args['style'] ? 'tile' : 'card';
$wrapper = isset( $args['wrapper'] ) && is_string( $args['wrapper'] ) ? $args['wrapper'] : '';
$sizes   = isset( $args['sizes'] ) && is_string( $args['sizes'] ) ? $args['sizes'] : '(max-width: 767.98px) 305px, (max-width: 991.98px) 50vw, 427px';

if ( empty( $event['title'] ) || empty( $event['start'] ) || ! $event['start'] instanceof DateTimeImmutable ) {
	return;
}

$timezone = $event['start']->getTimezone();
$tag      = ! empty( $event['categories'][0]['name'] ) ? $event['categories'][0]['name'] : '';
$excerpt  = ltt_dive_in_get_event_excerpt( $event, 'tile' === $style ? 14 : 12 );
$classes  = array( 'ltt-event-card', 'ltt-event-card--' . $style );

if ( empty( $event['image']['src'] ) ) {
	$classes[] = 'ltt-event-card--no-image';
}

if ( $wrapper ) {
	$classes = array_merge( $classes, array_map( 'sanitize_html_class', explode( ' ', $wrapper ) ) );
}
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-event-card>
	<div class="ltt-event-card__media" aria-hidden="true">
		<?php echo ltt_dive_in_get_event_image( $event, $sizes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by ltt_dive_in_get_event_image(). ?>
	</div>

	<?php if ( 'tile' === $style ) : ?>
		<div class="ltt-event-card__summary" aria-hidden="true">
			<span class="ltt-event-card__summary-tag ltt-tag ltt-tag--dark"><?php esc_html_e( 'Event', 'ltt-dive-in' ); ?></span>
			<span class="ltt-event-card__summary-title"><?php echo esc_html( $event['title'] ); ?></span>
		</div>
	<?php endif; ?>

	<p class="ltt-event-card__date">
		<time datetime="<?php echo esc_attr( ltt_dive_in_get_event_datetime_attribute( $event ) ); ?>">
			<span class="ltt-event-card__month"><?php echo esc_html( wp_date( 'F', $event['start']->getTimestamp(), $timezone ) ); ?></span>
			<span class="ltt-event-card__day"><?php echo esc_html( wp_date( 'j', $event['start']->getTimestamp(), $timezone ) ); ?></span>
			<span class="ltt-event-card__weekday"><?php echo esc_html( wp_date( 'l', $event['start']->getTimestamp(), $timezone ) ); ?></span>
		</time>
	</p>

	<div class="ltt-event-card__content">
		<?php if ( $tag ) : ?>
			<p class="ltt-event-card__tag ltt-tag ltt-tag--dark"><?php echo esc_html( $tag ); ?></p>
		<?php endif; ?>

		<h3 class="ltt-event-card__title">
			<?php if ( $event['url'] ) : ?>
				<a class="ltt-event-card__link" href="<?php echo esc_url( $event['url'] ); ?>"><?php echo esc_html( $event['title'] ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $event['title'] ); ?>
			<?php endif; ?>
		</h3>

		<?php if ( $event['venue'] ) : ?>
			<p class="ltt-event-card__venue"><?php echo esc_html( $event['venue'] ); ?></p>
		<?php endif; ?>

		<?php if ( $excerpt ) : ?>
			<p class="ltt-event-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<div class="ltt-event-card__actions">
			<?php if ( 'tile' === $style ) : ?>
				<a class="ltt-event-card__reminder" href="<?php echo esc_url( ltt_dive_in_get_event_reminder_url( $event ) ); ?>" download>
					<?php esc_html_e( 'Remind Me', 'ltt-dive-in' ); ?>
					<span class="screen-reader-text">
						<?php
						/* translators: %s: event name. */
						echo esc_html( sprintf( __( ', add %s to your calendar', 'ltt-dive-in' ), $event['title'] ) );
						?>
					</span>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-reminder.svg' ) ); ?>" alt="" width="18" height="20" />
				</a>
			<?php endif; ?>
			<?php if ( $event['url'] ) : ?>
				<span class="ltt-event-card__read-more" aria-hidden="true">
					<?php esc_html_e( 'Read More', 'ltt-dive-in' ); ?>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/read-more-chevron.svg' ) ); ?>" alt="" width="11" height="10" />
				</span>
			<?php endif; ?>
		</div>
	</div>
</article>
