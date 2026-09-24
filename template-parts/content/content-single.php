<?php
/**
 * Single post content.
 *
 * @package LTT_Dive_In
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-entry' ); ?>>
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
</article>
