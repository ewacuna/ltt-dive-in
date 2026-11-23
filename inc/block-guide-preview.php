<?php
/**
 * Block Guide live previews and practice drafts.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta that marks a practice draft created from the Block Guide.
 */
const LTT_DIVE_IN_BLOCK_GUIDE_PRACTICE_META = '_ltt_dive_in_block_guide_practice';

/**
 * Register the preview query variable.
 *
 * @param string[] $vars Public query variables.
 * @return string[]
 */
function ltt_dive_in_block_guide_query_vars( $vars ) {
	$vars[] = 'ltt_block_preview';

	return $vars;
}
add_filter( 'query_vars', 'ltt_dive_in_block_guide_query_vars' );

/**
 * Get the front-end preview URL for a block sample.
 *
 * @param string $slug    Block folder slug.
 * @param string $variant Variant key, or "default".
 * @return string
 */
function ltt_dive_in_block_guide_preview_url( $slug, $variant = 'default' ) {
	return add_query_arg(
		array(
			'ltt_block_preview' => $slug,
			'variant'           => $variant,
		),
		home_url( '/' )
	);
}

/**
 * Get the post type a practice draft uses for a block.
 *
 * @param string $slug Block folder slug.
 * @return string
 */
function ltt_dive_in_block_guide_practice_post_type( $slug ) {
	$post_type = 'story-module' === $slug ? 'post' : 'page';

	return (string) apply_filters( 'ltt_dive_in_block_guide_practice_post_type', $post_type, $slug );
}

/**
 * Render a block sample as a standalone front-end document.
 */
function ltt_dive_in_block_guide_render_preview() {
	$slug = sanitize_key( (string) get_query_var( 'ltt_block_preview' ) );

	if ( ! $slug ) {
		return;
	}

	if ( ! is_user_logged_in() || ! current_user_can( ltt_dive_in_block_guide_capability() ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to view this preview.', 'ltt-dive-in' ), '', array( 'response' => 403 ) );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview selection.
	$variant = isset( $_GET['variant'] ) ? sanitize_key( wp_unslash( $_GET['variant'] ) ) : 'default';
	$block   = ltt_dive_in_block_guide_sample_block( $slug, $variant );

	if ( ! $block ) {
		wp_die( esc_html__( 'There is no preview for this block yet.', 'ltt-dive-in' ), '', array( 'response' => 404 ) );
	}

	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	add_filter( 'show_admin_bar', '__return_false' );

	// The request reaches the home URL; reset it so front-page and page
	// conditionals do not load their assets or heroes.
	$GLOBALS['wp_query']->init();
	$GLOBALS['post'] = null;

	add_action(
		'wp_enqueue_scripts',
		static function () {
			$page_style_path = LTT_DIVE_IN_DIR . '/assets/css/templates/page.css';

			wp_enqueue_style(
				'ltt-dive-in-page',
				LTT_DIVE_IN_URI . '/assets/css/templates/page.css',
				array( 'ltt-dive-in-style' ),
				file_exists( $page_style_path ) ? (string) filemtime( $page_style_path ) : LTT_DIVE_IN_VERSION
			);

			$guard_script_path = LTT_DIVE_IN_DIR . '/assets/js/block-guide-preview.js';

			// Loaded in the head, not deferred, so it is listening before any
			// component script attaches its own submit handlers.
			wp_enqueue_script(
				'ltt-dive-in-block-guide-preview',
				LTT_DIVE_IN_URI . '/assets/js/block-guide-preview.js',
				array(),
				file_exists( $guard_script_path ) ? (string) filemtime( $guard_script_path ) : LTT_DIVE_IN_VERSION,
				false
			);
			wp_localize_script(
				'ltt-dive-in-block-guide-preview',
				'ltt_dive_in_block_guide_preview',
				array(
					'linkMessage' => __( 'Links are turned off in this preview.', 'ltt-dive-in' ),
					'formMessage' => __( 'Forms are turned off in this preview. Nothing was sent.', 'ltt-dive-in' ),
				)
			);
		},
		20
	);

	// Render first so the block's own assets are enqueued before wp_head().
	$content = render_block( $block );
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body class="ltt-block-guide-preview">
	<main id="primary" class="site-main site-main--has-hero page-main">
		<div class="page-main__content">
			<article class="page-entry">
				<div class="entry-content">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by the block's own escaped template. ?>
				</div>
			</article>
		</div>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'ltt_dive_in_block_guide_render_preview', 0 );

/**
 * Hide the admin bar in previews before WordPress sets it up.
 */
function ltt_dive_in_block_guide_preview_admin_bar() {
	if ( get_query_var( 'ltt_block_preview' ) ) {
		show_admin_bar( false );
	}
}
add_action( 'wp', 'ltt_dive_in_block_guide_preview_admin_bar' );

/**
 * Create a practice draft with a block sample and open it in the editor.
 */
function ltt_dive_in_block_guide_create_practice_draft() {
	check_admin_referer( 'ltt_dive_in_block_guide_practice' );

	$slug      = isset( $_POST['block'] ) ? sanitize_key( wp_unslash( $_POST['block'] ) ) : '';
	$variant   = isset( $_POST['variant'] ) ? sanitize_key( wp_unslash( $_POST['variant'] ) ) : 'default';
	$post_type = ltt_dive_in_block_guide_practice_post_type( $slug );
	$type      = get_post_type_object( $post_type );

	if ( ! $type || ! current_user_can( ltt_dive_in_block_guide_capability() ) || ! current_user_can( $type->cap->create_posts ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to create practice drafts.', 'ltt-dive-in' ), '', array( 'response' => 403 ) );
	}

	$block      = ltt_dive_in_block_guide_sample_block( $slug, $variant );
	$block_type = ltt_dive_in_block_guide_block_type( $slug );

	if ( ! $block || ! $block_type ) {
		wp_die( esc_html__( 'There is no sample for this block yet.', 'ltt-dive-in' ), '', array( 'response' => 404 ) );
	}

	$guide        = ltt_dive_in_get_block_guide_entries();
	$variant_name = isset( $guide[ $slug ]['guide']['variants'][ $variant ]['name'] ) ? $guide[ $slug ]['guide']['variants'][ $variant ]['name'] : '';
	$title        = $variant_name
		/* translators: 1: block name, 2: layout name. */
		? sprintf( __( 'Practice: %1$s (%2$s)', 'ltt-dive-in' ), $block_type->title, $variant_name )
		/* translators: %s: block name. */
		: sprintf( __( 'Practice: %s', 'ltt-dive-in' ), $block_type->title );

	$post_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => $post_type,
				'post_status'  => 'draft',
				'post_title'   => $title,
				'post_content' => serialize_block( $block ),
				'meta_input'   => array( LTT_DIVE_IN_BLOCK_GUIDE_PRACTICE_META => 1 ),
			)
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_die( esc_html( $post_id->get_error_message() ) );
	}

	wp_safe_redirect( get_edit_post_link( $post_id, 'url' ) );
	exit;
}
add_action( 'admin_post_ltt_dive_in_block_guide_practice', 'ltt_dive_in_block_guide_create_practice_draft' );

/**
 * Get the current user's practice drafts.
 *
 * @return int[]
 */
function ltt_dive_in_block_guide_practice_drafts() {
	return array_map(
		'intval',
		get_posts(
			array(
				'post_type'      => array( 'page', 'post' ),
				'post_status'    => array( 'draft', 'auto-draft', 'pending', 'private' ),
				'author'         => get_current_user_id(),
				'posts_per_page' => 100,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => LTT_DIVE_IN_BLOCK_GUIDE_PRACTICE_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			)
		)
	);
}

/**
 * Move the current user's practice drafts to the trash.
 */
function ltt_dive_in_block_guide_trash_practice_drafts() {
	check_admin_referer( 'ltt_dive_in_block_guide_trash_practice' );

	if ( ! current_user_can( ltt_dive_in_block_guide_capability() ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'ltt-dive-in' ), '', array( 'response' => 403 ) );
	}

	$trashed = 0;

	foreach ( ltt_dive_in_block_guide_practice_drafts() as $post_id ) {
		if ( current_user_can( 'delete_post', $post_id ) && wp_trash_post( $post_id ) ) {
			++$trashed;
		}
	}

	wp_safe_redirect( add_query_arg( 'practice_trashed', $trashed, ltt_dive_in_block_guide_url() ) );
	exit;
}
add_action( 'admin_post_ltt_dive_in_block_guide_trash_practice', 'ltt_dive_in_block_guide_trash_practice_drafts' );
