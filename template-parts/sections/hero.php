<?php
/**
 * Reusable hero section.
 *
 * Expected arguments:
 * - title: Hero heading.
 * - text: Supporting copy.
 * - button_label: Optional call-to-action label.
 * - button_url: Optional call-to-action URL.
 *
 * @package LTT_Dive_In
 */

$title        = $args['title'] ?? '';
$text         = $args['text'] ?? '';
$button_label = $args['button_label'] ?? '';
$button_url   = $args['button_url'] ?? '';

if ( ! $title && ! $text ) {
	return;
}
?>
<section class="hero-section alignwide">
	<div class="hero-section__content">
		<?php if ( $title ) : ?>
			<h2 class="hero-section__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<?php if ( $text ) : ?>
			<p class="hero-section__text"><?php echo wp_kses_post( $text ); ?></p>
		<?php endif; ?>

		<?php if ( $button_label && $button_url ) : ?>
			<a class="button" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_label ); ?></a>
		<?php endif; ?>
	</div>
</section>
