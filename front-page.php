<?php
/**
 * Static front page template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main front-page-main">
	<?php get_template_part( 'template-parts/sections/home', 'hero' ); ?>
	<?php get_template_part( 'template-parts/sections/home', 'activities' ); ?>
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/page/content', 'front-page' );
	endwhile;
	?>
</main>
<?php
get_footer();
