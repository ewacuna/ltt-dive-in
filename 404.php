<?php
/**
 * Not-found template.
 *
 * @package LTT_Dive_In
 */

get_header();
?>
<main id="primary" class="site-main container content-narrow">
	<section class="error-404 not-found">
		<header class="page-header">
			<p class="eyebrow"><?php esc_html_e( '404 error', 'ltt-dive-in' ); ?></p>
			<h1 class="page-title"><?php esc_html_e( 'That page could not be found.', 'ltt-dive-in' ); ?></h1>
		</header>
		<div class="page-content">
			<p><?php esc_html_e( 'Try searching the site or return to the homepage.', 'ltt-dive-in' ); ?></p>
			<?php get_search_form(); ?>
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'ltt-dive-in' ); ?></a>
		</div>
	</section>
</main>
<?php
get_footer();
