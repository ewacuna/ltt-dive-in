<?php
/**
 * Block Guide content for Meet the Team.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'summary'         => __( 'Introduces the people behind the organization. Each person gets a photo, name, role, short bio, and links to their social profiles. There is also an optional contact area next to the heading.', 'ltt-dive-in' ),
	'where'           => __( 'Pages and blog posts. Usually an About or Contact page.', 'ltt-dive-in' ),
	'steps'           => array(
		__( 'Click the + button, open <strong>LTT Blocks</strong>, and add <strong>Meet the Team</strong>.', 'ltt-dive-in' ),
		__( 'Write the heading and, if you want, the introduction and contact details.', 'ltt-dive-in' ),
		__( 'Add each person under <strong>Team members</strong>, in the order you want them shown.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Category tag', 'ltt-dive-in' ),
			'description' => __( 'A small label above the heading. Up to 30 characters.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Heading', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The section title, like "Meet the team".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Introduction', 'ltt-dive-in' ),
			'description' => __( 'A short paragraph about the team.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Contact heading', 'ltt-dive-in' ),
			'description' => __( 'An optional title for the contact area, like "Want to reach us?".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Contact copy', 'ltt-dive-in' ),
			'description' => __( 'A line or two for the contact area.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Contact button', 'ltt-dive-in' ),
			'description' => __( 'An optional button, like "Contact us", linked to your contact page.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Team members', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Add 1 to 12 people.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Photo', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'A square portrait. Use photos with a similar framing and background for everyone so the grid looks even.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Name', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The person\'s name.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Role', 'ltt-dive-in' ),
			'description' => __( 'Their job title.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Short bio', 'ltt-dive-in' ),
			'description' => __( 'Two or three sentences. Try to keep all the bios about the same length.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Social links', 'ltt-dive-in' ),
			'description' => __( 'Up to five profile URLs. Just paste the full link. The right icon (LinkedIn, X, Instagram, Facebook, YouTube, or Dribbble) is picked automatically from the address.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Paste the full profile address, starting with https://. Only the six sites listed above are accepted.', 'ltt-dive-in' ),
		__( 'The photo is treated as decoration because the name is right next to it, so you do not need to write alt text for it.', 'ltt-dive-in' ),
		__( 'Ask people before publishing their photo and social profiles.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The editor says "Add a heading and at least one team member with a name and photo."', 'ltt-dive-in' ) => __( 'Fill in the Heading and make sure at least one person has both a name and a photo.', 'ltt-dive-in' ),
		__( 'A social link is not showing.', 'ltt-dive-in' ) => __( 'The address is probably from a site the block does not support, so it is left out. Only LinkedIn, X, Instagram, Facebook, YouTube, and Dribbble links are shown.', 'ltt-dive-in' ),
	),
);
