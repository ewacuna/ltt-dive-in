<?php
/**
 * Empty content state.
 *
 * @package LTT_Dive_In
 */
?>
<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing found', 'ltt-dive-in' ); ?></h1>
	</header>
	<div class="page-content">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'No results matched your search. Try different keywords.', 'ltt-dive-in' ); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'There is no content here yet.', 'ltt-dive-in' ); ?></p>
		<?php endif; ?>
	</div>
</section>
