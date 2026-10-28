<?php
/**
 * Optional section navigation, separate from primary-menu disclosures.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section_navigation = isset( $args['navigation'] ) ? $args['navigation'] : array();
if ( empty( $section_navigation['html'] ) ) {
	return;
}

$seamless = ! empty( $args['seamless'] ) && ! empty( $section_navigation['menu_id'] );
?>
<nav id="section-navigation" class="ltt-section-navigation" aria-label="<?php echo esc_attr( $section_navigation['label'] ); ?>"<?php echo $seamless ? ' data-seamless-navigation data-menu="' . esc_attr( $section_navigation['menu_id'] ) . '"' : ''; ?>>
	<div class="ltt-section-navigation__viewport">
		<?php echo $section_navigation['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the core menu renderer. ?>
	</div>
</nav>
<?php if ( $seamless ) : ?>
	<?php /* translators: %s: Title of the page that was loaded. */ ?>
	<p class="screen-reader-text" role="status" data-seamless-status data-message="<?php echo esc_attr__( '%s loaded.', 'ltt-dive-in' ); ?>"></p>
<?php endif; ?>
