<?php
/**
 * Small reusable template helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print the published date.
 */
function ltt_dive_in_posted_on() {
	$time_string = sprintf(
		'<time class="entry-date published%1$s" datetime="%2$s">%3$s</time>',
		get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ? '' : ' updated',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);

	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string .= sprintf(
			'<time class="updated" datetime="%1$s">%2$s</time>',
			esc_attr( get_the_modified_date( DATE_W3C ) ),
			esc_html( get_the_modified_date() )
		);
	}

	printf(
		'<span class="posted-on">%1$s <a href="%2$s" rel="bookmark">%3$s</a></span>',
		esc_html__( 'Posted on', 'ltt-dive-in' ),
		esc_url( get_permalink() ),
		wp_kses_post( $time_string )
	);
}

/**
 * Print the post author.
 */
function ltt_dive_in_posted_by() {
	printf(
		'<span class="byline">%1$s <a class="url fn n" href="%2$s">%3$s</a></span>',
		esc_html__( 'by', 'ltt-dive-in' ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_html( get_the_author() )
	);
}

/**
 * Print the published month and year, e.g. "Mar 2024".
 */
function ltt_dive_in_posted_month() {
	printf(
		'<time class="entry-date published" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date( _x( 'M Y', 'post month date format', 'ltt-dive-in' ) ) )
	);
}

/**
 * Estimate the reading time of a post in whole minutes.
 *
 * @param int|WP_Post|null $post Optional. Post ID or object. Defaults to the current post.
 * @return int Minutes, at least 1.
 */
function ltt_dive_in_get_reading_time( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return 1;
	}

	/**
	 * Filter the reading speed used for read-time estimates.
	 *
	 * @param int $words_per_minute Average words read per minute.
	 */
	$words_per_minute = max( 1, (int) apply_filters( 'ltt_dive_in_reading_words_per_minute', 200 ) );
	$text             = wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) );
	$words            = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );

	return max( 1, (int) ceil( count( $words ) / $words_per_minute ) );
}

/**
 * Print the estimated reading time, e.g. "5 min read".
 */
function ltt_dive_in_reading_time() {
	$minutes = ltt_dive_in_get_reading_time();

	printf(
		'<span class="reading-time"><img src="%1$s" alt="" width="17" height="17"> %2$s</span>',
		esc_url( LTT_DIVE_IN_URI . '/assets/images/icons/read-time.svg' ),
		/* translators: %s: Estimated reading time in minutes. */
		esc_html( sprintf( _n( '%s min read', '%s min read', $minutes, 'ltt-dive-in' ), number_format_i18n( $minutes ) ) )
	);
}

/**
 * Print category and tag links.
 */
function ltt_dive_in_entry_footer() {
	$categories = get_the_category_list( esc_html__( ', ', 'ltt-dive-in' ) );
	$tags       = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'ltt-dive-in' ) );

	if ( $categories ) {
		printf( '<span class="cat-links">%1$s %2$s</span>', esc_html__( 'Filed under:', 'ltt-dive-in' ), wp_kses_post( $categories ) );
	}

	if ( $tags ) {
		printf( '<span class="tags-links">%1$s %2$s</span>', esc_html__( 'Tagged:', 'ltt-dive-in' ), wp_kses_post( $tags ) );
	}

	edit_post_link(
		esc_html__( 'Edit', 'ltt-dive-in' ),
		'<span class="edit-link">',
		'</span>'
	);
}

/**
 * Render the custom logo or a text site title.
 */
function ltt_dive_in_branding() {
	if ( has_custom_logo() ) {
		the_custom_logo();
	} else {
		printf(
			'<a class="site-title" href="%1$s" rel="home">%2$s</a>',
			esc_url( home_url( '/' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);
	}
}

/**
 * Prepare valid accordion items for a component template.
 *
 * @param mixed $rows Raw ACF repeater value.
 * @return array[]
 */
function ltt_dive_in_prepare_accordion_items( $rows ) {
	$items = array();

	if ( ! is_array( $rows ) ) {
		return $items;
	}

	foreach ( $rows as $row ) {
		$question = isset( $row['question'] ) && is_string( $row['question'] ) ? trim( $row['question'] ) : '';
		$answer   = isset( $row['answer'] ) && is_string( $row['answer'] ) ? trim( $row['answer'] ) : '';

		if ( ! $question || ! trim( wp_strip_all_tags( $answer ) ) ) {
			continue;
		}

		$items[] = array(
			'question' => $question,
			'answer'   => $answer,
			'link'     => isset( $row['link'] ) && is_array( $row['link'] ) ? $row['link'] : array(),
		);
	}

	return $items;
}

/**
 * Prepare topic groups for the toggle accordion variant.
 *
 * @param mixed $rows Raw ACF topic repeater value.
 * @return array[]
 */
function ltt_dive_in_prepare_accordion_topics( $rows ) {
	$topics = array();

	if ( ! is_array( $rows ) ) {
		return $topics;
	}

	foreach ( array_slice( $rows, 0, 4 ) as $row ) {
		$label = isset( $row['label'] ) && is_string( $row['label'] ) ? trim( $row['label'] ) : '';
		$items = ltt_dive_in_prepare_accordion_items( isset( $row['items'] ) ? $row['items'] : array() );

		if ( ! $label || ! $items ) {
			continue;
		}

		$topics[] = array(
			'label' => $label,
			'items' => $items,
		);
	}

	return $topics;
}

/**
 * Format an event's date and time for display.
 *
 * Dates are shown in the event's own timezone, which Seeker supplies.
 *
 * @param array $event Normalized Seeker event.
 * @return string
 */
function ltt_dive_in_format_event_date( $event ) {
	if ( empty( $event['start'] ) || ! $event['start'] instanceof DateTimeImmutable ) {
		return '';
	}

	$start    = $event['start'];
	$end      = isset( $event['end'] ) && $event['end'] instanceof DateTimeImmutable ? $event['end'] : null;
	$timezone = $start->getTimezone();
	$format   = static function ( $format, $date ) use ( $timezone ) {
		return wp_date( $format, $date->getTimestamp(), $timezone );
	};

	if ( $end && $end->format( 'Y-m-d' ) !== $start->format( 'Y-m-d' ) ) {
		$start_format = $end->format( 'Y' ) === $start->format( 'Y' ) ? __( 'M j', 'ltt-dive-in' ) : __( 'M j, Y', 'ltt-dive-in' );

		/* translators: 1: start date, 2: end date. */
		return sprintf( __( '%1$s – %2$s', 'ltt-dive-in' ), $format( $start_format, $start ), $format( __( 'M j, Y', 'ltt-dive-in' ), $end ) );
	}

	if ( ! empty( $event['all_day'] ) ) {
		return $format( __( 'D, M j, Y', 'ltt-dive-in' ), $start );
	}

	/* translators: 1: event date, 2: event start time. */
	return sprintf( __( '%1$s · %2$s', 'ltt-dive-in' ), $format( __( 'D, M j, Y', 'ltt-dive-in' ), $start ), $format( __( 'g:i A', 'ltt-dive-in' ), $start ) );
}

/**
 * Return the machine-readable start value for an event's `time` element.
 *
 * @param array $event Normalized Seeker event.
 * @return string
 */
function ltt_dive_in_get_event_datetime_attribute( $event ) {
	if ( empty( $event['start'] ) || ! $event['start'] instanceof DateTimeImmutable ) {
		return '';
	}

	return ! empty( $event['all_day'] ) ? $event['start']->format( 'Y-m-d' ) : $event['start']->format( DATE_W3C );
}

/**
 * Return decorative image markup for an event.
 *
 * Seeker images are remote, so the responsive attributes are built from the
 * renditions Seeker supplies instead of from the Media Library. The event title
 * is always visible next to the image, so the image is decorative.
 *
 * @param array  $event   Normalized Seeker event.
 * @param string $sizes   `sizes` attribute.
 * @param string $loading `lazy` or `eager`.
 * @return string
 */
function ltt_dive_in_get_event_image( $event, $sizes, $loading = 'lazy' ) {
	$image = isset( $event['image'] ) && is_array( $event['image'] ) ? $event['image'] : array();

	if ( empty( $image['src'] ) ) {
		return '';
	}

	return sprintf(
		'<img src="%1$s"%2$s sizes="%3$s"%4$s alt="" loading="%5$s" decoding="async"%6$s />',
		esc_url( $image['src'] ),
		! empty( $image['srcset'] ) ? ' srcset="' . esc_attr( $image['srcset'] ) . '"' : '',
		esc_attr( $sizes ),
		! empty( $image['width'] ) && ! empty( $image['height'] ) ? ' width="' . absint( $image['width'] ) . '" height="' . absint( $image['height'] ) . '"' : '',
		'eager' === $loading ? 'eager' : 'lazy',
		'eager' === $loading ? ' fetchpriority="high"' : ''
	);
}

/**
 * Return a Seeker event description shortened for cards and heroes.
 *
 * @param array $event Normalized Seeker event.
 * @param int   $words Maximum words.
 * @return string
 */
function ltt_dive_in_get_event_excerpt( $event, $words = 18 ) {
	return isset( $event['description'] ) && is_string( $event['description'] ) ? wp_trim_words( $event['description'], $words, '…' ) : '';
}
