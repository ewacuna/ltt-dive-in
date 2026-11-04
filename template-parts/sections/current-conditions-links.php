<?php
/**
 * Current Conditions tab: Related conditions prompts.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$links = ltt_dive_in_get_current_conditions_links( (int) ( $args['post_id'] ?? 0 ) );

if ( ! $links ) {
	return;
}
?>
<div class="current-conditions-links">
	<div class="current-conditions-links__container">
		<?php foreach ( $links as $link ) : ?>
			<div class="current-conditions-links__item">
				<div class="current-conditions-links__copy">
					<h2 class="current-conditions-links__title"><?php echo esc_html( $link['title'] ); ?></h2>

					<?php if ( $link['text'] ) : ?>
						<p class="current-conditions-links__text"><?php echo esc_html( $link['text'] ); ?></p>
					<?php endif; ?>
				</div>

				<a class="ltt-button ltt-button--primary-fill" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['new_tab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $link['label'] ); ?></a>
			</div>
		<?php endforeach; ?>
	</div>
</div>
