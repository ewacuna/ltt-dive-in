<?php
/**
 * Single post template.
 *
 * The Stories Hero carries the title, published month, read time, excerpt,
 * and featured image, followed by the article body. Comments are
 * intentionally not rendered.
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

			the_post_navigation(
				array(
					'class'     => 'post-navigation single-main__navigation',
					'prev_text' => '<span class="post-navigation__label">' . esc_html__( 'Previous post', 'ltt-dive-in' ) . '</span> <span class="post-navigation__title">%title</span>',
					'next_text' => '<span class="post-navigation__label">' . esc_html__( 'Next post', 'ltt-dive-in' ) . '</span> <span class="post-navigation__title">%title</span>',
				)
			);
			?>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
