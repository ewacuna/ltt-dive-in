<?php
/**
 * Template Name: Full Width
 * Template Post Type: page
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main container">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/page/content', 'page' );
	endwhile;
	?>
</main>
<?php
get_footer();
