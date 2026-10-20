<?php
/**
 * FacetWP query, search, pagination, and accessible output for Listing Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find the active Listing Module query configuration during a FacetWP request.
 *
 * @param array $query_args Optional query arguments already in scope.
 * @return array|false
 */
function ltt_dive_in_listing_module_facetwp_context( $query_args = array() ) {
	$candidates = array( $query_args );

	if ( function_exists( 'FWP' ) ) {
		if ( isset( FWP()->facet->query_args ) && is_array( FWP()->facet->query_args ) ) {
			$candidates[] = FWP()->facet->query_args;
		}

		if ( isset( FWP()->request->query_vars ) && is_array( FWP()->request->query_vars ) ) {
			$candidates[] = FWP()->request->query_vars;
		}
	}

	foreach ( $candidates as $candidate ) {
		if ( ! empty( $candidate['ltt_dive_in_listing_module_query'] ) ) {
			return $candidate;
		}
	}

	return false;
}

/**
 * Apply the initial count and subsequent Load More step to the FacetWP query.
 *
 * @param array                  $query_args WordPress query arguments.
 * @param FacetWP_Renderer|mixed $renderer   Active FacetWP renderer.
 * @return array
 */
function ltt_dive_in_listing_module_filtered_query_args( $query_args, $renderer ) {
	$context = ltt_dive_in_listing_module_facetwp_context( $query_args );
	if ( ! $context ) {
		return $query_args;
	}

	$initial_count = isset( $context['ltt_dive_in_listing_module_initial_count'] ) ? (int) $context['ltt_dive_in_listing_module_initial_count'] : 3;
	$initial_count = min( 12, max( 1, $initial_count ) );
	$step          = isset( $context['ltt_dive_in_listing_module_step'] ) ? (int) $context['ltt_dive_in_listing_module_step'] : $initial_count;
	$step          = min( 12, max( 1, $step ) );
	$page          = isset( $query_args['paged'] ) ? max( 1, (int) $query_args['paged'] ) : 1;

	$query_args['post_status']   = 'publish';
	$query_args['has_password']  = false;
	$query_args['posts_per_page'] = ( 1 === $page ) ? $initial_count : $step;

	if ( 1 === $page ) {
		unset( $query_args['offset'] );
	} else {
		$query_args['offset'] = $initial_count + ( ( $page - 2 ) * $step );
	}

	return $query_args;
}
add_filter( 'facetwp_filtered_query_args', 'ltt_dive_in_listing_module_filtered_query_args', 20, 2 );

/**
 * Calculate page count when the initial batch and Load More step differ.
 *
 * @param array                  $pager_args FacetWP pager values.
 * @param FacetWP_Renderer|mixed $renderer   Active FacetWP renderer.
 * @return array
 */
function ltt_dive_in_listing_module_pager_args( $pager_args, $renderer ) {
	$query_args = isset( $renderer->query_args ) && is_array( $renderer->query_args ) ? $renderer->query_args : array();
	$context    = ltt_dive_in_listing_module_facetwp_context( $query_args );
	if ( ! $context ) {
		return $pager_args;
	}

	$initial_count = isset( $context['ltt_dive_in_listing_module_initial_count'] ) ? (int) $context['ltt_dive_in_listing_module_initial_count'] : 3;
	$initial_count = min( 12, max( 1, $initial_count ) );
	$step          = isset( $context['ltt_dive_in_listing_module_step'] ) ? (int) $context['ltt_dive_in_listing_module_step'] : $initial_count;
	$step          = min( 12, max( 1, $step ) );
	$page          = isset( $pager_args['page'] ) ? max( 1, (int) $pager_args['page'] ) : 1;
	$total_rows    = isset( $pager_args['total_rows'] ) ? max( 0, (int) $pager_args['total_rows'] ) : 0;

	$pager_args['per_page']    = ( 1 === $page ) ? $initial_count : $step;
	$pager_args['total_pages'] = $total_rows <= $initial_count ? 1 : 1 + (int) ceil( ( $total_rows - $initial_count ) / $step );

	return $pager_args;
}
add_filter( 'facetwp_pager_args', 'ltt_dive_in_listing_module_pager_args', 20, 2 );

/**
 * Remove FacetWP Search's default 200-result cap for this Listing Module only.
 *
 * @param array $search_args Search query arguments.
 * @param array $params      Search facet parameters.
 * @return array
 */
function ltt_dive_in_listing_module_search_query_args( $search_args, $params ) {
	$context = ltt_dive_in_listing_module_facetwp_context();
	if ( ! $context || empty( $params['facet']['name'] ) ) {
		return $search_args;
	}

	$search_name = sanitize_key( (string) ( $context['ltt_dive_in_listing_module_search_facet'] ?? '' ) );
	if ( '' === $search_name || $search_name !== sanitize_key( (string) $params['facet']['name'] ) ) {
		return $search_args;
	}

	$post_type = sanitize_key( (string) ( $context['ltt_dive_in_listing_module_post_type'] ?? $context['post_type'] ?? '' ) );
	$post_type_object = $post_type ? get_post_type_object( $post_type ) : null;
	if ( ! $post_type_object || ! $post_type_object->public || in_array( $post_type, array( 'attachment', 'page' ), true ) ) {
		return $search_args;
	}

	$search_args['post_type']                            = $post_type;
	$search_args['post_status']                          = 'publish';
	$search_args['has_password']                         = false;
	$search_args['posts_per_page']                       = -1;
	$search_args['no_found_rows']                        = true;
	$search_args['fields']                               = 'ids';
	$search_args['order']                                = 'DESC';
	$search_args['ltt_dive_in_listing_module_search'] = true;

	return $search_args;
}
add_filter( 'facetwp_search_query_args', 'ltt_dive_in_listing_module_search_query_args', 20, 2 );

/**
 * Give tied WordPress search matches a deterministic order.
 *
 * @param string   $orderby SQL ORDER BY clause.
 * @param WP_Query $query   Current query.
 * @return string
 */
function ltt_dive_in_listing_module_search_orderby( $orderby, $query ) {
	if ( ! $query->get( 'ltt_dive_in_listing_module_search' ) ) {
		return $orderby;
	}

	global $wpdb;

	$orderby = trim( $orderby );
	return '' !== $orderby ? $orderby . ', ' . $wpdb->posts . '.ID DESC' : $wpdb->posts . '.ID DESC';
}
add_filter( 'posts_orderby', 'ltt_dive_in_listing_module_search_orderby', 20, 2 );

/**
 * Add an associated label and semantic controls to native FacetWP output.
 *
 * @param string $html Facet HTML.
 * @param array  $args Render arguments.
 * @return string
 */
function ltt_dive_in_listing_module_facet_html( $html, $args ) {
	if ( empty( $args['facet']['name'] ) ) {
		return $html;
	}

	$context = ltt_dive_in_listing_module_facetwp_context();
	if ( ! $context ) {
		return $html;
	}

	$facet_name = sanitize_key( (string) $args['facet']['name'] );
	$block_id   = isset( $context['ltt_dive_in_listing_module_id'] ) ? sanitize_html_class( $context['ltt_dive_in_listing_module_id'] ) : 'listing-module';
	$cards_id   = $block_id . '-cards';

	if ( 'search' === ( $args['facet']['type'] ?? '' ) && $facet_name === sanitize_key( (string) ( $context['ltt_dive_in_listing_module_search_facet'] ?? '' ) ) ) {
		$input_id = $block_id . '-search-input';
		$html     = str_replace(
			'<input type="text" class="facetwp-search"',
			sprintf( '<input id="%1$s" type="text" class="facetwp-search" aria-controls="%2$s"', esc_attr( $input_id ), esc_attr( $cards_id ) ),
			$html
		);

		$button = sprintf(
			'<button type="button" class="facetwp-icon" aria-label="%1$s" aria-controls="%2$s"><img src="%3$s" alt="" /></button>',
			esc_attr__( 'Search listings', 'ltt-dive-in' ),
			esc_attr( $cards_id ),
			esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) )
		);
		$html = str_replace( '<i class="facetwp-icon"></i>', $button, $html );
	} elseif ( 'pager' === ( $args['facet']['type'] ?? '' ) && $facet_name === sanitize_key( (string) ( $context['ltt_dive_in_listing_module_pager_facet'] ?? '' ) ) ) {
		$html = preg_replace_callback(
			'/<button class="facetwp-load-more" data-loading="([^"]*)">(.*?)<\/button>/s',
			static function ( $matches ) use ( $cards_id ) {
				return sprintf(
					'<button type="button" class="facetwp-load-more" data-loading="%1$s" aria-controls="%2$s">%3$s</button>',
					$matches[1],
					esc_attr( $cards_id ),
					$matches[2]
				);
			},
			$html
		);
	}

	return $html;
}
add_filter( 'facetwp_facet_html', 'ltt_dive_in_listing_module_facet_html', 20, 2 );
