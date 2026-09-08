<?php
/**
 * Single post content.
 *
 * @package LTT_Dive_In
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<div class="entry-meta">
			<?php ltt_dive_in_posted_on(); ?>
			<?php ltt_dive_in_posted_by(); ?>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="post-thumbnail"><?php the_post_thumbnail( 'full' ); ?></figure>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();
		wp_link_pages(
			array(
				'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'ltt-dive-in' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<footer class="entry-footer"><?php ltt_dive_in_entry_footer(); ?></footer>
</article>
