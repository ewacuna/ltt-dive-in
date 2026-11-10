<?php
/**
 * Stories Hero.
 *
 * Renders the current post's title, published month, read time, excerpt,
 * and featured image. Unlike the Internal Page Hero, it sits below the site
 * header on a white surface. Must be called inside The Loop.
 *
 * @package LTT_Dive_In
 */

$image_id = get_post_thumbnail_id();
?>
<section class="stories-hero<?php echo $image_id ? '' : ' stories-hero--no-image'; ?>">
	<header class="stories-hero__header">
		<?php the_title( '<h1 class="stories-hero__title">', '</h1>' ); ?>

		<div class="stories-hero__meta">
			<?php ltt_dive_in_posted_month(); ?>
			<?php ltt_dive_in_reading_time(); ?>
		</div>

		<?php if ( has_excerpt() ) : ?>
			<p class="stories-hero__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/content/ai-summary' ); ?>
	</header>

	<?php if ( $image_id ) : ?>
		<?php
		// Photo caption: the image's Media Library caption and photo credit.
		$caption = trim( (string) wp_get_attachment_caption( $image_id ) );
		$credit  = function_exists( 'get_field' ) ? trim( (string) get_field( 'field_ltt_dive_in_photo_credit', $image_id ) ) : '';
		?>
		<figure class="stories-hero__media">
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'full',
				false,
				array(
					'class'         => 'stories-hero__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '100vw',
				)
			);
			?>
			<?php if ( $caption || $credit ) : ?>
				<figcaption class="stories-hero__caption">
					<?php if ( $caption ) : ?>
						<span class="stories-hero__caption-text"><?php echo wp_kses( $caption, array( 'a' => array( 'href' => true ), 'em' => array(), 'strong' => array() ) ); ?></span>
					<?php endif; ?>
					<?php if ( $credit ) : ?>
						<span class="stories-hero__credit"><strong><?php esc_html_e( 'Photography:', 'ltt-dive-in' ); ?></strong> <?php echo esc_html( $credit ); ?></span>
					<?php endif; ?>
				</figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>
</section>
