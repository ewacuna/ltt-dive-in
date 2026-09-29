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
	$today = new DateTimeImmutable( 'today', wp_timezone() );
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
 * Query published Seeker events.
 *
 * Only these arguments are forwarded, which keeps cache keys bounded and
 * prevents visitor input from reaching other API parameters:
 *
 * - `limit` (1-100), `offset`
 * - `search` free text
 * - `category_ids` int[]
 * - `date` date filter key from ltt_dive_in_get_event_date_filters()
 * - `featured` bool
 * - `sort` `soonest` or `recent`
 *
 * @param array $args Query arguments.
 * @return array{events: array[], total: int}|WP_Error
 */
function ltt_dive_in_get_seeker_events( $args = array() ) {
	$args  = wp_parse_args(
		$args,
		array(
			'limit'        => 9,
			'offset'       => 0,
			'search'       => '',
			'category_ids' => array(),
			'date'         => 'upcoming',
			'featured'     => false,
			'sort'         => 'soonest',
		)
	);
	$range = ltt_dive_in_get_event_date_range( array_key_exists( $args['date'], ltt_dive_in_get_event_date_filters() ) ? $args['date'] : 'upcoming' );
	$query = array(
		'limit'          => max( 1, min( 100, absint( $args['limit'] ) ) ),
		'offset'         => absint( $args['offset'] ),
		'states'         => 'published',
		'eventstatus'    => 'scheduled,rescheduled,postponed',
		'datefilterfrom' => $range['from'],
		'sortby'         => 'recent' === $args['sort'] ? 'datecreated' : 'startdatetime',
		'sortdir'        => 'recent' === $args['sort'] ? 'desc' : 'asc',
	);
	$search       = trim( sanitize_text_field( (string) $args['search'] ) );
	$category_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $args['category_ids'] ) ) ) );

	if ( $range['to'] ) {
		$query['datefilterto'] = $range['to'];
	}

	if ( '' !== $search ) {
		$query['q'] = function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 80 ) : substr( $search, 0, 80 );
	}

	if ( $category_ids ) {
		sort( $category_ids );
		$query['categoryids'] = implode( ',', $category_ids );
	}

	if ( $args['featured'] ) {
		$query['featured'] = 'true';
	}

	$body = ltt_dive_in_seeker_request( '/events/feeds', $query );

	if ( is_wp_error( $body ) ) {
		return $body;
	}

	$events = array_values( array_filter( array_map( 'ltt_dive_in_normalize_seeker_event', ltt_dive_in_get_seeker_items( $body ) ) ) );

	return array(
		'events' => $events,
		'total'  => isset( $body['total'] ) ? absint( $body['total'] ) : $query['offset'] + count( $events ),
	);
}

/**
 * Look up individual events by Seeker UUID, preserving the requested order.
 *
 * @param string[] $uuids Seeker event UUIDs.
 * @return array[] Normalized upcoming events.
 */
function ltt_dive_in_get_seeker_events_by_id( $uuids ) {
	$events = array();

	foreach ( array_slice( array_filter( array_map( 'ltt_dive_in_sanitize_seeker_uuid', (array) $uuids ) ), 0, 12 ) as $uuid ) {
		$body = ltt_dive_in_seeker_request(
			'/events/feeds',
			array(
				'limit'       => 1,
				'q'           => $uuid,
				'searchfield' => 'id',
				'states'      => 'published',
			)
		);

		if ( is_wp_error( $body ) ) {
			continue;
		}

		foreach ( ltt_dive_in_get_seeker_items( $body ) as $item ) {
			$event = ltt_dive_in_normalize_seeker_event( $item );

			if ( $event && $uuid === $event['id'] ) {
				$events[] = $event;
				break;
			}
		}
	}

	return $events;
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
			$name = isset( $item['name'] ) ? trim( wp_strip_all_tags( (string) $item['name'] ) ) : '';

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
 * Pick the next upcoming occurrence for a recurring Seeker event.
 *
 * Seeker reports a recurring series by its first `startdate`, which may be in
 * the past; the `occurrences` list carries the individual dates.
 *
 * @param array        $event    Raw Seeker event.
 * @param DateTimeZone $timezone Event timezone.
 * @return array{start: DateTimeImmutable, end: DateTimeImmutable|null}|null
 */
function ltt_dive_in_get_seeker_next_occurrence( $event, $timezone ) {
	$today = new DateTimeImmutable( 'today', $timezone );
	$next  = null;

	foreach ( (array) ( $event['occurrences'] ?? array() ) as $occurrence ) {
		if ( is_string( $occurrence ) ) {
			$occurrence = array( 'startdate' => substr( $occurrence, 0, 10 ) );
		}

		if ( ! is_array( $occurrence ) ) {
			continue;
		}

		$start = ltt_dive_in_seeker_date( $occurrence['startdate'] ?? '', $occurrence['starttime'] ?? ( $event['starttime'] ?? '' ), $timezone );

		if ( ! $start || $start < $today || ( $next && $start >= $next['start'] ) ) {
			continue;
		}

		$next = array(
			'start' => $start,
			'end'   => ltt_dive_in_seeker_date( $occurrence['enddate'] ?? '', $occurrence['endtime'] ?? ( $event['endtime'] ?? '' ), $timezone ),
		);
	}

	return $next;
}

/**
 * Build responsive image data from a Seeker image collection.
 *
 * @param mixed $images Seeker paginated image envelope.
 * @return array{src: string, srcset: string, width: int, height: int}|array
 */
function ltt_dive_in_get_seeker_image( $images ) {
	$items = is_array( $images ) ? ltt_dive_in_get_seeker_items( $images ) : array();

	if ( ! $items ) {
		return array();
	}

	usort(
		$items,
		static function ( $a, $b ) {
			return (int) ! empty( $b['featured'] ) - (int) ! empty( $a['featured'] );
		}
	);

	$image       = $items[0];
	$renditions  = isset( $image['images'] ) && is_array( $image['images'] ) ? $image['images'] : array();
	$widths      = array(
		'small'    => 600,
		'medium'   => 1024,
		'large'    => 1400,
		'original' => absint( $image['width'] ?? 0 ),
	);
	$ratio       = ! empty( $image['width'] ) && ! empty( $image['height'] ) ? (float) $image['height'] / (float) $image['width'] : 0.75;
	$srcset      = array();
	$src         = '';
	$src_width   = 0;

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
		'src'    => $src,
		'srcset' => implode( ', ', $srcset ),
		'width'  => $src_width,
		'height' => (int) round( $src_width * $ratio ),
	);
}

/**
 * Normalize one Seeker event for templates.
 *
 * @param array $raw Raw Seeker event.
 * @return array|null Null when the event cannot be displayed.
 */
function ltt_dive_in_normalize_seeker_event( $raw ) {
	if ( ! is_array( $raw ) ) {
		return null;
	}

	$id    = ltt_dive_in_sanitize_seeker_uuid( $raw['uuid'] ?? '' );
	$title = ltt_dive_in_seeker_plain_text( $raw['name'] ?? '' );

	if ( ! $id || '' === $title || ( isset( $raw['state'] ) && 'published' !== $raw['state'] ) ) {
		return null;
	}

	try {
		$timezone = new DateTimeZone( ! empty( $raw['timezone'] ) ? (string) $raw['timezone'] : wp_timezone_string() );
	} catch ( Exception $exception ) {
		$timezone = wp_timezone();
	}

	$start = ltt_dive_in_seeker_date( $raw['startdate'] ?? '', $raw['starttime'] ?? '', $timezone );
	$end   = ltt_dive_in_seeker_date( $raw['enddate'] ?? '', $raw['endtime'] ?? '', $timezone );

	if ( ! empty( $raw['recurring'] ) ) {
		$next = ltt_dive_in_get_seeker_next_occurrence( $raw, $timezone );

		if ( $next ) {
			$start = $next['start'];
			$end   = $next['end'];
		}
	}

	if ( ! $start ) {
		return null;
	}

	$categories = array();

	foreach ( (array) ( $raw['categories'] ?? array() ) as $category ) {
		$name = is_array( $category ) ? ltt_dive_in_seeker_plain_text( $category['name'] ?? '' ) : '';

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
		'image'       => $image,
	);
}
