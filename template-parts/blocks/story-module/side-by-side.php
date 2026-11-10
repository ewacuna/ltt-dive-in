<?php
/**
 * Story Module — Side by Side.
 *
 * Text beside one image: optional eyebrow, H2 title, rich-text content (its
 * bulleted lists use the mountain icon), optional CTA, and the image on the
 * right or left.
 *
 * Styles: assets/css/components/story-module/side-by-side.css.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `side_by_side` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields     = $args['fields'] ?? array();
$id         = $args['id'] ?? wp_unique_id( 'story-module-' );
$is_preview = ! empty( $args['is_preview'] );
$eyebrow    = trim( (string) ( $fields['eyebrow'] ?? '' ) );
$title      = trim( (string) ( $fields['title'] ?? '' ) );
$body       = trim( (string) ( $fields['content'] ?? '' ) );
$cta        = $fields['cta'] ?? null;
$image_id   = absint( $fields['image'] ?? 0 );
$position   = 'left' === ( $fields['image_position'] ?? '' ) ? 'left' : 'right';
$has_cta    = is_array( $cta ) && '' !== trim( (string) ( $cta['title'] ?? '' ) ) && ! empty( $cta['url'] ) && (bool) esc_url( $cta['url'] );

if ( '' === $title ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add a title, content, and image to preview the Side by Side.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$classes = array_merge(
	(array) ( $args['classes'] ?? array() ),
	array( 'story-module--side-by-side-media-' . $position )
);

if ( ! $image_id ) {
	$classes[] = 'story-module--side-by-side-no-media';
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="story-side-by-side">
		<div class="story-side-by-side__content">
			<?php if ( $eyebrow ) : ?>
				<p class="story-side-by-side__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="story-side-by-side__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $body ) : ?>
				<div class="story-side-by-side__body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
			<?php if ( $has_cta && $is_preview ) : ?>
				<span class="ltt-button ltt-button--primary-outline story-side-by-side__cta"><?php echo esc_html( trim( $cta['title'] ) ); ?></span>
			<?php elseif ( $has_cta ) : ?>
				<a class="ltt-button ltt-button--primary-outline story-side-by-side__cta" href="<?php echo esc_url( $cta['url'] ); ?>"<?php if ( '_blank' === ( $cta['target'] ?? '' ) ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo esc_html( trim( $cta['title'] ) ); ?></a>
			<?php endif; ?>
		</div>
		<?php if ( $image_id ) : ?>
			<div class="story-side-by-side__media">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'   => 'story-side-by-side__image',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 767.98px) 100vw, 43vw',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
