<?php
/**
 * Current Conditions live-cameras module: a heading, description, and linked image per camera.
 *
 * The theme shows the newest downloaded image from the
 * camera's latest-image URL (inc/live-cams.php) or, without one, the editor's
 * still. The image links to the live feed and shows the Video Module play
 * icon on hover and focus (always on touch and small screens). The still's
 * alt text describes either image.
 * The tab feature renders the first camera on the tab background, then the
 * remaining cameras after it, each in its own band (`start` is their offset).
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cams  = $args['cams'] ?? array();
$start = absint( $args['start'] ?? 0 );

if ( ! $cams ) {
	return;
}
?>
<div class="live-cams<?php echo $start ? ' live-cams--bands' : ''; ?>">
	<?php foreach ( $cams as $index => $cam ) : ?>
		<?php
		$is_band = 0 < $start + $index;
		$id      = 'live-cam-' . ( $start + $index + 1 );
		$sizes   = $is_band ? '(max-width: 1058px) 100vw, 1058px' : '(max-width: 1220px) 100vw, 1220px';
		?>
		<section class="live-cam<?php echo $is_band ? ' live-cam--band' : ''; ?><?php echo $is_band && $cam['background'] ? ' live-cam--has-background' : ''; ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
			<?php
			if ( $is_band && $cam['background'] ) {
				echo wp_get_attachment_image(
					$cam['background'],
					'full',
					false,
					array(
						'class'   => 'live-cam__background',
						'alt'     => '',
						'sizes'   => '100vw',
						'loading' => 'lazy',
					)
				);
			}
			?>
			<div class="live-cam__container">
				<h2 id="<?php echo esc_attr( $id ); ?>-title" class="live-cam__title"><?php echo esc_html( $cam['heading'] ); ?></h2>

				<?php if ( $cam['text'] ) : ?>
					<p class="live-cam__text"><?php echo esc_html( $cam['text'] ); ?></p>
				<?php endif; ?>

				<a class="live-cam__media" href="<?php echo esc_url( $cam['url'] ); ?>"<?php echo $cam['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<?php if ( $cam['latest'] ) : ?>
						<img class="live-cam__image" src="<?php echo esc_url( $cam['latest']['src'] ); ?>" width="<?php echo esc_attr( $cam['latest']['width'] ); ?>" height="<?php echo esc_attr( $cam['latest']['height'] ); ?>" alt="<?php echo esc_attr( (string) get_post_meta( $cam['image'], '_wp_attachment_image_alt', true ) ); ?>"<?php echo $is_band ? ' loading="lazy"' : ''; ?> decoding="async" />
						<?php
					else :
						echo wp_get_attachment_image(
							$cam['image'],
							'full',
							false,
							array(
								'class'   => 'live-cam__image',
								'sizes'   => $sizes,
								'loading' => $is_band ? 'lazy' : false,
							)
						);
					endif;
					?>
					<span class="live-cam__play" aria-hidden="true">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/video-play.svg' ) ); ?>" width="96" height="111" alt="" />
					</span>
					<span class="screen-reader-text">
						<?php echo esc_html( $cam['label'] ); ?>
						<?php if ( $cam['new_tab'] ) : ?>
							<?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?>
						<?php endif; ?>
					</span>
				</a>
			</div>
		</section>
	<?php endforeach; ?>
</div>
