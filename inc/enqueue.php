<?php
/**
 * Theme scripts and styles.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue public assets.
 */
function ltt_dive_in_enqueue_assets() {
	$is_503_view           = ltt_dive_in_is_503_view();
	$style_path             = LTT_DIVE_IN_DIR . '/assets/css/main.css';
	$header_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/header.css';
	$search_bar_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/search-bar.css';
	$footer_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/footer.css';
	$buttons_style_path     = LTT_DIVE_IN_DIR . '/assets/css/components/buttons.css';
	$select_style_path      = LTT_DIVE_IN_DIR . '/assets/css/components/select.css';
	$activities_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/home-activities.css';
	$front_page_style_path  = LTT_DIVE_IN_DIR . '/assets/css/templates/front-page.css';
	$page_hero_style_path   = LTT_DIVE_IN_DIR . '/assets/css/components/page-hero.css';
	$page_hero_script_path  = LTT_DIVE_IN_DIR . '/assets/js/components/page-hero.js';
	$page_style_path        = LTT_DIVE_IN_DIR . '/assets/css/templates/page.css';
	$single_style_path      = LTT_DIVE_IN_DIR . '/assets/css/templates/single.css';
	$stories_hero_path      = LTT_DIVE_IN_DIR . '/assets/css/components/stories-hero.css';
	$select_script_path     = LTT_DIVE_IN_DIR . '/assets/js/components/select.js';
	$scroll_link_path       = LTT_DIVE_IN_DIR . '/assets/js/components/scroll-link.js';
	$script_path            = LTT_DIVE_IN_DIR . '/assets/js/main.js';

	wp_enqueue_style(
		'ltt-dive-in-style',
		LTT_DIVE_IN_URI . '/assets/css/main.css',
		array(),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-search-bar',
		LTT_DIVE_IN_URI . '/assets/css/components/search-bar.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $search_bar_style_path ) ? (string) filemtime( $search_bar_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-header',
		LTT_DIVE_IN_URI . '/assets/css/components/header.css',
		array( 'ltt-dive-in-style', 'ltt-dive-in-search-bar' ),
		file_exists( $header_style_path ) ? (string) filemtime( $header_style_path ) : LTT_DIVE_IN_VERSION
	);

	if ( ! $is_503_view && ltt_dive_in_get_section_navigation() ) {
		$section_style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/section-navigation.css';
		$section_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/section-navigation.js';

		wp_enqueue_style(
			'ltt-dive-in-section-navigation',
			LTT_DIVE_IN_URI . '/assets/css/components/section-navigation.css',
			array( 'ltt-dive-in-style', 'ltt-dive-in-swiper' ),
			file_exists( $section_style_path ) ? (string) filemtime( $section_style_path ) : LTT_DIVE_IN_VERSION
		);
		wp_enqueue_script(
			'ltt-dive-in-section-navigation',
			LTT_DIVE_IN_URI . '/assets/js/components/section-navigation.js',
			array( 'ltt-dive-in-swiper' ),
			file_exists( $section_script_path ) ? (string) filemtime( $section_script_path ) : LTT_DIVE_IN_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		if ( is_page_template( 'templates/current-conditions.php' ) ) {
			$seamless_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/seamless-navigation.js';

			wp_enqueue_script(
				'ltt-dive-in-seamless-navigation',
				LTT_DIVE_IN_URI . '/assets/js/components/seamless-navigation.js',
				array(),
				file_exists( $seamless_script_path ) ? (string) filemtime( $seamless_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);

			// Loaded on every tab: seamless navigation can swap the roads module in from another tab.
			$road_conditions_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/road-conditions.js';

			wp_enqueue_script(
				'ltt-dive-in-road-conditions',
				LTT_DIVE_IN_URI . '/assets/js/components/road-conditions.js',
				array(),
				file_exists( $road_conditions_script_path ) ? (string) filemtime( $road_conditions_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);

			$ski_cams_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/ski-cams.js';

			wp_enqueue_script(
				'ltt-dive-in-ski-cams',
				LTT_DIVE_IN_URI . '/assets/js/components/ski-cams.js',
				array(),
				file_exists( $ski_cams_script_path ) ? (string) filemtime( $ski_cams_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);

			// Loads the Prism Cam provider files only when a viewer is on the page, including after seamless navigation.
			$prism_cams_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/prism-cams.js';

			wp_enqueue_script(
				'ltt-dive-in-prism-cams',
				LTT_DIVE_IN_URI . '/assets/js/components/prism-cams.js',
				array(),
				file_exists( $prism_cams_script_path ) ? (string) filemtime( $prism_cams_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);

			$current_weather_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/current-weather.js';

			wp_enqueue_script(
				'ltt-dive-in-current-weather',
				LTT_DIVE_IN_URI . '/assets/js/components/current-weather.js',
				array(),
				file_exists( $current_weather_script_path ) ? (string) filemtime( $current_weather_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	wp_enqueue_style(
		'ltt-dive-in-footer',
		LTT_DIVE_IN_URI . '/assets/css/components/footer.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $footer_style_path ) ? (string) filemtime( $footer_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-buttons',
		LTT_DIVE_IN_URI . '/assets/css/components/buttons.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $buttons_style_path ) ? (string) filemtime( $buttons_style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_style(
		'ltt-dive-in-select',
		LTT_DIVE_IN_URI . '/assets/css/components/select.css',
		array( 'ltt-dive-in-style' ),
		file_exists( $select_style_path ) ? (string) filemtime( $select_style_path ) : LTT_DIVE_IN_VERSION
	);

	if ( is_404() || $is_503_view ) {
		if ( $is_503_view && ltt_dive_in_get_maintenance_prism_camera() ) {
			$maintenance_prism_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/prism-cams.js';

			wp_enqueue_script(
				'ltt-dive-in-prism-cams',
				LTT_DIVE_IN_URI . '/assets/js/components/prism-cams.js',
				array(),
				file_exists( $maintenance_prism_script_path ) ? (string) filemtime( $maintenance_prism_script_path ) : LTT_DIVE_IN_VERSION,
				array( 'strategy' => 'defer', 'in_footer' => true )
			);
		}

		$error_background_config = ltt_dive_in_get_error_background_config( $is_503_view ? '503' : '404' );
		$has_background_carousel = count( $error_background_config['images'] ) > 1;
		$error_component_path = LTT_DIVE_IN_DIR . '/assets/css/components/error-page.css';
		$error_background_script_path = LTT_DIVE_IN_DIR . '/assets/js/components/error-background.js';
		$error_template       = $is_503_view ? '503' : '404';
		$error_template_path  = LTT_DIVE_IN_DIR . '/assets/css/templates/' . $error_template . '.css';
		$error_style_deps     = array( 'ltt-dive-in-style', 'ltt-dive-in-search-bar' );

		if ( $has_background_carousel ) {
			$error_style_deps[] = 'ltt-dive-in-swiper';
		}

		wp_enqueue_style(
			'ltt-dive-in-error-page',
			LTT_DIVE_IN_URI . '/assets/css/components/error-page.css',
			$error_style_deps,
			file_exists( $error_component_path ) ? (string) filemtime( $error_component_path ) : LTT_DIVE_IN_VERSION
		);
		wp_enqueue_style(
			'ltt-dive-in-' . $error_template,
			LTT_DIVE_IN_URI . '/assets/css/templates/' . $error_template . '.css',
			array( 'ltt-dive-in-error-page' ),
			file_exists( $error_template_path ) ? (string) filemtime( $error_template_path ) : LTT_DIVE_IN_VERSION
		);
		if ( $error_background_config['images'] ) {
			wp_enqueue_script(
				'ltt-dive-in-error-background',
				LTT_DIVE_IN_URI . '/assets/js/components/error-background.js',
				$has_background_carousel ? array( 'ltt-dive-in-swiper' ) : array(),
				file_exists( $error_background_script_path ) ? (string) filemtime( $error_background_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( is_404() && ! $is_503_view ) {
		$error_search_script_path = LTT_DIVE_IN_DIR . '/assets/js/templates/404.js';

		wp_enqueue_script(
			'ltt-dive-in-404-search',
			LTT_DIVE_IN_URI . '/assets/js/templates/404.js',
			array(),
			file_exists( $error_search_script_path ) ? (string) filemtime( $error_search_script_path ) : LTT_DIVE_IN_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	if ( ! $is_503_view && is_front_page() ) {
		wp_enqueue_style(
			'ltt-dive-in-front-page',
			LTT_DIVE_IN_URI . '/assets/css/templates/front-page.css',
			array( 'ltt-dive-in-style', 'ltt-dive-in-header' ),
			file_exists( $front_page_style_path ) ? (string) filemtime( $front_page_style_path ) : LTT_DIVE_IN_VERSION
		);

		wp_enqueue_style(
			'ltt-dive-in-home-activities',
			LTT_DIVE_IN_URI . '/assets/css/components/home-activities.css',
			array( 'ltt-dive-in-buttons', 'ltt-dive-in-tags' ),
			file_exists( $activities_style_path ) ? (string) filemtime( $activities_style_path ) : LTT_DIVE_IN_VERSION
		);
	}

	// Internal pages (page.php and Full Width); Blank Canvas owns its own layout.
	if ( ! $is_503_view && is_page() && ! is_front_page() && ! is_page_template( 'templates/blank-canvas.php' ) ) {
		wp_enqueue_style(
			'ltt-dive-in-page',
			LTT_DIVE_IN_URI . '/assets/css/templates/page.css',
			array( 'ltt-dive-in-style' ),
			file_exists( $page_style_path ) ? (string) filemtime( $page_style_path ) : LTT_DIVE_IN_VERSION
		);

		if ( is_page_template( 'templates/current-conditions.php' ) ) {
			$current_conditions_style_path = LTT_DIVE_IN_DIR . '/assets/css/templates/current-conditions.css';

			wp_enqueue_style(
				'ltt-dive-in-current-conditions',
				LTT_DIVE_IN_URI . '/assets/css/templates/current-conditions.css',
				array( 'ltt-dive-in-style', 'ltt-dive-in-page', 'ltt-dive-in-buttons', 'ltt-dive-in-tags', 'ltt-dive-in-category-selection', ltt_dive_in_register_current_conditions_hero_style() ),
				file_exists( $current_conditions_style_path ) ? (string) filemtime( $current_conditions_style_path ) : LTT_DIVE_IN_VERSION
			);
		}

		if ( ltt_dive_in_is_events_page() ) {
			ltt_dive_in_enqueue_events_page_assets();
		}

		if ( ltt_dive_in_is_event_submission_page() ) {
			ltt_dive_in_enqueue_event_submission_assets();
		}
	}

	if ( ! $is_503_view && is_singular( array( 'post', 'listing' ) ) ) {
		wp_enqueue_style(
			'ltt-dive-in-single',
			LTT_DIVE_IN_URI . '/assets/css/templates/single.css',
			array( 'ltt-dive-in-style' ),
			file_exists( $single_style_path ) ? (string) filemtime( $single_style_path ) : LTT_DIVE_IN_VERSION
		);

		wp_enqueue_style(
			'ltt-dive-in-stories-hero',
			LTT_DIVE_IN_URI . '/assets/css/components/stories-hero.css',
			array( 'ltt-dive-in-style' ),
			file_exists( $stories_hero_path ) ? (string) filemtime( $stories_hero_path ) : LTT_DIVE_IN_VERSION
		);

		// The end-of-post Page Driver reuses the End Page Cluster styles.
		if ( function_exists( 'ltt_dive_in_get_post_page_driver' ) && ltt_dive_in_get_post_page_driver( get_queried_object_id() ) ) {
			wp_enqueue_style( 'ltt-dive-in-end-page-cluster' );
		}

		// The hero's "Show Summary" disclosure reuses the shared accordion script.
		if ( '' !== ltt_dive_in_get_ai_summary( get_queried_object_id() ) ) {
			wp_enqueue_script( 'ltt-dive-in-accordion' );
		}
	}

	if ( ! $is_503_view && ltt_dive_in_has_page_hero() ) {
		if ( 'meetings' === ltt_dive_in_get_page_header_hero_variant() ) {
			ltt_dive_in_enqueue_meetings_hero_assets();
		} else {
			wp_enqueue_style(
				'ltt-dive-in-page-hero',
				LTT_DIVE_IN_URI . '/assets/css/components/page-hero.css',
				array( 'ltt-dive-in-style', 'ltt-dive-in-buttons' ),
				file_exists( $page_hero_style_path ) ? (string) filemtime( $page_hero_style_path ) : LTT_DIVE_IN_VERSION
			);

			wp_enqueue_script(
				'ltt-dive-in-page-hero',
				LTT_DIVE_IN_URI . '/assets/js/components/page-hero.js',
				array(),
				file_exists( $page_hero_script_path ) ? (string) filemtime( $page_hero_script_path ) : LTT_DIVE_IN_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => false,
				)
			);
		}
	}

	wp_enqueue_script(
		'ltt-dive-in-select',
		LTT_DIVE_IN_URI . '/assets/js/components/select.js',
		array(),
		file_exists( $select_script_path ) ? (string) filemtime( $select_script_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => false,
		)
	);

	$alpine_deps = array( 'ltt-dive-in-select' );

	// Hero "Scroll for more" links; Alpine components must register before Alpine starts.
	if ( ! $is_503_view && ( is_front_page() || ltt_dive_in_has_page_hero() ) ) {
		wp_register_script(
			'ltt-dive-in-scroll-link',
			LTT_DIVE_IN_URI . '/assets/js/components/scroll-link.js',
			array(),
			file_exists( $scroll_link_path ) ? (string) filemtime( $scroll_link_path ) : LTT_DIVE_IN_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);
		$alpine_deps[] = 'ltt-dive-in-scroll-link';
	}

	wp_enqueue_script(
		'ltt-dive-in-alpine',
		LTT_DIVE_IN_URI . '/assets/js/vendor/alpine.min.js',
		$alpine_deps,
		'3.17.2',
		array(
			'strategy'  => 'defer',
			'in_footer' => false,
		)
	);

	wp_enqueue_script(
		'ltt-dive-in-script',
		LTT_DIVE_IN_URI . '/assets/js/main.js',
		array( 'ltt-dive-in-alpine' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// single.php does not render comments.
	if ( is_singular() && ! is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_enqueue_assets' );

/**
 * Register the Current Conditions hero style shared by page templates.
 *
 * @return string Style handle.
 */
function ltt_dive_in_register_current_conditions_hero_style() {
	$path = LTT_DIVE_IN_DIR . '/assets/css/components/current-conditions-hero.css';

	if ( ! wp_style_is( 'ltt-dive-in-current-conditions-hero', 'registered' ) ) {
		wp_register_style(
			'ltt-dive-in-current-conditions-hero',
			LTT_DIVE_IN_URI . '/assets/css/components/current-conditions-hero.css',
			array( 'ltt-dive-in-style' ),
			file_exists( $path ) ? (string) filemtime( $path ) : LTT_DIVE_IN_VERSION
		);
	}

	return 'ltt-dive-in-current-conditions-hero';
}

/**
 * Enqueue the Events page template assets.
 *
 * templates/events.php → templates/events.css, with the event card, text
 * input, checkbox bubble, load more, expand CTA, select, search bar, and tag
 * components.
 */
function ltt_dive_in_enqueue_events_page_assets() {
	$version = static function ( $relative ) {
		$path = LTT_DIVE_IN_DIR . $relative;

		return file_exists( $path ) ? (string) filemtime( $path ) : LTT_DIVE_IN_VERSION;
	};

	if ( ltt_dive_in_is_event_detail() ) {
		ltt_dive_in_enqueue_event_detail_assets( $version );
		return;
	}

	foreach ( array( 'text-input', 'checkbox-bubble', 'load-more', 'expand-cta', 'events-intro' ) as $component ) {
		wp_register_style( 'ltt-dive-in-' . $component, LTT_DIVE_IN_URI . '/assets/css/components/' . $component . '.css', array( 'ltt-dive-in-style' ), $version( '/assets/css/components/' . $component . '.css' ) );
	}

	wp_enqueue_style(
		'ltt-dive-in-events',
		LTT_DIVE_IN_URI . '/assets/css/templates/events.css',
		array( 'ltt-dive-in-page', 'ltt-dive-in-buttons', 'ltt-dive-in-select', 'ltt-dive-in-search-bar', 'ltt-dive-in-tags', 'ltt-dive-in-event-card', 'ltt-dive-in-text-input', 'ltt-dive-in-checkbox-bubble', 'ltt-dive-in-load-more', 'ltt-dive-in-expand-cta', 'ltt-dive-in-events-intro' ),
		$version( '/assets/css/templates/events.css' )
	);

	wp_enqueue_script(
		'ltt-dive-in-events',
		LTT_DIVE_IN_URI . '/assets/js/templates/events.js',
		array(),
		$version( '/assets/js/templates/events.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script(
		'ltt-dive-in-events',
		'ltt_dive_in_events',
		array(
			'loading'   => __( 'Loading events…', 'ltt-dive-in' ),
			/* translators: 1: number of events shown, 2: total matching events. */
			'showing'   => __( 'Showing %1$d of %2$d events.', 'ltt-dive-in' ),
			/* translators: %d: number of events added. */
			'loaded'    => __( '%d more events loaded.', 'ltt-dive-in' ),
			'noResults' => __( 'No events match your filters.', 'ltt-dive-in' ),
			'error'     => __( 'Events could not be loaded. Please try again.', 'ltt-dive-in' ),
		)
	);
}

/**
 * Enqueue the event detail view of the Events page template.
 *
 * templates/events.php (detail view) → templates/events-detail.css and
 * events-detail.js, with the event card, Event Driver carousel, slider
 * navigation, carousel indicator, and Swiper assets.
 *
 * @param callable $version Returns a file version from a theme-relative path.
 */
function ltt_dive_in_enqueue_event_detail_assets( $version ) {
	wp_enqueue_style(
		'ltt-dive-in-events-detail',
		LTT_DIVE_IN_URI . '/assets/css/templates/events-detail.css',
		array( 'ltt-dive-in-page', 'ltt-dive-in-buttons', 'ltt-dive-in-tags', 'ltt-dive-in-event-card', 'ltt-dive-in-event-driver', 'ltt-dive-in-slider-navigation', 'ltt-dive-in-carousel-indicators', 'ltt-dive-in-swiper' ),
		$version( '/assets/css/templates/events-detail.css' )
	);

	wp_enqueue_script( 'ltt-dive-in-event-driver' );
	wp_enqueue_script(
		'ltt-dive-in-events-detail',
		LTT_DIVE_IN_URI . '/assets/js/templates/events-detail.js',
		array( 'ltt-dive-in-swiper' ),
		$version( '/assets/js/templates/events-detail.js' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script(
		'ltt-dive-in-events-detail',
		'ltt_dive_in_event_detail',
		array(
			'carousel'   => __( 'carousel', 'ltt-dive-in' ),
			'slide'      => __( 'slide', 'ltt-dive-in' ),
			/* translators: Swiper replaces {{index}} and {{slidesLength}}; keep both placeholders. */
			'slideLabel' => __( '{{index}} of {{slidesLength}}', 'ltt-dive-in' ),
			/* translators: Swiper replaces {{index}}; keep the placeholder. */
			'goToPhoto'  => __( 'Go to photo {{index}}', 'ltt-dive-in' ),
		)
	);
}

/**
 * Preload fonts used above the fold.
 *
 * Medium and Bold are intentionally left on demand. Big Caslon is limited to templates
 * whose hero uses it immediately, including the Stories Hero on posts.
 *
 * @param array[] $preload_resources Resources and attributes to preload.
 * @return array[]
 */
function ltt_dive_in_preload_fonts( $preload_resources ) {
	$preload_resources[] = array(
		'href'        => LTT_DIVE_IN_URI . '/assets/fonts/gotham/Gotham-Book.woff2',
		'as'          => 'font',
		'type'        => 'font/woff2',
		'crossorigin' => 'anonymous',
	);

	if (
		is_front_page()
		|| is_404()
		|| ltt_dive_in_is_503_view()
		|| ( ltt_dive_in_has_page_hero() && 'standard' === ltt_dive_in_get_page_header_hero_variant() )
		|| ltt_dive_in_is_events_page()
		|| ltt_dive_in_is_event_submission_page()
		|| is_singular( array( 'post', 'listing' ) )
	) {
		$preload_resources[] = array(
			'href'        => LTT_DIVE_IN_URI . '/assets/fonts/big-caslon/Big-Caslon-Medium.woff2',
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $preload_resources;
}
add_filter( 'wp_preload_resources', 'ltt_dive_in_preload_fonts' );

/**
 * Whether the current view renders the internal page hero.
 *
 * @return bool
 */
function ltt_dive_in_has_page_hero() {
	return is_page() && ! is_front_page() && ! is_page_template( array( 'templates/blank-canvas.php', 'templates/events.php', 'templates/event-submission.php' ) );
}

/**
 * Load foundations scoped to block-editor content, not the admin interface.
 */
function ltt_dive_in_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/main.css' );
}
add_action( 'after_setup_theme', 'ltt_dive_in_editor_styles' );
