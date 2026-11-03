<?php
/**
 * Error-page helpers.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a site integration is rendering the maintenance template.
 *
 * @return bool
 */
function ltt_dive_in_is_503_view() {
	return (bool) apply_filters( 'ltt_dive_in_is_503_view', false );
}

/**
 * Get a configured, approved external camera URL.
 *
 * The embed stays unavailable until a site integration supplies an exact
 * allowed host and an HTTPS URL. Site administrators must verify that the
 * approved host belongs to the external camera provider.
 *
 * @param string $kind Either embed or public.
 * @return string
 */
function ltt_dive_in_get_maintenance_camera_url( $kind = 'embed' ) {
	$filter = 'public' === $kind
		? 'ltt_dive_in_maintenance_camera_public_url'
		: 'ltt_dive_in_maintenance_camera_embed_url';
	$url    = trim( (string) apply_filters( $filter, '' ) );
	$hosts  = (array) apply_filters( 'ltt_dive_in_maintenance_camera_allowed_hosts', array() );
	$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
	$user   = (string) wp_parse_url( $url, PHP_URL_USER );
	$pass   = (string) wp_parse_url( $url, PHP_URL_PASS );
	$port   = wp_parse_url( $url, PHP_URL_PORT );
	$hosts  = array_map( 'strtolower', array_filter( array_map( 'strval', $hosts ) ) );

	if ( 'https' !== $scheme || ! $host || ! in_array( $host, $hosts, true ) || $user || $pass || ( $port && 443 !== $port ) ) {
		return '';
	}

	return esc_url_raw( $url );
}

/**
 * Register the editorial settings for system-page backgrounds.
 *
 * The field group is defined once in acf-json/group_ltt_dive_in_error_backgrounds.json.
 */
function ltt_dive_in_register_error_background_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Page Not Found & Maintenance Background Images', 'ltt-dive-in' ),
			'menu_title'  => __( '404 & Maintenance Images', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-error-backgrounds',
			'parent_slug' => 'themes.php',
			'capability'  => 'edit_theme_options',
			'redirect'    => false,
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_error_background_settings' );

/**
 * Reject animated WebP and APNG attachments when their local file is available.
 *
 * @param int    $id Attachment ID.
 * @param string $mime Attachment MIME type.
 * @return bool
 */
function ltt_dive_in_is_static_error_background( $id, $mime ) {
	if ( 'image/jpeg' === $mime ) {
		return true;
	}

	$path = get_attached_file( $id );
	if ( ! $path || ! is_readable( $path ) ) {
		// Media-offload integrations may not retain the original file locally.
		return true;
	}

	$file = fopen( $path, 'rb' );
	if ( false === $file ) {
		return true;
	}

	if ( 'image/webp' === $mime ) {
		$header = fread( $file, 21 );
		fclose( $file );
		return ! ( 21 === strlen( $header ) && 'VP8X' === substr( $header, 12, 4 ) && ( ord( $header[20] ) & 2 ) );
	}

	// An APNG has an acTL chunk before its first IDAT chunk.
	$signature = fread( $file, 8 );
	if ( "\x89PNG\r\n\x1a\n" !== $signature ) {
		fclose( $file );
		return false;
	}

	$static = true;
	while ( ftell( $file ) < 262144 && ! feof( $file ) ) {
		$chunk = fread( $file, 8 );
		if ( 8 !== strlen( $chunk ) ) {
			break;
		}

		$length = unpack( 'Nlength', substr( $chunk, 0, 4 ) )['length'];
		$type   = substr( $chunk, 4, 4 );
		if ( 'acTL' === $type ) {
			$static = false;
			break;
		}
		if ( 'IDAT' === $type ) {
			break;
		}
		if ( 0 !== fseek( $file, $length + 4, SEEK_CUR ) ) {
			break;
		}
	}

	fclose( $file );
	return $static;
}

/**
 * Return validated image sources and interval for an error-page background.
 *
 * @param string $page Either 404 or 503.
 * @return array{images: array, interval: int}
 */
function ltt_dive_in_get_error_background_config( $page ) {
	$config = array(
		'images'   => array(),
		'interval' => 10,
	);

	if ( ! in_array( $page, array( '404', '503' ), true ) || ! function_exists( 'get_field' ) ) {
		return $config;
	}

	// The unformatted Gallery value is an ordered list of attachment IDs.
	$ids = get_field( 'ltt_dive_in_error_' . $page . '_background_images', 'option', false );
	if ( is_array( $ids ) ) {
		foreach ( array_slice( $ids, 0, 10 ) as $value ) {
			$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
			$attachment = false !== $id ? get_post( $id ) : null;
			if ( ! $attachment || 'attachment' !== $attachment->post_type || 'inherit' !== $attachment->post_status ) {
				continue;
			}

			$mime = get_post_mime_type( $id );
			if ( ! in_array( $mime, array( 'image/jpeg', 'image/png', 'image/webp' ), true ) || ! ltt_dive_in_is_static_error_background( $id, $mime ) ) {
				continue;
			}

			$src    = wp_get_attachment_image_url( $id, 'full' );
			$srcset = wp_get_attachment_image_srcset( $id, 'full' );
			$meta   = wp_get_attachment_metadata( $id );
			if ( ! $src || ! is_array( $meta ) || empty( $meta['width'] ) || empty( $meta['height'] ) ) {
				continue;
			}

			$config['images'][] = array(
				'src'    => esc_url_raw( $src ),
				'srcset' => $srcset ? (string) $srcset : '',
				'ratio'  => (float) $meta['width'] / (float) $meta['height'],
			);
		}
	}

	$interval = get_field( 'ltt_dive_in_error_' . $page . '_interval_seconds', 'option' );
	$valid_interval = filter_var( $interval, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 5, 'max_range' => 60 ) ) );
	if ( false !== $valid_interval ) {
		$config['interval'] = $valid_interval;
	}

	return $config;
}
