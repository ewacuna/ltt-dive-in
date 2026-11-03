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

$camera_embed_url  = ltt_dive_in_get_maintenance_camera_url( 'embed' );
$camera_public_url = ltt_dive_in_get_maintenance_camera_url( 'public' );
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
			<?php if ( $camera_embed_url ) : ?>
				<br><?php esc_html_e( 'Take a moment to take a break or watch', 'ltt-dive-in' ); ?>
				<br><?php esc_html_e( 'a live feed from one of our live cams below:', 'ltt-dive-in' ); ?>
			<?php endif; ?>
		</p>
		<?php if ( $camera_embed_url ) : ?>
			<div class="ltt-error-page__camera">
				<iframe src="<?php echo esc_url( $camera_embed_url ); ?>" title="<?php esc_attr_e( 'Lake Tahoe live camera', 'ltt-dive-in' ); ?>" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
			</div>
		<?php else : ?>
			<div class="ltt-error-page__camera ltt-error-page__camera--unavailable">
				<p><?php esc_html_e( 'The live camera is temporarily unavailable. Please check back soon.', 'ltt-dive-in' ); ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $camera_public_url ) : ?>
			<a class="ltt-error-page__camera-link" href="<?php echo esc_url( $camera_public_url ); ?>"><?php esc_html_e( 'Open the live camera', 'ltt-dive-in' ); ?></a>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
