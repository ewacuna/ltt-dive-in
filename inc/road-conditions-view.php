<?php
/**
 * Road conditions view model for the Current Conditions roads tab.
 *
 * Turns cached provider data (inc/road-conditions.php) into highway cards:
 * an overall status, report entries with an optional camera, and the list of
 * chain-control checkpoints. Templates only print the result.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * States shown as filter buttons, in display order.
 *
 * @return array<string, array{label: string, source: string, source_url: string}>
 */
function ltt_dive_in_get_road_states() {
	return array(
		'california' => array(
			'label'      => __( 'California', 'ltt-dive-in' ),
			'source'     => __( 'Caltrans', 'ltt-dive-in' ),
			'source_url' => 'https://quickmap.dot.ca.gov/',
		),
		'nevada'     => array(
			'label'      => __( 'Nevada', 'ltt-dive-in' ),
			'source'     => __( 'Nevada DOT', 'ltt-dive-in' ),
			'source_url' => 'https://www.nvroads.com/',
		),
	);
}

/**
 * Card statuses, most severe first, with their visible labels.
 *
 * Labels are always visible text, so the badge never relies on color alone.
 *
 * @return array<string, string>
 */
function ltt_dive_in_get_road_status_labels() {
	return array(
		'closed'   => __( 'Closed', 'ltt-dive-in' ),
		'chains'   => __( 'Chain Controls', 'ltt-dive-in' ),
		'advisory' => __( 'Advisory', 'ltt-dive-in' ),
		'clear'    => __( 'No Restrictions', 'ltt-dive-in' ),
		'none'     => __( 'No Report', 'ltt-dive-in' ),
	);
}

/**
 * Get the valid highways configured on a roads tab.
 *
 * @param int $post_id Tab page ID.
 * @return array<int, array{route: string, slug: string, shield: string, number: int, name: string, subtitle: string, states: string[], sign: int, sign_nevada: int, camera_mode: string, cameras: string[]}>
 */
function ltt_dive_in_get_road_highways( $post_id ) {
	$rows     = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_roads_highways', $post_id ) : array();
	$prefixes = array(
		'interstate' => 'I-',
		'us'         => 'US-',
		'state'      => 'SR-',
	);
	$states   = array_keys( ltt_dive_in_get_road_states() );
	$highways = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 12 ) : array() as $row ) {
		$shield = (string) ( $row['shield'] ?? '' );
		$number = absint( $row['number'] ?? 0 );
		$name   = trim( (string) ( $row['name'] ?? '' ) );

		if ( ! isset( $prefixes[ $shield ] ) || ! $number || $number > 999 || '' === $name ) {
			continue;
		}

		$route = $prefixes[ $shield ] . $number;

		if ( isset( $highways[ $route ] ) ) {
			continue;
		}

		$mode = (string) ( $row['camera_mode'] ?? 'auto' );

		$highways[ $route ] = array(
			'route'       => $route,
			'slug'        => strtolower( $route ),
			'shield'      => $shield,
			'number'      => $number,
			'name'        => $name,
			'subtitle'    => trim( (string) ( $row['subtitle'] ?? '' ) ),
			'states'      => array_values( array_intersect( $states, (array) ( $row['states'] ?? array() ) ) ),
			'sign'        => absint( $row['sign'] ?? 0 ),
			'sign_nevada' => absint( $row['sign_nevada'] ?? 0 ),
			'camera_mode' => in_array( $mode, array( 'auto', 'manual', 'none' ), true ) ? $mode : 'auto',
			'cameras'     => array_values( array_filter( array_map( 'strval', (array) ( $row['cameras'] ?? array() ) ) ) ),
		);
	}

	return array_values( $highways );
}

/**
 * Resolve the roads module for a tab, including the visitor's filter choices.
 *
 * @param int $post_id Tab page ID.
 * @return array{module: string, post_id?: int, area?: array, highways?: array, state?: string, highway?: string}
 */
function ltt_dive_in_get_road_conditions_module( $post_id ) {
	$highways = ltt_dive_in_get_road_highways( $post_id );

	if ( ! $highways ) {
		return array( 'module' => '' );
	}

	$states = array_keys( ltt_dive_in_get_road_states() );

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only display choices, validated against allowed values.
	$state   = isset( $_GET['road_state'] ) ? sanitize_key( wp_unslash( $_GET['road_state'] ) ) : '';
	$highway = isset( $_GET['highway'] ) ? sanitize_title( wp_unslash( $_GET['highway'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$state = in_array( $state, $states, true ) ? $state : $states[0];

	if ( ! in_array( $highway, wp_list_pluck( ltt_dive_in_filter_highways_by_state( $highways, $state ), 'slug' ), true ) ) {
		$highway = '';
	}

	return array(
		'module'   => 'roads',
		'post_id'  => (int) $post_id,
		'area'     => ltt_dive_in_get_road_conditions_area( $post_id ),
		'highways' => $highways,
		'state'    => $state,
		'highway'  => $highway,
	);
}

/**
 * Keep the highways shown under a state.
 *
 * @param array  $highways Highways from ltt_dive_in_get_road_highways().
 * @param string $state    State slug.
 * @return array
 */
function ltt_dive_in_filter_highways_by_state( $highways, $state ) {
	return array_values(
		array_filter(
			$highways,
			static function ( $highway ) use ( $state ) {
				return in_array( $state, $highway['states'], true );
			}
		)
	);
}

/**
 * Build the cards for one state.
 *
 * @param string $state    State slug.
 * @param array  $highways Highways from ltt_dive_in_get_road_highways().
 * @param array  $area     Area from ltt_dive_in_get_road_conditions_area().
 * @return array{status: string, updated: int, cards: array}
 *         Status is `ok`, `pending`, `missing_key`, or `unavailable`; cards are empty unless `ok`.
 */
function ltt_dive_in_get_road_state_view( $state, $highways, $area ) {
	$data = ltt_dive_in_get_road_conditions( $state, $area );
	$view = array(
		'status'  => $data['status'],
		'updated' => $data['updated'],
		'cards'   => array(),
	);

	if ( 'ok' !== $data['status'] ) {
		return $view;
	}

	$builder = 'nevada' === $state ? 'ltt_dive_in_build_nevada_road_card' : 'ltt_dive_in_build_california_road_card';

	foreach ( ltt_dive_in_filter_highways_by_state( $highways, $state ) as $highway ) {
		$view['cards'][] = call_user_func( $builder, $highway, $data['sources'], $area );
	}

	return $view;
}

/**
 * Card fields shared by every state.
 *
 * @param array  $highway Highway.
 * @param string $state   State slug; Nevada prefers its own route sign when one is set.
 * @return array
 */
function ltt_dive_in_road_card_base( $highway, $state ) {
	return array(
		'route'       => $highway['route'],
		'sign'        => 'nevada' === $state && $highway['sign_nevada'] ? $highway['sign_nevada'] : $highway['sign'],
		'slug'        => $highway['slug'],
		'shield'      => $highway['shield'],
		'number'      => $highway['number'],
		'name'        => $highway['name'],
		'subtitle'    => $highway['subtitle'],
		'status'      => 'none',
		'entries'     => array(),
		'checkpoints' => array(),
		'summary'     => '',
	);
}

/**
 * Items of a source that belong to a route.
 *
 * @param array  $sources Sources from ltt_dive_in_get_road_conditions().
 * @param string $source  Source ID.
 * @param string $route   Canonical route.
 * @return array
 */
function ltt_dive_in_get_route_items( $sources, $source, $route ) {
	return array_values(
		array_filter(
			$sources[ $source ]['items'] ?? array(),
			static function ( $item ) use ( $route ) {
				return ( $item['route'] ?? '' ) === $route;
			}
		)
	);
}

/**
 * Classify a Caltrans chain-control code.
 *
 * @param string $code Status code such as `R-2` or `ROAD CLOSED`.
 * @return array{status: string, label: string} Card status and the short row value.
 */
function ltt_dive_in_classify_caltrans_chain_status( $code ) {
	$code = strtoupper( trim( (string) $code ) );

	if ( 'R-0' === $code ) {
		return array(
			'status' => 'clear',
			'label'  => __( 'No chain controls', 'ltt-dive-in' ),
		);
	}

	if ( preg_match( '/^R-?([123])( MODIFIED)?$/', $code, $matches ) ) {
		return array(
			'status' => 'chains',
			/* translators: %s: chain-control requirement code, such as R-2. */
			'label'  => sprintf( __( '%s chain controls', 'ltt-dive-in' ), 'R-' . $matches[1] . ( empty( $matches[2] ) ? '' : ' ' . __( 'modified', 'ltt-dive-in' ) ) ),
		);
	}

	if ( in_array( $code, array( 'ROAD CLOSED', 'RC' ), true ) ) {
		return array(
			'status' => 'closed',
			'label'  => __( 'Road closed', 'ltt-dive-in' ),
		);
	}

	if ( '' === $code || 'NOT REPORTED' === $code ) {
		return array(
			'status' => 'none',
			'label'  => __( 'No report', 'ltt-dive-in' ),
		);
	}

	return array(
		'status' => 'advisory',
		'label'  => __( 'Advisory', 'ltt-dive-in' ),
	);
}

/**
 * Pick the more severe of two card statuses.
 *
 * @param string $current   Current status.
 * @param string $candidate Candidate status.
 * @return string
 */
function ltt_dive_in_worst_road_status( $current, $candidate ) {
	$order = array_keys( ltt_dive_in_get_road_status_labels() );
	$a     = array_search( $current, $order, true );
	$b     = array_search( $candidate, $order, true );

	return false !== $b && ( false === $a || $b < $a ) ? $candidate : $current;
}

/**
 * Clean a Caltrans checkpoint name for display.
 *
 * Removes operator notes such as “(Holding only)” and title-cases names sent in capitals.
 *
 * @param string $name Location name.
 * @return string
 */
function ltt_dive_in_clean_road_location_name( $name ) {
	$name = trim( preg_replace( '/\s*,?\s*\((?:[^)]*holding only|unselect[^)]*)\)/i', '', (string) $name ) );

	if ( $name === strtoupper( $name ) ) {
		$name = ucwords( strtolower( $name ), " -/(" );
		$name = preg_replace_callback(
			'/\b(Mt|Rd|Wo|Alp|Sr|Us|I)\b/',
			static function ( $match ) {
				$keep = array(
					'Wo'  => 'W/O',
					'Alp' => 'ALP',
					'Sr'  => 'SR',
					'Us'  => 'US',
					'I'   => 'I',
				);

				return $keep[ $match[1] ] ?? $match[1];
			},
			$name
		);
	}

	return $name;
}

/**
 * Describe a closure's direction of travel.
 *
 * @param string[] $directions Caltrans directions, such as `North` or `East / West`.
 * @return string
 */
function ltt_dive_in_describe_road_directions( $directions ) {
	$bounds = array();

	foreach ( $directions as $direction ) {
		foreach ( preg_split( '/\s*\/\s*/', (string) $direction ) as $part ) {
			$part = ucfirst( strtolower( trim( $part ) ) );

			if ( in_array( $part, array( 'North', 'South', 'East', 'West' ), true ) ) {
				$bounds[ $part ] = $part;
			}
		}
	}

	if ( count( $bounds ) > 1 ) {
		return __( 'Both directions', 'ltt-dive-in' );
	}

	$labels = array(
		'North' => __( 'Northbound', 'ltt-dive-in' ),
		'South' => __( 'Southbound', 'ltt-dive-in' ),
		'East'  => __( 'Eastbound', 'ltt-dive-in' ),
		'West'  => __( 'Westbound', 'ltt-dive-in' ),
	);

	return $bounds ? $labels[ reset( $bounds ) ] : '';
}

/**
 * Format a closure end time relative to today.
 *
 * @param int $timestamp End timestamp, or 0 when indefinite.
 * @return string
 */
function ltt_dive_in_format_road_end_time( $timestamp ) {
	if ( ! $timestamp ) {
		return __( 'Until further notice.', 'ltt-dive-in' );
	}

	$format = wp_date( 'Y-m-d', $timestamp ) === wp_date( 'Y-m-d' ) ? get_option( 'time_format' ) : 'M j, ' . get_option( 'time_format' );

	/* translators: %s: end time, such as 5:00 pm or Nov 4, 5:00 pm. */
	return sprintf( __( 'Until %s.', 'ltt-dive-in' ), wp_date( $format, $timestamp ) );
}

/**
 * Build one display entry for a group of matching Caltrans closures.
 *
 * @param array $closures Closures with the same location, type, and work.
 * @return array{title: string, text: string, muted: bool, points: array, full: bool}
 */
function ltt_dive_in_build_caltrans_closure_entry( $closures ) {
	$first = $closures[0];
	$place = $first['from'] && $first['to'] && $first['from'] !== $first['to']
		/* translators: 1: starting location, 2: ending location. */
		? sprintf( __( '%1$s to %2$s', 'ltt-dive-in' ), $first['from'], $first['to'] )
		: ( $first['from'] ? $first['from'] : $first['to'] );
	$parts = array_filter(
		array(
			ltt_dive_in_describe_road_directions( wp_list_pluck( $closures, 'direction' ) ),
			$place,
			/* translators: %s: county name. */
			$first['county'] ? sprintf( __( '%s Co', 'ltt-dive-in' ), $first['county'] ) : '',
		)
	);
	$types = array(
		'lane'              => __( 'Lane closure', 'ltt-dive-in' ),
		'full'              => __( 'Full closure', 'ltt-dive-in' ),
		'one-way traffic'   => __( 'One-way traffic control', 'ltt-dive-in' ),
		'alternating lanes' => __( 'Alternating lane closure', 'ltt-dive-in' ),
	);
	$type  = $types[ strtolower( $first['type'] ) ] ?? ( $first['type'] ? $first['type'] : __( 'Closure', 'ltt-dive-in' ) );

	if ( false !== stripos( $first['facility'], 'ramp' ) ) {
		/* translators: 1: closure type, 2: ramp type, such as Off Ramp. */
		$type = sprintf( __( '%1$s on the %2$s', 'ltt-dive-in' ), $type, strtolower( $first['facility'] ) );
	}

	/* translators: 1: closure type, 2: type of work, such as drainage work. */
	$sentences = array( $first['work'] ? sprintf( __( '%1$s for %2$s.', 'ltt-dive-in' ), $type, strtolower( $first['work'] ) ) : $type . '.' );
	$lanes     = preg_match_all( '/\b\d+\b/', $first['lanes_closed'] );

	if ( $lanes && is_numeric( $first['total_lanes'] ) && 'full' !== strtolower( $first['type'] ) ) {
		/* translators: 1: lanes closed, 2: total lanes. */
		$sentences[] = sprintf( _n( '%1$d of %2$d lane closed.', '%1$d of %2$d lanes closed.', (int) $first['total_lanes'], 'ltt-dive-in' ), $lanes, (int) $first['total_lanes'] );
	}

	$ends_at     = array_map( 'intval', wp_list_pluck( $closures, 'ends_at' ) );
	$sentences[] = ltt_dive_in_format_road_end_time( in_array( 0, $ends_at, true ) ? 0 : max( $ends_at ) );

	if ( $first['delay'] ) {
		/* translators: %d: delay in minutes. */
		$sentences[] = sprintf( _n( 'Expect delays of up to %d minute.', 'Expect delays of up to %d minutes.', $first['delay'], 'ltt-dive-in' ), $first['delay'] );
	}

	return array(
		'title'  => implode( ' · ', $parts ),
		'text'   => implode( ' ', $sentences ),
		'muted'  => false,
		'points' => array_merge( ...wp_list_pluck( $closures, 'points' ) ),
		'full'   => 'full' === strtolower( $first['type'] ) && false === stripos( $first['facility'], 'ramp' ),
	);
}

/**
 * Build a California highway card.
 *
 * @param array $highway Highway.
 * @param array $sources Sources from ltt_dive_in_get_road_conditions().
 * @param array $area    Area.
 * @return array
 */
function ltt_dive_in_build_california_road_card( $highway, $sources, $area ) {
	$card     = ltt_dive_in_road_card_base( $highway, 'california' );
	$route    = $highway['route'];
	$controls = ltt_dive_in_get_route_items( $sources, 'ca_chain_controls', $route );
	$closures = ltt_dive_in_get_route_items( $sources, 'ca_closures', $route );

	// Chain-control checkpoints, one row per place (both directions merged), west to east by milepost.
	$rows = array();

	foreach ( $controls as $control ) {
		$name           = ltt_dive_in_clean_road_location_name( $control['location'] );
		$key            = strtolower( $name );
		$classification = ltt_dive_in_classify_caltrans_chain_status( $control['status'] );

		if ( ! isset( $rows[ $key ] ) || ltt_dive_in_worst_road_status( $rows[ $key ]['status'], $classification['status'] ) !== $rows[ $key ]['status'] ) {
			$rows[ $key ] = array(
				'label'    => $name,
				'value'    => $classification['label'],
				'detail'   => 'clear' === $classification['status'] || 'none' === $classification['status'] ? '' : $control['description'],
				'status'   => $classification['status'],
				'milepost' => $control['milepost'] ?? $rows[ $key ]['milepost'] ?? null,
			);
		}

		$card['status'] = 'none' === $card['status'] ? $classification['status'] : ltt_dive_in_worst_road_status( $card['status'], $classification['status'] );
	}

	uasort(
		$rows,
		static function ( $a, $b ) {
			return ( $a['milepost'] ?? PHP_INT_MAX ) <=> ( $b['milepost'] ?? PHP_INT_MAX );
		}
	);

	if ( $rows ) {
		$restricted = count(
			array_filter(
				$rows,
				static function ( $row ) {
					return in_array( $row['status'], array( 'closed', 'chains', 'advisory' ), true );
				}
			)
		);

		$card['summary']     = $restricted
			/* translators: 1: number of checkpoints, 2: number with restrictions. */
			? sprintf( _n( '%1$d checkpoint reporting · %2$d with restrictions', '%1$d checkpoints reporting · %2$d with restrictions', count( $rows ), 'ltt-dive-in' ), count( $rows ), $restricted )
			/* translators: %d: number of checkpoints. */
			: sprintf( _n( '%d checkpoint reporting · no chain controls', '%d checkpoints reporting · no chain controls', count( $rows ), 'ltt-dive-in' ), count( $rows ) );
		$card['checkpoints'] = array_values( $rows );
	}

	// Closures in effect, grouped so both directions of one work zone read as one entry.
	$groups = array();

	foreach ( $closures as $closure ) {
		$ends = array( strtolower( $closure['from'] ), strtolower( $closure['to'] ) );
		sort( $ends );
		$groups[ implode( '|', array_merge( $ends, array( strtolower( $closure['type'] ), strtolower( $closure['work'] ), strtolower( $closure['facility'] ) ) ) ) ][] = $closure;
	}

	foreach ( $groups as $group ) {
		$entry           = ltt_dive_in_build_caltrans_closure_entry( $group );
		$card['status']  = ltt_dive_in_worst_road_status( 'none' === $card['status'] ? 'clear' : $card['status'], $entry['full'] ? 'closed' : 'advisory' );
		$card['entries'] = array_merge( $card['entries'], array( $entry ) );
	}

	$card = ltt_dive_in_add_road_card_empty_entry( $card, $sources, 'ca_closures', $area );

	return ltt_dive_in_attach_road_cameras( $card, $highway, $sources['ca_cameras']['items'] ?? array() );
}

/**
 * Classify a Nevada 511 road-condition status.
 *
 * @param string   $status    Overall status, such as `No Report` or `Road Closed`.
 * @param string[] $secondary Secondary conditions.
 * @return array{status: string, label: string} Card status and the short row value.
 */
function ltt_dive_in_classify_nv511_condition( $status, $secondary ) {
	$status = trim( (string) $status );
	$text   = strtolower( $status . ' ' . implode( ' ', $secondary ) );

	if ( '' === $status || 'no report' === strtolower( $status ) ) {
		$result = 'none';
	} elseif ( preg_match( '/\bclosed\b/', strtolower( $status ) ) && ! preg_match( '/\b(vehicles|trucks|high[- ]profile|trailers)\b/', strtolower( $status ) ) ) {
		$result = 'closed';
	} elseif ( preg_match( '/\b(chains?|snow tires)\b/', $text ) ) {
		$result = 'chains';
	} elseif ( in_array( strtolower( $status ), array( 'dry', 'clear', 'normal', 'good', 'seasonal' ), true ) ) {
		$result = 'clear';
	} else {
		$result = 'advisory';
	}

	return array(
		'status' => $result,
		'label'  => 'none' === $result ? __( 'No report', 'ltt-dive-in' ) : $status,
	);
}

/**
 * Build one display entry for a Nevada 511 traffic event from its generated description.
 *
 * @param array $event Normalized event.
 * @return array{title: string, text: string, muted: bool, points: array, full: bool}
 */
function ltt_dive_in_build_nv511_event_entry( $event ) {
	$description = $event['description'];
	$ramp        = preg_match( '/\bon (ramp from .+? to .+?)\./i', $description, $ramp_match );
	$place       = '';

	if ( $ramp ) {
		$place = ucfirst( $ramp_match[1] );
	} elseif ( preg_match( '/\bfrom (.+?) to (.+?)\./', $description, $matches ) ) {
		/* translators: 1: starting location, 2: ending location. */
		$place = sprintf( __( '%1$s to %2$s', 'ltt-dive-in' ), $matches[1], $matches[2] );
	} elseif ( preg_match( '/\bNear (.+?)\./', $description, $matches ) ) {
		/* translators: %s: location. */
		$place = sprintf( __( 'Near %s', 'ltt-dive-in' ), $matches[1] );
	}

	$direction = 'both' === strtolower( $event['direction'] ) ? __( 'Both directions', 'ltt-dive-in' ) : ltt_dive_in_describe_road_directions( array( $event['direction'] ) );
	$title     = implode( ' · ', array_filter( array( $ramp ? '' : $direction, $place ) ) );
	$sentences = array();

	if ( preg_match( '/^(?:(?:minor|moderate|major|serious)\s+)?(.+?)\s+on\s+/i', $description, $matches ) ) {
		$sentences[] = ucfirst( strtolower( $matches[1] ) ) . '.';
	}

	if ( $event['lanes'] && 'no data' !== strtolower( $event['lanes'] ) ) {
		$sentences[] = ucfirst( strtolower( str_replace( ' - ', '; ', $event['lanes'] ) ) ) . '.';
	}

	$sentences[] = ltt_dive_in_format_road_end_time( $event['ends_at'] );

	if ( preg_match( '/Public Details:\s*(.+)$/is', $description, $matches ) || preg_match( '/End time:[^.]*?[AP]M\.\s*(.+)$/is', $description, $matches ) ) {
		$details = trim( preg_replace( array( '/\s*\.(?:\s*\.)+/', '/\s+/' ), array( '.', ' ' ), $matches[1] ) );

		if ( '' !== $details ) {
			$sentences[] = preg_match( '/[.!?]$/', $details ) ? $details : $details . '.';
		}
	}

	return array(
		'title'  => '' !== $title ? $title : __( 'Traffic event', 'ltt-dive-in' ),
		'text'   => implode( ' ', $sentences ),
		'muted'  => false,
		'points' => $event['points'],
		'full'   => $event['full_closure'] && ! $ramp,
	);
}

/**
 * Build a Nevada highway card.
 *
 * Road-condition segments become the rows; traffic events in effect become the entries.
 *
 * @param array $highway Highway.
 * @param array $sources Sources from ltt_dive_in_get_road_conditions().
 * @param array $area    Area.
 * @return array
 */
function ltt_dive_in_build_nevada_road_card( $highway, $sources, $area ) {
	$card           = ltt_dive_in_road_card_base( $highway, 'nevada' );
	$card['status'] = 'clear';
	$route          = $highway['route'];
	$rows           = array();

	foreach ( ltt_dive_in_get_route_items( $sources, 'nv_conditions', $route ) as $segment ) {
		$classification = ltt_dive_in_classify_nv511_condition( $segment['status'], $segment['secondary'] );
		$label          = $segment['location'] ? $segment['location'] : $segment['area'];
		$key            = strtolower( $label );

		if ( '' === $label || ( isset( $rows[ $key ] ) && ltt_dive_in_worst_road_status( $rows[ $key ]['status'], $classification['status'] ) === $rows[ $key ]['status'] ) ) {
			continue;
		}

		$rows[ $key ] = array(
			'label'  => $label,
			'value'  => $classification['label'],
			'detail' => implode( ' · ', $segment['secondary'] ),
			'status' => $classification['status'],
		);

		if ( 'none' !== $classification['status'] ) {
			$card['status'] = ltt_dive_in_worst_road_status( $card['status'], $classification['status'] );
		}
	}

	if ( $rows ) {
		$statuses   = wp_list_pluck( $rows, 'status' );
		$count      = count( $rows );
		$restricted = count( array_intersect( $statuses, array( 'closed', 'chains', 'advisory' ) ) );

		if ( $restricted ) {
			/* translators: 1: number of road segments, 2: number with restrictions. */
			$card['summary'] = sprintf( _n( '%1$d segment reporting · %2$d with restrictions', '%1$d segments reporting · %2$d with restrictions', $count, 'ltt-dive-in' ), $count, $restricted );
		} elseif ( array_diff( $statuses, array( 'none' ) ) ) {
			/* translators: %d: number of road segments. */
			$card['summary'] = sprintf( _n( '%d segment reporting · no restrictions', '%d segments reporting · no restrictions', $count, 'ltt-dive-in' ), $count );
		} else {
			/* translators: %d: number of road segments. */
			$card['summary'] = sprintf( _n( '%d segment reporting · no reports', '%d segments reporting · no reports', $count, 'ltt-dive-in' ), $count );
		}

		$card['checkpoints'] = array_values( $rows );
	}

	foreach ( ltt_dive_in_get_route_items( $sources, 'nv_events', $route ) as $event ) {
		$entry           = ltt_dive_in_build_nv511_event_entry( $event );
		$card['status']  = ltt_dive_in_worst_road_status( $card['status'], $entry['full'] ? 'closed' : 'advisory' );
		$card['entries'] = array_merge( $card['entries'], array( $entry ) );
	}

	$card = ltt_dive_in_add_road_card_empty_entry( $card, $sources, 'nv_events', $area );

	return ltt_dive_in_attach_road_cameras( $card, $highway, $sources['nv_cameras']['items'] ?? array() );
}

/**
 * Add the placeholder entry to a card without reports.
 *
 * When the closure or event source failed, the card cannot claim No Restrictions.
 *
 * @param array  $card    Card.
 * @param array  $sources Sources from ltt_dive_in_get_road_conditions().
 * @param string $source  Source ID of the closures or events feed.
 * @param array  $area    Area.
 * @return array
 */
function ltt_dive_in_add_road_card_empty_entry( $card, $sources, $source, $area ) {
	$available = 'ok' === ( $sources[ $source ]['status'] ?? '' );

	if ( ! $available && 'clear' === $card['status'] ) {
		$card['status'] = 'none';
	}

	if ( ! $card['entries'] ) {
		$card['entries'][] = array(
			'title'  => __( 'Closures and lane restrictions', 'ltt-dive-in' ),
			'text'   => $available ? __( 'No traffic restrictions are reported for this area.', 'ltt-dive-in' ) : __( 'Closure and lane-restriction reports are not available right now.', 'ltt-dive-in' ),
			'muted'  => true,
			'points' => array( array( $area['latitude'], $area['longitude'] ) ),
			'full'   => false,
		);
	}

	return $card;
}

/**
 * Give report entries the nearest camera from the highway's camera pool.
 *
 * Automatic uses every camera on the highway inside the area; Choose uses the
 * editor's cameras; None shows no images. A camera is used once per card and
 * only when it is within three miles of the report (the first entry of a card
 * without reports takes the nearest camera to the area center at any distance).
 *
 * @param array $card    Card.
 * @param array $highway Highway.
 * @param array $cameras Cached cameras for the state, already limited to the area.
 * @return array
 */
function ltt_dive_in_attach_road_cameras( $card, $highway, $cameras ) {
	if ( 'none' === $highway['camera_mode'] ) {
		return $card;
	}

	$pool = array_values(
		array_filter(
			$cameras,
			static function ( $camera ) use ( $highway ) {
				return 'manual' === $highway['camera_mode']
					? in_array( $camera['id'], $highway['cameras'], true )
					: $camera['route'] === $highway['route'];
			}
		)
	);
	$used = array();

	foreach ( $card['entries'] as $index => $entry ) {
		$best      = null;
		$best_dist = $entry['muted'] ? INF : 3.0;

		foreach ( $pool as $camera ) {
			if ( isset( $used[ $camera['id'] ] ) ) {
				continue;
			}

			foreach ( $entry['points'] as $point ) {
				$miles = ltt_dive_in_get_distance_miles( $point[0], $point[1], $camera['points'][0][0], $camera['points'][0][1] );

				if ( $miles <= $best_dist ) {
					$best      = $camera;
					$best_dist = $miles;
				}
			}
		}

		if ( $best ) {
			$used[ $best['id'] ]                = true;
			$card['entries'][ $index ]['camera'] = array(
				'image' => $best['image'],
				'alt'   => $best['nearby']
					/* translators: 1: camera location, 2: nearby town. */
					? sprintf( __( 'Live traffic camera: %1$s, near %2$s', 'ltt-dive-in' ), $best['name'], $best['nearby'] )
					/* translators: %s: camera location. */
					: sprintf( __( 'Live traffic camera: %s', 'ltt-dive-in' ), $best['name'] ),
			);
		}
	}

	return $card;
}

/**
 * Offer cameras inside the roads tab area as choices for “Chosen cameras”.
 *
 * Choices come from the cached provider data, so the list fills after the
 * first background refresh. Saved cameras that are no longer reported stay
 * listed so an editor can see and remove them.
 *
 * @param array $field Select field.
 * @return array
 */
function ltt_dive_in_load_road_camera_choices( $field ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only editor context.
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : ltt_dive_in_get_roads_tab_page_id();
	$area    = ltt_dive_in_get_road_conditions_area( $post_id );
	$choices = array();

	foreach ( array( 'ca_cameras', 'nv_cameras' ) as $source ) {
		$cached = get_transient( ltt_dive_in_road_source_cache_key( $source ) );

		foreach ( ltt_dive_in_filter_road_items( is_array( $cached ) ? ( $cached['items'] ?? array() ) : array(), $area ) as $camera ) {
			$choices[ $camera['id'] ] = trim( ( $camera['route'] ? $camera['route'] . ' · ' : '' ) . $camera['name'] . ( $camera['nearby'] ? ' (' . $camera['nearby'] . ')' : '' ) );
		}
	}

	asort( $choices, SORT_NATURAL | SORT_FLAG_CASE );

	foreach ( (array) ( $field['value'] ?? array() ) as $saved ) {
		if ( is_string( $saved ) && '' !== $saved && ! isset( $choices[ $saved ] ) ) {
			/* translators: %s: camera ID. */
			$choices[ $saved ] = sprintf( __( 'Camera no longer reported (%s)', 'ltt-dive-in' ), $saved );
		}
	}

	$field['choices'] = $choices;

	return $field;
}
add_filter( 'acf/prepare_field/key=field_ltt_dive_in_roads_highway_cameras', 'ltt_dive_in_load_road_camera_choices' );

/**
 * Markup for an editor-chosen route sign, or an empty string to use the generic sign.
 *
 * Decorative: the card heading names the highway. SVG attachments, which
 * wp_get_attachment_image() does not render, are printed as a plain image.
 *
 * @param int $attachment_id Attachment ID.
 * @return string
 */
function ltt_dive_in_get_road_sign_markup( $attachment_id ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
		return '';
	}

	$attributes = array(
		'class'    => 'road-shield road-shield--custom',
		'alt'      => '',
		'loading'  => 'lazy',
		'decoding' => 'async',
		'sizes'    => '(max-width: 767.98px) 30px, 62px',
	);
	$markup     = wp_get_attachment_image( $attachment_id, 'medium', false, $attributes );

	if ( $markup ) {
		return $markup;
	}

	$url = wp_get_attachment_url( $attachment_id );

	if ( ! $url || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return '';
	}

	return sprintf(
		'<img class="%1$s" src="%2$s" alt="" width="62" height="62" loading="lazy" decoding="async" />',
		esc_attr( $attributes['class'] ),
		esc_url( $url )
	);
}
