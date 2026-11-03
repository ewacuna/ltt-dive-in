<?php
/**
 * First editorial background image for an error page.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$images = isset( $args['images'] ) && is_array( $args['images'] ) ? $args['images'] : array();
$first  = reset( $images );

if ( ! is_array( $first ) || empty( $first['src'] ) ) {
	return;
}
?>
<div class="ltt-error-page__background swiper" aria-hidden="true">
	<div class="ltt-error-page__background-track swiper-wrapper">
		<?php foreach ( $images as $index => $image ) : ?>
			<div class="ltt-error-page__background-slide swiper-slide">
				<img class="ltt-error-page__background-image"
					<?php if ( 0 === $index ) : ?>src="<?php echo esc_url( $image['src'] ); ?>"<?php else : ?>data-src="<?php echo esc_url( $image['src'] ); ?>"<?php endif; ?>
					<?php if ( ! empty( $image['srcset'] ) ) : ?><?php echo 0 === $index ? 'srcset' : 'data-srcset'; ?>="<?php echo esc_attr( $image['srcset'] ); ?>"<?php endif; ?>
					data-ratio="<?php echo esc_attr( $image['ratio'] ); ?>"
					sizes="100vw"
					alt=""
					<?php if ( 0 === $index ) : ?>loading="eager" fetchpriority="high"<?php else : ?>loading="lazy"<?php endif; ?>
					decoding="async">
			</div>
		<?php endforeach; ?>
	</div>
</div>
