<?php
/**
 * Controlled-maintenance presentation.
 *
 * A site plugin selects this template and sends the HTTP 503 response.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$camera_public_url = ltt_dive_in_get_maintenance_camera_url();
$prism_camera      = ltt_dive_in_get_maintenance_prism_camera();
$prism_assets      = $prism_camera ? ltt_dive_in_get_prism_assets() : array();
$camera_image_id   = ltt_dive_in_get_maintenance_camera_image_id();
$camera_image      = $camera_public_url && $camera_image_id ? wp_get_attachment_image(
	$camera_image_id,
	'large',
	false,
	array(
		'class' => 'ltt-error-page__camera-image',
		'sizes' => '(max-width: 767.98px) 265px, 596px',
	)
) : '';
$background_config = ltt_dive_in_get_error_background_config( '503' );

get_header();
?>
<main id="primary" class="site-main ltt-error-page ltt-error-page--503"
	<?php if ( $background_config['images'] ) : ?>
		data-background-images
		data-background-interval="<?php echo esc_attr( $background_config['interval'] ); ?>"
	<?php endif; ?>
>
	<?php if ( $background_config['images'] ) : ?>
		<?php get_template_part( 'template-parts/components/error-background', null, array( 'images' => $background_config['images'] ) ); ?>
	<?php endif; ?>
	<div class="ltt-error-page__intro">
		<h1 class="ltt-error-page__title"><?php esc_html_e( 'Great things are on the horizon...', 'ltt-dive-in' ); ?></h1>
	</div>
	<div class="ltt-error-page__content">
		<p class="ltt-error-page__message">
			<?php esc_html_e( 'Our site is down for momentarily.', 'ltt-dive-in' ); ?>
			<?php if ( $camera_public_url || $prism_camera ) : ?>
				<br><?php esc_html_e( 'Take a moment to take a break or watch', 'ltt-dive-in' ); ?>
				<br><?php esc_html_e( 'a live feed from one of our live cams below:', 'ltt-dive-in' ); ?>
			<?php endif; ?>
		</p>
		<?php if ( $camera_image || $prism_camera ) : ?>
			<div class="ltt-error-page__camera ltt-error-page__camera--preview<?php echo $prism_camera ? ' ltt-error-page__camera--viewer' : ''; ?>"
				<?php if ( $prism_camera ) : ?>
					data-prism-viewer
					data-prism-script="<?php echo esc_url( $prism_assets['script'] ); ?>"
					data-prism-style="<?php echo esc_url( $prism_assets['style'] ); ?>"
				<?php endif; ?>
			>
				<?php if ( $camera_image ) : ?>
					<a class="ltt-error-page__camera-media" href="<?php echo esc_url( $camera_public_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo $camera_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image(). ?>
						<span class="ltt-error-page__camera-play" aria-hidden="true">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/video-play.svg' ) ); ?>" width="96" height="111" alt="" />
						</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Open the Lake Tahoe live camera (opens in a new tab)', 'ltt-dive-in' ); ?></span>
					</a>
				<?php else : ?>
					<div class="ltt-error-page__camera-placeholder">
						<p><?php esc_html_e( 'The live camera is temporarily unavailable. Please check back soon.', 'ltt-dive-in' ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( $prism_camera ) : ?>
					<div class="ltt-error-page__camera-prism" data-prism-embed role="group" aria-label="<?php esc_attr_e( 'Lake Tahoe live camera', 'ltt-dive-in' ); ?>">
						<div class="luma-<?php echo esc_attr( $prism_camera['client'] ); ?>" cam="<?php echo esc_attr( $prism_camera['cam'] ); ?>" data-mode="embed"></div>
					</div>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<div class="ltt-error-page__camera ltt-error-page__camera--unavailable">
				<p><?php esc_html_e( 'The live camera is temporarily unavailable. Please check back soon.', 'ltt-dive-in' ); ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $camera_public_url && ! $camera_image ) : ?>
			<a class="ltt-error-page__camera-link" href="<?php echo esc_url( $camera_public_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open the live camera', 'ltt-dive-in' ); ?> <span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?></span></a>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
