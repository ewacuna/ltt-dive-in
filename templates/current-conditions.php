<?php
/**
 * Template Name: Current Conditions
 * Template Post Type: page
 *
 * Tabs are child pages using this template: they share the parent's compact hero and section menu, and
 * each renders its own title as the page H1 so it remains a complete page when opened directly.
 *
 * @package LTT_Dive_In
 */

get_header();
$page_main_classes = 'site-main site-main--has-hero page-main current-conditions';
$is_tab            = ltt_dive_in_get_current_conditions_hub_id() !== get_queried_object_id();

if ( 'overlay' === ltt_dive_in_get_page_header_background() ) {
	$page_main_classes .= ' page-main--header-overlay';
}
?>
<main id="primary" class="<?php echo esc_attr( $page_main_classes ); ?>">
	<?php get_template_part( 'template-parts/sections/current-conditions-hero', null, ltt_dive_in_get_current_conditions_hero_args() ); ?>
	<?php
	get_template_part(
		'template-parts/components/section-navigation',
		null,
		array(
			'navigation' => ltt_dive_in_get_section_navigation(),
			'seamless'   => true,
		)
	);
	?>
	<div id="page-content" class="page-main__content current-conditions__content" data-seamless-region="content">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<?php if ( $is_tab ) : ?>
				<header class="current-conditions__intro">
					<h1 class="current-conditions__title"><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="current-conditions__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</header>
			<?php endif; ?>
			<?php
			get_template_part( 'template-parts/page/content', 'page' );
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
