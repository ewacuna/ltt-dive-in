<?php
/**
 * Default page template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main container content-narrow">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/page/content', 'page' );

		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
