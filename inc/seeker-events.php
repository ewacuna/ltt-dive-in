<?php
/**
 * Seeker Events API client.
 *
 * Every request is made by the server with the site's Seeker API key and is
 * cached, so the browser never receives the key or calls Seeker directly.
 * Responses are normalized into a small event shape that templates consume
 * without knowing the upstream field names.
 *
 * @link https://seeker.io/apidocs/events/
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the Seeker API key.
 *
 * A `LTT_DIVE_IN_SEEKER_API_KEY` constant in wp-config.php takes precedence
 * over the key saved in Settings → Events.
 *
 * @return string
 */
function ltt_dive_in_get_seeker_api_key() {
	if ( defined( 'LTT_DIVE_IN_SEEKER_API_KEY' ) && is_string( LTT_DIVE_IN_SEEKER_API_KEY ) ) {
		$key = LTT_DIVE_IN_SEEKER_API_KEY;
	} else {
		$key = get_option( 'options_ltt_dive_in_seeker_api_key', '' );
	}

	/**
	 * Filter the Seeker API key used for server-side requests.
	 *
	 * @param string $key Seeker API key.
	 */
	$key = apply_filters( 'ltt_dive_in_seeker_api_key', is_string( $key ) ? trim( $key ) : '' );

	return is_string( $key ) ? trim( $key ) : '';
}

/**
 * Whether the Seeker API key is supplied by wp-config.php.
 *
 * @return bool
 */
function ltt_dive_in_seeker_api_key_is_constant() {
	return defined( 'LTT_DIVE_IN_SEEKER_API_KEY' ) && is_string( LTT_DIVE_IN_SEEKER_API_KEY ) && '' !== trim( LTT_DIVE_IN_SEEKER_API_KEY );
}

/**
 * Return the current cache generation.
 *
 * Cache keys include this value, so changing the API key invalidates every
 * cached Seeker response without enumerating transients.
 *
 * @return int
 */
function ltt_dive_in_get_seeker_cache_generation() {
	return absint( get_option( 'ltt_dive_in_seeker_cache_generation', 1 ) );
}

/**
 * Invalidate every cached Seeker response.
 *
 * @return void
 */
function ltt_dive_in_flush_seeker_cache() {
	update_option( 'ltt_dive_in_seeker_cache_generation', ltt_dive_in_get_seeker_cache_generation() + 1, false );
	delete_transient( 'ltt_dive_in_seeker_backoff' );
}

/**
 * Record the outcome of the latest uncached Seeker request for the settings screen.
 *
 * @param string $status  `ok` or an error code.
 * @param string $message Optional human-readable detail.
 * @return void
 */
function ltt_dive_in_record_seeker_status( $status, $message = '' ) {
	update_option(
		'ltt_dive_in_seeker_last_status',
		array(
			'status'  => sanitize_key( $status ),
			'message' => sanitize_text_field( $message ),
			'time'    => time(),
		),
		false
	);
}

/**
 * Perform a cached GET request against the Seeker public API.
 *
 * Successful responses are cached for `$ttl` seconds and retained for a day as
 * a stale fallback. A 429 response pauses all requests for the `Retry-After`
 * period; other failures are briefly cached so an outage does not add a slow
 * request to every page view.
 *
 * @param string $path  API path, such as `/events/feeds`.
 * @param array  $query Query arguments.
 * @param int    $ttl   Fresh cache lifetime in seconds.
 * @return array|WP_Error Decoded JSON body.
 */
function ltt_dive_in_seeker_request( $path, $query = array(), $ttl = 900 ) {
	$api_key = ltt_dive_in_get_seeker_api_key();

	if ( '' === $api_key ) {
		return new WP_Error( 'ltt_dive_in_seeker_missing_api_key', __( 'The Seeker API key has not been configured.', 'ltt-dive-in' ) );
	}

	ksort( $query );

	$cache_key = 'ltt_dive_in_seeker_' . md5( ltt_dive_in_get_seeker_cache_generation() . '|' . $path . '|' . wp_json_encode( $query ) );
	$cached    = get_transient( $cache_key );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$stale = get_transient( $cache_key . '_stale' );

	if ( 'unavailable' === $cached || get_transient( 'ltt_dive_in_seeker_backoff' ) ) {
		return is_array( $stale ) ? $stale : new WP_Error( 'ltt_dive_in_seeker_unavailable', __( 'Events are temporarily unavailable.', 'ltt-dive-in' ) );
	}

	$url      = add_query_arg( array_map( 'rawurlencode', $query ), 'https://public.api.seeker.io' . $path );
	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 8,
			'headers' => array(
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		set_transient( $cache_key, 'unavailable', 5 * MINUTE_IN_SECONDS );
		ltt_dive_in_record_seeker_status( 'request_failed', $response->get_error_message() );

		return is_array( $stale ) ? $stale : $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );

	if ( 429 === $code ) {
		$retry_after = absint( wp_remote_retrieve_header( $response, 'retry-after' ) );

		set_transient( 'ltt_dive_in_seeker_backoff', 1, max( MINUTE_IN_SECONDS, min( HOUR_IN_SECONDS, $retry_after ) ) );
		ltt_dive_in_record_seeker_status( 'rate_limited' );

		return is_array( $stale ) ? $stale : new WP_Error( 'ltt_dive_in_seeker_rate_limited', __( 'Events are temporarily unavailable.', 'ltt-dive-in' ) );
	}

	if ( 200 !== $code ) {
		// An invalid or unauthorized key will not recover on its own; wait longer before retrying.
		set_transient( $cache_key, 'unavailable', in_array( $code, array( 401, 403 ), true ) ? 15 * MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS );
		ltt_dive_in_record_seeker_status( in_array( $code, array( 401, 403 ), true ) ? 'unauthorized' : 'http_' . $code );

		return is_array( $stale ) ? $stale : new WP_Error( 'ltt_dive_in_seeker_http_error', __( 'Events are temporarily unavailable.', 'ltt-dive-in' ), array( 'status' => $code ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $body ) ) {
		set_transient( $cache_key, 'unavailable', 5 * MINUTE_IN_SECONDS );
		ltt_dive_in_record_seeker_status( 'invalid_response' );

		return is_array( $stale ) ? $stale : new WP_Error( 'ltt_dive_in_seeker_invalid_response', __( 'Events are temporarily unavailable.', 'ltt-dive-in' ) );
	}

	set_transient( $cache_key, $body, max( MINUTE_IN_SECONDS, (int) $ttl ) );
	set_transient( $cache_key . '_stale', $body, DAY_IN_SECONDS );
	ltt_dive_in_record_seeker_status( 'ok' );

	return $body;
}

/**
 * Return the list of records from a Seeker collection response.
 *
 * @param array $body Decoded response.
 * @return array[]
 */
function ltt_dive_in_get_seeker_items( $body ) {
	foreach ( array( 'items', 'data', 'events', 'results' ) as $key ) {
		if ( isset( $body[ $key ] ) && is_array( $body[ $key ] ) ) {
			return array_values( array_filter( $body[ $key ], 'is_array' ) );
		}
	}

	return wp_is_numeric_array( $body ) ? array_values( array_filter( $body, 'is_array' ) ) : array();
}

/**
 * Return the date range for an approved date filter.
 *
 * @param string $preset Date filter key.
 * @return array{from:string, to:string}
 */
function ltt_dive_in_get_event_date_range( $preset ) {
	$today = current_datetime()->setTime( 0, 0 );
	$from  = $today;
	$to    = null;

	switch ( $preset ) {
		case 'today':
			$to = $today;
			break;
		case 'weekend':
			$weekday = (int) $today->format( 'N' );
			$from    = $weekday >= 5 ? $today : $today->modify( 'next friday' );
			$to      = 7 === $weekday ? $today : $from->modify( 'sunday this week' );
			break;
		case 'week':
			$to = $today->modify( '+6 days' );
			break;
		case 'month':
			$to = $today->modify( 'last day of this month' );
			break;
		case 'next_month':
			$from = $today->modify( 'first day of next month' );
			$to   = $today->modify( 'last day of next month' );
			break;
	}

	return array(
		'from' => $from->format( 'Y-m-d' ),
		'to'   => $to ? $to->format( 'Y-m-d' ) : '',
	);
}

/**
 * Return the date filters offered to visitors.
 *
 * @return array<string, string>
 */
function ltt_dive_in_get_event_date_filters() {
	return array(
		'upcoming'   => __( 'Upcoming', 'ltt-dive-in' ),
		'today'      => __( 'Today', 'ltt-dive-in' ),
		'weekend'    => __( 'This weekend', 'ltt-dive-in' ),
		'week'       => __( 'Next 7 days', 'ltt-dive-in' ),
		'month'      => __( 'This month', 'ltt-dive-in' ),
		'next_month' => __( 'Next month', 'ltt-dive-in' ),
	);
}

/**
 * Validate a `YYYY-MM-DD` date string.
 *
 * @param mixed $date Candidate value.
 * @return string The date, or an empty string.
 */
function ltt_dive_in_sanitize_event_date( $date ) {
	$date   = is_string( $date ) ? trim( $date ) : '';
	$parsed = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() ) : false;

	return $parsed && $parsed->format( 'Y-m-d' ) === $date ? $date : '';
}

/**
 * Normalize Seeker feed arguments into a bounded API query.
 *
 * Only these arguments are forwarded, which keeps cache keys bounded and
 * prevents visitor input from reaching other API parameters:
 *
 * - `limit` (0-100; 0 requests only the total), `offset`
 * - `search` free text
 * - `category_ids` int[] (an event matches any of them)
 * - `region_ids` int[] (an event matches when its city is in any of them)
 * - `date` date filter key from ltt_dive_in_get_event_date_filters()
 * - `date_from`, `date_to` `YYYY-MM-DD`; a valid `date_from` replaces `date`
 * - `featured` bool, only featured events
 * - `exclude_featured` bool, only events that are not featured
 * - `sort` `soonest` or `recent`
 *
 * @param array $args Query arguments.
 * @return array{query: array, from: string, to: string}
 */
function ltt_dive_in_get_seeker_event_query( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'limit'            => 9,
			'offset'           => 0,
			'search'           => '',
			'category_ids'     => array(),
			'region_ids'       => array(),
			'date'             => 'upcoming',
			'date_from'        => '',
			'date_to'          => '',
			'featured'         => false,
			'exclude_featured' => false,
			'sort'             => 'soonest',
		)
	);
	$from = ltt_dive_in_sanitize_event_date( $args['date_from'] );

	if ( $from ) {
		$to    = ltt_dive_in_sanitize_event_date( $args['date_to'] );
		$range = array(
			'from' => $from,
			'to'   => $to && $to >= $from ? $to : '',
		);
	} else {
		$range = ltt_dive_in_get_event_date_range( array_key_exists( $args['date'], ltt_dive_in_get_event_date_filters() ) ? $args['date'] : 'upcoming' );
	}

	$query = array(
		'limit'          => max( 0, min( 100, absint( $args['limit'] ) ) ),
		'offset'         => absint( $args['offset'] ),
		'states'         => 'published',
		'eventstatus'    => 'scheduled,rescheduled,postponed',
		'datefilterfrom' => $range['from'],
		'sortby'         => 'recent' === $args['sort'] ? 'datecreated' : 'startdatetime',
		'sortdir'        => 'recent' === $args['sort'] ? 'desc' : 'asc',
	);
	$search = trim( sanitize_text_field( (string) $args['search'] ) );
	$ids    = static function ( $values ) {
		$values = array_values( array_unique( array_filter( array_map( 'absint', (array) $values ) ) ) );
		sort( $values );

		return implode( ',', $values );
	};

	if ( $range['to'] ) {
		$query['datefilterto'] = $range['to'];
	}

	if ( '' !== $search ) {
		$query['q'] = function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 80 ) : substr( $search, 0, 80 );
	}

	if ( $ids( $args['category_ids'] ) ) {
		$query['categoryids'] = $ids( $args['category_ids'] );
	}

	if ( $ids( $args['region_ids'] ) ) {
		$query['regionids'] = $ids( $args['region_ids'] );
	}

	if ( $args['featured'] ) {
		$query['featured'] = 'true';
	} elseif ( $args['exclude_featured'] ) {
		$query['featured'] = 'false';
	}

	return array(
		'query' => $query,
		'from'  => $range['from'],
		'to'    => $range['to'],
	);
}

/**
 * Count published Seeker events without fetching them.
 *
 * Accepts the same arguments as ltt_dive_in_get_seeker_events(). Recurring
 * series are counted once, as Seeker counts them.
 *
 * @param array $args Query arguments.
 * @return int|WP_Error
 */
function ltt_dive_in_count_seeker_events( $args = array() ) {
	$built = ltt_dive_in_get_seeker_event_query( array_merge( $args, array( 'limit' => 0, 'offset' => 0 ) ) );
	$body  = ltt_dive_in_seeker_request( '/events/feeds', $built['query'] );

	if ( is_wp_error( $body ) ) {
		return $body;
	}

	return isset( $body['total'] ) ? absint( $body['total'] ) : 0;
}

/**
 * Query published Seeker events.
 *
 * See ltt_dive_in_get_seeker_event_query() for the accepted arguments; `limit`
 * is at least 1 here.
 *
 * @param array $args Query arguments.
 * @return array{events: array[], fetched: int, total: int}|WP_Error Fetched counts feed records, including any that were not displayable.
 */
function ltt_dive_in_get_seeker_events( $args = array() ) {
	$built = ltt_dive_in_get_seeker_event_query( $args );
	$query = $built['query'];
	$range = array(
		'from' => $built['from'],
		'to'   => $built['to'],
	);

	$query['limit'] = max( 1, $query['limit'] );

	$body = ltt_dive_in_seeker_request( '/events/feeds', $query );

	if ( is_wp_error( $body ) ) {
		return $body;
	}

	$items   = ltt_dive_in_get_seeker_items( $body );
	$events  = array();
	$bounded = ! empty( $args['date_from'] ) && ltt_dive_in_sanitize_event_date( $args['date_from'] );
	$seen    = array();

	foreach ( $items as $item ) {
		$event = ltt_dive_in_normalize_seeker_event( $item, $range['from'], $bounded );

		// A recurring series can span the range without an occurrence inside it.
		if ( $event && ! isset( $seen[ $event['id'] ] ) && ( '' === $range['to'] || $event['start']->format( 'Y-m-d' ) <= $range['to'] ) && ( ! $bounded || ( $event['end'] ?? $event['start'] )->format( 'Y-m-d' ) >= $range['from'] ) ) {
			$events[] = $event;
			$seen[ $event['id'] ] = true;
		}
	}

	return array(
		'events'  => $events,
		'fetched' => count( $items ),
		'total'   => isset( $body['total'] ) ? absint( $body['total'] ) : $query['offset'] + count( $items ),
	);
}

/**
 * Find raw Seeker events by UUID in the upcoming feed or on a specific date.
 *
 * The feed cannot be filtered by event ID (`searchfield=id` returns 422), so
 * the cached upcoming feed is read in pages and matched locally.
 *
 * @param string[] $uuids Seeker event UUIDs.
 * @param string   $event_date Optional occurrence date; searches that day.
 * @return array<string, array> Raw events keyed by UUID.
 */
function ltt_dive_in_find_seeker_raw_events( $uuids, $event_date = '' ) {
	$uuids = array_slice( array_values( array_unique( array_filter( array_map( 'ltt_dive_in_sanitize_seeker_uuid', (array) $uuids ) ) ) ), 0, 12 );
	$found = array();
	$dates = array();
	if ( '' !== $event_date ) {
		if ( ! ltt_dive_in_sanitize_event_date( $event_date ) ) {
			return $found;
		}
		$dates = array( 'date_from' => $event_date, 'date_to' => $event_date );
	}

	for ( $offset = 0; $uuids && $offset < 500 && count( $found ) < count( $uuids ); $offset += 100 ) {
		$built = ltt_dive_in_get_seeker_event_query(
			array_merge(
				$dates,
				array(
					'limit'  => 100,
					'offset' => $offset,
				)
			)
		);
		$body  = ltt_dive_in_seeker_request( '/events/feeds', $built['query'] );

		if ( is_wp_error( $body ) ) {
			break;
		}

		$items = ltt_dive_in_get_seeker_items( $body );

		foreach ( $items as $item ) {
			$uuid = ltt_dive_in_sanitize_seeker_uuid( $item['uuid'] ?? '' );

			if ( $uuid && in_array( $uuid, $uuids, true ) ) {
				$found[ $uuid ] = $item;
			}
		}

		if ( count( $items ) < 100 || ( isset( $body['total'] ) && $offset + 100 >= (int) $body['total'] ) ) {
			break;
		}
	}

	return $found;
}

/**
 * Look up individual events by Seeker UUID, preserving the requested order.
 *
 * @param string[] $uuids Seeker event UUIDs.
 * @return array[] Normalized upcoming events.
 */
function ltt_dive_in_get_seeker_events_by_id( $uuids ) {
	$events = array();

	foreach ( ltt_dive_in_find_seeker_raw_events( $uuids ) as $raw ) {
		$event = ltt_dive_in_normalize_seeker_event( $raw );

		if ( $event ) {
			$events[ $event['id'] ] = $event;
		}
	}

	$ordered = array();

	foreach ( array_map( 'ltt_dive_in_sanitize_seeker_uuid', (array) $uuids ) as $uuid ) {
		if ( isset( $events[ $uuid ] ) ) {
			$ordered[] = $events[ $uuid ];
		}
	}

	return $ordered;
}

/**
 * Return an event with details, optionally at an exact occurrence date.
 *
 * @param string $uuid       Seeker event UUID.
 * @param string $event_date Optional occurrence date in YYYY-MM-DD format.
 * @return array{event: array, details: array}|null
 */
function ltt_dive_in_get_seeker_event_with_details( $uuid, $event_date = '' ) {
	if ( '' !== $event_date && ! ltt_dive_in_sanitize_event_date( $event_date ) ) {
		return null;
	}
	$uuid = ltt_dive_in_sanitize_seeker_uuid( $uuid );
	$raw  = $uuid ? ( ltt_dive_in_find_seeker_raw_events( array( $uuid ), $event_date )[ $uuid ] ?? null ) : null;
	$event = $raw ? ltt_dive_in_normalize_seeker_event( $raw, $event_date ) : null;

	if ( ! $event || ( $event_date && $event['start']->format( 'Y-m-d' ) !== $event_date ) ) {
		return null;
	}
	if ( $event_date && ( ! empty( $raw['recurring'] ) || ! empty( $raw['occurrences'] ) ) ) {
		$dates = ltt_dive_in_get_seeker_occurrences( $raw, $event['start']->getTimezone(), $event_date, 1 );
		if ( ! $dates || $dates[0]['start']->format( 'Y-m-d' ) !== $event_date ) {
			return null;
		}
	}
	if ( $event_date ) {
		$event['date_context'] = $event_date;
	}

	return array(
		'event'   => $event,
		'details' => ltt_dive_in_get_seeker_event_details( $raw, $event ),
	);
}

/**
 * Whether the displayed occurrence has ended in the event's timezone.
 *
 * Events without an end time remain actionable through their final day.
 *
 * @param array $event Normalized event.
 * @return bool
 */
function ltt_dive_in_event_has_ended( $event ) {
	$last = $event['end'] ?? $event['start'];
	if ( ! empty( $event['all_day'] ) || empty( $event['end'] ) || ( isset( $event['has_end_time'] ) && ! $event['has_end_time'] ) ) {
		$last = $last->setTime( 23, 59, 59 );
	}
	return $last < current_datetime();
}

/**
 * Normalize the Seeker fields used only on an event's own page.
 *
 * @param array $raw   Raw Seeker event.
 * @param array $event Normalized event.
 * @return array
 */
function ltt_dive_in_get_seeker_event_details( $raw, $event ) {
	$place     = isset( $raw['place'] ) && is_array( $raw['place'] ) ? $raw['place'] : array();
	$location  = isset( $place['location'] ) && is_array( $place['location'] ) ? $place['location'] : array();
	$organizer = isset( $raw['organizer'] ) && is_array( $raw['organizer'] ) ? $raw['organizer'] : array();
	$url       = static function ( $value ) {
		return is_string( $value ) ? esc_url_raw( $value, array( 'http', 'https' ) ) : '';
	};
	$tickets   = array();

	foreach ( (array) ( $raw['tickets'] ?? array() ) as $ticket ) {
		$ticket_url = is_array( $ticket ) ? $url( $ticket['url'] ?? '' ) : '';

		if ( $ticket_url ) {
			$tickets[] = array(
				'name' => ltt_dive_in_seeker_plain_text( $ticket['name'] ?? '' ),
				'url'  => $ticket_url,
			);
		}
	}

	$images = ltt_dive_in_get_seeker_images( $raw['images'] ?? array() );

	if ( ! $images && $event['image'] ) {
		$images = array( $event['image'] );
	}

	$address  = implode(
		', ',
		array_filter(
			array(
				trim( $event['address']['street'] . ' ' . ltt_dive_in_seeker_plain_text( $location['address2'] ?? '' ) ),
				$event['address']['city'],
				trim( $event['address']['region'] . ' ' . $event['address']['postal'] ),
			)
		)
	);
	$timezone = $event['start']->getTimezone();

	return array(
		'images'           => array_slice( $images, 0, 10 ),
		'description_html' => wp_kses(
			is_string( $raw['description'] ?? null ) ? $raw['description'] : '',
			array(
				'p'      => array(),
				'br'     => array(),
				'strong' => array(),
				'b'      => array(),
				'em'     => array(),
				'i'      => array(),
				'ul'     => array(),
				'ol'     => array(),
				'li'     => array(),
				'a'      => array( 'href' => true ),
			)
		),
		'tickets'          => $tickets,
		'website'          => $event['url'],
		'online_url'       => $url( $raw['onlineurl'] ?? '' ),
		'age'              => ltt_dive_in_seeker_plain_text( $raw['agerequirement'] ?? '' ),
		'organizer_url'    => $url( $organizer['url'] ?? '' ),
		'venue_url'        => $url( $place['url'] ?? '' ),
		'address'          => $address,
		'occurrences'      => ! empty( $raw['recurring'] ) || ! empty( $raw['occurrences'] ) ? ltt_dive_in_get_seeker_occurrences( $raw, $timezone, wp_date( 'Y-m-d' ), 60 ) : array(),
	);
}

/**
 * Return Seeker event categories as `id => name`.
 *
 * @return array<int, string>
 */
function ltt_dive_in_get_seeker_categories() {
	$categories = array();

	for ( $offset = 0; $offset < 200; $offset += 50 ) {
		$body = ltt_dive_in_seeker_request(
			'/v1/categories',
			array(
				'categorytypes' => 'event',
				'limit'         => 50,
				'offset'        => $offset,
			),
			12 * HOUR_IN_SECONDS
		);

		if ( is_wp_error( $body ) ) {
			break;
		}

		$items = ltt_dive_in_get_seeker_items( $body );

		foreach ( $items as $item ) {
			$id   = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$name = trim( wp_strip_all_tags( (string) ( $item['category'] ?? $item['name'] ?? '' ) ) );

			if ( $id && '' !== $name ) {
				$categories[ $id ] = $name;
			}
		}

		if ( count( $items ) < 50 || ( isset( $body['total'] ) && $offset + 50 >= (int) $body['total'] ) ) {
			break;
		}
	}

	asort( $categories, SORT_NATURAL | SORT_FLAG_CASE );

	return $categories;
}

/**
 * Return Seeker regions as `id => name`.
 *
 * @return array<int, string>
 */
function ltt_dive_in_get_seeker_regions() {
	$regions = array();

	for ( $offset = 0; $offset < 200; $offset += 50 ) {
		$body = ltt_dive_in_seeker_request(
			'/v1/regions',
			array(
				'limit'  => 50,
				'offset' => $offset,
			),
			12 * HOUR_IN_SECONDS
		);

		if ( is_wp_error( $body ) ) {
			break;
		}

		$items = ltt_dive_in_get_seeker_items( $body );

		foreach ( $items as $item ) {
			$id   = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$name = ltt_dive_in_seeker_plain_text( $item['name'] ?? '' );

			if ( $id && '' !== $name ) {
				$regions[ $id ] = $name;
			}
		}

		if ( count( $items ) < 50 || ( isset( $body['total'] ) && $offset + 50 >= (int) $body['total'] ) ) {
			break;
		}
	}

	asort( $regions, SORT_NATURAL | SORT_FLAG_CASE );

	return $regions;
}

/**
 * Validate a Seeker UUID.
 *
 * @param mixed $uuid Candidate value.
 * @return string Lowercase UUID, or an empty string.
 */
function ltt_dive_in_sanitize_seeker_uuid( $uuid ) {
	$uuid = strtolower( trim( (string) $uuid ) );

	return preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid ) ? $uuid : '';
}

/**
 * Convert Seeker plain or HTML copy to trimmed plain text.
 *
 * @param mixed $value Source value.
 * @return string
 */
function ltt_dive_in_seeker_plain_text( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$text = html_entity_decode( wp_strip_all_tags( (string) $value, true ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	return trim( preg_replace( '/\s+/u', ' ', $text ) );
}

/**
 * Create a date in the event's timezone from Seeker date and time fields.
 *
 * @param string       $date     Date in `YYYY-MM-DD`.
 * @param string       $time     Time in `HH:MM`, or empty for midnight.
 * @param DateTimeZone $timezone Event timezone.
 * @return DateTimeImmutable|null
 */
function ltt_dive_in_seeker_date( $date, $time, $timezone ) {
	if ( ! is_string( $date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		return null;
	}

	$time   = is_string( $time ) && preg_match( '/^\d{2}:\d{2}/', $time ) ? substr( $time, 0, 5 ) : '00:00';
	$parsed = DateTimeImmutable::createFromFormat( '!Y-m-d H:i', $date . ' ' . $time, $timezone );

	return $parsed instanceof DateTimeImmutable ? $parsed : null;
}

/**
 * Parse the RRULE line of a Seeker recurrence rule.
 *
 * @param mixed $rrule iCalendar recurrence text, with or without DTSTART.
 * @return array<string, string> Upper-case rule parts, or an empty array.
 */
function ltt_dive_in_parse_seeker_rrule( $rrule ) {
	if ( ! is_string( $rrule ) || ! preg_match( '/(?:^|\n)(?:RRULE:)?(FREQ=[^\r\n]+)/i', $rrule, $match ) ) {
		return array();
	}

	$parts = array();

	foreach ( explode( ';', strtoupper( trim( $match[1] ) ) ) as $part ) {
		$pair = explode( '=', $part, 2 );

		if ( 2 === count( $pair ) && '' !== $pair[0] ) {
			$parts[ $pair[0] ] = $pair[1];
		}
	}

	return $parts;
}

/**
 * Whether a day belongs to a parsed recurrence rule.
 *
 * @param DateTimeImmutable $day   Candidate day.
 * @param DateTimeImmutable $first First day of the series.
 * @param array             $rule  Parsed rule from ltt_dive_in_parse_seeker_rrule().
 * @return bool
 */
function ltt_dive_in_seeker_rrule_matches( $day, $first, $rule ) {
	$weekdays = array( 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7 );
	$interval = max( 1, absint( $rule['INTERVAL'] ?? 1 ) );
	$byday    = array();

	foreach ( array_filter( explode( ',', $rule['BYDAY'] ?? '' ) ) as $code ) {
		if ( preg_match( '/^([+-]?\d{1,2})?(MO|TU|WE|TH|FR|SA|SU)$/', $code, $match ) ) {
			$byday[] = array( (int) $match[1], $weekdays[ $match[2] ] );
		}
	}

	switch ( $rule['FREQ'] ?? '' ) {
		case 'DAILY':
			return 0 === (int) $first->diff( $day )->days % $interval;

		case 'WEEKLY':
			$week_start = $first->modify( '-' . ( (int) $first->format( 'N' ) - 1 ) . ' days' );
			$days       = $byday ? wp_list_pluck( $byday, 1 ) : array( (int) $first->format( 'N' ) );

			return 0 === intdiv( (int) $week_start->diff( $day )->days, 7 ) % $interval && in_array( (int) $day->format( 'N' ), $days, true );

		case 'MONTHLY':
			$months = ( (int) $day->format( 'Y' ) - (int) $first->format( 'Y' ) ) * 12 + (int) $day->format( 'n' ) - (int) $first->format( 'n' );

			if ( 0 !== $months % $interval ) {
				return false;
			}

			if ( $byday ) {
				$from_start = intdiv( (int) $day->format( 'j' ) - 1, 7 ) + 1;
				$from_end   = -1 * ( intdiv( (int) $day->format( 't' ) - (int) $day->format( 'j' ), 7 ) + 1 );

				foreach ( $byday as $code ) {
					if ( (int) $day->format( 'N' ) === $code[1] && in_array( $code[0], array( 0, $from_start, $from_end ), true ) ) {
						return true;
					}
				}

				return false;
			}

			$monthdays = array_filter( array_map( 'intval', explode( ',', $rule['BYMONTHDAY'] ?? '' ) ) );
			$monthdays = $monthdays ? $monthdays : array( (int) $first->format( 'j' ) );

			foreach ( $monthdays as $monthday ) {
				if ( (int) $day->format( 'j' ) === ( $monthday > 0 ? $monthday : (int) $day->format( 't' ) + $monthday + 1 ) ) {
					return true;
				}
			}

			return false;

		case 'YEARLY':
			return 0 === ( (int) $day->format( 'Y' ) - (int) $first->format( 'Y' ) ) % $interval && $day->format( 'm-d' ) === $first->format( 'm-d' );
	}

	return false;
}

/**
 * List the dates of one Seeker schedule on or after a date.
 *
 * A schedule is the event itself or one entry of its `occurrences` list. A
 * schedule without a recurrence rule has a single date.
 *
 * @param array        $series   Seeker schedule: startdate, starttime, endtime, enddate, rrule, exceptions.
 * @param DateTimeZone $timezone Event timezone.
 * @param string       $from     Earliest date in `YYYY-MM-DD`.
 * @param int          $limit    Maximum number of dates.
 * @return array<int, array{start: DateTimeImmutable, end: DateTimeImmutable|null, has_end_time: bool}>
 */
function ltt_dive_in_get_seeker_series_dates( $series, $timezone, $from, $limit ) {
	$start    = ltt_dive_in_seeker_date( $series['startdate'] ?? '', $series['starttime'] ?? '', $timezone );
	$end      = ltt_dive_in_seeker_date( $series['startdate'] ?? '', $series['endtime'] ?? '', $timezone );
	$duration = $start && $end && ! empty( $series['endtime'] ) ? $end->getTimestamp() - $start->getTimestamp() : null;
	$rule     = ltt_dive_in_parse_seeker_rrule( $series['rrule'] ?? '' );
	$dates    = array();

	if ( ! $start ) {
		return $dates;
	}

	if ( null !== $duration && $duration <= 0 ) {
		$duration += DAY_IN_SECONDS;
	}

	if ( ! in_array( $rule['FREQ'] ?? '', array( 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY' ), true ) ) {
		$last = ltt_dive_in_seeker_date( $series['enddate'] ?? '', $series['endtime'] ?? '', $timezone );

		if ( ( $last ? $last->format( 'Y-m-d' ) : $start->format( 'Y-m-d' ) ) >= $from ) {
			$dates[] = array(
				'start'        => $start,
				'end'          => $last && $last > $start ? $last : ( null !== $duration ? $start->modify( '+' . $duration . ' seconds' ) : null ),
				'has_end_time' => ! empty( $series['endtime'] ),
			);
		}

		return $dates;
	}

	$first = $start->setTime( 0, 0 );
	$last  = $first->modify( '+10 years' );
	$until = ltt_dive_in_seeker_date( $series['enddate'] ?? '', '', $timezone );

	if ( $until && $until < $last ) {
		$last = $until;
	}

	if ( ! empty( $rule['UNTIL'] ) && preg_match( '/^(\d{8})(T\d{6}Z?)?$/', $rule['UNTIL'], $match ) ) {
		$until = isset( $match[2] ) && 'Z' === substr( $match[2], -1 )
			? DateTimeImmutable::createFromFormat( 'Ymd\THis\Z', $rule['UNTIL'], new DateTimeZone( 'UTC' ) )
			: DateTimeImmutable::createFromFormat( '!Ymd', $match[1], $timezone );

		if ( $until instanceof DateTimeImmutable ) {
			$until = $until->setTimezone( $timezone )->setTime( 0, 0 );
			$last  = $until < $last ? $until : $last;
		}
	}

	$excluded = array();

	foreach ( (array) ( $series['exceptions'] ?? array() ) as $exception ) {
		$value = is_array( $exception ) ? ( $exception['startdate'] ?? $exception['date'] ?? '' ) : $exception;

		if ( is_string( $value ) && preg_match( '/^(\d{4})-?(\d{2})-?(\d{2})/', $value, $match ) ) {
			$excluded[ $match[1] . '-' . $match[2] . '-' . $match[3] ] = true;
		}
	}

	$count   = absint( $rule['COUNT'] ?? 0 );
	$matched = 0;

	for ( $day = $first; $day <= $last && count( $dates ) < $limit; $day = $day->modify( '+1 day' ) ) {
		if ( ! ltt_dive_in_seeker_rrule_matches( $day, $first, $rule ) ) {
			continue;
		}

		++$matched;

		if ( $count && $matched > $count ) {
			break;
		}

		$date = $day->format( 'Y-m-d' );

		if ( $date < $from || isset( $excluded[ $date ] ) ) {
			continue;
		}

		$occurrence = ltt_dive_in_seeker_date( $date, $series['starttime'] ?? '', $timezone );
		$dates[]    = array(
			'start'        => $occurrence,
			'end'          => null !== $duration ? $occurrence->modify( '+' . $duration . ' seconds' ) : null,
			'has_end_time' => ! empty( $series['endtime'] ),
		);
	}

	return $dates;
}

/**
 * List the upcoming dates of a Seeker event, soonest first.
 *
 * Seeker reports a recurring series by its first `startdate`, which may be in
 * the past, and describes the individual dates with an iCalendar `rrule` and
 * an `exceptions` list. Additional schedules, each with its own rule and
 * exceptions, are listed in `occurrences`.
 *
 * @param array        $event    Raw Seeker event.
 * @param DateTimeZone $timezone Event timezone.
 * @param string       $from     Earliest date in `YYYY-MM-DD`.
 * @param int          $limit    Maximum number of dates.
 * @return array<int, array{start: DateTimeImmutable, end: DateTimeImmutable|null, has_end_time: bool}>
 */
function ltt_dive_in_get_seeker_occurrences( $event, $timezone, $from, $limit = 50 ) {
	$schedules = array_values( array_filter( (array) ( $event['occurrences'] ?? array() ), 'is_array' ) );
	$schedules = $schedules ? $schedules : array( $event );
	$dates     = array();

	foreach ( $schedules as $schedule ) {
		foreach ( ltt_dive_in_get_seeker_series_dates( $schedule, $timezone, $from, $limit ) as $date ) {
			$dates[ $date['start']->format( 'Y-m-d H:i' ) ] = $date;
		}
	}

	ksort( $dates );

	return array_slice( array_values( $dates ), 0, max( 1, $limit ) );
}

/**
 * Pick the first occurrence of a recurring Seeker event on or after a date.
 *
 * @param array        $event    Raw Seeker event.
 * @param DateTimeZone $timezone Event timezone.
 * @param string       $from     Earliest occurrence date in `YYYY-MM-DD`.
 * @return array{start: DateTimeImmutable, end: DateTimeImmutable|null, has_end_time: bool}|null
 */
function ltt_dive_in_get_seeker_next_occurrence( $event, $timezone, $from ) {
	if ( ! in_array( ltt_dive_in_parse_seeker_rrule( $event['rrule'] ?? '' )['FREQ'] ?? '', array( 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY' ), true ) && empty( $event['occurrences'] ) ) {
		return null;
	}

	$dates = ltt_dive_in_get_seeker_occurrences( $event, $timezone, $from, 1 );

	return $dates ? $dates[0] : null;
}

/**
 * Pick the first occurrence that overlaps or follows the start of a range.
 *
 * Recurring schedules have a time-of-day duration of at most 24 hours;
 * also inspect the preceding day to include overnight occurrences.
 *
 * @param array        $event Raw event.
 * @param DateTimeZone $timezone Event timezone.
 * @param string       $from First date of the range.
 * @return array|null
 */
function ltt_dive_in_get_seeker_range_occurrence( $event, $timezone, $from ) {
	$schedules = array_values( array_filter( (array) ( $event['occurrences'] ?? array() ), 'is_array' ) );
	$schedules = $schedules ? $schedules : array( $event );
	$lookback  = ( new DateTimeImmutable( $from, $timezone ) )->modify( '-1 day' )->format( 'Y-m-d' );
	$matches   = array();
	foreach ( $schedules as $schedule ) {
		foreach ( ltt_dive_in_get_seeker_series_dates( $schedule, $timezone, $lookback, 2 ) as $date ) {
			if ( ( $date['end'] ?? $date['start'] )->format( 'Y-m-d' ) >= $from ) {
				$matches[ $date['start']->format( 'Y-m-d H:i' ) ] = $date;
				break;
			}
		}
	}
	ksort( $matches );
	return $matches ? reset( $matches ) : null;
}

/**
 * Build responsive image data from one Seeker image.
 *
 * @param array $image Seeker image record.
 * @return array{src: string, srcset: string, width: int, height: int, caption: string}|array
 */
function ltt_dive_in_get_seeker_image_data( $image ) {
	$renditions = isset( $image['images'] ) && is_array( $image['images'] ) ? $image['images'] : array();
	$widths     = array(
		'small'    => 600,
		'medium'   => 1024,
		'large'    => 1400,
		'original' => absint( $image['width'] ?? 0 ),
	);
	$ratio      = ! empty( $image['width'] ) && ! empty( $image['height'] ) ? (float) $image['height'] / (float) $image['width'] : 0.75;
	$srcset     = array();
	$src        = '';
	$src_width  = 0;

	foreach ( $widths as $name => $width ) {
		$rendition = $renditions[ $name ] ?? null;
		$url       = is_array( $rendition ) ? (string) ( $rendition['url'] ?? '' ) : (string) $rendition;
		$width     = is_array( $rendition ) && ! empty( $rendition['width'] ) ? absint( $rendition['width'] ) : $width;
		$url       = esc_url_raw( $url, array( 'http', 'https' ) );

		if ( ! $url || ! $width || isset( $srcset[ $width ] ) ) {
			continue;
		}

		$srcset[ $width ] = $url . ' ' . $width . 'w';

		// Prefer the 1024px rendition as the fallback source when present.
		if ( ! $src || 'medium' === $name ) {
			$src       = $url;
			$src_width = $width;
		}
	}

	if ( ! $src ) {
		return array();
	}

	ksort( $srcset );

	return array(
		'src'     => $src,
		'srcset'  => implode( ', ', $srcset ),
		'width'   => $src_width,
		'height'  => (int) round( $src_width * $ratio ),
		'caption' => ltt_dive_in_seeker_plain_text( $image['caption'] ?? '' ),
	);
}

/**
 * Build responsive image data for every image in a Seeker collection.
 *
 * @param mixed $images Seeker paginated image envelope.
 * @return array[] Featured image first.
 */
function ltt_dive_in_get_seeker_images( $images ) {
	$items = is_array( $images ) ? ltt_dive_in_get_seeker_items( $images ) : array();

	usort(
		$items,
		static function ( $a, $b ) {
			return (int) ! empty( $b['featured'] ) - (int) ! empty( $a['featured'] );
		}
	);

	return array_values( array_filter( array_map( 'ltt_dive_in_get_seeker_image_data', $items ) ) );
}

/**
 * Build responsive image data from a Seeker image collection.
 *
 * @param mixed $images Seeker paginated image envelope.
 * @return array{src: string, srcset: string, width: int, height: int}|array The featured or first image.
 */
function ltt_dive_in_get_seeker_image( $images ) {
	$images = ltt_dive_in_get_seeker_images( $images );

	return $images ? $images[0] : array();
}

/**
 * Normalize one Seeker event for templates.
 *
 * @param array  $raw  Raw Seeker event.
 * @param string $from Earliest date in `YYYY-MM-DD` for a recurring occurrence; defaults to today.
 * @param bool   $include_overlap Include occurrences continuing into the range.
 * @return array|null Null when the event cannot be displayed.
 */
function ltt_dive_in_normalize_seeker_event( $raw, $from = '', $include_overlap = false ) {
	if ( ! is_array( $raw ) ) {
		return null;
	}

	if ( ! is_string( $from ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
		$from = wp_date( 'Y-m-d' );
	}

	$id    = ltt_dive_in_sanitize_seeker_uuid( $raw['uuid'] ?? '' );
	$title = ltt_dive_in_seeker_plain_text( $raw['name'] ?? '' );

	if ( ! $id || '' === $title || ( isset( $raw['state'] ) && 'published' !== $raw['state'] ) || 'cancelled' === ( $raw['eventstatus'] ?? '' ) ) {
		return null;
	}

	try {
		$timezone = new DateTimeZone( ! empty( $raw['timezone'] ) ? (string) $raw['timezone'] : wp_timezone_string() );
	} catch ( Exception $exception ) {
		$timezone = wp_timezone();
	}

	$start        = ltt_dive_in_seeker_date( $raw['startdate'] ?? '', $raw['starttime'] ?? '', $timezone );
	$end          = ltt_dive_in_seeker_date( $raw['enddate'] ?? '', $raw['endtime'] ?? '', $timezone );
	$has_end_time = ! empty( $raw['endtime'] );

	if ( ! empty( $raw['recurring'] ) || ! empty( $raw['occurrences'] ) ) {
		$next = $include_overlap ? ltt_dive_in_get_seeker_range_occurrence( $raw, $timezone, $from ) : ltt_dive_in_get_seeker_next_occurrence( $raw, $timezone, $from );

		if ( $next ) {
			$start        = $next['start'];
			$end          = $next['end'];
			$has_end_time = $next['has_end_time'];
		} elseif ( $include_overlap || ( $start && $start->format( 'Y-m-d' ) < $from ) ) {
			return null;
		}
	}

	if ( ! $start ) {
		return null;
	}

	$categories = array();

	foreach ( (array) ( $raw['categories'] ?? array() ) as $category ) {
		$name = is_array( $category ) ? ltt_dive_in_seeker_plain_text( $category['category'] ?? $category['name'] ?? '' ) : '';

		if ( '' !== $name ) {
			$categories[] = array(
				'id'   => absint( $category['id'] ?? 0 ),
				'name' => $name,
			);
		}
	}

	$place      = isset( $raw['place'] ) && is_array( $raw['place'] ) ? $raw['place'] : array();
	$location   = isset( $place['location'] ) && is_array( $place['location'] ) ? $place['location'] : array();
	$organizer  = isset( $raw['organizer'] ) && is_array( $raw['organizer'] ) ? $raw['organizer'] : array();
	$ticket_url = '';

	foreach ( (array) ( $raw['tickets'] ?? array() ) as $ticket ) {
		$ticket_url = is_array( $ticket ) ? esc_url_raw( (string) ( $ticket['url'] ?? '' ), array( 'http', 'https' ) ) : '';

		if ( $ticket_url ) {
			break;
		}
	}

	$image = ltt_dive_in_get_seeker_image( $raw['images'] ?? array() );

	if ( ! $image && ! empty( $place['images'] ) ) {
		$image = ltt_dive_in_get_seeker_image( $place['images'] );
	}

	return array(
		'id'          => $id,
		'title'       => $title,
		'description' => ltt_dive_in_seeker_plain_text( $raw['description'] ?? '' ),
		'url'         => esc_url_raw( (string) ( $raw['eventurl'] ?? '' ), array( 'http', 'https' ) ),
		'ticket_url'  => $ticket_url,
		'start'       => $start,
		'end'         => $end && $end >= $start ? $end : null,
		'has_end_time' => $has_end_time,
		'all_day'     => ! empty( $raw['allday'] ),
		'status'      => sanitize_key( $raw['eventstatus'] ?? 'scheduled' ),
		'mode'        => in_array( $raw['eventtype'] ?? '', array( 'online', 'hybrid' ), true ) ? (string) $raw['eventtype'] : 'physical',
		'categories'  => $categories,
		'venue'       => ltt_dive_in_seeker_plain_text( $place['name'] ?? '' ),
		'address'     => array(
			'street'  => ltt_dive_in_seeker_plain_text( $location['address'] ?? '' ),
			'city'    => ltt_dive_in_seeker_plain_text( $location['city'] ?? '' ),
			'region'  => ltt_dive_in_seeker_plain_text( $location['state'] ?? '' ),
			'postal'  => ltt_dive_in_seeker_plain_text( $location['zip'] ?? '' ),
			'country' => ltt_dive_in_seeker_plain_text( $location['country'] ?? '' ),
		),
		'organizer'   => ltt_dive_in_seeker_plain_text( $organizer['name'] ?? '' ),
		'free'        => ! empty( $raw['free'] ),
		'price'       => ltt_dive_in_seeker_plain_text( $raw['pricerange'] ?? '' ),
		'featured'    => ! empty( $raw['featured'] ),
		'recurring'   => ! empty( $raw['recurring'] ),
		'image'       => $image,
	);
}
