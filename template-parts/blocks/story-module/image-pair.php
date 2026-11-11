<?php
/**
 * Story Module — Image Pair.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `image_pair` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields = $args['fields'] ?? array();
$id     = $args['id'] ?? wp_unique_id( 'story-module-' );
$images = array();

foreach ( array_slice( (array) ( $fields['images'] ?? array() ), 0, 2 ) as $image_id ) {
	$image_id = absint( $image_id );

	if ( $image_id && wp_attachment_is_image( $image_id ) ) {
		$images[] = $image_id;
	}
}

if ( 2 !== count( $images ) ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add two images to preview the Image Pair.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>">
	<div class="story-image-pair">
		<?php foreach ( $images as $image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'large',
				false,
				array(
					'class'   => 'story-image-pair__image',
					'loading' => 'lazy',
					'sizes'   => '(max-width: 767.98px) 50vw, 422px',
				)
			);
			?>
		<?php endforeach; ?>
	</div>
</div>
