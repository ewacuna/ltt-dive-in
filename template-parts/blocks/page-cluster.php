<?php
/**
 * Manual Page Cluster variants.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = ! empty( $is_preview );
$variant = get_field( 'ltt_dive_in_page_cluster_variant' );
if ( ! in_array( $variant, array( 'hub', 'features' ), true ) ) {
	$variant = 'hub';
}
$title = trim( (string) get_field( 'ltt_dive_in_page_cluster_title' ) );
$label = trim( (string) get_field( 'ltt_dive_in_page_cluster_label' ) );
$body = trim( (string) get_field( 'ltt_dive_in_page_cluster_body' ) );
$cards = get_field( 'ltt_dive_in_page_cluster_cards' );
$cards = is_array( $cards ) ? array_slice( array_filter( $cards, 'is_array' ), 0, 4 ) : array();
$primary_cta = get_field( 'ltt_dive_in_page_cluster_primary_cta' );
$secondary_cta = get_field( 'ltt_dive_in_page_cluster_secondary_cta' );
$has_cta = static function ( $link ) {
	return is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] ) && (bool) esc_url( $link['url'] );
};
$has_actions = $has_cta( $primary_cta ) || $has_cta( $secondary_cta );
$id = ! empty( $block['anchor'] ) ? sanitize_title( $block['anchor'] ) : wp_unique_id( 'page-cluster-' );
$render_cta = static function ( $link, $modifier ) use ( $is_preview ) {
	if ( ! is_array( $link ) || empty( $link['title'] ) || empty( $link['url'] ) || ! esc_url( $link['url'] ) ) {
		return;
	}
	$tag = $is_preview ? 'span' : 'a';
	?>
	<<?php echo esc_html( $tag ); ?> class="ltt-button ltt-button--<?php echo esc_attr( $modifier ); ?>"<?php if ( ! $is_preview ) : ?> href="<?php echo esc_url( $link['url'] ); ?>"<?php if ( '_blank' === ( $link['target'] ?? '' ) ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?><?php endif; ?>><?php echo esc_html( $link['title'] ); ?></<?php echo esc_html( $tag ); ?>>
	<?php
};
$render_actions = static function () use ( $has_actions, $primary_cta, $secondary_cta, $render_cta ) {
	if ( ! $has_actions ) {
		return;
	}
	?>
	<div class="page-cluster__actions"><?php $render_cta( $primary_cta, 'primary-outline' ); ?><?php $render_cta( $secondary_cta, 'secondary-outline' ); ?></div>
	<?php
};
if ( ! $title || ! $cards ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add a title and cards to preview Page Cluster.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}
if ( 'features' === $variant && ! $is_preview && function_exists( 'ltt_dive_in_enqueue_page_cluster_carousel_assets' ) ) {
	ltt_dive_in_enqueue_page_cluster_carousel_assets();
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="page-cluster page-cluster--<?php echo esc_attr( $variant ); ?><?php echo $has_actions ? ' page-cluster--has-actions' : ''; ?><?php echo isset( $block['align'] ) && 'full' === $block['align'] ? ' alignfull' : ''; ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="page-cluster__container">
		<header class="page-cluster__header">
			<div class="page-cluster__intro">
				<?php if ( $label ) : ?><p class="page-cluster__label"><?php echo esc_html( $label ); ?></p><?php endif; ?>
				<h2 id="<?php echo esc_attr( $id ); ?>-title" class="page-cluster__title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( 'hub' === $variant && $body ) : ?><p class="page-cluster__body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</div>
		</header>
		<?php if ( 'hub' === $variant ) { $render_actions(); } ?>
		<?php if ( 'features' === $variant ) : ?><div class="page-cluster__viewport" role="region" aria-label="<?php esc_attr_e( 'Feature cards', 'ltt-dive-in' ); ?>"><?php endif; ?>
		<div class="page-cluster__grid" id="<?php echo esc_attr( $id ); ?>-cards">
			<?php foreach ( $cards as $card ) : ?>
				<article class="page-cluster__card">
					<?php if ( ! empty( $card['image'] ) ) : ?>
						<div class="page-cluster__image"><?php echo wp_get_attachment_image( absint( $card['image'] ), 'large', false, 'hub' === $variant ? array( 'alt' => '', 'sizes' => '(max-width: 767.98px) 50vw, 25vw' ) : array( 'sizes' => '(max-width: 767.98px) 85vw, 25vw' ) ); ?></div>
					<?php endif; ?>
					<div class="page-cluster__copy">
						<?php if ( ! empty( $card['title'] ) ) : ?><h3 class="page-cluster__card-title"><?php echo esc_html( $card['title'] ); ?></h3><?php endif; ?>
						<?php if ( ! empty( $card['copy'] ) ) : ?><p class="page-cluster__card-body"><?php echo esc_html( $card['copy'] ); ?></p><?php endif; ?>
						<?php if ( 'hub' === $variant ) { $render_cta( $card['cta'] ?? array(), 'glass' ); } ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php if ( 'features' === $variant ) : ?></div><?php endif; ?>
		<?php if ( 'features' === $variant ) : ?>
			<?php if ( ! $is_preview ) : ?>
				<div class="page-cluster__controls" hidden>
					<div class="page-cluster__pagination ltt-carousel-indicators" data-cluster-pagination aria-label="<?php esc_attr_e( 'Choose a feature', 'ltt-dive-in' ); ?>"></div>
					<div class="page-cluster__arrows">
						<button type="button" class="ltt-slider-navigation ltt-slider-navigation--white" data-cluster-previous aria-controls="<?php echo esc_attr( $id ); ?>-cards" aria-label="<?php esc_attr_e( 'Previous feature', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
						<button type="button" class="ltt-slider-navigation ltt-slider-navigation--white" data-cluster-next aria-controls="<?php echo esc_attr( $id ); ?>-cards" aria-label="<?php esc_attr_e( 'Next feature', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
					</div>
				</div>
			<?php endif; ?>
			<?php $render_actions(); ?>
		<?php endif; ?>
	</div>
</section>
