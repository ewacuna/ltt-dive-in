<?php
/**
 * Reusable story tile (Figma “Story Card – Default”, “Story Card – Expanded”,
 * and “Story Card – Mobile”).
 *
 * The static tile shows the image under a navy overlay with the category and
 * title. Hover, keyboard focus, or the `is-expanded` class set by
 * story-drivers.js reveals the information panel. Below 768px the card stacks
 * its image above the panel unless `tile` keeps the full-image presentation,
 * as in the mobile carousel.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$story   = isset( $args['story'] ) && is_array( $args['story'] ) ? $args['story'] : array();
$wrapper = isset( $args['wrapper'] ) && is_string( $args['wrapper'] ) ? $args['wrapper'] : '';
$sizes   = isset( $args['sizes'] ) && is_string( $args['sizes'] ) ? $args['sizes'] : '(max-width: 767.98px) 100vw, 471px';
$is_tile = ! empty( $args['tile'] );

if ( empty( $story['title'] ) || empty( $story['url'] ) || empty( $story['image'] ) ) {
	return;
}

$classes = array( 'ltt-story-card' );

if ( $is_tile ) {
	$classes[] = 'ltt-story-card--tile';
}

if ( $wrapper ) {
	$classes = array_merge( $classes, array_map( 'sanitize_html_class', explode( ' ', $wrapper ) ) );
}

$category_ids = isset( $story['category_ids'] ) && is_array( $story['category_ids'] ) ? array_map( 'absint', $story['category_ids'] ) : array();
$read_time    = isset( $story['read_time'] ) ? max( 1, absint( $story['read_time'] ) ) : 1;
?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-story-card data-story-terms="<?php echo esc_attr( implode( ' ', $category_ids ) ); ?>">
	<div class="ltt-story-card__media" aria-hidden="true">
		<?php
		echo wp_get_attachment_image(
			absint( $story['image'] ),
			'large',
			false,
			array(
				'alt'     => '',
				'loading' => 'lazy',
				'sizes'   => $sizes,
			)
		);
		?>
	</div>

	<div class="ltt-story-card__summary" aria-hidden="true">
		<?php if ( ! empty( $story['category'] ) ) : ?>
			<span class="ltt-story-card__summary-tag ltt-tag ltt-tag--dark"><?php echo esc_html( $story['category'] ); ?></span>
		<?php endif; ?>
		<span class="ltt-story-card__summary-title"><?php echo esc_html( $story['title'] ); ?></span>
	</div>

	<div class="ltt-story-card__panel">
		<div class="ltt-story-card__content">
			<p class="ltt-story-card__meta">
				<?php if ( ! empty( $story['category'] ) ) : ?>
					<span class="ltt-story-card__tag ltt-tag"><?php echo esc_html( $story['category'] ); ?></span>
				<?php endif; ?>
				<span class="ltt-story-card__read-time">
					<span class="ltt-story-card__read-time-icon" aria-hidden="true"></span>
					<?php
					/* translators: %s: Estimated reading time in minutes. */
					echo esc_html( sprintf( _n( '%s min read', '%s min read', $read_time, 'ltt-dive-in' ), number_format_i18n( $read_time ) ) );
					?>
				</span>
			</p>

			<h3 class="ltt-story-card__title">
				<a class="ltt-story-card__link" href="<?php echo esc_url( $story['url'] ); ?>"><?php echo esc_html( $story['title'] ); ?></a>
			</h3>

			<?php if ( ! empty( $story['excerpt'] ) ) : ?>
				<p class="ltt-story-card__excerpt"><?php echo esc_html( $story['excerpt'] ); ?></p>
			<?php endif; ?>

			<?php /* A pointer-only duplicate of the title link, so “Read More” gets its own hover; keyboard and screen-reader users use the title link. */ ?>
			<a class="ltt-story-card__read-more" href="<?php echo esc_url( $story['url'] ); ?>" tabindex="-1" aria-hidden="true"><?php esc_html_e( 'Read More', 'ltt-dive-in' ); ?><span class="ltt-story-card__read-more-icon"></span></a>
		</div>
	</div>
</article>
