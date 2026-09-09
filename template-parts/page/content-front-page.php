<?php
/**
 * Front page content.
 *
 * Replace or split this file into additional template parts as the homepage grows.
 *
 * @package LTT_Dive_In
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'front-page-entry' ); ?>>
	<div id="front-page-content" class="container content-narrow">
		<div class="entry-content"><?php the_content(); ?></div>
	</div>
</article>
