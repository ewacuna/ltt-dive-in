<?php
/**
 * Internal page hero.
 *
 * Expected arguments:
 * - title: Hero heading.
 * - heading_level: Optional heading tag, h1 or h2. Defaults to h2.
 * - text: Optional supporting copy.
 * - image_id: Optional background image attachment ID.
 * - links: Optional ACF link arrays rendered as glass buttons.
 * - scroll_target: Optional fragment ID for the "Scroll for more" link.
 *
 * @package LTT_Dive_In
 */

$title         = $args['title'] ?? '';
$heading_level = in_array( $args['heading_level'] ?? '', array( 'h1', 'h2' ), true ) ? $args['heading_level'] : 'h2';
$text          = $args['text'] ?? '';
$image_id      = absint( $args['image_id'] ?? 0 );
$scroll_target = $args['scroll_target'] ?? '';
$hero_links    = array_filter(
	(array) ( $args['links'] ?? array() ),
	static function ( $link ) {
		return is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] );
	}
);

if ( ! $title ) {
	return;
}
?>
<section class="page-hero<?php echo $image_id ? '' : ' page-hero--no-image'; ?>">
	<?php if ( $image_id ) : ?>
		<div class="page-hero__media" aria-hidden="true">
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'full',
				false,
				array(
					'class'         => 'page-hero__image',
					'alt'           => '',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '100vw',
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="page-hero__content">
		<<?php echo esc_html( $heading_level ); ?> class="page-hero__title"><?php echo esc_html( $title ); ?></<?php echo esc_html( $heading_level ); ?>>

		<?php if ( $text ) : ?>
			<p class="page-hero__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>

		<?php if ( $hero_links ) : ?>
			<div class="page-hero__actions">
				<?php foreach ( $hero_links as $hero_link ) : ?>
					<a class="ltt-button ltt-button--glass" href="<?php echo esc_url( $hero_link['url'] ); ?>"<?php echo ! empty( $hero_link['target'] ) ? ' target="' . esc_attr( $hero_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $hero_link['title'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $scroll_target ) : ?>
		<a class="page-hero__scroll" href="#<?php echo esc_attr( $scroll_target ); ?>">
			<span><?php esc_html_e( 'Scroll for more', 'ltt-dive-in' ); ?></span>
			<img src="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/header/scroll-arrow.svg' ); ?>" alt="" width="14" height="12">
		</a>
	<?php endif; ?>
</section>
