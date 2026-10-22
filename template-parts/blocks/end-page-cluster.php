<?php
/**
 * End Page Cluster variants: Copy/CTA Driver, Page Driver, and Content Trail.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = ! empty( $is_preview );
$variant    = get_field( 'ltt_dive_in_end_page_cluster_variant' );
if ( ! in_array( $variant, array( 'copy_cta', 'page_driver', 'content_trail' ), true ) ) {
	$variant = 'copy_cta';
}
$id         = ! empty( $block['anchor'] ) ? sanitize_title( $block['anchor'] ) : wp_unique_id( 'end-page-cluster-' );
$is_full    = isset( $block['align'] ) && 'full' === $block['align'];
$has_cta    = static function ( $link ) {
	return is_array( $link ) && '' !== trim( (string) ( $link['title'] ?? '' ) ) && ! empty( $link['url'] ) && (bool) esc_url( $link['url'] );
};
$render_cta = static function ( $link, $modifier, $placement_class = '' ) use ( $is_preview, $has_cta ) {
	if ( ! $has_cta( $link ) ) {
		return;
	}
	$tag = $is_preview ? 'span' : 'a';
	?>
	<<?php echo esc_html( $tag ); ?> class="ltt-button ltt-button--<?php echo esc_attr( $modifier ); ?><?php echo $placement_class ? ' ' . esc_attr( $placement_class ) : ''; ?>"<?php if ( ! $is_preview ) : ?> href="<?php echo esc_url( $link['url'] ); ?>"<?php if ( '_blank' === ( $link['target'] ?? '' ) ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?><?php endif; ?>><?php echo esc_html( trim( $link['title'] ) ); ?></<?php echo esc_html( $tag ); ?>>
	<?php
};
$empty_notice = static function ( $message ) use ( $is_preview ) {
	if ( $is_preview ) {
		echo '<p class="end-page-cluster__notice">' . esc_html( $message ) . '</p>';
	}
};

if ( 'copy_cta' === $variant ) {
	$callouts = get_field( 'ltt_dive_in_end_page_cluster_callouts' );
	$callouts = is_array( $callouts ) ? array_slice( array_values( array_filter( $callouts, static function ( $callout ) {
		return is_array( $callout ) && '' !== trim( (string) ( $callout['title'] ?? '' ) );
	} ) ), 0, 3 ) : array();
	if ( ! $callouts ) {
		$empty_notice( __( 'Add two or three callouts to preview the Copy/CTA Driver.', 'ltt-dive-in' ) );
		return;
	}

	// Stats module swatches; an empty selection keeps the white background.
	$color       = (string) get_field( 'ltt_dive_in_end_page_cluster_background_color' );
	$color       = in_array( $color, array( 'navy', 'gold', 'burgundy', 'red', 'forest', 'green', 'blue', 'cyan' ), true ) ? $color : '';
	$is_dark     = in_array( $color, array( 'navy', 'burgundy', 'red', 'forest', 'blue' ), true );
	$dark_tag    = $is_dark || 'green' === $color;
	$cta_style   = 'primary-outline';
	if ( $is_dark ) {
		$cta_style = 'dark-standard';
	} elseif ( in_array( $color, array( 'gold', 'green', 'cyan' ), true ) ) {
		$cta_style = 'primary-fill';
	}
	$classes = array( 'end-page-cluster', 'end-page-cluster--copy-cta', 'end-page-cluster--count-' . count( $callouts ) );
	$classes[] = $color ? 'end-page-cluster--bg-' . $color : 'end-page-cluster--bg-white';
	if ( $is_full ) {
		$classes[] = 'alignfull';
	}
	?>
	<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<div class="end-page-cluster__container end-page-cluster__callouts">
			<?php foreach ( $callouts as $callout ) : ?>
				<div class="end-page-cluster__callout">
					<?php if ( '' !== trim( (string) ( $callout['tag'] ?? '' ) ) ) : ?>
						<p class="end-page-cluster__tag ltt-tag<?php echo $dark_tag ? ' ltt-tag--dark' : ''; ?>"><?php echo esc_html( trim( $callout['tag'] ) ); ?></p>
					<?php endif; ?>
					<h2 class="end-page-cluster__title"><?php echo esc_html( trim( $callout['title'] ) ); ?></h2>
					<?php if ( '' !== trim( (string) ( $callout['copy'] ?? '' ) ) ) : ?>
						<p class="end-page-cluster__body"><?php echo esc_html( trim( $callout['copy'] ) ); ?></p>
					<?php endif; ?>
					<?php if ( $has_cta( $callout['primary_cta'] ?? null ) || $has_cta( $callout['secondary_cta'] ?? null ) ) : ?>
						<div class="end-page-cluster__actions">
							<?php $render_cta( $callout['primary_cta'] ?? null, $cta_style ); ?>
							<?php $render_cta( $callout['secondary_cta'] ?? null, $cta_style ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return;
}

$tag   = trim( (string) get_field( 'ltt_dive_in_end_page_cluster_tag' ) );
$title = trim( (string) get_field( 'ltt_dive_in_end_page_cluster_title' ) );
$body  = trim( (string) get_field( 'ltt_dive_in_end_page_cluster_body' ) );

if ( 'page_driver' === $variant ) {
	$cards = get_field( 'ltt_dive_in_end_page_cluster_cards' );
	$cards = is_array( $cards ) ? array_slice( array_values( array_filter( $cards, static function ( $card ) {
		return is_array( $card ) && '' !== trim( (string) ( $card['title'] ?? '' ) );
	} ) ), 0, 3 ) : array();
	if ( ! $title || ! $cards ) {
		$empty_notice( __( 'Add a title and three cards to preview the Page Driver.', 'ltt-dive-in' ) );
		return;
	}
	?>
	<section id="<?php echo esc_attr( $id ); ?>" class="end-page-cluster end-page-cluster--page-driver<?php echo $is_full ? ' alignfull' : ''; ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
		<div class="end-page-cluster__container">
			<header class="end-page-cluster__header">
				<?php if ( $tag ) : ?><p class="end-page-cluster__tag ltt-tag"><?php echo esc_html( $tag ); ?></p><?php endif; ?>
				<h2 id="<?php echo esc_attr( $id ); ?>-title" class="end-page-cluster__title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $body ) : ?><p class="end-page-cluster__body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</header>
			<div class="end-page-cluster__cards">
				<?php foreach ( $cards as $card ) : ?>
					<article class="end-page-cluster__card">
						<?php if ( ! empty( $card['image'] ) ) : ?>
							<div class="end-page-cluster__card-media" aria-hidden="true"><?php echo wp_get_attachment_image( absint( $card['image'] ), 'large', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 767.98px) 100vw, 33vw' ) ); ?></div>
						<?php endif; ?>
						<div class="end-page-cluster__card-content">
							<h3 class="end-page-cluster__card-title"><?php echo esc_html( trim( $card['title'] ) ); ?></h3>
							<?php if ( '' !== trim( (string) ( $card['copy'] ?? '' ) ) ) : ?>
								<p class="end-page-cluster__card-body"><?php echo esc_html( trim( $card['copy'] ) ); ?></p>
							<?php endif; ?>
							<?php $render_cta( $card['cta'] ?? null, 'glass', 'end-page-cluster__card-cta' ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return;
}

// Content Trail: each item renders with the existing card for its content type.
$rows      = get_field( 'ltt_dive_in_end_page_cluster_trail_items' );
$rows      = is_array( $rows ) ? array_slice( array_values( array_filter( $rows, 'is_array' ) ), 0, 12 ) : array();
$post_ids  = array();
$event_ids = array();
foreach ( $rows as $row ) {
	$type = $row['type'] ?? '';
	if ( 'story' === $type && ! empty( $row['story'] ) ) {
		$post_ids[] = absint( $row['story'] );
	} elseif ( 'place' === $type && ! empty( $row['place'] ) ) {
		$post_ids[] = absint( $row['place'] );
	} elseif ( 'event' === $type && ! empty( $row['event'] ) && function_exists( 'ltt_dive_in_sanitize_seeker_uuid' ) ) {
		$event_ids[] = ltt_dive_in_sanitize_seeker_uuid( (string) $row['event'] );
	}
}
if ( $post_ids ) {
	_prime_post_caches( $post_ids );
}
$events = array();
if ( array_filter( $event_ids ) && function_exists( 'ltt_dive_in_get_seeker_events_by_id' ) ) {
	foreach ( ltt_dive_in_get_seeker_events_by_id( $event_ids ) as $event ) {
		$events[ $event['id'] ] = $event;
	}
}
$items = array();
foreach ( $rows as $row ) {
	$type = $row['type'] ?? '';
	if ( 'story' === $type && function_exists( 'ltt_dive_in_get_story_driver_story' ) ) {
		$story = ltt_dive_in_get_story_driver_story( absint( $row['story'] ?? 0 ) );
		if ( $story ) {
			$items[] = array( 'type' => 'story', 'story' => $story );
		}
	} elseif ( 'event' === $type ) {
		$uuid = function_exists( 'ltt_dive_in_sanitize_seeker_uuid' ) ? ltt_dive_in_sanitize_seeker_uuid( (string) ( $row['event'] ?? '' ) ) : '';
		if ( $uuid && isset( $events[ $uuid ] ) ) {
			$items[] = array( 'type' => 'event', 'event' => $events[ $uuid ] );
		}
	} elseif ( 'place' === $type ) {
		$place = get_post( absint( $row['place'] ?? 0 ) );
		if ( $place instanceof WP_Post && 'publish' === $place->post_status && has_post_thumbnail( $place ) ) {
			$items[] = array( 'type' => 'place', 'place' => $place );
		}
	}
}
if ( ! $title || ! $items ) {
	$empty_notice( __( 'Add a title and at least one available story, event, or place to preview the Content Trail.', 'ltt-dive-in' ) );
	return;
}
if ( ! $is_preview && function_exists( 'ltt_dive_in_enqueue_end_page_cluster_carousel_assets' ) ) {
	ltt_dive_in_enqueue_end_page_cluster_carousel_assets();
}
$cta        = get_field( 'ltt_dive_in_end_page_cluster_cta' );
$card_sizes = '(max-width: 767.98px) 313px, 335px';
?>
<section id="<?php echo esc_attr( $id ); ?>" class="end-page-cluster end-page-cluster--content-trail<?php echo $is_full ? ' alignfull' : ''; ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="end-page-cluster__container">
		<header class="end-page-cluster__header">
			<div class="end-page-cluster__intro">
				<?php if ( $tag ) : ?><p class="end-page-cluster__tag ltt-tag"><?php echo esc_html( $tag ); ?></p><?php endif; ?>
				<h2 id="<?php echo esc_attr( $id ); ?>-title" class="end-page-cluster__title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $body ) : ?><p class="end-page-cluster__body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</div>
			<?php $render_cta( $cta, 'primary-outline', 'end-page-cluster__header-cta' ); ?>
		</header>
		<div class="end-page-cluster__trail" data-end-page-trail>
			<div class="end-page-cluster__viewport swiper" role="region" aria-label="<?php /* translators: %s: Content Trail title. */ echo esc_attr( sprintf( __( '%s carousel', 'ltt-dive-in' ), $title ) ); ?>" tabindex="0">
				<div class="end-page-cluster__track swiper-wrapper" id="<?php echo esc_attr( $id ); ?>-items">
					<?php foreach ( $items as $item ) : ?>
						<div class="end-page-cluster__slide swiper-slide">
							<?php
							if ( 'story' === $item['type'] ) {
								get_template_part( 'template-parts/components/story-card', null, array( 'story' => $item['story'], 'tile' => true, 'sizes' => $card_sizes ) );
							} elseif ( 'event' === $item['type'] ) {
								get_template_part( 'template-parts/components/event-card', null, array( 'event' => $item['event'], 'style' => 'tile', 'sizes' => $card_sizes ) );
							} else {
								$place_title = get_the_title( $item['place'] );
								?>
								<article class="end-page-cluster__place">
									<div class="end-page-cluster__place-media" aria-hidden="true"><?php echo get_the_post_thumbnail( $item['place'], 'large', array( 'alt' => '', 'loading' => 'lazy', 'sizes' => $card_sizes ) ); ?></div>
									<h3 class="end-page-cluster__place-title">
										<a class="end-page-cluster__place-link" href="<?php echo esc_url( get_permalink( $item['place'] ) ); ?>"><?php echo esc_html( $place_title ); ?></a>
									</h3>
								</article>
								<?php
							}
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( ! $is_preview && count( $items ) > 1 ) : ?>
				<div class="end-page-cluster__controls" data-end-page-trail-controls hidden>
					<div class="end-page-cluster__pagination ltt-carousel-indicators" data-end-page-trail-pagination></div>
					<div class="end-page-cluster__arrows">
						<button type="button" class="ltt-slider-navigation ltt-slider-navigation--white ltt-slider-navigation--previous" data-end-page-trail-previous aria-controls="<?php echo esc_attr( $id ); ?>-items" aria-label="<?php esc_attr_e( 'Previous items', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
						<button type="button" class="ltt-slider-navigation ltt-slider-navigation--next" data-end-page-trail-next aria-controls="<?php echo esc_attr( $id ); ?>-items" aria-label="<?php esc_attr_e( 'Next items', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
