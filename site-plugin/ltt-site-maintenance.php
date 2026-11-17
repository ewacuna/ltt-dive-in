<?php
/**
 * Plugin Name: LTT Site Maintenance
 * Description: Serves the theme's 503 view during controlled maintenance.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize the maintenance switch.
 *
 * @param mixed $value Submitted value.
 * @return int
 */
function ltt_site_maintenance_sanitize_enabled( $value ) {
	return empty( $value ) ? 0 : 1;
}

/**
 * Sanitize Retry-After in seconds.
 *
 * @param mixed $value Submitted value.
 * @return int
 */
function ltt_site_maintenance_sanitize_retry_after( $value ) {
	return min( 86400, max( 60, absint( $value ) ) );
}

/**
 * Sanitize the camera presentation mode.
 *
 * @param mixed $value Submitted mode.
 * @return string
 */
function ltt_site_maintenance_sanitize_camera_viewer( $value ) {
	return 'prism' === $value ? 'prism' : 'image';
}

/**
 * Sanitize the camera number from the provider's Prism embed code.
 *
 * @param mixed $value Submitted camera ID.
 * @return int
 */
function ltt_site_maintenance_sanitize_prism_cam( $value ) {
	if ( null === $value ) {
		return get_option( 'ltt_site_maintenance_prism_cam', 0 );
	}

	if ( '' === $value || '0' === $value || 0 === $value ) {
		return 0;
	}

	$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
	if ( false === $id ) {
		add_settings_error( 'ltt_site_maintenance_prism_cam', 'invalid_prism_cam', __( 'Enter a positive whole number for the Prism camera ID.', 'ltt-site-maintenance' ) );
		return 0;
	}

	return (int) $id;
}

/**
 * Sanitize a camera URL against the explicitly approved host.
 *
 * @param mixed $value Submitted URL.
 * @return string
 */
function ltt_site_maintenance_sanitize_camera_url( $value ) {
	if ( null === $value ) {
		return get_option( 'ltt_site_maintenance_camera_public_url', '' );
	}

	$url     = esc_url_raw( trim( (string) $value ) );
	$host    = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$scheme  = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
	$allowed = strtolower( (string) get_option( 'ltt_site_maintenance_camera_host', '' ) );
	$user    = wp_parse_url( $url, PHP_URL_USER );
	$pass    = wp_parse_url( $url, PHP_URL_PASS );
	$port    = wp_parse_url( $url, PHP_URL_PORT );

	if ( '' === $url ) {
		return '';
	}

	if ( 'https' !== $scheme || ! $allowed || $host !== $allowed || $user || $pass || ( $port && 443 !== $port ) ) {
		add_settings_error( 'ltt_site_maintenance_camera', 'invalid_camera_url', __( 'The camera URL must use HTTPS and the approved host.', 'ltt-site-maintenance' ) );
		return '';
	}

	return $url;
}

/**
 * Sanitize the exact host authorized for the camera URLs.
 *
 * @param mixed $value Submitted host.
 * @return string
 */
function ltt_site_maintenance_sanitize_camera_host( $value ) {
	if ( null === $value ) {
		return get_option( 'ltt_site_maintenance_camera_host', '' );
	}

	$host = strtolower( trim( (string) $value ) );

	if ( '' === $host ) {
		return '';
	}

	if ( ! preg_match( '/^(?=.{1,253}$)[a-z0-9](?:[a-z0-9.-]*[a-z0-9])$/', $host ) || false === strpos( $host, '.' ) || false !== strpos( $host, '..' ) || preg_match( '/\.(?:local|localhost|internal|test|invalid)$/', $host ) || filter_var( $host, FILTER_VALIDATE_IP ) ) {
		add_settings_error( 'ltt_site_maintenance_camera_host', 'invalid_camera_host', __( 'Enter an approved public domain, without a scheme or path.', 'ltt-site-maintenance' ) );
		return '';
	}

	return $host;
}

/**
 * Validate a maintenance camera attachment.
 *
 * @param mixed $value Attachment ID.
 * @return int
 */
function ltt_site_maintenance_validate_camera_image( $value ) {
	$id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
	$attachment = false !== $id ? get_post( $id ) : null;

	if ( ! $attachment || 'attachment' !== $attachment->post_type || 'inherit' !== $attachment->post_status || ! in_array( get_post_mime_type( $id ), array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
		return 0;
	}

	return (int) $id;
}

/**
 * Read the cover, preserving the previous ACF image until the first native save.
 *
 * @return int
 */
function ltt_site_maintenance_get_camera_image_id() {
	$value = get_option( 'ltt_site_maintenance_camera_image', -1 );
	if ( -1 === $value ) {
		$value = get_option( 'options_ltt_dive_in_error_503_camera_image', 0 );
	}

	return ltt_site_maintenance_validate_camera_image( $value );
}

/**
 * Sanitize the native Media Library selection, retaining inactive mode values.
 *
 * @param mixed $value Submitted attachment ID, or null for an inactive field.
 * @return int
 */
function ltt_site_maintenance_sanitize_camera_image( $value ) {
	if ( null === $value ) {
		return ltt_site_maintenance_get_camera_image_id();
	}
	if ( '' === $value || '0' === $value || 0 === $value ) {
		return 0;
	}

	$id = ltt_site_maintenance_validate_camera_image( $value );
	if ( ! $id ) {
		add_settings_error( 'ltt_site_maintenance_camera_image', 'invalid_camera_image', __( 'Choose a JPG, PNG, or WebP image from the Media Library.', 'ltt-site-maintenance' ) );
		return ltt_site_maintenance_get_camera_image_id();
	}

	return $id;
}

/**
 * Register maintenance settings.
 */
function ltt_site_maintenance_register_settings() {
	register_setting(
		'ltt_site_maintenance',
		'ltt_site_maintenance_enabled',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'ltt_site_maintenance_sanitize_enabled',
			'default'           => 0,
		)
	);
	register_setting(
		'ltt_site_maintenance',
		'ltt_site_maintenance_retry_after',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'ltt_site_maintenance_sanitize_retry_after',
			'default'           => 600,
		)
	);
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_image', array( 'type' => 'integer', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_image', 'default' => 0 ) );
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_host', array( 'type' => 'string', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_host', 'default' => '' ) );
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_public_url', array( 'type' => 'string', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_url', 'default' => '' ) );
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_viewer', array( 'type' => 'string', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_viewer', 'default' => 'image' ) );
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_prism_cam', array( 'type' => 'integer', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_prism_cam', 'default' => 0 ) );
}
add_action( 'admin_init', 'ltt_site_maintenance_register_settings' );

/**
 * Add an administrator-only maintenance control.
 */
function ltt_site_maintenance_add_settings_page() {
	add_options_page(
		__( 'Site Maintenance', 'ltt-site-maintenance' ),
		__( 'Site Maintenance', 'ltt-site-maintenance' ),
		'manage_options',
		'ltt-site-maintenance',
		'ltt_site_maintenance_render_settings_page'
	);
}
add_action( 'admin_menu', 'ltt_site_maintenance_add_settings_page' );

/**
 * Load native media controls only on this plugin's settings page.
 *
 * @param string $hook Current administrator screen.
 */
function ltt_site_maintenance_enqueue_admin_assets( $hook ) {
	if ( 'settings_page_ltt-site-maintenance' !== $hook || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_media();
	$path = __DIR__ . '/ltt-site-maintenance-assets/admin.js';
	wp_enqueue_script( 'ltt-site-maintenance-admin', plugin_dir_url( __FILE__ ) . 'ltt-site-maintenance-assets/admin.js', array( 'media-views' ), file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0', true );
	wp_localize_script( 'ltt-site-maintenance-admin', 'lttSiteMaintenanceAdmin', array(
		'title' => __( 'Choose the camera cover image', 'ltt-site-maintenance' ),
		'choose' => __( 'Use this image', 'ltt-site-maintenance' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'ltt_site_maintenance_enqueue_admin_assets' );

/**
 * Render the settings page.
 */
function ltt_site_maintenance_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$prism_cam    = get_option( 'ltt_site_maintenance_prism_cam', 0 );
	$camera_image = ltt_site_maintenance_get_camera_image_id();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Site Maintenance', 'ltt-site-maintenance' ); ?></h1>
		<p><?php esc_html_e( 'When enabled, public pages show the 503 maintenance view. Administrators can still browse the site normally.', 'ltt-site-maintenance' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'ltt_site_maintenance' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Maintenance mode', 'ltt-site-maintenance' ); ?></th>
					<td>
						<label for="ltt-site-maintenance-enabled">
							<input type="hidden" name="ltt_site_maintenance_enabled" value="0">
							<input id="ltt-site-maintenance-enabled" type="checkbox" name="ltt_site_maintenance_enabled" value="1" <?php checked( 1, (int) get_option( 'ltt_site_maintenance_enabled', 0 ) ); ?>>
							<?php esc_html_e( 'Show the 503 page to visitors', 'ltt-site-maintenance' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ltt-site-maintenance-retry"><?php esc_html_e( 'Retry-After (seconds)', 'ltt-site-maintenance' ); ?></label></th>
					<td><input id="ltt-site-maintenance-retry" type="number" class="small-text" min="60" max="86400" step="1" name="ltt_site_maintenance_retry_after" value="<?php echo esc_attr( (string) get_option( 'ltt_site_maintenance_retry_after', 600 ) ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="ltt-site-maintenance-camera-viewer"><?php esc_html_e( 'Viewer', 'ltt-site-maintenance' ); ?></label></th>
					<td>
						<select id="ltt-site-maintenance-camera-viewer" name="ltt_site_maintenance_camera_viewer" aria-describedby="ltt-site-maintenance-camera-viewer-help">
							<option value="image" <?php selected( 'image', get_option( 'ltt_site_maintenance_camera_viewer', 'image' ) ); ?>><?php esc_html_e( 'Image', 'ltt-site-maintenance' ); ?></option>
							<option value="prism" <?php selected( 'prism', get_option( 'ltt_site_maintenance_camera_viewer', 'image' ) ); ?>><?php esc_html_e( 'Prism Cam', 'ltt-site-maintenance' ); ?></option>
						</select>
						<p class="description" id="ltt-site-maintenance-camera-viewer-help"><?php esc_html_e( 'Image opens the camera public URL. Prism Cam uses the camera ID. Your Image settings are kept as a fallback while Prism loads or is unavailable.', 'ltt-site-maintenance' ); ?></p>
					</td>
				</tr>
				<tr data-maintenance-viewer="image">
					<th scope="row"><label for="ltt-site-maintenance-camera-image"><?php esc_html_e( 'Camera cover image', 'ltt-site-maintenance' ); ?></label></th>
					<td>
						<input id="ltt-site-maintenance-camera-image" type="hidden" name="ltt_site_maintenance_camera_image" value="<?php echo esc_attr( (string) $camera_image ); ?>">
						<div id="ltt-site-maintenance-camera-preview" aria-live="polite">
							<?php if ( $camera_image ) : ?>
								<?php echo wp_get_attachment_image( $camera_image, 'thumbnail' ); ?>
							<?php endif; ?>
						</div>
						<button type="button" class="button" data-maintenance-select-image aria-controls="ltt-site-maintenance-camera-preview" aria-describedby="ltt-site-maintenance-camera-image-help"><?php esc_html_e( 'Choose image', 'ltt-site-maintenance' ); ?></button>
						<button type="button" class="button<?php echo $camera_image ? '' : ' hidden'; ?>" data-maintenance-remove-image<?php echo $camera_image ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove image', 'ltt-site-maintenance' ); ?></button>
						<p class="description" id="ltt-site-maintenance-camera-image-help"><?php esc_html_e( 'Choose a landscape JPG, PNG, or WebP cover. Describe the view in its Media Library alt text. This image is also used as the Prism Cam fallback.', 'ltt-site-maintenance' ); ?></p>
						<noscript><p><?php esc_html_e( 'Enable JavaScript to choose or remove the cover image. Your saved image is kept.', 'ltt-site-maintenance' ); ?></p></noscript>
					</td>
				</tr>
				<tr data-maintenance-viewer="prism">
					<th scope="row"><label for="ltt-site-maintenance-prism-cam"><?php esc_html_e( 'Prism camera ID', 'ltt-site-maintenance' ); ?></label></th>
					<td>
						<input id="ltt-site-maintenance-prism-cam" type="number" class="small-text" min="1" step="1" name="ltt_site_maintenance_prism_cam" value="<?php echo esc_attr( $prism_cam ? (string) $prism_cam : '' ); ?>" aria-describedby="ltt-site-maintenance-prism-cam-help">
						<p class="description" id="ltt-site-maintenance-prism-cam-help"><?php esc_html_e( 'Used only with Prism Cam. Enter the cam number from the provider-approved embed code for the same Prism partner account used by Live Cams. Without a valid ID, the cover or unavailable message is shown.', 'ltt-site-maintenance' ); ?></p>
					</td>
				</tr>
				<tr data-maintenance-viewer="image">
					<th scope="row"><label for="ltt-site-maintenance-camera-host"><?php esc_html_e( 'Approved camera host', 'ltt-site-maintenance' ); ?></label></th>
					<td><input id="ltt-site-maintenance-camera-host" type="text" class="regular-text" name="ltt_site_maintenance_camera_host" value="<?php echo esc_attr( (string) get_option( 'ltt_site_maintenance_camera_host', '' ) ); ?>" placeholder="embed.example.com"><p class="description"><?php esc_html_e( 'Enter the exact domain authorized by the camera provider; leave blank until confirmed.', 'ltt-site-maintenance' ); ?></p></td>
				</tr>
				<tr data-maintenance-viewer="image">
					<th scope="row"><label for="ltt-site-maintenance-camera-public"><?php esc_html_e( 'Camera public URL', 'ltt-site-maintenance' ); ?></label></th>
					<td><input id="ltt-site-maintenance-camera-public" type="url" class="large-text" name="ltt_site_maintenance_camera_public_url" value="<?php echo esc_attr( (string) get_option( 'ltt_site_maintenance_camera_public_url', '' ) ); ?>" placeholder="https://"><p class="description"><?php esc_html_e( 'Destination for the cover image and fallback link. Must use HTTPS and the approved camera host.', 'ltt-site-maintenance' ); ?></p></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<p><a href="<?php echo esc_url( add_query_arg( 'ltt_maintenance_preview', '1', home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Preview the 503 page', 'ltt-site-maintenance' ); ?></a></p>
	</div>
	<?php
}

/**
 * Share the current rendering state with the theme.
 *
 * @param bool $current Current state from another integration.
 * @return bool
 */
function ltt_site_maintenance_is_rendering( $current = false ) {
	return (bool) $current || ! empty( $GLOBALS['ltt_site_maintenance_rendering'] );
}
add_filter( 'ltt_dive_in_is_503_view', 'ltt_site_maintenance_is_rendering' );

/**
 * Supply the theme with the configured camera host and URLs.
 *
 * @param mixed $current Value from another integration.
 * @return mixed
 */
function ltt_site_maintenance_camera_host_filter( $current ) {
	$host = strtolower( trim( (string) get_option( 'ltt_site_maintenance_camera_host', '' ) ) );
	return $host ? array_merge( (array) $current, array( $host ) ) : $current;
}
add_filter( 'ltt_dive_in_maintenance_camera_allowed_hosts', 'ltt_site_maintenance_camera_host_filter' );

/**
 * Supply the theme with the camera cover without requiring ACF.
 *
 * @param int $current Current attachment ID.
 * @return int
 */
function ltt_site_maintenance_camera_image_filter( $current ) {
	return ltt_site_maintenance_get_camera_image_id();
}
add_filter( 'ltt_dive_in_maintenance_camera_image', 'ltt_site_maintenance_camera_image_filter' );

/**
 * Supply the theme with the public camera link.
 *
 * @param string $current Current URL.
 * @return string
 */
function ltt_site_maintenance_camera_public_filter( $current ) {
	return get_option( 'ltt_site_maintenance_camera_public_url', '' ) ?: $current;
}
add_filter( 'ltt_dive_in_maintenance_camera_public_url', 'ltt_site_maintenance_camera_public_filter' );

/**
 * Supply the selected camera presentation mode to the theme.
 *
 * @param string $current Current mode.
 * @return string
 */
function ltt_site_maintenance_camera_viewer_filter( $current ) {
	return ltt_site_maintenance_sanitize_camera_viewer( get_option( 'ltt_site_maintenance_camera_viewer', $current ) );
}
add_filter( 'ltt_dive_in_maintenance_camera_viewer', 'ltt_site_maintenance_camera_viewer_filter' );

/**
 * Supply the saved Prism camera ID to the theme for runtime validation.
 *
 * @param int $current Current camera ID.
 * @return mixed
 */
function ltt_site_maintenance_prism_cam_filter( $current ) {
	return get_option( 'ltt_site_maintenance_prism_cam', $current );
}
add_filter( 'ltt_dive_in_maintenance_prism_cam', 'ltt_site_maintenance_prism_cam_filter' );

/**
 * Serve the controlled-maintenance template for public requests.
 */
function ltt_site_maintenance_maybe_render() {
	$preview = isset( $_GET['ltt_maintenance_preview'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['ltt_maintenance_preview'] ) ) && current_user_can( 'manage_options' );
	$enabled = (bool) get_option( 'ltt_site_maintenance_enabled', 0 );

	if ( ! $preview && ( ! $enabled || current_user_can( 'manage_options' ) ) ) {
		return;
	}

	$template = get_theme_file_path( '503.php' );

	if ( ! is_readable( $template ) ) {
		status_header( 503 );
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		header( 'Retry-After: ' . ltt_site_maintenance_sanitize_retry_after( get_option( 'ltt_site_maintenance_retry_after', 600 ) ) );
		wp_die( esc_html__( 'The site is temporarily unavailable. Please check back soon.', 'ltt-site-maintenance' ), esc_html__( 'Temporarily unavailable', 'ltt-site-maintenance' ), array( 'response' => 503 ) );
	}

	$GLOBALS['ltt_site_maintenance_rendering'] = true;

	status_header( 503 );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
	header( 'Retry-After: ' . ltt_site_maintenance_sanitize_retry_after( get_option( 'ltt_site_maintenance_retry_after', 600 ) ) );

	require $template;
	exit;
}
add_action( 'template_redirect', 'ltt_site_maintenance_maybe_render', 0 );

/**
 * Give the maintenance document a meaningful browser title.
 *
 * @param string $title Current title.
 * @return string
 */
function ltt_site_maintenance_document_title( $title ) {
	return ltt_site_maintenance_is_rendering() ? __( 'Temporarily unavailable', 'ltt-site-maintenance' ) : $title;
}
add_filter( 'pre_get_document_title', 'ltt_site_maintenance_document_title' );
