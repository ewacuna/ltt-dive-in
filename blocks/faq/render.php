<?php
/**
 * FAQ block render template.
 *
 * @package LTT_Dive_In
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview Whether the block is being rendered in the editor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variant        = get_field( 'ltt_dive_in_home_faq_variant' );
$heading        = get_field( 'ltt_dive_in_home_faq_heading' );
$description    = get_field( 'ltt_dive_in_home_faq_description' );
$section_cta    = get_field( 'ltt_dive_in_home_faq_cta' );
$faq_rows       = get_field( 'ltt_dive_in_home_faq_items' );
$toggle_tagline = get_field( 'ltt_dive_in_home_faq_toggle_tagline' );
$toggle_rows    = get_field( 'ltt_dive_in_home_faq_toggle_topics' );
$variants       = array(
	'global'       => 'global',
	'toggle'       => 'toggle',
	'side_by_side' => 'side-by-side',
);
$block_id       = ! empty( $block['anchor'] ) ? $block['anchor'] : 'faq-' . ( isset( $block['id'] ) ? $block['id'] : wp_unique_id() );
$block_id       = sanitize_html_class( $block_id );
$faq_items      = ltt_dive_in_prepare_accordion_items( $faq_rows );
$topics         = ltt_dive_in_prepare_accordion_topics( $toggle_rows );
$is_valid       = is_string( $variant ) && isset( $variants[ $variant ] ) && is_string( $heading ) && trim( $heading );

if ( ! $block_id ) {
	$block_id = wp_unique_id( 'faq-' );
}

if ( $is_valid ) {
	$is_valid = ( 'toggle' === $variant && count( $topics ) >= 2 ) || ( 'toggle' !== $variant && $faq_items );
}

if ( ! $is_valid ) {
	if ( ! empty( $is_preview ) ) {
		?>
		<div class="ltt-faq-block-placeholder">
			<strong><?php esc_html_e( 'FAQ / Accordion', 'ltt-dive-in' ); ?></strong>
			<p><?php esc_html_e( 'Choose a variant and complete its required FAQ fields to preview this module.', 'ltt-dive-in' ); ?></p>
		</div>
		<?php
	}

	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'ltt-faq-block alignfull',
	)
);
?>
<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<?php
	get_template_part(
		'template-parts/components/accordions/' . $variants[ $variant ],
		null,
		array(
			'id'          => $block_id,
			'heading'     => trim( $heading ),
			'description' => is_string( $description ) ? trim( $description ) : '',
			'cta'         => is_array( $section_cta ) ? $section_cta : array(),
			'items'       => $faq_items,
			'tagline'     => is_string( $toggle_tagline ) ? trim( $toggle_tagline ) : '',
			'topics'      => $topics,
		)
	);
	?>
</div>
