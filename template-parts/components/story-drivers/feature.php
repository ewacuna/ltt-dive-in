<?php
/**
 * Feature story slideshow (Figma “Feature Story Driver” desktop and mobile).
 *
 * Used by the 1 Up layout and as the hero of Hero + Page Grid. Without
 * JavaScript, slides fall back to a horizontally scrolling row.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stories       = isset( $args['stories'] ) && is_array( $args['stories'] ) ? $args['stories'] : array();
$heading_level = isset( $args['heading_level'] ) && 3 === $args['heading_level'] ? 'h3' : 'h2';
$label         = isset( $args['label'] ) && is_string( $args['label'] ) ? $args['label'] : __( 'Featured stories', 'ltt-dive-in' );
$is_preview    = ! empty( $args['is_preview'] );
$has_controls  = count( $stories ) > 1;

if ( ! $stories ) {
	return;
}
?>
<div class="story-drivers__feature<?php echo $has_controls ? ' story-drivers__feature--carousel' : ''; ?>" role="group" aria-roledescription="<?php esc_attr_e( 'carousel', 'ltt-dive-in' ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"<?php echo $has_controls && ! $is_preview ? ' data-story-feature' : ''; ?>>
	<div class="story-drivers__feature-viewport swiper">
		<div class="story-drivers__feature-track swiper-wrapper">
			<?php foreach ( $stories as $index => $story ) : ?>
				<article class="story-drivers__feature-slide swiper-slide">
					<div class="story-drivers__feature-media" aria-hidden="true">
						<?php
						echo wp_get_attachment_image(
							absint( $story['image'] ),
							'full',
							false,
							array(
								'alt'     => '',
								'loading' => 0 === $index ? 'eager' : 'lazy',
								'sizes'   => '100vw',
							)
						);
						?>
					</div>
					<div class="story-drivers__feature-content">
						<?php if ( $story['category'] ) : ?>
							<p class="story-drivers__feature-tag ltt-tag ltt-tag--dark"><?php echo esc_html( $story['category'] ); ?></p>
						<?php endif; ?>
						<<?php echo esc_attr( $heading_level ); ?> class="story-drivers__feature-title">
							<a class="story-drivers__feature-link" href="<?php echo esc_url( $story['url'] ); ?>"><?php echo esc_html( $story['title'] ); ?></a>
						</<?php echo esc_attr( $heading_level ); ?>>
						<?php if ( $story['tags'] ) : ?>
							<ul class="story-drivers__feature-tags" aria-label="<?php esc_attr_e( 'Tags', 'ltt-dive-in' ); ?>">
								<?php foreach ( $story['tags'] as $tag_name ) : ?>
									<li class="ltt-tag ltt-tag--dark"><?php echo esc_html( $tag_name ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<p class="story-drivers__feature-meta">
							<time datetime="<?php echo esc_attr( $story['date'] ); ?>"><?php echo esc_html( $story['date_label'] ); ?></time>
							<span class="story-drivers__feature-read-time">
								<span class="story-drivers__feature-read-time-icon" aria-hidden="true"></span>
								<?php
								/* translators: %s: Estimated reading time in minutes. */
								echo esc_html( sprintf( _n( '%s min read', '%s min read', $story['read_time'], 'ltt-dive-in' ), number_format_i18n( $story['read_time'] ) ) );
								?>
							</span>
						</p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( $has_controls ) : ?>
		<div class="story-drivers__feature-controls">
			<button class="story-drivers__feature-control ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--previous" type="button" data-story-feature-previous aria-label="<?php esc_attr_e( 'Previous story', 'ltt-dive-in' ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-previous.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
			<div class="story-drivers__feature-pagination ltt-carousel-indicators" data-story-feature-pagination></div>
			<button class="story-drivers__feature-control ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--next" type="button" data-story-feature-next aria-label="<?php esc_attr_e( 'Next story', 'ltt-dive-in' ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-next.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
		</div>
	<?php endif; ?>
</div>
