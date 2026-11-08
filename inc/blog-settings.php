<?php
/**
 * Blog Settings: the end-of-post Page Driver ("See More").
 *
 * By default every post shows three related posts under the heading set in
 * Posts → Blog Settings. A post may instead choose its posts, use custom
 * cards, or hide the section. Both groups are PHP-registered because they
 * are generated from shared field definitions.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the Page Driver header fields for a given name prefix.
 *
 * @param string $prefix      Field name and key prefix.
 * @param array  $conditional Optional ACF conditional logic for every field.
 * @return array
 */
function ltt_dive_in_blog_page_driver_header_fields( $prefix, $conditional = array() ) {
	$fields = array(
		array(
			'key'           => 'field_' . $prefix . '_tag',
			'label'         => __( 'Category tag', 'ltt-dive-in' ),
			'name'          => $prefix . '_tag',
			'type'          => 'text',
			'instructions'  => __( 'Optional short label shown above the title.', 'ltt-dive-in' ),
			'maxlength'     => 40,
			'default_value' => __( 'Experiences', 'ltt-dive-in' ),
		),
		array(
			'key'           => 'field_' . $prefix . '_title',
			'label'         => __( 'Title', 'ltt-dive-in' ),
			'name'          => $prefix . '_title',
			'type'          => 'text',
			'instructions'  => __( 'The section is hidden when the title is empty.', 'ltt-dive-in' ),
			'maxlength'     => 120,
			'default_value' => __( 'See More', 'ltt-dive-in' ),
		),
		array(
			'key'           => 'field_' . $prefix . '_body',
			'label'         => __( 'Body copy', 'ltt-dive-in' ),
			'name'          => $prefix . '_body',
			'type'          => 'textarea',
			'instructions'  => __( 'Optional.', 'ltt-dive-in' ),
			'rows'          => 3,
			'maxlength'     => 300,
			'new_lines'     => '',
			'default_value' => __( 'There’s even more to Lake Tahoe, see some of the examples below:', 'ltt-dive-in' ),
		),
		array(
			'key'           => 'field_' . $prefix . '_background',
			'label'         => __( 'Background', 'ltt-dive-in' ),
			'name'          => $prefix . '_background',
			'type'          => 'select',
			'instructions'  => __( 'White uses navy text. Navy uses light text and the dark category tag.', 'ltt-dive-in' ),
			'choices'       => array(
				'white' => __( 'White', 'ltt-dive-in' ),
				'navy'  => __( 'Navy', 'ltt-dive-in' ),
			),
			'default_value' => 'white',
			'return_format' => 'value',
		),
	);

	if ( $conditional ) {
		foreach ( $fields as $index => $field ) {
			$fields[ $index ]['conditional_logic'] = $conditional;
		}
	}

	return $fields;
}

/**
 * Register the Blog Settings page and the per-post override group.
 */
function ltt_dive_in_register_blog_settings() {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Blog Settings', 'ltt-dive-in' ),
			'menu_title'  => __( 'Blog Settings', 'ltt-dive-in' ),
			'menu_slug'   => 'ltt-dive-in-blog-settings',
			'parent_slug' => 'edit.php',
			'capability'  => 'edit_others_posts',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_blog_settings',
			'title'                 => __( 'LTT Dive In — End-of-Post Page Driver', 'ltt-dive-in' ),
			'fields'                => array_merge(
				array(
					array(
						'key'     => 'field_ltt_dive_in_blog_driver_message',
						'label'   => '',
						'name'    => '',
						'type'    => 'message',
						'message' => __( 'Heading for the section at the end of every post. Its cards show three related posts with featured images, chosen automatically from the post’s categories and tags. Each post can choose its own posts, use custom cards, or hide the section in its “End-of-Post Page Driver” box.', 'ltt-dive-in' ),
					),
				),
				ltt_dive_in_blog_page_driver_header_fields( 'ltt_dive_in_blog_driver' )
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'ltt-dive-in-blog-settings',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	$is_mode = static function ( $mode ) {
		return array(
			array(
				array(
					'field'    => 'field_ltt_dive_in_post_driver_mode',
					'operator' => '==',
					'value'    => $mode,
				),
			),
		);
	};
	$custom  = $is_mode( 'custom' );

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_post_driver',
			'title'                 => __( 'End-of-Post Page Driver', 'ltt-dive-in' ),
			'fields'                => array_merge(
				array(
					array(
						'key'           => 'field_ltt_dive_in_post_driver_mode',
						'label'         => __( 'Display', 'ltt-dive-in' ),
						'name'          => 'ltt_dive_in_post_driver_mode',
						'type'          => 'button_group',
						'instructions'  => __( 'Related posts are chosen automatically from this post’s categories and tags. The heading is managed in Posts → Blog Settings.', 'ltt-dive-in' ),
						'choices'       => array(
							'default' => __( 'Related posts', 'ltt-dive-in' ),
							'posts'   => __( 'Choose posts', 'ltt-dive-in' ),
							'custom'  => __( 'Custom cards', 'ltt-dive-in' ),
							'hide'    => __( 'Hide', 'ltt-dive-in' ),
						),
						'default_value' => 'default',
						'return_format' => 'value',
					),
					array(
						'key'               => 'field_ltt_dive_in_post_driver_posts',
						'label'             => __( 'Posts', 'ltt-dive-in' ),
						'name'              => 'ltt_dive_in_post_driver_posts',
						'type'              => 'relationship',
						'instructions'      => __( 'Choose up to three published posts with a featured image. Posts without one are skipped.', 'ltt-dive-in' ),
						'post_type'         => array( 'post' ),
						'post_status'       => array( 'publish' ),
						'filters'           => array( 'search', 'taxonomy' ),
						'elements'          => array( 'featured_image' ),
						'min'               => 0,
						'max'               => 3,
						'return_format'     => 'id',
						'conditional_logic' => $is_mode( 'posts' ),
					),
				),
				ltt_dive_in_blog_page_driver_header_fields( 'ltt_dive_in_post_driver', $custom ),
				array(
					array(
						'key'               => 'field_ltt_dive_in_post_driver_cards',
						'label'             => __( 'Cards', 'ltt-dive-in' ),
						'name'              => 'ltt_dive_in_post_driver_cards',
						'type'              => 'repeater',
						'instructions'      => __( 'Add three cards.', 'ltt-dive-in' ),
						'min'               => 0,
						'max'               => 3,
						'layout'            => 'block',
						'button_label'      => __( 'Add card', 'ltt-dive-in' ),
						'collapsed'         => 'field_ltt_dive_in_post_driver_card_title',
						'conditional_logic' => $custom,
						'sub_fields'        => array(
							array(
								'key'           => 'field_ltt_dive_in_post_driver_card_image',
								'label'         => __( 'Background image', 'ltt-dive-in' ),
								'name'          => 'image',
								'type'          => 'image',
								'instructions'  => __( 'Decorative: the card title describes the destination.', 'ltt-dive-in' ),
								'required'      => 1,
								'return_format' => 'id',
								'preview_size'  => 'medium',
								'library'       => 'all',
								'mime_types'    => 'jpg,jpeg,png,webp',
							),
							array(
								'key'       => 'field_ltt_dive_in_post_driver_card_title',
								'label'     => __( 'Title', 'ltt-dive-in' ),
								'name'      => 'title',
								'type'      => 'text',
								'required'  => 1,
								'maxlength' => 50,
							),
							array(
								'key'       => 'field_ltt_dive_in_post_driver_card_copy',
								'label'     => __( 'Short copy', 'ltt-dive-in' ),
								'name'      => 'copy',
								'type'      => 'textarea',
								'rows'      => 3,
								'maxlength' => 150,
								'new_lines' => '',
							),
							array(
								'key'           => 'field_ltt_dive_in_post_driver_card_cta',
								'label'         => __( 'Button', 'ltt-dive-in' ),
								'name'          => 'cta',
								'type'          => 'link',
								'instructions'  => __( 'Optional. Choose a descriptive label and destination.', 'ltt-dive-in' ),
								'return_format' => 'array',
							),
						),
					),
				)
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
			),
			'menu_order'            => 20,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
			'description'           => __( 'The “See More” section at the end of the post.', 'ltt-dive-in' ),
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_blog_settings' );

/**
 * Keep the current post out of its own “Choose posts” picker.
 *
 * @param array $args    WP_Query arguments.
 * @param array $field   Field settings.
 * @param int   $post_id Post being edited.
 * @return array
 */
function ltt_dive_in_exclude_current_post_from_driver_picker( $args, $field, $post_id ) {
	if ( is_numeric( $post_id ) && absint( $post_id ) ) {
		$args['post__not_in'] = array( absint( $post_id ) );
	}

	return $args;
}
add_filter( 'acf/fields/relationship/query/key=field_ltt_dive_in_post_driver_posts', 'ltt_dive_in_exclude_current_post_from_driver_picker', 10, 3 );

/**
 * Find up to three related published posts with featured images.
 *
 * Posts sharing a category come first, then posts sharing a tag, then the
 * most recent posts, so the section stays full on a sparsely tagged blog.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function ltt_dive_in_get_related_post_ids( $post_id ) {
	$post_id          = absint( $post_id );
	$default_category = (int) get_option( 'default_category' );
	$categories       = array_diff( wp_get_post_categories( $post_id ), array( $default_category ) );
	$tags             = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );
	$related          = array();
	$queries          = array();

	if ( $categories ) {
		$queries[] = array( 'category__in' => array_values( $categories ) );
	}
	if ( is_array( $tags ) && $tags ) {
		$queries[] = array( 'tag__in' => $tags );
	}
	$queries[] = array();

	foreach ( $queries as $query ) {
		$related = array_merge(
			$related,
			get_posts(
				array_merge(
					array(
						'post_type'           => 'post',
						'post_status'         => 'publish',
						'posts_per_page'      => 3 - count( $related ),
						'post__not_in'        => array_merge( array( $post_id ), $related ),
						'ignore_sticky_posts' => true,
						'no_found_rows'       => true,
						'fields'              => 'ids',
						'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Small, bounded query; cards need a featured image.
							array(
								'key'     => '_thumbnail_id',
								'compare' => 'EXISTS',
							),
						),
					),
					$query
				)
			)
		);

		if ( count( $related ) >= 3 ) {
			break;
		}
	}

	return array_map( 'intval', $related );
}

/**
 * Convert posts into Page Driver cards.
 *
 * @param int[] $post_ids Post IDs, in display order.
 * @return array
 */
function ltt_dive_in_post_driver_cards_from_posts( $post_ids ) {
	$cards = array();

	foreach ( array_slice( (array) $post_ids, 0, 3 ) as $related_id ) {
		$related = get_post( absint( $related_id ) );

		if ( ! $related instanceof WP_Post || 'post' !== $related->post_type || 'publish' !== $related->post_status || ! has_post_thumbnail( $related ) ) {
			continue;
		}

		$title   = get_the_title( $related );
		$cards[] = array(
			'image' => (int) get_post_thumbnail_id( $related ),
			'title' => $title,
			'copy'  => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $related ) ), 18 ),
			'cta'   => array(
				'title'   => __( 'Read More', 'ltt-dive-in' ),
				'url'     => get_permalink( $related ),
				'target'  => '',
				'context' => $title,
			),
		);
	}

	return $cards;
}

/**
 * Get the end-of-post Page Driver for a post.
 *
 * @param int $post_id Post ID.
 * @return array|null Template-part arguments, or null when nothing renders.
 */
function ltt_dive_in_get_post_page_driver( $post_id ) {
	static $cache = array();

	$post_id = absint( $post_id );
	if ( array_key_exists( $post_id, $cache ) ) {
		return $cache[ $post_id ];
	}

	$cache[ $post_id ] = null;
	if ( ! $post_id || ! function_exists( 'get_field' ) || 'post' !== get_post_type( $post_id ) ) {
		return null;
	}

	$mode = (string) get_field( 'ltt_dive_in_post_driver_mode', $post_id );
	if ( 'hide' === $mode ) {
		return null;
	}

	if ( 'custom' === $mode ) {
		$prefix = 'ltt_dive_in_post_driver';
		$source = $post_id;
		$cards  = get_field( 'ltt_dive_in_post_driver_cards', $post_id );
	} else {
		$prefix = 'ltt_dive_in_blog_driver';
		$source = 'option';
		$chosen = 'posts' === $mode ? get_field( 'ltt_dive_in_post_driver_posts', $post_id ) : null;
		$cards  = ltt_dive_in_post_driver_cards_from_posts( 'posts' === $mode ? (array) $chosen : ltt_dive_in_get_related_post_ids( $post_id ) );
	}

	$title = trim( (string) get_field( $prefix . '_title', $source ) );
	$cards = is_array( $cards ) ? array_slice( array_values( array_filter( $cards, static function ( $card ) {
		return is_array( $card ) && '' !== trim( (string) ( $card['title'] ?? '' ) );
	} ) ), 0, 3 ) : array();

	if ( '' === $title || ! $cards ) {
		return null;
	}

	$cache[ $post_id ] = array(
		'id'    => 'post-page-driver',
		'tag'   => (string) get_field( $prefix . '_tag', $source ),
		'title' => $title,
		'body'  => (string) get_field( $prefix . '_body', $source ),
		'cards' => $cards,
		'navy'  => 'navy' === get_field( $prefix . '_background', $source ),
		'full'  => true,
	);

	return $cache[ $post_id ];
}
