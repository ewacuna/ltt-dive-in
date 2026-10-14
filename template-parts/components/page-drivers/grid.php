<?php
/**
 * Reusable Page Drivers grid.
 *
 * @package LTT_Dive_In
 */

$section_id   = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : wp_unique_id( 'page-drivers-' );
$class_name   = ! empty( $args['class_name'] ) ? sanitize_html_class( $args['class_name'] ) : '';
$layout       = isset( $args['layout'] ) && is_string( $args['layout'] ) ? $args['layout'] : 'one_up';
$surface      = isset( $args['surface'] ) && 'dark' === $args['surface'] ? 'dark' : 'light';
$heading      = isset( $args['heading'] ) && is_string( $args['heading'] ) ? $args['heading'] : '';
$tag          = isset( $args['tag'] ) && is_string( $args['tag'] ) ? $args['tag'] : '';
$intro        = isset( $args['intro'] ) && is_string( $args['intro'] ) ? $args['intro'] : '';
$show_excerpt = ! empty( $args['show_excerpt'] );
$tiles        = isset( $args['tiles'] ) && is_array( $args['tiles'] ) ? $args['tiles'] : array();
$filters      = isset( $args['filters'] ) && is_array( $args['filters'] ) ? $args['filters'] : array();
$is_carousel  = in_array( $layout, array( 'six_plus_up', 'monthly' ), true );
$is_monthly   = 'monthly' === $layout;

if ( ! $heading || ! $tiles ) {
	return;
}

$heading_id     = $section_id . '-heading';
$status_id      = $section_id . '-filter-status';
$month_options  = array();
$selected_month = '';

if ( $is_monthly ) {
	$calendar_months = array(
		'january',
		'february',
		'march',
		'april',
		'may',
		'june',
		'july',
		'august',
		'september',
		'october',
		'november',
		'december',
	);
	$month_order     = array_flip( $calendar_months );

	usort(
		$filters,
		static function ( $left, $right ) use ( $month_order ) {
			$left_slug  = isset( $left->slug ) ? sanitize_title( $left->slug ) : '';
			$right_slug = isset( $right->slug ) ? sanitize_title( $right->slug ) : '';
			$left_order = isset( $month_order[ $left_slug ] ) ? $month_order[ $left_slug ] : PHP_INT_MAX;
			$right_order = isset( $month_order[ $right_slug ] ) ? $month_order[ $right_slug ] : PHP_INT_MAX;

			if ( $left_order === $right_order ) {
				$left_name  = isset( $left->name ) ? $left->name : '';
				$right_name = isset( $right->name ) ? $right->name : '';

				return strnatcasecmp( $left_name, $right_name );
			}

			return $left_order <=> $right_order;
		}
	);

	foreach ( $filters as $term ) {
		$term_id       = isset( $term->term_id ) ? absint( $term->term_id ) : 0;
		$target_tile_id = 0;

		if ( ! $term_id ) {
			continue;
		}

		foreach ( $tiles as $tile ) {
			$tile_terms = isset( $tile['terms'] ) && is_array( $tile['terms'] ) ? array_map( 'absint', $tile['terms'] ) : array();

			if ( ! empty( $tile['id'] ) && ! empty( $tile['image'] ) && in_array( $term_id, $tile_terms, true ) ) {
				$target_tile_id = absint( $tile['id'] );
				break;
			}
		}

		if ( ! $target_tile_id ) {
			continue;
		}

		$month_options[ $term_id ] = array(
			'value'     => (string) $term_id,
			'label'     => (string) $term->name,
			'target_id' => $target_tile_id,
		);
	}

	$month_positions = array_flip( array_keys( $month_options ) );
	$tile_month_order = static function ( $tile ) use ( $month_positions ) {
		$tile_terms = isset( $tile['terms'] ) && is_array( $tile['terms'] ) ? array_map( 'absint', $tile['terms'] ) : array();
		$position   = PHP_INT_MAX;

		foreach ( $tile_terms as $term_id ) {
			if ( isset( $month_positions[ $term_id ] ) ) {
				$position = min( $position, $month_positions[ $term_id ] );
			}
		}

		return $position;
	};

	// Keep monthly cards in the same calendar order as the selector.
	usort(
		$tiles,
		static function ( $left, $right ) use ( $tile_month_order ) {
			return $tile_month_order( $left ) <=> $tile_month_order( $right );
		}
	);

	if ( $month_options ) {
		$selected_month = (string) array_key_first( $month_options );
	}
}
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="page-drivers page-drivers--<?php echo esc_attr( str_replace( '_', '-', $layout ) ); ?> page-drivers--<?php echo esc_attr( $surface ); ?><?php echo $class_name ? ' ' . esc_attr( $class_name ) : ''; ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-page-driver>
	<div class="page-drivers__container">
		<header class="page-drivers__header">
			<?php if ( $tag ) : ?>
				<p class="page-drivers__tag ltt-tag<?php echo 'dark' === $surface ? ' ltt-tag--dark' : ''; ?>"><?php echo esc_html( $tag ); ?></p>
			<?php endif; ?>
			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="page-drivers__heading"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( $intro ) : ?>
				<p class="page-drivers__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( $is_monthly && $month_options ) : ?>
			<div class="page-drivers__month-select">
				<?php
				get_template_part(
					'template-parts/components/select',
					null,
					array(
						'id'                => $section_id . '-month',
						'label'             => __( 'Choose a month', 'ltt-dive-in' ),
						'options'           => array_values( $month_options ),
						'selected_value'    => (string) $selected_month,
						'surface'           => $surface,
						'aria_controls'     => $section_id . '-tiles',
						'native_attributes' => array(
							'data-page-driver-month-select' => true,
						),
					)
				);
				?>
			</div>
			<nav class="page-drivers__month-links" aria-label="<?php esc_attr_e( 'Choose a month', 'ltt-dive-in' ); ?>">
				<?php foreach ( $month_options as $option ) : ?>
					<a class="page-drivers__month-link" href="#<?php echo esc_attr( $section_id . '-tile-' . $option['target_id'] ); ?>"><?php echo esc_html( $option['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php elseif ( $filters ) : ?>
			<div class="page-drivers__filters" role="group" aria-label="<?php esc_attr_e( 'Filter destinations', 'ltt-dive-in' ); ?>">
				<button class="page-drivers__filter is-selected" type="button" aria-pressed="true" data-page-driver-filter="all"><?php esc_html_e( 'All', 'ltt-dive-in' ); ?></button>
				<?php foreach ( $filters as $term ) : ?>
					<button class="page-drivers__filter" type="button" aria-pressed="false" data-page-driver-filter="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<p id="<?php echo esc_attr( $status_id ); ?>" class="screen-reader-text" aria-live="polite" data-page-driver-status></p>

		<?php if ( $is_carousel ) : ?>
			<div class="page-drivers__carousel" data-page-driver-carousel>
				<div class="page-drivers__carousel-viewport swiper">
		<?php endif; ?>
		<div id="<?php echo esc_attr( $section_id ); ?>-tiles" class="page-drivers__grid<?php echo $is_carousel ? ' swiper-wrapper' : ''; ?>" data-page-driver-grid aria-describedby="<?php echo esc_attr( $status_id ); ?>">
			<?php foreach ( $tiles as $tile ) : ?>
				<?php
				$tile_id     = isset( $tile['id'] ) ? absint( $tile['id'] ) : 0;
				$tile_title  = isset( $tile['title'] ) && is_string( $tile['title'] ) ? $tile['title'] : '';
				$image_id    = isset( $tile['image'] ) ? absint( $tile['image'] ) : 0;
				$terms       = isset( $tile['terms'] ) && is_array( $tile['terms'] ) ? array_map( 'absint', $tile['terms'] ) : array();
				$month_terms = $is_monthly ? array_values( array_intersect( array_keys( $month_options ), $terms ) ) : array();
				$tile_anchor = $is_monthly ? $section_id . '-tile-' . $tile_id : '';

				if ( ! $tile_id || ! $tile_title || ! $image_id ) {
					continue;
				}
				?>
				<article<?php echo $tile_anchor ? ' id="' . esc_attr( $tile_anchor ) . '"' : ''; ?> class="page-drivers__card<?php echo $is_carousel ? ' swiper-slide' : ''; ?>" data-page-driver-card data-page-driver-terms="<?php echo esc_attr( implode( ' ', $terms ) ); ?>"<?php echo $is_monthly ? ' data-page-driver-month-terms="' . esc_attr( implode( ' ', $month_terms ) ) . '"' : ''; ?>>
					<a class="page-drivers__card-link" href="<?php echo esc_url( get_permalink( $tile_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Explore %s', 'ltt-dive-in' ), $tile_title ) ); ?>">
						<span class="page-drivers__media" aria-hidden="true">
							<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
						</span>
						<span class="page-drivers__card-content">
							<span class="page-drivers__card-title"><?php echo esc_html( $tile_title ); ?></span>
							<?php if ( $show_excerpt && ! empty( $tile['excerpt'] ) ) : ?>
								<span class="page-drivers__card-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $tile['excerpt'] ), 22 ) ); ?></span>
							<?php endif; ?>
							<span class="page-drivers__card-cta ltt-button ltt-button--glass" aria-hidden="true"><?php esc_html_e( 'Explore', 'ltt-dive-in' ); ?></span>
						</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
		<?php if ( $is_carousel ) : ?>
				</div>
				<button class="page-drivers__carousel-control page-drivers__carousel-control--previous ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--previous" type="button" data-page-driver-carousel-previous aria-label="<?php echo esc_attr( $is_monthly ? __( 'Previous month', 'ltt-dive-in' ) : __( 'Previous destinations', 'ltt-dive-in' ) ); ?>">
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-previous.svg' ) ); ?>" alt="" aria-hidden="true" />
				</button>
				<button class="page-drivers__carousel-control page-drivers__carousel-control--next ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--next" type="button" data-page-driver-carousel-next aria-label="<?php echo esc_attr( $is_monthly ? __( 'Next month', 'ltt-dive-in' ) : __( 'Next destinations', 'ltt-dive-in' ) ); ?>">
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-next.svg' ) ); ?>" alt="" aria-hidden="true" />
				</button>
				<div class="page-drivers__carousel-pagination ltt-carousel-indicators" data-page-driver-carousel-pagination></div>
			</div>
		<?php endif; ?>
	</div>
</section>
