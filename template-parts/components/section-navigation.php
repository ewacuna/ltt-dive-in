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
<nav id="section-navigation" class="ltt-section-navigation" aria-label="<?php echo esc_attr( $section_navigation['label'] ); ?>">
	<div class="ltt-section-navigation__viewport">
		<?php echo $section_navigation['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the core menu renderer. ?>
	</div>
</nav>
