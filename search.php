<?php
/**
 * Search results template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main container">
	<header class="page-header">
		<h1 class="page-title">
			<?php
			printf(
				/* translators: %s: search term. */
				esc_html__( 'Search results for: %s', 'ltt-dive-in' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content', 'search' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
