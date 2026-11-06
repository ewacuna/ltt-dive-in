<?php
/**
 * Road conditions highway card: status, report entries, and checkpoints.
 *
 * Details are expanded by default and stay readable without JavaScript; the
 * toggle is revealed only once the script can operate it.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card   = $args['card'] ?? array();
$id     = sanitize_html_class( (string) ( $args['id'] ?? '' ) );
$hidden = ! empty( $args['hidden'] );

if ( empty( $card['name'] ) || ! $id ) {
	return;
}

$labels = ltt_dive_in_get_road_status_labels();
$status = isset( $labels[ $card['status'] ] ) ? $card['status'] : 'none';
?>
<article class="road-card" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" data-road-highway="<?php echo esc_attr( $card['slug'] ); ?>"<?php echo 'clear' === $status ? ' data-road-card-collapsed' : ''; ?><?php echo $hidden ? ' hidden' : ''; ?>>
	<div class="road-card__header">
		<?php
		$sign = ltt_dive_in_get_road_sign_markup( $card['sign'] ?? 0 );

		if ( $sign ) {
			echo $sign; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by wp_get_attachment_image() or escaped in ltt_dive_in_get_road_sign_markup().
		} else {
			get_template_part(
				'template-parts/components/road-conditions/shield',
				null,
				array(
					'shield' => $card['shield'],
					'number' => $card['number'],
				)
			);
		}
		?>

		<div class="road-card__heading">
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="road-card__title"><?php echo esc_html( $card['name'] ); ?></h2>

			<?php if ( $card['subtitle'] ) : ?>
				<p class="road-card__subtitle"><?php echo esc_html( $card['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>

		<p class="road-card__status road-card__status--<?php echo esc_attr( $status ); ?>">
			<span class="screen-reader-text"><?php esc_html_e( 'Status:', 'ltt-dive-in' ); ?> </span><?php echo esc_html( $labels[ $status ] ); ?>
		</p>

		<button class="road-card__toggle" type="button" aria-expanded="true" aria-controls="<?php echo esc_attr( $id ); ?>-details" data-road-card-toggle hidden>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: highway name. */
				echo esc_html( sprintf( __( '%s details', 'ltt-dive-in' ), $card['name'] ) );
				?>
			</span>
			<img class="road-card__toggle-icon road-card__toggle-icon--expanded" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/expand-dark-expanded.svg' ) ); ?>" width="21" height="40" alt="" />
			<img class="road-card__toggle-icon road-card__toggle-icon--collapsed" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/expand-dark-collapsed.svg' ) ); ?>" width="21" height="40" alt="" />
		</button>
	</div>

	<div id="<?php echo esc_attr( $id ); ?>-details" class="road-card__details">
		<?php foreach ( $card['entries'] as $entry ) : ?>
			<div class="road-card__entry<?php echo ! empty( $entry['camera'] ) ? ' road-card__entry--has-camera' : ''; ?>">
				<div class="road-card__entry-copy">
					<h3 class="road-card__entry-title"><?php echo esc_html( $entry['title'] ); ?></h3>
					<p class="road-card__entry-text<?php echo $entry['muted'] ? ' road-card__entry-text--muted' : ''; ?>"><?php echo esc_html( $entry['text'] ); ?></p>
				</div>

				<?php if ( ! empty( $entry['camera'] ) ) : ?>
					<img class="road-card__camera" src="<?php echo esc_url( $entry['camera']['image'] ); ?>" alt="<?php echo esc_attr( $entry['camera']['alt'] ); ?>" width="144" height="114" loading="lazy" decoding="async" referrerpolicy="no-referrer" />
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<?php if ( $card['checkpoints'] ) : ?>
			<div class="road-card__entry road-card__entry--checkpoints">
				<h3 class="road-card__entry-title"><?php echo esc_html( $card['summary'] ); ?></h3>

				<ul class="road-card__rows">
					<?php foreach ( $card['checkpoints'] as $row ) : ?>
						<li class="road-card__row">
							<span class="road-card__row-label"><?php echo esc_html( $row['label'] ); ?></span>
							<span class="road-card__row-value"><?php echo esc_html( $row['value'] ); ?></span>

							<?php if ( $row['detail'] ) : ?>
								<span class="road-card__row-detail"><?php echo esc_html( $row['detail'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</article>
