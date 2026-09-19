<?php
/**
 * Testimonials block renderer.
 *
 * Without JavaScript, and in the editor preview, every testimonial is shown
 * in order and the carousel controls stay hidden.
 *
 * @package LTT_Dive_In
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview Whether the block is being rendered in the editor.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview   = ! empty( $is_preview );
$testimonials = ltt_dive_in_prepare_testimonials( get_field( 'ltt_dive_in_testimonials_items' ) );

if ( ! $testimonials ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add at least one testimonial with a quote and a name.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$has_carousel = count( $testimonials ) > 1;
$id           = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'testimonials-' . sanitize_html_class( $block['id'] );
$classes      = array( 'testimonials' );

if ( ! empty( $block['align'] ) && 'full' === $block['align'] ) {
	$classes[] = 'alignfull';
}

if ( $has_carousel && ! $is_preview && function_exists( 'ltt_dive_in_enqueue_testimonials_carousel_assets' ) ) {
	// Fallback for blocks the head-time block-tree check could not discover.
	ltt_dive_in_enqueue_testimonials_carousel_assets();
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-label="<?php esc_attr_e( 'Testimonials', 'ltt-dive-in' ); ?>"<?php echo $has_carousel ? ' data-testimonials-carousel' : ''; ?>>
	<div class="testimonials__container">
		<?php if ( $has_carousel ) : ?>
			<button class="testimonials__control testimonials__control--previous ltt-slider-navigation ltt-slider-navigation--white" type="button" data-testimonials-previous aria-label="<?php esc_attr_e( 'Previous testimonial', 'ltt-dive-in' ); ?>" hidden>
				<span class="testimonials__control-icon ltt-slider-navigation__icon" aria-hidden="true"></span>
			</button>
		<?php endif; ?>

		<div class="testimonials__viewport" data-testimonials-viewport>
			<div class="testimonials__track" data-testimonials-track>
				<?php foreach ( $testimonials as $testimonial ) : ?>
					<figure class="testimonials__slide" data-testimonials-slide>
						<blockquote class="testimonials__quote">
							<p><?php echo esc_html( $testimonial['quote'] ); ?></p>
						</blockquote>
						<figcaption class="testimonials__author">
							<?php if ( $testimonial['image_id'] ) : ?>
								<?php echo wp_get_attachment_image( $testimonial['image_id'], 'thumbnail', false, array( 'class' => 'testimonials__photo', 'alt' => '', 'loading' => 'lazy', 'sizes' => '56px' ) ); ?>
							<?php endif; ?>
							<span class="testimonials__name"><?php echo esc_html( $testimonial['name'] ); ?></span>
							<?php if ( $testimonial['role'] ) : ?>
								<span class="testimonials__role"><?php echo esc_html( $testimonial['role'] ); ?></span>
							<?php endif; ?>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $has_carousel ) : ?>
			<button class="testimonials__control testimonials__control--next ltt-slider-navigation ltt-slider-navigation--white" type="button" data-testimonials-next aria-label="<?php esc_attr_e( 'Next testimonial', 'ltt-dive-in' ); ?>" hidden>
				<span class="testimonials__control-icon ltt-slider-navigation__icon" aria-hidden="true"></span>
			</button>
			<div class="testimonials__pagination ltt-carousel-indicators" data-testimonials-pagination hidden></div>
		<?php endif; ?>
	</div>
</section>
