<?php
/**
 * Block Guide samples for Meet the Team.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$member = static function ( $name, $role, $bio, $photo ) {
	return array(
		'photo'        => ltt_dive_in_block_guide_photo( $photo ),
		'name'         => $name,
		'role'         => $role,
		'bio'          => $bio,
		'social_links' => array(
			array( 'url' => 'https://www.linkedin.com/' ),
			array( 'url' => 'https://www.instagram.com/' ),
		),
	);
};

return array(
	'default' => array(
		'ltt_dive_in_team_tag'             => __( 'About us', 'ltt-dive-in' ),
		'ltt_dive_in_team_heading'         => __( 'Meet the team', 'ltt-dive-in' ),
		'ltt_dive_in_team_intro'           => __( 'We live here, ski here, and spend our summers on the water. Ask us anything.', 'ltt-dive-in' ),
		'ltt_dive_in_team_contact_heading' => __( 'Want to reach us?', 'ltt-dive-in' ),
		'ltt_dive_in_team_contact_copy'    => __( 'We usually answer within one business day.', 'ltt-dive-in' ),
		'ltt_dive_in_team_contact_link'    => ltt_dive_in_block_guide_link( __( 'Contact us', 'ltt-dive-in' ) ),
		'ltt_dive_in_team_members'         => array(
			$member( __( 'Sarah Kim', 'ltt-dive-in' ), __( 'Executive Director', 'ltt-dive-in' ), __( 'Sarah has worked in Tahoe tourism for fifteen years and still hikes to Eagle Lake every week.', 'ltt-dive-in' ), 5 ),
			$member( __( 'Daniel Ortiz', 'ltt-dive-in' ), __( 'Marketing Manager', 'ltt-dive-in' ), __( 'Daniel runs our campaigns and knows every taco spot on the South Shore.', 'ltt-dive-in' ), 6 ),
			$member( __( 'Emily Chen', 'ltt-dive-in' ), __( 'Partnerships', 'ltt-dive-in' ), __( 'Emily works with local businesses and spends her winters on a splitboard.', 'ltt-dive-in' ), 7 ),
		),
	),
);
