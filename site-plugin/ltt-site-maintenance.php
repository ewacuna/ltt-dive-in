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
 * Sanitize a camera URL against the explicitly approved host.
 *
 * @param mixed $value Submitted URL.
 * @return string
 */
function ltt_site_maintenance_sanitize_camera_url( $value ) {
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
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_host', array( 'type' => 'string', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_host', 'default' => '' ) );
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_embed_url', array( 'type' => 'string', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_url', 'default' => '' ) );
	register_setting( 'ltt_site_maintenance', 'ltt_site_maintenance_camera_public_url', array( 'type' => 'string', 'sanitize_callback' => 'ltt_site_maintenance_sanitize_camera_url', 'default' => '' ) );
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
 * Render the settings page.
 */
function ltt_site_maintenance_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
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
					<th scope="row"><label for="ltt-site-maintenance-camera-host"><?php esc_html_e( 'Approved camera host', 'ltt-site-maintenance' ); ?></label></th>
					<td><input id="ltt-site-maintenance-camera-host" type="text" class="regular-text" name="ltt_site_maintenance_camera_host" value="<?php echo esc_attr( (string) get_option( 'ltt_site_maintenance_camera_host', '' ) ); ?>" placeholder="embed.example.com"><p class="description"><?php esc_html_e( 'Enter the exact domain authorized by the camera provider; leave blank until confirmed.', 'ltt-site-maintenance' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="ltt-site-maintenance-camera-embed"><?php esc_html_e( 'Camera embed URL', 'ltt-site-maintenance' ); ?></label></th>
					<td><input id="ltt-site-maintenance-camera-embed" type="url" class="large-text" name="ltt_site_maintenance_camera_embed_url" value="<?php echo esc_attr( (string) get_option( 'ltt_site_maintenance_camera_embed_url', '' ) ); ?>" placeholder="https://"><p class="description"><?php esc_html_e( 'Use only the provider-approved iframe URL.', 'ltt-site-maintenance' ); ?></p></td>
				</tr>
				<tr>
					<th scope="row"><label for="ltt-site-maintenance-camera-public"><?php esc_html_e( 'Camera public URL', 'ltt-site-maintenance' ); ?></label></th>
					<td><input id="ltt-site-maintenance-camera-public" type="url" class="large-text" name="ltt_site_maintenance_camera_public_url" value="<?php echo esc_attr( (string) get_option( 'ltt_site_maintenance_camera_public_url', '' ) ); ?>" placeholder="https://"><p class="description"><?php esc_html_e( 'Optional link for visitors when the embed is blocked.', 'ltt-site-maintenance' ); ?></p></td>
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
 * Supply the theme with one configured camera URL.
 *
 * @param string $current Current URL.
 * @return string
 */
function ltt_site_maintenance_camera_embed_filter( $current ) {
	return get_option( 'ltt_site_maintenance_camera_embed_url', '' ) ?: $current;
}
add_filter( 'ltt_dive_in_maintenance_camera_embed_url', 'ltt_site_maintenance_camera_embed_filter' );

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
