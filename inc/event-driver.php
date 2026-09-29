<?php
/**
 * Event Driver block data, endpoints, and structured data.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize saved Event Driver settings.
 *
 * The same normalization serves block rendering, where values come from
 * get_field(), and the REST endpoint, where they come from saved block data.
 *
 * @param callable $get Callback that receives a field name and returns its value.
 * @return array
 */
function ltt_dive_in_get_event_driver_config( $get ) {
	$limits   = ltt_dive_in_get_event_driver_limits();
	$variant  = (string) call_user_func( $get, 'ltt_dive_in_event_driver_variant' );
	$flag     = static function ( $name, $default ) use ( $get ) {
		$value = call_user_func( $get, $name );

		return null === $value || '' === $value ? $default : (bool) $value;
	};
	$count    = static function ( $name, $limit ) use ( $get ) {
		$value = call_user_func( $get, $name );
		$value = is_numeric( $value ) ? (int) $value : $limit['default'];

		return max( $limit['min'], min( $limit['max'], $value ) );
	};
	$text     = static function ( $name, $default = '' ) use ( $get ) {
		$value = call_user_func( $get, $name );

		return is_string( $value ) && '' !== trim( $value ) ? trim( $value ) : $default;
	};
	$link     = static function ( $name ) use ( $get ) {
		$value = call_user_func( $get, $name );

		if ( ! is_array( $value ) || empty( $value['title'] ) || empty( $value['url'] ) || ! is_string( $value['title'] ) || ! trim( $value['title'] ) ) {
			return array();
		}

		return array(
			'title'  => trim( $value['title'] ),
			'url'    => (string) $value['url'],
			'target' => ! empty( $value['target'] ) ? '_blank' : '',
		);
	};
	$ids      = static function ( $name ) use ( $get ) {
		return array_values( array_unique( array_filter( array_map( 'absint', (array) call_user_func( $get, $name ) ) ) ) );
	};

	return array(
		'variant'          => in_array( $variant, array( 'hero', 'listed', 'carousel' ), true ) ? $variant : '',
		'hero_source'      => 'manual' === call_user_func( $get, 'ltt_dive_in_event_driver_hero_source' ) ? 'manual' : 'featured',
		'hero_count'       => $count( 'ltt_dive_in_event_driver_hero_count', $limits['hero'] ),
		'hero_events'      => array_values( array_filter( array_map( 'ltt_dive_in_sanitize_seeker_uuid', (array) call_user_func( $get, 'ltt_dive_in_event_driver_hero_events' ) ) ) ),
		'primary_label'    => $text( 'ltt_dive_in_event_driver_primary_label', __( 'Get Tickets', 'ltt-dive-in' ) ),
		'secondary_label'  => $text( 'ltt_dive_in_event_driver_secondary_label', __( 'Learn More', 'ltt-dive-in' ) ),
		'tag'              => $text( 'ltt_dive_in_event_driver_tag' ),
		'heading'          => $text( 'ltt_dive_in_event_driver_heading' ),
		'intro'            => $text( 'ltt_dive_in_event_driver_intro' ),
		'links'            => array_values( array_filter( array( $link( 'ltt_dive_in_event_driver_primary_link' ), $link( 'ltt_dive_in_event_driver_secondary_link' ) ) ) ),
		'initial_count'    => $count( 'ltt_dive_in_event_driver_initial_count', $limits['listed'] ),
		'load_more_count'  => $count( 'ltt_dive_in_event_driver_load_more_count', $limits['load_more'] ),
		'filter_categories' => $ids( 'ltt_dive_in_event_driver_filter_categories' ),
		'show_category'    => $flag( 'ltt_dive_in_event_driver_show_category_filter', true ),
		'show_date'        => $flag( 'ltt_dive_in_event_driver_show_date_filter', true ),
		'show_search'      => $flag( 'ltt_dive_in_event_driver_show_search', true ),
		'carousel_count'   => $count( 'ltt_dive_in_event_driver_carousel_count', $limits['carousel'] ),
		'carousel_sort'    => 'recent' === call_user_func( $get, 'ltt_dive_in_event_driver_carousel_sort' ) ? 'recent' : 'soonest',
		'carousel_featured' => $flag( 'ltt_dive_in_event_driver_carousel_featured', false ),
		'carousel_categories' => $ids( 'ltt_dive_in_event_driver_carousel_categories' ),
	);
}

/**
 * Return the events for the Hero Featured Events variant.
 *
 * @param array $config Normalized block settings.
 * @return array[]|WP_Error
 */
function ltt_dive_in_get_event_driver_hero_events( $config ) {
	if ( 'manual' === $config['hero_source'] ) {
		$today  = new DateTimeImmutable( 'today', wp_timezone() );
		$events = array_filter(
			ltt_dive_in_get_seeker_events_by_id( $config['hero_events'] ),
			static function ( $event ) use ( $today ) {
				$last_day = $event['end'] ? $event['end'] : $event['start'];

				return $last_day >= $today && 'cancelled' !== $event['status'];
			}
		);

		return array_slice( array_values( $events ), 0, ltt_dive_in_get_event_driver_limits()['hero']['max'] );
	}

	$result = ltt_dive_in_get_seeker_events(
		array(
			'limit'    => $config['hero_count'],
			'featured' => true,
		)
	);

	return is_wp_error( $result ) ? $result : $result['events'];
}

/**
 * Return the events for the Events Carousel variant.
 *
 * @param array $config Normalized block settings.
 * @return array[]|WP_Error
 */
function ltt_dive_in_get_event_driver_carousel_events( $config ) {
	$result = ltt_dive_in_get_seeker_events(
		array(
			'limit'        => $config['carousel_count'],
			'sort'         => $config['carousel_sort'],
			'featured'     => $config['carousel_featured'],
			'category_ids' => $config['carousel_categories'],
		)
	);

	return is_wp_error( $result ) ? $result : $result['events'];
}

/**
 * Sanitize visitor filter input for the Listed Events variant.
 *
 * @param array $config Normalized block settings.
 * @param array $params Raw filter values: category, date, search, page.
 * @return array{category: int, date: string, search: string, page: int}
 */
function ltt_dive_in_get_event_driver_listed_filters( $config, $params ) {
	$category = $config['show_category'] ? absint( $params['category'] ?? 0 ) : 0;
	$date     = $config['show_date'] ? sanitize_key( (string) ( $params['date'] ?? '' ) ) : '';
	$search   = $config['show_search'] ? trim( sanitize_text_field( wp_unslash( (string) ( $params['search'] ?? '' ) ) ) ) : '';

	if ( $category && $config['filter_categories'] && ! in_array( $category, $config['filter_categories'], true ) ) {
		$category = 0;
	}

	return array(
		'category' => $category,
		'date'     => array_key_exists( $date, ltt_dive_in_get_event_date_filters() ) ? $date : 'upcoming',
		'search'   => function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 80 ) : substr( $search, 0, 80 ),
		'page'     => max( 1, min( 20, absint( $params['page'] ?? 1 ) ) ),
	);
}

/**
 * Query one page of Listed Events.
 *
 * @param array $config  Normalized block settings.
 * @param array $filters Sanitized filters.
 * @param int   $offset  Zero-based offset.
 * @param int   $limit   Number of events.
 * @return array{events: array[], total: int, has_more: bool}|WP_Error
 */
function ltt_dive_in_query_event_driver_listed_events( $config, $filters, $offset, $limit ) {
	$result = ltt_dive_in_get_seeker_events(
		array(
			'limit'        => $limit,
			'offset'       => $offset,
			'search'       => $filters['search'],
			'category_ids' => $filters['category'] ? array( $filters['category'] ) : array(),
			'date'         => $filters['date'],
		)
	);

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	$result['has_more'] = $result['total'] > $offset + count( $result['events'] ) && count( $result['events'] ) === $limit;

	return $result;
}

/**
 * Return the category choices shown in the Listed Events dropdown.
 *
 * @param array $config Normalized block settings.
 * @return array<int, string>
 */
function ltt_dive_in_get_event_driver_category_options( $config ) {
	$categories = ltt_dive_in_get_seeker_categories();

	if ( $config['filter_categories'] ) {
		$categories = array_intersect_key( $categories, array_flip( $config['filter_categories'] ) );
	}

	return $categories;
}

/**
 * Identify a saved block from its ACF data.
 *
 * @param array $data Saved ACF block data.
 * @return string
 */
function ltt_dive_in_get_event_driver_block_hash( $data ) {
	return md5( wp_json_encode( is_array( $data ) ? $data : array() ) );
}

/**
 * Find a saved Event Driver block by its data hash.
 *
 * @param array[] $blocks Parsed blocks.
 * @param string  $hash   Block data hash.
 * @return array|null
 */
function ltt_dive_in_find_event_driver_block( $blocks, $hash ) {
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( 'ltt-dive-in/event-driver' === ( $block['blockName'] ?? '' ) && hash_equals( ltt_dive_in_get_event_driver_block_hash( $block['attrs']['data'] ?? array() ), $hash ) ) {
			return $block;
		}

		$found = ltt_dive_in_find_event_driver_block( $block['innerBlocks'] ?? array(), $hash );

		if ( $found ) {
			return $found;
		}
	}

	return null;
}

/**
 * Render event cards for the Listed Events grid.
 *
 * @param array[] $events Normalized events.
 * @return string
 */
function ltt_dive_in_render_event_driver_listed_cards( $events ) {
	ob_start();

	foreach ( $events as $event ) {
		get_template_part(
			'template-parts/components/event-card',
			null,
			array(
				'event'   => $event,
				'style'   => 'tile',
				'wrapper' => 'event-driver__item swiper-slide',
			)
		);
	}

	return (string) ob_get_clean();
}

/**
 * Return filtered Listed Events for the client-side filters and Load More.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function ltt_dive_in_get_event_driver_rest_events( WP_REST_Request $request ) {
	$post = get_post( $request->get_param( 'post' ) );

	if ( ! $post instanceof WP_Post || ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post->ID ) ) || ( post_password_required( $post ) && ! current_user_can( 'edit_post', $post->ID ) ) ) {
		return new WP_Error( 'ltt_dive_in_event_driver_not_found', __( 'Events not found.', 'ltt-dive-in' ), array( 'status' => 404 ) );
	}

	$block = ltt_dive_in_find_event_driver_block( parse_blocks( $post->post_content ), (string) $request->get_param( 'block' ) );

	if ( ! $block ) {
		return new WP_Error( 'ltt_dive_in_event_driver_not_found', __( 'Events not found.', 'ltt-dive-in' ), array( 'status' => 404 ) );
	}

	$data   = is_array( $block['attrs']['data'] ?? null ) ? $block['attrs']['data'] : array();
	$config = ltt_dive_in_get_event_driver_config(
		static function ( $name ) use ( $data ) {
			return $data[ $name ] ?? null;
		}
	);

	if ( 'listed' !== $config['variant'] ) {
		return new WP_Error( 'ltt_dive_in_event_driver_not_found', __( 'Events not found.', 'ltt-dive-in' ), array( 'status' => 404 ) );
	}

	$filters = ltt_dive_in_get_event_driver_listed_filters(
		$config,
		array(
			'category' => $request->get_param( 'category' ),
			'date'     => $request->get_param( 'date' ),
			'search'   => $request->get_param( 'search' ),
		)
	);
	$offset  = min( 200, absint( $request->get_param( 'offset' ) ) );
	$limit   = $offset ? $config['load_more_count'] : $config['initial_count'];
	$result  = ltt_dive_in_query_event_driver_listed_events( $config, $filters, $offset, $limit );

	if ( is_wp_error( $result ) ) {
		return new WP_Error( 'ltt_dive_in_event_driver_unavailable', __( 'Events are temporarily unavailable. Please try again later.', 'ltt-dive-in' ), array( 'status' => 503 ) );
	}

	$response = rest_ensure_response(
		array(
			'html'       => ltt_dive_in_render_event_driver_listed_cards( $result['events'] ),
			'count'      => count( $result['events'] ),
			'total'      => $result['total'],
			'nextOffset' => $offset + count( $result['events'] ),
			'hasMore'    => $result['has_more'],
		)
	);
	$response->header( 'Cache-Control', 'publish' === $post->post_status ? 'public, max-age=300' : 'private, no-store' );

	return $response;
}

/**
 * Escape an iCalendar text value.
 *
 * @param string $value Plain text.
 * @return string
 */
function ltt_dive_in_escape_ical_text( $value ) {
	return str_replace( array( '\\', ';', ',', "\r\n", "\n", "\r" ), array( '\\\\', '\;', '\,', '\n', '\n', '\n' ), (string) $value );
}

/**
 * Fold an iCalendar content line to 75 octets.
 *
 * @param string $line Unfolded line.
 * @return string
 */
function ltt_dive_in_fold_ical_line( $line ) {
	$folded = '';

	while ( strlen( $line ) > 75 ) {
		$chunk = function_exists( 'mb_strcut' ) ? mb_strcut( $line, 0, 75, 'UTF-8' ) : substr( $line, 0, 75 );
		$folded .= $chunk . "\r\n ";
		$line    = substr( $line, strlen( $chunk ) );
	}

	return $folded . $line;
}

/**
 * Build an iCalendar file with a one-hour reminder for an event.
 *
 * @param array $event Normalized event.
 * @return string
 */
function ltt_dive_in_get_event_ical( $event ) {
	$utc      = new DateTimeZone( 'UTC' );
	$location = implode( ', ', array_filter( array( $event['venue'], $event['address']['street'], $event['address']['city'], $event['address']['region'] ) ) );
	$lines    = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//' . ltt_dive_in_escape_ical_text( get_bloginfo( 'name' ) ) . '//Events//EN',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VEVENT',
		'UID:' . $event['id'] . '@seeker.io',
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
	);

	if ( $event['all_day'] ) {
		$last_day = $event['end'] ? $event['end'] : $event['start'];
		$lines[]  = 'DTSTART;VALUE=DATE:' . $event['start']->format( 'Ymd' );
		$lines[]  = 'DTEND;VALUE=DATE:' . $last_day->modify( '+1 day' )->format( 'Ymd' );
	} else {
		$lines[] = 'DTSTART:' . $event['start']->setTimezone( $utc )->format( 'Ymd\THis\Z' );

		if ( $event['end'] && $event['end'] > $event['start'] ) {
			$lines[] = 'DTEND:' . $event['end']->setTimezone( $utc )->format( 'Ymd\THis\Z' );
		}
	}

	$lines[] = 'SUMMARY:' . ltt_dive_in_escape_ical_text( $event['title'] );

	if ( $event['description'] ) {
		$lines[] = 'DESCRIPTION:' . ltt_dive_in_escape_ical_text( wp_trim_words( $event['description'], 80, '…' ) );
	}

	if ( $location ) {
		$lines[] = 'LOCATION:' . ltt_dive_in_escape_ical_text( $location );
	}

	if ( $event['url'] ) {
		$lines[] = 'URL:' . $event['url'];
	}

	$lines[] = 'BEGIN:VALARM';
	$lines[] = 'TRIGGER:-PT1H';
	$lines[] = 'ACTION:DISPLAY';
	$lines[] = 'DESCRIPTION:' . ltt_dive_in_escape_ical_text( $event['title'] );
	$lines[] = 'END:VALARM';
	$lines[] = 'END:VEVENT';
	$lines[] = 'END:VCALENDAR';

	return implode( "\r\n", array_map( 'ltt_dive_in_fold_ical_line', $lines ) ) . "\r\n";
}

/**
 * Return the reminder calendar file URL for an event.
 *
 * @param array $event Normalized event.
 * @return string
 */
function ltt_dive_in_get_event_reminder_url( $event ) {
	return add_query_arg( 'event', rawurlencode( $event['id'] ), rest_url( 'ltt-dive-in/v1/event-calendar' ) );
}

/**
 * Return an iCalendar file for one Seeker event.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function ltt_dive_in_get_event_driver_calendar( WP_REST_Request $request ) {
	$events = ltt_dive_in_get_seeker_events_by_id( array( $request->get_param( 'event' ) ) );

	if ( ! $events ) {
		return new WP_Error( 'ltt_dive_in_event_not_found', __( 'Event not found.', 'ltt-dive-in' ), array( 'status' => 404 ) );
	}

	$response = new WP_REST_Response( ltt_dive_in_get_event_ical( $events[0] ) );
	$response->header( 'Content-Type', 'text/calendar; charset=utf-8' );
	$response->header( 'Content-Disposition', 'attachment; filename="' . sanitize_file_name( sanitize_title( $events[0]['title'] ) ) . '.ics"' );
	$response->header( 'Cache-Control', 'public, max-age=900' );
	$response->header( 'X-Robots-Tag', 'noindex' );

	return $response;
}

/**
 * Serve the calendar route as a raw file instead of JSON.
 *
 * @param bool             $served  Whether the request has been served.
 * @param WP_HTTP_Response $result  Response.
 * @param WP_REST_Request  $request Request.
 * @return bool
 */
function ltt_dive_in_serve_event_driver_calendar( $served, $result, $request ) {
	if ( $served || '/ltt-dive-in/v1/event-calendar' !== $request->get_route() || ! $result instanceof WP_HTTP_Response || 200 !== $result->get_status() ) {
		return $served;
	}

	echo $result->get_data(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- iCalendar text escaped by ltt_dive_in_get_event_ical().

	return true;
}
add_filter( 'rest_pre_serve_request', 'ltt_dive_in_serve_event_driver_calendar', 10, 3 );

/**
 * Register Event Driver REST routes.
 */
function ltt_dive_in_register_event_driver_routes() {
	register_rest_route(
		'ltt-dive-in/v1',
		'/event-driver-events',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ltt_dive_in_get_event_driver_rest_events',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post'     => array( 'required' => true, 'sanitize_callback' => 'absint' ),
				'block'    => array( 'required' => true, 'sanitize_callback' => 'sanitize_key' ),
				'offset'   => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'category' => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'date'     => array( 'default' => '', 'sanitize_callback' => 'sanitize_key' ),
				'search'   => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
			),
		)
	);

	register_rest_route(
		'ltt-dive-in/v1',
		'/event-calendar',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ltt_dive_in_get_event_driver_calendar',
			'permission_callback' => '__return_true',
			'args'                => array(
				'event' => array( 'required' => true, 'sanitize_callback' => 'ltt_dive_in_sanitize_seeker_uuid' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'ltt_dive_in_register_event_driver_routes' );

/**
 * Build schema.org Event data for rendered events.
 *
 * @param array[] $events Normalized events.
 * @return array
 */
function ltt_dive_in_get_event_schema( $events ) {
	$statuses = array(
		'scheduled'   => 'https://schema.org/EventScheduled',
		'rescheduled' => 'https://schema.org/EventRescheduled',
		'postponed'   => 'https://schema.org/EventPostponed',
		'cancelled'   => 'https://schema.org/EventCancelled',
	);
	$modes    = array(
		'physical' => 'https://schema.org/OfflineEventAttendanceMode',
		'online'   => 'https://schema.org/OnlineEventAttendanceMode',
		'hybrid'   => 'https://schema.org/MixedEventAttendanceMode',
	);
	$items    = array();

	foreach ( $events as $event ) {
		$item = array(
			'@context'            => 'https://schema.org',
			'@type'               => 'Event',
			'name'                => $event['title'],
			'startDate'           => ltt_dive_in_get_event_datetime_attribute( $event ),
			'eventStatus'         => $statuses[ $event['status'] ] ?? $statuses['scheduled'],
			'eventAttendanceMode' => $modes[ $event['mode'] ],
		);

		if ( $event['end'] ) {
			$item['endDate'] = $event['all_day'] ? $event['end']->format( 'Y-m-d' ) : $event['end']->format( DATE_W3C );
		}

		if ( $event['description'] ) {
			$item['description'] = wp_trim_words( $event['description'], 60, '…' );
		}

		if ( ! empty( $event['image']['src'] ) ) {
			$item['image'] = array( $event['image']['src'] );
		}

		if ( $event['url'] ) {
			$item['url'] = $event['url'];
		}

		if ( $event['venue'] ) {
			$item['location'] = array(
				'@type'   => 'Place',
				'name'    => $event['venue'],
				'address' => array_filter(
					array(
						'@type'           => 'PostalAddress',
						'streetAddress'   => $event['address']['street'],
						'addressLocality' => $event['address']['city'],
						'addressRegion'   => $event['address']['region'],
						'postalCode'      => $event['address']['postal'],
						'addressCountry'  => $event['address']['country'],
					)
				),
			);
		}

		if ( $event['organizer'] ) {
			$item['organizer'] = array(
				'@type' => 'Organization',
				'name'  => $event['organizer'],
			);
		}

		if ( $event['ticket_url'] ) {
			$item['offers'] = array(
				'@type' => 'Offer',
				'url'   => $event['ticket_url'],
			);
		}

		$items[] = $item;
	}

	return $items;
}

/**
 * Print schema.org Event data for rendered events.
 *
 * @param array[] $events Normalized events.
 * @return void
 */
function ltt_dive_in_print_event_schema( $events ) {
	$schema = ltt_dive_in_get_event_schema( $events );

	if ( ! $schema ) {
		return;
	}

	wp_print_inline_script_tag( wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ), array( 'type' => 'application/ld+json' ) );
}
