<?php
/**
 * Site Guide: Footer settings.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'updated'         => '2026-11-23',
	'title'           => __( 'Footer', 'ltt-dive-in' ),
	'group'           => 'appearance',
	'icon'            => 'table-row-after',
	'menu'            => __( 'Appearance → Footer Settings', 'ltt-dive-in' ),
	'screen'          => array(
		'parent'     => 'themes.php',
		'slug'       => 'ltt-dive-in-footer-settings',
		'capability' => 'edit_theme_options',
	),
	'short'           => __( 'Newsletter sign-up, logos, social links, and the FAQ button at the bottom of every page.', 'ltt-dive-in' ),
	'summary'         => __( 'Everything in the dark footer at the bottom of every page, except the menus: the newsletter sign-up, the FAQ button, the logos, and the social media links. The screen has three tabs: Content, Logos, and Social links.', 'ltt-dive-in' ),
	'shows'           => array(
		__( 'The footer of every page.', 'ltt-dive-in' ),
	),
	'fields'          => array(
		array(
			'label'       => __( 'Frequently Asked Questions button', 'ltt-dive-in' ),
			'description' => __( 'Content tab. Where the FAQ button goes. If you leave the label empty, it says "Frequently Asked Questions".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Newsletter heading', 'ltt-dive-in' ),
			'description' => __( 'Content tab. The title above the sign-up form. It starts as "Subscribe".', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Newsletter description', 'ltt-dive-in' ),
			'description' => __( 'Content tab. One sentence about what people get when they sign up.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Newsletter form', 'ltt-dive-in' ),
			'description' => __( 'Content tab. Which Gravity Forms form is used for the sign-up. The form\'s fields, confirmation message, and where the emails go are set in <strong>Forms</strong>, not here.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Newsletter disclaimer', 'ltt-dive-in' ),
			'description' => __( 'Content tab. The small print under the form. Type <code>{privacy_policy}</code> where the link to your privacy policy should go. The page it links to is chosen in <strong>Settings → Privacy</strong>.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Main Logo', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Logos tab. The large logo in the footer. Use a version that reads well on a dark background, and give it alt text in the Media Library.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Left column logos / Right column logos', 'ltt-dive-in' ),
			'required'    => true,
			'description' => __( 'Logos tab. Partner logos, at least one in each column, in the order you want them. SVG files look sharpest. Each one needs alt text in the Media Library, usually just the partner\'s name.', 'ltt-dive-in' ),
		),
		array(
			'label'       => __( 'Facebook, Instagram, X, LinkedIn, and YouTube URL', 'ltt-dive-in' ),
			'description' => __( 'Social links tab. Paste the full address of each profile. Leave a field empty to hide that icon.', 'ltt-dive-in' ),
		),
	),
	'status'          => array(
		array(
			'label' => __( 'Newsletter form', 'ltt-dive-in' ),
			'check' => static function () {
				if ( ! class_exists( 'GFAPI' ) ) {
					return array(
						'state' => 'warning',
						'text'  => __( 'Gravity Forms is not active, so the footer has no sign-up form.', 'ltt-dive-in' ),
					);
				}

				$form_id = function_exists( 'ltt_dive_in_get_footer_option' ) ? absint( ltt_dive_in_get_footer_option( 'ltt_dive_in_footer_newsletter_form_id', 1 ) ) : 0;
				$form    = $form_id ? GFAPI::get_form( $form_id ) : false;

				if ( ! is_array( $form ) || empty( $form['is_active'] ) ) {
					return array(
						'state' => 'warning',
						'text'  => __( 'The selected form is missing or inactive, so the footer has no sign-up form.', 'ltt-dive-in' ),
					);
				}

				$feeds = GFAPI::get_feeds( null, $form_id );

				if ( is_wp_error( $feeds ) || ! $feeds ) {
					return array(
						'state' => 'warning',
						/* translators: %s: form title. */
						'text'  => sprintf( __( 'Using "%s", but it is not connected to a newsletter service. Sign-ups are only saved in Forms → Entries.', 'ltt-dive-in' ), $form['title'] ),
					);
				}

				return array(
					'state' => 'ok',
					/* translators: %s: form title. */
					'text'  => sprintf( __( 'Using "%s", connected to a newsletter service', 'ltt-dive-in' ), $form['title'] ),
				);
			},
		),
		array(
			'label' => __( 'Privacy policy page', 'ltt-dive-in' ),
			'check' => static function () {
				$page_id = (int) get_option( 'wp_page_for_privacy_policy' );

				return $page_id && 'publish' === get_post_status( $page_id )
					? array(
						'state' => 'ok',
						'text'  => __( 'Published, so the disclaimer link works', 'ltt-dive-in' ),
					)
					: array(
						'state' => 'warning',
						'text'  => __( 'Not set or not published. The disclaimer shows without a link. Choose the page in Settings → Privacy.', 'ltt-dive-in' ),
					);
			},
		),
		array(
			'label' => __( 'Social links', 'ltt-dive-in' ),
			'check' => static function () {
				$count = 0;

				foreach ( array( 'facebook', 'instagram', 'x', 'linkedin', 'youtube' ) as $network ) {
					if ( function_exists( 'get_field' ) && '' !== trim( (string) get_field( 'ltt_dive_in_footer_' . $network . '_url', 'option' ) ) ) {
						++$count;
					}
				}

				return $count
					? array(
						'state' => 'ok',
						/* translators: %d: number of links. */
						'text'  => sprintf( _n( '%d of 5 set', '%d of 5 set', $count, 'ltt-dive-in' ), $count ),
					)
					: array(
						'state' => 'off',
						'text'  => __( 'None set. No social icons show in the footer.', 'ltt-dive-in' ),
					);
			},
		),
	),
	'tips'            => array(
		__( 'The footer menus are edited in <strong>Appearance → Menus</strong>: <strong>Footer Navigation</strong> for the main links and <strong>Footer Legal</strong> for the small links at the very bottom.', 'ltt-dive-in' ),
		__( 'To check who signed up, go to <strong>Forms → Entries</strong> and pick the newsletter form.', 'ltt-dive-in' ),
	),
	'troubleshooting' => array(
		__( 'The sign-up form is missing from the footer.', 'ltt-dive-in' ) => __( 'The selected form was deleted or deactivated in Forms. Pick an active form in the Newsletter form field.', 'ltt-dive-in' ),
		__( 'People sign up but never get the newsletter.', 'ltt-dive-in' ) => __( 'The form saves sign-ups in WordPress, but it also needs a connection to your newsletter service (like Mailchimp) to add them to your list. Check the Current status above, and ask your developer to set up the connection if it is missing.', 'ltt-dive-in' ),
	),
);
