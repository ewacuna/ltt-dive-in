<?php
/**
 * Site Guide: 404 and maintenance background images.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_check = static function ( $field, $label ) {
	return array(
		'label' => $label,
		'check' => static function () use ( $field ) {
			$images = function_exists( 'get_field' ) ? get_field( $field, 'option', false ) : array();
			$count  = is_array( $images ) ? count( array_filter( $images ) ) : 0;

			return $count
				? array(
					'state' => 'ok',
					/* translators: %d: number of images. */
					'text'  => sprintf( _n( '%d image', '%d images', $count, 'ltt-dive-in' ), $count ),
				)
				: array(
					'state' => 'off',
					'text'  => __( 'Using the default background', 'ltt-dive-in' ),
				);
		},
	);
};

return array(
	'updated'         => '2026-11-23',
	'title'           => __( '404 & Maintenance Images', 'ltt-dive-in' ),
	'group'           => 'appearance',
	'icon'            => 'format-gallery',
	'menu'            => __( 'Appearance → 404 & Maintenance Images', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'themes.php',
		'slug'       => 'ltt-dive-in-error-backgrounds',
		'capability' => 'edit_theme_options',
	),
	'short'           => __( 'Background photos for the "page not found" and maintenance pages.', 'ltt-dive-in' ),
	'summary'         => __( 'Two special pages use a full-screen photo behind their text: the "Page not found" page visitors see when a link is broken, and the maintenance page shown while the site is closed for work. This screen picks those photos.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The "Page not found" (404) page.', 'ltt-dive-in' ),
		__( 'The maintenance page, when maintenance mode is turned on in Settings → Site Maintenance.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Background images (Page Not Found)', 'ltt-dive-in' ),
			'description' => __( 'Up to 10 photos. With two or more, they fade from one to the next. Pick photos with a calm area in the middle so the white text stays readable. Leave empty to use the default background.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Change image every (seconds)', 'ltt-dive-in' ),
			'description' => __( 'How long each photo stays before the next one, from 5 to 60 seconds. It starts at 10. Only used with two or more photos.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Background images (Maintenance Page)', 'ltt-dive-in' ),
			'description' => __( 'Same as above, for the maintenance page.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Change image every (seconds) (Maintenance Page)', 'ltt-dive-in' ),
			'description' => __( 'Same as above, for the maintenance page.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		$image_check( 'ltt_dive_in_error_404_background_images', __( 'Page not found', 'ltt-dive-in' ) ),
		$image_check( 'ltt_dive_in_error_503_background_images', __( 'Maintenance page', 'ltt-dive-in' ) ),
	),
	'tips'            => array(
		__( 'To see the 404 page, open any address on the site that does not exist, like your site address followed by <code>/test-404</code>.', 'ltt-dive-in' ),
		__( 'Visitors who prefer less motion see the first photo only, without the fade.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(),
);
