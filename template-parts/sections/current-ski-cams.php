<?php
/**
 * Current Conditions live-cameras tab: ski cams section.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ski_cams = $args['ski_cams'] ?? array();

if ( ! $ski_cams ) {
	return;
}

$resorts  = $ski_cams['resorts'];
$featured = $ski_cams['featured'];
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display choice, validated against the resort list.
$requested  = isset( $_GET['ski_resort'] ) ? sanitize_title( wp_unslash( $_GET['ski_resort'] ) ) : '';
$active     = isset( $resorts[ $requested ] ) ? $requested : (string) array_key_first( $resorts );
$new_tab    = __( '(opens in a new tab)', 'ltt-dive-in' );
$section_id = 'find-a-ski-cam';
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="ski-cams" aria-labelledby="<?php echo esc_attr( $section_id ); ?>-title" data-ski-cams<?php /* translators: %s: Ski resort name. */ ?> data-ski-cams-message="<?php echo esc_attr__( 'Showing %s cams.', 'ltt-dive-in' ); ?>">
	<div class="ski-cams__container">
		<header class="ski-cams__header">
			<?php if ( $ski_cams['eyebrow'] ) : ?>
				<p class="ski-cams__eyebrow ltt-tag"><?php echo esc_html( $ski_cams['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h2 id="<?php echo esc_attr( $section_id ); ?>-title" class="ski-cams__title"><?php echo esc_html( $ski_cams['heading'] ); ?></h2>
			<?php if ( $ski_cams['intro'] ) : ?>
				<p class="ski-cams__intro"><?php echo esc_html( $ski_cams['intro'] ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( $featured ) : ?>
			<article class="ski-cams__featured" aria-labelledby="<?php echo esc_attr( $section_id ); ?>-featured">
				<a class="ski-cams__media" href="<?php echo esc_url( $featured['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php
					echo wp_get_attachment_image(
						$featured['image'],
						'large',
						false,
						array(
							'class'   => 'ski-cams__image',
							'sizes'   => '(max-width: 991px) 100vw, 656px',
							'loading' => 'lazy',
						)
					);
					?>
					<span class="ski-cams__badge" aria-hidden="true"><?php esc_html_e( 'Live', 'ltt-dive-in' ); ?></span>
					<img class="ski-cams__media-play" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/live-feed-play.svg' ) ); ?>" width="64" height="64" alt="" />
					<span class="screen-reader-text">
						<?php
						/* translators: %s: Ski cam name. */
						echo esc_html( sprintf( __( 'Watch %s live', 'ltt-dive-in' ), $featured['heading'] ) . ' ' . $new_tab );
						?>
					</span>
				</a>
				<div class="ski-cams__featured-copy">
					<p class="ski-cams__live"><?php esc_html_e( 'Live', 'ltt-dive-in' ); ?></p>
					<h3 id="<?php echo esc_attr( $section_id ); ?>-featured" class="ski-cams__cam-title"><?php echo esc_html( $featured['heading'] ); ?></h3>
					<?php if ( $featured['text'] ) : ?>
						<div class="ski-cams__text"><?php echo wp_kses_post( wpautop( esc_html( $featured['text'] ) ) ); ?></div>
					<?php endif; ?>
					<a class="ski-cams__cta ltt-button ltt-button--primary-outline" href="<?php echo esc_url( $featured['url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $featured['label'] ); ?>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: Ski cam name. */
							echo esc_html( sprintf( __( 'for %s', 'ltt-dive-in' ), $featured['heading'] ) . ' ' . $new_tab );
							?>
						</span>
					</a>
				</div>
			</article>
		<?php endif; ?>

		<?php if ( $resorts ) : ?>
			<div class="ski-cams__browser">
				<?php if ( 1 < count( $resorts ) ) : ?>
					<nav class="ski-cams__filters" aria-label="<?php esc_attr_e( 'Ski resorts', 'ltt-dive-in' ); ?>">
						<ul class="ski-cams__filter-list">
							<?php foreach ( $resorts as $resort ) : ?>
								<li>
									<a class="ski-cams__filter ltt-category-selection" href="<?php echo esc_url( add_query_arg( 'ski_resort', $resort['slug'] ) . '#' . $section_id ); ?>" data-ski-cams-filter="<?php echo esc_attr( $resort['slug'] ); ?>"<?php echo $resort['slug'] === $active ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $resort['name'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
					<p class="screen-reader-text" role="status" data-ski-cams-status></p>
				<?php endif; ?>

				<?php foreach ( $resorts as $resort ) : ?>
					<ul class="ski-cams__list" data-ski-cams-panel="<?php echo esc_attr( $resort['slug'] ); ?>" data-ski-cams-name="<?php echo esc_attr( $resort['name'] ); ?>"<?php echo $resort['slug'] === $active ? '' : ' hidden'; ?>>
						<?php foreach ( $resort['cams'] as $cam ) : ?>
							<li class="ski-cams__item">
								<div class="ski-cams__item-copy">
									<p class="ski-cams__meta">
										<span class="ltt-tag"><?php echo esc_html( $resort['name'] ); ?></span>
										<span class="ski-cams__live"><?php esc_html_e( 'Live', 'ltt-dive-in' ); ?></span>
									</p>
									<h3 class="ski-cams__cam-title"><?php echo esc_html( $cam['heading'] ); ?></h3>
									<?php if ( $cam['text'] ) : ?>
										<p class="ski-cams__text"><?php echo esc_html( $cam['text'] ); ?></p>
									<?php endif; ?>
								</div>
								<a class="ski-cams__play" href="<?php echo esc_url( $cam['url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/live-feed-play-dark.svg' ) ); ?>" width="48" height="48" alt="" />
									<span class="screen-reader-text">
										<?php
										/* translators: 1: Ski cam name, 2: Ski resort name. */
										echo esc_html( sprintf( __( 'Watch %1$s at %2$s live', 'ltt-dive-in' ), $cam['heading'], $resort['name'] ) . ' ' . $new_tab );
										?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
