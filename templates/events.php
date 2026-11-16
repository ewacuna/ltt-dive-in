<?php
/**
 * Template Name: Events
 * Template Post Type: page
 *
 * Renders the events listing, or one event when the URL names an event.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main page-main events-page">
	<?php
	while ( have_posts() ) :
		the_post();

		$current_event = ltt_dive_in_get_current_event();

		if ( $current_event ) :
			get_template_part(
				'template-parts/sections/event-detail',
				null,
				array(
					'event'    => $current_event['event'],
					'details'  => $current_event['details'],
					'page_id'  => get_the_ID(),
					'page_url' => get_permalink(),
				)
			);
			?>
			<div id="page-content" class="page-main__content">
				<?php echo ltt_dive_in_get_event_detail_page_blocks( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks escape their own output. ?>
			</div>
			<?php
		else :
			$events_settings = ltt_dive_in_get_events_page_settings( get_the_ID() );

			get_template_part( 'template-parts/sections/events-intro', null, $events_settings );
			get_template_part( 'template-parts/components/section-navigation', null, array( 'navigation' => ltt_dive_in_get_section_navigation() ) );
			get_template_part(
				'template-parts/sections/events-browser',
				null,
				array(
					'page_id'     => get_the_ID(),
					'submit_link' => $events_settings['submit_link'],
				)
			);
			?>
			<div id="page-content" class="page-main__content">
				<?php get_template_part( 'template-parts/page/content', 'page' ); ?>
			</div>
			<?php
		endif;
	endwhile;
	?>
</main>
<?php
get_footer();
