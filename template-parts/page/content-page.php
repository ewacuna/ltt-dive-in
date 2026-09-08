<?php
/**
 * Default page content.
 *
 * @package LTT_Dive_In
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-entry' ); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>

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

	<?php edit_post_link( esc_html__( 'Edit this page', 'ltt-dive-in' ), '<footer class="entry-footer">', '</footer>' ); ?>
</article>
