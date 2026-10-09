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
 * @var array $args       Optional Content Module rows and block context.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$context      = isset( $args ) && is_array( $args ) ? $args : array();
$is_preview   = array_key_exists( 'is_preview', $context ) ? (bool) $context['is_preview'] : ! empty( $is_preview );
$rows         = array_key_exists( 'rows', $context ) ? $context['rows'] : get_field( 'ltt_dive_in_testimonials_items' );
$testimonials = ltt_dive_in_prepare_testimonials( $rows );

if ( isset( $context['block'] ) && is_array( $context['block'] ) ) {
	$block = $context['block'];
} elseif ( ! isset( $block ) || ! is_array( $block ) ) {
	$block = array();
}

if ( ! $testimonials ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add at least one testimonial with a quote and a name.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$has_carousel = count( $testimonials ) > 1;
$block_id     = ! empty( $block['id'] ) ? $block['id'] : wp_unique_id();
$classes      = array( 'testimonials' );

if ( ! empty( $context['id'] ) ) {
	$id = sanitize_html_class( $context['id'] );
} elseif ( ! empty( $block['anchor'] ) ) {
	$id = sanitize_html_class( $block['anchor'] );
} else {
	$id = 'testimonials-' . sanitize_html_class( $block_id );
}

// The block metadata supplies this handle normally; this also covers dynamic
// Content Module rendering outside the queried post's parsed block tree.
wp_enqueue_style( 'ltt-dive-in-testimonials' );

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
			<button class="testimonials__control testimonials__control--previous ltt-slider-navigation ltt-slider-navigation--white ltt-slider-navigation--previous" type="button" data-testimonials-previous aria-label="<?php esc_attr_e( 'Previous testimonial', 'ltt-dive-in' ); ?>" hidden>
				<span class="testimonials__control-icon ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span>
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
			<button class="testimonials__control testimonials__control--next ltt-slider-navigation ltt-slider-navigation--white ltt-slider-navigation--next" type="button" data-testimonials-next aria-label="<?php esc_attr_e( 'Next testimonial', 'ltt-dive-in' ); ?>" hidden>
				<span class="testimonials__control-icon ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span>
			</button>
			<div class="testimonials__pagination ltt-carousel-indicators" data-testimonials-pagination hidden></div>
		<?php endif; ?>
	</div>
</section>
