<?php
/**
 * Meet the Team block data adapters.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social networks supported by team member cards.
 *
 * Keys match the icon modifier class. Hosts include their subdomains, so
 * "linkedin.com" also matches "www.linkedin.com" and "uk.linkedin.com".
 *
 * @return array<string, array{label: string, hosts: string[]}>
 */
function ltt_dive_in_get_team_social_networks() {
	return array(
		'linkedin'  => array(
			'label' => __( 'LinkedIn', 'ltt-dive-in' ),
			'hosts' => array( 'linkedin.com' ),
		),
		'x'         => array(
			'label' => __( 'X', 'ltt-dive-in' ),
			'hosts' => array( 'x.com', 'twitter.com' ),
		),
		'instagram' => array(
			'label' => __( 'Instagram', 'ltt-dive-in' ),
			'hosts' => array( 'instagram.com' ),
		),
		'facebook'  => array(
			'label' => __( 'Facebook', 'ltt-dive-in' ),
			'hosts' => array( 'facebook.com', 'fb.com' ),
		),
		'youtube'   => array(
			'label' => __( 'YouTube', 'ltt-dive-in' ),
			'hosts' => array( 'youtube.com', 'youtu.be' ),
		),
		'dribbble'  => array(
			'label' => __( 'Dribbble', 'ltt-dive-in' ),
			'hosts' => array( 'dribbble.com' ),
		),
	);
}

/**
 * Identify the supported social network for a profile URL.
 *
 * @param mixed $url Profile URL.
 * @return string Network key, or an empty string when unsupported.
 */
function ltt_dive_in_get_team_social_network( $url ) {
	if ( ! is_string( $url ) ) {
		return '';
	}

	$scheme = wp_parse_url( trim( $url ), PHP_URL_SCHEME );
	$host   = wp_parse_url( trim( $url ), PHP_URL_HOST );

	if ( ! in_array( $scheme, array( 'http', 'https' ), true ) || ! is_string( $host ) ) {
		return '';
	}

	$host = strtolower( $host );

	foreach ( ltt_dive_in_get_team_social_networks() as $network => $config ) {
		foreach ( $config['hosts'] as $domain ) {
			if ( $host === $domain || str_ends_with( $host, '.' . $domain ) ) {
				return $network;
			}
		}
	}

	return '';
}

/**
 * Reject social links that do not belong to a supported network.
 *
 * @param bool|string $valid Current validation state.
 * @param mixed       $value Submitted URL.
 * @return bool|string
 */
function ltt_dive_in_validate_team_social_url( $valid, $value ) {
	if ( true !== $valid || '' === $value || null === $value ) {
		return $valid;
	}

	if ( ! ltt_dive_in_get_team_social_network( $value ) ) {
		return __( 'Unsupported network. Use a LinkedIn, X, Instagram, Facebook, YouTube, or Dribbble profile URL.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_team_member_social_url', 'ltt_dive_in_validate_team_social_url', 10, 2 );

/**
 * Normalize team member repeater rows into renderable cards.
 *
 * Rows without a name or a valid image attachment are skipped so the block
 * never outputs an empty card.
 *
 * @param mixed $rows Raw ACF repeater value.
 * @return array[] Prepared team members.
 */
function ltt_dive_in_prepare_team_members( $rows ) {
	$members  = array();
	$networks = ltt_dive_in_get_team_social_networks();

	if ( ! is_array( $rows ) ) {
		return $members;
	}

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$name     = isset( $row['name'] ) && is_string( $row['name'] ) ? trim( $row['name'] ) : '';
		$image_id = isset( $row['photo'] ) ? absint( $row['photo'] ) : 0;

		if ( ! $name || ! $image_id || ! wp_attachment_is_image( $image_id ) ) {
			continue;
		}

		$socials = array();
		$links   = isset( $row['social_links'] ) && is_array( $row['social_links'] ) ? $row['social_links'] : array();

		foreach ( $links as $link ) {
			$url     = is_array( $link ) && isset( $link['url'] ) && is_string( $link['url'] ) ? esc_url_raw( trim( $link['url'] ) ) : '';
			$network = ltt_dive_in_get_team_social_network( $url );

			// Unsupported URLs saved before validation existed are skipped, not guessed.
			if ( $network ) {
				$socials[] = array(
					'network' => $network,
					'label'   => $networks[ $network ]['label'],
					'url'     => $url,
				);
			}
		}

		$members[] = array(
			'name'     => $name,
			'role'     => isset( $row['role'] ) && is_string( $row['role'] ) ? trim( $row['role'] ) : '',
			'bio'      => isset( $row['bio'] ) && is_string( $row['bio'] ) ? trim( $row['bio'] ) : '',
			'image_id' => $image_id,
			'socials'  => $socials,
		);
	}

	return $members;
}
