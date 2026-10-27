<?php
/**
 * Reusable Meetings Hero component.
 *
 * Expected arguments:
 * - title: Required plain-text hero heading; line breaks are preserved.
 * - heading_level: Optional h1 or h2. Defaults to h2.
 * - text: Optional plain-text supporting copy.
 * - image_id: Optional background image attachment ID.
 * - links: Optional ACF link arrays; at most two glass buttons are shown.
 * - scroll_target: Optional fragment ID for the "Scroll for more" link.
 * - icon_id: Optional SVG attachment ID; falls back to the Figma artwork.
 *
 * @package LTT_Dive_In
 */

$title = str_replace( array( "\r\n", "\r" ), "\n", (string) ( $args['title'] ?? '' ) );
$title = trim( $title );

if ( '' === $title ) {
	return;
}

$heading_level = in_array( $args['heading_level'] ?? '', array( 'h1', 'h2' ), true ) ? $args['heading_level'] : 'h2';
$text          = trim( (string) ( $args['text'] ?? '' ) );
$image_id      = absint( $args['image_id'] ?? 0 );
$scroll_target = trim( (string) ( $args['scroll_target'] ?? '' ) );
$icon_id       = absint( $args['icon_id'] ?? 0 );
$icon_url      = $icon_id && 'image/svg+xml' === get_post_mime_type( $icon_id ) ? wp_get_attachment_url( $icon_id ) : '';
$hero_id       = wp_unique_id( 'meetings-hero-' );
$hero_class    = 'meetings-hero';

if ( '' === $scroll_target ) {
	$hero_class .= ' meetings-hero--no-scroll';
}

$hero_links    = array_slice(
	array_filter(
		(array) ( $args['links'] ?? array() ),
		static function ( $link ) {
			return is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] );
		}
	),
	0,
	2
);
?>
<section id="<?php echo esc_attr( $hero_id ); ?>" class="<?php echo esc_attr( $hero_class ); ?>" aria-labelledby="<?php echo esc_attr( $hero_id ); ?>-title">
	<div class="meetings-hero__media" aria-hidden="true">
		<?php if ( $image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'full',
				false,
				array(
					'class'         => 'meetings-hero__image',
					'alt'           => '',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '100vw',
				)
			);
			?>
		<?php endif; ?>
	</div>

	<div class="meetings-hero__art<?php echo $icon_url ? ' meetings-hero__art--custom' : ''; ?>" aria-hidden="true">
		<?php if ( ! $icon_url ) : ?>
			<picture class="meetings-hero__icon">
				<source media="(max-width: 767.98px)" srcset="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/hero/meetings-hero-icon-mobile.svg' ); ?>" type="image/svg+xml">
				<img src="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/hero/meetings-hero-icon.svg' ); ?>" width="500" height="743" alt="" decoding="async">
			</picture>
		<?php else : ?>
			<div class="meetings-hero__icon meetings-hero__icon--custom">
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="" decoding="async">
			</div>
		<?php endif; ?>
		<div class="meetings-hero__wash"></div>
	</div>

	<div class="meetings-hero__content">
		<<?php echo esc_html( $heading_level ); ?> id="<?php echo esc_attr( $hero_id ); ?>-title" class="meetings-hero__title"><?php echo esc_html( $title ); ?></<?php echo esc_html( $heading_level ); ?>>

		<?php if ( $text ) : ?>
			<p class="meetings-hero__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>

		<?php if ( $hero_links ) : ?>
			<div class="meetings-hero__actions">
				<?php foreach ( $hero_links as $hero_link ) : ?>
					<a class="ltt-button ltt-button--glass" href="<?php echo esc_url( $hero_link['url'] ); ?>"<?php echo ! empty( $hero_link['target'] ) ? ' target="' . esc_attr( $hero_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $hero_link['title'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $scroll_target ) : ?>
		<a class="meetings-hero__scroll" href="#<?php echo esc_attr( ltrim( $scroll_target, '#' ) ); ?>" x-data="lttScrollLink" x-on:click="scroll">
			<span><?php esc_html_e( 'Scroll for more', 'ltt-dive-in' ); ?></span>
			<img src="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/header/scroll-arrow.svg' ); ?>" alt="" width="14" height="12">
		</a>
	<?php endif; ?>
</section>
