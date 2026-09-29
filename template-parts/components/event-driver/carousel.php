<?php
/**
 * Events Carousel (Figma nodes 1:11658 desktop, 1:11659 mobile).
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config          = $args['config'];
$section_id      = $args['section_id'];
$is_preview      = ! empty( $args['is_preview'] );
$preview_message = $args['preview_message'];
$events          = ltt_dive_in_get_event_driver_carousel_events( $config );

if ( is_wp_error( $events ) || ! $events ) {
	$preview_message( is_wp_error( $events ) ? __( 'Events could not be loaded from Seeker. Check Settings → Events.', 'ltt-dive-in' ) : __( 'There are no upcoming events that match these settings.', 'ltt-dive-in' ) );
	return;
}

$classes = array( 'event-driver', 'event-driver--carousel', $args['class_name'] );

if ( $is_preview ) {
	$classes[] = 'event-driver--preview';
}
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $section_id . '-title' ); ?>"<?php echo $is_preview ? '' : ' data-event-driver-carousel'; ?>>
	<div class="event-driver__container">
		<?php
		get_template_part(
			'template-parts/components/event-driver/header',
			null,
			array(
				'config'     => $config,
				'heading_id' => $section_id . '-title',
				'is_preview' => $is_preview,
			)
		);
		?>
		<div class="event-driver__carousel">
			<div class="event-driver__viewport swiper" data-event-driver-viewport>
				<div class="event-driver__track swiper-wrapper">
					<?php foreach ( $events as $event ) : ?>
						<?php
						get_template_part(
							'template-parts/components/event-card',
							null,
							array(
								'event'   => $event,
								'style'   => 'card',
								'wrapper' => 'event-driver__item swiper-slide',
								'sizes'   => '(max-width: 767.98px) 305px, 405px',
							)
						);
						?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="event-driver__carousel-footer" data-event-driver-controls hidden>
				<div class="event-driver__pagination ltt-carousel-indicators" data-event-driver-pagination></div>
				<div class="event-driver__carousel-controls">
					<button class="ltt-slider-navigation ltt-slider-navigation--white" type="button" data-event-driver-previous aria-label="<?php esc_attr_e( 'Previous events', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
					<button class="ltt-slider-navigation" type="button" data-event-driver-next aria-label="<?php esc_attr_e( 'Next events', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
				</div>
			</div>
		</div>
	</div>
	<?php ltt_dive_in_print_event_schema( $events ); ?>
</section>
