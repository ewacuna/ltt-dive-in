<?php
/**
 * Block Guide content for Event Driver.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-24',
	'summary'         => __( 'Shows upcoming events from the Seeker event calendar. You never type event details here: names, dates, photos, venues, and ticket links all come from Seeker and update on their own. This block only decides which events to show and how. It has three layouts: a big rotating feature, a filterable list, and a carousel.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Event Driver</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong>.', 'ltt-dive-in' ),
		__( 'Set which events to show. The preview in the editor pulls real events from Seeker, so you can check the result right away.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Choosing a layout', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Hero Featured Events, Listed Events, or Events Carousel. To change an event\'s details, edit it in Seeker, not here.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'hero'     => array(
			'name'        => __( 'Hero Featured Events', 'ltt-dive-in' ),
			'description' => __( 'A full-width slideshow, one event per slide, with the photo (softly blurred behind the text so posters do not compete with it), date, venue, organizer, and up to two buttons. Visitors move through it with arrows or dots. It does not move on its own.', 'ltt-dive-in' ),
			'best_for'    => __( 'The top of the Events page, or any page where a few big events deserve attention.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Featured events', 'ltt-dive-in' ),
					'description' => __( '<strong>Featured in Seeker</strong> uses the events marked as featured in Seeker, soonest first. <strong>Choose events</strong> lets you pick them yourself.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Number of slides', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Shown with Featured in Seeker. Between 3 and 6.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Events', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Shown with Choose events. Type part of an event name to search, then pick 3 to 6 events in the order you want.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Tickets button label', 'ltt-dive-in' ),
					'description' => __( 'The text on the ticket button. It starts as "Get Tickets". The button only appears when the event has a ticket link in Seeker. Up to 24 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Details button label', 'ltt-dive-in' ),
					'description' => __( 'The text on the button that opens the event page. It starts as "Learn More". Up to 24 characters.', 'ltt-dive-in' ),
				),
			),
		),
		'listed'   => array(
			'name'        => __( 'Listed Events', 'ltt-dive-in' ),
			'description' => __( 'A heading and a grid of event cards with filters on top: categories, dates (Upcoming, Today, This weekend, and so on), and a keyword search. A Load More button adds more cards. On phones the cards turn into a swipeable row.', 'ltt-dive-in' ),
			'best_for'    => __( 'A page where people browse and search events.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Limit to a month', 'ltt-dive-in' ),
					'description' => __( 'Turn this on to show only events that start in one month, like a "December events" page. Then pick the <strong>Month</strong> and <strong>Year</strong>. Leave it off to show everything upcoming.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Category tag', 'ltt-dive-in' ),
					'description' => __( 'A small label above the heading. Up to 40 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Heading', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'The section title. Up to 90 characters.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Introduction', 'ltt-dive-in' ),
					'description' => __( 'Up to three sentences under the heading.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Primary link / Secondary link', 'ltt-dive-in' ),
					'description' => __( 'Optional buttons next to the heading, like "Submit an event".', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Initial events', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'How many cards show before Load More. Between 3 and 12. Multiples of three fill the rows evenly on desktop.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Load More step', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'How many cards each Load More click adds. Between 3 and 12.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Category filter options', 'ltt-dive-in' ),
					'description' => __( 'The categories visitors can pick from. Leave it empty to offer every category.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Show Categories dropdown / Show date dropdown / Show keyword search', 'ltt-dive-in' ),
					'description' => __( 'Turn any of the three filters off if you do not need it.', 'ltt-dive-in' ),
				),
			),
		),
		'carousel' => array(
			'name'        => __( 'Events Carousel', 'ltt-dive-in' ),
			'description' => __( 'A heading and a row of event cards that visitors slide through. No filters.', 'ltt-dive-in' ),
			'best_for'    => __( 'A taste of what is coming up on pages that are not about events, like a town page or a blog post.', 'ltt-dive-in' ),
			'fields'      => array(
				array(
					'label'       => __( 'Limit to a month', 'ltt-dive-in' ),
					'description' => __( 'Same as in Listed Events.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Category tag, Heading, Introduction, links', 'ltt-dive-in' ),
					'description' => __( 'Same as in Listed Events. The Heading is required.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Number of events', 'ltt-dive-in' ),
					'required'    => true,
					'description' => __( 'Between 4 and 12.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Sort', 'ltt-dive-in' ),
					'description' => __( '<strong>Soonest first</strong> or <strong>Recently added</strong> to Seeker.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Featured events only', 'ltt-dive-in' ),
					'description' => __( 'Only show events marked as featured in Seeker.', 'ltt-dive-in' ),
				),
				array(
					'label'       => __( 'Limit to categories', 'ltt-dive-in' ),
					'description' => __( 'Optional. Only show events in these Seeker categories, like Music or Outdoors.', 'ltt-dive-in' ),
				),
			),
		),
	),
	'tips'            => array(
		__( 'Events are refreshed from Seeker about every 15 minutes. If you just changed something in Seeker, give it a few minutes.', 'ltt-dive-in' ),
		__( 'Event photos come from Seeker. If a card photo looks wrong, change it in Seeker.', 'ltt-dive-in' ),
		__( 'Cards link to the event\'s own page on this site, where visitors can see the full details and add it to their calendar.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Add a Seeker API key in Settings → Events to display events."', 'ltt-dive-in' ) => __( 'The connection to Seeker is not set up. An administrator needs to add the API key under Settings → Events.', 'ltt-dive-in' ),
		__( 'The editor says "Events could not be loaded from Seeker."', 'ltt-dive-in' ) => __( 'Seeker did not answer, or the key is wrong. Wait a few minutes and reload. If it keeps happening, check Settings → Events.', 'ltt-dive-in' ),
		__( 'The block is missing on the live page.', 'ltt-dive-in' ) => __( 'When there are no upcoming events that match the settings, the block hides itself so visitors do not see an empty section. Try a wider setting, like removing a category limit. A block limited to a month stays visible and says there are no events that month.', 'ltt-dive-in' ),
		__( 'The editor says "Choose 3 to 6 events to preview Hero Featured Events."', 'ltt-dive-in' ) => __( 'With Choose events, pick at least three events.', 'ltt-dive-in' ),
	),
);
