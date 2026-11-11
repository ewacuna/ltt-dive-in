<?php
/**
 * Story Module — shared Guide layout.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the variant's field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 *     @type string   $list       List style: `numbers`, `bullets`, or `none`.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields       = $args['fields'] ?? array();
$id           = $args['id'] ?? wp_unique_id( 'story-module-' );
$is_preview   = ! empty( $args['is_preview'] );
$list         = in_array( $args['list'] ?? '', array( 'numbers', 'bullets' ), true ) ? $args['list'] : 'none';
$title        = trim( (string) ( $fields['title'] ?? '' ) );
$body         = trim( (string) ( $fields['content'] ?? '' ) );
$list_heading = 'none' === $list ? '' : trim( (string) ( $fields['list_heading'] ?? '' ) );
$items        = array();
$images       = array();

if ( 'none' !== $list ) {
	foreach ( array_slice( (array) ( $fields['items'] ?? array() ), 0, 6 ) as $item ) {
		$text = trim( (string) ( is_array( $item ) ? ( $item['text'] ?? '' ) : '' ) );

		if ( '' !== $text ) {
			$items[] = $text;
		}
	}
}

foreach ( array_slice( (array) ( $fields['images'] ?? array() ), 0, 6 ) as $image_id ) {
	$image_id = absint( $image_id );

	if ( $image_id && wp_attachment_is_image( $image_id ) ) {
		$images[] = $image_id;
	}
}

if ( '' === $title ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add a title to preview this guide section.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$count       = count( $images );
$is_carousel = $count > 1;
$classes     = (array) ( $args['classes'] ?? array() );
$classes[]   = 'story-guide-section';

if ( ! $count ) {
	$classes[] = 'story-guide-section--no-media';
}

if ( $is_carousel && ! $is_preview ) {
	wp_enqueue_script( 'ltt-dive-in-story-module-guide' );
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="story-guide">
		<div class="story-guide__content">
			<h3 id="<?php echo esc_attr( $id ); ?>-title" class="story-guide__title"><?php echo esc_html( $title ); ?></h3>
			<?php if ( $body ) : ?>
				<div class="story-guide__body"><?php echo wp_kses_post( $body ); ?></div>
			<?php endif; ?>
			<?php if ( $items ) : ?>
				<div class="story-guide__list-block">
					<?php if ( $list_heading ) : ?>
						<p class="story-guide__list-heading"><?php echo esc_html( $list_heading ); ?></p>
					<?php endif; ?>
					<?php $list_tag = 'numbers' === $list ? 'ol' : 'ul'; ?>
					<<?php echo esc_html( $list_tag ); ?> class="story-guide__list story-guide__list--<?php echo esc_attr( $list ); ?>">
						<?php foreach ( $items as $index => $text ) : ?>
							<li class="story-guide__item">
								<?php if ( 'numbers' === $list ) : ?>
									<span class="story-guide__marker" aria-hidden="true"><?php echo esc_html( number_format_i18n( $index + 1 ) ); ?></span>
								<?php else : ?>
									<span class="story-guide__marker" aria-hidden="true"></span>
								<?php endif; ?>
								<span class="story-guide__item-text"><?php echo esc_html( $text ); ?></span>
							</li>
						<?php endforeach; ?>
					</<?php echo esc_html( $list_tag ); ?>>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $count ) : ?>
			<div class="story-guide__media<?php echo $is_carousel ? ' story-guide__media--carousel' : ''; ?>"<?php echo $is_carousel ? ' data-story-guide-carousel' : ''; ?>>
				<div class="story-guide__viewport"<?php if ( $is_carousel ) : ?> role="region" tabindex="0" aria-label="<?php /* translators: %s: Section title. */ echo esc_attr( sprintf( __( 'Images: %s', 'ltt-dive-in' ), $title ) ); ?>" data-story-guide-viewport<?php endif; ?>>
					<div class="story-guide__track" id="<?php echo esc_attr( $id ); ?>-images" data-story-guide-track>
						<?php foreach ( $images as $image_id ) : ?>
							<div class="story-guide__slide">
								<?php
								echo wp_get_attachment_image(
									$image_id,
									'large',
									false,
									array(
										'class'   => 'story-guide__image',
										'loading' => 'lazy',
										'sizes'   => '(max-width: 767.98px) 100vw, 475px',
									)
								);
								?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ( $is_carousel && ! $is_preview ) : ?>
					<div class="story-guide__controls" data-story-guide-controls hidden>
						<div class="story-guide__pagination ltt-carousel-indicators" data-story-guide-pagination></div>
						<div class="story-guide__arrows">
							<button type="button" class="ltt-slider-navigation ltt-slider-navigation--white ltt-slider-navigation--previous" data-story-guide-previous aria-controls="<?php echo esc_attr( $id ); ?>-images" aria-label="<?php esc_attr_e( 'Previous image', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
							<button type="button" class="ltt-slider-navigation ltt-slider-navigation--next" data-story-guide-next aria-controls="<?php echo esc_attr( $id ); ?>-images" aria-label="<?php esc_attr_e( 'Next image', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
