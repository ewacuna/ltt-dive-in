<?php
/**
 * Latest images for the Current Conditions live-cameras tab.
 *
 * A camera may have an official latest-image URL from its provider. WP-Cron
 * downloads each URL every five minutes, validates it, and stores a copy in
 * the uploads directory. Page loads only read that copy, so visitors never
 * request the provider and never wait on it. When no recent copy exists, the
 * camera shows its editor-uploaded still instead.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Longest a downloaded image may be shown after it was fetched.
 */
define( 'LTT_DIVE_IN_LIVE_CAM_STALE_TTL', HOUR_IN_SECONDS );

/**
 * Largest image the refresh accepts, in bytes.
 */
define( 'LTT_DIVE_IN_LIVE_CAM_MAX_BYTES', 5 * MB_IN_BYTES );

/**
 * Uploads subdirectory that holds the downloaded images.
 */
define( 'LTT_DIVE_IN_LIVE_CAM_DIR', 'ltt-dive-in-live-cams' );

/**
 * Normalize an editor-entered latest-image URL.
 *
 * @param mixed $url Raw field value.
 * @return string HTTPS URL, or an empty string when the value is unusable.
 */
function ltt_dive_in_sanitize_live_cam_image_url( $url ) {
	$url = is_string( $url ) ? esc_url_raw( trim( $url ), array( 'https' ) ) : '';

	return $url && wp_http_validate_url( $url ) ? $url : '';
}

/**
 * Cache key for one latest-image URL.
 *
 * @param string $url Latest-image URL.
 * @return string
 */
function ltt_dive_in_live_cam_cache_key( $url ) {
	return 'ltt_dive_in_live_cam_' . md5( $url );
}

/**
 * Get the current downloaded copy of a latest-image URL.
 *
 * Queues a refresh when nothing usable is cached.
 *
 * @param string $url Sanitized latest-image URL.
 * @return array{src: string, width: int, height: int, updated: int}|null
 */
function ltt_dive_in_get_live_cam_image( $url ) {
	if ( ! $url ) {
		return null;
	}

	$cached  = get_transient( ltt_dive_in_live_cam_cache_key( $url ) );
	$uploads = wp_get_upload_dir();

	if (
		! is_array( $cached ) || empty( $cached['file'] ) || empty( $cached['updated'] ) ||
		time() - (int) $cached['updated'] > LTT_DIVE_IN_LIVE_CAM_STALE_TTL ||
		! file_exists( trailingslashit( $uploads['basedir'] ) . $cached['file'] )
	) {
		if ( ! is_array( $cached ) || empty( $cached['error'] ) ) {
			ltt_dive_in_queue_live_cams_refresh();
		}

		return null;
	}

	return array(
		'src'     => add_query_arg( 'v', (int) $cached['updated'], trailingslashit( $uploads['baseurl'] ) . $cached['file'] ),
		'width'   => (int) $cached['width'],
		'height'  => (int) $cached['height'],
		'updated' => (int) $cached['updated'],
	);
}

/**
 * Queue a background refresh unless one is already pending.
 */
function ltt_dive_in_queue_live_cams_refresh() {
	if ( ! wp_next_scheduled( 'ltt_dive_in_refresh_live_cams' ) ) {
		wp_schedule_single_event( time(), 'ltt_dive_in_refresh_live_cams' );
	}
}

/**
 * Collect the latest-image URLs of the published live-cameras tab.
 *
 * @return string[] Unique sanitized URLs.
 */
function ltt_dive_in_get_live_cam_image_urls() {
	$page_id = ltt_dive_in_get_live_cams_tab_page_id();

	if ( ! $page_id || ! function_exists( 'get_field' ) ) {
		return array();
	}

	$rows = get_field( 'ltt_dive_in_live_cams', $page_id );
	$urls = array();

	foreach ( is_array( $rows ) ? array_slice( $rows, 0, 4 ) : array() as $row ) {
		$url = ltt_dive_in_sanitize_live_cam_image_url( $row['image_url'] ?? '' );

		if ( $url ) {
			$urls[ $url ] = $url;
		}
	}

	return array_values( $urls );
}

/**
 * Download every latest-image URL and remove copies that are no longer used.
 *
 * A failed download keeps the previous copy until it goes stale; when there is
 * none, the failure is cached briefly so the provider is not retried on every
 * page load.
 */
function ltt_dive_in_refresh_live_cams() {
	if ( get_transient( 'ltt_dive_in_live_cams_refreshing' ) ) {
		return;
	}

	set_transient( 'ltt_dive_in_live_cams_refreshing', 1, 2 * MINUTE_IN_SECONDS );

	$uploads = wp_get_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . LTT_DIVE_IN_LIVE_CAM_DIR;
	$keep    = array();

	if ( wp_mkdir_p( $dir ) ) {
		foreach ( ltt_dive_in_get_live_cam_image_urls() as $url ) {
			$key    = ltt_dive_in_live_cam_cache_key( $url );
			$result = ltt_dive_in_download_live_cam_image( $url, $dir );

			if ( ! is_wp_error( $result ) ) {
				$keep[] = $result['name'];
				set_transient(
					$key,
					array(
						'file'    => LTT_DIVE_IN_LIVE_CAM_DIR . '/' . $result['name'],
						'width'   => $result['width'],
						'height'  => $result['height'],
						'updated' => time(),
					),
					LTT_DIVE_IN_LIVE_CAM_STALE_TTL
				);
				continue;
			}

			$cached = get_transient( $key );

			if ( is_array( $cached ) && ! empty( $cached['file'] ) ) {
				$keep[] = basename( $cached['file'] );
			} else {
				set_transient( $key, array( 'error' => $result->get_error_code() ), 5 * MINUTE_IN_SECONDS );
			}
		}

		foreach ( (array) glob( trailingslashit( $dir ) . '*' ) as $file ) {
			if ( $file && preg_match( '/\.(jpg|png|webp|tmp)$/', $file ) && ! in_array( basename( $file ), $keep, true ) ) {
				wp_delete_file( $file );
			}
		}
	}

	delete_transient( 'ltt_dive_in_live_cams_refreshing' );
}
add_action( 'ltt_dive_in_refresh_live_cams', 'ltt_dive_in_refresh_live_cams' );

/**
 * Download one latest-image URL into the live-cameras directory.
 *
 * Only JPEG, PNG, and WebP images up to LTT_DIVE_IN_LIVE_CAM_MAX_BYTES are kept.
 * The file is written to a temporary name and then renamed, so a visitor never
 * receives a partly written image.
 *
 * @param string $url Sanitized latest-image URL.
 * @param string $dir Absolute destination directory.
 * @return array{name: string, width: int, height: int}|WP_Error
 */
function ltt_dive_in_download_live_cam_image( $url, $dir ) {
	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'             => 15,
			'redirection'         => 2,
			'limit_response_size' => LTT_DIVE_IN_LIVE_CAM_MAX_BYTES + 1,
			'user-agent'          => 'LTT Dive In WordPress theme/' . LTT_DIVE_IN_VERSION . '; ' . home_url( '/' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'ltt_dive_in_live_cam_request_failed' );
	}

	if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'ltt_dive_in_live_cam_http_error' );
	}

	$body = wp_remote_retrieve_body( $response );

	if ( '' === $body || strlen( $body ) > LTT_DIVE_IN_LIVE_CAM_MAX_BYTES ) {
		return new WP_Error( 'ltt_dive_in_live_cam_invalid_size' );
	}

	$info       = function_exists( 'getimagesizefromstring' ) ? getimagesizefromstring( $body ) : false;
	$extensions = array(
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_PNG  => 'png',
		IMAGETYPE_WEBP => 'webp',
	);

	if ( ! $info || ! isset( $extensions[ $info[2] ] ) || 1 > $info[0] || 1 > $info[1] ) {
		return new WP_Error( 'ltt_dive_in_live_cam_not_an_image' );
	}

	$name = md5( $url ) . '.' . $extensions[ $info[2] ];
	$path = trailingslashit( $dir ) . $name;
	$temp = $path . '.tmp';

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Runs in WP-Cron, where WP_Filesystem may need credentials.
	if ( false === file_put_contents( $temp, $body ) ) {
		return new WP_Error( 'ltt_dive_in_live_cam_write_failed' );
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename -- Atomic replacement of the previous copy.
	if ( ! rename( $temp, $path ) ) {
		wp_delete_file( $temp );

		return new WP_Error( 'ltt_dive_in_live_cam_write_failed' );
	}

	return array(
		'name'   => $name,
		'width'  => (int) $info[0],
		'height' => (int) $info[1],
	);
}

/**
 * Keep the recurring refresh scheduled only while a live-cameras tab has a latest-image URL.
 *
 * Uses the five-minute interval registered in inc/road-conditions.php.
 */
function ltt_dive_in_schedule_live_cams_refresh() {
	$scheduled = wp_next_scheduled( 'ltt_dive_in_refresh_live_cams_recurring' );

	if ( ltt_dive_in_get_live_cam_image_urls() ) {
		if ( ! $scheduled ) {
			wp_schedule_event( time(), 'ltt_dive_in_five_minutes', 'ltt_dive_in_refresh_live_cams_recurring' );
		}
	} elseif ( $scheduled ) {
		wp_clear_scheduled_hook( 'ltt_dive_in_refresh_live_cams_recurring' );
	}
}
add_action( 'init', 'ltt_dive_in_schedule_live_cams_refresh' );
add_action( 'ltt_dive_in_refresh_live_cams_recurring', 'ltt_dive_in_refresh_live_cams' );

/**
 * Remove the live-cameras refresh events when the theme is switched off.
 */
function ltt_dive_in_unschedule_live_cams_refresh() {
	wp_clear_scheduled_hook( 'ltt_dive_in_refresh_live_cams_recurring' );
	wp_clear_scheduled_hook( 'ltt_dive_in_refresh_live_cams' );
}
add_action( 'switch_theme', 'ltt_dive_in_unschedule_live_cams_refresh' );

/**
 * Get the published Current Conditions page that shows the live-cameras module.
 *
 * @return int Page ID, or 0 when no live-cameras tab is set up.
 */
function ltt_dive_in_get_live_cams_tab_page_id() {
	$page_id = get_transient( 'ltt_dive_in_live_cams_tab_page_id' );

	if ( false === $page_id ) {
		$pages   = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cached in a transient that is cleared whenever a page is saved.
				'meta_query'     => array(
					array(
						'key'   => '_wp_page_template',
						'value' => 'templates/current-conditions.php',
					),
					array(
						'key'   => 'ltt_dive_in_conditions_tab_module',
						'value' => 'cams',
					),
				),
			)
		);
		$page_id = $pages ? (int) $pages[0] : 0;

		set_transient( 'ltt_dive_in_live_cams_tab_page_id', $page_id, DAY_IN_SECONDS );
	}

	return (int) $page_id;
}

/**
 * Forget the cached live-cameras tab whenever a page changes.
 */
function ltt_dive_in_clear_live_cams_tab_page_cache() {
	delete_transient( 'ltt_dive_in_live_cams_tab_page_id' );
}
add_action( 'save_post_page', 'ltt_dive_in_clear_live_cams_tab_page_cache' );
add_action( 'deleted_post', 'ltt_dive_in_clear_live_cams_tab_page_cache' );

/**
 * Download new images as soon as a live-cameras tab is saved.
 *
 * Runs after ACF has stored the fields, so the refresh sees the new URLs.
 *
 * @param int|string $post_id Saved post ID, or an ACF options identifier.
 */
function ltt_dive_in_refresh_live_cams_after_save( $post_id ) {
	if ( is_numeric( $post_id ) && 'page' === get_post_type( (int) $post_id ) && 'cams' === get_post_meta( (int) $post_id, 'ltt_dive_in_conditions_tab_module', true ) ) {
		delete_transient( 'ltt_dive_in_live_cams_tab_page_id' );
		ltt_dive_in_queue_live_cams_refresh();
	}
}
add_action( 'acf/save_post', 'ltt_dive_in_refresh_live_cams_after_save', 20 );
