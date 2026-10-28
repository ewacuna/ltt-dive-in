<?php
/**
 * Template Name: Current Conditions
 * Template Post Type: page
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
			<?php
			if ( $is_tab ) {
				get_template_part(
					'template-parts/sections/current-conditions-intro',
					null,
					array(
						'title' => get_the_title(),
						'text'  => has_excerpt() ? get_the_excerpt() : '',
					)
				);
			}

			get_template_part( 'template-parts/page/content', 'page' );
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
