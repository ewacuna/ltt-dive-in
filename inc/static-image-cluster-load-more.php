<?php
/**
 * Progressive loading for Inspired Gallery images.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find one Static Image Cluster block in a parsed block tree.
 *
 * @param array[] $blocks   Parsed blocks.
 * @param string  $block_id Hash of the saved ACF block data.
 * @return array|null
 */
function ltt_dive_in_find_static_image_cluster_block( $blocks, $block_id ) {
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		$data = is_array( $block['attrs']['data'] ?? null ) ? $block['attrs']['data'] : array();

		if ( 'ltt-dive-in/static-image-cluster' === ( $block['blockName'] ?? '' ) && hash_equals( $block_id, md5( wp_json_encode( $data ) ) ) ) {
			return $block;
		}

		$found = ltt_dive_in_find_static_image_cluster_block( $block['innerBlocks'] ?? array(), $block_id );

		if ( $found ) {
			return $found;
		}
	}

	return null;
}

/**
 * Return attachment IDs from an ACF Gallery value stored in block attributes.
 *
 * @param array  $data  ACF block data.
 * @param string $field Field name.
 * @return int[]
 */
function ltt_dive_in_get_static_image_cluster_attachment_ids( $data, $field ) {
	$value = $data[ $field ] ?? array();

	if ( is_string( $value ) && '' !== $value ) {
		$decoded = json_decode( $value, true );
		$value   = is_array( $decoded ) ? $decoded : explode( ',', $value );
	}

	if ( ! is_array( $value ) && is_numeric( $value ) ) {
		$rows = array();

		for ( $index = 0; $index < (int) $value; ++$index ) {
			if ( isset( $data[ $field . '_' . $index ] ) ) {
				$rows[] = $data[ $field . '_' . $index ];
			}
		}

		$value = $rows ? $rows : array( $value );
	}

	return array_values( array_filter( array_map( 'absint', (array) $value ) ) );
}

/**
 * Return position-aligned image detail rows from ACF block attributes.
 *
 * @param array  $data  ACF block data.
 * @param string $field Field name.
 * @return array[]
 */
function ltt_dive_in_get_static_image_cluster_image_details( $data, $field ) {
	$value = $data[ $field ] ?? array();

	if ( is_array( $value ) ) {
		return $value;
	}

	$details = array();

	for ( $index = 0; $index < absint( $value ); ++$index ) {
		$prefix = $field . '_' . $index . '_';
		$details[] = array(
			'title'       => $data[ $prefix . 'title' ] ?? '',
			'description' => $data[ $prefix . 'description' ] ?? '',
		);
	}

	return $details;
}

/**
 * Build the ordered Inspired Gallery image records used by the block and REST endpoint.
 *
 * @param int[] $image_ids Attachment IDs in editor order.
 * @param array $details Position-aligned rows containing optional `title` and `description` strings.
 * @param bool  $require_all Whether an invalid attachment invalidates the complete gallery.
 * @return array<int, array{id:int, alt:string, title:string, description:string, full:string, width:int, height:int, srcset:string}>|false Gallery records, or false when a required image is invalid.
 */
function ltt_dive_in_build_inspired_gallery_images( $image_ids, $details, $require_all = false ) {
	$images = array();
	$details = is_array( $details ) ? $details : array();

	foreach ( $image_ids as $index => $image_id ) {
		if ( ! wp_attachment_is_image( $image_id ) ) {
			if ( $require_all ) {
				return false;
			}

			continue;
		}

		$alt  = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
		$full = wp_get_attachment_image_src( $image_id, 'full' );

		if ( '' === $alt || ! $full ) {
			if ( $require_all ) {
				return false;
			}

			continue;
		}

		$detail = isset( $details[ $index ] ) && is_array( $details[ $index ] ) ? $details[ $index ] : array();

		$images[] = array(
			'id'          => $image_id,
			'alt'         => $alt,
			'title'       => trim( (string) ( $detail['title'] ?? '' ) ),
			'description' => trim( (string) ( $detail['description'] ?? '' ) ),
			'full'        => (string) $full[0],
			'width'       => (int) $full[1],
			'height'      => (int) $full[2],
			'srcset'      => (string) wp_get_attachment_image_srcset( $image_id, 'full' ),
		);
	}

	return $images;
}

/**
 * Build the public Inspired Gallery image data from a saved block.
 *
 * @param array $block Parsed Static Image Cluster block.
 * @return array<int, array{id:int, alt:string, title:string, description:string, full:string, width:int, height:int, srcset:string}>
 */
function ltt_dive_in_get_saved_inspired_gallery_images( $block ) {
	$data      = is_array( $block['attrs']['data'] ?? null ) ? $block['attrs']['data'] : array();
	$variant   = $data['ltt_dive_in_static_image_cluster_variant'] ?? '';
	$image_ids = ltt_dive_in_get_static_image_cluster_attachment_ids( $data, 'ltt_dive_in_static_image_cluster_images' );
	$details   = ltt_dive_in_get_static_image_cluster_image_details( $data, 'ltt_dive_in_static_image_cluster_image_details' );

	if ( 'inspired' !== $variant ) {
		return array();
	}

	return ltt_dive_in_build_inspired_gallery_images( $image_ids, $details );
}

/**
 * Render an Inspired Gallery item through the shared block template.
 *
 * @param array $image Image record created by ltt_dive_in_build_inspired_gallery_images().
 * @param int   $index Zero-based image position.
 * @param int   $total Total gallery image count.
 * @param bool  $inline_detail Whether the cards open inline details.
 * @return string
 */
function ltt_dive_in_render_inspired_gallery_item( $image, $index, $total, $inline_detail = false ) {
	$desktop_width = in_array( $index % 6, array( 2, 3 ), true ) ? '28vw' : '36vw';
	$image_html = wp_get_attachment_image(
		$image['id'],
		'large',
		false,
		array(
			'alt'      => $image['alt'],
			'sizes'    => '(max-width: 767.98px) calc((100vw - 22px) / 3), ' . $desktop_width,
			// The user-requested batch is inserted into the visible grid.
			'loading'  => 'eager',
			'decoding' => 'async',
		)
	);

	if ( ! $image_html ) {
		return '';
	}

	ob_start();
	get_template_part(
		'template-parts/blocks/static-image-cluster-item',
		null,
		array(
			'image'             => $image,
			'image_html'        => $image_html,
			'index'             => $index,
			'total'             => $total,
			'inline_detail'     => $inline_detail,
			'uses_photoswipe'   => ! $inline_detail,
			'section_id'        => 'static-image-cluster-load-more',
		)
	);

	return ob_get_clean();
}

/**
 * Return the next six Inspired Gallery cards for a public post.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function ltt_dive_in_get_static_image_cluster_load_more( WP_REST_Request $request ) {
	$post = get_post( $request->get_param( 'post' ) );

	if ( ! $post instanceof WP_Post || ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post->ID ) ) ) {
		return new WP_Error( 'ltt_dive_in_static_image_cluster_not_found', __( 'Gallery not found.', 'ltt-dive-in' ), array( 'status' => 404 ) );
	}

	$block = ltt_dive_in_find_static_image_cluster_block( parse_blocks( $post->post_content ), (string) $request->get_param( 'block' ) );

	if ( ! $block ) {
		return new WP_Error( 'ltt_dive_in_static_image_cluster_not_found', __( 'Gallery not found.', 'ltt-dive-in' ), array( 'status' => 404 ) );
	}

	$images = ltt_dive_in_get_saved_inspired_gallery_images( $block );
	$block_data    = is_array( $block['attrs']['data'] ?? null ) ? $block['attrs']['data'] : array();
	$inline_detail = 'inline_detail' === ( $block_data['ltt_dive_in_static_image_cluster_mobile_display'] ?? '' );
	$offset        = min( absint( $request->get_param( 'offset' ) ), count( $images ) );
	$batch         = array_slice( $images, $offset, 6 );
	$html          = '';

	foreach ( $batch as $batch_index => $image ) {
		$html .= ltt_dive_in_render_inspired_gallery_item( $image, $offset + $batch_index, count( $images ), $inline_detail );
	}

	if ( $html ) {
		$html = '<div class="static-image-cluster__inspired-batch">' . $html . '</div>';
	}

	$response = rest_ensure_response(
		array(
			'html'       => $html,
			'images'     => $batch,
			'nextOffset' => $offset + count( $batch ),
			'hasMore'    => $offset + count( $batch ) < count( $images ),
		)
	);
	$response->header( 'Cache-Control', 'publish' === $post->post_status ? 'public, max-age=300' : 'private, no-store' );

	return $response;
}

/**
 * Register the cacheable endpoint used by the Inspired Gallery Load More button.
 */
function ltt_dive_in_register_static_image_cluster_routes() {
	register_rest_route(
		'ltt-dive-in/v1',
		'/static-image-cluster-images',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ltt_dive_in_get_static_image_cluster_load_more',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post'   => array( 'required' => true, 'sanitize_callback' => 'absint' ),
				'block'  => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
				'offset' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'ltt_dive_in_register_static_image_cluster_routes' );
