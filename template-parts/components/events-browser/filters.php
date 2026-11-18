<?php
/**
 * Events browser filters.
 *
 * Renders inside the events browser form. On wider screens the panel sits
 * beside the results; below 768px events.js opens it as a bottom sheet.
 *
 * Expected arguments:
 * - filters: Sanitized filters.
 * - page_url: Events page URL.
 * - results_id: ID of the results region.
 * - submit_link: Optional normalized link.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filters      = $args['filters'];
$page_url     = (string) $args['page_url'];
$results_id   = (string) $args['results_id'];
$submit_link  = is_array( $args['submit_link'] ?? null ) ? $args['submit_link'] : array();
$regions      = ltt_dive_in_get_seeker_regions();
$categories   = ltt_dive_in_get_events_page_categories();
$range_label  = ltt_dive_in_get_events_page_range_label( $filters );
$visible_max  = 4;
$clear_url    = add_query_arg( ltt_dive_in_get_events_page_query_args( $filters, array( 'event_search' => '', 'event_category' => array(), 'event_region' => '', 'event_period' => '', 'event_from' => '', 'event_to' => '' ) ), $page_url ) . '#events-browser';
$region_options = array(
	array(
		'value' => '',
		'label' => __( 'Select Region', 'ltt-dive-in' ),
	),
);

foreach ( $regions as $region_id => $region_name ) {
	$region_options[] = array(
		'value' => (string) $region_id,
		'label' => $region_name,
	);
}
?>
<div id="events-filters" class="events-filters" aria-labelledby="events-filters-title" data-events-filters>
	<div class="events-filters__header">
		<h3 id="events-filters-title" class="screen-reader-text"><?php esc_html_e( 'Filter events', 'ltt-dive-in' ); ?></h3>
		<button class="events-filters__hide" type="button" aria-controls="events-filters" aria-expanded="true" data-events-filters-toggle hidden>
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-filters-hide.svg' ) ); ?>" alt="" width="29" height="24" />
			<?php esc_html_e( 'Hide Filters', 'ltt-dive-in' ); ?>
		</button>
	</div>

	<div class="events-filters__body">
		<?php if ( count( $region_options ) > 1 ) : ?>
			<div class="events-filters__group">
				<p id="events-region-heading" class="events-filters__heading" aria-hidden="true"><?php esc_html_e( 'Locations', 'ltt-dive-in' ); ?></p>
				<?php
				get_template_part(
					'template-parts/components/select',
					null,
					array(
						'id'                => 'events-region',
						'name'              => 'event_region',
						'label'             => __( 'Location', 'ltt-dive-in' ),
						'options'           => $region_options,
						'selected_value'    => $filters['region'] ? (string) $filters['region'] : '',
						'surface'           => 'dark',
						'icon'              => 'assets/images/icons/event-select-location.svg',
						'class_name'        => 'events-filters__select',
						'aria_controls'     => $results_id,
						'native_attributes' => array( 'data-events-filter' => 'region' ),
					)
				);
				?>
			</div>
		<?php endif; ?>

		<fieldset class="events-filters__group">
			<legend class="events-filters__heading"><?php esc_html_e( 'Show Events For', 'ltt-dive-in' ); ?></legend>
			<div class="events-filters__periods">
				<?php foreach ( ltt_dive_in_get_events_page_periods() as $period => $period_label ) : ?>
					<label class="events-filters__period">
						<input class="events-filters__period-input" type="radio" name="event_period" value="<?php echo esc_attr( $period ); ?>" <?php checked( $filters['period'], $period ); ?> data-events-filter="period" />
						<span><?php echo esc_html( $period_label ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="events-filters__group">
			<legend class="events-filters__heading"><?php esc_html_e( 'Dates', 'ltt-dive-in' ); ?></legend>
			<output class="events-filters__range ltt-text-input" for="events-from events-to" aria-live="polite" data-events-range data-empty-label="<?php esc_attr_e( 'All upcoming dates', 'ltt-dive-in' ); ?>"><?php echo esc_html( '' !== $range_label ? $range_label : __( 'All upcoming dates', 'ltt-dive-in' ) ); ?></output>
			<button class="events-filters__dates-toggle" type="button" aria-expanded="false" aria-controls="events-dates" data-events-dates-toggle hidden>
				<img class="events-filters__dates-icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-select-calendar.svg' ) ); ?>" alt="" width="24" height="24" />
				<span class="events-filters__dates-label"><?php esc_html_e( 'Select Dates', 'ltt-dive-in' ); ?></span>
				<span class="events-filters__dates-arrow" aria-hidden="true"></span>
			</button>
			<div id="events-dates" class="events-filters__dates" data-events-dates>
				<label class="events-filters__date">
					<span><?php esc_html_e( 'From', 'ltt-dive-in' ); ?></span>
					<input id="events-from" class="ltt-text-input" type="date" name="event_from" value="<?php echo esc_attr( $filters['from'] ); ?>" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" data-events-filter="from" />
				</label>
				<label class="events-filters__date">
					<span><?php esc_html_e( 'To', 'ltt-dive-in' ); ?></span>
					<input id="events-to" class="ltt-text-input" type="date" name="event_to" value="<?php echo esc_attr( $filters['to'] ); ?>" min="<?php echo esc_attr( $filters['from'] ? $filters['from'] : wp_date( 'Y-m-d' ) ); ?>" data-events-filter="to" />
				</label>
			</div>
		</fieldset>

		<?php if ( $categories ) : ?>
			<fieldset class="events-filters__group events-filters__group--categories">
				<legend class="events-filters__heading"><?php esc_html_e( 'Categories', 'ltt-dive-in' ); ?></legend>
				<button class="events-filters__dates-toggle events-filters__categories-toggle" type="button" aria-expanded="false" aria-controls="events-categories" data-events-categories-dropdown data-label-empty="<?php esc_attr_e( 'Select Categories', 'ltt-dive-in' ); ?>" data-label-selected="<?php /* translators: %d: number of selected categories. */ esc_attr_e( '%d Selected', 'ltt-dive-in' ); ?>" hidden>
					<span class="events-filters__dates-label" data-events-categories-summary><?php esc_html_e( 'Select Categories', 'ltt-dive-in' ); ?></span>
					<span class="events-filters__dates-arrow" aria-hidden="true"></span>
				</button>
				<div id="events-categories" class="events-filters__categories-panel" data-events-categories-panel>
					<ul class="events-filters__categories">
						<?php $index = 0; ?>
						<?php foreach ( $categories as $category_id => $category ) : ?>
							<li class="events-filters__category"<?php echo $index >= $visible_max ? ' data-events-category-extra' : ''; ?>>
								<label class="ltt-checkbox-bubble ltt-checkbox-bubble--dark">
									<input class="ltt-checkbox-bubble__input" type="checkbox" name="event_category[]" value="<?php echo esc_attr( (string) $category_id ); ?>" <?php checked( in_array( $category_id, $filters['categories'], true ) ); ?> data-events-filter="category" />
									<span class="ltt-checkbox-bubble__box" aria-hidden="true"></span>
									<span class="ltt-checkbox-bubble__label"><?php echo esc_html( $category['name'] ); ?></span>
									<?php if ( null !== $category['count'] ) : ?>
										<span class="ltt-checkbox-bubble__count">
											<span aria-hidden="true"><?php echo esc_html( number_format_i18n( $category['count'] ) ); ?></span>
											<span class="screen-reader-text">
												<?php
												/* translators: %s: number of upcoming events. */
												echo esc_html( sprintf( _n( '(%s upcoming event)', '(%s upcoming events)', $category['count'], 'ltt-dive-in' ), number_format_i18n( $category['count'] ) ) );
												?>
											</span>
										</span>
									<?php endif; ?>
								</label>
							</li>
							<?php ++$index; ?>
						<?php endforeach; ?>
					</ul>
					<?php if ( count( $categories ) > $visible_max ) : ?>
						<button class="events-filters__more" type="button" aria-expanded="false" data-events-categories-toggle data-label-more="<?php esc_attr_e( 'Show More', 'ltt-dive-in' ); ?>" data-label-less="<?php esc_attr_e( 'Show Less', 'ltt-dive-in' ); ?>" hidden>
							<span data-events-categories-label><?php esc_html_e( 'Show More', 'ltt-dive-in' ); ?></span>
							<span class="events-filters__more-icon" aria-hidden="true"></span>
						</button>
					<?php endif; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<div class="events-filters__actions">
			<a class="events-filters__clear" href="<?php echo esc_url( $clear_url ); ?>" data-events-clear<?php echo ltt_dive_in_events_page_has_filters( $filters ) ? '' : ' hidden'; ?>>
				<?php esc_html_e( 'Clear Filters', 'ltt-dive-in' ); ?>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-clear.svg' ) ); ?>" alt="" width="24" height="24" />
			</a>
			<button class="ltt-button ltt-button--dark-standard events-filters__apply" type="submit" data-events-apply><?php esc_html_e( 'Apply Filters', 'ltt-dive-in' ); ?></button>
		</div>

		<?php if ( $submit_link ) : ?>
			<div class="events-filters__submit">
				<p class="events-filters__heading"><?php esc_html_e( 'Not seeing your event?', 'ltt-dive-in' ); ?></p>
				<a class="ltt-button ltt-button--dark-standard" href="<?php echo esc_url( $submit_link['url'] ); ?>"<?php echo $submit_link['target'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $submit_link['title'] ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>
