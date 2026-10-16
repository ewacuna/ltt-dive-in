<?php
/**
 * Map block renderer.
 *
 * The server renders the map frame and a hidden text list of places. The map
 * script reveals the list instead of the frame when Mapbox cannot load; without
 * JavaScript, the noscript copy of the list is shown. The editor preview never
 * loads Mapbox.
 *
 * @package LTT_Dive_In
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview Whether the block is being rendered in the editor.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = ! empty( $is_preview );
$block      = isset( $block ) && is_array( $block ) ? $block : array();
$places      = ltt_dive_in_prepare_map_places( get_field( 'ltt_dive_in_map_places' ) );
$attractions = ltt_dive_in_prepare_map_attractions( get_field( 'ltt_dive_in_map_attractions' ) );
$snow        = get_field( 'ltt_dive_in_map_snow' );
$snow        = null === $snow ? true : (bool) $snow;
$token       = ltt_dive_in_get_mapbox_access_token();
$block_id   = ! empty( $block['id'] ) ? $block['id'] : wp_unique_id();
$id         = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'ltt-map-' . sanitize_html_class( $block_id );
$classes    = array( 'ltt-map' );

if ( ! empty( $block['align'] ) && 'full' === $block['align'] ) {
	$classes[] = 'alignfull';
}

/**
 * Print the text list of places used when the interactive map is unavailable.
 *
 * @param array[] $places Prepared places.
 */
$render_places = static function ( $places ) {
	?>
	<p class="ltt-map__fallback-title"><?php esc_html_e( 'The interactive map is unavailable.', 'ltt-dive-in' ); ?></p>
	<?php if ( $places ) : ?>
		<ul class="ltt-map__places">
			<?php foreach ( $places as $place ) : ?>
				<li class="ltt-map__place">
					<p class="ltt-map__place-name"><?php echo esc_html( $place['name'] ); ?></p>
					<?php if ( $place['region'] ) : ?>
						<p class="ltt-map__place-region"><?php echo esc_html( $place['region'] ); ?></p>
					<?php endif; ?>
					<?php if ( $place['description'] ) : ?>
						<p class="ltt-map__place-text"><?php echo esc_html( $place['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( $place['link'] ) : ?>
						<a class="ltt-map__place-link" href="<?php echo esc_url( $place['link']['url'] ); ?>"<?php echo '_blank' === $place['link']['target'] ? ' target="_blank" rel="noopener"' : ''; ?>>
							<?php echo esc_html( $place['link']['title'] ); ?>
							<?php if ( '_blank' === $place['link']['target'] ) : ?>
								<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?></span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php
};

if ( $is_preview ) :
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?> ltt-map--preview">
		<div class="ltt-map__preview">
			<p class="ltt-map__preview-title"><?php esc_html_e( 'Lake Tahoe map', 'ltt-dive-in' ); ?></p>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: number of destinations, 2: number of attractions. */
						__( 'The interactive map with %1$d destinations and %2$d attractions appears on the published page.', 'ltt-dive-in' ),
						count( $places ),
						count( $attractions )
					)
				);
				?>
			</p>
			<?php if ( ! $token ) : ?>
				<p class="ltt-map__preview-notice">
					<?php esc_html_e( 'No Mapbox token is configured. Add a public token in Settings → Map; until then visitors see only the list of places.', 'ltt-dive-in' ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $places ) : ?>
				<ul class="ltt-map__preview-places">
					<?php foreach ( $places as $place ) : ?>
						<li><?php echo esc_html( $place['name'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return;
endif;

// Without a token there is nothing interactive to show; fall back to the list.
if ( ! $token ) {
	if ( $places ) {
		wp_enqueue_style( 'ltt-dive-in-map' );
		?>
		<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?> is-fallback" aria-label="<?php esc_attr_e( 'Places around Lake Tahoe', 'ltt-dive-in' ); ?>">
			<div class="ltt-map__fallback"><?php $render_places( $places ); ?></div>
		</section>
		<?php
	}

	return;
}

// The block metadata supplies these handles normally; this also covers blocks
// rendered outside the queried post's parsed block tree.
wp_enqueue_style( 'ltt-dive-in-map' );
wp_enqueue_script( 'ltt-dive-in-map' );

?>
<section
	id="<?php echo esc_attr( $id ); ?>"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	aria-label="<?php esc_attr_e( 'Lake Tahoe map', 'ltt-dive-in' ); ?>"
	data-ltt-map
	data-access-token="<?php echo esc_attr( $token ); ?>"
	data-map-style="<?php echo esc_attr( ltt_dive_in_get_mapbox_style_url() ); ?>"
	data-places="<?php echo esc_attr( wp_json_encode( $places ) ); ?>"
	data-attractions="<?php echo esc_attr( wp_json_encode( $attractions ) ); ?>"
	data-cartography="<?php echo esc_attr( wp_json_encode( ltt_dive_in_get_map_cartography() ) ); ?>"
	<?php echo $snow ? 'data-snow' : ''; ?>
>
	<div class="ltt-map__frame" data-ltt-map-frame>
		<div class="ltt-map__canvas" data-ltt-map-canvas></div>
	</div>

	<div class="ltt-map__fallback" data-ltt-map-fallback hidden>
		<?php $render_places( $places ); ?>
	</div>
	<noscript>
		<div class="ltt-map__fallback"><?php $render_places( $places ); ?></div>
	</noscript>

	<p class="screen-reader-text" role="status" data-ltt-map-status></p>
</section>
