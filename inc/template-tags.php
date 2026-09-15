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
