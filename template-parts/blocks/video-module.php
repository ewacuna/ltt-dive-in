<?php
/**
 * Video Module ACF block renderer.
 *
 * Players are represented by their poster until a visitor explicitly presses
 * Play. This keeps third-party player scripts and self-hosted media off the
 * initial request while retaining a useful no-JavaScript fallback link.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = ! empty( $is_preview );
$variant    = (string) get_field( 'ltt_dive_in_video_module_variant' );
$variants   = array( 'editorial', 'full_bleed', 'episodic', 'full_bleed_copy', 'carousel' );
$raw_items  = (array) get_field( 'ltt_dive_in_video_module_items' );
$items      = array();

if ( ! in_array( $variant, $variants, true ) ) {
	$variant = 'editorial';
}

$normalize_link = static function ( $link ) {
	if ( ! is_array( $link ) || empty( $link['title'] ) || empty( $link['url'] ) ) {
		return array();
	}

	return array(
		'title'  => trim( (string) $link['title'] ),
		'url'    => (string) $link['url'],
		'target' => ! empty( $link['target'] ) ? (string) $link['target'] : '',
	);
};

$get_player = static function ( $item ) {
	$source = isset( $item['source'] ) ? (string) $item['source'] : '';
	$url    = '';

	if ( 'cloudflare' === $source ) {
		$uid = isset( $item['cloudflare_id'] ) ? trim( (string) $item['cloudflare_id'] ) : '';

		if ( preg_match( '/^[A-Za-z0-9_-]+$/', $uid ) ) {
			$url = 'https://iframe.videodelivery.net/' . rawurlencode( $uid ) . '?preload=metadata';
		}
	} elseif ( 'external' === $source ) {
		$external = isset( $item['external_url'] ) ? esc_url_raw( $item['external_url'] ) : '';
		$host     = strtolower( (string) wp_parse_url( $external, PHP_URL_HOST ) );
		$path     = trim( (string) wp_parse_url( $external, PHP_URL_PATH ), '/' );

		if ( in_array( $host, array( 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be' ), true ) ) {
			$video_id = 'youtu.be' === $host ? strtok( $path, '/' ) : '';

			if ( ! $video_id && preg_match( '#^(?:embed|shorts)/([^/]+)#', $path, $matches ) ) {
				$video_id = $matches[1];
			}

			if ( ! $video_id ) {
				parse_str( (string) wp_parse_url( $external, PHP_URL_QUERY ), $query );
				$video_id = isset( $query['v'] ) ? (string) $query['v'] : '';
			}

			if ( preg_match( '/^[A-Za-z0-9_-]{6,20}$/', $video_id ) ) {
				$url = 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $video_id ) . '?autoplay=1&rel=0';
			}
		} elseif ( in_array( $host, array( 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com' ), true ) && preg_match( '/(?:video\/)?([0-9]+)/', $path, $matches ) ) {
			$url = 'https://player.vimeo.com/video/' . rawurlencode( $matches[1] ) . '?autoplay=1';
		}
	} elseif ( 'self_hosted' === $source ) {
		$file_id = isset( $item['mp4'] ) ? absint( $item['mp4'] ) : 0;
		$url     = $file_id ? (string) wp_get_attachment_url( $file_id ) : '';
	}

	return $url ? array( 'source' => $source, 'url' => $url ) : array();
};

foreach ( array_slice( $raw_items, 0, 12 ) as $raw_item ) {
	if ( ! is_array( $raw_item ) ) {
		continue;
	}

	$player = $get_player( $raw_item );
	$poster = isset( $raw_item['poster'] ) ? absint( $raw_item['poster'] ) : 0;

	if ( ! $player || ! $poster ) {
		continue;
	}

	$items[] = array(
		'player'  => $player,
		'poster'  => $poster,
		'tag'     => isset( $raw_item['tag'] ) ? trim( (string) $raw_item['tag'] ) : '',
		'duration' => isset( $raw_item['duration'] ) ? trim( (string) $raw_item['duration'] ) : '',
		'title'   => isset( $raw_item['title'] ) ? trim( (string) $raw_item['title'] ) : '',
		'body'    => isset( $raw_item['body'] ) ? (string) $raw_item['body'] : '',
		'cta'     => $normalize_link( $raw_item['cta'] ?? array() ),
	);
}

$minimum = in_array( $variant, array( 'episodic', 'carousel' ), true ) ? 2 : 1;
$maximum = 'carousel' === $variant ? 8 : ( 'episodic' === $variant ? 12 : 1 );

if ( count( $items ) < $minimum ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add the required videos and complete each source and poster image to preview this module.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

$items = array_slice( $items, 0, $maximum );

if ( 'full_bleed' !== $variant ) {
	foreach ( $items as $item ) {
		if ( ! $item['title'] ) {
			if ( $is_preview ) {
				echo '<p>' . esc_html__( 'Every video in this variant needs a title.', 'ltt-dive-in' ) . '</p>';
			}
			return;
		}
	}
}

if ( 'carousel' === $variant && function_exists( 'ltt_dive_in_enqueue_video_module_carousel_assets' ) ) {
	ltt_dive_in_enqueue_video_module_carousel_assets();
}

$block_id   = ! empty( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id( 'video-module-' );
$section_id = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'video-module-' . $block_id;
$classes    = array( 'video-module', 'video-module--' . $variant );

if ( ! empty( $block['align'] ) && 'full' === $block['align'] ) {
	$classes[] = 'alignfull';
}

if ( $is_preview ) {
	$classes[] = 'video-module--preview';
}

$render_cta = static function ( $link, $modifier = 'primary-outline' ) {
	if ( ! $link ) {
		return;
	}

	$target = $link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener noreferrer"' : '';
	echo '<a class="video-module__cta ltt-button ltt-button--' . esc_attr( $modifier ) . '" href="' . esc_url( $link['url'] ) . '"' . $target . '>' . esc_html( $link['title'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
};

$render_player = static function ( $item, $label, $preview = false, $modal = false ) {
	$source = $item['player']['source'];
	$url    = $item['player']['url'];
	$attrs  = ' data-video-source="' . esc_attr( $source ) . '" data-video-url="' . esc_url( $url ) . '"';

	if ( $modal ) {
		echo '<button class="video-module__episode-play ltt-slider-navigation" type="button" data-video-modal-trigger' . $attrs . ' data-video-title="' . esc_attr( $label ) . '">';
		echo '<img src="' . esc_url( get_theme_file_uri( 'assets/images/icons/video-play.svg' ) ) . '" alt="" aria-hidden="true" />';
		echo '<span class="screen-reader-text">' . esc_html( sprintf( __( 'Play %s', 'ltt-dive-in' ), $label ) ) . '</span></button>';
		return;
	}

	echo '<div class="video-module__player" data-video-player' . $attrs . '>';
	echo wp_get_attachment_image( $item['poster'], 'full', false, array( 'class' => 'video-module__poster', 'alt' => '', 'loading' => 'lazy' ) );

	if ( ! $preview ) {
		echo '<button class="video-module__play" type="button" data-video-play aria-label="' . esc_attr( sprintf( __( 'Play %s', 'ltt-dive-in' ), $label ) ) . '">';
		echo '<img src="' . esc_url( get_theme_file_uri( 'assets/images/icons/video-play.svg' ) ) . '" alt="" aria-hidden="true" /></button>';
	}

	echo '<noscript><a class="video-module__fallback" href="' . esc_url( $url ) . '">' . esc_html( sprintf( __( 'Watch %s', 'ltt-dive-in' ), $label ) ) . '</a></noscript>';
	echo '</div>';
};

$render_copy = static function ( $item, $heading_id = '' ) use ( $render_cta ) {
	if ( $item['tag'] ) {
		echo '<p class="video-module__tag">' . esc_html( $item['tag'] ) . '</p>';
	}

	$id = $heading_id ? ' id="' . esc_attr( $heading_id ) . '"' : '';
	echo '<h2' . $id . ' class="video-module__title">' . esc_html( $item['title'] ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	if ( $item['body'] ) {
		echo '<div class="video-module__body">' . wp_kses_post( wpautop( $item['body'] ) ) . '</div>';
	}

	$render_cta( $item['cta'] );
};
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-video-module<?php echo 'full_bleed' === $variant ? ' aria-label="' . esc_attr__( 'Video', 'ltt-dive-in' ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( 'editorial' === $variant ) : ?>
		<div class="video-module__container video-module__editorial">
			<div class="video-module__copy"><?php $render_copy( $items[0], $section_id . '-title' ); ?></div>
			<div class="video-module__editorial-media"><?php $render_player( $items[0], $items[0]['title'], $is_preview ); ?></div>
		</div>

	<?php elseif ( 'full_bleed' === $variant ) : ?>
		<?php $render_player( $items[0], $items[0]['title'] ? $items[0]['title'] : __( 'video', 'ltt-dive-in' ), $is_preview ); ?>

	<?php elseif ( 'full_bleed_copy' === $variant ) : ?>
		<?php $render_player( $items[0], $items[0]['title'], $is_preview ); ?>
		<div class="video-module__container video-module__copy video-module__copy--full"><?php $render_copy( $items[0], $section_id . '-title' ); ?></div>

	<?php elseif ( 'carousel' === $variant ) : ?>
		<div class="video-module__container" data-video-carousel>
			<div class="video-module__carousel-viewport" data-video-carousel-viewport>
				<div class="video-module__carousel-track" data-video-carousel-track>
					<?php foreach ( $items as $index => $item ) : ?>
						<article class="video-module__carousel-slide" data-video-carousel-slide aria-labelledby="<?php echo esc_attr( $section_id . '-slide-' . ( $index + 1 ) ); ?>">
							<div class="video-module__carousel-copy"><?php $render_copy( $item, $section_id . '-slide-' . ( $index + 1 ) ); ?></div>
							<?php $render_player( $item, $item['title'], $is_preview ); ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( ! $is_preview ) : ?>
				<div class="video-module__carousel-footer">
					<div class="video-module__carousel-pagination ltt-carousel-indicators" data-video-carousel-pagination></div>
					<div class="video-module__carousel-controls">
						<button class="ltt-slider-navigation ltt-slider-navigation--white" type="button" data-video-carousel-previous aria-label="<?php esc_attr_e( 'Previous video', 'ltt-dive-in' ); ?>" hidden><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
						<button class="ltt-slider-navigation" type="button" data-video-carousel-next aria-label="<?php esc_attr_e( 'Next video', 'ltt-dive-in' ); ?>" hidden><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
					</div>
				</div>
			<?php endif; ?>
		</div>

	<?php else : ?>
		<?php
		$heading = trim( (string) get_field( 'ltt_dive_in_video_module_heading' ) );
		$eyebrow = trim( (string) get_field( 'ltt_dive_in_video_module_eyebrow' ) );
		$intro   = trim( (string) get_field( 'ltt_dive_in_video_module_intro' ) );
		$tags    = array_values( array_unique( array_filter( wp_list_pluck( array_slice( $items, 1 ), 'tag' ) ) ) );
		?>
		<div class="video-module__container video-module__episodes">
			<header class="video-module__episodes-header">
				<?php if ( $eyebrow ) : ?><p class="video-module__tag"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
				<h2 id="<?php echo esc_attr( $section_id . '-title' ); ?>" class="video-module__title"><?php echo esc_html( $heading ? $heading : $items[0]['title'] ); ?></h2>
				<?php if ( $intro ) : ?><p class="video-module__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
			</header>
			<article class="video-module__featured-episode">
				<?php $render_player( $items[0], $items[0]['title'], $is_preview ); ?>
				<div class="video-module__featured-copy">
					<?php if ( $items[0]['duration'] ) : ?><p class="video-module__duration"><?php echo esc_html( $items[0]['duration'] ); ?></p><?php endif; ?>
					<h3 class="video-module__episode-title"><?php echo esc_html( $items[0]['title'] ); ?></h3>
					<?php if ( $items[0]['body'] ) : ?><div class="video-module__episode-body"><?php echo wp_kses_post( wpautop( $items[0]['body'] ) ); ?></div><?php endif; ?>
					<?php $render_cta( $items[0]['cta'] ); ?>
				</div>
			</article>
			<?php if ( $tags ) : ?>
				<div class="video-module__filters" role="group" aria-label="<?php esc_attr_e( 'Filter episodes', 'ltt-dive-in' ); ?>">
					<button type="button" class="video-module__filter ltt-category-selection" data-video-filter="all" aria-pressed="true"><?php esc_html_e( 'All', 'ltt-dive-in' ); ?></button>
					<?php foreach ( $tags as $tag ) : ?><button type="button" class="video-module__filter ltt-category-selection" data-video-filter="<?php echo esc_attr( sanitize_title( $tag ) ); ?>" aria-pressed="false"><?php echo esc_html( $tag ); ?></button><?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="video-module__episode-list" data-video-episode-list>
				<?php foreach ( array_slice( $items, 1 ) as $item ) : ?>
					<article class="video-module__episode" data-video-episode="<?php echo esc_attr( sanitize_title( $item['tag'] ) ); ?>">
						<div class="video-module__episode-copy">
							<div class="video-module__meta"><?php if ( $item['tag'] ) : ?><span class="video-module__tag"><?php echo esc_html( $item['tag'] ); ?></span><?php endif; ?><?php if ( $item['duration'] ) : ?><span class="video-module__duration"><?php echo esc_html( $item['duration'] ); ?></span><?php endif; ?></div>
							<h3 class="video-module__episode-title"><?php echo esc_html( $item['title'] ); ?></h3>
							<?php if ( $item['body'] ) : ?><div class="video-module__episode-body"><?php echo wp_kses_post( wpautop( $item['body'] ) ); ?></div><?php endif; ?>
						</div>
						<?php if ( ! $is_preview ) : ?><?php $render_player( $item, $item['title'], false, true ); ?><?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( 'episodic' === $variant && ! $is_preview ) : ?>
		<dialog class="video-module__dialog" data-video-dialog aria-labelledby="<?php echo esc_attr( $section_id . '-dialog-title' ); ?>">
			<div class="video-module__dialog-inner">
				<div class="video-module__dialog-header"><h2 id="<?php echo esc_attr( $section_id . '-dialog-title' ); ?>" data-video-dialog-title></h2><button type="button" class="video-module__dialog-close" data-video-dialog-close><?php esc_html_e( 'Close', 'ltt-dive-in' ); ?></button></div>
				<div class="video-module__dialog-player" data-video-dialog-player></div>
			</div>
		</dialog>
	<?php endif; ?>
</section>
