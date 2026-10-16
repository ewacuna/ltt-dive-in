<?php
/**
 * Story Drivers ACF block renderer (C-07).
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = isset( $is_preview ) && $is_preview;
$block_id   = isset( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id( 'story-drivers-' );
$anchor     = isset( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : '';
$alignment  = isset( $block['align'] ) && 'full' === $block['align'] ? 'alignfull' : '';
$block_data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
$get_value  = static function ( $name ) use ( $block_data ) {
	$value = get_field( $name );

	return ( false === $value || null === $value ) && array_key_exists( $name, $block_data ) ? $block_data[ $name ] : $value;
};
$preview_message = static function ( $message ) use ( $is_preview ) {
	if ( $is_preview ) {
		echo '<p class="story-drivers__preview-message">' . esc_html( $message ) . '</p>';
	}
};

$layouts = ltt_dive_in_get_story_driver_layouts();
$layout  = $get_value( 'ltt_dive_in_story_driver_layout' );

if ( ! is_string( $layout ) || ! isset( $layouts[ $layout ] ) ) {
	$preview_message( __( 'Choose a layout to preview this Story Drivers block.', 'ltt-dive-in' ) );
	return;
}

$limits     = $layouts[ $layout ];
$has_header = 'one_up' !== $layout;
$heading    = trim( (string) $get_value( 'ltt_dive_in_story_driver_heading' ) );

if ( $has_header && '' === $heading ) {
	$preview_message( __( 'Add a heading to preview this Story Drivers block.', 'ltt-dive-in' ) );
	return;
}

$links = array();

if ( in_array( $layout, array( 'two_up', 'three_up', 'four_up' ), true ) ) {
	foreach ( array( 'ltt_dive_in_story_driver_primary_link', 'ltt_dive_in_story_driver_secondary_link' ) as $link_field ) {
		$link = $get_value( $link_field );

		if ( is_array( $link ) && ! empty( $link['url'] ) && ! empty( $link['title'] ) && trim( (string) $link['title'] ) ) {
			$links[] = array(
				'url'    => (string) $link['url'],
				'title'  => trim( (string) $link['title'] ),
				'target' => '_blank' === ( $link['target'] ?? '' ),
			);
		}
	}
}

$config = array(
	'tag'     => trim( (string) $get_value( 'ltt_dive_in_story_driver_tag' ) ),
	'heading' => $heading,
	'intro'   => trim( (string) $get_value( 'ltt_dive_in_story_driver_intro' ) ),
	'links'   => $links,
);

/* Choose the stories: an ordered manual selection or the most recent posts. */
$mode = 'manual' === $get_value( 'ltt_dive_in_story_driver_mode' ) ? 'manual' : 'dynamic';

if ( 'manual' === $mode ) {
	$post_ids = $get_value( 'ltt_dive_in_story_driver_posts' );
	$stories  = array_slice( ltt_dive_in_get_story_driver_stories( is_array( $post_ids ) ? $post_ids : array() ), 0, $limits['max'] );
} else {
	$count   = $limits['min'] === $limits['max'] ? $limits['max'] : absint( $get_value( 'ltt_dive_in_story_driver_count' ) );
	$count   = max( $limits['min'], min( $limits['max'], $count ? $count : $limits['default'] ) );
	$current = isset( $post_id ) && is_numeric( $post_id ) ? absint( $post_id ) : get_the_ID();
	$stories = ltt_dive_in_query_story_driver_stories(
		array(
			'categories' => (array) $get_value( 'ltt_dive_in_story_driver_categories' ),
			'tags'       => (array) $get_value( 'ltt_dive_in_story_driver_tags' ),
			'count'      => $count,
			'exclude'    => $get_value( 'ltt_dive_in_story_driver_exclude_current' ) && $current ? array( $current ) : array(),
		)
	);
}

if ( ! $stories ) {
	$preview_message( 'manual' === $mode ? __( 'Choose published posts with featured images to preview this Story Drivers block.', 'ltt-dive-in' ) : __( 'No published posts with featured images match these settings.', 'ltt-dive-in' ) );
	return;
}

if ( in_array( $layout, array( 'one_up', 'carousel', 'hero_grid' ), true ) && function_exists( 'ltt_dive_in_enqueue_story_driver_carousel_assets' ) ) {
	ltt_dive_in_enqueue_story_driver_carousel_assets();
}

$section_id = $anchor ? $anchor : 'story-drivers-' . $block_id;
$heading_id = $section_id . '-heading';
$classes    = array( 'story-drivers', 'story-drivers--' . str_replace( '_', '-', $layout ), $alignment );

if ( $is_preview ) {
	$classes[] = 'story-drivers--preview';
}

$header_args = array(
	'config'     => $config,
	'heading_id' => $heading_id,
	'is_preview' => $is_preview,
);

if ( 'one_up' === $layout ) :
	?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-label="<?php esc_attr_e( 'Featured stories', 'ltt-dive-in' ); ?>">
		<?php
		get_template_part(
			'template-parts/components/story-drivers/feature',
			null,
			array(
				'stories'    => $stories,
				'is_preview' => $is_preview,
			)
		);
		?>
	</section>
	<?php
	return;
endif;

if ( 'carousel' === $layout ) :
	$filters = array();

	if ( $get_value( 'ltt_dive_in_story_driver_enable_filters' ) ) {
		$story_terms = array_unique( array_merge( array(), ...array_column( $stories, 'category_ids' ) ) );

		foreach ( array_slice( array_filter( array_map( 'absint', (array) $get_value( 'ltt_dive_in_story_driver_filter_terms' ) ) ), 0, 5 ) as $term_id ) {
			$term = get_term( $term_id, 'category' );

			if ( $term instanceof WP_Term && in_array( $term_id, $story_terms, true ) ) {
				$filters[] = $term;
			}
		}

		/* A single toggle cannot narrow anything, so filters need two categories. */
		$filters = count( $filters ) > 1 ? $filters : array();
	}
	?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-story-carousel-section>
		<div class="story-drivers__container">
			<?php get_template_part( 'template-parts/components/story-drivers/header', null, $header_args ); ?>

			<?php if ( $filters ) : ?>
				<div class="story-drivers__toggles" role="group" aria-label="<?php esc_attr_e( 'Filter stories by category', 'ltt-dive-in' ); ?>">
					<button class="ltt-category-selection" type="button" aria-pressed="true" data-story-filter="all"><?php esc_html_e( 'All', 'ltt-dive-in' ); ?></button>
					<?php foreach ( $filters as $term ) : ?>
						<button class="ltt-category-selection" type="button" aria-pressed="false" data-story-filter="<?php echo esc_attr( (string) $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</div>
				<p class="screen-reader-text" role="status" data-story-filter-status></p>
			<?php endif; ?>
		</div>

		<?php
		/* Fewer than three stories cannot fill the carousel, so they use the 2 Up row presentation. */
		$is_row = count( $stories ) < 3;
		?>
		<div class="story-drivers__carousel" data-story-carousel<?php echo $is_row ? ' data-visible-count="' . esc_attr( (string) count( $stories ) ) . '"' : ''; ?>>
			<div class="story-drivers__carousel-viewport swiper">
				<div class="story-drivers__carousel-track swiper-wrapper" data-story-carousel-track>
					<?php
					foreach ( $stories as $story ) {
						get_template_part(
							'template-parts/components/story-card',
							null,
							array(
								'story'   => $story,
								'wrapper' => 'story-drivers__slide swiper-slide',
								'sizes'   => $is_row ? '(max-width: 767.98px) 100vw, ' . (int) ceil( 1436 / count( $stories ) * 1.5 ) . 'px' : '(max-width: 767.98px) 317px, 471px',
								'tile'    => ! $is_row,
							)
						);
					}
					?>
				</div>
			</div>
			<button class="story-drivers__carousel-control story-drivers__carousel-control--previous ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--previous" type="button" data-story-carousel-previous aria-label="<?php esc_attr_e( 'Previous stories', 'ltt-dive-in' ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-previous.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
			<button class="story-drivers__carousel-control story-drivers__carousel-control--next ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--next" type="button" data-story-carousel-next aria-label="<?php esc_attr_e( 'Next stories', 'ltt-dive-in' ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-next.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
		</div>
	</section>
	<?php
	return;
endif;

if ( 'hero_grid' === $layout ) :
	$block_hash    = substr( md5( $block_id ), 0, 10 );
	$is_this_block = ! $is_preview && isset( $_GET['story_block'] ) && hash_equals( $block_hash, sanitize_key( wp_unslash( $_GET['story_block'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public filters.
	$filters       = ltt_dive_in_sanitize_story_driver_grid_filters(
		$is_this_block ? array(
			'category' => wp_unslash( $_GET['story_category'] ?? 0 ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by the helper.
			'tag'      => wp_unslash( $_GET['story_tag'] ?? 0 ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'search'   => wp_unslash( $_GET['story_search'] ?? '' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'page'     => wp_unslash( $_GET['story_page'] ?? 1 ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) : array()
	);
	$per_page      = absint( $get_value( 'ltt_dive_in_story_driver_grid_count' ) );
	$per_page      = max( 3, min( 12, $per_page ? $per_page - $per_page % 3 : 9 ) );
	$hero_ids      = array_column( $stories, 'id' );
	$result        = ltt_dive_in_query_story_driver_grid( $filters, $hero_ids, 0, $per_page * $filters['page'] );
	$form_id       = $section_id . '-filters';
	$results_id    = $section_id . '-results';
	$categories    = ltt_dive_in_get_story_driver_filter_options( 'category' );
	$tags          = ltt_dive_in_get_story_driver_filter_options( 'post_tag' );
	$build_options = static function ( $terms ) {
		$options = array();

		foreach ( $terms as $term_id => $term_name ) {
			$options[] = array(
				'value' => (string) $term_id,
				'label' => $term_name,
			);
		}

		return $options;
	};
	?>
	<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"<?php if ( ! $is_preview ) : ?> data-story-grid data-per-page="<?php echo esc_attr( (string) $per_page ); ?>" data-exclude="<?php echo esc_attr( implode( ',', $hero_ids ) ); ?>"<?php endif; ?>>
		<div class="story-drivers__container">
			<?php get_template_part( 'template-parts/components/story-drivers/header', null, $header_args ); ?>

			<?php
			get_template_part(
				'template-parts/components/story-drivers/feature',
				null,
				array(
					'stories'       => $stories,
					'heading_level' => 3,
					/* translators: %s: Story Drivers section heading. */
					'label'         => sprintf( __( 'Featured stories: %s', 'ltt-dive-in' ), $heading ),
					'is_preview'    => $is_preview,
				)
			);
			?>

			<form id="<?php echo esc_attr( $form_id ); ?>" class="story-drivers__filters" method="get" action="<?php echo esc_attr( '#' . $section_id ); ?>" aria-label="<?php esc_attr_e( 'Filter stories', 'ltt-dive-in' ); ?>" data-story-grid-filters>
				<input type="hidden" name="story_block" value="<?php echo esc_attr( $block_hash ); ?>" />

				<?php if ( $categories ) : ?>
					<div class="story-drivers__filter">
						<?php
						get_template_part(
							'template-parts/components/select',
							null,
							array(
								'id'                => $section_id . '-category',
								'name'              => 'story_category',
								'label'             => __( 'Story category', 'ltt-dive-in' ),
								'placeholder'       => __( 'Categories', 'ltt-dive-in' ),
								'options'           => $build_options( $categories ),
								'selected_value'    => $filters['category'] ? (string) $filters['category'] : '',
								'surface'           => 'light',
								'aria_controls'     => $results_id,
								'native_attributes' => array( 'data-story-grid-filter' => 'category' ),
							)
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( $tags ) : ?>
					<div class="story-drivers__filter">
						<?php
						get_template_part(
							'template-parts/components/select',
							null,
							array(
								'id'                => $section_id . '-tag',
								'name'              => 'story_tag',
								'label'             => __( 'Story tag', 'ltt-dive-in' ),
								'placeholder'       => __( 'Tags', 'ltt-dive-in' ),
								'options'           => $build_options( $tags ),
								'selected_value'    => $filters['tag'] ? (string) $filters['tag'] : '',
								'surface'           => 'light',
								'aria_controls'     => $results_id,
								'native_attributes' => array( 'data-story-grid-filter' => 'tag' ),
							)
						);
						?>
					</div>
				<?php endif; ?>

				<div class="story-drivers__search ltt-search-bar ltt-search-bar--filter" role="search">
					<label class="screen-reader-text" for="<?php echo esc_attr( $section_id . '-search' ); ?>"><?php esc_html_e( 'Search stories', 'ltt-dive-in' ); ?></label>
					<button class="ltt-search-bar__submit" type="submit">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) ); ?>" alt="" width="28" height="28" />
						<span class="screen-reader-text"><?php esc_html_e( 'Search stories', 'ltt-dive-in' ); ?></span>
					</button>
					<input id="<?php echo esc_attr( $section_id . '-search' ); ?>" class="ltt-search-bar__input" type="search" name="story_search" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search...', 'ltt-dive-in' ); ?>" maxlength="80" autocomplete="off" aria-controls="<?php echo esc_attr( $results_id ); ?>" data-story-grid-filter="search" />
				</div>

				<noscript>
					<button class="ltt-button ltt-button--primary-outline story-drivers__apply" type="submit"><?php esc_html_e( 'Apply filters', 'ltt-dive-in' ); ?></button>
				</noscript>
			</form>

			<p class="screen-reader-text" role="status" data-story-grid-status></p>

			<div id="<?php echo esc_attr( $results_id ); ?>" class="story-drivers__grid" data-story-grid-results>
				<?php echo ltt_dive_in_render_story_driver_grid_cards( $result['stories'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the story-card template part. ?>
			</div>

			<p class="story-drivers__empty" data-story-grid-empty<?php echo $result['stories'] ? ' hidden' : ''; ?>><?php esc_html_e( 'No stories match your filters. Try another category, tag, or search term.', 'ltt-dive-in' ); ?></p>

			<button class="story-drivers__load-more" type="submit" form="<?php echo esc_attr( $form_id ); ?>" name="story_page" value="<?php echo esc_attr( (string) ( $filters['page'] + 1 ) ); ?>" data-story-grid-load-more data-offset="<?php echo esc_attr( (string) count( $result['stories'] ) ); ?>"<?php echo $result['has_more'] ? '' : ' hidden'; ?><?php echo $is_preview ? ' disabled' : ''; ?>>
				<span class="story-drivers__load-more-label"><?php esc_html_e( 'Load More', 'ltt-dive-in' ); ?></span>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/header/scroll-arrow.svg' ) ); ?>" alt="" width="14" height="12" />
			</button>
		</div>
	</section>
	<?php
	return;
endif;

/* 2 Up, 3 Up, and 4 Up. */
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="story-drivers__container">
		<?php get_template_part( 'template-parts/components/story-drivers/header', null, $header_args ); ?>
	</div>
	<div class="story-drivers__row" data-story-row>
		<?php
		foreach ( $stories as $story ) {
			get_template_part(
				'template-parts/components/story-card',
				null,
				array(
					'story'   => $story,
					'wrapper' => 'story-drivers__item',
					'sizes'   => '(max-width: 767.98px) 100vw, ' . (int) ceil( 1436 / count( $stories ) * 1.5 ) . 'px',
				)
			);
		}
		?>
	</div>
</section>
