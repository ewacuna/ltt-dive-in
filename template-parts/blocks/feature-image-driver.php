<?php
/**
 * Feature Image Driver ACF block renderer.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = isset( $is_preview ) && $is_preview;
$block_id   = isset( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id( 'feature-image-driver-' );
$anchor     = isset( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : '';
$alignment  = isset( $block['align'] ) && 'full' === $block['align'] ? ' alignfull' : '';
$block_data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
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
$normalize_item = static function ( $item ) use ( $normalize_link ) {
	if ( ! is_array( $item ) ) {
		return array();
	}

	$image_id = isset( $item['image'] ) ? absint( $item['image'] ) : 0;
	$title    = isset( $item['title'] ) ? trim( (string) $item['title'] ) : '';
	$link     = $normalize_link( $item['link'] ?? array() );

	if ( ! $image_id || ! $title || ! $link ) {
		return array();
	}

	return array(
		'image' => $image_id,
		'title' => $title,
		'tag'   => isset( $item['tag'] ) ? trim( (string) $item['tag'] ) : '',
		'body'  => ! empty( $item['show_body'] ) && ! empty( $item['body'] ) ? trim( (string) $item['body'] ) : '',
		'link'  => $link,
	);
};
$link_attributes = static function ( $link ) {
	if ( empty( $link['target'] ) ) {
		return '';
	}

	return ' target="' . esc_attr( $link['target'] ) . '" rel="noopener noreferrer"';
};

$variant  = $get_value( 'ltt_dive_in_feature_image_driver_variant' );
$variants = array( 'single', 'carousel', 'short', 'stacked' );
$items    = array();

if ( ! is_string( $variant ) || ! in_array( $variant, $variants, true ) ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Choose a Feature Image Driver variant to preview this block.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

$raw_items = (array) $get_value( 'ltt_dive_in_feature_image_driver_items' );

/* Keep older saved blocks rendering until their data is saved in the shared repeater. */
if ( ! $raw_items && in_array( $variant, array( 'single', 'short' ), true ) ) {
	$raw_items[] = array(
		'enabled'   => 1,
		'image'     => $get_value( 'ltt_dive_in_feature_image_driver_image' ),
		'title'     => $get_value( 'ltt_dive_in_feature_image_driver_title' ),
		'tag'       => 'single' === $variant ? $get_value( 'ltt_dive_in_feature_image_driver_tag' ) : '',
		'show_body' => $get_value( 'ltt_dive_in_feature_image_driver_show_body' ),
		'body'      => $get_value( 'ltt_dive_in_feature_image_driver_body' ),
		'link'      => $get_value( 'ltt_dive_in_feature_image_driver_link' ),
	);
} elseif ( ! $raw_items && 'carousel' === $variant ) {
	$raw_items = (array) $get_value( 'ltt_dive_in_feature_image_driver_slides' );
} elseif ( ! $raw_items && 'stacked' === $variant ) {
	$raw_items = (array) $get_value( 'ltt_dive_in_feature_image_driver_rows' );
}

$active_item_count = 0;

foreach ( array_slice( $raw_items, 0, 5 ) as $raw_item ) {
	if ( ! is_array( $raw_item ) || ( array_key_exists( 'enabled', $raw_item ) && ! $raw_item['enabled'] ) ) {
		continue;
	}

	++$active_item_count;
	$item = $normalize_item( $raw_item );

	if ( $item ) {
		$items[] = $item;
	}
}

$limits  = function_exists( 'ltt_dive_in_get_feature_image_driver_item_limits' ) ? ltt_dive_in_get_feature_image_driver_item_limits() : array();
$minimum = isset( $limits[ $variant ]['min'] ) ? (int) $limits[ $variant ]['min'] : 1;
$maximum = isset( $limits[ $variant ]['max'] ) ? (int) $limits[ $variant ]['max'] : 5;
$is_valid_item_count = $active_item_count >= $minimum && $active_item_count <= $maximum;
$has_complete_items  = count( $items ) === $active_item_count;

if ( ! $is_valid_item_count || ! $has_complete_items ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Adjust the active item count for this variant and complete every active image, title, and call to action.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

if ( 'carousel' === $variant && function_exists( 'ltt_dive_in_enqueue_feature_image_driver_carousel_assets' ) ) {
	ltt_dive_in_enqueue_feature_image_driver_carousel_assets();
}

$section_id = $anchor ? $anchor : 'feature-image-driver-' . $block_id;
$classes    = 'feature-image-driver feature-image-driver--' . $variant . $alignment . ( $is_preview ? ' feature-image-driver--preview' : '' );
?>

<?php if ( in_array( $variant, array( 'single', 'short' ), true ) ) : ?>
	<?php $item = $items[0]; ?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $section_id . '-title' ); ?>">
		<div class="feature-image-driver__feature">
			<span class="feature-image-driver__media" aria-hidden="true">
				<?php echo wp_get_attachment_image( $item['image'], 'full', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
			</span>
			<span class="feature-image-driver__overlay" aria-hidden="true"></span>
			<span class="feature-image-driver__content">
				<?php if ( $item['tag'] && 'short' !== $variant ) : ?>
					<span class="feature-image-driver__tag"><?php echo esc_html( $item['tag'] ); ?></span>
				<?php endif; ?>
				<h2 id="<?php echo esc_attr( $section_id . '-title' ); ?>" class="feature-image-driver__title"><?php echo esc_html( $item['title'] ); ?></h2>
				<?php if ( $item['body'] ) : ?>
					<span class="feature-image-driver__body"><?php echo esc_html( $item['body'] ); ?></span>
				<?php endif; ?>
				<a class="feature-image-driver__cta ltt-button ltt-button--<?php echo esc_attr( 'short' === $variant ? 'dark-standard' : 'glass' ); ?>" href="<?php echo esc_url( $item['link']['url'] ); ?>"<?php echo $link_attributes( $item['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $item['link']['title'] ); ?></a>
			</span>
		</div>
	</section>

<?php elseif ( 'carousel' === $variant ) : ?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php esc_attr_e( 'Featured content', 'ltt-dive-in' ); ?>" data-feature-image-driver-carousel>
		<div class="feature-image-driver__carousel-viewport swiper">
			<div class="feature-image-driver__carousel-track swiper-wrapper">
				<?php foreach ( $items as $index => $item ) : ?>
					<div class="feature-image-driver__feature feature-image-driver__slide swiper-slide">
						<span class="feature-image-driver__media" aria-hidden="true">
							<?php echo wp_get_attachment_image( $item['image'], 'full', false, array( 'alt' => '', 'loading' => 0 === $index ? 'eager' : 'lazy' ) ); ?>
						</span>
						<span class="feature-image-driver__overlay" aria-hidden="true"></span>
						<span class="feature-image-driver__content">
							<?php if ( $item['tag'] ) : ?>
								<span class="feature-image-driver__tag"><?php echo esc_html( $item['tag'] ); ?></span>
							<?php endif; ?>
							<h2 class="feature-image-driver__title"><?php echo esc_html( $item['title'] ); ?></h2>
							<?php if ( $item['body'] ) : ?>
								<span class="feature-image-driver__body"><?php echo esc_html( $item['body'] ); ?></span>
							<?php endif; ?>
							<a class="feature-image-driver__cta ltt-button ltt-button--glass" href="<?php echo esc_url( $item['link']['url'] ); ?>"<?php echo $link_attributes( $item['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $item['link']['title'] ); ?></a>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( count( $items ) > 1 ) : ?>
			<button class="feature-image-driver__carousel-control feature-image-driver__carousel-control--previous ltt-slider-navigation ltt-slider-navigation--image" type="button" data-feature-image-driver-previous aria-label="<?php esc_attr_e( 'Previous feature', 'ltt-dive-in' ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-previous.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
			<button class="feature-image-driver__carousel-control feature-image-driver__carousel-control--next ltt-slider-navigation ltt-slider-navigation--image" type="button" data-feature-image-driver-next aria-label="<?php esc_attr_e( 'Next feature', 'ltt-dive-in' ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-next.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
			<div class="feature-image-driver__carousel-pagination ltt-carousel-indicators" data-feature-image-driver-pagination>
				<?php foreach ( $items as $item_index => $item ) : ?>
					<span class="ltt-carousel-indicator<?php echo 0 === $item_index ? ' is-active' : ''; ?>" aria-hidden="true"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

<?php else : ?>
	<?php
	$stacked_heading        = trim( (string) $get_value( 'ltt_dive_in_feature_image_driver_stacked_heading' ) );
	$stacked_tag            = trim( (string) $get_value( 'ltt_dive_in_feature_image_driver_stacked_tag' ) );
	$stacked_intro          = trim( (string) $get_value( 'ltt_dive_in_feature_image_driver_stacked_intro' ) );
	$stacked_primary_link   = $normalize_link( $get_value( 'ltt_dive_in_feature_image_driver_stacked_primary_link' ) );
	$stacked_secondary_link = $normalize_link( $get_value( 'ltt_dive_in_feature_image_driver_stacked_secondary_link' ) );
	$stacked_mobile_background = 'dark' === $get_value( 'ltt_dive_in_feature_image_driver_stacked_mobile_background' ) ? 'dark' : 'light';
	$stacked_links          = array_filter( array( $stacked_primary_link, $stacked_secondary_link ) );
	$row_heading_tag        = $stacked_heading ? 'h3' : 'h2';
	$classes               .= ' feature-image-driver--mobile-background-' . $stacked_mobile_background;
	?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $classes ); ?>"<?php echo $stacked_heading ? ' aria-labelledby="' . esc_attr( $section_id . '-title' ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="feature-image-driver__stacked-container">
			<?php if ( $stacked_heading || $stacked_tag || $stacked_intro || $stacked_links ) : ?>
				<header class="feature-image-driver__stacked-header">
					<div class="feature-image-driver__stacked-intro">
						<?php if ( $stacked_tag ) : ?>
							<p class="feature-image-driver__tag feature-image-driver__tag--light"><?php echo esc_html( $stacked_tag ); ?></p>
						<?php endif; ?>
						<?php if ( $stacked_heading ) : ?>
							<h2 id="<?php echo esc_attr( $section_id . '-title' ); ?>" class="feature-image-driver__stacked-title"><?php echo esc_html( $stacked_heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $stacked_intro ) : ?>
							<p class="feature-image-driver__stacked-copy"><?php echo esc_html( $stacked_intro ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $stacked_links ) : ?>
						<div class="feature-image-driver__stacked-actions">
							<?php foreach ( $stacked_links as $index => $stacked_link ) : ?>
								<a class="ltt-button ltt-button--<?php echo esc_attr( 0 === $index ? 'primary-outline' : 'secondary-outline' ); ?>" href="<?php echo esc_url( $stacked_link['url'] ); ?>"<?php echo $link_attributes( $stacked_link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $stacked_link['title'] ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</header>
			<?php endif; ?>

			<div class="feature-image-driver__stacked-list">
				<?php foreach ( $items as $item_index => $item ) : ?>
					<?php
					$panel_id  = $section_id . '-stacked-panel-' . ( $item_index + 1 );
					$toggle_id = $section_id . '-stacked-toggle-' . ( $item_index + 1 );
					$toggle_name = $section_id . '-stacked-mobile-toggle';
					?>
					<?php if ( 'light' === $stacked_mobile_background ) : ?>
						<input id="<?php echo esc_attr( $toggle_id ); ?>" class="feature-image-driver__stacked-card-toggle" type="radio" name="<?php echo esc_attr( $toggle_name ); ?>" value="<?php echo esc_attr( $item_index + 1 ); ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Expand %s', 'ltt-dive-in' ), $item['title'] ) ); ?>"<?php checked( 0, $item_index ); ?> />
					<?php endif; ?>
					<article class="feature-image-driver__stacked-card">
						<div class="feature-image-driver__stacked-row">
							<span class="feature-image-driver__media" aria-hidden="true">
								<?php echo wp_get_attachment_image( $item['image'], 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
							</span>
							<span class="feature-image-driver__overlay" aria-hidden="true"></span>
							<div class="feature-image-driver__stacked-card-content">
								<<?php echo esc_attr( $row_heading_tag ); ?> class="feature-image-driver__stacked-card-title"><?php echo esc_html( $item['title'] ); ?></<?php echo esc_attr( $row_heading_tag ); ?>>
								<?php if ( 'light' === $stacked_mobile_background ) : ?>
								<label class="feature-image-driver__stacked-expand" for="<?php echo esc_attr( $toggle_id ); ?>">
									<span><?php esc_html_e( 'Expand', 'ltt-dive-in' ); ?></span>
									<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/feature-image-driver-expand.svg' ) ); ?>" alt="" aria-hidden="true" width="9" height="11" />
								</label>
								<?php endif; ?>
								<div id="<?php echo esc_attr( $panel_id ); ?>" class="feature-image-driver__stacked-card-panel">
									<div class="feature-image-driver__stacked-card-panel-inner">
										<?php if ( $item['body'] ) : ?>
											<span class="feature-image-driver__stacked-card-body"><?php echo esc_html( $item['body'] ); ?></span>
										<?php endif; ?>
										<a class="feature-image-driver__cta ltt-button ltt-button--glass" href="<?php echo esc_url( $item['link']['url'] ); ?>"<?php echo $link_attributes( $item['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $item['link']['title'] ); ?></a>
									</div>
								</div>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
