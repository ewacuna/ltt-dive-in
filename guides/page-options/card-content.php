<?php
/**
 * Site Guide: Card content box.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Card content', 'ltt-dive-in' ),
	'group'           => 'pages',
	'order'           => 30,
	'icon'            => 'id-alt',
	'where'           => __( 'Page editor, "Card content" box below the content', 'ltt-dive-in' ),
	'applies'         => __( 'Every page.', 'ltt-dive-in' ),
	'short'           => __( 'The short text shown when this page appears as a card in other blocks.', 'ltt-dive-in' ),
	'summary'         => __( 'Several blocks show other pages as photo cards, like the Up Driver and Activities. Those cards use the page\'s title, its featured image, and the short description written here. Write it once and it is used everywhere the page shows up as a card.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'Cards in the Up Driver block.', 'ltt-dive-in' ),
		__( 'Cards in the Activities block.', 'ltt-dive-in' ),
		__( 'Place cards in the End Page Cluster Content Trail (which uses the page excerpt).', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Description', 'ltt-dive-in' ),
			'description' => __( 'One or two sentences that make someone want to open the page. Plain text only. Leave it empty and the card shows just the title.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Keep it under about 120 characters. Cards are small, and longer text gets cut off on phones.', 'ltt-dive-in' ),
		__( 'Do not repeat the page title. Add something new, like what visitors can do there.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
