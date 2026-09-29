<?php
/**
 * Optional, page-specific navigation using native WordPress menus.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the code-driven selector; its choices depend on existing menus.
 */
function ltt_dive_in_register_section_navigation() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_section_navigation',
			'title'    => __( 'LTT Dive In — Page Header', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'           => 'field_ltt_dive_in_page_header_background',
					'name'          => 'ltt_dive_in_page_header_background',
					'label'         => __( 'Header background', 'ltt-dive-in' ),
					'type'          => 'select',
					'instructions'  => __( 'Choose the background for this page’s main header row. This also works without a section menu. The homepage header is managed separately.', 'ltt-dive-in' ),
					'choices'       => array(
						'solid'    => __( 'Solid navy', 'ltt-dive-in' ),
						'gradient' => __( 'Navy gradient', 'ltt-dive-in' ),
					),
					'default_value' => 'solid',
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_ltt_dive_in_section_navigation_menu',
					'name'          => 'ltt_dive_in_section_navigation_menu',
					'label'         => __( 'Section menu', 'ltt-dive-in' ),
					'type'          => 'select',
					'instructions'  => __( 'Optional. Create and edit a menu in Appearance → Menus, then select it here. Only top-level links appear below the main header. Use a descriptive menu name; it also labels this navigation for screen readers. Leave empty to hide the bar. Reload this editor after creating a menu.', 'ltt-dive-in' ),
					'choices'       => array(),
					'allow_null'    => 1,
					'placeholder'   => __( 'None', 'ltt-dive-in' ),
					'default_value' => '',
					'return_format' => 'value',
					'ui'            => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
					array(
						'param'    => 'page_type',
						'operator' => '!=',
						'value'    => 'front_page',
					),
					array(
						'param'    => 'page_type',
						'operator' => '!=',
						'value'    => 'posts_page',
					),
					array(
						'param'    => 'page_template',
						'operator' => '!=',
						'value'    => 'templates/blank-canvas.php',
					),
				),
			),
			'position' => 'side',
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_section_navigation' );

/**
 * Resolve the approved background variant for an internal page header.
 *
 * @return string Whitelisted CSS modifier suffix; existing pages default to solid.
 */
function ltt_dive_in_get_page_header_background() {
	if ( ! is_page() || is_front_page() || is_page_template( 'templates/blank-canvas.php' ) || ! function_exists( 'get_field' ) ) {
		return 'solid';
	}

	return 'gradient' === get_field( 'ltt_dive_in_page_header_background', get_queried_object_id() ) ? 'gradient' : 'solid';
}

/**
 * Populate the selector without copying menu content into ACF.
 *
 * @param array $field ACF field definition.
 * @return array
 */
function ltt_dive_in_section_navigation_choices( $field ) {
	$field['choices'] = array();
	foreach ( wp_get_nav_menus() as $menu ) {
		$field['choices'][ $menu->term_id ] = $menu->name;
	}
	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_section_navigation_menu', 'ltt_dive_in_section_navigation_choices' );

/**
 * Resolve and render once so asset loading and the header share an empty state.
 *
 * @return array Navigation label and core-generated HTML, or an empty array.
 */
function ltt_dive_in_get_section_navigation() {
	static $navigation = null;

	if ( null !== $navigation ) {
		return $navigation;
	}

	$navigation = array();
	if ( ! is_page() || is_front_page() || is_page_template( 'templates/blank-canvas.php' ) || ! function_exists( 'get_field' ) ) {
		return $navigation;
	}

	$menu_id = get_field( 'ltt_dive_in_section_navigation_menu', get_queried_object_id() );
	if ( ! is_scalar( $menu_id ) || ! ctype_digit( (string) $menu_id ) || 0 === (int) $menu_id ) {
		return $navigation;
	}

	$menu = wp_get_nav_menu_object( (int) $menu_id );
	if ( ! $menu || is_wp_error( $menu ) || ! wp_get_nav_menu_items( $menu->term_id ) ) {
		return $navigation;
	}

	$html = wp_nav_menu(
		array(
			'menu'        => $menu->term_id,
			'menu_id'     => 'ltt-section-navigation-menu',
			'menu_class'  => 'ltt-section-navigation__menu',
			'container'   => false,
			'fallback_cb' => false,
			'depth'       => 1,
			'echo'        => false,
		)
	);

	if ( is_string( $html ) && '' !== trim( $html ) ) {
		$navigation = array(
			'label' => $menu->name,
			'html'  => $html,
		);
	}

	return $navigation;
}

/**
 * Avoid duplicate item IDs when the selected menu is also used elsewhere.
 *
 * @param string   $id    Menu item element ID.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  Menu rendering arguments.
 * @return string
 */
function ltt_dive_in_section_navigation_item_id( $id, $item, $args ) {
	return isset( $args->menu_id ) && 'ltt-section-navigation-menu' === $args->menu_id ? 'ltt-section-menu-item-' . $item->ID : $id;
}
add_filter( 'nav_menu_item_id', 'ltt_dive_in_section_navigation_item_id', 10, 3 );
