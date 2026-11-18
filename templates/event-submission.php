<?php
/**
 * Template Name: Event Submission
 * Template Post Type: page
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main page-main event-submission-page">
	<?php
	while ( have_posts() ) :
		the_post();

		$submission_settings = ltt_dive_in_get_event_submission_settings( get_the_ID() );

		get_template_part(
			'template-parts/sections/current-conditions-hero',
			null,
			array(
				'title'         => $submission_settings['title'],
				'heading_level' => 'h1',
				'text'          => $submission_settings['text'],
			)
		);
		?>
		<div id="page-content" class="page-main__content">
			<?php get_template_part( 'template-parts/page/content', 'page' ); ?>
			<?php get_template_part( 'template-parts/sections/event-submission-form', null, $submission_settings ); ?>
		</div>
	<?php endwhile; ?>
</main>
<?php
get_footer();
