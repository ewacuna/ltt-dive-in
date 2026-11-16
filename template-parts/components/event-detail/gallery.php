<?php
/**
 * Event detail photo gallery.
 *
 * Arrows and dots appear only with more than one photo.
 *
 * Expected arguments:
 * - images: Image data from ltt_dive_in_get_seeker_images().
 * - title: Event name, used in the accessible name.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$images = isset( $args['images'] ) && is_array( $args['images'] ) ? array_values( $args['images'] ) : array();
$title  = isset( $args['title'] ) ? (string) $args['title'] : '';

if ( ! $images ) {
	return;
}

$is_carousel = count( $images ) > 1;
?>
<div class="event-detail__gallery<?php echo $is_carousel ? ' event-detail__gallery--carousel' : ''; ?>"<?php if ( $is_carousel ) : ?> role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'ltt-dive-in' ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: event name. */ __( '%s photos', 'ltt-dive-in' ), $title ) ); ?>" data-event-gallery<?php endif; ?>>
	<div class="event-detail__gallery-viewport<?php echo $is_carousel ? ' swiper' : ''; ?>" data-event-gallery-viewport>
		<div class="event-detail__gallery-track<?php echo $is_carousel ? ' swiper-wrapper' : ''; ?>">
			<?php foreach ( $images as $index => $image ) : ?>
				<figure class="event-detail__gallery-slide<?php echo $is_carousel ? ' swiper-slide' : ''; ?>">
					<img src="<?php echo esc_url( $image['src'] ); ?>"<?php echo ! empty( $image['srcset'] ) ? ' srcset="' . esc_attr( $image['srcset'] ) . '"' : ''; ?> sizes="(max-width: 767.98px) 100vw, 648px" width="<?php echo absint( $image['width'] ); ?>" height="<?php echo absint( $image['height'] ); ?>" alt="<?php echo esc_attr( $image['caption'] ?? '' ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" decoding="async"<?php echo 0 === $index ? ' fetchpriority="high"' : ''; ?> />
				</figure>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( $is_carousel ) : ?>
		<div class="event-detail__gallery-controls" data-event-gallery-controls hidden>
			<div class="event-detail__gallery-pagination ltt-carousel-indicators" data-event-gallery-pagination></div>
			<div class="event-detail__gallery-arrows">
				<button class="ltt-slider-navigation ltt-slider-navigation--white ltt-slider-navigation--previous" type="button" aria-label="<?php esc_attr_e( 'Previous photo', 'ltt-dive-in' ); ?>" data-event-gallery-previous><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
				<button class="ltt-slider-navigation ltt-slider-navigation--next" type="button" aria-label="<?php esc_attr_e( 'Next photo', 'ltt-dive-in' ); ?>" data-event-gallery-next><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
			</div>
		</div>
	<?php endif; ?>
</div>
