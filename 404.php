<?php
/**
 * Not-found template.
 *
 * @package LTT_Dive_In
 */

$background_config = ltt_dive_in_get_error_background_config( '404' );

get_header();
?>
<main id="primary" class="site-main ltt-error-page ltt-error-page--404"
	<?php if ( $background_config['images'] ) : ?>
		data-background-images
		data-background-interval="<?php echo esc_attr( $background_config['interval'] ); ?>"
	<?php endif; ?>
>
	<?php if ( $background_config['images'] ) : ?>
		<?php get_template_part( 'template-parts/components/error-background', null, array( 'images' => $background_config['images'] ) ); ?>
	<?php endif; ?>
	<div class="ltt-error-page__intro">
		<h1 class="ltt-error-page__title"><?php esc_html_e( 'You dove in too deep...', 'ltt-dive-in' ); ?></h1>
	</div>
	<div class="ltt-error-page__content">
		<p class="ltt-error-page__message">
			<?php esc_html_e( 'We couldn’t find what you were looking for.', 'ltt-dive-in' ); ?><br>
			<?php esc_html_e( 'Use the search below to find your way:', 'ltt-dive-in' ); ?>
		</p>
		<form class="ltt-error-page__search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="ltt-search-bar ltt-search-bar--error">
				<input id="ltt-error-search" class="ltt-search-bar__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search', 'ltt-dive-in' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'ltt-dive-in' ); ?>">
				<button class="ltt-search-bar__clear" type="button" data-error-search-clear aria-label="<?php esc_attr_e( 'Clear search', 'ltt-dive-in' ); ?>" hidden></button>
				<button class="ltt-search-bar__submit" type="submit" aria-label="<?php esc_attr_e( 'Submit search', 'ltt-dive-in' ); ?>">
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/header/search-icon.svg' ) ); ?>" alt="" width="22" height="17">
				</button>
			</span>
		</form>
	</div>
</main>
<?php
get_footer();
