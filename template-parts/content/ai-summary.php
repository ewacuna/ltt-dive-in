<?php
/**
 * "Show Summary" disclosure in the Stories Hero (Figma review file, Stories
 * Hero Desktop 95:86676, "EXPANDED" instance and its open state).
 *
 * The summary sits above its toggle, so it comes first in the DOM to keep the
 * reading order equal to the visual order. Driven by the shared accordion
 * script; the closed icon is the Figma instance's own, the open icon is the
 * shared accordion one. The text stays in the server-rendered HTML while
 * collapsed. Renders nothing when the post has no saved summary.
 *
 * @package LTT_Dive_In
 */

$ltt_dive_in_summary = function_exists( 'ltt_dive_in_get_ai_summary' ) ? ltt_dive_in_get_ai_summary() : '';

if ( '' === $ltt_dive_in_summary ) {
	return;
}

$ltt_dive_in_summary_id = 'stories-hero-summary-' . get_the_ID();
?>
<div class="stories-hero__summary" data-accordion>
	<div
		id="<?php echo esc_attr( $ltt_dive_in_summary_id ); ?>"
		class="stories-hero__summary-panel"
		role="region"
		aria-labelledby="<?php echo esc_attr( $ltt_dive_in_summary_id ); ?>-toggle"
		hidden
	>
		<div class="stories-hero__summary-clip">
			<p class="stories-hero__summary-text">
				<strong class="stories-hero__summary-label"><?php esc_html_e( 'Summary:', 'ltt-dive-in' ); ?></strong>
				<?php echo esc_html( $ltt_dive_in_summary ); ?>
			</p>
		</div>
	</div>

	<button
		id="<?php echo esc_attr( $ltt_dive_in_summary_id ); ?>-toggle"
		class="stories-hero__summary-toggle"
		type="button"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $ltt_dive_in_summary_id ); ?>"
		data-accordion-trigger
	>
		<span class="stories-hero__summary-show"><?php esc_html_e( 'Show Summary', 'ltt-dive-in' ); ?></span>
		<span class="stories-hero__summary-hide"><?php esc_html_e( 'Hide Summary', 'ltt-dive-in' ); ?></span>
		<span class="stories-hero__summary-icon" aria-hidden="true">
			<img class="stories-hero__summary-icon-closed" src="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/icons/summary-expand-closed.svg' ); ?>" alt="" width="21" height="40">
			<img class="stories-hero__summary-icon-open" src="<?php echo esc_url( LTT_DIVE_IN_URI . '/assets/images/accordions/expand-collapsed.svg' ); ?>" alt="" width="21" height="40">
		</span>
	</button>
</div>
