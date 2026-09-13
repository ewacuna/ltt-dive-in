<?php
/**
 * Global accordion composition.
 *
 * @package LTT_Dive_In
 */

$section_id  = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : wp_unique_id( 'global-accordion-' );
$heading     = isset( $args['heading'] ) ? $args['heading'] : '';
$description = isset( $args['description'] ) ? $args['description'] : '';
$cta         = isset( $args['cta'] ) && is_array( $args['cta'] ) ? $args['cta'] : array();
$items       = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( ! $section_id ) {
	$section_id = wp_unique_id( 'global-accordion-' );
}

if ( ! $heading || ! $items ) {
	return;
}

$heading_id = $section_id . '-heading';
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="global-accordion global-accordion--global" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="global-accordion__container">
		<?php
		get_template_part(
			'template-parts/components/accordions/section-header',
			null,
			array(
				'heading_id'  => $heading_id,
				'heading'     => $heading,
				'description' => $description,
				'cta'         => $cta,
			)
		);
		get_template_part(
			'template-parts/components/accordions/list',
			null,
			array(
				'id'    => $section_id,
				'items' => $items,
			)
		);
		?>
	</div>
</section>
