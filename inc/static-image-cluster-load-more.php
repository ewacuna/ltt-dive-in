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
 * Build the public Inspired Gallery image data from a saved block.
 *
 * @param array $block Parsed Static Image Cluster block.
 * @return array[]
 */
function ltt_dive_in_get_saved_inspired_gallery_images( $block ) {
	$data       = is_array( $block['attrs']['data'] ?? null ) ? $block['attrs']['data'] : array();
	$variant    = $data['ltt_dive_in_static_image_cluster_variant'] ?? '';
	$image_ids  = ltt_dive_in_get_static_image_cluster_attachment_ids( $data, 'ltt_dive_in_static_image_cluster_images' );
	$details    = ltt_dive_in_get_static_image_cluster_image_details( $data, 'ltt_dive_in_static_image_cluster_image_details' );
	$images     = array();

	if ( 'inspired' !== $variant ) {
		return $images;
	}

	foreach ( $image_ids as $index => $image_id ) {
		if ( ! wp_attachment_is_image( $image_id ) ) {
			continue;
		}

		$detail = isset( $details[ $index ] ) && is_array( $details[ $index ] ) ? $details[ $index ] : array();
		$alt    = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
		$full   = wp_get_attachment_image_src( $image_id, 'full' );

		if ( '' === $alt || ! $full ) {
			continue;
		}

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
 * Render an Inspired Gallery item returned by the progressive-load endpoint.
 *
 * @param array  $image     Image data.
 * @param int    $index     Zero-based image position.
 * @param int    $total     Total gallery image count.
 * @return string
 */
function ltt_dive_in_render_inspired_gallery_item( $image, $index, $total ) {
	$desktop_width = in_array( $index % 6, array( 2, 3 ), true ) ? '28vw' : '36vw';
	$image_html = wp_get_attachment_image(
		$image['id'],
		'large',
		false,
		array(
			'alt'      => $image['alt'],
			'sizes'    => '(max-width: 767.98px) calc((100vw - 22px) / 3), ' . $desktop_width,
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	if ( ! $image_html ) {
		return '';
	}

	return sprintf(
		'<figure class="static-image-cluster__item"><a class="static-image-cluster__trigger" href="%1$s" data-ltt-photoswipe-trigger data-image-index="%2$d" data-pswp-width="%3$d" data-pswp-height="%4$d" aria-haspopup="dialog" aria-label="%5$s">%6$s<span class="static-image-cluster__inspired-info" aria-hidden="true"><img src="%7$s" alt="" /></span><span class="static-image-cluster__inspired-title"><img src="%8$s" alt="" />%9$s</span></a></figure>',
		esc_url( $image['full'] ),
		(int) $index,
		(int) $image['width'],
		(int) $image['height'],
		esc_attr( sprintf( __( 'View image %1$d of %2$d: %3$s', 'ltt-dive-in' ), $index + 1, $total, $image['alt'] ) ),
		$image_html,
		esc_url( get_theme_file_uri( 'assets/images/icons/info.svg' ) ),
		esc_url( get_theme_file_uri( 'assets/images/icons/location.svg' ) ),
		esc_html( $image['title'] )
	);
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
	$offset = min( absint( $request->get_param( 'offset' ) ), count( $images ) );
	$batch  = array_slice( $images, $offset, 6 );
	$html   = '';

	foreach ( $batch as $batch_index => $image ) {
		$html .= ltt_dive_in_render_inspired_gallery_item( $image, $offset + $batch_index, count( $images ) );
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
				'post'  => array( 'required' => true, 'sanitize_callback' => 'absint' ),
				'block' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
				'offset' => array( 'required' => true, 'sanitize_callback' => 'absint' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'ltt_dive_in_register_static_image_cluster_routes' );
