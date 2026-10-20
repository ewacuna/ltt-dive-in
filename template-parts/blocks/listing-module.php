<?php
/**
 * Listing Module: editorial header and FacetWP-filterable cards.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$is_preview = ! empty( $is_preview );
$eyebrow    = trim( (string) get_field( 'ltt_dive_in_listing_module_eyebrow' ) );
$heading    = trim( (string) get_field( 'ltt_dive_in_listing_module_heading' ) );
$intro      = trim( (string) get_field( 'ltt_dive_in_listing_module_intro' ) );
$source     = get_field( 'ltt_dive_in_listing_module_source' );
$source     = is_string( $source ) ? sanitize_key( $source ) : '';
$count      = (int) get_field( 'ltt_dive_in_listing_module_initial_count' );
$count      = min( 12, max( 1, $count ) );
$block_data = isset( $block['data'] ) && is_array( $block['data'] ) ? $block['data'] : array();
$step_saved = array_key_exists( 'field_ltt_dive_in_listing_module_load_more_step', $block_data ) || array_key_exists( 'ltt_dive_in_listing_module_load_more_step', $block_data );
$raw_step   = get_field( 'ltt_dive_in_listing_module_load_more_step' );
$step       = ( ! $step_saved && ! $is_preview ) || false === $raw_step || null === $raw_step || '' === $raw_step ? $count : (int) $raw_step;
$step       = min( 12, max( 1, $step ) );
$post_type  = $source ? get_post_type_object( $source ) : null;

if ( ! $heading || ! $post_type || ! $post_type->public || in_array( $source, array( 'attachment', 'page' ), true ) ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add a heading and choose a public content type to preview Listing Module.', 'ltt-dive-in' ) . '</p>';
	}
	return;
}

$facet_one   = get_field( 'ltt_dive_in_listing_module_facet_one' );
$facet_two   = get_field( 'ltt_dive_in_listing_module_facet_two' );
$facet_names = array(
	is_string( $facet_one ) ? sanitize_key( $facet_one ) : '',
	is_string( $facet_two ) ? sanitize_key( $facet_two ) : '',
);
$facets      = array();
$facet_sources = array();
$duplicate_filter = false;

foreach ( $facet_names as $facet_name ) {
	$facet = ltt_dive_in_listing_module_get_dropdown_facet( $facet_name, $source );
	if ( ! $facet ) {
		continue;
	}

	if ( isset( $facets[ $facet['name'] ] ) || in_array( $facet['source'], $facet_sources, true ) ) {
		$duplicate_filter = true;
		continue;
	}

	$facets[ $facet['name'] ] = $facet;
	$facet_sources[]         = $facet['source'];
}

$search_value = get_field( 'ltt_dive_in_listing_module_search_facet' );
$search_name  = is_string( $search_value ) ? sanitize_key( $search_value ) : '';
$search_facet = ltt_dive_in_listing_module_get_search_facet( $search_name );
$pager_value  = get_field( 'ltt_dive_in_listing_module_pager_facet' );
$pager_name   = is_string( $pager_value ) ? sanitize_key( $pager_value ) : '';
$pager_facet  = ltt_dive_in_listing_module_get_load_more_facet( $pager_name );
$id           = ! empty( $block['anchor'] ) ? sanitize_title( $block['anchor'] ) : wp_unique_id( 'listing-module-' );
$alignment    = isset( $block['align'] ) && 'full' === $block['align'] ? ' alignfull' : '';
$use_facets   = ! $is_preview && function_exists( 'facetwp_display' ) && ( ! empty( $facets ) || $search_facet || $pager_facet );

$query_args = array(
	'post_type'           => $source,
	'post_status'         => 'publish',
	'posts_per_page'      => $count,
	'orderby'             => array(
		'date' => 'DESC',
		'ID'   => 'DESC',
	),
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
	'has_password'        => false,
	'no_found_rows'       => ! $use_facets,
	'facetwp'             => $use_facets,
);

if ( $use_facets ) {
	$query_args['ltt_dive_in_listing_module_query']               = 1;
	$query_args['ltt_dive_in_listing_module_initial_count']       = $count;
	$query_args['ltt_dive_in_listing_module_step']                = $step;
	$query_args['ltt_dive_in_listing_module_post_type']           = $source;
	$query_args['ltt_dive_in_listing_module_search_facet']        = $search_facet ? $search_name : '';
	$query_args['ltt_dive_in_listing_module_pager_facet']         = $pager_facet ? $pager_name : '';
	$query_args['ltt_dive_in_listing_module_id']                  = $id;
}

$query = new WP_Query( $query_args );

$reset_facets = array_keys( $facets );
if ( $search_facet ) {
	$reset_facets[] = $search_name;
}
$reset_facets = array_unique( array_filter( $reset_facets ) );
?>
<section id="<?php echo esc_attr( $id ); ?>" class="ltt-listing-module<?php echo esc_attr( $alignment ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" data-listing-results-updated="<?php esc_attr_e( 'Listing results updated.', 'ltt-dive-in' ); ?>" data-listing-loading="<?php esc_attr_e( 'Updating listings…', 'ltt-dive-in' ); ?>" data-listing-loading-more="<?php esc_attr_e( 'Loading more listings…', 'ltt-dive-in' ); ?>" data-listing-search-name="<?php echo esc_attr( $search_name ); ?>" data-listing-search-icon="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) ); ?>" data-listing-search-label="<?php esc_attr_e( 'Search listings', 'ltt-dive-in' ); ?>" data-listing-result-singular="<?php esc_attr_e( 'listing shown.', 'ltt-dive-in' ); ?>" data-listing-result-plural="<?php esc_attr_e( 'listings shown.', 'ltt-dive-in' ); ?>" data-listing-no-results="<?php esc_attr_e( 'No listings match your search. Try changing your filters or search term.', 'ltt-dive-in' ); ?>" data-listing-load-more-error="<?php esc_attr_e( 'We could not load more listings. Use Load More to try again.', 'ltt-dive-in' ); ?>" data-listing-update-error="<?php esc_attr_e( 'We could not update the listings. Please try again.', 'ltt-dive-in' ); ?>" data-listing-retry="<?php esc_attr_e( 'Try again', 'ltt-dive-in' ); ?>">
	<div class="ltt-listing-module__container">
		<header class="ltt-listing-module__header">
			<?php if ( $eyebrow ) : ?><p class="ltt-tag ltt-listing-module__eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="ltt-listing-module__title"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( $intro ) : ?><p class="ltt-listing-module__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		</header>

		<?php if ( $is_preview ) : ?>
			<div class="ltt-listing-module__filter-preview" aria-hidden="true">
				<span class="ltt-select ltt-select--light ltt-listing-module__select-preview"><span class="ltt-select__native"><?php echo esc_html( isset( $facet_names[0], $facets[ $facet_names[0] ] ) ? $facets[ $facet_names[0] ]['label'] : __( 'Filter one', 'ltt-dive-in' ) ); ?></span><span class="ltt-select__native-icon"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/select-toggle.svg' ) ); ?>" alt="" /></span></span>
				<span class="ltt-select ltt-select--light ltt-listing-module__select-preview"><span class="ltt-select__native"><?php echo esc_html( isset( $facet_names[1], $facets[ $facet_names[1] ] ) ? $facets[ $facet_names[1] ]['label'] : __( 'Filter two', 'ltt-dive-in' ) ); ?></span><span class="ltt-select__native-icon"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/select-toggle.svg' ) ); ?>" alt="" /></span></span>
				<span class="ltt-search-bar ltt-search-bar--filter ltt-listing-module__search-preview"><span class="ltt-search-bar__submit"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) ); ?>" alt="" /></span><span class="ltt-search-bar__input"><?php esc_html_e( 'Search...', 'ltt-dive-in' ); ?></span></span>
			</div>
		<?php elseif ( $use_facets && ( ! empty( $facets ) || $search_facet ) ) : ?>
			<div class="ltt-listing-module__filters">
				<?php foreach ( $facets as $facet_index => $facet ) : ?>
					<?php $select_id = $id . '-facet-' . sanitize_html_class( $facet_index ); ?>
					<div class="ltt-listing-module__filter">
						<label id="<?php echo esc_attr( $select_id ); ?>-label" class="screen-reader-text ltt-listing-module__filter-label" for="<?php echo esc_attr( $select_id ); ?>"><?php echo esc_html( $facet['label'] ); ?></label>
						<div class="ltt-select ltt-select--light ltt-listing-module__filter-control" data-listing-select-id="<?php echo esc_attr( $select_id ); ?>" data-listing-results-id="<?php echo esc_attr( $id ); ?>-cards">
							<?php echo facetwp_display( 'facet', $facet['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- FacetWP renders its own facet container. ?>
							<span class="ltt-select__native-icon" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/select-toggle.svg' ) ); ?>" alt="" /></span>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if ( $search_facet ) : ?>
					<div class="ltt-listing-module__search-wrap">
						<label class="screen-reader-text ltt-listing-module__search-label" for="<?php echo esc_attr( $id ); ?>-search-input"><?php esc_html_e( 'Search listings', 'ltt-dive-in' ); ?></label>
						<div class="ltt-search-bar ltt-search-bar--filter ltt-listing-module__search">
							<?php echo facetwp_display( 'facet', $search_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- FacetWP renders its own facet container. ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="ltt-listing-module__carousel">
			<div id="<?php echo esc_attr( $id ); ?>-cards" class="ltt-listing-module__grid<?php echo $use_facets ? ' facetwp-template' : ''; ?>"<?php if ( ! $is_preview ) : ?> tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Listings', 'ltt-dive-in' ); ?>"<?php endif; ?>>
				<?php if ( ! $query->have_posts() ) : ?>
					<div class="ltt-listing-module__empty">
						<p><?php esc_html_e( 'No listings match your search. Try changing your filters or search term.', 'ltt-dive-in' ); ?></p>
						<?php if ( $use_facets && $reset_facets ) : ?>
							<button class="facetwp-reset ltt-listing-module__reset" type="button" data-values="<?php echo esc_attr( implode( ',', $reset_facets ) ); ?>"><?php esc_html_e( 'Clear filters and search', 'ltt-dive-in' ); ?></button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php
					$listing_post = get_post();
					get_template_part(
						'template-parts/components/listing-card',
						null,
						array(
							'image_id'   => get_post_thumbnail_id( $listing_post ),
							'title'      => get_the_title( $listing_post ),
							'copy'       => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $listing_post ) ), 24, '…' ),
							'buttons'    => ltt_dive_in_listing_card_buttons( $listing_post ),
							'is_preview' => $is_preview,
						)
					);
					?>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</div>
			<?php if ( $use_facets ) : ?>
				<div class="screen-reader-text" role="status" aria-live="polite" aria-atomic="true" tabindex="-1" data-listing-status></div>
			<?php endif; ?>
			<?php if ( ! $is_preview ) : ?>
				<div class="ltt-listing-module__arrows" hidden>
					<button class="ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--previous ltt-listing-module__arrow" type="button" data-listing-previous aria-controls="<?php echo esc_attr( $id ); ?>-cards" aria-label="<?php esc_attr_e( 'Previous listing', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
					<button class="ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--next ltt-listing-module__arrow" type="button" data-listing-next aria-controls="<?php echo esc_attr( $id ); ?>-cards" aria-label="<?php esc_attr_e( 'Next listing', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $is_preview && $query->post_count > 0 ) : ?>
			<span class="ltt-listing-module__load-preview" aria-hidden="true"><span class="ltt-listing-module__load-preview-label"><?php esc_html_e( 'Load More', 'ltt-dive-in' ); ?></span></span>
		<?php elseif ( $use_facets && $pager_facet ) : ?>
			<div class="ltt-listing-module__load-more-wrap" data-listing-load-more>
				<?php echo facetwp_display( 'facet', $pager_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- FacetWP renders its own pager facet. ?>
			</div>
		<?php endif; ?>

		<?php if ( $use_facets ) : ?>
			<noscript><p class="ltt-listing-module__no-script"><?php esc_html_e( 'The listings above are available without JavaScript. Filtering, search, and Load More need JavaScript to work.', 'ltt-dive-in' ); ?></p></noscript>
		<?php endif; ?>

		<?php if ( $is_preview ) : ?>
			<?php
			$editor_warnings = array();
			if ( ! function_exists( 'FWP' ) ) {
				$editor_warnings[] = __( 'FacetWP is not active, so front-end filters, search, and Load More will not be available.', 'ltt-dive-in' );
			} else {
				if ( 2 > count( $facets ) ) {
					$editor_warnings[] = __( 'Choose two valid Dropdown facets whose taxonomies are attached to the selected content type.', 'ltt-dive-in' );
				}
				if ( ! $search_facet ) {
					$editor_warnings[] = __( 'Choose a Search facet that uses WP Default with Auto refresh and Order by relevance enabled.', 'ltt-dive-in' );
				}
				if ( ! $pager_facet ) {
					$editor_warnings[] = __( 'Choose a Pager facet set to Load More to enable pagination.', 'ltt-dive-in' );
				}
				if ( $duplicate_filter ) {
					$editor_warnings[] = __( 'The same taxonomy filter is selected more than once. Choose two different filters.', 'ltt-dive-in' );
				}
			}
			?>
			<p class="ltt-listing-module__editor-note"><?php esc_html_e( 'This is an editor preview. Interactive filters, keyword search, and Load More run on the front end.', 'ltt-dive-in' ); ?></p>
			<?php if ( $editor_warnings ) : ?>
				<ul class="ltt-listing-module__editor-warnings">
					<?php foreach ( $editor_warnings as $warning ) : ?><li><?php echo esc_html( $warning ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
