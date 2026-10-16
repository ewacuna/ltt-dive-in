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

foreach ( $facet_names as $facet_name ) {
	$facet = ltt_dive_in_listing_module_get_dropdown_facet( $facet_name, $source );
	if ( $facet && ! isset( $facets[ $facet['name'] ] ) ) {
		$facets[ $facet['name'] ] = $facet;
	}
}

$use_facets = ! $is_preview && ! empty( $facets ) && function_exists( 'facetwp_display' );

$query = new WP_Query(
	array(
		'post_type'           => $source,
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'has_password'        => false,
		'no_found_rows'       => ! $use_facets,
		'facetwp'             => $use_facets,
	)
);

$id        = ! empty( $block['anchor'] ) ? sanitize_title( $block['anchor'] ) : wp_unique_id( 'listing-module-' );
$alignment = isset( $block['align'] ) && 'full' === $block['align'] ? ' alignfull' : '';
?>
<section id="<?php echo esc_attr( $id ); ?>" class="ltt-listing-module<?php echo esc_attr( $alignment ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title" data-listing-results-updated="<?php esc_attr_e( 'Listing results updated.', 'ltt-dive-in' ); ?>">
	<div class="ltt-listing-module__container">
		<header class="ltt-listing-module__header">
			<?php if ( $eyebrow ) : ?><p class="ltt-tag ltt-listing-module__eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<h2 id="<?php echo esc_attr( $id ); ?>-title" class="ltt-listing-module__title"><?php echo esc_html( $heading ); ?></h2>
			<?php if ( $intro ) : ?><p class="ltt-listing-module__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		</header>
		<?php if ( $is_preview ) : ?>
			<div class="ltt-listing-module__filter-preview" aria-hidden="true">
				<span class="ltt-search-bar ltt-search-bar--filter ltt-listing-module__search-preview"><span class="ltt-search-bar__submit"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-search.svg' ) ); ?>" alt="" /></span><span class="ltt-search-bar__input"><?php esc_html_e( 'Search...', 'ltt-dive-in' ); ?></span></span>
				<span class="ltt-select ltt-select--light ltt-listing-module__select-preview"><span class="ltt-select__native"><?php echo esc_html( isset( $facet_names[0], $facets[ $facet_names[0] ] ) ? $facets[ $facet_names[0] ]['label'] : __( 'Filter one', 'ltt-dive-in' ) ); ?></span><span class="ltt-select__native-icon"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/select-toggle.svg' ) ); ?>" alt="" /></span></span>
				<span class="ltt-select ltt-select--light ltt-listing-module__select-preview"><span class="ltt-select__native"><?php echo esc_html( isset( $facet_names[1], $facets[ $facet_names[1] ] ) ? $facets[ $facet_names[1] ]['label'] : __( 'Filter two', 'ltt-dive-in' ) ); ?></span><span class="ltt-select__native-icon"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/select-toggle.svg' ) ); ?>" alt="" /></span></span>
			</div>
		<?php elseif ( $use_facets ) : ?>
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
			</div>
		<?php endif; ?>
		<div class="ltt-listing-module__carousel">
			<div id="<?php echo esc_attr( $id ); ?>-cards" class="ltt-listing-module__grid<?php echo $use_facets ? ' facetwp-template' : ''; ?>"<?php if ( $query->post_count > 1 && ! $is_preview ) : ?> tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Listings', 'ltt-dive-in' ); ?>"<?php endif; ?>>
				<?php if ( ! $query->have_posts() ) : ?>
					<p class="ltt-listing-module__empty"><?php esc_html_e( 'No listings found.', 'ltt-dive-in' ); ?></p>
				<?php endif; ?>
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php
					$listing_post = get_post();
					$listing_url = get_permalink( $listing_post );
					get_template_part(
						'template-parts/components/listing-card',
						null,
						array(
							'image_id'   => get_post_thumbnail_id( $listing_post ),
							'title'      => get_the_title( $listing_post ),
							'copy'       => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $listing_post ) ), 24, '…' ),
							'buttons'    => $listing_url ? array( array( 'title' => __( 'See More', 'ltt-dive-in' ), 'url' => $listing_url ) ) : array(),
							'is_preview' => $is_preview,
						)
					);
					?>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</div>
			<?php if ( $use_facets ) : ?><p class="screen-reader-text" role="status" data-listing-status></p><?php endif; ?>
			<?php if ( $query->post_count > 1 && ! $is_preview ) : ?>
				<div class="ltt-listing-module__arrows" hidden>
					<button class="ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--previous ltt-listing-module__arrow" type="button" data-listing-previous aria-controls="<?php echo esc_attr( $id ); ?>-cards" aria-label="<?php esc_attr_e( 'Previous listing', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--previous" aria-hidden="true"></span></button>
					<button class="ltt-slider-navigation ltt-slider-navigation--image ltt-slider-navigation--next ltt-listing-module__arrow" type="button" data-listing-next aria-controls="<?php echo esc_attr( $id ); ?>-cards" aria-label="<?php esc_attr_e( 'Next listing', 'ltt-dive-in' ); ?>"><span class="ltt-slider-navigation__icon ltt-slider-navigation__icon--next" aria-hidden="true"></span></button>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( $is_preview ) : ?>
			<span class="ltt-listing-module__load-preview" aria-hidden="true"><?php esc_html_e( 'Load More', 'ltt-dive-in' ); ?></span>
			<p class="ltt-listing-module__editor-note"><?php esc_html_e( 'Configured FacetWP dropdowns are interactive on the front end. Search and Load More remain previews.', 'ltt-dive-in' ); ?></p>
		<?php endif; ?>
	</div>
</section>
