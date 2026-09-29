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
?>
<nav class="ltt-section-navigation" aria-label="<?php echo esc_attr( $section_navigation['label'] ); ?>">
	<button class="ltt-section-navigation__toggle" type="button" aria-expanded="false" aria-controls="ltt-section-navigation-menu" hidden>
		<span><?php echo esc_html( $section_navigation['collapsed_label'] ); ?></span>
		<img src="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/icons/select-toggle.svg' ); ?>" alt="" width="15" height="12">
	</button>
	<?php echo $section_navigation['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the core menu renderer. ?>
</nav>
