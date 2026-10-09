<?php
/**
 * Event Driver section header (Figma “Left-Align Header w CTA”).
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config     = isset( $args['config'] ) && is_array( $args['config'] ) ? $args['config'] : array();
$heading_id = isset( $args['heading_id'] ) ? (string) $args['heading_id'] : '';
$is_preview = ! empty( $args['is_preview'] );
$link_tag   = $is_preview ? 'span' : 'a';

if ( empty( $config['heading'] ) ) {
	return;
}
?>
<header class="event-driver__header">
	<div class="event-driver__intro">
		<?php if ( $config['tag'] ) : ?>
			<p class="event-driver__tag event-driver__tag--light ltt-tag"><?php echo esc_html( $config['tag'] ); ?></p>
		<?php endif; ?>
		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="event-driver__heading"><?php echo esc_html( $config['heading'] ); ?></h2>
		<?php if ( $config['intro'] ) : ?>
			<p class="event-driver__copy"><?php echo esc_html( $config['intro'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $config['links'] ) : ?>
		<div class="event-driver__header-actions">
			<?php foreach ( $config['links'] as $index => $link ) : ?>
				<<?php echo esc_attr( $link_tag ); ?> class="ltt-button ltt-button--<?php echo esc_attr( 0 === $index ? 'primary-outline' : 'secondary-outline' ); ?>"<?php if ( ! $is_preview ) : ?> href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?><?php endif; ?>><?php echo esc_html( $link['title'] ); ?></<?php echo esc_attr( $link_tag ); ?>>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</header>
