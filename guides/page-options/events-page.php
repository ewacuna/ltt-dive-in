<?php
/**
 * Site Guide: Events page template.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Events page', 'ltt-dive-in' ),
	'group'           => 'templates',
	'order'           => 20,
	'icon'            => 'calendar-alt',
	'where'           => __( 'Page editor, "Events Page" box below the title, on the page using the Events template', 'ltt-dive-in' ),
	'applies'         => __( 'The page that uses the Events template.', 'ltt-dive-in' ),
	'short'           => __( 'The main events listing and each event\'s detail page.', 'ltt-dive-in' ),
	'summary'         => __( 'The Events template shows every upcoming event from Seeker with filters and a search, and it also creates a detail page for each event. The page title is used as its main heading. The events themselves are managed in Seeker.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The Events listing page.', 'ltt-dive-in' ),
		__( 'Every event detail page. Any Up Driver or End Page Cluster blocks you add to the Events page content also appear at the bottom of each event detail page. Other blocks only show on the listing.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Tag', 'ltt-dive-in' ),
			'description' => __( 'Optional small label above the title. Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Submit link', 'ltt-dive-in' ),
			'description' => __( 'Optional button below the filters, usually to the event submission page, like "Submit an event".', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Event detail pages live under the Events page address, so changing that page\'s slug changes every event link.', 'ltt-dive-in' ),
		__( 'If events do not show, check Settings → Events in the Site Guide.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
