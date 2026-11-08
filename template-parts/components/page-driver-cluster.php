<?php
/**
 * Page Driver: a centered header and three full-bleed image cards.
 *
 * Shared by the End Page Cluster block's Page Driver variant and the
 * end-of-post driver in single.php. Styles live in
 * assets/css/components/end-page-cluster.css.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string $id      Unique section ID.
 *     @type string $tag     Optional category tag.
 *     @type string $title   Required heading.
 *     @type string $body    Optional intro copy.
 *     @type array  $cards   Up to three cards with image, title, copy, and cta
 *                           (a link array; optional `context` adds screen-reader text).
 *     @type bool   $navy    Use the Navy background.
 *     @type bool   $full    Add the alignfull class.
 *     @type bool   $preview Render links as inert spans for the editor preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args       = wp_parse_args(
	is_array( $args ?? null ) ? $args : array(),
	array(
		'id'      => '',
		'tag'     => '',
		'title'   => '',
		'body'    => '',
		'cards'   => array(),
		'navy'    => false,
		'full'    => false,
		'preview' => false,
	)
);
$id         = $args['id'] ? sanitize_title( $args['id'] ) : wp_unique_id( 'page-driver-' );
$tag        = trim( (string) $args['tag'] );
$title      = trim( (string) $args['title'] );
$body       = trim( (string) $args['body'] );
$is_navy    = (bool) $args['navy'];
$is_preview = (bool) $args['preview'];
$cards      = is_array( $args['cards'] ) ? array_slice( array_values( array_filter( $args['cards'], static function ( $card ) {
	return is_array( $card ) && '' !== trim( (string) ( $card['title'] ?? '' ) );
} ) ), 0, 3 ) : array();

if ( '' === $title || ! $cards ) {
	return;
}

$classes = array( 'end-page-cluster', 'end-page-cluster--page-driver' );
if ( $is_navy ) {
	$classes[] = 'end-page-cluster--page-driver-navy';
}
if ( $args['full'] ) {
	$classes[] = 'alignfull';
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="end-page-cluster__container">
		<header class="end-page-cluster__header">
			<?php if ( $tag ) : ?><p class="end-page-cluster__tag ltt-tag<?php echo $is_navy ? ' ltt-tag--dark' : ''; ?>"><?php echo esc_html( $tag ); ?></p><?php endif; ?>
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="end-page-cluster__title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $body ) : ?><p class="end-page-cluster__body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
		</header>
		<div class="end-page-cluster__cards">
			<?php foreach ( $cards as $card ) : ?>
				<?php
				$cta     = $card['cta'] ?? null;
				$has_cta = is_array( $cta ) && '' !== trim( (string) ( $cta['title'] ?? '' ) ) && ! empty( $cta['url'] ) && (bool) esc_url( $cta['url'] );
				// Optional context names the destination of a repeated label such as “Read More”.
				$context = $has_cta ? trim( (string) ( $cta['context'] ?? '' ) ) : '';
				$label   = $has_cta ? esc_html( trim( $cta['title'] ) ) . ( $context ? '<span class="screen-reader-text">: ' . esc_html( $context ) . '</span>' : '' ) : '';
				?>
				<article class="end-page-cluster__card">
					<?php if ( ! empty( $card['image'] ) ) : ?>
						<div class="end-page-cluster__card-media" aria-hidden="true"><?php echo wp_get_attachment_image( absint( $card['image'] ), 'large', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 767.98px) 100vw, 33vw' ) ); ?></div>
					<?php endif; ?>
					<div class="end-page-cluster__card-content">
						<h3 class="end-page-cluster__card-title"><?php echo esc_html( trim( $card['title'] ) ); ?></h3>
						<?php if ( '' !== trim( (string) ( $card['copy'] ?? '' ) ) ) : ?>
							<p class="end-page-cluster__card-body"><?php echo esc_html( trim( $card['copy'] ) ); ?></p>
						<?php endif; ?>
						<?php if ( $has_cta && $is_preview ) : ?>
							<span class="ltt-button ltt-button--glass end-page-cluster__card-cta"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></span>
						<?php elseif ( $has_cta ) : ?>
							<a class="ltt-button ltt-button--glass end-page-cluster__card-cta" href="<?php echo esc_url( $cta['url'] ); ?>"<?php if ( '_blank' === ( $cta['target'] ?? '' ) ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
