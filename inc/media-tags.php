<?php
/**
 * Media Library tags for organizing attachments.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Media Tag taxonomy for attachments.
 *
 * The taxonomy is `public` only because WordPress requires it to show the
 * tag field in the media modal's attachment details. It is not publicly
 * queryable, so it has no front-end archives, rewrite rules, or sitemap.
 */
function ltt_dive_in_register_media_tags() {
	register_taxonomy(
		'ltt_media_tag',
		'attachment',
		array(
			'labels'                => array(
				'name'                       => __( 'Media Tags', 'ltt-dive-in' ),
				'singular_name'              => __( 'Media Tag', 'ltt-dive-in' ),
				'menu_name'                  => __( 'Media Tags', 'ltt-dive-in' ),
				'all_items'                  => __( 'All Media Tags', 'ltt-dive-in' ),
				'edit_item'                  => __( 'Edit Media Tag', 'ltt-dive-in' ),
				'view_item'                  => __( 'View Media Tag', 'ltt-dive-in' ),
				'update_item'                => __( 'Update Media Tag', 'ltt-dive-in' ),
				'add_new_item'               => __( 'Add New Media Tag', 'ltt-dive-in' ),
				'new_item_name'              => __( 'New Media Tag Name', 'ltt-dive-in' ),
				'search_items'               => __( 'Search Media Tags', 'ltt-dive-in' ),
				'popular_items'              => __( 'Popular Media Tags', 'ltt-dive-in' ),
				'separate_items_with_commas' => __( 'Separate media tags with commas', 'ltt-dive-in' ),
				'add_or_remove_items'        => __( 'Add or remove media tags', 'ltt-dive-in' ),
				'choose_from_most_used'      => __( 'Choose from the most used media tags', 'ltt-dive-in' ),
				'not_found'                  => __( 'No media tags found.', 'ltt-dive-in' ),
				'no_terms'                   => __( 'No media tags', 'ltt-dive-in' ),
				'back_to_items'              => __( '&larr; Go to Media Tags', 'ltt-dive-in' ),
			),
			'hierarchical'          => false,
			'public'                => true,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => false,
			'show_tagcloud'         => false,
			'show_in_quick_edit'    => true,
			'show_admin_column'     => true,
			'show_in_rest'          => true,
			'rewrite'               => false,
			'query_var'             => 'ltt_media_tag',
			'update_count_callback' => '_update_generic_term_count',
		)
	);
}
add_action( 'init', 'ltt_dive_in_register_media_tags' );

/**
 * Add a Media Tag filter to the Media Library list view.
 *
 * @param string $post_type Current list-table post type.
 */
function ltt_dive_in_media_tag_filter( $post_type ) {
	if ( 'attachment' !== $post_type || ! taxonomy_exists( 'ltt_media_tag' ) ) {
		return;
	}

	$selected = isset( $_GET['ltt_media_tag'] ) ? sanitize_title( wp_unslash( $_GET['ltt_media_tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filter.

	printf(
		'<label class="screen-reader-text" for="ltt-media-tag-filter">%s</label>',
		esc_html__( 'Filter by media tag', 'ltt-dive-in' )
	);

	wp_dropdown_categories(
		array(
			'taxonomy'        => 'ltt_media_tag',
			'name'            => 'ltt_media_tag',
			'id'              => 'ltt-media-tag-filter',
			'value_field'     => 'slug',
			'selected'        => $selected,
			'show_option_all' => __( 'All media tags', 'ltt-dive-in' ),
			'hide_empty'      => false,
			'hide_if_empty'   => true,
			'orderby'         => 'name',
		)
	);
}
add_action( 'restrict_manage_posts', 'ltt_dive_in_media_tag_filter' );

/**
 * Load the Media Tag filter and field behavior for the media grid and modals.
 */
function ltt_dive_in_enqueue_media_tag_assets() {
	if ( ! is_admin() || ! taxonomy_exists( 'ltt_media_tag' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'ltt_media_tag',
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$style_path  = LTT_DIVE_IN_DIR . '/assets/css/admin/media-tags.css';
	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/media-tags.js';

	wp_enqueue_style(
		'ltt-dive-in-media-tags',
		LTT_DIVE_IN_URI . '/assets/css/admin/media-tags.css',
		array( 'media-views' ),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	wp_enqueue_script(
		'ltt-dive-in-media-tags',
		LTT_DIVE_IN_URI . '/assets/js/admin/media-tags.js',
		array( 'media-views' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		true
	);

	wp_localize_script(
		'ltt-dive-in-media-tags',
		'ltt_dive_in_media_tags',
		array(
			'filterLabel' => __( 'Filter by media tag', 'ltt-dive-in' ),
			'allLabel'    => __( 'All media tags', 'ltt-dive-in' ),
			'noMatches'   => __( 'No matching tags.', 'ltt-dive-in' ),
			'match'       => __( '1 tag shown.', 'ltt-dive-in' ),
			/* translators: %d: number of media tags shown. */
			'matches'     => __( '%d tags shown.', 'ltt-dive-in' ),
			'terms'       => array_map(
				static function ( $term ) {
					return array(
						'slug' => $term->slug,
						'name' => $term->name,
					);
				},
				$terms
			),
		)
	);
}
add_action( 'wp_enqueue_media', 'ltt_dive_in_enqueue_media_tag_assets' );

/**
 * Apply the Media Tag filter to media grid and modal queries.
 *
 * Core drops unknown query keys before this filter runs, so the tag is read
 * from the original request.
 *
 * @param array $query Attachment query arguments.
 * @return array
 */
function ltt_dive_in_filter_media_query_by_tag( $query ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Core checks the upload_files capability for this read-only request.
	$request = isset( $_REQUEST['query'] ) && is_array( $_REQUEST['query'] ) ? wp_unslash( $_REQUEST['query'] ) : array();
	$slug    = isset( $request['ltt_media_tag'] ) && is_string( $request['ltt_media_tag'] ) ? sanitize_title( $request['ltt_media_tag'] ) : '';

	if ( '' === $slug ) {
		return $query;
	}

	$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Admin-only media filter.
		array(
			'taxonomy' => 'ltt_media_tag',
			'field'    => 'slug',
			'terms'    => $slug,
		),
	);

	return $query;
}
add_filter( 'ajax_query_attachments_args', 'ltt_dive_in_filter_media_query_by_tag' );

/**
 * Replace the free-text Media Tags field with checkboxes for existing tags.
 *
 * Core renders the attachment taxonomy as a comma-separated text input in the
 * media modal, which makes typos create duplicate tags. Checkboxes limit the
 * choice to managed terms. The field is only present in the media modal.
 *
 * @param array   $form_fields Attachment form fields.
 * @param WP_Post $post        Attachment post.
 * @return array
 */
function ltt_dive_in_media_tag_checkbox_field( $form_fields, $post ) {
	if ( ! isset( $form_fields['ltt_media_tag'] ) || ! $post instanceof WP_Post ) {
		return $form_fields;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'ltt_media_tag',
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return $form_fields;
	}

	$assigned  = wp_get_object_terms( $post->ID, 'ltt_media_tag', array( 'fields' => 'ids' ) );
	$assigned  = is_wp_error( $assigned ) ? array() : array_map( 'intval', $assigned );
	$can_edit  = current_user_can( 'edit_post', $post->ID );
	$name_base = 'attachments[' . $post->ID . ']';
	$id_base   = 'attachments-' . $post->ID . '-ltt-media-tag';
	$manage    = current_user_can( get_taxonomy( 'ltt_media_tag' )->cap->manage_terms )
		? admin_url( 'edit-tags.php?taxonomy=ltt_media_tag&post_type=attachment' )
		: '';

	// Show the image's current tags first so they stay visible in a long list.
	usort(
		$terms,
		static function ( $a, $b ) use ( $assigned ) {
			$a_assigned = in_array( (int) $a->term_id, $assigned, true );
			$b_assigned = in_array( (int) $b->term_id, $assigned, true );

			if ( $a_assigned !== $b_assigned ) {
				return $a_assigned ? -1 : 1;
			}

			return strnatcasecmp( $a->name, $b->name );
		}
	);

	$html = '<fieldset class="ltt-media-tags" aria-labelledby="' . esc_attr( $id_base . '-label' ) . '">';

	if ( $can_edit ) {
		// Lets the save handler tell "all tags unchecked" apart from "field not submitted".
		$html .= '<input type="hidden" name="' . esc_attr( $name_base . '[ltt_media_tag_submitted]' ) . '" value="1" />';
	}

	if ( count( $terms ) > 5 ) {
		$html .= sprintf(
			'<label class="ltt-media-tags__search-label" for="%1$s">%2$s</label><input type="search" class="ltt-media-tags__search" id="%1$s" aria-controls="%3$s" autocomplete="off" />',
			esc_attr( $id_base . '-search' ),
			esc_html__( 'Search tags', 'ltt-dive-in' ),
			esc_attr( $id_base . '-list' )
		);
	}

	if ( $terms ) {
		$html .= '<div class="ltt-media-tags__list" id="' . esc_attr( $id_base . '-list' ) . '">';

		foreach ( $terms as $term ) {
			$html .= sprintf(
				'<label class="ltt-media-tags__option"><input type="checkbox" name="%1$s" value="%2$d"%3$s%4$s /> %5$s</label>',
				esc_attr( $name_base . '[ltt_media_tag_terms][' . $term->term_id . ']' ),
				(int) $term->term_id,
				checked( in_array( (int) $term->term_id, $assigned, true ), true, false ),
				disabled( $can_edit, false, false ),
				esc_html( $term->name )
			);
		}

		$html .= '</div>';
		$html .= '<p class="ltt-media-tags__status description" role="status" aria-live="polite"></p>';
	} else {
		$html .= '<p class="description">' . esc_html__( 'No media tags have been created yet.', 'ltt-dive-in' ) . '</p>';
	}

	if ( $manage ) {
		$html .= sprintf(
			'<p class="description"><a href="%1$s" target="_blank">%2$s<span class="screen-reader-text"> %3$s</span></a></p>',
			esc_url( $manage ),
			esc_html__( 'Manage media tags', 'ltt-dive-in' ),
			esc_html__( '(opens in a new tab)', 'ltt-dive-in' )
		);
	}

	$html .= '</fieldset>';

	$form_fields['ltt_media_tag'] = array(
		'tr' => sprintf(
			"\t\t<tr class='compat-field-ltt_media_tag'><th scope='row' class='label'><span class='alignleft' id='%1\$s'>%2\$s</span><br class='clear' /></th><td class='field'>%3\$s</td></tr>\n",
			esc_attr( $id_base . '-label' ),
			esc_html( get_taxonomy( 'ltt_media_tag' )->labels->name ),
			$html
		),
	);

	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'ltt_dive_in_media_tag_checkbox_field', 10, 2 );

/**
 * Save Media Tag checkboxes from the media modal.
 *
 * Core has already verified the nonce and the edit_post capability before
 * this filter runs.
 *
 * @param array $post       Attachment post data.
 * @param array $attachment Submitted attachment fields.
 * @return array
 */
function ltt_dive_in_save_media_tag_checkboxes( $post, $attachment ) {
	if ( empty( $attachment['ltt_media_tag_submitted'] ) || empty( $post['ID'] ) ) {
		return $post;
	}

	$taxonomy = get_taxonomy( 'ltt_media_tag' );

	if ( ! $taxonomy || ! current_user_can( 'edit_post', $post['ID'] ) || ! current_user_can( $taxonomy->cap->assign_terms ) ) {
		return $post;
	}

	$term_ids = isset( $attachment['ltt_media_tag_terms'] ) && is_array( $attachment['ltt_media_tag_terms'] )
		? array_values( array_filter( array_map( 'absint', $attachment['ltt_media_tag_terms'] ) ) )
		: array();

	// Integer IDs never create new terms; unknown IDs are skipped by core.
	wp_set_object_terms( (int) $post['ID'], $term_ids, 'ltt_media_tag', false );

	return $post;
}
add_filter( 'attachment_fields_to_save', 'ltt_dive_in_save_media_tag_checkboxes', 10, 2 );
