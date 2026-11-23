<?php
/**
 * Site Guide: Page Header box.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Page Header', 'ltt-dive-in' ),
	'group'           => 'pages',
	'order'           => 20,
	'icon'            => 'welcome-widgets-menus',
	'where'           => __( 'Page editor, right sidebar, "Page Header" box', 'ltt-dive-in' ),
	'applies'         => __( 'Every page except the home page, the blog page, and Blank Canvas pages.', 'ltt-dive-in' ),
	'short'           => __( 'Hero design, header color, and the section menu under the hero.', 'ltt-dive-in' ),
	'summary'         => __( 'Three choices about the top of the page: which hero design to use, how the site header looks over it, and whether to show a row of section links under the hero.', 'ltt-dive-in' ),
	'fields'          => array(
		array(
			'label'       => __( 'Page hero design', 'ltt-dive-in' ),
			'description' => __( '<strong>Standard</strong> is the regular hero used on most pages. <strong>Immersive Hero</strong> is a bigger, full-screen version with an icon, used for pages like Meetings.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Immersive Hero title', 'ltt-dive-in' ),
			'description' => __( 'Only for Immersive Hero. Leave it empty to use the page title. Press Enter to choose where the title breaks into lines.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Immersive Hero description', 'ltt-dive-in' ),
			'description' => __( 'Only for Immersive Hero. Leave it empty to use the page excerpt.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Immersive Hero icon (SVG)', 'ltt-dive-in' ),
			'description' => __( 'Only for Immersive Hero. A transparent SVG shown with the title. Leave it empty for the default icon.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Header background', 'ltt-dive-in' ),
			'description' => __( '<strong>Solid navy</strong>, <strong>Navy gradient</strong>, or <strong>Hero overlay</strong>, which places the header on top of the hero photo for a more open look.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Section menu', 'ltt-dive-in' ),
			'description' => __( 'Shows a row of links under the hero, for example to move between the Current Conditions pages. Pick a menu from the list. Menus are created in <strong>Appearance → Menus</strong>; after creating one, reload the editor to see it here.', 'ltt-dive-in' ),
		),
	),
	'tips'            => array(
		__( 'Give section menus a clear name, like "Current Conditions: Section Navigation", so nobody picks the wrong menu.', 'ltt-dive-in' ),
		__( 'Only the top-level links of the chosen menu appear in the section menu.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'My new menu is not in the Section menu list.', 'ltt-dive-in' ) => __( 'Save the menu in Appearance → Menus, then reload the page editor.', 'ltt-dive-in' ),
	),
);
