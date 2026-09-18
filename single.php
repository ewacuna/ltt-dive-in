<?php
/**
 * Single post template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main site-main--has-hero">
	<?php get_template_part( 'template-parts/sections/hero', null, ltt_dive_in_get_page_hero_args() ); ?>
	<div id="page-content" class="container content-with-sidebar">
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
	</div>
</main>
<?php
get_footer();
