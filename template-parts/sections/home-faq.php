<?php
/**
 * Homepage frequently asked questions.
 *
 * @package LTT_Dive_In
 */

$front_page_id = (int) get_queried_object_id();

if ( ! function_exists( 'get_field' ) ) {
	return;
}

$heading     = get_field( 'ltt_dive_in_home_faq_heading', $front_page_id );
$description = get_field( 'ltt_dive_in_home_faq_description', $front_page_id );
$section_cta = get_field( 'ltt_dive_in_home_faq_cta', $front_page_id );
$faq_rows    = get_field( 'ltt_dive_in_home_faq_items', $front_page_id );
$faq_items   = array();

if ( ! $heading || ! is_array( $faq_rows ) ) {
	return;
}

foreach ( array_slice( $faq_rows, 0, 5 ) as $faq_row ) {
	$question = isset( $faq_row['question'] ) && is_string( $faq_row['question'] ) ? trim( $faq_row['question'] ) : '';
	$answer   = isset( $faq_row['answer'] ) && is_string( $faq_row['answer'] ) ? trim( $faq_row['answer'] ) : '';

	if ( ! $question || ! trim( wp_strip_all_tags( $answer ) ) ) {
		continue;
	}

	$faq_items[] = array(
		'question' => $question,
		'answer'   => $answer,
		'link'     => isset( $faq_row['link'] ) && is_array( $faq_row['link'] ) ? $faq_row['link'] : array(),
	);
}

if ( ! $faq_items ) {
	return;
}

get_template_part(
	'template-parts/components/global',
	'accordion',
	array(
		'id'          => 'home-faq',
		'heading'     => $heading,
		'description' => $description,
		'cta'         => is_array( $section_cta ) ? $section_cta : array(),
		'items'       => $faq_items,
	)
);
