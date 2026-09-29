<?php
/**
 * Listed Events (Figma nodes 1:11591 desktop, 1:11592 mobile).
 *
 * The filters are a GET form, so filtering and Load More work without
 * JavaScript. event-driver.js replaces full-page submissions with requests to
 * the ltt-dive-in/v1/event-driver-events endpoint.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config          = $args['config'];
$section_id      = $args['section_id'];
$is_preview      = ! empty( $args['is_preview'] );
$preview_message = $args['preview_message'];
$block_hash      = (string) $args['block_hash'];
$post_id         = absint( $args['post_id'] );
$is_this_block   = ! $is_preview && isset( $_GET['event_block'] ) && hash_equals( $block_hash, sanitize_key( wp_unslash( $_GET['event_block'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public filters.
$filters         = ltt_dive_in_get_event_driver_listed_filters(
	$config,
	$is_this_block ? array(
		'category' => $_GET['event_category'] ?? 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		'date'     => $_GET['event_date'] ?? '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		'search'   => $_GET['event_search'] ?? '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		'page'     => $_GET['event_page'] ?? 1, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	) : array()
);
$has_filters     = $filters['category'] || 'upcoming' !== $filters['date'] || '' !== $filters['search'];
$limit           = min( 100, $config['initial_count'] + ( $filters['page'] - 1 ) * $config['load_more_count'] );
$result          = ltt_dive_in_query_event_driver_listed_events( $config, $filters, 0, $limit );

if ( is_wp_error( $result ) || ( ! $result['events'] && ! $has_filters ) ) {
	$preview_message( is_wp_error( $result ) ? __( 'Events could not be loaded from Seeker. Check Settings → Events.', 'ltt-dive-in' ) : __( 'There are no upcoming events to list.', 'ltt-dive-in' ) );
	return;
}

$events     = $result['events'];
$categories = $config['show_category'] ? ltt_dive_in_get_event_driver_category_options( $config ) : array();
$form_id    = $section_id . '-filters';
$results_id = $section_id . '-results';
$classes    = array( 'event-driver', 'event-driver--listed', $args['class_name'] );

if ( $is_preview ) {
	$classes[] = 'event-driver--preview';
}

$category_options = array(
	array(
		'value' => '',
		'label' => __( 'Categories', 'ltt-dive-in' ),
	),
);

foreach ( $categories as $category_id => $category_name ) {
	$category_options[] = array(
		'value' => (string) $category_id,
		'label' => $category_name,
	);
}

$date_options = array();

foreach ( ltt_dive_in_get_event_date_filters() as $value => $label ) {
	$date_options[] = array(
		'value' => 'upcoming' === $value ? '' : $value,
		'label' => $label,
	);
}

$show_category = count( $category_options ) > 1;
$show_filters  = $show_category || $config['show_date'] || $config['show_search'];
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $section_id . '-title' ); ?>"<?php if ( ! $is_preview && $post_id ) : ?> data-event-driver-listed data-post="<?php echo esc_attr( (string) $post_id ); ?>" data-block="<?php echo esc_attr( $block_hash ); ?>"<?php endif; ?>>
	<div class="event-driver__container">
		<?php
		get_template_part(
			'template-parts/components/event-driver/header',
			null,
			array(
				'config'     => $config,
				'heading_id' => $section_id . '-title',
				'is_preview' => $is_preview,
			)
		);
		?>

		<form id="<?php echo esc_attr( $form_id ); ?>" class="event-driver__filters<?php echo $show_filters ? '' : ' event-driver__filters--empty'; ?>" method="get" action="<?php echo esc_attr( '#' . $section_id ); ?>" aria-label="<?php esc_attr_e( 'Filter events', 'ltt-dive-in' ); ?>" data-event-driver-filters>
				<input type="hidden" name="event_block" value="<?php echo esc_attr( $block_hash ); ?>" />

				<?php if ( $show_category ) : ?>
					<div class="event-driver__filter event-driver__filter--category">
						<?php
						get_template_part(
							'template-parts/components/select',
							null,
							array(
								'id'                => $section_id . '-category',
								'name'              => 'event_category',
								'label'             => __( 'Event category', 'ltt-dive-in' ),
								'options'           => $category_options,
								'selected_value'    => $filters['category'] ? (string) $filters['category'] : '',
								'surface'           => 'light',
								'aria_controls'     => $results_id,
								'native_attributes' => array( 'data-event-driver-filter' => 'category' ),
							)
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( $config['show_date'] ) : ?>
					<div class="event-driver__filter event-driver__filter--date">
						<?php
						get_template_part(
							'template-parts/components/select',
							null,
							array(
								'id'                => $section_id . '-date',
								'name'              => 'event_date',
								'label'             => __( 'Event dates', 'ltt-dive-in' ),
								'options'           => $date_options,
								'selected_value'    => 'upcoming' === $filters['date'] ? '' : $filters['date'],
								'surface'           => 'light',
								'aria_controls'     => $results_id,
								'native_attributes' => array( 'data-event-driver-filter' => 'date' ),
							)
						);
						?>
					</div>
				<?php endif; ?>

				<?php if ( $config['show_search'] ) : ?>
					<div class="event-driver__search ltt-search-bar ltt-search-bar--filter" role="search">
						<label class="screen-reader-text" for="<?php echo esc_attr( $section_id . '-search' ); ?>"><?php esc_html_e( 'Search events', 'ltt-dive-in' ); ?></label>
						<button class="ltt-search-bar__submit" type="submit">
							<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) ); ?>" alt="" width="28" height="28" />
							<span class="screen-reader-text"><?php esc_html_e( 'Search events', 'ltt-dive-in' ); ?></span>
						</button>
						<input id="<?php echo esc_attr( $section_id . '-search' ); ?>" class="ltt-search-bar__input" type="search" name="event_search" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search...', 'ltt-dive-in' ); ?>" maxlength="80" autocomplete="off" aria-controls="<?php echo esc_attr( $results_id ); ?>" data-event-driver-filter="search" />
					</div>
				<?php endif; ?>

				<?php if ( $show_filters ) : ?>
					<noscript>
						<button class="ltt-button ltt-button--primary-outline event-driver__apply" type="submit"><?php esc_html_e( 'Apply filters', 'ltt-dive-in' ); ?></button>
					</noscript>
				<?php endif; ?>
			</form>

		<p class="screen-reader-text" role="status" data-event-driver-status></p>

		<div class="event-driver__results">
			<div class="event-driver__viewport swiper" data-event-driver-viewport>
				<div id="<?php echo esc_attr( $results_id ); ?>" class="event-driver__grid swiper-wrapper" data-event-driver-grid>
					<?php echo ltt_dive_in_render_event_driver_listed_cards( $events ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the event-card template part. ?>
				</div>
			</div>

			<p class="event-driver__empty" data-event-driver-empty<?php echo $events ? ' hidden' : ''; ?>><?php esc_html_e( 'No events match your filters. Try another category, date range, or search term.', 'ltt-dive-in' ); ?></p>

			<div class="event-driver__carousel-footer" data-event-driver-controls hidden>
				<div class="event-driver__pagination ltt-carousel-indicators" data-event-driver-pagination></div>
				<div class="event-driver__carousel-controls">
					<button class="ltt-slider-navigation ltt-slider-navigation--white" type="button" data-event-driver-previous aria-label="<?php esc_attr_e( 'Previous events', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
					<button class="ltt-slider-navigation" type="button" data-event-driver-next aria-label="<?php esc_attr_e( 'Next events', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
				</div>
			</div>

			<button class="event-driver__load-more" type="submit" form="<?php echo esc_attr( $form_id ); ?>" name="event_page" value="<?php echo esc_attr( (string) ( $filters['page'] + 1 ) ); ?>" data-event-driver-load-more data-offset="<?php echo esc_attr( (string) count( $events ) ); ?>"<?php echo $result['has_more'] ? '' : ' hidden'; ?><?php echo $is_preview ? ' disabled' : ''; ?>>
				<span class="event-driver__load-more-label"><?php esc_html_e( 'Load More', 'ltt-dive-in' ); ?></span>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/header/scroll-arrow.svg' ) ); ?>" alt="" width="14" height="12" />
			</button>
		</div>
	</div>
	<?php ltt_dive_in_print_event_schema( $events ); ?>
</section>
