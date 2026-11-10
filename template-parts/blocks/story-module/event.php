<?php
/**
 * Story Module — Event.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `event` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields   = $args['fields'] ?? array();
$id       = $args['id'] ?? wp_unique_id( 'story-module-' );
$title    = trim( (string) ( $fields['title'] ?? '' ) );
$date     = trim( (string) ( $fields['date'] ?? '' ) );
$location = trim( (string) ( $fields['location'] ?? '' ) );
$body     = trim( (string) ( $fields['content'] ?? '' ) );
$image_id = absint( $fields['image'] ?? 0 );
$has_img  = $image_id && wp_attachment_is_image( $image_id );

if ( '' === $title ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add a title to preview the Event.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$classes = (array) ( $args['classes'] ?? array() );

if ( ! $has_img ) {
	$classes[] = 'story-module--event-no-media';
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="story-event">
		<div class="story-event__content">
			<h3 id="<?php echo esc_attr( $id ); ?>-title" class="story-event__title"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $date || $location ) : ?>
				<div class="story-event__details">
					<?php if ( $date ) : ?>
						<p class="story-event__date"><?php echo esc_html( $date ); ?></p>
					<?php endif; ?>
					<?php if ( $location ) : ?>
						<p class="story-event__location"><?php echo esc_html( $location ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $body ) : ?>
				<div class="story-event__body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $has_img ) : ?>
			<div class="story-event__media">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'   => 'story-event__image',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 767.98px) 100vw, 400px',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
