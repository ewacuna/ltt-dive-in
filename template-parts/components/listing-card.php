<?php
/**
 * Listing card presentation. Data is supplied by the owning module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = isset( $args['title'] ) ? trim( (string) $args['title'] ) : '';
$copy       = isset( $args['copy'] ) ? trim( (string) $args['copy'] ) : '';
$image_id   = isset( $args['image_id'] ) ? absint( $args['image_id'] ) : 0;
$buttons    = isset( $args['buttons'] ) && is_array( $args['buttons'] ) ? $args['buttons'] : array();
$is_preview = ! empty( $args['is_preview'] );

$buttons = array_slice(
	array_values(
		array_filter(
			$buttons,
			static function ( $button ) {
				return is_array( $button ) && ! empty( $button['title'] ) && ! empty( $button['url'] ) && (bool) esc_url( $button['url'] );
			}
		)
	),
	0,
	2
);

if ( '' === $title ) {
	return;
}
?>
<article class="ltt-listing-module__card<?php echo $buttons ? '' : ' ltt-listing-module__card--without-buttons'; ?>">
	<div class="ltt-listing-module__media<?php echo $image_id ? '' : ' ltt-listing-module__media--empty'; ?>">
		<?php if ( $image_id ) : ?>
			<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'ltt-listing-module__image', 'sizes' => '(max-width: 767.98px) 100vw, (max-width: 1199.98px) 50vw, 33vw' ) ); ?>
		<?php endif; ?>
		<div class="ltt-listing-module__card-copy">
			<h3 class="ltt-listing-module__card-title"><?php echo esc_html( $title ); ?></h3>
			<?php if ( '' !== $copy ) : ?><p class="ltt-listing-module__card-description"><?php echo esc_html( $copy ); ?></p><?php endif; ?>
		</div>
	</div>
	<?php if ( $buttons ) : ?>
		<div class="ltt-listing-module__card-actions">
			<?php foreach ( $buttons as $button_index => $button ) : ?>
				<?php
				$modifier = isset( $button['variant'] ) && in_array( $button['variant'], array( 'primary-outline', 'secondary-outline' ), true )
					? $button['variant']
					: ( 0 === $button_index ? 'primary-outline' : 'secondary-outline' );
				$accessible_name = sprintf(
					/* translators: 1: button label, 2: listing title. */
					__( '%1$s for %2$s', 'ltt-dive-in' ),
					$button['title'],
					$title
				);
				if ( '_blank' === ( $button['target'] ?? '' ) ) {
					$accessible_name .= ' ' . __( '(opens in a new tab)', 'ltt-dive-in' );
				}
				?>
				<?php if ( $is_preview ) : ?>
					<span class="ltt-button ltt-button--<?php echo esc_attr( $modifier ); ?> ltt-listing-module__card-button"><?php echo esc_html( $button['title'] ); ?></span>
				<?php else : ?>
					<a class="ltt-button ltt-button--<?php echo esc_attr( $modifier ); ?> ltt-listing-module__card-button" href="<?php echo esc_url( $button['url'] ); ?>" aria-label="<?php echo esc_attr( $accessible_name ); ?>"<?php if ( '_blank' === ( $button['target'] ?? '' ) ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo esc_html( $button['title'] ); ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</article>
