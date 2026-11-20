<?php
/**
 * Site alert settings, data, and assets.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alert types with their default label, icon, and colors.
 *
 * @return array<string, array<string, string>>
 */
function ltt_dive_in_get_site_alert_types() {
	return array(
		'travel'       => array(
			'name'       => __( 'Travel Alert', 'ltt-dive-in' ),
			'icon'       => 'warning',
			'background' => '#E1841A',
			'text'       => '#FFFFFF',
		),
		'announcement' => array(
			'name'       => __( 'Announcement', 'ltt-dive-in' ),
			'icon'       => 'announcement',
			'background' => '#61C2B7',
			'text'       => '#FFFFFF',
		),
	);
}

/**
 * Register the Site Alert options page and fields.
 */
function ltt_dive_in_register_site_alert_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Site Alert', 'ltt-dive-in' ),
			'menu_title'  => __( 'Site Alert', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-site-alert',
			'parent_slug' => 'themes.php',
			'capability'  => 'edit_theme_options',
			'redirect'    => false,
		)
	);

	$type_choices = array();
	foreach ( ltt_dive_in_get_site_alert_types() as $type => $config ) {
		$type_choices[ $type ] = $config['name'];
	}

	$enabled_rule = array(
		array(
			array(
				'field'    => 'field_ltt_dive_in_site_alert_enabled',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_site_alert',
			'title'    => __( 'LTT Dive In — Site Alert', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'           => 'field_ltt_dive_in_site_alert_enabled',
					'label'         => __( 'Show alert', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_site_alert_enabled',
					'type'          => 'true_false',
					'instructions'  => __( 'Displays the alert above the site header.', 'ltt-dive-in' ),
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_type',
					'label'             => __( 'Type', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_type',
					'type'              => 'button_group',
					'instructions'      => __( 'Sets the default icon, background, and accessible name.', 'ltt-dive-in' ),
					'choices'           => $type_choices,
					'default_value'     => 'travel',
					'return_format'     => 'value',
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_sticky',
					'label'             => __( 'Sticky', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_sticky',
					'type'              => 'true_false',
					'instructions'      => __( 'Keeps the alert at the top of the screen while visitors scroll. When off, it scrolls away with the page.', 'ltt-dive-in' ),
					'ui'                => 1,
					'default_value'     => 1,
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_label',
					'label'             => __( 'Label', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_label',
					'type'              => 'text',
					'instructions'      => __( 'Optional. Short bold text shown before the message.', 'ltt-dive-in' ),
					'maxlength'         => 40,
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_message',
					'label'             => __( 'Message', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_message',
					'type'              => 'textarea',
					'instructions'      => __( 'Keep it brief; the alert stays visible while visitors scroll.', 'ltt-dive-in' ),
					'required'          => 1,
					'maxlength'         => 300,
					'rows'              => 3,
					'new_lines'         => '',
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_link',
					'label'             => __( 'Link', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_link',
					'type'              => 'link',
					'instructions'      => __( 'Optional. Internal or external page. The link is hidden until both a label and destination are provided.', 'ltt-dive-in' ),
					'return_format'     => 'array',
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_icon',
					'label'             => __( 'Icon', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_icon',
					'type'              => 'image',
					'instructions'      => __( 'Optional. Replaces the default icon for the type. Use a single-color SVG (preferred) or a PNG with a transparent background; it is shown in the alert text color.', 'ltt-dive-in' ),
					'return_format'     => 'id',
					'preview_size'      => 'thumbnail',
					'library'           => 'all',
					'mime_types'        => 'svg,png',
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_background_hex',
					'label'             => __( 'Background color', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_background_hex',
					'type'              => 'color_picker',
					'instructions'      => __( 'Changing the type resets both colors to that type\'s defaults.', 'ltt-dive-in' ),
					'default_value'     => '#E1841A',
					'enable_opacity'    => 0,
					'return_format'     => 'string',
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_text_hex',
					'label'             => __( 'Text color', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_text_hex',
					'type'              => 'color_picker',
					'instructions'      => __( 'Used for the text and icon. A notice appears after saving if the contrast with the background is below 4.5:1.', 'ltt-dive-in' ),
					'default_value'     => '#FFFFFF',
					'enable_opacity'    => 0,
					'return_format'     => 'string',
					'wrapper'           => array( 'width' => '50' ),
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_display',
					'label'             => __( 'Display on', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_display',
					'type'              => 'select',
					'choices'           => array(
						'all'     => __( 'Entire site', 'ltt-dive-in' ),
						'include' => __( 'Only the selected pages', 'ltt-dive-in' ),
						'exclude' => __( 'Entire site except the selected pages', 'ltt-dive-in' ),
					),
					'default_value'     => 'all',
					'return_format'     => 'value',
					'conditional_logic' => $enabled_rule,
				),
				array(
					'key'               => 'field_ltt_dive_in_site_alert_pages',
					'label'             => __( 'Pages', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_site_alert_pages',
					'type'              => 'post_object',
					'post_type'         => array( 'page', 'post' ),
					'multiple'          => 1,
					'allow_null'        => 1,
					'return_format'     => 'id',
					'ui'                => 1,
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_ltt_dive_in_site_alert_enabled',
								'operator' => '==',
								'value'    => '1',
							),
							array(
								'field'    => 'field_ltt_dive_in_site_alert_display',
								'operator' => '!=',
								'value'    => 'all',
							),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-site-alert',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_site_alert_settings' );

/**
 * WCAG contrast ratio between two hex colors.
 *
 * @param string $first  Hex color.
 * @param string $second Hex color.
 * @return float Ratio from 1 to 21, or 0 when a color is invalid.
 */
function ltt_dive_in_get_contrast_ratio( $first, $second ) {
	$luminance = function ( $hex ) {
		$hex = sanitize_hex_color( $hex );

		if ( ! $hex ) {
			return null;
		}

		$hex = ltrim( $hex, '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		$channels = array();
		foreach ( str_split( $hex, 2 ) as $channel ) {
			$value      = hexdec( $channel ) / 255;
			$channels[] = $value <= 0.03928 ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
		}

		return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
	};

	$first_luminance  = $luminance( $first );
	$second_luminance = $luminance( $second );

	if ( null === $first_luminance || null === $second_luminance ) {
		return 0.0;
	}

	return ( max( $first_luminance, $second_luminance ) + 0.05 ) / ( min( $first_luminance, $second_luminance ) + 0.05 );
}

/**
 * Warn on the Site Alert screen when the saved colors have low contrast.
 *
 * Editors may keep the colors; the notice only informs them.
 */
function ltt_dive_in_site_alert_contrast_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || false === strpos( (string) $screen->id, 'ltt-dive-in-site-alert' ) || ! function_exists( 'get_field' ) ) {
		return;
	}

	$colors = ltt_dive_in_get_site_alert_colors( (string) get_field( 'ltt_dive_in_site_alert_type', 'option' ) );

	$ratio = ltt_dive_in_get_contrast_ratio( $colors['background'], $colors['text'] );

	if ( ! $ratio || $ratio >= 4.5 ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: %s: contrast ratio, for example 2.8. */
				__( 'The alert text and background colors have a contrast ratio of %s:1. A ratio of at least 4.5:1 is recommended so the alert is easy to read for all visitors.', 'ltt-dive-in' ),
				number_format_i18n( floor( $ratio * 100 ) / 100, 2 )
			)
		)
	);
}
add_action( 'admin_notices', 'ltt_dive_in_site_alert_contrast_notice' );

/**
 * Resolve the alert's background and text colors from the saved settings.
 *
 * @param string $type Alert type, used for the defaults.
 * @return array{background: string, text: string}
 */
function ltt_dive_in_get_site_alert_colors( $type ) {
	$types      = ltt_dive_in_get_site_alert_types();
	$type       = isset( $types[ $type ] ) ? $type : 'travel';
	$background = function_exists( 'get_field' ) ? sanitize_hex_color( (string) get_field( 'ltt_dive_in_site_alert_background_hex', 'option' ) ) : '';
	$text       = function_exists( 'get_field' ) ? sanitize_hex_color( (string) get_field( 'ltt_dive_in_site_alert_text_hex', 'option' ) ) : '';

	return array(
		'background' => $background ? $background : $types[ $type ]['background'],
		'text'       => $text ? $text : $types[ $type ]['text'],
	);
}

/**
 * Get the alert for the current request, or null when none should render.
 *
 * @return array<string, mixed>|null
 */
function ltt_dive_in_get_site_alert() {
	static $alert = false;

	if ( false !== $alert ) {
		return $alert;
	}

	$alert = null;

	if ( ! function_exists( 'get_field' ) || ! get_field( 'ltt_dive_in_site_alert_enabled', 'option' ) ) {
		return $alert;
	}

	$message = trim( (string) get_field( 'ltt_dive_in_site_alert_message', 'option' ) );

	if ( '' === $message ) {
		return $alert;
	}

	$display = (string) get_field( 'ltt_dive_in_site_alert_display', 'option' );

	if ( 'include' === $display || 'exclude' === $display ) {
		$pages      = array_map( 'absint', (array) get_field( 'ltt_dive_in_site_alert_pages', 'option' ) );
		$current_id = ( is_singular() || is_home() ) ? (int) get_queried_object_id() : 0;
		$is_listed  = $current_id && in_array( $current_id, $pages, true );

		if ( ( 'include' === $display ) !== $is_listed ) {
			return $alert;
		}
	}

	$types       = ltt_dive_in_get_site_alert_types();
	$type        = (string) get_field( 'ltt_dive_in_site_alert_type', 'option' );
	$type        = isset( $types[ $type ] ) ? $type : 'travel';
	$colors      = ltt_dive_in_get_site_alert_colors( $type );
	$sticky      = get_field( 'ltt_dive_in_site_alert_sticky', 'option' );
	$sticky      = null === $sticky || (bool) $sticky;
	$label       = trim( (string) get_field( 'ltt_dive_in_site_alert_label', 'option' ) );
	$link        = get_field( 'ltt_dive_in_site_alert_link', 'option' );
	$link        = ( is_array( $link ) && ! empty( $link['url'] ) && '' !== trim( (string) ( $link['title'] ?? '' ) ) ) ? $link : null;
	$icon_id     = absint( get_field( 'ltt_dive_in_site_alert_icon', 'option' ) );
	$icon_url    = $icon_id ? wp_get_attachment_url( $icon_id ) : '';

	$alert = array(
		'type'       => $type,
		'name'       => $types[ $type ]['name'],
		'icon'       => $types[ $type ]['icon'],
		'icon_url'   => $icon_url ? $icon_url : '',
		'colors'     => $colors,
		'sticky'     => $sticky,
		'label'      => $label,
		'message'    => $message,
		'link'       => $link,
		// Changing the content shows the alert again to visitors who dismissed or collapsed it.
		'version'    => substr( md5( wp_json_encode( array( $type, $label, $message, $link ? $link['url'] : '', $link ? $link['title'] : '' ) ) ), 0, 12 ),
	);

	return $alert;
}

/**
 * Load the Site Alert settings behavior on its options screen.
 */
function ltt_dive_in_enqueue_site_alert_admin_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || false === strpos( (string) $screen->id, 'ltt-dive-in-site-alert' ) ) {
		return;
	}

	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/site-alert-fields.js';
	$defaults    = array();

	foreach ( ltt_dive_in_get_site_alert_types() as $type => $config ) {
		$defaults[ $type ] = array(
			'background' => $config['background'],
			'text'       => $config['text'],
		);
	}

	wp_enqueue_script(
		'ltt-dive-in-site-alert-fields',
		LTT_DIVE_IN_URI . '/assets/js/admin/site-alert-fields.js',
		array( 'acf-input', 'jquery' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		true
	);
	wp_add_inline_script( 'ltt-dive-in-site-alert-fields', 'window.ltt_dive_in_site_alert_defaults = ' . wp_json_encode( $defaults ) . ';', 'before' );
}
add_action( 'acf/input/admin_enqueue_scripts', 'ltt_dive_in_enqueue_site_alert_admin_assets' );

/**
 * Add a body class when the alert renders so the header can sit below it.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function ltt_dive_in_site_alert_body_class( $classes ) {
	if ( ltt_dive_in_get_site_alert() ) {
		$classes[] = 'has-site-alert';
	}

	return $classes;
}
add_filter( 'body_class', 'ltt_dive_in_site_alert_body_class' );

/**
 * Enqueue the alert styles and behavior only when an alert renders.
 */
function ltt_dive_in_enqueue_site_alert_assets() {
	if ( ! ltt_dive_in_get_site_alert() ) {
		return;
	}

	$style_path  = LTT_DIVE_IN_DIR . '/assets/css/components/site-alert.css';
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/components/site-alert.js';

	wp_enqueue_style(
		'ltt-dive-in-site-alert',
		LTT_DIVE_IN_URI . '/assets/css/components/site-alert.css',
		array( 'ltt-dive-in-style', 'ltt-dive-in-header' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_script(
		'ltt-dive-in-site-alert',
		LTT_DIVE_IN_URI . '/assets/js/components/site-alert.js',
		array(),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_enqueue_site_alert_assets', 20 );
