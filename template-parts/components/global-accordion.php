<?php
/**
 * Reusable global accordion.
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
$assets_uri = LTT_DIVE_IN_URI . '/assets/images/accordions';
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="global-accordion" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-accordion>
	<div class="global-accordion__container">
		<header class="global-accordion__header">
			<div class="global-accordion__intro">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="global-accordion__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php if ( $description ) : ?>
					<p class="global-accordion__description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $cta['url'] ) && ! empty( $cta['title'] ) ) : ?>
				<div class="global-accordion__actions">
					<a class="global-accordion__section-link" href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ! empty( $cta['target'] ) ? ' target="' . esc_attr( $cta['target'] ) . '" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $cta['title'] ); ?></a>
				</div>
			<?php endif; ?>
		</header>

		<div class="global-accordion__list">
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$trigger_id = $section_id . '-trigger-' . ( $index + 1 );
				$panel_id   = $section_id . '-panel-' . ( $index + 1 );
				$item_link  = isset( $item['link'] ) && is_array( $item['link'] ) ? $item['link'] : array();
				?>
				<div class="global-accordion__item">
					<h3 class="global-accordion__item-heading">
						<button
							id="<?php echo esc_attr( $trigger_id ); ?>"
							class="global-accordion__trigger"
							type="button"
							aria-expanded="false"
							aria-controls="<?php echo esc_attr( $panel_id ); ?>"
							data-accordion-trigger
						>
							<span class="global-accordion__question"><?php echo esc_html( $item['question'] ); ?></span>
							<span class="global-accordion__icon" aria-hidden="true">
								<img class="global-accordion__icon-collapsed" src="<?php echo esc_url( $assets_uri . '/expand-collapsed.svg' ); ?>" alt="" width="21" height="40">
								<img class="global-accordion__icon-expanded" src="<?php echo esc_url( $assets_uri . '/expand-expanded.svg' ); ?>" alt="" width="21" height="40">
							</span>
						</button>
					</h3>

					<div
						id="<?php echo esc_attr( $panel_id ); ?>"
						class="global-accordion__panel"
						role="region"
						aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
						data-accordion-panel
						hidden
					>
						<div class="global-accordion__answer">
							<?php echo wp_kses_post( $item['answer'] ); ?>

							<?php if ( ! empty( $item_link['url'] ) && ! empty( $item_link['title'] ) ) : ?>
								<a class="global-accordion__answer-link" href="<?php echo esc_url( $item_link['url'] ); ?>"<?php echo ! empty( $item_link['target'] ) ? ' target="' . esc_attr( $item_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>>
									<span><?php echo esc_html( $item_link['title'] ); ?></span>
									<img src="<?php echo esc_url( $assets_uri . '/link-carrot.svg' ); ?>" alt="" width="11" height="10">
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
