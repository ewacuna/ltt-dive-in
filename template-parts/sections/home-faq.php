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

$variant        = get_field( 'ltt_dive_in_home_faq_variant', $front_page_id );
$heading        = get_field( 'ltt_dive_in_home_faq_heading', $front_page_id );
$description    = get_field( 'ltt_dive_in_home_faq_description', $front_page_id );
$section_cta    = get_field( 'ltt_dive_in_home_faq_cta', $front_page_id );
$faq_rows       = get_field( 'ltt_dive_in_home_faq_items', $front_page_id );
$toggle_tagline = get_field( 'ltt_dive_in_home_faq_toggle_tagline', $front_page_id );
$toggle_rows    = get_field( 'ltt_dive_in_home_faq_toggle_topics', $front_page_id );
$variants       = array(
	'global'       => 'global',
	'toggle'       => 'toggle',
	'side_by_side' => 'side-by-side',
);

if ( ! is_string( $variant ) || ! isset( $variants[ $variant ] ) || ! $heading ) {
	return;
}

$faq_items = ltt_dive_in_prepare_accordion_items( $faq_rows );
$topics    = ltt_dive_in_prepare_accordion_topics( $toggle_rows );

if ( ( 'toggle' === $variant && count( $topics ) < 2 ) || ( 'toggle' !== $variant && ! $faq_items ) ) {
	return;
}

get_template_part(
	'template-parts/components/accordions/' . $variants[ $variant ],
	null,
	array(
		'id'          => 'home-faq',
		'heading'     => $heading,
		'description' => $description,
		'cta'         => is_array( $section_cta ) ? $section_cta : array(),
		'items'       => $faq_items,
		'tagline'     => is_string( $toggle_tagline ) ? trim( $toggle_tagline ) : '',
		'topics'      => $topics,
	)
);
