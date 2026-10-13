<?php
/**
 * Story Drivers block fields and validation.
 *
 * The group is code-driven because its layout choices and story-count limits
 * come from ltt_dive_in_get_story_driver_layouts().
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build ACF conditional logic that shows a field for the given layouts.
 *
 * @param string[] $layouts    Layout values.
 * @param array[]  $extra_rule Optional rules every group must also satisfy.
 * @return array[]
 */
function ltt_dive_in_story_driver_layout_logic( $layouts, $extra_rule = array() ) {
	$groups = array();

	foreach ( $layouts as $layout ) {
		$groups[] = array_merge(
			array(
				array(
					'field'    => 'field_ltt_dive_in_story_driver_layout',
					'operator' => '==',
					'value'    => $layout,
				),
			),
			$extra_rule
		);
	}

	return $groups;
}

/**
 * Register the code-driven Story Drivers group.
 *
 * @return void
 */
function ltt_dive_in_register_story_driver_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$layout_choices = wp_list_pluck( ltt_dive_in_get_story_driver_layouts(), 'label' );
	$with_header    = array( 'two_up', 'three_up', 'four_up', 'carousel', 'hero_grid' );
	$manual_rule    = array(
		array(
			'field'    => 'field_ltt_dive_in_story_driver_mode',
			'operator' => '==',
			'value'    => 'manual',
		),
	);
	$dynamic_rule   = array(
		array(
			'field'    => 'field_ltt_dive_in_story_driver_mode',
			'operator' => '==',
			'value'    => 'dynamic',
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_ltt_dive_in_story_drivers',
			'title'                 => __( 'LTT Dive In — Story Drivers Block', 'ltt-dive-in' ),
			'fields'                => array(
				array(
					'key'           => 'field_ltt_dive_in_story_driver_layout',
					'label'         => __( 'Layout', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_story_driver_layout',
					'type'          => 'select',
					'instructions'  => __( '1 Up and Hero + Page Grid open with a feature-story slideshow. 2, 3, and 4 Up show that exact number of stories. The carousel shows 3 to 10.', 'ltt-dive-in' ),
					'required'      => 1,
					'choices'       => $layout_choices,
					'allow_null'    => 1,
					'ui'            => 1,
					'return_format' => 'value',
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_tag',
					'label'             => __( 'Category tag', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_tag',
					'type'              => 'text',
					'instructions'      => __( 'Optional short label shown above the heading.', 'ltt-dive-in' ),
					'maxlength'         => 40,
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( $with_header ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_heading',
					'label'             => __( 'Heading', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_heading',
					'type'              => 'text',
					'instructions'      => __( 'Required section heading.', 'ltt-dive-in' ),
					'required'          => 1,
					'maxlength'         => 90,
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( $with_header ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_intro',
					'label'             => __( 'Introduction', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_intro',
					'type'              => 'textarea',
					'instructions'      => __( 'Optional one- or two-sentence introduction.', 'ltt-dive-in' ),
					'rows'              => 3,
					'new_lines'         => '',
					'maxlength'         => 240,
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( $with_header ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_primary_link',
					'label'             => __( 'Primary button', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_primary_link',
					'type'              => 'link',
					'instructions'      => __( 'Optional. Use a label that describes the destination, such as “All stories”.', 'ltt-dive-in' ),
					'return_format'     => 'array',
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( array( 'two_up', 'three_up', 'four_up' ) ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_secondary_link',
					'label'             => __( 'Secondary button', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_secondary_link',
					'type'              => 'link',
					'instructions'      => __( 'Optional second button.', 'ltt-dive-in' ),
					'return_format'     => 'array',
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( array( 'two_up', 'three_up', 'four_up' ) ),
				),
				array(
					'key'           => 'field_ltt_dive_in_story_driver_mode',
					'label'         => __( 'Stories', 'ltt-dive-in' ),
					'name'          => 'ltt_dive_in_story_driver_mode',
					'type'          => 'button_group',
					'instructions'  => __( 'Dynamic shows the most recent posts. Manual shows the posts you choose, in your order. For Hero + Page Grid this choice applies to the feature slideshow; the grid always lists every post.', 'ltt-dive-in' ),
					'choices'       => array(
						'dynamic' => __( 'Dynamic', 'ltt-dive-in' ),
						'manual'  => __( 'Manual', 'ltt-dive-in' ),
					),
					'default_value' => 'dynamic',
					'return_format' => 'value',
					'layout'        => 'horizontal',
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_posts',
					'label'             => __( 'Posts', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_posts',
					'type'              => 'relationship',
					'instructions'      => __( 'Choose and order published posts. Every post needs a featured image. The number required depends on the layout.', 'ltt-dive-in' ),
					'required'          => 1,
					'post_type'         => array( 'post' ),
					'post_status'       => array( 'publish' ),
					'filters'           => array( 'search', 'taxonomy' ),
					'return_format'     => 'id',
					'min'               => 1,
					'max'               => 10,
					'conditional_logic' => array( $manual_rule ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_categories',
					'label'             => __( 'Categories', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_categories',
					'type'              => 'taxonomy',
					'instructions'      => __( 'Optional. Show only posts in any of these categories.', 'ltt-dive-in' ),
					'taxonomy'          => 'category',
					'field_type'        => 'checkbox',
					'add_term'          => 0,
					'save_terms'        => 0,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'conditional_logic' => array( $dynamic_rule ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_tags',
					'label'             => __( 'Tags', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_tags',
					'type'              => 'taxonomy',
					'instructions'      => __( 'Optional. Show only posts with any of these tags.', 'ltt-dive-in' ),
					'taxonomy'          => 'post_tag',
					'field_type'        => 'multi_select',
					'allow_null'        => 1,
					'add_term'          => 0,
					'save_terms'        => 0,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'conditional_logic' => array( $dynamic_rule ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_count',
					'label'             => __( 'Number of stories', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_count',
					'type'              => 'number',
					'instructions'      => __( '1 Up and Hero + Page Grid: 1 to 5 feature slides. Carousel: 3 to 10 stories. 2, 3, and 4 Up always show that many.', 'ltt-dive-in' ),
					'default_value'     => 3,
					'min'               => 1,
					'max'               => 10,
					'step'              => 1,
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( array( 'one_up', 'carousel', 'hero_grid' ), $dynamic_rule ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_exclude_current',
					'label'             => __( 'Leave out the current post', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_exclude_current',
					'type'              => 'true_false',
					'instructions'      => __( 'When this block appears on a post, do not show that same post.', 'ltt-dive-in' ),
					'default_value'     => 1,
					'ui'                => 1,
					'conditional_logic' => array( $dynamic_rule ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_enable_filters',
					'label'             => __( 'Enable category filters', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_enable_filters',
					'type'              => 'true_false',
					'instructions'      => __( 'Let visitors narrow the carousel by category without reloading the page.', 'ltt-dive-in' ),
					'default_value'     => 0,
					'ui'                => 1,
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( array( 'carousel' ) ),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_filter_terms',
					'label'             => __( 'Filter categories', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_filter_terms',
					'type'              => 'taxonomy',
					'instructions'      => __( 'Choose two to five categories. Categories without a story in this carousel are not shown.', 'ltt-dive-in' ),
					'taxonomy'          => 'category',
					'field_type'        => 'checkbox',
					'add_term'          => 0,
					'save_terms'        => 0,
					'load_terms'        => 0,
					'return_format'     => 'id',
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic(
						array( 'carousel' ),
						array(
							array(
								'field'    => 'field_ltt_dive_in_story_driver_enable_filters',
								'operator' => '==',
								'value'    => '1',
							),
						)
					),
				),
				array(
					'key'               => 'field_ltt_dive_in_story_driver_grid_count',
					'label'             => __( 'Grid stories per load', 'ltt-dive-in' ),
					'name'              => 'ltt_dive_in_story_driver_grid_count',
					'type'              => 'number',
					'instructions'      => __( 'Stories shown first and added by each Load More: 3, 6, 9, or 12.', 'ltt-dive-in' ),
					'default_value'     => 9,
					'min'               => 3,
					'max'               => 12,
					'step'              => 3,
					'conditional_logic' => ltt_dive_in_story_driver_layout_logic( array( 'hero_grid' ) ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'ltt-dive-in/story-drivers',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
			'description'           => __( 'Blog post tiles in approved layouts, chosen manually or by recency.', 'ltt-dive-in' ),
		)
	);
}
add_action( 'acf/init', 'ltt_dive_in_register_story_driver_settings' );

/**
 * Validate the manual post count and featured images for the chosen layout.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_story_driver_posts( $valid, $value, $field, $input ) {
	if ( true !== $valid ) {
		return $valid;
	}

	$layout  = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_story_driver_layout' );
	$layouts = ltt_dive_in_get_story_driver_layouts();

	if ( ! is_string( $layout ) || ! isset( $layouts[ $layout ] ) ) {
		/* ACF may validate this field before the sibling Layout value is sent. */
		return $valid;
	}

	$posts   = is_array( $value ) ? array_values( array_filter( array_map( 'absint', $value ) ) ) : array();
	$minimum = $layouts[ $layout ]['min'];
	$maximum = $layouts[ $layout ]['max'];

	if ( count( $posts ) < $minimum || count( $posts ) > $maximum ) {
		return $minimum === $maximum
			/* translators: %d: required number of posts. */
			? sprintf( __( 'This layout requires exactly %d posts.', 'ltt-dive-in' ), $minimum )
			/* translators: 1: minimum number of posts, 2: maximum number of posts. */
			: sprintf( __( 'This layout requires between %1$d and %2$d posts.', 'ltt-dive-in' ), $minimum, $maximum );
	}

	foreach ( $posts as $post_id ) {
		if ( ! ltt_dive_in_get_story_driver_story( $post_id ) ) {
			return __( 'Every post must be published and have a featured image.', 'ltt-dive-in' );
		}
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_story_driver_posts', 'ltt_dive_in_validate_story_driver_posts', 10, 4 );

/**
 * Validate the dynamic story count for the chosen layout.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_story_driver_count( $valid, $value, $field, $input ) {
	$layout  = ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_story_driver_layout' );
	$layouts = ltt_dive_in_get_story_driver_layouts();

	if ( true !== $valid || '' === $value || null === $value || ! is_string( $layout ) || ! isset( $layouts[ $layout ] ) ) {
		return $valid;
	}

	$count = absint( $value );

	if ( $count < $layouts[ $layout ]['min'] || $count > $layouts[ $layout ]['max'] ) {
		/* translators: 1: minimum number of stories, 2: maximum number of stories. */
		return sprintf( __( 'Choose between %1$d and %2$d for this layout.', 'ltt-dive-in' ), $layouts[ $layout ]['min'], $layouts[ $layout ]['max'] );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_story_driver_count', 'ltt_dive_in_validate_story_driver_count', 10, 4 );

/**
 * Validate the carousel filter count.
 *
 * @param bool|string $valid Validation result.
 * @param mixed       $value Submitted value.
 * @param array       $field Field configuration.
 * @param string      $input Field input name.
 * @return bool|string
 */
function ltt_dive_in_validate_story_driver_filter_terms( $valid, $value, $field, $input ) {
	if ( true !== $valid || ! ltt_dive_in_get_page_driver_submitted_value( 'field_ltt_dive_in_story_driver_enable_filters' ) ) {
		return $valid;
	}

	$terms = is_array( $value ) ? array_filter( array_map( 'absint', $value ) ) : array();

	if ( count( $terms ) < 2 || count( $terms ) > 5 ) {
		return __( 'Choose between two and five filter categories.', 'ltt-dive-in' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/key=field_ltt_dive_in_story_driver_filter_terms', 'ltt_dive_in_validate_story_driver_filter_terms', 10, 4 );
