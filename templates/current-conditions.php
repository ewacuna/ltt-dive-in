<?php
/**
 * Template Name: Current Conditions
 * Template Post Type: page
 *
 * @package LTT_Dive_In
 */

get_header();
$page_main_classes = 'site-main site-main--has-hero page-main current-conditions';

if ( 'overlay' === ltt_dive_in_get_page_header_background() ) {
	$page_main_classes .= ' page-main--header-overlay';
}
?>
<main id="primary" class="<?php echo esc_attr( $page_main_classes ); ?>">
	<?php if ( 'meetings' === ltt_dive_in_get_page_header_hero_variant() ) : ?>
		<?php get_template_part( 'template-parts/sections/meetings-hero', null, ltt_dive_in_get_meetings_hero_args() ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/sections/hero', null, ltt_dive_in_get_page_hero_args() ); ?>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/components/section-navigation', null, array( 'navigation' => ltt_dive_in_get_section_navigation() ) ); ?>
	<div id="page-content" class="page-main__content current-conditions__content">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/page/content', 'page' );
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
