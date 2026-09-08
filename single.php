<?php
/**
 * Single post template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main container content-with-sidebar">
	<div class="content-area">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content/content', 'single' );

			the_post_navigation();

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
	<?php get_sidebar(); ?>
</main>
<?php
get_footer();
