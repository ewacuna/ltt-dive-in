<?php
/**
 * Story Module: blog post sections from the Blog Templates design.
 *
 * One ACF block with one variant per Figma section. Each variant owns:
 *
 * - its fields: an ACF group field named `ltt_dive_in_story_module_{key}`
 *   in acf-json/group_ltt_dive_in_story_module.json;
 * - its markup: template-parts/blocks/story-module/{slug}.php;
 * - its styles: assets/css/components/story-module/{slug}.css, loaded only
 *   on pages that use that variant.
 *
 * To add a variant, register it in ltt_dive_in_story_module_variants() and
 * create those three pieces.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the registered Story Module variants.
 *
 * @return array<string, array{label:string, slug:string}> Variants keyed by stored value.
 */
function ltt_dive_in_story_module_variants() {
	return array(
		'travel_tip' => array(
			'label' => __( 'Responsible Travel Tip', 'ltt-dive-in' ),
			'slug'  => 'travel-tip',
		),
	);
}

/**
 * Get the style handle for a Story Module variant.
 *
 * @param string $variant Variant key.
 * @return string
 */
function ltt_dive_in_story_module_style_handle( $variant ) {
	return 'ltt-dive-in-story-module-' . sanitize_key( str_replace( '_', '-', $variant ) );
}

/**
 * Register the block and one stylesheet per variant.
 */
function ltt_dive_in_register_story_module() {
	$handles = array();

	foreach ( ltt_dive_in_story_module_variants() as $variant => $config ) {
		$relative = '/assets/css/components/story-module/' . $config['slug'] . '.css';
		$path     = LTT_DIVE_IN_DIR . $relative;

		if ( ! file_exists( $path ) ) {
			continue;
		}

		$handle    = ltt_dive_in_story_module_style_handle( $variant );
		$handles[] = $handle;
		wp_register_style( $handle, LTT_DIVE_IN_URI . $relative, array(), (string) filemtime( $path ) );
	}

	// The editor previews every variant, so its handle pulls in all of them.
	wp_register_style( 'ltt-dive-in-story-module-editor', false, $handles, LTT_DIVE_IN_VERSION );

	if ( file_exists( LTT_DIVE_IN_DIR . '/blocks/story-module/block.json' ) ) {
		register_block_type( LTT_DIVE_IN_DIR . '/blocks/story-module' );
	}
}
add_action( 'init', 'ltt_dive_in_register_story_module' );

/**
 * Fill the variant select from the registered variants.
 *
 * @param array $field ACF field settings.
 * @return array
 */
function ltt_dive_in_load_story_module_variant_choices( $field ) {
	$field['choices'] = wp_list_pluck( ltt_dive_in_story_module_variants(), 'label' );

	return $field;
}
add_filter( 'acf/load_field/key=field_ltt_dive_in_story_module_variant', 'ltt_dive_in_load_story_module_variant_choices' );

/**
 * Collect the Story Module variants used in a block tree.
 *
 * @param array[] $blocks Parsed blocks.
 * @param array   $found  Variants found so far.
 * @return string[]
 */
function ltt_dive_in_story_module_variants_in_blocks( $blocks, $found = array() ) {
	foreach ( (array) $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( 'ltt-dive-in/story-module' === ( $block['blockName'] ?? '' ) ) {
			$variant = $block['attrs']['data']['ltt_dive_in_story_module_variant'] ?? '';

			if ( is_string( $variant ) && '' !== $variant ) {
				$found[ $variant ] = $variant;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = ltt_dive_in_story_module_variants_in_blocks( $block['innerBlocks'], $found );
		}
	}

	return $found;
}

/**
 * Enqueue the stylesheets of the variants used on the current post.
 *
 * The renderer also enqueues its variant, which covers content that cannot be
 * discovered here; this early pass keeps the styles in the head.
 */
function ltt_dive_in_enqueue_story_module_styles() {
	$post = is_singular() ? get_queried_object() : null;

	if ( ! $post instanceof WP_Post || ! has_block( 'ltt-dive-in/story-module', $post ) ) {
		return;
	}

	foreach ( ltt_dive_in_story_module_variants_in_blocks( parse_blocks( $post->post_content ) ) as $variant ) {
		wp_enqueue_style( ltt_dive_in_story_module_style_handle( $variant ) );
	}
}
add_action( 'wp_enqueue_scripts', 'ltt_dive_in_enqueue_story_module_styles', 20 );

/**
 * Offer the Story Module only when editing posts.
 *
 * @param bool|string[]           $allowed Allowed block names, or true for all.
 * @param WP_Block_Editor_Context $context Editor context.
 * @return bool|string[]
 */
function ltt_dive_in_limit_story_module_to_posts( $allowed, $context ) {
	if ( ! isset( $context->post ) || ! $context->post instanceof WP_Post || 'post' === $context->post->post_type ) {
		return $allowed;
	}

	if ( true === $allowed ) {
		$allowed = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );
	}

	return is_array( $allowed ) ? array_values( array_diff( $allowed, array( 'ltt-dive-in/story-module' ) ) ) : $allowed;
}
add_filter( 'allowed_block_types_all', 'ltt_dive_in_limit_story_module_to_posts', 10, 2 );
