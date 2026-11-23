<?php
/**
 * Site Guide: Event Submission template.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Event submission page', 'ltt-dive-in' ),
	'group'           => 'templates',
	'order'           => 30,
	'icon'            => 'welcome-add-page',
	'where'           => __( 'Page editor, "Event Submission" box below the title, on the page using the Event Submission template', 'ltt-dive-in' ),
	'applies'         => __( 'The page that uses the Event Submission template.', 'ltt-dive-in' ),
	'short'           => __( 'The form where organizers send their events to the Seeker calendar.', 'ltt-dive-in' ),
	'summary'         => __( 'This page shows Seeker\'s event submission form, so organizers can send their events straight to the calendar for review. The form is provided by Seeker; this box only tells the page which form to load.', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Calendar ID', 'ltt-dive-in' ),
			'description' => __( 'Copy the <code>calendarId</code> value from the embed code Seeker gives you for the submission form.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Form ID', 'ltt-dive-in' ),
			'description' => __( 'Copy the <code>formId</code> value from the same embed code.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Both IDs are needed. If either one is empty, the form does not load.', 'ltt-dive-in' ),
		__( 'Submitted events go to Seeker. They appear on the site once they are published there.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The form does not appear.', 'ltt-dive-in' ) => __( 'Check that both the Calendar ID and Form ID are filled in and match the Seeker embed code exactly, with no extra spaces.', 'ltt-dive-in' ),
	),
);
