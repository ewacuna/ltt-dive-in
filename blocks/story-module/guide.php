<?php
/**
 * Block Guide content for Story Module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'The building blocks for blog articles: numbered list items, travel tips, itineraries, guides, Q&As, comparison tables, and more. Each Story Module is one piece of an article. You stack several of them, mixed with normal paragraphs and headings, to build the post.', 'ltt-dive-in' ),
	'where'           => __( 'Blog posts only. It does not appear in the inserter on pages.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'In a blog post, click the + button, open <strong>LTT Blocks</strong>, and add <strong>Story Module</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Variant</strong>. Only the fields for that variant are shown.', 'ltt-dive-in' ),
		__( 'Fill it in. For lists like a round-up or an itinerary, add one Story Module per item, one after the other.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Choosing a variant', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Variant', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'What kind of piece this is. The variants are grouped below by the type of article they are made for.', 'ltt-dive-in' ),
		),
	),
	'variants_intro'  => __( 'Rich text fields (Content, Answer) let you use bold, italic, lists, and links. Images always use the alt text saved in the Media Library, so set it there.', 'ltt-dive-in' ),
	'variants'        => array(
		'listicle'         => array(
			'name'        => __( 'Listicle', 'ltt-dive-in' ),
			'description' => __( 'One numbered item of a round-up, like "3. Lone Eagle Grille". A big number, a title, text, and one to four photos. Add one Listicle per item.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Number', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The item\'s position in the list.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The item name. Up to 100 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Subtitle', 'ltt-dive-in' ), 'description' => __( 'A line under the title, often the location. Up to 150 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The description of the item.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Images', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 4 photos. They are arranged in a square: one photo, two side by side, two over one, or four in a grid.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Image position', 'ltt-dive-in' ), 'description' => __( 'Right or Left on desktop. Alternating sides from item to item makes a long list easier to read. On phones the photos always go under the text.', 'ltt-dive-in' ) ),
			),
		),
		'numbered_heading' => array(
			'name'        => __( 'Numbered Heading', 'ltt-dive-in' ),
			'description' => __( 'Opens a numbered section of a round-up, with a big number, a title, and an optional intro. Use it when each numbered section has more content under it.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Number', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 99.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The section title. Up to 150 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Introduction', 'ltt-dive-in' ), 'description' => __( 'A short paragraph. Up to 600 characters.', 'ltt-dive-in' ) ),
			),
		),
		'event'            => array(
			'name'        => __( 'Event', 'ltt-dive-in' ),
			'description' => __( 'One event written by hand, with title, date, location, text, and an optional photo. Good for event round-ups. Unlike the Event Driver, this does not come from the event calendar, so it will not update or disappear on its own.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The event name. Up to 100 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Date', 'ltt-dive-in' ), 'description' => __( 'Written however you like, like "December 6 to 8". Up to 100 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Location', 'ltt-dive-in' ), 'description' => __( 'Where it happens. Up to 150 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'description' => __( 'A description of the event.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Image', 'ltt-dive-in' ), 'description' => __( 'An optional photo next to the text.', 'ltt-dive-in' ) ),
			),
		),
		'side_by_side'     => array(
			'name'        => __( 'Side by Side', 'ltt-dive-in' ),
			'description' => __( 'Text next to one big photo, with an optional button. Bullet lists in the text get the mountain icon bullets.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Eyebrow', 'ltt-dive-in' ), 'description' => __( 'A small label above the title. Up to 60 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Up to 120 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The text.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Button', 'ltt-dive-in' ), 'description' => __( 'An optional button under the text.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Image', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The photo.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Image position', 'ltt-dive-in' ), 'description' => __( 'Right or Left on desktop. On phones the photo goes under the text.', 'ltt-dive-in' ) ),
			),
		),
		'travel_tip'       => array(
			'name'        => __( 'Responsible Travel Tip', 'ltt-dive-in' ),
			'description' => __( 'A green box that stands out from the article, for a tip about traveling responsibly (Leave No Trace, wildlife, fire safety, and so on).', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Label', 'ltt-dive-in' ), 'description' => __( 'The small label at the top. Leave it empty to use "Responsible Travel Tip". Up to 40 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The tip in a few words. Up to 100 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The tip itself. Keep it short.', 'ltt-dive-in' ) ),
			),
		),
		'explainer'        => array(
			'name'        => __( 'Explainer Q&A', 'ltt-dive-in' ),
			'description' => __( 'One question and its answer, always visible (not collapsed). For explainer articles like "Flying into Tahoe". Add one per question.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Question', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Up to 150 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Answer', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The answer. Lists work well here.', 'ltt-dive-in' ) ),
			),
		),
		'itinerary_day'    => array(
			'name'        => __( 'Itinerary Day', 'ltt-dive-in' ),
			'description' => __( 'The header that opens each day of an itinerary, like "Day 1: Arrive on the North Shore". Follow it with Itinerary Items.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Day number', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 99.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'What the day is about. Up to 120 characters.', 'ltt-dive-in' ) ),
			),
		),
		'itinerary_item'   => array(
			'name'        => __( 'Itinerary Item', 'ltt-dive-in' ),
			'description' => __( 'One part of the day (Morning, Midday, Afternoon, or Evening) with one to four stops and an optional photo.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Time of day', 'ltt-dive-in' ), 'description' => __( 'Morning, Midday, Afternoon, or Evening. Each has its own color.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Stops', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 4 stops. Each has a <strong>Title</strong>, where you can link part of the text (like the restaurant name) with the link button, and optional <strong>Content</strong>.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Image', 'ltt-dive-in' ), 'description' => __( 'An optional photo on the right.', 'ltt-dive-in' ) ),
			),
		),
		'numbers_guide'    => array(
			'name'        => __( 'Numbers Guide', 'ltt-dive-in' ),
			'description' => __( 'A guide section with a title, text, a numbered list, and optional photos. Two or more photos become a small slider.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Up to 120 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'description' => __( 'Text before the list.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'List heading', 'ltt-dive-in' ), 'description' => __( 'A short title above the list. Up to 80 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'List items', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 6 items, up to 80 characters each. They are numbered in this order.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Images', 'ltt-dive-in' ), 'description' => __( 'Up to 6 photos.', 'ltt-dive-in' ) ),
			),
		),
		'bullet_guide'     => array(
			'name'        => __( 'Bullet Guide', 'ltt-dive-in' ),
			'description' => __( 'The same as the Numbers Guide, but the list uses mountain icon bullets instead of numbers. Use it when the order does not matter.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Title, Content, List heading, List items, Images', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Same as the Numbers Guide. Title and at least one list item are required.', 'ltt-dive-in' ) ),
			),
		),
		'guide_1up'        => array(
			'name'        => __( 'Guide 1-Up', 'ltt-dive-in' ),
			'description' => __( 'A guide section with just a title, text, and optional photos. No list.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Up to 120 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The text.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Images', 'ltt-dive-in' ), 'description' => __( 'Up to 6 photos. Two or more become a slider.', 'ltt-dive-in' ) ),
			),
		),
		'comparison_table' => array(
			'name'        => __( 'Comparison Table', 'ltt-dive-in' ),
			'description' => __( 'A table that compares things across up to seven columns, using check marks, crosses, or short text. On phones the table keeps its size and visitors scroll it sideways.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Caption', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'A short description of what the table compares. It is not shown on screen, but screen readers read it out. Up to 150 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'First column heading', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The heading above the row names, like "Restaurant". Up to 40 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Columns', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 7 column headings, like "Lake view" or "Kid friendly". Up to 40 characters each.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Rows', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 20 rows. Each row has a <strong>Name</strong> and one <strong>Value</strong> per column, in the same order as the columns. A value is a Check, a Cross, or a short Text (up to 30 characters).', 'ltt-dive-in' ) ),
			),
		),
		'resource_links'   => array(
			'name'        => __( 'Resource Links', 'ltt-dive-in' ),
			'description' => __( 'A centered title with one to four buttons that point to useful pages, like permits, maps, or booking sites.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Up to 100 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Introduction', 'ltt-dive-in' ), 'description' => __( 'One line under the title. Up to 200 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Links', 'ltt-dive-in' ), 'required' => true, 'description' => __( '1 to 4 buttons. Give each one a label that says where it goes.', 'ltt-dive-in' ) ),
			),
		),
		'image_pair'       => array(
			'name'        => __( 'Image Pair', 'ltt-dive-in' ),
			'description' => __( 'Two photos side by side, with no text. A nice break between sections.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Images', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'Exactly 2 photos. Landscape photos fit best.', 'ltt-dive-in' ) ),
			),
		),
		'conclusion'       => array(
			'name'        => __( 'Article Conclusion', 'ltt-dive-in' ),
			'description' => __( 'The closing text of an article, centered between two lines, with an optional title and button.', 'ltt-dive-in' ),
			'fields'      => array(
				array( 'label' => __( 'Title', 'ltt-dive-in' ), 'description' => __( 'An optional title above the text. Up to 100 characters.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Content', 'ltt-dive-in' ), 'required' => true, 'description' => __( 'The closing words.', 'ltt-dive-in' ) ),
				array( 'label' => __( 'Button', 'ltt-dive-in' ), 'description' => __( 'An optional button, like "Plan your trip".', 'ltt-dive-in' ) ),
			),
		),
	),
	'tips'            => array(
		__( 'Each article type has its own set of pieces. A round-up uses Numbered Heading or Listicle. An itinerary uses Itinerary Day followed by Itinerary Items. A guide uses Numbers Guide, Bullet Guide, or Guide 1-Up. Most articles end with an Article Conclusion.', 'ltt-dive-in' ),
		__( 'You can copy a Story Module with Ctrl+Shift+D (Cmd+Shift+D on Mac) and then change the content. It is faster than starting each item from scratch.', 'ltt-dive-in' ),
		__( 'Number your Listicles, Numbered Headings, and Itinerary Days by hand, and check them again if you reorder the items.', 'ltt-dive-in' ),
		__( 'Write link text that says where it goes, like "book a table at Lone Eagle", not "click here".', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'I cannot find Story Module in the inserter.', 'ltt-dive-in' ) => __( 'You are probably editing a page. Story Module is only available in blog posts.', 'ltt-dive-in' ),
		__( 'The editor says "Choose a Story Module variant to begin."', 'ltt-dive-in' ) => __( 'Pick a Variant in the panel on the right.', 'ltt-dive-in' ),
	),
);
