<?php
/**
 * Theme-owned block registration.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register assets referenced by dynamic block metadata.
 *
 * The public enqueue callback may enqueue these same handles. Registering them
 * on init makes them available when WordPress resolves conditional block assets.
 */
function ltt_dive_in_register_block_assets() {
	$style_path            = LTT_DIVE_IN_DIR . '/assets/css/main.css';
	$buttons_style_path    = LTT_DIVE_IN_DIR . '/assets/css/components/buttons.css';
	$select_style_path     = LTT_DIVE_IN_DIR . '/assets/css/components/select.css';
	$category_selection_style_path = LTT_DIVE_IN_DIR . '/assets/css/components/category-selection.css';
	$activities_style_path = LTT_DIVE_IN_DIR . '/assets/css/components/home-activities.css';
	$accordion_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/accordions.css';
	$accordion_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/accordion.js';

	// In admin, main.css is supplied as a scoped editor style, not a global
	// dependency: its element selectors must not reach media dialogs or admin UI.
	$foundation_dependencies = is_admin() ? array() : array( 'ltt-dive-in-style' );

	if ( ! wp_style_is( 'ltt-dive-in-style', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-style',
			LTT_DIVE_IN_URI . '/assets/css/main.css',
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_style_is( 'ltt-dive-in-buttons', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-buttons',
			LTT_DIVE_IN_URI . '/assets/css/components/buttons.css',
			$foundation_dependencies,
			file_exists( $buttons_style_path ) ? (string) filemtime( $buttons_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_style_is( 'ltt-dive-in-select', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-select',
			LTT_DIVE_IN_URI . '/assets/css/components/select.css',
			$foundation_dependencies,
			file_exists( $select_style_path ) ? (string) filemtime( $select_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_style_is( 'ltt-dive-in-category-selection', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-category-selection',
			LTT_DIVE_IN_URI . '/assets/css/components/category-selection.css',
			$foundation_dependencies,
			file_exists( $category_selection_style_path ) ? (string) filemtime( $category_selection_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_style_is( 'ltt-dive-in-accordions', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-accordions',
			LTT_DIVE_IN_URI . '/assets/css/components/accordions.css',
			array_merge( $foundation_dependencies, array( 'ltt-dive-in-buttons' ) ),
			file_exists( $accordion_style_path ) ? (string) filemtime( $accordion_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( ! wp_script_is( 'ltt-dive-in-accordion', 'registered' ) ) {
		wp_register_script(
			'ltt-dive-in-accordion',
			LTT_DIVE_IN_URI . '/assets/js/components/accordion.js',
			array(),
			file_exists( $accordion_script_path ) ? (string) filemtime( $accordion_script_path ) : LTT_DIVE_IN_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'init', 'ltt_dive_in_register_block_assets', 5 );

/**
 * Add a dedicated category for theme blocks in the editor inserter.
 *
 * @param array[] $categories Existing block categories.
 * @return array[]
 */
function ltt_dive_in_block_categories( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'ltt-blocks',
			'title' => __( 'LTT Blocks', 'ltt-dive-in' ),
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'ltt_dive_in_block_categories' );

/**
 * Register theme-owned ACF blocks and their assets.
 */
function ltt_dive_in_register_blocks() {
	$style_path                        = LTT_DIVE_IN_DIR . '/assets/css/components/page-drivers.css';
	$feature_image_style_path          = LTT_DIVE_IN_DIR . '/assets/css/components/feature-image-driver.css';
	$static_image_cluster_style_path   = LTT_DIVE_IN_DIR . '/assets/css/components/static-image-cluster.css';
	$static_image_cluster_script_path  = LTT_DIVE_IN_DIR . '/assets/js/components/static-image-cluster.js';
	$lightbox_style_path                = LTT_DIVE_IN_DIR . '/assets/css/components/lightbox.css';
	$lightbox_script_path               = LTT_DIVE_IN_DIR . '/assets/js/components/lightbox.js';
	$activities_style_path             = LTT_DIVE_IN_DIR . '/assets/css/components/home-activities.css';
	$script_path                       = LTT_DIVE_IN_DIR . '/assets/js/components/page-drivers.js';
	$carousel_style_path               = LTT_DIVE_IN_DIR . '/assets/css/vendor/swiper-bundle.min.css';
	$carousel_script_path              = LTT_DIVE_IN_DIR . '/assets/js/vendor/swiper-bundle.min.js';
	$carousel_init_path                = LTT_DIVE_IN_DIR . '/assets/js/components/page-drivers-carousel.js';
	$feature_image_carousel_init_path = LTT_DIVE_IN_DIR . '/assets/js/components/feature-image-driver-carousel.js';
	$slider_navigation_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/slider-navigation.css';
	$carousel_indicators_style_path    = LTT_DIVE_IN_DIR . '/assets/css/components/carousel-indicators.css';
	$page_driver_path                  = LTT_DIVE_IN_DIR . '/blocks/page-drivers';
	$feature_image_driver_path         = LTT_DIVE_IN_DIR . '/blocks/feature-image-driver';
	$static_image_cluster_path          = LTT_DIVE_IN_DIR . '/blocks/static-image-cluster';
	$activities_path                   = LTT_DIVE_IN_DIR . '/blocks/activities';
	$accordion_path                    = LTT_DIVE_IN_DIR . '/blocks/faq';
	$team_style_path                   = LTT_DIVE_IN_DIR . '/assets/css/components/meet-the-team.css';
	$team_path                         = LTT_DIVE_IN_DIR . '/blocks/team';
	$video_module_style_path           = LTT_DIVE_IN_DIR . '/assets/css/components/video-module.css';
	$video_module_script_path          = LTT_DIVE_IN_DIR . '/assets/js/components/video-module.js';
	$video_module_path                 = LTT_DIVE_IN_DIR . '/blocks/video-module';

	ltt_dive_in_register_block_assets();

	wp_register_style( 'ltt-dive-in-page-drivers', LTT_DIVE_IN_URI . '/assets/css/components/page-drivers.css', array( 'ltt-dive-in-buttons', 'ltt-dive-in-select' ), file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_script( 'ltt-dive-in-page-drivers', LTT_DIVE_IN_URI . '/assets/js/components/page-drivers.js', array(), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_register_style( 'ltt-dive-in-swiper', LTT_DIVE_IN_URI . '/assets/css/vendor/swiper-bundle.min.css', array(), file_exists( $carousel_style_path ) ? (string) filemtime( $carousel_style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_style( 'ltt-dive-in-slider-navigation', LTT_DIVE_IN_URI . '/assets/css/components/slider-navigation.css', array(), file_exists( $slider_navigation_style_path ) ? (string) filemtime( $slider_navigation_style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_style( 'ltt-dive-in-carousel-indicators', LTT_DIVE_IN_URI . '/assets/css/components/carousel-indicators.css', array(), file_exists( $carousel_indicators_style_path ) ? (string) filemtime( $carousel_indicators_style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_script( 'ltt-dive-in-swiper', LTT_DIVE_IN_URI . '/assets/js/vendor/swiper-bundle.min.js', array(), file_exists( $carousel_script_path ) ? (string) filemtime( $carousel_script_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_register_script( 'ltt-dive-in-page-drivers-carousel', LTT_DIVE_IN_URI . '/assets/js/components/page-drivers-carousel.js', array( 'ltt-dive-in-swiper' ), file_exists( $carousel_init_path ) ? (string) filemtime( $carousel_init_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_register_style( 'ltt-dive-in-lightbox', LTT_DIVE_IN_URI . '/assets/css/components/lightbox.css', array(), file_exists( $lightbox_style_path ) ? (string) filemtime( $lightbox_style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_script( 'ltt-dive-in-lightbox', LTT_DIVE_IN_URI . '/assets/js/components/lightbox.js', array(), file_exists( $lightbox_script_path ) ? (string) filemtime( $lightbox_script_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_register_style(
		'ltt-dive-in-feature-image-driver',
		LTT_DIVE_IN_URI . '/assets/css/components/feature-image-driver.css',
		array( 'ltt-dive-in-buttons' ),
		file_exists( $feature_image_style_path ) ? (string) filemtime( $feature_image_style_path ) : LTT_DIVE_IN_VERSION
	);
	wp_register_script( 'ltt-dive-in-feature-image-driver-carousel', LTT_DIVE_IN_URI . '/assets/js/components/feature-image-driver-carousel.js', array( 'ltt-dive-in-swiper' ), file_exists( $feature_image_carousel_init_path ) ? (string) filemtime( $feature_image_carousel_init_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_register_style(
		'ltt-dive-in-static-image-cluster',
		LTT_DIVE_IN_URI . '/assets/css/components/static-image-cluster.css',
		array( 'ltt-dive-in-buttons', 'ltt-dive-in-swiper', 'ltt-dive-in-slider-navigation', 'ltt-dive-in-carousel-indicators', 'ltt-dive-in-lightbox' ),
		file_exists( $static_image_cluster_style_path ) ? (string) filemtime( $static_image_cluster_style_path ) : LTT_DIVE_IN_VERSION
	);
	wp_register_script(
		'ltt-dive-in-static-image-cluster',
		LTT_DIVE_IN_URI . '/assets/js/components/static-image-cluster.js',
		array( 'ltt-dive-in-swiper', 'ltt-dive-in-lightbox' ),
		file_exists( $static_image_cluster_script_path ) ? (string) filemtime( $static_image_cluster_script_path ) : LTT_DIVE_IN_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
	wp_localize_script(
		'ltt-dive-in-static-image-cluster',
		'ltt_dive_in_static_image_cluster',
		array(
			/* translators: 1: current image number, 2: total images. */
			'imageCount'     => __( 'Image %1$d of %2$d', 'ltt-dive-in' ),
			'previousImages' => __( 'Previous images', 'ltt-dive-in' ),
			'nextImages'     => __( 'Next images', 'ltt-dive-in' ),
			/* translators: %d: gallery position. */
			'goToPosition'   => __( 'Go to gallery position %d', 'ltt-dive-in' ),
		)
	);
	wp_localize_script(
		'ltt-dive-in-feature-image-driver-carousel',
		'ltt_dive_in_feature_image_driver',
		array(
			'previousSlide' => __( 'Previous feature', 'ltt-dive-in' ),
			'nextSlide'     => __( 'Next feature', 'ltt-dive-in' ),
			'goToSlide'     => __( 'Go to feature %d', 'ltt-dive-in' ),
		)
	);
	wp_localize_script( 'ltt-dive-in-page-drivers', 'ltt_dive_in_page_drivers', array( 'destinationSingular' => __( 'destination shown.', 'ltt-dive-in' ), 'destinationPlural' => __( 'destinations shown.', 'ltt-dive-in' ) ) );

	if ( file_exists( $page_driver_path . '/block.json' ) ) {
		register_block_type( $page_driver_path );
	}

	if ( file_exists( $feature_image_driver_path . '/block.json' ) ) {
		register_block_type( $feature_image_driver_path );
	}

	if ( file_exists( $static_image_cluster_path . '/block.json' ) ) {
		register_block_type( $static_image_cluster_path );
	}

	if ( ! wp_style_is( 'ltt-dive-in-home-activities', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-home-activities',
			LTT_DIVE_IN_URI . '/assets/css/components/home-activities.css',
			array( 'ltt-dive-in-buttons' ),
			file_exists( $activities_style_path ) ? (string) filemtime( $activities_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( file_exists( $activities_path . '/block.json' ) ) {
		register_block_type( $activities_path );
	}

	if ( file_exists( $accordion_path . '/block.json' ) ) {
		register_block_type( $accordion_path );
	}

	if ( ! wp_style_is( 'ltt-dive-in-team', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-team',
			LTT_DIVE_IN_URI . '/assets/css/components/meet-the-team.css',
			array( 'ltt-dive-in-buttons' ),
			file_exists( $team_style_path ) ? (string) filemtime( $team_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	if ( file_exists( $team_path . '/block.json' ) ) {
		register_block_type( $team_path );
	}

	$testimonials_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/testimonials.css';
	$testimonials_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/testimonials.js';
	$testimonials_path        = LTT_DIVE_IN_DIR . '/blocks/testimonials';

	// Carousel styles are not dependencies here so a single-testimonial block
	// does not load Swiper; they are enqueued only for multi-testimonial blocks.
	wp_register_style( 'ltt-dive-in-testimonials', LTT_DIVE_IN_URI . '/assets/css/components/testimonials.css', array(), file_exists( $testimonials_style_path ) ? (string) filemtime( $testimonials_style_path ) : LTT_DIVE_IN_VERSION );
	wp_register_script( 'ltt-dive-in-testimonials', LTT_DIVE_IN_URI . '/assets/js/components/testimonials.js', array( 'ltt-dive-in-swiper' ), file_exists( $testimonials_script_path ) ? (string) filemtime( $testimonials_script_path ) : LTT_DIVE_IN_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script(
		'ltt-dive-in-testimonials',
		'ltt_dive_in_testimonials',
		array(
			'carousel'   => __( 'carousel', 'ltt-dive-in' ),
			'slide'      => __( 'slide', 'ltt-dive-in' ),
			/* translators: Swiper replaces {{index}} and {{slidesLength}}; keep both placeholders. */
			'slideLabel' => __( '{{index}} of {{slidesLength}}', 'ltt-dive-in' ),
			'previous'   => __( 'Previous testimonial', 'ltt-dive-in' ),
			'next'       => __( 'Next testimonial', 'ltt-dive-in' ),
			/* translators: Swiper replaces {{index}}; keep the placeholder. */
			'goTo'       => __( 'Go to testimonial {{index}}', 'ltt-dive-in' ),
		)
	);

	if ( file_exists( $testimonials_path . '/block.json' ) ) {
		register_block_type( $testimonials_path );
	}

	wp_register_style(
		'ltt-dive-in-video-module',
		LTT_DIVE_IN_URI . '/assets/css/components/video-module.css',
		array( 'ltt-dive-in-buttons', 'ltt-dive-in-category-selection', 'ltt-dive-in-slider-navigation' ),
		file_exists( $video_module_style_path ) ? (string) filemtime( $video_module_style_path ) : LTT_DIVE_IN_VERSION
	);
	wp_register_script(
		'ltt-dive-in-video-module',
		LTT_DIVE_IN_URI . '/assets/js/components/video-module.js',
		array(),
		file_exists( $video_module_script_path ) ? (string) filemtime( $video_module_script_path ) : LTT_DIVE_IN_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
	wp_localize_script(
		'ltt-dive-in-video-module',
		'ltt_dive_in_video_module',
		array(
			'carousel'   => __( 'video carousel', 'ltt-dive-in' ),
			'slide'      => __( 'video', 'ltt-dive-in' ),
			'slideLabel' => __( '{{index}} of {{slidesLength}}', 'ltt-dive-in' ),
			'previous'   => __( 'Previous video', 'ltt-dive-in' ),
			'next'       => __( 'Next video', 'ltt-dive-in' ),
			'goTo'       => __( 'Go to video {{index}}', 'ltt-dive-in' ),
			'playerTitle' => __( 'Video player', 'ltt-dive-in' ),
		)
	);

	if ( file_exists( $video_module_path . '/block.json' ) ) {
		register_block_type( $video_module_path );
	}
}
add_action( 'init', 'ltt_dive_in_register_blocks' );

/**
 * Load carousel assets for Testimonials blocks with two or more testimonials.
 *
 * @return void
 */
function ltt_dive_in_enqueue_testimonials_carousel_assets() {
	wp_enqueue_style( 'ltt-dive-in-swiper' );
	wp_enqueue_style( 'ltt-dive-in-slider-navigation' );
	wp_enqueue_style( 'ltt-dive-in-carousel-indicators' );
	wp_enqueue_script( 'ltt-dive-in-testimonials' );
}

/**
 * Determine whether a parsed Testimonials block renders as a carousel.
 *
 * ACF stores the repeater row count under the field name, so rows skipped at
 * render time for missing content can make this a harmless over-match.
 *
 * @param array $block Parsed block.
 * @return bool
 */
function ltt_dive_in_testimonials_uses_carousel( $block ) {
	if ( ! is_array( $block ) || 'ltt-dive-in/testimonials' !== ( $block['blockName'] ?? '' ) ) {
		return false;
	}

	$count = $block['attrs']['data']['ltt_dive_in_testimonials_items'] ?? 0;

	return is_numeric( $count ) && (int) $count > 1;
}

/**
 * Enqueue Testimonials carousel assets before the document head is printed.
 *
 * @return void
 */
function ltt_dive_in_maybe_enqueue_testimonials_carousel_assets() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! has_block( 'ltt-dive-in/testimonials', $post ) ) {
		return;
	}

	if ( ltt_dive_in_block_tree_contains( parse_blocks( $post->post_content ), 'ltt_dive_in_testimonials_uses_carousel' ) ) {
		ltt_dive_in_enqueue_testimonials_carousel_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_maybe_enqueue_testimonials_carousel_assets', 20 );

/**
 * Load carousel assets only on pages that render a carousel Up Driver.
 *
 * WordPress de-duplicates registered handles, so multiple carousel blocks still
 * produce one copy of Swiper and one initializer.
 *
 * @return void
 */
function ltt_dive_in_enqueue_page_driver_carousel_assets() {
	wp_enqueue_style( 'ltt-dive-in-swiper' );
	wp_enqueue_style( 'ltt-dive-in-slider-navigation' );
	wp_enqueue_style( 'ltt-dive-in-carousel-indicators' );
	wp_enqueue_script( 'ltt-dive-in-page-drivers-carousel' );
}

/**
 * Load assets used only by the Feature Image Driver carousel variant.
 *
 * @return void
 */
function ltt_dive_in_enqueue_feature_image_driver_carousel_assets() {
	wp_enqueue_style( 'ltt-dive-in-swiper' );
	wp_enqueue_style( 'ltt-dive-in-slider-navigation' );
	wp_enqueue_style( 'ltt-dive-in-carousel-indicators' );
	wp_enqueue_script( 'ltt-dive-in-feature-image-driver-carousel' );
}

/**
 * Load shared carousel assets used by the Video Carousel variant.
 *
 * @return void
 */
function ltt_dive_in_enqueue_video_module_carousel_assets() {
	wp_enqueue_style( 'ltt-dive-in-swiper' );
	wp_enqueue_style( 'ltt-dive-in-slider-navigation' );
	wp_enqueue_style( 'ltt-dive-in-carousel-indicators' );
	wp_enqueue_script( 'ltt-dive-in-swiper' );
}

/**
 * Determine whether a block tree contains a block matching a callback.
 *
 * @param array[] $blocks Parsed blocks.
 * @param callable $matcher Callback that receives one parsed block.
 * @return bool
 */
function ltt_dive_in_block_tree_contains( $blocks, $matcher ) {
	if ( ! is_array( $blocks ) || ! is_callable( $matcher ) ) {
		return false;
	}

	foreach ( $blocks as $block ) {
		if ( is_array( $block ) && call_user_func( $matcher, $block ) ) {
			return true;
		}

		if ( ! empty( $block['innerBlocks'] ) && ltt_dive_in_block_tree_contains( $block['innerBlocks'], $matcher ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Determine whether a parsed Page Driver block uses a carousel layout.
 *
 * Other blocks can supply their own predicate to ltt_dive_in_block_tree_contains()
 * when they need to conditionally enqueue an asset.
 *
 * @param array $block Parsed block.
 * @return bool
 */
function ltt_dive_in_page_driver_uses_carousel( $block ) {
	if ( ! is_array( $block ) || 'ltt-dive-in/page-drivers' !== ( $block['blockName'] ?? '' ) ) {
		return false;
	}

	$layout           = isset( $block['attrs']['data']['ltt_dive_in_page_driver_layout'] ) ? $block['attrs']['data']['ltt_dive_in_page_driver_layout'] : '';
	$carousel_layouts = apply_filters( 'ltt_dive_in_page_driver_carousel_layouts', array( 'six_plus_up', 'monthly' ) );

	if ( ! is_array( $carousel_layouts ) ) {
		return false;
	}

	return is_string( $layout ) && in_array( $layout, $carousel_layouts, true );
}

/**
 * Determine whether a parsed Feature Image Driver block uses a carousel.
 *
 * @param array $block Parsed block.
 * @return bool
 */
function ltt_dive_in_feature_image_driver_uses_carousel( $block ) {
	if ( ! is_array( $block ) || 'ltt-dive-in/feature-image-driver' !== ( $block['blockName'] ?? '' ) ) {
		return false;
	}

	$variant = isset( $block['attrs']['data']['ltt_dive_in_feature_image_driver_variant'] ) ? $block['attrs']['data']['ltt_dive_in_feature_image_driver_variant'] : '';

	return 'carousel' === $variant;
}

/**
 * Determine whether a parsed Video Module block uses the carousel variant.
 *
 * @param array $block Parsed block.
 * @return bool
 */
function ltt_dive_in_video_module_uses_carousel( $block ) {
	if ( ! is_array( $block ) || 'ltt-dive-in/video-module' !== ( $block['blockName'] ?? '' ) ) {
		return false;
	}

	$variant = isset( $block['attrs']['data']['ltt_dive_in_video_module_variant'] ) ? $block['attrs']['data']['ltt_dive_in_video_module_variant'] : '';

	return 'carousel' === $variant;
}

/**
 * Enqueue carousel assets before the document head is printed when possible.
 *
 * @return void
 */
function ltt_dive_in_maybe_enqueue_page_driver_carousel_assets() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! has_blocks( $post->post_content ) ) {
		return;
	}

	if ( ltt_dive_in_block_tree_contains( parse_blocks( $post->post_content ), 'ltt_dive_in_page_driver_uses_carousel' ) ) {
		ltt_dive_in_enqueue_page_driver_carousel_assets();
	}
}
// Run before Core collects and hoists on-demand block styles. This keeps the
// vendor foundation before page-drivers.css in the final document cascade.
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_maybe_enqueue_page_driver_carousel_assets', 5 );

/**
 * Enqueue Feature Image Driver carousel assets before the document head.
 *
 * @return void
 */
function ltt_dive_in_maybe_enqueue_feature_image_driver_carousel_assets() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! has_blocks( $post->post_content ) ) {
		return;
	}

	if ( ltt_dive_in_block_tree_contains( parse_blocks( $post->post_content ), 'ltt_dive_in_feature_image_driver_uses_carousel' ) ) {
		ltt_dive_in_enqueue_feature_image_driver_carousel_assets();
	}
}
// Match the Page Driver priority so Swiper precedes every carousel component.
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_maybe_enqueue_feature_image_driver_carousel_assets', 5 );

/**
 * Enqueue Video Module carousel foundations before the document head.
 *
 * @return void
 */
function ltt_dive_in_maybe_enqueue_video_module_carousel_assets() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! has_blocks( $post->post_content ) ) {
		return;
	}

	if ( ltt_dive_in_block_tree_contains( parse_blocks( $post->post_content ), 'ltt_dive_in_video_module_uses_carousel' ) ) {
		ltt_dive_in_enqueue_video_module_carousel_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_maybe_enqueue_video_module_carousel_assets', 5 );

/**
 * Load shared carousel styles before block styles inside the editor canvas.
 *
 * All registered block variants can be previewed in the editor, so the shared
 * visual foundations are loaded there without enqueueing either initializer.
 *
 * @return void
 */
function ltt_dive_in_enqueue_carousel_editor_styles() {
	if ( ! is_admin() ) {
		return;
	}

	wp_enqueue_style( 'ltt-dive-in-swiper' );
	wp_enqueue_style( 'ltt-dive-in-slider-navigation' );
	wp_enqueue_style( 'ltt-dive-in-carousel-indicators' );
}
add_action( 'enqueue_block_assets', 'ltt_dive_in_enqueue_carousel_editor_styles', 5 );

function ltt_dive_in_enqueue_page_driver_editor_assets() {
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/page-driver-fields.js';

	wp_enqueue_script( 'ltt-dive-in-page-driver-editor', LTT_DIVE_IN_URI . '/assets/js/admin/page-driver-fields.js', array( 'acf-input', 'jquery', 'wp-data' ), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, true );
	wp_localize_script(
		'ltt-dive-in-page-driver-editor',
		'ltt_dive_in_page_driver_editor',
		array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'ltt_dive_in_page_driver_terms' ),
			'selectPages'     => __( 'Select ', 'ltt-dive-in' ),
			'currentlySelected' => __( 'Currently selected: ', 'ltt-dive-in' ),
			'filtersWithoutPages' => __( 'These filter values are not assigned to any selected hub page: ', 'ltt-dive-in' ),
			'filterValueCount' => __( 'Choose between two and five filter values.', 'ltt-dive-in' ),
			'tileLimits'      => array(
				'one_up'      => array( 'min' => 1, 'max' => 1, 'label' => __( 'exactly 1 hub page', 'ltt-dive-in' ) ),
				'two_up'      => array( 'min' => 2, 'max' => 2, 'label' => __( 'exactly 2 hub pages', 'ltt-dive-in' ) ),
				'three_up'    => array( 'min' => 3, 'max' => 3, 'label' => __( 'exactly 3 hub pages', 'ltt-dive-in' ) ),
				'four_up'     => array( 'min' => 4, 'max' => 4, 'label' => __( 'exactly 4 hub pages', 'ltt-dive-in' ) ),
				'five_up'     => array( 'min' => 5, 'max' => 5, 'label' => __( 'exactly 5 hub pages', 'ltt-dive-in' ) ),
				'six_plus_up' => array( 'min' => 6, 'max' => 12, 'label' => __( 'between 6 and 12 hub pages', 'ltt-dive-in' ) ),
				'monthly'     => array( 'min' => 8, 'max' => 14, 'label' => __( 'between 8 and 14 hub pages', 'ltt-dive-in' ) ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_page_driver_editor_assets' );

/**
 * Load Feature Image Driver item-limit feedback in the block editor.
 *
 * @return void
 */
function ltt_dive_in_enqueue_feature_image_driver_editor_assets() {
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/feature-image-driver-fields.js';

	wp_enqueue_script( 'ltt-dive-in-feature-image-driver-editor', LTT_DIVE_IN_URI . '/assets/js/admin/feature-image-driver-fields.js', array( 'acf-input', 'jquery', 'wp-data' ), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, true );
	wp_localize_script(
		'ltt-dive-in-feature-image-driver-editor',
		'ltt_dive_in_feature_image_driver_editor',
		array(
			'requirementPrefix' => __( 'This variant requires ', 'ltt-dive-in' ),
			'currentlyActive'   => __( 'Currently active: ', 'ltt-dive-in' ),
			'removeItem'        => __( 'Remove feature item', 'ltt-dive-in' ),
			'minimumItem'       => __( 'At least one feature item is required.', 'ltt-dive-in' ),
			'itemLimits'        => array(
				'single'   => array( 'min' => 1, 'max' => 1, 'label' => __( 'exactly 1 active item', 'ltt-dive-in' ) ),
				'short'    => array( 'min' => 1, 'max' => 1, 'label' => __( 'exactly 1 active item', 'ltt-dive-in' ) ),
				'carousel' => array( 'min' => 2, 'max' => 3, 'label' => __( 'between 2 and 3 active items', 'ltt-dive-in' ) ),
				'stacked'  => array( 'min' => 2, 'max' => 5, 'label' => __( 'between 2 and 5 active items', 'ltt-dive-in' ) ),
			),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_feature_image_driver_editor_assets' );

/**
 * Load Static Image Cluster count feedback in the block editor.
 *
 * @return void
 */
function ltt_dive_in_enqueue_static_image_cluster_editor_assets() {
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/static-image-cluster-fields.js';
	$limits      = function_exists( 'ltt_dive_in_get_static_image_cluster_image_limits' ) ? ltt_dive_in_get_static_image_cluster_image_limits() : array();
	$labels      = array(
		'hero_caption'   => __( 'at least 1 image; displays the first image', 'ltt-dive-in' ),
		'one_up'         => __( 'at least 1 image; displays the first image', 'ltt-dive-in' ),
		'two_up'         => __( 'at least 2 images; displays the first 2 images', 'ltt-dive-in' ),
		'three_up'       => __( 'at least 3 images; displays the first 3 images', 'ltt-dive-in' ),
		'inline_four_up' => __( 'at least 4 images; displays the first 4 images', 'ltt-dive-in' ),
		'staggered_four' => __( 'at least 4 images; displays the first 4 images', 'ltt-dive-in' ),
		'five_plus'      => __( 'at least 5 images', 'ltt-dive-in' ),
		'side_by_side'   => __( 'at least 1 image; displays the first image', 'ltt-dive-in' ),
		'inspired'       => __( 'at least 6 images', 'ltt-dive-in' ),
	);

	foreach ( $limits as $variant => &$limit ) {
		$limit['label'] = $labels[ $variant ] ?? '';
	}
	unset( $limit );

	wp_enqueue_script( 'ltt-dive-in-static-image-cluster-editor', LTT_DIVE_IN_URI . '/assets/js/admin/static-image-cluster-fields.js', array( 'acf-input', 'jquery', 'wp-data' ), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, true );
	wp_localize_script(
		'ltt-dive-in-static-image-cluster-editor',
		'ltt_dive_in_static_image_cluster_editor',
		array(
			'requirementPrefix' => __( 'This variant requires ', 'ltt-dive-in' ),
			'currentlySelected' => __( 'Currently selected: ', 'ltt-dive-in' ),
			'headingRequired'   => __( 'Section heading is required for this variant.', 'ltt-dive-in' ),
			'imageLimits'       => $limits,
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_static_image_cluster_editor_assets' );

function ltt_dive_in_enqueue_activities_editor_assets() {
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/activities-fields.js';

	wp_enqueue_script( 'ltt-dive-in-activities-editor', LTT_DIVE_IN_URI . '/assets/js/admin/activities-fields.js', array( 'acf-input', 'jquery', 'wp-data' ), file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION, true );
}
add_action( 'enqueue_block_editor_assets', 'ltt_dive_in_enqueue_activities_editor_assets' );
