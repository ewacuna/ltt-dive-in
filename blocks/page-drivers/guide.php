<?php
/**
 * Block Guide content for Up Driver.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'summary'         => __( 'A grid of photo cards that link to other pages on the site. You only pick the pages: each card fills itself in with the page\'s featured image, title, and short description. You can add filter buttons so visitors can narrow the cards down, for example by activity or by area.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts. It is the main building block of hub pages.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Up Driver</strong>.', 'ltt-dive-in' ),
		__( 'Choose a <strong>Layout</strong>. The number in the name is how many cards it shows.', 'ltt-dive-in' ),
		__( 'Write the heading, then pick the pages under <strong>Pages</strong> and drag them into order.', 'ltt-dive-in' ),
		__( 'Turn on <strong>Show filters</strong> if you want filter buttons.', 'ltt-dive-in' ),
	),
	'fields_heading'  => __( 'Fields every layout has', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Layout', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'How many cards and how they are arranged. See the layouts below.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Heading', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The section title. Up to 90 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Background', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Light or Dark.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Category tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the heading. Up to 40 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Introduction', 'ltt-dive-in' ),
			'description' => __( 'A short paragraph under the heading. Up to 240 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Button text', 'ltt-dive-in' ),
			'description' => __( 'The button label on every card. If you leave it empty, the cards say "Explore".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Show page descriptions', 'ltt-dive-in' ),
			'description' => __( 'Shows the short description under each card title. Turn it off for a cleaner look with titles only.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Pages', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The pages for the cards. Each one must be published and have a featured image. The layout decides how many you need.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Show filters', 'ltt-dive-in' ),
			'description' => __( 'Adds a row of filter buttons above the cards.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Filter by', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'With filters on: what to group by. Activity, Lodging Type, Place, or When.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Filters', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'With filters on: the options visitors can pick, like Hiking, Biking, Skiing. A page shows under a filter when it has that option checked in its <strong>Classification</strong> box.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Cards per filter', 'ltt-dive-in' ),
			'description' => __( 'For 1 Up to 5 Up with filters on. Instead of filtering one list of pages, you set a separate group of pages for each filter. Add 2 to 5 filters, and for each one choose the option and its pages. The first filter is the one shown when the page opens.', 'ltt-dive-in' ),
		),
	),
	'variants'        => array(
		'one_up'      => array(
			'name'        => __( '1 Up', 'ltt-dive-in' ),
			'description' => __( 'One wide card.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 1 page.', 'ltt-dive-in' ) ),
		),
		'two_up'      => array(
			'name'        => __( '2 Up', 'ltt-dive-in' ),
			'description' => __( 'Two cards side by side.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 2 pages.', 'ltt-dive-in' ) ),
		),
		'three_up'    => array(
			'name'        => __( '3 Up', 'ltt-dive-in' ),
			'description' => __( 'Three cards in a row.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 3 pages.', 'ltt-dive-in' ) ),
		),
		'four_up'     => array(
			'name'        => __( '4 Up', 'ltt-dive-in' ),
			'description' => __( 'Four cards.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 4 pages.', 'ltt-dive-in' ) ),
		),
		'five_up'     => array(
			'name'        => __( '5 Up', 'ltt-dive-in' ),
			'description' => __( 'Five cards, with one of them larger than the rest.', 'ltt-dive-in' ),
			'notes'       => array( __( 'Exactly 5 pages.', 'ltt-dive-in' ) ),
		),
		'six_plus_up' => array(
			'name'        => __( '6+ Up', 'ltt-dive-in' ),
			'description' => __( 'A carousel that visitors slide through with arrows and dots.', 'ltt-dive-in' ),
			'notes'       => array( __( '6 to 12 pages.', 'ltt-dive-in' ) ),
		),
		'monthly'     => array(
			'name'        => __( 'Monthly Up Driver', 'ltt-dive-in' ),
			'description' => __( 'A carousel with one card per month, January to December, and a month dropdown on top. Choosing a month jumps to that month\'s card.', 'ltt-dive-in' ),
			'best_for'    => __( 'A "Tahoe through the year" section.', 'ltt-dive-in' ),
			'notes'       => array(
				__( 'Needs exactly 12 pages, and <strong>Show filters</strong> must be on.', 'ltt-dive-in' ),
				__( 'Set <strong>Filter by</strong> to the group that holds the months (usually When), then pick the months under Filters. Each month must be checked on at least one of the 12 pages.', 'ltt-dive-in' ),
				__( 'The cards are always sorted from January to December, no matter the order you picked them in.', 'ltt-dive-in' ),
			),
		),
	),
	'tips'            => array(
		__( 'The card text comes from the page itself. The photo is the featured image, and the description is the <strong>Card content</strong> box on that page. To change a card, edit the page.', 'ltt-dive-in' ),
		__( 'Write the Card content description once and it is used everywhere that page shows up as a card.', 'ltt-dive-in' ),
		__( 'For filters to work, the pages need to be classified. Open each page and check the right options in its <strong>Classification</strong> box.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'WordPress says "Choose exactly X pages."', 'ltt-dive-in' ) => __( 'The layout needs a set number of pages. Add or remove pages, or pick a different layout.', 'ltt-dive-in' ),
		__( 'WordPress says "Choose published pages with a featured image."', 'ltt-dive-in' ) => __( 'One of the pages is a draft or has no featured image. Fix that page and save again.', 'ltt-dive-in' ),
		__( 'WordPress says "Turn on filters to use Monthly."', 'ltt-dive-in' ) => __( 'The Monthly layout always needs filters. Turn on Show filters and set it up as described above.', 'ltt-dive-in' ),
		__( 'The editor says "Choose a page for each filter to preview this section."', 'ltt-dive-in' ) => __( 'With Cards per filter on, every filter needs at least one page.', 'ltt-dive-in' ),
	),
);
