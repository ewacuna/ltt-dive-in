<?php
/**
 * Shared accordion section heading and optional call to action.
 *
 * @package LTT_Dive_In
 */

$heading_id  = isset( $args['heading_id'] ) ? sanitize_html_class( $args['heading_id'] ) : '';
$heading     = isset( $args['heading'] ) && is_string( $args['heading'] ) ? $args['heading'] : '';
$description = isset( $args['description'] ) && is_string( $args['description'] ) ? $args['description'] : '';
$tagline     = isset( $args['tagline'] ) && is_string( $args['tagline'] ) ? $args['tagline'] : '';
$cta         = isset( $args['cta'] ) && is_array( $args['cta'] ) ? $args['cta'] : array();

if ( ! $heading_id || ! $heading ) {
	return;
}
?>
<header class="global-accordion__header">
	<div class="global-accordion__intro">
		<?php if ( $tagline ) : ?>
			<p class="global-accordion__tagline"><?php echo esc_html( $tagline ); ?></p>
		<?php endif; ?>

		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="global-accordion__heading"><?php echo esc_html( $heading ); ?></h2>

		<?php if ( $description ) : ?>
			<p class="global-accordion__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $cta['url'] ) && ! empty( $cta['title'] ) ) : ?>
		<div class="global-accordion__actions">
			<a class="global-accordion__section-link ltt-button ltt-button--primary-outline" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ! empty( $cta['target'] ) ? ' target="' . esc_attr( $cta['target'] ) . '" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $cta['title'] ); ?></a>
		</div>
	<?php endif; ?>
</header>
