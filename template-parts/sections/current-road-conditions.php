<?php
/**
 * Current Conditions roads module: state and highway filters, then one card per highway.
 *
 * Both states render on the server. The filters are links and a GET form, so
 * they work without JavaScript; road-conditions.js switches views in place.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$highways = $args['highways'] ?? array();
$area     = $args['area'] ?? array();
$active   = (string) ( $args['state'] ?? '' );
$selected = (string) ( $args['highway'] ?? '' );
$states   = ltt_dive_in_get_road_states();

if ( ! $highways || ! $area || ! isset( $states[ $active ] ) ) {
	return;
}

$permalink = get_permalink();
$hls       = wp_scripts()->query( 'ltt-dive-in-hls' );
$hls_url   = $hls ? add_query_arg( 'ver', $hls->ver, $hls->src ) : '';
?>
<section id="road-conditions" class="road-conditions" aria-label="<?php esc_attr_e( 'Road conditions by highway', 'ltt-dive-in' ); ?>" data-road-conditions<?php echo $hls_url ? ' data-road-hls="' . esc_url( $hls_url ) . '"' : ''; ?>>
	<div class="road-conditions__filters">
		<p id="road-conditions-filter-label" class="road-conditions__filter-label"><?php esc_html_e( 'Filter by location:', 'ltt-dive-in' ); ?></p>

		<div class="road-conditions__controls">
			<ul class="road-conditions__states" aria-labelledby="road-conditions-filter-label">
				<?php foreach ( $states as $state => $state_data ) : ?>
					<li>
						<a class="road-conditions__state" href="<?php echo esc_url( add_query_arg( 'road_state', $state, $permalink ) . '#road-conditions' ); ?>" aria-controls="road-conditions-<?php echo esc_attr( $state ); ?>" data-road-state-link="<?php echo esc_attr( $state ); ?>"<?php echo $state === $active ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $state_data['label'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php foreach ( $states as $state => $state_data ) : ?>
				<?php $state_highways = ltt_dive_in_filter_highways_by_state( $highways, $state ); ?>
				<?php if ( ! $state_highways ) { continue; } ?>
				<form class="road-conditions__highway-form" method="get" action="<?php echo esc_url( $permalink . '#road-conditions' ); ?>" data-road-highway-form="<?php echo esc_attr( $state ); ?>"<?php echo $state !== $active ? ' hidden' : ''; ?>>
					<input type="hidden" name="road_state" value="<?php echo esc_attr( $state ); ?>" />
					<?php
					get_template_part(
						'template-parts/components/select',
						null,
						array(
							'id'                => 'road-conditions-highway-' . $state,
							/* translators: %s: state name. */
							'label'             => sprintf( __( 'Filter %s highways', 'ltt-dive-in' ), $state_data['label'] ),
							'name'              => 'highway',
							'placeholder'       => __( 'Filter Highway', 'ltt-dive-in' ),
							'selected_value'    => $state === $active ? $selected : '',
							'surface'           => 'light',
							'class_name'        => 'road-conditions__highway',
							'aria_controls'     => 'road-conditions-' . $state,
							'native_attributes' => array( 'data-road-highway-select' => true ),
							'options'           => array_map(
								static function ( $highway ) {
									return array(
										'value' => $highway['slug'],
										'label' => $highway['name'],
									);
								},
								$state_highways
							),
						)
					);
					?>
					<button class="road-conditions__apply ltt-button ltt-button--glass" type="submit" data-road-highway-apply><?php esc_html_e( 'Apply', 'ltt-dive-in' ); ?></button>
				</form>
			<?php endforeach; ?>
		</div>
	</div>

	<p class="screen-reader-text" role="status" data-road-conditions-status
		data-state-message="<?php /* translators: %s: state name. */ echo esc_attr__( 'Showing %s road conditions.', 'ltt-dive-in' ); ?>"
		data-highway-message="<?php /* translators: %s: highway name. */ echo esc_attr__( 'Showing %s.', 'ltt-dive-in' ); ?>"
		data-all-message="<?php echo esc_attr__( 'Showing all highways.', 'ltt-dive-in' ); ?>"></p>

	<?php foreach ( $states as $state => $state_data ) : ?>
		<?php
		$view    = ltt_dive_in_get_road_state_view( $state, $highways, $area );
		$filter  = $state === $active ? $selected : '';
		$panel   = 'road-conditions-' . $state;
		?>
		<div id="<?php echo esc_attr( $panel ); ?>" class="road-conditions__panel" data-road-state-panel="<?php echo esc_attr( $state ); ?>" data-road-state-label="<?php echo esc_attr( $state_data['label'] ); ?>"<?php echo $state !== $active ? ' hidden' : ''; ?>>
			<?php if ( 'ok' === $view['status'] && $view['cards'] ) : ?>
				<div class="road-conditions__cards">
					<?php
					foreach ( $view['cards'] as $card ) {
						get_template_part(
							'template-parts/components/road-conditions/card',
							null,
							array(
								'card'   => $card,
								'id'     => $panel . '-' . $card['slug'],
								'hidden' => $filter && $filter !== $card['slug'],
							)
						);
					}
					?>
				</div>
			<?php else : ?>
				<div class="road-conditions__message">
					<p>
						<?php
						if ( 'pending' === $view['status'] ) {
							/* translators: %s: state name. */
							echo esc_html( sprintf( __( '%s road conditions are loading. Please check back in a minute.', 'ltt-dive-in' ), $state_data['label'] ) );
						} elseif ( 'ok' === $view['status'] ) {
							/* translators: %s: state name. */
							echo esc_html( sprintf( __( 'No %s highways are set up for this page yet.', 'ltt-dive-in' ), $state_data['label'] ) );
						} else {
							/* translators: %s: state name. */
							echo esc_html( sprintf( __( '%s road conditions are not available right now.', 'ltt-dive-in' ), $state_data['label'] ) );
						}
						?>
					</p>
					<p>
						<a href="<?php echo esc_url( $state_data['source_url'] ); ?>">
							<?php
							/* translators: %s: data provider name. */
							echo esc_html( sprintf( __( 'See current conditions from %s', 'ltt-dive-in' ), $state_data['source'] ) );
							?>
						</a>
					</p>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

	<dialog class="road-camera-dialog" aria-labelledby="road-camera-dialog-title" data-road-camera-dialog data-open-label="<?php /* translators: %s: camera location. */ echo esc_attr__( 'Watch live video: %s', 'ltt-dive-in' ); ?>" data-photo-label="<?php /* translators: %s: camera location. */ echo esc_attr__( 'View larger camera photo: %s', 'ltt-dive-in' ); ?>">
		<div class="road-camera-dialog__inner">
			<div class="road-camera-dialog__header">
				<h2 id="road-camera-dialog-title" class="road-camera-dialog__title" data-road-camera-dialog-title></h2>
				<button type="button" class="road-camera-dialog__close" data-road-camera-dialog-close><span class="road-camera-dialog__close-icon" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Close camera', 'ltt-dive-in' ); ?></span></button>
			</div>
			<div class="road-camera-dialog__player" data-road-camera-player></div>
			<p class="road-camera-dialog__note" role="status" data-road-camera-dialog-status
				data-loading-message="<?php echo esc_attr__( 'Loading live video.', 'ltt-dive-in' ); ?>"
				data-error-message="<?php echo esc_attr__( 'The live video is not available right now. Please try again later.', 'ltt-dive-in' ); ?>"
				data-source-message="<?php echo esc_attr__( 'Live video from Caltrans. There is no sound.', 'ltt-dive-in' ); ?>"
				data-photo-message="<?php /* translators: %s: camera provider, such as Caltrans. */ echo esc_attr__( 'Latest photo from %s.', 'ltt-dive-in' ); ?>"></p>
		</div>
	</dialog>
</section>
