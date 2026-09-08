<?php
/**
 * Global footer settings and data helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the footer options page and its versioned field group.
 */
function ltt_dive_in_register_footer_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$newsletter_form_choices = array();

	if ( is_admin() && class_exists( 'GFAPI' ) ) {
		$gravity_forms = GFAPI::get_forms( true, false, 'title', 'ASC' );

		if ( is_array( $gravity_forms ) ) {
			foreach ( $gravity_forms as $gravity_form ) {
				$newsletter_form_choices[ (string) $gravity_form['id'] ] = $gravity_form['title'];
			}
		}
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Footer Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Footer Settings', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-footer-settings',
			'parent_slug' => 'themes.php',
			'capability'  => 'edit_theme_options',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_ltt_dive_in_footer_settings',
			'title'    => __( 'LTT Dive In — Footer Settings', 'ltt-dive-in' ),
			'fields'   => array(
				array(
					'key'   => 'field_ltt_dive_in_footer_content_tab',
					'label' => __( 'Content', 'ltt-dive-in' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_ltt_dive_in_footer_faq_link',
					'label'         => __( 'FAQ link', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_footer_faq_link',
					'type'          => 'link',
					'instructions'  => __( 'Optional. The FAQ button is hidden until a link is provided.', 'ltt-dive-in' ),
					'return_format' => 'array',
				),
				array(
					'key'           => 'field_ltt_dive_in_footer_newsletter_heading',
					'label'         => __( 'Newsletter heading', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_footer_newsletter_heading',
					'type'          => 'text',
					'default_value' => __( 'Subscribe', 'ltt-dive-in' ),
				),
				array(
					'key'           => 'field_ltt_dive_in_footer_newsletter_description',
					'label'         => __( 'Newsletter description', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_footer_newsletter_description',
					'type'          => 'textarea',
					'rows'          => 3,
					'new_lines'     => '',
					'default_value' => __( 'Get trip ideas and seasonal guides delivered to your inbox.', 'ltt-dive-in' ),
				),
				array(
					'key'           => 'field_ltt_dive_in_footer_newsletter_form_id',
					'label'         => __( 'Newsletter form', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_footer_newsletter_form_id',
					'type'          => 'select',
					'instructions'  => __( 'Select the active Gravity Form used for footer subscriptions.', 'ltt-dive-in' ),
					'choices'       => $newsletter_form_choices,
					'default_value' => '1',
					'allow_null'    => 1,
					'ui'            => 1,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_ltt_dive_in_footer_newsletter_disclaimer',
					'label'         => __( 'Newsletter disclaimer', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_footer_newsletter_disclaimer',
					'type'          => 'textarea',
					'rows'          => 3,
					'new_lines'     => '',
					'default_value' => __( 'By subscribing you agree to our Privacy Policy and consent to receive updates from Lake Tahoe Travel.', 'ltt-dive-in' ),
				),
				array(
					'key'   => 'field_ltt_dive_in_footer_social_tab',
					'label' => __( 'Social links', 'ltt-dive-in' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'         => 'field_ltt_dive_in_footer_facebook_url',
					'label'       => __( 'Facebook URL', 'ltt-dive-in' ),
					'name'        => 'ltt_dive_in_footer_facebook_url',
					'type'        => 'url',
					'placeholder' => 'https://www.facebook.com/',
				),
				array(
					'key'         => 'field_ltt_dive_in_footer_instagram_url',
					'label'       => __( 'Instagram URL', 'ltt-dive-in' ),
					'name'        => 'ltt_dive_in_footer_instagram_url',
					'type'        => 'url',
					'placeholder' => 'https://www.instagram.com/',
				),
				array(
					'key'         => 'field_ltt_dive_in_footer_x_url',
					'label'       => __( 'X URL', 'ltt-dive-in' ),
					'name'        => 'ltt_dive_in_footer_x_url',
					'type'        => 'url',
					'placeholder' => 'https://x.com/',
				),
				array(
					'key'         => 'field_ltt_dive_in_footer_linkedin_url',
					'label'       => __( 'LinkedIn URL', 'ltt-dive-in' ),
					'name'        => 'ltt_dive_in_footer_linkedin_url',
					'type'        => 'url',
					'placeholder' => 'https://www.linkedin.com/',
				),
				array(
					'key'         => 'field_ltt_dive_in_footer_youtube_url',
					'label'       => __( 'YouTube URL', 'ltt-dive-in' ),
					'name'        => 'ltt_dive_in_footer_youtube_url',
					'type'        => 'url',
					'placeholder' => 'https://www.youtube.com/',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-footer-settings',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_footer_settings' );

/**
 * Get an optional ACF footer value without requiring ACF at runtime.
 *
 * @param string $field_name ACF field name.
 * @param mixed  $default    Value returned when the field is empty or unavailable.
 * @return mixed
 */
function ltt_dive_in_get_footer_option( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $field_name, 'option' );

	return empty( $value ) ? $default : $value;
}

/**
 * Get configured social profile links for the footer.
 *
 * @return array[]
 */
function ltt_dive_in_get_footer_social_links() {
	$networks = array(
		'facebook'  => __( 'Facebook', 'ltt-dive-in' ),
		'instagram' => __( 'Instagram', 'ltt-dive-in' ),
		'x'         => __( 'X', 'ltt-dive-in' ),
		'linkedin'  => __( 'LinkedIn', 'ltt-dive-in' ),
		'youtube'   => __( 'YouTube', 'ltt-dive-in' ),
	);
	$links    = array();

	foreach ( $networks as $slug => $label ) {
		$url = ltt_dive_in_get_footer_option( 'ltt_dive_in_footer_' . $slug . '_url' );

		if ( ! $url ) {
			continue;
		}

		$links[] = array(
			'label' => $label,
			'slug'  => $slug,
			'url'   => $url,
		);
	}

	return $links;
}

/**
 * Render the configured production newsletter form.
 *
 * Gravity Forms is preferred when a valid form is selected. The existing
 * widget area and integration hook remain as fallbacks for other providers.
 *
 * @return string
 */
function ltt_dive_in_get_footer_newsletter_form() {
	$form_id      = absint( ltt_dive_in_get_footer_option( 'ltt_dive_in_footer_newsletter_form_id', 1 ) );
	$gravity_form = $form_id && class_exists( 'GFAPI' ) ? GFAPI::get_form( $form_id ) : false;

	ob_start();

	if ( function_exists( 'gravity_form' ) && is_array( $gravity_form ) && ! empty( $gravity_form['is_active'] ) ) {
		gravity_form( $form_id, false, false, false, null, true, 0, true );
	} elseif ( is_active_sidebar( 'footer-1' ) ) {
		dynamic_sidebar( 'footer-1' );
	} else {
		/**
		 * Render a production newsletter form when Gravity Forms and the footer
		 * form widget are unavailable.
		 *
		 * Integrations should output a complete, accessible form with a real action.
		 */
		do_action( 'ltt_dive_in_footer_newsletter_form' );
	}

	return trim( ob_get_clean() );
}
