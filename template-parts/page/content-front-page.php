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
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="hero-media"><?php the_post_thumbnail( 'full' ); ?></div>
	<?php endif; ?>

	<div class="container content-narrow">
		<header class="entry-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>
		<div class="entry-content"><?php the_content(); ?></div>
	</div>
</article>
