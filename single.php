<?php
/**
 * Single post template.
 *
 * The Stories Hero carries the title, published month, read time, excerpt,
 * and featured image, followed by the article body and the end-of-post
 * Page Driver (inc/blog-settings.php). Comments are intentionally not rendered.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main single-main">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/sections/stories-hero' );
		?>
		<div id="page-content" class="single-main__content">
			<?php
			get_template_part( 'template-parts/content/content', 'single' );
			?>
		</div>
		<?php
		$page_driver = ltt_dive_in_get_post_page_driver( get_the_ID() );
		if ( $page_driver ) {
			get_template_part( 'template-parts/components/page-driver-cluster', null, $page_driver );
		}
	endwhile;
	?>
</main>
<?php
get_footer();
