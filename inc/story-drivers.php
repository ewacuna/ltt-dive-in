<?php
/**
 * Story Drivers data, queries, and REST endpoint.
 *
 * Story tiles are built from WordPress posts. The Hero + Page Grid variant
 * filters and loads more posts through ltt-dive-in/v1/story-driver-stories and
 * falls back to a GET form without JavaScript.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the approved Story Driver layouts and their story counts.
 *
 * For One Up and Hero + Page Grid the count is the number of feature slides.
 *
 * @return array[] Layout data keyed by layout value.
 */
function ltt_dive_in_get_story_driver_layouts() {
	return array(
		'one_up'    => array(
			'label'   => __( '1 Up (Feature Story)', 'ltt-dive-in' ),
			'min'     => 1,
			'max'     => 5,
			'default' => 3,
		),
		'two_up'    => array(
			'label'   => __( '2 Up', 'ltt-dive-in' ),
			'min'     => 2,
			'max'     => 2,
			'default' => 2,
		),
		'three_up'  => array(
			'label'   => __( '3 Up', 'ltt-dive-in' ),
			'min'     => 3,
			'max'     => 3,
			'default' => 3,
		),
		'four_up'   => array(
			'label'   => __( '4 Up', 'ltt-dive-in' ),
			'min'     => 4,
			'max'     => 4,
			'default' => 4,
		),
		'carousel'  => array(
			'label'   => __( '3+ Up Carousel', 'ltt-dive-in' ),
			'min'     => 3,
			'max'     => 10,
			'default' => 6,
		),
		'hero_grid' => array(
			'label'   => __( 'Hero + Page Grid', 'ltt-dive-in' ),
			'min'     => 1,
			'max'     => 5,
			'default' => 3,
		),
	);
}

/**
 * Normalize a published post into Story tile data.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return array|null Story data, or null when the post cannot be a tile.
 */
function ltt_dive_in_get_story_driver_story( $post ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type || 'publish' !== $post->post_status || ! has_post_thumbnail( $post ) ) {
		return null;
	}

	$default_category = (int) get_option( 'default_category' );
	$categories       = array_values(
		array_filter(
			get_the_category( $post->ID ),
			static function ( $term ) use ( $default_category ) {
				return $term instanceof WP_Term && $default_category !== (int) $term->term_id;
			}
		)
	);
	$tags             = get_the_tags( $post->ID );

	return array(
		'id'           => (int) $post->ID,
		'title'        => get_the_title( $post ),
		'url'          => get_permalink( $post ),
		'excerpt'      => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), 18 ),
		'image'        => (int) get_post_thumbnail_id( $post ),
		'category'     => $categories ? $categories[0]->name : '',
		'category_ids' => array_map( 'intval', wp_list_pluck( $categories, 'term_id' ) ),
		'tags'         => is_array( $tags ) ? array_slice( wp_list_pluck( $tags, 'name' ), 0, 4 ) : array(),
		'date'         => get_the_date( DATE_W3C, $post ),
		'date_label'   => get_the_date( _x( 'M Y', 'post month date format', 'ltt-dive-in' ), $post ),
		'read_time'    => ltt_dive_in_get_reading_time( $post ),
	);
}

/**
 * Convert post IDs into Story tile data, skipping posts that cannot render.
 *
 * @param int[] $post_ids Post IDs in display order.
 * @return array[]
 */
function ltt_dive_in_get_story_driver_stories( $post_ids ) {
	$stories = array();

	foreach ( (array) $post_ids as $post_id ) {
		$story = ltt_dive_in_get_story_driver_story( absint( $post_id ) );

		if ( $story ) {
			$stories[] = $story;
		}
	}

	return $stories;
}

/**
 * Query the most recent posts for a dynamic Story Driver.
 *
 * @param array $args {
 *     @type int[] $categories Category IDs; posts may match any of them.
 *     @type int[] $tags       Tag IDs; posts may match any of them.
 *     @type int   $count      Number of posts.
 *     @type int[] $exclude    Post IDs to leave out.
 * }
 * @return array[]
 */
function ltt_dive_in_query_story_driver_stories( $args ) {
	$query_args = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => max( 1, min( 10, absint( $args['count'] ?? 3 ) ) ),
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'fields'                 => 'ids',
		'update_post_term_cache' => true,
		// Tiles are full-image; posts without a featured image cannot render.
		'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		),
	);
	$categories = array_values( array_filter( array_map( 'absint', (array) ( $args['categories'] ?? array() ) ) ) );
	$tags       = array_values( array_filter( array_map( 'absint', (array) ( $args['tags'] ?? array() ) ) ) );
	$exclude    = array_values( array_filter( array_map( 'absint', (array) ( $args['exclude'] ?? array() ) ) ) );

	if ( $categories ) {
		$query_args['category__in'] = $categories;
	}

	if ( $tags ) {
		$query_args['tag__in'] = $tags;
	}

	if ( $exclude ) {
		$query_args['post__not_in'] = $exclude; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
	}

	return ltt_dive_in_get_story_driver_stories( ( new WP_Query( $query_args ) )->posts );
}

/**
 * Sanitize Hero + Page Grid filters from a request.
 *
 * @param array $raw Unslashed raw values: category, tag, search, page.
 * @return array{category:int,tag:int,search:string,page:int}
 */
function ltt_dive_in_sanitize_story_driver_grid_filters( $raw ) {
	$category = absint( $raw['category'] ?? 0 );
	$tag      = absint( $raw['tag'] ?? 0 );
	$search   = is_scalar( $raw['search'] ?? '' ) ? sanitize_text_field( (string) ( $raw['search'] ?? '' ) ) : '';

	return array(
		'category' => $category && term_exists( $category, 'category' ) ? $category : 0,
		'tag'      => $tag && term_exists( $tag, 'post_tag' ) ? $tag : 0,
		'search'   => mb_substr( $search, 0, 80 ),
		'page'     => max( 1, min( 20, absint( $raw['page'] ?? 1 ) ) ),
	);
}

/**
 * Query one batch of Hero + Page Grid stories.
 *
 * @param array $filters  Sanitized filters from ltt_dive_in_sanitize_story_driver_grid_filters().
 * @param int[] $exclude  Post IDs already shown in the hero.
 * @param int   $offset   Number of matching posts to skip.
 * @param int   $per_page Number of posts to return.
 * @return array{stories:array[],total:int,has_more:bool}
 */
function ltt_dive_in_query_story_driver_grid( $filters, $exclude, $offset, $per_page ) {
	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, min( 60, absint( $per_page ) ) ),
		'offset'              => absint( $offset ),
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'fields'              => 'ids',
		'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		),
	);
	$exclude    = array_slice( array_values( array_filter( array_map( 'absint', (array) $exclude ) ) ), 0, 10 );

	if ( ! empty( $filters['category'] ) ) {
		$query_args['cat'] = (int) $filters['category'];
	}

	if ( ! empty( $filters['tag'] ) ) {
		$query_args['tag_id'] = (int) $filters['tag'];
	}

	if ( ! empty( $filters['search'] ) ) {
		$query_args['s'] = $filters['search'];
	}

	if ( $exclude ) {
		$query_args['post__not_in'] = $exclude; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
	}

	$query   = new WP_Query( $query_args );
	$stories = ltt_dive_in_get_story_driver_stories( $query->posts );
	$total   = (int) $query->found_posts;

	return array(
		'stories'  => $stories,
		'total'    => $total,
		'has_more' => absint( $offset ) + count( $query->posts ) < $total,
	);
}

/**
 * Get term options for a Hero + Page Grid dropdown.
 *
 * @param string $taxonomy Taxonomy name: category or post_tag.
 * @return array<int,string> Term names keyed by term ID.
 */
function ltt_dive_in_get_story_driver_filter_options( $taxonomy ) {
	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
		'number'     => 50,
	);

	if ( 'category' === $taxonomy ) {
		$args['exclude'] = array( (int) get_option( 'default_category' ) );
	}

	$terms = get_terms( $args );

	return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'term_id' );
}

/**
 * Render Story tiles for the Hero + Page Grid.
 *
 * @param array[] $stories Story data.
 * @return string
 */
function ltt_dive_in_render_story_driver_grid_cards( $stories ) {
	ob_start();

	foreach ( $stories as $story ) {
		get_template_part(
			'template-parts/components/story-card',
			null,
			array(
				'story'   => $story,
				'wrapper' => 'story-drivers__item',
				'sizes'   => '(max-width: 767.98px) 100vw, (max-width: 1199.98px) 50vw, 440px',
			)
		);
	}

	return (string) ob_get_clean();
}

/**
 * Return a batch of Hero + Page Grid stories.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function ltt_dive_in_get_story_driver_rest_stories( $request ) {
	$filters  = ltt_dive_in_sanitize_story_driver_grid_filters(
		array(
			'category' => $request->get_param( 'category' ),
			'tag'      => $request->get_param( 'tag' ),
			'search'   => $request->get_param( 'search' ),
		)
	);
	$exclude  = array_map( 'absint', explode( ',', (string) $request->get_param( 'exclude' ) ) );
	$per_page = max( 3, min( 12, absint( $request->get_param( 'per_page' ) ) ) );
	$result   = ltt_dive_in_query_story_driver_grid( $filters, $exclude, absint( $request->get_param( 'offset' ) ), $per_page );

	return rest_ensure_response(
		array(
			'html'     => ltt_dive_in_render_story_driver_grid_cards( $result['stories'] ),
			'count'    => count( $result['stories'] ),
			'total'    => $result['total'],
			'has_more' => $result['has_more'],
		)
	);
}

/**
 * Register the public Story Driver REST route.
 *
 * Only published posts are returned, so the endpoint exposes nothing beyond
 * the public blog.
 *
 * @return void
 */
function ltt_dive_in_register_story_driver_routes() {
	register_rest_route(
		'ltt-dive-in/v1',
		'/story-driver-stories',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ltt_dive_in_get_story_driver_rest_stories',
			'permission_callback' => '__return_true',
			'args'                => array(
				'offset'   => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'per_page' => array( 'default' => 9, 'sanitize_callback' => 'absint' ),
				'category' => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'tag'      => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'search'   => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
				'exclude'  => array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'ltt_dive_in_register_story_driver_routes' );

/**
 * Store a post's estimated reading time when it is saved.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function ltt_dive_in_store_reading_time( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_ltt_dive_in_reading_time', ltt_dive_in_calculate_reading_time( $post ) );
}
add_action( 'save_post_post', 'ltt_dive_in_store_reading_time', 10, 2 );
