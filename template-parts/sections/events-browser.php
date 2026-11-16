<?php
/**
 * Events browser: toolbar, filters, and results.
 *
 * The toolbar and filters are one GET form, so filtering, view changes, and
 * Load More work without JavaScript. events.js replaces full-page
 * submissions with requests to the ltt-dive-in/v1/events endpoint.
 *
 * Expected arguments:
 * - page_id: Events page ID.
 * - submit_link: Optional normalized link for visitors whose event is missing.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id   = isset( $args['page_id'] ) ? absint( $args['page_id'] ) : get_the_ID();
$page_url  = get_permalink( $page_id );
$filters   = ltt_dive_in_get_events_page_filters( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public filters.
$limits    = ltt_dive_in_get_events_page_limits();
$limit     = min( 100, $limits['initial'] + ( $filters['page'] - 1 ) * $limits['load_more'] );
$result    = ltt_dive_in_query_events_page( $filters, 0, $limit );
$is_error  = is_wp_error( $result );
$events    = $is_error ? array() : $result['events'];
$view      = $filters['view'];
$results_id = 'events-results';

$view_links = array(
	'grid' => array(
		'label' => __( 'Grid', 'ltt-dive-in' ),
		'icon'  => 'event-view-grid.svg',
		'width' => 24,
	),
	'list' => array(
		'label' => __( 'List', 'ltt-dive-in' ),
		'icon'  => 'event-view-list.svg',
		'width' => 30,
	),
);

$sort_options = array();

foreach ( ltt_dive_in_get_events_page_sorts() as $value => $label ) {
	$sort_options[] = array(
		'value' => $value,
		'label' => $label,
	);
}
?>
<section id="events-browser" class="events-browser" aria-labelledby="events-browser-title" data-events-browser data-endpoint="<?php echo esc_url( rest_url( 'ltt-dive-in/v1/events' ) ); ?>">
	<h2 id="events-browser-title" class="screen-reader-text"><?php esc_html_e( 'Browse events', 'ltt-dive-in' ); ?></h2>

	<form id="events-browser-form" class="events-browser__layout" method="get" action="<?php echo esc_url( $page_url . '#events-browser' ); ?>" data-events-form>
		<input type="hidden" name="event_view" value="<?php echo esc_attr( 'list' === $view ? 'list' : '' ); ?>" data-events-view-input />

		<?php
		get_template_part(
			'template-parts/components/events-browser/filters',
			null,
			array(
				'filters'     => $filters,
				'page_url'    => $page_url,
				'results_id'  => $results_id,
				'submit_link' => $args['submit_link'] ?? array(),
			)
		);
		?>

		<div class="events-browser__main">
			<div class="events-toolbar">
				<button class="events-toolbar__filters" type="button" aria-controls="events-filters" aria-expanded="false" data-events-filters-toggle hidden>
					<span class="events-toolbar__filters-icon" aria-hidden="true"></span>
					<span class="events-toolbar__filters-label"><?php esc_html_e( 'Show Filters', 'ltt-dive-in' ); ?></span>
				</button>

				<div class="events-toolbar__group events-toolbar__group--view">
					<span id="events-view-label" class="events-toolbar__label"><?php esc_html_e( 'View:', 'ltt-dive-in' ); ?></span>
					<ul class="events-toolbar__views" aria-labelledby="events-view-label">
						<?php foreach ( $view_links as $view_key => $view_link ) : ?>
							<li>
								<a class="events-toolbar__view" href="<?php echo esc_url( add_query_arg( ltt_dive_in_get_events_page_query_args( $filters, array( 'event_view' => 'grid' === $view_key ? '' : $view_key ) ), $page_url ) . '#events-browser' ); ?>" data-events-view="<?php echo esc_attr( $view_key ); ?>"<?php echo $view === $view_key ? ' aria-current="true"' : ''; ?>>
									<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $view_link['icon'] ) ); ?>" alt="" width="<?php echo esc_attr( (string) $view_link['width'] ); ?>" height="24" />
									<?php echo esc_html( $view_link['label'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="events-toolbar__group events-toolbar__group--sort">
					<span class="events-toolbar__label" aria-hidden="true"><?php esc_html_e( 'Sort:', 'ltt-dive-in' ); ?></span>
					<?php
					get_template_part(
						'template-parts/components/select',
						null,
						array(
							'id'                => 'events-sort',
							'name'              => 'event_sort',
							'label'             => __( 'Sort events by', 'ltt-dive-in' ),
							'options'           => $sort_options,
							'selected_value'    => $filters['sort'],
							'surface'           => 'light',
							'class_name'        => 'events-toolbar__sort',
							'aria_controls'     => $results_id,
							'native_attributes' => array( 'data-events-filter' => 'sort' ),
						)
					);
					?>
				</div>

				<div class="events-toolbar__search ltt-search-bar ltt-search-bar--filter" role="search">
					<label class="screen-reader-text" for="events-search"><?php esc_html_e( 'Search events', 'ltt-dive-in' ); ?></label>
					<button class="ltt-search-bar__submit" type="submit">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) ); ?>" alt="" width="28" height="28" />
						<span class="screen-reader-text"><?php esc_html_e( 'Search events', 'ltt-dive-in' ); ?></span>
					</button>
					<input id="events-search" class="ltt-search-bar__input" type="search" name="event_search" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search...', 'ltt-dive-in' ); ?>" maxlength="80" autocomplete="off" aria-controls="<?php echo esc_attr( $results_id ); ?>" data-events-filter="search" />
				</div>
			</div>

			<p class="screen-reader-text" role="status" data-events-status></p>

			<div class="events-browser__results">
				<div id="<?php echo esc_attr( $results_id ); ?>" class="events-browser__grid events-browser__grid--<?php echo esc_attr( $view ); ?>" data-events-results>
					<?php echo ltt_dive_in_render_events_page_cards( $events, $view ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the event-card template part. ?>
				</div>

				<p class="events-browser__message" data-events-empty<?php echo $events || $is_error ? ' hidden' : ''; ?>><?php esc_html_e( 'No events match your filters. Try another date, category, or search term.', 'ltt-dive-in' ); ?></p>
				<p class="events-browser__message" data-events-error<?php echo $is_error ? '' : ' hidden'; ?>><?php esc_html_e( 'Events are temporarily unavailable. Please try again later.', 'ltt-dive-in' ); ?></p>

				<button class="ltt-load-more events-browser__load-more" type="submit" name="event_page" value="<?php echo esc_attr( (string) ( $filters['page'] + 1 ) ); ?>" data-events-load-more data-offset="<?php echo esc_attr( (string) ( $is_error ? 0 : $result['fetched'] ) ); ?>"<?php echo ! $is_error && $result['has_more'] ? '' : ' hidden'; ?>>
					<span class="ltt-load-more__label"><?php esc_html_e( 'Load More', 'ltt-dive-in' ); ?></span>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/header/scroll-arrow.svg' ) ); ?>" alt="" width="14" height="12" />
				</button>
			</div>
		</div>
	</form>
	<?php ltt_dive_in_print_event_schema( $events ); ?>
</section>
