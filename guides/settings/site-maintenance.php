<?php
/**
 * Site Guide: Site Maintenance (site plugin) settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Site Maintenance', 'ltt-dive-in' ),
	'group'           => 'settings',
	'icon'            => 'hammer',
	'menu'            => __( 'Settings → Site Maintenance', 'ltt-dive-in' ),
	'available'       => static function () {
		return function_exists( 'ltt_site_maintenance_register_settings' );
	},
	'screen'          => array(
		'parent'     => 'options-general.php',
		'slug'       => 'ltt-site-maintenance',
		'capability' => 'manage_options',
	),
	'short'           => __( 'Closes the site to visitors during planned work and shows a maintenance page.', 'ltt-dive-in' ),
	'summary'         => __( 'Maintenance mode replaces every public page with a "temporarily unavailable" page while work is done on the site. Administrators keep seeing the normal site. The maintenance page can also show a live lake camera so visitors have something to look at.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'Every public page, only while maintenance mode is on.', 'ltt-dive-in' ),
		__( 'Its background photos are chosen in Appearance → 404 & Maintenance Images.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Maintenance mode', 'ltt-dive-in' ),
			'description' => __( 'The on and off switch. When it is on, visitors and search engines see the maintenance page. <strong>Remember to turn it off</strong> when the work is done.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Retry-After (seconds)', 'ltt-dive-in' ),
			'description' => __( 'Tells search engines how long to wait before checking again. Leave the default unless your developer asks you to change it.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Viewer', 'ltt-dive-in' ),
			'description' => __( '<strong>Image</strong> shows a cover photo that opens the camera\'s public page. <strong>Prism Cam</strong> shows the live camera player.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Camera cover image', 'ltt-dive-in' ),
			'description' => __( 'A landscape photo of the camera view. Describe the view in its alt text in the Media Library. It is also used as the still image for Prism Cam.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Prism camera ID', 'ltt-dive-in' ),
			'description' => __( 'Only for Prism Cam. The camera number from the embed code Prism provided.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Approved camera host / Camera public URL', 'ltt-dive-in' ),
			'description' => __( 'Only for Image. The camera website\'s domain, and the page the cover image opens. Your developer usually sets these once.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Maintenance mode', 'ltt-dive-in' ),
			'check' => static function () {
				return get_option( 'ltt_site_maintenance_enabled', 0 )
					? array(
						'state' => 'warning',
						'text'  => __( 'ON. Visitors cannot see the site right now.', 'ltt-dive-in' ),
					)
					: array(
						'state' => 'ok',
						'text'  => __( 'Off. The site is open to visitors.', 'ltt-dive-in' ),
					);
			},
		),
	),
	'tips'            => array(
		__( 'Use the <strong>Preview the 503 page</strong> link at the bottom of the settings screen to check the page without closing the site.', 'ltt-dive-in' ),
		__( 'Because you are logged in as an administrator, you will not see the maintenance page while browsing. Use a private window to check what visitors see.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
