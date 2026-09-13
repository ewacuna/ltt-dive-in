<?php
/**
 * Topic-toggle accordion composition.
 *
 * @package LTT_Dive_In
 */

$section_id  = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : wp_unique_id( 'toggle-accordion-' );
$heading     = isset( $args['heading'] ) ? $args['heading'] : '';
$description = isset( $args['description'] ) ? $args['description'] : '';
$tagline     = isset( $args['tagline'] ) ? $args['tagline'] : '';
$cta         = isset( $args['cta'] ) && is_array( $args['cta'] ) ? $args['cta'] : array();
$topics      = isset( $args['topics'] ) && is_array( $args['topics'] ) ? $args['topics'] : array();

if ( ! $section_id ) {
	$section_id = wp_unique_id( 'toggle-accordion-' );
}

if ( ! $heading || count( $topics ) < 2 ) {
	return;
}

$heading_id = $section_id . '-heading';
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="global-accordion global-accordion--toggle" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-toggle-accordion>
	<div class="global-accordion__container">
		<?php
		get_template_part(
			'template-parts/components/accordions/section-header',
			null,
			array(
				'heading_id'  => $heading_id,
				'heading'     => $heading,
				'description' => $description,
				'tagline'     => $tagline,
				'cta'         => $cta,
			)
		);
		?>

		<div class="global-accordion__topics" role="tablist" aria-label="<?php esc_attr_e( 'FAQ topics', 'ltt-dive-in' ); ?>">
			<?php foreach ( $topics as $index => $topic ) : ?>
				<?php
				$tab_id   = $section_id . '-topic-' . ( $index + 1 );
				$panel_id = $section_id . '-topic-panel-' . ( $index + 1 );
				?>
				<button
					id="<?php echo esc_attr( $tab_id ); ?>"
					class="global-accordion__topic"
					type="button"
					role="tab"
					aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $panel_id ); ?>"
					tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
					data-toggle-tab
				><?php echo esc_html( $topic['label'] ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="global-accordion__topic-panels">
			<?php foreach ( $topics as $index => $topic ) : ?>
				<?php
				$tab_id   = $section_id . '-topic-' . ( $index + 1 );
				$panel_id = $section_id . '-topic-panel-' . ( $index + 1 );
				?>
				<div
					id="<?php echo esc_attr( $panel_id ); ?>"
					class="global-accordion__topic-panel"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"
					data-toggle-panel
					<?php echo 0 === $index ? '' : ' hidden'; ?>
				>
					<?php
					get_template_part(
						'template-parts/components/accordions/list',
						null,
						array(
							'id'    => $panel_id,
							'items' => $topic['items'],
						)
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
