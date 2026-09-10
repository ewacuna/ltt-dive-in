<?php
/**
 * Accessible primary-navigation walker.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render menu parents as disclosure buttons and add panel controls.
 */
class LTT_Dive_In_Navigation_Walker extends Walker_Nav_Menu {
	/**
	 * Submenu data waiting to be used by start_lvl().
	 *
	 * @var array
	 */
	private $pending_submenu = array();

	/**
	 * Start a submenu level.
	 *
	 * @param string   $output Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent       = str_repeat( "\t", $depth );
		$submenu_data = isset( $this->pending_submenu[ $depth ] ) ? $this->pending_submenu[ $depth ] : array();
		$submenu_id   = isset( $submenu_data['id'] ) ? $submenu_data['id'] : '';
		$labelledby   = isset( $submenu_data['labelledby'] ) ? $submenu_data['labelledby'] : '';
		$classes      = array( 'sub-menu', 'main-navigation__submenu', 'main-navigation__submenu--level-' . ( $depth + 1 ) );
		$class_names  = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

		$output .= "\n$indent<ul";
		$output .= $submenu_id ? ' id="' . esc_attr( $submenu_id ) . '"' : '';
		$output .= ' class="' . esc_attr( $class_names ) . '"';
		$output .= $labelledby ? ' aria-labelledby="' . esc_attr( $labelledby ) . '"' : '';
		$output .= ' hidden>';

		if ( 0 === $depth ) {
			$output .= '<li class="main-navigation__panel-control main-navigation__panel-control--close">';
			$output .= '<button type="button" class="main-navigation__close" data-menu-close>';
			$output .= '<span class="screen-reader-text">' . esc_html__( 'Close submenu', 'ltt-dive-in' ) . '</span>';
			$output .= '<img src="' . esc_url( LTT_DIVE_IN_URI . '/assets/images/header/close-icon.svg' ) . '" alt="" width="38" height="38">';
			$output .= '</button></li>';
		} elseif ( 1 === $depth ) {
			$output .= '<li class="main-navigation__panel-control main-navigation__panel-control--back">';
			$output .= '<button type="button" class="main-navigation__back" data-menu-back>';
			$output .= '<span aria-hidden="true">&lt;</span> ' . esc_html__( 'Back', 'ltt-dive-in' );
			$output .= '</button></li>';
		}
	}

	/**
	 * Start a menu item.
	 *
	 * @param string   $output Used to append additional content.
	 * @param WP_Post  $menu_item Menu item data object.
	 * @param int      $depth Depth of menu item.
	 * @param stdClass $args Menu arguments.
	 * @param int      $current_object_id Current object ID.
	 */
	public function start_el( &$output, $menu_item, $depth = 0, $args = null, $current_object_id = 0 ) {
		$indent       = $depth ? str_repeat( "\t", $depth ) : '';
		$classes      = empty( $menu_item->classes ) ? array() : (array) $menu_item->classes;
		$classes[]    = 'menu-item-' . $menu_item->ID;
		$has_children = ! empty( $this->has_children );

		$args = apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) );
		$item_id     = apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );

		$output .= $indent . '<li' . ( $item_id ? ' id="' . esc_attr( $item_id ) . '"' : '' ) . ( $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '' ) . '>';

		$title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );
		$item  = isset( $args->before ) ? $args->before : '';

		if ( $has_children && $depth < 2 ) {
			$toggle_id  = 'primary-menu-toggle-' . $menu_item->ID;
			$submenu_id = 'primary-submenu-' . $menu_item->ID;

			$this->pending_submenu[ $depth ] = array(
				'id'         => $submenu_id,
				'labelledby' => $toggle_id,
			);

			$item .= '<button id="' . esc_attr( $toggle_id ) . '" class="main-navigation__submenu-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr( $submenu_id ) . '" data-menu-toggle>';
			$item .= '<span class="main-navigation__label">' . ( isset( $args->link_before ) ? $args->link_before : '' ) . esc_html( $title ) . ( isset( $args->link_after ) ? $args->link_after : '' ) . '</span>';
			$item .= '<span class="main-navigation__indicator" aria-hidden="true"></span>';
			$item .= '</button>';
		} else {
			$atts = array(
				'target'       => ! empty( $menu_item->target ) ? $menu_item->target : '',
				'rel'          => ! empty( $menu_item->xfn ) ? $menu_item->xfn : '',
				'href'         => ! empty( $menu_item->url ) ? $menu_item->url : '',
				'aria-current' => $menu_item->current ? 'page' : '',
			);

			if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
				$atts['rel'] = 'noopener';
			}

			$atts       = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );
			$attributes = '';

			foreach ( $atts as $attribute => $value ) {
				if ( false !== $value && '' !== $value ) {
					$value       = 'href' === $attribute ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attribute . '="' . $value . '"';
				}
			}

			$item .= '<a' . $attributes . '>';
			$item .= ( isset( $args->link_before ) ? $args->link_before : '' ) . esc_html( $title ) . ( isset( $args->link_after ) ? $args->link_after : '' );
			$item .= '</a>';
		}

		$item   .= isset( $args->after ) ? $args->after : '';
		$output .= apply_filters( 'walker_nav_menu_start_el', $item, $menu_item, $depth, $args );
	}
}
