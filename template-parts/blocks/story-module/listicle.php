<?php
/**
 * Story Module — Listicle.
 *
 * One numbered item of a round-up list: number, title, location line, copy,
 * and a gallery of one to four images beside the text.
 *
 * Figma: Blog Templates `464gNuDz4jnsCACmxyGie3`, “Round-Up • Listicle: Fine
 * Dining”. Desktop 4 `2991:82108`, 3 `2991:82131`, 2 `2991:82121`,
 * 1 `2991:82143`; mobile 4 `2991:82964`, 3 `2991:82987`, 2 `2991:82977`,
 * 1 `2991:82999`.
 * Styles: assets/css/components/story-module/listicle.css.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `listicle` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields   = $args['fields'] ?? array();
$id       = $args['id'] ?? wp_unique_id( 'story-module-' );
$number   = isset( $fields['number'] ) && is_numeric( $fields['number'] ) ? absint( $fields['number'] ) : 0;
$title    = trim( (string) ( $fields['title'] ?? '' ) );
$location = trim( (string) ( $fields['location'] ?? '' ) );
$body     = trim( (string) ( $fields['body'] ?? '' ) );
$position = 'left' === ( $fields['image_position'] ?? '' ) ? 'left' : 'right';
$images   = array_slice( array_values( array_filter( array_map( 'absint', (array) ( $fields['images'] ?? array() ) ) ) ), 0, 4 );

if ( '' === $title ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add a title, copy, and images to preview the Listicle item.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

$count   = count( $images );
$classes = array_merge(
	(array) ( $args['classes'] ?? array() ),
	array( 'story-module--listicle-media-' . $position )
);

/*
 * Each image's rendered width, for its `sizes` attribute: the media column is
 * about 39vw on desktop and full width when stacked. One image, or the wide
 * third image of three, fills the column; the others take half of it.
 */
$image_sizes = static function ( $index ) use ( $count ) {
	$is_full = 1 === $count || ( 3 === $count && 2 === $index );

	return $is_full
		? '(max-width: 767.98px) 100vw, 39vw'
		: '(max-width: 767.98px) 50vw, 20vw';
};
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="story-listicle">
		<div class="story-listicle__content">
			<?php if ( $number ) : ?>
				<p class="story-listicle__number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $number ) ); ?></p>
			<?php endif; ?>
			<div class="story-listicle__text">
				<h3 id="<?php echo esc_attr( $id ); ?>-title" class="story-listicle__title">
					<?php if ( $number ) : ?>
						<span class="screen-reader-text"><?php echo esc_html( number_format_i18n( $number ) ); ?>. </span>
					<?php endif; ?>
					<?php echo esc_html( $title ); ?>
				</h3>
				<?php if ( $location ) : ?>
					<p class="story-listicle__location"><?php echo esc_html( $location ); ?></p>
				<?php endif; ?>
				<?php if ( $body ) : ?>
					<div class="story-listicle__body"><?php echo wp_kses_post( $body ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $count ) : ?>
			<div class="story-listicle__media story-listicle__media--count-<?php echo esc_attr( (string) $count ); ?>">
				<?php foreach ( $images as $index => $image_id ) : ?>
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'large',
						false,
						array(
							'class'   => 'story-listicle__image',
							'loading' => 'lazy',
							'sizes'   => $image_sizes( $index ),
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
