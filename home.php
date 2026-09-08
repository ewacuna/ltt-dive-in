<?php
/**
 * Blog posts page template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main container content-with-sidebar">
	<div class="content-area">
		<header class="page-header">
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/content', get_post_type() );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</main>
<?php
get_footer();
