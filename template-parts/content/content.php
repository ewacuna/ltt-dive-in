<?php
/**
 * Default card used in index and archive loops.
 *
 * @package LTT_Dive_In
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="post-card__image" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'large' ); ?>
		</a>
	<?php endif; ?>

	<div class="post-card__body">
		<header class="entry-header">
			<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			<div class="entry-meta">
				<?php ltt_dive_in_posted_on(); ?>
			</div>
		</header>

		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div>
	</div>
</article>
