<?php
/**
 * Site Guide: page, post, template, and media options.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Page options submenu.
 */
function ltt_dive_in_register_page_options_guide() {
	ltt_dive_in_site_guide_hooks(
		add_submenu_page(
			'ltt-dive-in-block-guide',
			__( 'Page options', 'ltt-dive-in' ),
			__( 'Page options', 'ltt-dive-in' ),
			ltt_dive_in_block_guide_capability(),
			'ltt-dive-in-page-options-guide',
			'ltt_dive_in_render_page_options_guide_page'
		)
	);
}
add_action( 'admin_menu', 'ltt_dive_in_register_page_options_guide', 11 );

/**
 * Groups that page option guides are listed under, in display order.
 *
 * @return string[]
 */
function ltt_dive_in_page_options_guide_groups() {
	return array(
		'pages'     => __( 'Every page', 'ltt-dive-in' ),
		'templates' => __( 'Special page templates', 'ltt-dive-in' ),
		'posts'     => __( 'Blog posts', 'ltt-dive-in' ),
		'media'     => __( 'Media Library', 'ltt-dive-in' ),
	);
}

/**
 * Load every page option guide from guides/page-options/*.php.
 *
 * @return array[]
 */
function ltt_dive_in_get_page_options_guides() {
	static $guides = null;

	if ( null !== $guides ) {
		return $guides;
	}

	$guides = array();

	foreach ( (array) glob( LTT_DIVE_IN_DIR . '/guides/page-options/*.php' ) as $file ) {
		$guide = include $file;

		if ( ! is_array( $guide ) || empty( $guide['title'] ) ) {
			continue;
		}

		$guide['slug']            = sanitize_key( basename( $file, '.php' ) );
		$guides[ $guide['slug'] ] = $guide;
	}

	uasort(
		$guides,
		static function ( $a, $b ) {
			$order = array_keys( ltt_dive_in_page_options_guide_groups() );
			$group = array_search( $a['group'] ?? '', $order, true ) <=> array_search( $b['group'] ?? '', $order, true );

			return 0 !== $group ? $group : ( (int) ( $a['order'] ?? 50 ) <=> (int) ( $b['order'] ?? 50 ) ?: strnatcasecmp( $a['title'], $b['title'] ) );
		}
	);

	return $guides;
}

/**
 * Get the URL of a page option guide.
 *
 * @param string $slug Guide slug, or empty for the list.
 * @return string
 */
function ltt_dive_in_page_options_guide_url( $slug = '' ) {
	$args = array( 'page' => 'ltt-dive-in-page-options-guide' );

	if ( $slug ) {
		$args['option'] = $slug;
	}

	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

/**
 * Render the sections every Site Guide entry shares.
 *
 * @param array  $guide  Guide content.
 * @param string $prefix Unique ID prefix for section headings.
 * @param array  $labels Optional heading overrides keyed by section.
 */
function ltt_dive_in_render_site_guide_sections( $guide, $prefix, $labels = array() ) {
	$labels = array_merge(
		array(
			'shows'  => __( 'Where it shows on the site', 'ltt-dive-in' ),
			'fields' => __( 'Fields', 'ltt-dive-in' ),
		),
		$labels
	);
	?>
	<?php if ( ! empty( $guide['shows'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="<?php echo esc_attr( $prefix ); ?>-shows">
			<h2 id="<?php echo esc_attr( $prefix ); ?>-shows"><?php echo esc_html( $labels['shows'] ); ?></h2>
			<?php ltt_dive_in_render_block_guide_list( (array) $guide['shows'] ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['steps'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="<?php echo esc_attr( $prefix ); ?>-steps">
			<h2 id="<?php echo esc_attr( $prefix ); ?>-steps"><?php esc_html_e( 'How to use it', 'ltt-dive-in' ); ?></h2>
			<ol class="ltt-block-guide__list ltt-block-guide__steps">
				<?php foreach ( (array) $guide['steps'] as $step ) : ?>
					<li><?php echo wp_kses_post( $step ); ?></li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['items_heading'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="<?php echo esc_attr( $prefix ); ?>-items">
			<h2 id="<?php echo esc_attr( $prefix ); ?>-items"><?php echo esc_html( $guide['items_heading'] ); ?></h2>
			<?php if ( ! empty( $guide['items_intro'] ) ) : ?>
				<p><?php echo wp_kses_post( $guide['items_intro'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $guide['items'] ) ) : ?>
				<dl class="ltt-block-guide__faq">
					<?php foreach ( (array) $guide['items'] as $item_name => $item ) : ?>
						<?php $item = is_array( $item ) ? $item : array( 'text' => $item ); ?>
						<dt><?php echo esc_html( $item_name ); ?></dt>
						<dd>
							<?php echo wp_kses_post( $item['text'] ?? '' ); ?>
							<?php if ( ! empty( $item['pattern'] ) ) : ?>
								<?php ltt_dive_in_render_blog_template_actions( (int) $item['pattern'], (string) $item_name ); ?>
							<?php endif; ?>
						</dd>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['fields'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="<?php echo esc_attr( $prefix ); ?>-fields">
			<h2 id="<?php echo esc_attr( $prefix ); ?>-fields"><?php echo esc_html( $labels['fields'] ); ?></h2>
			<?php if ( ! empty( $guide['fields_intro'] ) ) : ?>
				<p><?php echo wp_kses_post( $guide['fields_intro'] ); ?></p>
			<?php endif; ?>
			<?php ltt_dive_in_render_block_guide_fields( $guide['fields'], $labels['fields'] ); ?>
		</section>
	<?php endif; ?>

	<?php foreach ( (array) ( $guide['parts'] ?? array() ) as $index => $part ) : ?>
		<?php $part_id = $prefix . '-part-' . $index; ?>
		<section class="ltt-block-guide__section ltt-block-guide__variant" aria-labelledby="<?php echo esc_attr( $part_id ); ?>">
			<h3 id="<?php echo esc_attr( $part_id ); ?>"><?php echo esc_html( $part['name'] ?? '' ); ?></h3>
			<?php if ( ! empty( $part['description'] ) ) : ?>
				<p><?php echo wp_kses_post( $part['description'] ); ?></p>
			<?php endif; ?>
			<?php ltt_dive_in_render_block_guide_fields( $part['fields'] ?? array(), (string) ( $part['name'] ?? '' ) ); ?>
			<?php if ( ! empty( $part['notes'] ) ) : ?>
				<?php ltt_dive_in_render_block_guide_list( (array) $part['notes'], 'ltt-block-guide__notes' ); ?>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>

	<?php if ( ! empty( $guide['tips'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="<?php echo esc_attr( $prefix ); ?>-tips">
			<h2 id="<?php echo esc_attr( $prefix ); ?>-tips"><?php esc_html_e( 'Tips', 'ltt-dive-in' ); ?></h2>
			<?php ltt_dive_in_render_block_guide_list( (array) $guide['tips'] ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['troubleshooting'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="<?php echo esc_attr( $prefix ); ?>-troubleshooting">
			<h2 id="<?php echo esc_attr( $prefix ); ?>-troubleshooting"><?php esc_html_e( 'If something looks off', 'ltt-dive-in' ); ?></h2>
			<dl class="ltt-block-guide__faq">
				<?php foreach ( (array) $guide['troubleshooting'] as $problem => $answer ) : ?>
					<dt><?php echo esc_html( $problem ); ?></dt>
					<dd><?php echo wp_kses_post( $answer ); ?></dd>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>
	<?php
}

/**
 * Render the Page options section of the Site Guide.
 */
function ltt_dive_in_render_page_options_guide_page() {
	if ( ! current_user_can( ltt_dive_in_block_guide_capability() ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to view this page.', 'ltt-dive-in' ) );
	}

	$guides = ltt_dive_in_get_page_options_guides();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view selection.
	$current = isset( $_GET['option'] ) ? sanitize_key( wp_unslash( $_GET['option'] ) ) : '';

	echo '<div class="wrap ltt-block-guide">';

	if ( $current && isset( $guides[ $current ] ) ) {
		ltt_dive_in_render_page_options_guide_detail( $guides[ $current ] );
	} else {
		ltt_dive_in_render_page_options_guide_index( $guides );
	}

	echo '</div>';
}

/**
 * Render the list of page option guides.
 *
 * @param array[] $guides Page option guides.
 */
function ltt_dive_in_render_page_options_guide_index( $guides ) {
	?>
	<h1><?php esc_html_e( 'Page options', 'ltt-dive-in' ); ?></h1>
	<p class="ltt-block-guide__lead">
		<?php esc_html_e( 'Besides blocks, the page and post editors have extra boxes that control things like the top of the page, how a page appears on cards, and how it is grouped. Some page templates and the Media Library have their own options too.', 'ltt-dive-in' ); ?>
	</p>

	<?php
	foreach ( ltt_dive_in_page_options_guide_groups() as $group => $group_label ) :
		$in_group = array_filter(
			$guides,
			static function ( $guide ) use ( $group ) {
				return ( $guide['group'] ?? '' ) === $group;
			}
		);

		if ( ! $in_group ) {
			continue;
		}
		?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-page-options-group-<?php echo esc_attr( $group ); ?>">
			<h2 id="ltt-page-options-group-<?php echo esc_attr( $group ); ?>"><?php echo esc_html( $group_label ); ?></h2>
			<ul class="ltt-block-guide__grid">
				<?php foreach ( $in_group as $guide ) : ?>
					<?php $updated = ltt_dive_in_block_guide_updated( $guide ); ?>
					<li class="ltt-block-guide__card">
						<span class="ltt-block-guide__card-icon dashicons dashicons-<?php echo esc_attr( $guide['icon'] ?? 'admin-page' ); ?>" aria-hidden="true"></span>
						<h3 class="ltt-block-guide__card-title">
							<a href="<?php echo esc_url( ltt_dive_in_page_options_guide_url( $guide['slug'] ) ); ?>"><?php echo esc_html( $guide['title'] ); ?></a>
						</h3>
						<?php if ( ! empty( $guide['where'] ) ) : ?>
							<p class="ltt-block-guide__card-menu"><?php echo esc_html( $guide['where'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $guide['short'] ) ) : ?>
							<p class="ltt-block-guide__card-description"><?php echo esc_html( $guide['short'] ); ?></p>
						<?php endif; ?>
						<?php if ( $updated ) : ?>
							<p class="ltt-block-guide__card-updated">
								<?php
								/* translators: %s: date. */
								echo esc_html( sprintf( __( 'Updated %s', 'ltt-dive-in' ), $updated ) );
								?>
							</p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	endforeach;
}

/**
 * Render one page option guide.
 *
 * @param array $guide Page option guide.
 */
function ltt_dive_in_render_page_options_guide_detail( $guide ) {
	$updated = ltt_dive_in_block_guide_updated( $guide );
	?>
	<p class="ltt-block-guide__back">
		<a href="<?php echo esc_url( ltt_dive_in_page_options_guide_url() ); ?>">
			<span aria-hidden="true">&larr;</span> <?php esc_html_e( 'All page options', 'ltt-dive-in' ); ?>
		</a>
	</p>

	<header class="ltt-block-guide__header">
		<span class="ltt-block-guide__header-icon dashicons dashicons-<?php echo esc_attr( $guide['icon'] ?? 'admin-page' ); ?>" aria-hidden="true"></span>
		<div>
			<h1><?php echo esc_html( $guide['title'] ); ?></h1>
			<?php if ( ! empty( $guide['summary'] ) ) : ?>
				<p class="ltt-block-guide__lead"><?php echo wp_kses_post( $guide['summary'] ); ?></p>
			<?php endif; ?>
			<?php if ( $updated ) : ?>
				<p class="ltt-block-guide__updated">
					<?php
					/* translators: %s: date. */
					echo esc_html( sprintf( __( 'Last updated: %s', 'ltt-dive-in' ), $updated ) );
					?>
				</p>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( ! empty( $guide['where'] ) || ! empty( $guide['applies'] ) ) : ?>
		<div class="ltt-block-guide__where">
			<?php if ( ! empty( $guide['where'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Where to find it:', 'ltt-dive-in' ); ?></strong> <?php echo esc_html( $guide['where'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $guide['applies'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Available on:', 'ltt-dive-in' ); ?></strong> <?php echo wp_kses_post( $guide['applies'] ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php
	ltt_dive_in_render_site_guide_sections( $guide, 'ltt-page-option' );
}

/**
 * Get the published blog template patterns ("Blog Templates" pattern category).
 *
 * @return WP_Post[]
 */
function ltt_dive_in_get_blog_templates() {
	static $templates = null;

	if ( null !== $templates ) {
		return $templates;
	}

	$templates = array();
	$term      = get_term_by( 'name', 'Blog Templates', 'wp_pattern_category' );

	if ( ! $term || is_wp_error( $term ) ) {
		return $templates;
	}

	$templates = get_posts(
		array(
			'post_type'      => 'wp_block',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_pattern_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
		)
	);

	return $templates;
}

/**
 * Get a blog template's short name, without the "Blog Template: 1." prefix.
 *
 * @param WP_Post $pattern Pattern post.
 * @return string
 */
function ltt_dive_in_blog_template_name( $pattern ) {
	return (string) preg_replace( '/^Blog Template:\s*\d+\.\s*/', '', get_the_title( $pattern ) );
}

/**
 * Find a published blog template by ID.
 *
 * @param int $pattern_id Pattern post ID.
 * @return WP_Post|null
 */
function ltt_dive_in_find_blog_template( $pattern_id ) {
	foreach ( ltt_dive_in_get_blog_templates() as $pattern ) {
		if ( (int) $pattern->ID === (int) $pattern_id ) {
			return $pattern;
		}
	}

	return null;
}

/**
 * Render the buttons under one blog template in the Site Guide.
 *
 * @param int    $pattern_id Pattern post ID.
 * @param string $name       Template name, for accessible button names.
 */
function ltt_dive_in_render_blog_template_actions( $pattern_id, $name ) {
	$post_type = get_post_type_object( 'post' );

	if ( ! $post_type || ! current_user_can( $post_type->cap->create_posts ) ) {
		return;
	}

	$nonce = wp_create_nonce( 'ltt_dive_in_blog_template_draft' );
	$modes = array(
		'post'     => array(
			'label' => __( 'Start a post with this template', 'ltt-dive-in' ),
			'class' => 'button button-primary',
		),
		'practice' => array(
			'label' => __( 'Try this template', 'ltt-dive-in' ),
			'class' => 'button',
		),
	);
	?>
	<div class="ltt-block-guide__preview-actions ltt-block-guide__item-actions">
		<?php foreach ( $modes as $mode => $button ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ltt_dive_in_blog_template_draft">
				<input type="hidden" name="pattern" value="<?php echo esc_attr( (string) $pattern_id ); ?>">
				<input type="hidden" name="mode" value="<?php echo esc_attr( $mode ); ?>">
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">
				<button type="submit" class="<?php echo esc_attr( $button['class'] ); ?>">
					<?php echo esc_html( $button['label'] ); ?>
					<span class="screen-reader-text"><?php echo esc_html( sprintf( '(%s)', $name ) ); ?></span>
				</button>
			</form>
		<?php endforeach; ?>
		<?php if ( current_user_can( 'edit_post', $pattern_id ) ) : ?>
			<a class="button-link" href="<?php echo esc_url( (string) get_edit_post_link( $pattern_id ) ); ?>">
				<?php esc_html_e( 'Edit the template', 'ltt-dive-in' ); ?>
				<span class="screen-reader-text"><?php echo esc_html( sprintf( '(%s)', $name ) ); ?></span>
			</a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Create a post from a blog template and open it in the editor.
 *
 * "post" creates a normal draft. "practice" creates a practice draft that is
 * trashed on its own like the ones from "Try this block".
 */
function ltt_dive_in_create_blog_template_draft() {
	check_admin_referer( 'ltt_dive_in_blog_template_draft' );

	$post_type = get_post_type_object( 'post' );

	if ( ! $post_type || ! current_user_can( ltt_dive_in_block_guide_capability() ) || ! current_user_can( $post_type->cap->create_posts ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to create posts.', 'ltt-dive-in' ), '', array( 'response' => 403 ) );
	}

	$pattern  = ltt_dive_in_find_blog_template( isset( $_POST['pattern'] ) ? absint( $_POST['pattern'] ) : 0 );
	$practice = isset( $_POST['mode'] ) && 'practice' === sanitize_key( wp_unslash( $_POST['mode'] ) );

	if ( ! $pattern ) {
		wp_die( esc_html__( 'That blog template no longer exists.', 'ltt-dive-in' ), '', array( 'response' => 404 ) );
	}

	$post = array(
		'post_type'    => 'post',
		'post_status'  => 'draft',
		'post_title'   => '',
		'post_content' => $pattern->post_content,
	);

	if ( $practice ) {
		/* translators: %s: blog template name. */
		$post['post_title'] = sprintf( __( 'Practice: %s (blog template)', 'ltt-dive-in' ), ltt_dive_in_blog_template_name( $pattern ) );
		$post['meta_input'] = array( LTT_DIVE_IN_BLOCK_GUIDE_PRACTICE_META => 1 );
	}

	$post_id = wp_insert_post( wp_slash( $post ), true );

	if ( is_wp_error( $post_id ) ) {
		wp_die( esc_html( $post_id->get_error_message() ) );
	}

	wp_safe_redirect( get_edit_post_link( $post_id, 'url' ) );
	exit;
}
add_action( 'admin_post_ltt_dive_in_blog_template_draft', 'ltt_dive_in_create_blog_template_draft' );

/**
 * Render a shortcut to the blog templates guide on the other Site Guide screens.
 */
function ltt_dive_in_render_blog_templates_shortcut() {
	if ( ! ltt_dive_in_get_blog_templates() ) {
		return;
	}
	?>
	<div class="ltt-block-guide__where ltt-block-guide__shortcut">
		<p>
			<strong><?php esc_html_e( 'Writing a blog post?', 'ltt-dive-in' ); ?></strong>
			<?php esc_html_e( 'Start from one of the ready-made blog templates.', 'ltt-dive-in' ); ?>
			<a href="<?php echo esc_url( ltt_dive_in_page_options_guide_url( 'blog-templates' ) ); ?>"><?php esc_html_e( 'See the blog templates', 'ltt-dive-in' ); ?></a>
		</p>
	</div>
	<?php
}
