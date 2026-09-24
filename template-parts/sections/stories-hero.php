<?php
/**
 * Stories Hero (Figma "Blog Post Header": Stories Hero Desktop / Mobile).
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
	</header>

	<?php if ( $image_id ) : ?>
		<div class="stories-hero__media">
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
		</div>
	<?php endif; ?>
</section>
