<?php
/**
 * Current Conditions tab feature: intro and live module over the tab's background image.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id  = (int) ( $args['post_id'] ?? 0 );
$image_id = (int) get_post_thumbnail_id( $post_id );
$module   = ltt_dive_in_get_current_conditions_tab_module( $post_id );
?>
<div class="current-conditions-feature<?php echo $module['module'] ? ' current-conditions-feature--has-module current-conditions-feature--' . esc_attr( $module['module'] ) : ''; ?>">
	<?php
	$backgrounds  = ! empty( $module['backgrounds'] ) ? $module['backgrounds'] : array();
	$active_state = $module['state'] ?? '';

	if ( $image_id ) {
		$backgrounds = array( '' => $image_id ) + $backgrounds;
	}

	foreach ( $backgrounds as $background_state => $background_id ) {
		$background_state = (string) $background_state;
		$is_active        = isset( $backgrounds[ $active_state ] ) ? $background_state === $active_state : '' === $background_state;
		$attributes       = array(
			'class'   => 'current-conditions-feature__background',
			'alt'     => '',
			'sizes'   => '100vw',
			'loading' => $is_active ? 'eager' : 'lazy',
		);

		if ( $is_active ) {
			$attributes['fetchpriority'] = 'high';
		}

		$image = wp_get_attachment_image( $background_id, 'full', false, $attributes );

		// Road states with their own photo swap backgrounds when visitors switch state.
		if ( 1 < count( $backgrounds ) ) {
			$image = str_replace( '<img ', '<img data-road-background="' . esc_attr( $background_state ) . '"' . ( $is_active ? '' : ' hidden' ) . ' ', $image );
		}

		echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core image markup.
	}

	get_template_part(
		'template-parts/sections/current-conditions-intro',
		null,
		array(
			'title' => get_the_title( $post_id ),
			'text'  => has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '',
		)
	);

	if ( 'weather' === $module['module'] ) {
		get_template_part( 'template-parts/sections/current-weather', null, $module );
	} elseif ( 'roads' === $module['module'] ) {
		get_template_part( 'template-parts/sections/current-road-conditions', null, $module );
	} elseif ( 'cams' === $module['module'] ) {
		get_template_part( 'template-parts/sections/current-live-cams', null, array( 'cams' => array_slice( $module['cams'], 0, 1 ) ) );
	}
	?>
</div>
<?php
// Later cameras sit in their own bands, outside the tab background.
if ( 'cams' === $module['module'] && 1 < count( $module['cams'] ) ) {
	get_template_part(
		'template-parts/sections/current-live-cams',
		null,
		array(
			'cams'  => array_slice( $module['cams'], 1 ),
			'start' => 1,
		)
	);
}

if ( 'cams' === $module['module'] ) {
	get_template_part( 'template-parts/sections/current-ski-cams', null, array( 'ski_cams' => ltt_dive_in_get_ski_cams( $post_id ) ) );
}
