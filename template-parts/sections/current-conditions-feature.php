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
<div class="current-conditions-feature<?php echo $module['module'] ? ' current-conditions-feature--has-module' : ''; ?>">
	<?php
	if ( $image_id ) {
		echo wp_get_attachment_image(
			$image_id,
			'full',
			false,
			array(
				'class'         => 'current-conditions-feature__background',
				'alt'           => '',
				'sizes'         => '100vw',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
			)
		);
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
	}
	?>
</div>
