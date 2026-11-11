<?php
/**
 * Story Module — Itinerary Item.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `itinerary_item` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields   = $args['fields'] ?? array();
$id       = $args['id'] ?? wp_unique_id( 'story-module-' );
$times    = ltt_dive_in_story_module_itinerary_times();
$time     = (string) ( $fields['time'] ?? '' );
$image_id = absint( $fields['image'] ?? 0 );
$has_img  = $image_id && wp_attachment_is_image( $image_id );
$stops    = array();

foreach ( array_slice( (array) ( $fields['stops'] ?? array() ), 0, 4 ) as $stop ) {
	$title = ltt_dive_in_story_module_inline_link_text( $stop['title'] ?? '' );
	$body  = trim( (string) ( $stop['content'] ?? '' ) );

	if ( '' !== wp_strip_all_tags( $title ) ) {
		$stops[] = array(
			'title' => $title,
			'body'  => $body,
		);
	}
}

if ( ! $stops ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add at least one stop with a title to preview the Itinerary Item.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$classes = (array) ( $args['classes'] ?? array() );

if ( ! $has_img ) {
	$classes[] = 'story-module--itinerary-item-no-media';
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<div class="story-itinerary-item">
		<div class="story-itinerary-item__content">
			<?php if ( isset( $times[ $time ] ) ) : ?>
				<p class="story-itinerary-item__time story-itinerary-item__time--<?php echo esc_attr( $time ); ?>"><?php echo esc_html( $times[ $time ] ); ?></p>
			<?php endif; ?>
			<?php foreach ( $stops as $stop ) : ?>
				<div class="story-itinerary-item__stop">
					<h4 class="story-itinerary-item__title"><?php echo $stop['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ltt_dive_in_story_module_inline_link_text(). ?></h4>
					<?php if ( $stop['body'] ) : ?>
						<div class="story-itinerary-item__body"><?php echo wp_kses_post( $stop['body'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $has_img ) : ?>
			<div class="story-itinerary-item__media">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'   => 'story-itinerary-item__image',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 767.98px) 100vw, 475px',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
