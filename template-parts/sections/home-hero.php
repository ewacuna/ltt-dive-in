<?php
/**
 * Homepage video hero.
 *
 * @package LTT_Dive_In
 */

$hero_assets_uri = LTT_DIVE_IN_URI . '/assets/images/header';
$front_page_id   = (int) get_option( 'page_on_front' );
$video_id        = absint( ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_video' ) );
$poster_id       = absint( ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_poster' ) );
$video_url       = $video_id ? wp_get_attachment_url( $video_id ) : LTT_DIVE_IN_URI . '/assets/video/home-hero.mp4';
$poster_url      = $poster_id ? wp_get_attachment_image_url( $poster_id, 'full' ) : '';
$title           = ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_title' );
$description     = ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_description' );
$primary_link    = ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_primary_link', array() );
$secondary_link  = ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_secondary_link', array() );
$scroll_label    = ltt_dive_in_get_home_hero_field( 'ltt_dive_in_home_hero_scroll_label' );

if ( ! $video_url || ( $video_id && 'video/mp4' !== get_post_mime_type( $video_id ) ) ) {
	$video_url = LTT_DIVE_IN_URI . '/assets/video/home-hero.mp4';
}

if ( ! $description && $front_page_id && has_excerpt( $front_page_id ) ) {
	$description = get_the_excerpt( $front_page_id );
}

if ( ! $title && $front_page_id ) {
	$title = get_the_title( $front_page_id );
}

if ( ! $title ) {
	$title = get_bloginfo( 'name' );
}

$hero_links = array_filter(
	array( $primary_link, $secondary_link ),
	static function ( $link ) {
		return is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] );
	}
);
?>
<section class="home-hero" aria-labelledby="home-hero-title">
	<div class="home-hero__media" aria-hidden="true">
		<?php if ( $video_url ) : ?>
			<video
				class="home-hero__video"
				x-init="
					const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
					const syncMotion = (event) => {
						if (event.matches) {
							$el.pause();
						} else {
							const playPromise = $el.play();
							if (playPromise) playPromise.catch(() => {});
						}
					};
					syncMotion(motionQuery);
					motionQuery.addEventListener('change', syncMotion);
				"
				autoplay
				muted
				loop
				playsinline
				preload="metadata"
				<?php echo $poster_url ? ' poster="' . esc_url( $poster_url ) . '"' : ''; ?>
			>
				<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
			</video>
		<?php endif; ?>
		<div class="home-hero__overlay"></div>
	</div>

	<div class="home-hero__content">
		<h1 id="home-hero-title" class="home-hero__title"><?php echo esc_html( $title ); ?></h1>

		<?php if ( $description ) : ?>
			<p class="home-hero__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<?php if ( $hero_links ) : ?>
			<div class="home-hero__actions">
				<?php foreach ( $hero_links as $hero_link ) : ?>
					<a class="home-hero__button" href="<?php echo esc_url( $hero_link['url'] ); ?>"<?php echo ! empty( $hero_link['target'] ) ? ' target="' . esc_attr( $hero_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $hero_link['title'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $scroll_label ) : ?>
		<a class="home-hero__scroll" href="#front-page-content">
			<span><?php echo esc_html( $scroll_label ); ?></span>
			<img src="<?php echo esc_url( $hero_assets_uri . '/scroll-arrow.svg' ); ?>" alt="" width="14" height="12">
		</a>
	<?php endif; ?>
</section>
