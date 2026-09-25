<?php
/**
 * Static Image Cluster ACF block renderer.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = isset( $is_preview ) && $is_preview;
$block_id   = isset( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id( 'static-image-cluster-' );
$anchor     = isset( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : '';
$alignment  = isset( $block['align'] ) && 'full' === $block['align'] ? ' alignfull' : '';
$block_data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
$rest_block_id = md5( wp_json_encode( $block_data ) );
$get_value  = static function ( $name ) use ( $block_data ) {
	$value = get_field( $name );

	return ( false === $value || null === $value ) && array_key_exists( $name, $block_data ) ? $block_data[ $name ] : $value;
};
$normalize_link = static function ( $link ) {
	if ( ! is_array( $link ) || empty( $link['title'] ) || empty( $link['url'] ) ) {
		return array();
	}

	return array(
		'title'  => trim( (string) $link['title'] ),
		'url'    => (string) $link['url'],
		'target' => ! empty( $link['target'] ) ? (string) $link['target'] : '',
	);
};
$link_attributes = static function ( $link ) {
	if ( empty( $link['target'] ) ) {
		return '';
	}

	return ' target="' . esc_attr( $link['target'] ) . '" rel="noopener noreferrer"';
};
$legacy_ctas_from_block_data = static function ( $data ) use ( $normalize_link ) {
	if ( ! is_array( $data ) ) {
		return array();
	}

	$links = array();
	$rows  = $data['ltt_dive_in_static_image_cluster_ctas'] ?? array();

	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || empty( $row['enabled'] ) ) {
				continue;
			}

			$link = $normalize_link( $row['link'] ?? array() );

			if ( $link ) {
				$links[] = $link;
			}
		}

		return array_slice( $links, 0, 3 );
	}

	for ( $index = 0, $count = absint( $rows ); $index < $count; ++$index ) {
		$prefix  = 'ltt_dive_in_static_image_cluster_ctas_' . $index . '_';
		$enabled = $data[ $prefix . 'enabled' ] ?? 0;
		$link    = $normalize_link( $data[ $prefix . 'link' ] ?? array() );

		if ( $enabled && $link ) {
			$links[] = $link;
		}
	}

	return array_slice( $links, 0, 3 );
};

$variant = $get_value( 'ltt_dive_in_static_image_cluster_variant' );
$limits  = function_exists( 'ltt_dive_in_get_static_image_cluster_image_limits' ) ? ltt_dive_in_get_static_image_cluster_image_limits() : array();

if ( ! is_string( $variant ) || ! isset( $limits[ $variant ] ) ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Choose a Static Image Cluster variant to preview this block.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

$selected_image_ids = array_values( array_filter( array_map( 'absint', (array) $get_value( 'ltt_dive_in_static_image_cluster_images' ) ) ) );
$minimum            = (int) $limits[ $variant ]['min'];
$display_limit      = (int) $limits[ $variant ]['display'];

if ( count( $selected_image_ids ) < $minimum ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add enough images for the selected Static Image Cluster variant.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

$image_ids = $display_limit ? array_slice( $selected_image_ids, 0, $display_limit ) : $selected_image_ids;
$image_details = (array) $get_value( 'ltt_dive_in_static_image_cluster_image_details' );
$images = array();

foreach ( $image_ids as $image_index => $image_id ) {
	$detail = isset( $image_details[ $image_index ] ) && is_array( $image_details[ $image_index ] ) ? $image_details[ $image_index ] : array();
	$alt    = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
	$full   = wp_get_attachment_image_src( $image_id, 'full' );

	if ( '' === $alt || ! $full || ! wp_attachment_is_image( $image_id ) ) {
		continue;
	}

	$images[] = array(
		'id'          => $image_id,
		'alt'         => $alt,
		'title'       => 'inspired' === $variant ? trim( (string) ( $detail['title'] ?? '' ) ) : '',
		'description' => 'inspired' === $variant ? trim( (string) ( $detail['description'] ?? '' ) ) : '',
		'full'    => (string) $full[0],
		'width'   => (int) $full[1],
		'height'  => (int) $full[2],
		'srcset'  => (string) wp_get_attachment_image_srcset( $image_id, 'full' ),
	);
}

if ( count( $images ) !== count( $image_ids ) ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Every image needs alternative text in the Media Library.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

$ctas = array_filter(
	array(
		$normalize_link( $get_value( 'ltt_dive_in_static_image_cluster_primary_link' ) ),
		$normalize_link( $get_value( 'ltt_dive_in_static_image_cluster_secondary_link' ) ),
		$normalize_link( $get_value( 'ltt_dive_in_static_image_cluster_third_link' ) ),
	)
);

if ( ! $ctas ) {
	$ctas = $legacy_ctas_from_block_data( $block_data );
}

$heading        = trim( (string) $get_value( 'ltt_dive_in_static_image_cluster_heading' ) );
$intro          = trim( (string) $get_value( 'ltt_dive_in_static_image_cluster_intro' ) );
$hero_caption   = $intro ? $intro : trim( (string) $get_value( 'ltt_dive_in_static_image_cluster_hero_caption' ) );
$has_lightbox   = count( $images ) >= 3;
$section_id     = $anchor ? $anchor : 'static-image-cluster-' . $block_id;
$heading_id     = $section_id . '-title';
$classes        = 'static-image-cluster static-image-cluster--' . $variant . $alignment . ( $has_lightbox ? ' static-image-cluster--has-lightbox' : '' ) . ( $is_preview ? ' static-image-cluster--preview' : '' );
$post_id        = get_the_ID();
$can_load_more  = 'inspired' === $variant && ! $is_preview && $post_id && $rest_block_id;
$rendered_images = $can_load_more ? array_slice( $images, 0, 6 ) : $images;
$image_batches   = 'inspired' === $variant ? array_chunk( $rendered_images, 6, true ) : array( $rendered_images );
$button_classes = array( 'primary-outline', 'secondary-outline', 'primary-fill' );
$photoswipe_lightbox_url = get_theme_file_uri( 'assets/vendor/photoswipe/photoswipe-lightbox.esm.js' );
$photoswipe_core_url     = get_theme_file_uri( 'assets/vendor/photoswipe/photoswipe.esm.js' );
$close_icon_url          = get_theme_file_uri( 'assets/images/header/close-icon.svg' );
$arrow_icon_url          = get_theme_file_uri( 'assets/images/icons/lightbox-arrow.svg' );
$location_icon_url       = get_theme_file_uri( 'assets/images/icons/location.svg' );
$get_image_sizes = static function ( $index ) use ( $variant ) {
	if ( in_array( $variant, array( 'hero_caption', 'one_up' ), true ) ) {
		return '100vw';
	}

	if ( 'two_up' === $variant ) {
		return '(max-width: 767.98px) calc(100vw - 40px), calc(50vw - 6px)';
	}

	if ( 'three_up' === $variant ) {
		return '(max-width: 767.98px) calc(100vw - 40px), calc(33.333vw - 8px)';
	}

	if ( 'inline_four_up' === $variant ) {
		return '(max-width: 767.98px) calc(50vw - 5.5px), calc(25vw - 9px)';
	}

	if ( 'staggered_four' === $variant ) {
		return 'calc(50vw - 5.5px)';
	}

	if ( 'five_plus' === $variant ) {
		return '(max-width: 767.98px) calc(100vw - 40px), 296px';
	}

	if ( 'inspired' === $variant ) {
		$desktop_width = in_array( $index % 6, array( 2, 3 ), true ) ? '28vw' : '36vw';

		return '(max-width: 767.98px) calc((100vw - 22px) / 3), ' . $desktop_width;
	}

	if ( 'side_by_side' === $variant ) {
		return '(max-width: 767.98px) 100vw, (max-width: 1199.98px) 45vw, 616px';
	}

	return '100vw';
};

$get_mobile_image_source = static function ( $index ) use ( $variant ) {
	if ( in_array( $variant, array( 'inline_four_up', 'staggered_four' ), true ) ) {
		return 'half';
	}

	if ( 'inspired' === $variant ) {
		return 'half';
	}

	return 'full';
};

$render_image = static function ( $image, $sizes, $mobile_source ) {
	$image_html = wp_get_attachment_image(
		$image['id'],
		'large',
		false,
		array(
			'alt'   => $image['alt'],
			'sizes' => $sizes,
		)
	);

	if ( 'half' === $mobile_source ) {
		$mobile_one_x = wp_get_attachment_image_url( $image['id'], 'medium' );
		$mobile_two_x = wp_get_attachment_image_url( $image['id'], 'medium_large' );
	} else {
		$mobile_one_x = wp_get_attachment_image_url( $image['id'], 'medium_large' );
		$mobile_two_x = wp_get_attachment_image_url( $image['id'], 'large' );
	}

	if ( ! $image_html || ! $mobile_one_x || ! $mobile_two_x ) {
		return $image_html;
	}

	return sprintf(
		'<picture><source media="(max-width: 767.98px)" srcset="%1$s 1x, %2$s 2x">%3$s</picture>',
		esc_url( $mobile_one_x ),
		esc_url( $mobile_two_x ),
		$image_html
	);
};
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $classes ); ?>"<?php echo $heading ? ' aria-labelledby="' . esc_attr( $heading_id ) . '"' : ' aria-label="' . esc_attr__( 'Image gallery', 'ltt-dive-in' ) . '"'; ?><?php if ( $has_lightbox && ! $is_preview ) : ?> data-ltt-photoswipe data-photoswipe-lightbox-module="<?php echo esc_url( $photoswipe_lightbox_url ); ?>" data-photoswipe-core-module="<?php echo esc_url( $photoswipe_core_url ); ?>" data-photoswipe-close-icon="<?php echo esc_url( $close_icon_url ); ?>" data-photoswipe-arrow-icon="<?php echo esc_url( $arrow_icon_url ); ?>" data-photoswipe-location-icon="<?php echo esc_url( $location_icon_url ); ?>" data-photoswipe-inspired="<?php echo 'inspired' === $variant ? 'true' : 'false'; ?>"<?php endif; ?>>
	<div class="static-image-cluster__container"<?php echo 'five_plus' === $variant ? ' data-static-image-cluster-carousel' : ''; ?>>
		<?php if ( 'hero_caption' !== $variant && ( $heading || $intro || $ctas ) ) : ?>
			<header class="static-image-cluster__header">
				<div class="static-image-cluster__header-copy">
					<?php if ( $heading ) : ?>
						<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="static-image-cluster__title"><?php echo esc_html( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $intro ) : ?>
						<p class="static-image-cluster__intro"><?php echo esc_html( $intro ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $ctas ) : ?>
					<div class="static-image-cluster__actions">
						<?php foreach ( $ctas as $index => $cta ) : ?>
							<a class="ltt-button ltt-button--<?php echo esc_attr( $button_classes[ $index ] ); ?>" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo $link_attributes( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $cta['title'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</header>
		<?php endif; ?>

		<div class="static-image-cluster__gallery<?php echo 'five_plus' === $variant ? ' static-image-cluster__gallery--carousel' : ''; ?>">
			<?php if ( 'five_plus' === $variant ) : ?>
				<div class="static-image-cluster__carousel-viewport swiper" role="region" aria-roledescription="<?php esc_attr_e( 'carousel', 'ltt-dive-in' ); ?>" aria-label="<?php esc_attr_e( 'Image gallery', 'ltt-dive-in' ); ?>">
					<div class="static-image-cluster__carousel-track swiper-wrapper" data-static-image-cluster-carousel-track tabindex="0">
			<?php endif; ?>
			<?php foreach ( $image_batches as $image_batch ) : ?>
				<?php if ( 'inspired' === $variant ) : ?>
					<div class="static-image-cluster__inspired-batch">
				<?php endif; ?>
			<?php foreach ( $image_batch as $index => $image ) : ?>
				<?php $image_sizes = $get_image_sizes( $index ); ?>
				<?php $mobile_image_source = $get_mobile_image_source( $index ); ?>
				<figure class="static-image-cluster__item<?php echo 'five_plus' === $variant ? ' swiper-slide' : ''; ?>">
					<?php if ( $has_lightbox ) : ?>
						<a class="static-image-cluster__trigger" href="<?php echo esc_url( $image['full'] ); ?>" data-ltt-photoswipe-trigger data-image-index="<?php echo esc_attr( (string) $index ); ?>" data-pswp-width="<?php echo esc_attr( (string) $image['width'] ); ?>" data-pswp-height="<?php echo esc_attr( (string) $image['height'] ); ?>" aria-haspopup="dialog" aria-label="<?php echo esc_attr( sprintf( __( 'View image %1$d of %2$d: %3$s', 'ltt-dive-in' ), $index + 1, count( $images ), $image['alt'] ) ); ?>">
							<?php echo $render_image( $image, $image_sizes, $mobile_image_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php if ( 'inspired' === $variant ) : ?><span class="static-image-cluster__inspired-info" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/info.svg' ) ); ?>" alt="" /></span><span class="static-image-cluster__inspired-title"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/location.svg' ) ); ?>" alt="" /><?php echo esc_html( $image['title'] ); ?></span><?php endif; ?>
						</a>
					<?php else : ?>
						<?php echo $render_image( $image, $image_sizes, $mobile_image_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>

					<?php if ( 'hero_caption' === $variant && 0 === $index && $hero_caption ) : ?>
						<figcaption class="static-image-cluster__hero-caption"><?php echo esc_html( $hero_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
				<?php if ( 'inspired' === $variant ) : ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( 'five_plus' === $variant ) : ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( 'five_plus' === $variant ) : ?>
			<div class="static-image-cluster__carousel-controls">
				<div class="static-image-cluster__carousel-pagination ltt-carousel-indicators" data-static-image-cluster-carousel-pagination aria-label="<?php esc_attr_e( 'Choose an image', 'ltt-dive-in' ); ?>"></div>
				<div class="static-image-cluster__carousel-navigation">
					<button class="ltt-slider-navigation ltt-slider-navigation--white" type="button" data-static-image-cluster-carousel-previous aria-label="<?php esc_attr_e( 'Previous images', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
					<button class="ltt-slider-navigation ltt-slider-navigation--white" type="button" data-static-image-cluster-carousel-next aria-label="<?php esc_attr_e( 'Next images', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $can_load_more && count( $images ) > count( $rendered_images ) ) : ?>
			<button class="static-image-cluster__inspired-load-more" type="button" data-inspired-load-more data-load-more-post="<?php echo esc_attr( (string) $post_id ); ?>" data-load-more-block="<?php echo esc_attr( $rest_block_id ); ?>" data-load-more-offset="<?php echo esc_attr( (string) count( $rendered_images ) ); ?>">
				<span data-inspired-load-more-label><?php esc_html_e( 'Load More', 'ltt-dive-in' ); ?></span>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/select-toggle.svg' ) ); ?>" alt="" aria-hidden="true" data-inspired-load-more-icon />
				<span class="screen-reader-text" data-inspired-load-more-status role="status"></span>
			</button>
		<?php endif; ?>

		<?php if ( 'hero_caption' === $variant && $ctas ) : ?>
			<div class="static-image-cluster__actions static-image-cluster__actions--hero">
				<?php foreach ( $ctas as $index => $cta ) : ?>
					<a class="ltt-button ltt-button--<?php echo esc_attr( $button_classes[ $index ] ); ?>" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo $link_attributes( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $cta['title'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $has_lightbox && ! $is_preview ) : ?>
		<script type="application/json" data-ltt-photoswipe-data><?php echo wp_json_encode( $rendered_images, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
	<?php endif; ?>
</section>
