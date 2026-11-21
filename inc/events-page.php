<?php
/**
 * Events page template: filters, queries, and endpoint.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request renders the Events page template.
 *
 * @return bool
 */
function ltt_dive_in_is_events_page() {
	return is_page_template( 'templates/events.php' );
}

/**
 * Return the number of events loaded first and on each Load More.
 *
 * @return array{initial: int, load_more: int}
 */
function ltt_dive_in_get_events_page_limits() {
	return array(
		'initial'   => 9,
		'load_more' => 9,
	);
}

/**
 * Return the "Show Events For" periods.
 *
 * Keys are date filter keys from ltt_dive_in_get_event_date_filters().
 *
 * @return array<string, string>
 */
function ltt_dive_in_get_events_page_periods() {
	return array(
		'today'   => __( 'Day', 'ltt-dive-in' ),
		'week'    => __( 'Week', 'ltt-dive-in' ),
		'weekend' => __( 'Weekend', 'ltt-dive-in' ),
		'month'   => __( 'Month', 'ltt-dive-in' ),
	);
}

/**
 * Return the sort options.
 *
 * @return array<string, string>
 */
function ltt_dive_in_get_events_page_sorts() {
	return array(
		'featured' => __( 'Featured', 'ltt-dive-in' ),
		'soonest'  => __( 'Date', 'ltt-dive-in' ),
		'recent'   => __( 'Newest', 'ltt-dive-in' ),
	);
}

/**
 * Sanitize visitor filter input.
 *
 * Accepts the `event_*` query arguments used by the filter form, the REST
 * endpoint, and shareable URLs.
 *
 * @param array $params Raw values.
 * @return array{search: string, categories: int[], region: int, period: string, from: string, to: string, sort: string, view: string, page: int}
 */
function ltt_dive_in_get_events_page_filters( $params ) {
	$param  = static function ( $name ) use ( $params ) {
		return $params[ $name ] ?? null;
	};
	$search = is_scalar( $param( 'event_search' ) ) ? trim( sanitize_text_field( wp_unslash( (string) $param( 'event_search' ) ) ) ) : '';
	$period = is_scalar( $param( 'event_period' ) ) ? sanitize_key( (string) $param( 'event_period' ) ) : '';
	$sort   = is_scalar( $param( 'event_sort' ) ) ? sanitize_key( (string) $param( 'event_sort' ) ) : '';
	$region = is_scalar( $param( 'event_region' ) ) ? absint( $param( 'event_region' ) ) : 0;
	$from   = ltt_dive_in_sanitize_event_date( is_scalar( $param( 'event_from' ) ) ? (string) $param( 'event_from' ) : '' );
	$to     = ltt_dive_in_sanitize_event_date( is_scalar( $param( 'event_to' ) ) ? (string) $param( 'event_to' ) : '' );
	$today  = wp_date( 'Y-m-d' );

	$categories = array_slice( array_values( array_unique( array_filter( array_map( 'absint', (array) $param( 'event_category' ) ) ) ) ), 0, 20 );
	$categories = array_values( array_intersect( $categories, array_keys( ltt_dive_in_get_seeker_categories() ) ) );

	if ( $region && ! array_key_exists( $region, ltt_dive_in_get_seeker_regions() ) ) {
		$region = 0;
	}

	if ( ! $from && $to ) {
		$from = $today;
	}

	if ( $from ) {
		$from   = max( $from, $today );
		$latest = wp_date( 'Y-m-d', strtotime( $from . ' +1 year' ) );
		$to     = $to && $to >= $from ? min( $to, $latest ) : '';
		$period = '';
	}

	return array(
		'search'     => function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 80 ) : substr( $search, 0, 80 ),
		'categories' => $categories,
		'region'     => $region,
		'period'     => array_key_exists( $period, ltt_dive_in_get_events_page_periods() ) ? $period : '',
		'from'       => $from,
		'to'         => $to,
		'sort'       => array_key_exists( $sort, ltt_dive_in_get_events_page_sorts() ) ? $sort : 'featured',
		'view'       => is_scalar( $param( 'event_view' ) ) && 'list' === $param( 'event_view' ) ? 'list' : 'grid',
		'page'       => max( 1, min( 20, is_scalar( $param( 'event_page' ) ) ? absint( $param( 'event_page' ) ) : 1 ) ),
	);
}

/**
 * Whether any filter narrows the results.
 *
 * @param array $filters Sanitized filters.
 * @return bool
 */
function ltt_dive_in_events_page_has_filters( $filters ) {
	return '' !== $filters['search'] || $filters['categories'] || $filters['region'] || $filters['period'] || $filters['from'];
}

/**
 * Convert filters to ltt_dive_in_get_seeker_events() arguments.
 *
 * @param array $filters Sanitized filters.
 * @return array
 */
function ltt_dive_in_get_events_page_seeker_args( $filters ) {
	return array(
		'search'       => $filters['search'],
		'category_ids' => $filters['categories'],
		'region_ids'   => $filters['region'] ? array( $filters['region'] ) : array(),
		'date'         => $filters['period'] ? $filters['period'] : 'upcoming',
		'date_from'    => $filters['from'],
		'date_to'      => $filters['to'],
		'sort'         => 'recent' === $filters['sort'] ? 'recent' : 'soonest',
	);
}

/**
 * Query one page of events.
 *
 * Seeker cannot sort by its featured flag, so the Featured sort reads the
 * featured events first and continues with the others, both soonest first.
 * Offsets count feed records across both lists.
 *
 * @param array $filters Sanitized filters.
 * @param int   $offset  Zero-based offset.
 * @param int   $limit   Number of feed records.
 * @return array{events: array[], fetched: int, total: int, has_more: bool}|WP_Error
 */
function ltt_dive_in_query_events_page( $filters, $offset, $limit ) {
	$args   = ltt_dive_in_get_events_page_seeker_args( $filters );
	$limit  = max( 1, $limit );
	$events = array();

	if ( 'featured' !== $filters['sort'] ) {
		$result = ltt_dive_in_get_seeker_events( array_merge( $args, array( 'limit' => $limit, 'offset' => $offset ) ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$result['has_more'] = $result['fetched'] > 0 && $result['total'] > $offset + $result['fetched'];

		return $result;
	}

	$featured_total = ltt_dive_in_count_seeker_events( array_merge( $args, array( 'featured' => true ) ) );

	if ( is_wp_error( $featured_total ) ) {
		return $featured_total;
	}

	$fetched = 0;

	if ( $offset < $featured_total ) {
		$featured = ltt_dive_in_get_seeker_events(
			array_merge(
				$args,
				array(
					'featured' => true,
					'limit'    => min( $limit, $featured_total - $offset ),
					'offset'   => $offset,
				)
			)
		);

		if ( is_wp_error( $featured ) ) {
			return $featured;
		}

		$events  = $featured['events'];
		$fetched = $featured['fetched'];
	}

	$remaining = $limit - $fetched;
	$others    = ltt_dive_in_get_seeker_events(
		array_merge(
			$args,
			array(
				'exclude_featured' => true,
				'limit'            => max( 1, $remaining ),
				'offset'           => max( 0, $offset - $featured_total ),
			)
		)
	);

	if ( is_wp_error( $others ) ) {
		return $others;
	}

	if ( $remaining > 0 ) {
		$events   = array_merge( $events, $others['events'] );
		$fetched += $others['fetched'];
	}

	$total = $featured_total + $others['total'];

	return array(
		'events'   => $events,
		'fetched'  => $fetched,
		'total'    => $total,
		'has_more' => $fetched > 0 && $total > $offset + $fetched,
	);
}

/**
 * Return the categories offered as filters, with their upcoming event counts.
 *
 * @return array<int, array{name: string, count: int|null}>
 */
function ltt_dive_in_get_events_page_categories() {
	return ltt_dive_in_get_seeker_categories_with_counts();
}

/**
 * Return the visible date range for the active date filter.
 *
 * @param array $filters Sanitized filters.
 * @return string Empty when every upcoming date is included.
 */
function ltt_dive_in_get_events_page_range_label( $filters ) {
	if ( $filters['from'] ) {
		$range = array(
			'from' => $filters['from'],
			'to'   => $filters['to'],
		);
	} elseif ( $filters['period'] ) {
		$range = ltt_dive_in_get_event_date_range( $filters['period'] );
	} else {
		return '';
	}

	$format = static function ( $date ) {
		return wp_date( _x( 'M jS', 'event filter date', 'ltt-dive-in' ), strtotime( $date . ' 12:00:00' ), wp_timezone() );
	};

	if ( ! $range['to'] ) {
		/* translators: %s: first date. */
		return sprintf( __( 'From %s', 'ltt-dive-in' ), $format( $range['from'] ) );
	}

	if ( $range['from'] === $range['to'] ) {
		return $format( $range['from'] );
	}

	/* translators: 1: first date, 2: last date. */
	return sprintf( __( '%1$s - %2$s', 'ltt-dive-in' ), $format( $range['from'] ), $format( $range['to'] ) );
}

/**
 * Return the query arguments that reproduce the filters in a URL.
 *
 * @param array $filters   Sanitized filters.
 * @param array $overrides Values to replace, such as `event_view`.
 * @return array
 */
function ltt_dive_in_get_events_page_query_args( $filters, $overrides = array() ) {
	$args = array(
		'event_search'   => $filters['search'],
		'event_category' => $filters['categories'],
		'event_region'   => $filters['region'] ? $filters['region'] : '',
		'event_period'   => $filters['period'],
		'event_from'     => $filters['from'],
		'event_to'       => $filters['to'],
		'event_sort'     => 'featured' === $filters['sort'] ? '' : $filters['sort'],
		'event_view'     => 'grid' === $filters['view'] ? '' : $filters['view'],
	);

	return array_filter(
		array_merge( $args, $overrides ),
		static function ( $value ) {
			return '' !== $value && array() !== $value && null !== $value;
		}
	);
}

/**
 * Render event cards for the results.
 *
 * @param array[] $events Normalized events.
 * @param string  $view   `grid` or `list`.
 * @return string
 */
function ltt_dive_in_render_events_page_cards( $events, $view ) {
	ob_start();

	foreach ( $events as $event ) {
		get_template_part(
			'template-parts/components/event-card',
			null,
			array(
				'event'          => $event,
				'style'          => 'list' === $view ? 'row' : 'tile',
				'compact_mobile' => true,
				'flags'          => true,
				'reminder_label' => __( 'Add to Calendar', 'ltt-dive-in' ),
				'reminder_icon'  => 'add',
				'quick_view'     => true,
				'sizes'          => 'list' === $view ? '(max-width: 767.98px) 120px, 307px' : '(max-width: 767.98px) 120px, (max-width: 1199.98px) 50vw, 458px',
				'wrapper'        => 'events-browser__item',
			)
		);
	}

	return (string) ob_get_clean();
}

/**
 * Return a page of events for the client-side filters and Load More.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function ltt_dive_in_get_events_page_rest_events( WP_REST_Request $request ) {
	$filters = ltt_dive_in_get_events_page_filters( $request->get_query_params() );
	$limits  = ltt_dive_in_get_events_page_limits();
	$offset  = min( 200, absint( $request->get_param( 'offset' ) ) );
	$limit   = min( 100, absint( $request->get_param( 'limit' ) ) );
	$limit   = $limit ? $limit : ( $offset ? $limits['load_more'] : $limits['initial'] );
	$result  = ltt_dive_in_query_events_page( $filters, $offset, $limit );

	if ( is_wp_error( $result ) ) {
		return new WP_Error( 'ltt_dive_in_events_unavailable', __( 'Events are temporarily unavailable. Please try again later.', 'ltt-dive-in' ), array( 'status' => 503 ) );
	}

	$response = rest_ensure_response(
		array(
			'html'       => ltt_dive_in_render_events_page_cards( $result['events'], $filters['view'] ),
			'count'      => count( $result['events'] ),
			'total'      => $result['total'],
			'nextOffset' => $offset + $result['fetched'],
			'hasMore'    => $result['has_more'],
			'rangeLabel' => ltt_dive_in_get_events_page_range_label( $filters ),
		)
	);
	$response->header( 'Cache-Control', 'public, max-age=300' );

	return $response;
}

/**
 * Register the Events page REST route.
 */
function ltt_dive_in_register_events_page_routes() {
	register_rest_route(
		'ltt-dive-in/v1',
		'/events',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ltt_dive_in_get_events_page_rest_events',
			'permission_callback' => '__return_true',
			'args'                => array(
				'offset' => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'limit'  => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'ltt_dive_in_register_events_page_routes' );
