<?php
/**
 * Theme setup and widget areas.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme defaults and WordPress features.
 */
function ltt_dive_in_setup() {
	load_theme_textdomain( 'ltt-dive-in', LTT_DIVE_IN_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
			'navigation-widgets',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary'           => __( 'Primary Menu', 'ltt-dive-in' ),
			'footer_navigation' => __( 'Footer Navigation', 'ltt-dive-in' ),
			'footer'            => __( 'Footer Legal', 'ltt-dive-in' ),
		)
	);
}
add_action( 'after_setup_theme', 'ltt_dive_in_setup' );

/**
 * Set the content width in pixels.
 */
function ltt_dive_in_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'ltt_dive_in_content_width', 760 );
}
add_action( 'after_setup_theme', 'ltt_dive_in_content_width', 0 );

/**
 * Register widget areas.
 */
function ltt_dive_in_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'ltt-dive-in' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Widgets shown in the blog sidebar.', 'ltt-dive-in' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Newsletter Form', 'ltt-dive-in' ),
			'id'            => 'footer-1',
			'description'   => __( 'Add the production newsletter form widget shown in the site footer.', 'ltt-dive-in' ),
			'before_widget' => '<div id="%1$s" class="site-footer__newsletter-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="site-footer__newsletter-widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'ltt_dive_in_widgets_init' );
