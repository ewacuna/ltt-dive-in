<?php
/**
 * Sidebar template.
 *
 * @package LTT_Dive_In
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area" aria-label="<?php esc_attr_e( 'Blog sidebar', 'ltt-dive-in' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
