<?php
/**
 * Site Guide: Site Alert settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Site Alert', 'ltt-dive-in' ),
	'group'           => 'appearance',
	'icon'            => 'megaphone',
	'menu'            => __( 'Appearance → Site Alert', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'themes.php',
		'slug'       => 'ltt-dive-in-site-alert',
		'capability' => 'edit_theme_options',
	),
	'short'           => __( 'A banner above the header for road closures, weather, or news.', 'ltt-dive-in' ),
	'summary'         => __( 'A colored banner at the very top of the site, above the header. Use it for things visitors need to know right now, like a road closure, a storm warning, or a big announcement. Only one alert can be on at a time.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'Above the header, on every page or only on the pages you choose.', 'ltt-dive-in' ),
		__( 'On desktop, visitors can close it. It comes back the next day.', 'ltt-dive-in' ),
		__( 'On phones, visitors can collapse it, and it stays collapsed while they browse.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Show alert', 'ltt-dive-in' ),
			'description' => __( 'The on and off switch. Turn it off when the alert is no longer needed. Your text is kept for next time.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Type', 'ltt-dive-in' ),
			'description' => __( '<strong>Travel Alert</strong> (orange, for road and weather problems) or <strong>Announcement</strong> (teal, for news). It sets the icon and the default colors, and tells screen reader users what kind of alert it is.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Sticky', 'ltt-dive-in' ),
			'description' => __( 'On: the alert stays at the top while visitors scroll. Off: it scrolls away with the page.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Label', 'ltt-dive-in' ),
			'description' => __( 'Optional bold text before the message, like "Road closure:".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Message', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'The alert itself. One short sentence works best, like "Highway 89 at Emerald Bay is closed until Friday." Without a message the alert does not show.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Link', 'ltt-dive-in' ),
			'description' => __( 'Optional link for more details, like the road conditions page.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Icon', 'ltt-dive-in' ),
			'description' => __( 'Optional. Replaces the default icon. Use a single-color SVG or a PNG with a transparent background. It takes the text color automatically.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Background color / Text color', 'ltt-dive-in' ),
			'description' => __( 'Start with the colors of the selected type. Changing the type resets them. If the two colors are hard to read together, the screen shows a warning but still lets you save.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Display on', 'ltt-dive-in' ),
			'description' => __( '<strong>Entire site</strong>, <strong>Only the selected pages</strong>, or <strong>Entire site except the selected pages</strong>. With the last two, pick the pages in the <strong>Pages</strong> field.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Alert', 'ltt-dive-in' ),
			'check' => static function () {
				if ( ! function_exists( 'get_field' ) || ! get_field( 'ltt_dive_in_site_alert_enabled', 'option' ) ) {
					return array(
						'state' => 'off',
						'text'  => __( 'Off', 'ltt-dive-in' ),
					);
				}

				if ( '' === trim( (string) get_field( 'ltt_dive_in_site_alert_message', 'option' ) ) ) {
					return array(
						'state' => 'warning',
						'text'  => __( 'Turned on, but the message is empty, so nothing shows.', 'ltt-dive-in' ),
					);
				}

				$scopes = array(
					'all'     => __( 'on the entire site', 'ltt-dive-in' ),
					'include' => __( 'only on selected pages', 'ltt-dive-in' ),
					'exclude' => __( 'on the entire site except selected pages', 'ltt-dive-in' ),
				);
				$scope  = (string) get_field( 'ltt_dive_in_site_alert_display', 'option' );

				return array(
					'state' => 'ok',
					/* translators: %s: where the alert shows. */
					'text'  => sprintf( __( 'Showing %s', 'ltt-dive-in' ), $scopes[ $scope ] ?? $scopes['all'] ),
				);
			},
		),
	),
	'tips'            => array(
		__( 'Remember to turn the alert off when the situation is over. An old alert makes visitors ignore the next one.', 'ltt-dive-in' ),
		__( 'Use Travel Alert only for real problems. For news and promotions, use Announcement.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'I turned the alert on but it does not show.', 'ltt-dive-in' ) => __( 'Check that the Message has text and that Display on includes the page you are looking at. If you closed it on desktop before, it stays hidden for you until the next day; try a private window.', 'ltt-dive-in' ),
	),
);
