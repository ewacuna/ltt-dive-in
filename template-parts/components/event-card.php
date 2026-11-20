<?php
/**
 * Reusable event card.
 *
 * Styles:
 * - `card`: navy card with the image above its content.
 * - `tile`: full-image Story Card – Event on wider screens that reveals its
 *   details on hover or keyboard focus, and the `card` presentation on small
 *   screens.
 * - `row`: Story Card – Event list view, with the image beside its content.
 *
 * Optional arguments:
 * - `flags`: show the Featured and Recurring badges.
 * - `compact_mobile`: use the compact row below 768px instead of the card.
 * - `reminder_label`: visible label of the calendar link.
 * - `reminder_icon`: `remind` (alarm clock, default) or `add` (calendar).
 * - `quick_view`: add a Quick View button and its panel template.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event          = isset( $args['event'] ) && is_array( $args['event'] ) ? $args['event'] : array();
$style          = isset( $args['style'] ) && in_array( $args['style'], array( 'tile', 'row' ), true ) ? $args['style'] : 'card';
$wrapper        = isset( $args['wrapper'] ) && is_string( $args['wrapper'] ) ? $args['wrapper'] : '';
$sizes          = isset( $args['sizes'] ) && is_string( $args['sizes'] ) ? $args['sizes'] : '(max-width: 767.98px) 305px, (max-width: 991.98px) 50vw, 427px';
$show_flags     = ! empty( $args['flags'] );
$quick_view     = 'card' !== $style && ! empty( $args['quick_view'] );
$reminder_icon  = isset( $args['reminder_icon'] ) && 'add' === $args['reminder_icon'] ? array( 'event-add-to-calendar.svg', 30, 24 ) : array( 'event-reminder.svg', 18, 20 );
$reminder_label = isset( $args['reminder_label'] ) && is_string( $args['reminder_label'] ) && '' !== $args['reminder_label'] ? $args['reminder_label'] : __( 'Remind Me', 'ltt-dive-in' );

if ( empty( $event['title'] ) || empty( $event['start'] ) || ! $event['start'] instanceof DateTimeImmutable ) {
	return;
}

$timezone = $event['start']->getTimezone();
$link     = ltt_dive_in_get_event_permalink( $event );
$tag      = 'row' !== $style && ! empty( $event['categories'][0]['name'] ) ? $event['categories'][0]['name'] : '';
$excerpt  = ltt_dive_in_get_event_excerpt( $event, 'card' === $style ? 12 : 14 );
$address  = 'row' === $style || ! empty( $args['compact_mobile'] ) ? ( $event['address']['street'] ? $event['address']['street'] : $event['venue'] ) : '';
$flags    = array();
$classes  = array( 'ltt-event-card', 'ltt-event-card--' . $style );

if ( $show_flags && ! empty( $event['featured'] ) ) {
	$flags['featured'] = array(
		'label' => __( 'Featured', 'ltt-dive-in' ),
		'icon'  => 'event-featured.svg',
		'width' => 26,
	);
}

if ( $show_flags && ! empty( $event['recurring'] ) ) {
	$flags['recurring'] = array(
		'label' => __( 'Recurring', 'ltt-dive-in' ),
		'icon'  => 'event-recurring.svg',
		'width' => 32,
	);
}

if ( empty( $event['image']['src'] ) ) {
	$classes[] = 'ltt-event-card--no-image';
}

if ( 'card' !== $style && ! empty( $args['compact_mobile'] ) ) {
	$classes[] = 'ltt-event-card--compact-mobile';
}

if ( $wrapper ) {
	$classes = array_merge( $classes, array_map( 'sanitize_html_class', explode( ' ', $wrapper ) ) );
}

$render_flags = static function ( $modifier ) use ( $flags ) {
	if ( ! $flags ) {
		return;
	}
	?>
	<ul class="ltt-event-card__flags ltt-event-card__flags--<?php echo esc_attr( $modifier ); ?>" aria-label="<?php esc_attr_e( 'Event highlights', 'ltt-dive-in' ); ?>">
		<?php foreach ( $flags as $flag_key => $flag ) : ?>
			<li class="ltt-event-card__flag ltt-event-card__flag--<?php echo esc_attr( $flag_key ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $flag['icon'] ) ); ?>" alt="" width="<?php echo esc_attr( (string) $flag['width'] ); ?>" height="24" />
				<?php echo esc_html( $flag['label'] ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
};
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-event-card<?php echo $quick_view ? ' data-event-id="' . esc_attr( $event['id'] ) . '"' : ''; ?>>
	<div class="ltt-event-card__media" aria-hidden="true">
		<?php echo ltt_dive_in_get_event_image( $event, $sizes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by ltt_dive_in_get_event_image(). ?>
	</div>

	<?php if ( 'tile' === $style ) : ?>
		<?php $render_flags( 'media' ); ?>
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
		<?php if ( $tag || $quick_view ) : ?>
			<div class="ltt-event-card__header">
				<?php if ( $tag ) : ?>
					<p class="ltt-event-card__tag ltt-tag ltt-tag--dark"><?php echo esc_html( $tag ); ?></p>
				<?php endif; ?>
				<?php if ( $quick_view ) : ?>
					<button class="ltt-expand-cta ltt-expand-cta--image ltt-event-card__quick-view" type="button" aria-expanded="false" data-event-quick-view-open hidden>
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-quick-view.svg' ) ); ?>" alt="" width="33" height="24" />
						<?php esc_html_e( 'Quick View', 'ltt-dive-in' ); ?>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: event name. */
							echo esc_html( sprintf( __( 'of %s', 'ltt-dive-in' ), $event['title'] ) );
							?>
						</span>
					</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h3 class="ltt-event-card__title">
			<?php if ( $link ) : ?>
				<a class="ltt-event-card__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $event['title'] ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $event['title'] ); ?>
			<?php endif; ?>
		</h3>

		<?php if ( 'row' !== $style && $event['venue'] ) : ?>
			<p class="ltt-event-card__venue"><?php echo esc_html( $event['venue'] ); ?></p>
		<?php endif; ?>

		<?php if ( $address ) : ?>
			<p class="ltt-event-card__address">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-address.svg' ) ); ?>" alt="" width="24" height="35" />
				<?php echo esc_html( $address ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $excerpt ) : ?>
			<p class="ltt-event-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<?php $render_flags( 'content' ); ?>

		<div class="ltt-event-card__actions">
			<?php if ( 'card' !== $style && ! ltt_dive_in_event_has_ended( $event ) ) : ?>
				<a class="ltt-event-card__reminder" href="<?php echo esc_url( ltt_dive_in_get_event_reminder_url( $event ) ); ?>" download>
					<?php echo esc_html( $reminder_label ); ?>
					<span class="screen-reader-text">
						<?php
						/* translators: %s: event name. */
						echo esc_html( sprintf( __( ', add %s to your calendar', 'ltt-dive-in' ), $event['title'] ) );
						?>
					</span>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $reminder_icon[0] ) ); ?>" alt="" width="<?php echo esc_attr( (string) $reminder_icon[1] ); ?>" height="<?php echo esc_attr( (string) $reminder_icon[2] ); ?>" />
				</a>
			<?php endif; ?>
			<?php if ( 'row' !== $style && $link ) : ?>
				<span class="ltt-event-card__read-more" aria-hidden="true">
					<?php esc_html_e( 'Read More', 'ltt-dive-in' ); ?>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/read-more-chevron.svg' ) ); ?>" alt="" width="11" height="10" />
				</span>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $quick_view ) : ?>
		<template data-event-quick-view-template>
			<?php get_template_part( 'template-parts/components/event-quick-view', null, array( 'event' => $event ) ); ?>
		</template>
	<?php endif; ?>
</article>
