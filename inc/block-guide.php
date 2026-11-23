<?php
/**
 * Block Guide admin screen.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability required to read the Block Guide (administrators and editors).
 *
 * @return string
 */
function ltt_dive_in_block_guide_capability() {
	return (string) apply_filters( 'ltt_dive_in_block_guide_capability', 'edit_others_posts' );
}

/**
 * Register the Block Guide admin page.
 */
function ltt_dive_in_register_block_guide_page() {
	add_menu_page(
		__( 'Block Guide', 'ltt-dive-in' ),
		__( 'Block Guide', 'ltt-dive-in' ),
		ltt_dive_in_block_guide_capability(),
		'ltt-dive-in-block-guide',
		'ltt_dive_in_render_block_guide_page',
		'dashicons-book-alt',
		21
	);
}
add_action( 'admin_menu', 'ltt_dive_in_register_block_guide_page' );

/**
 * Enqueue the Block Guide stylesheet on its own screen only.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function ltt_dive_in_enqueue_block_guide_assets( $hook_suffix ) {
	if ( 'toplevel_page_ltt-dive-in-block-guide' !== $hook_suffix ) {
		return;
	}

	$style_path = LTT_DIVE_IN_DIR . '/assets/css/admin/block-guide.css';

	wp_enqueue_style(
		'ltt-dive-in-block-guide',
		LTT_DIVE_IN_URI . '/assets/css/admin/block-guide.css',
		array(),
		file_exists( $style_path ) ? (string) filemtime( $style_path ) : LTT_DIVE_IN_VERSION
	);

	$script_path = LTT_DIVE_IN_DIR . '/assets/js/admin/block-guide.js';

	wp_enqueue_script(
		'ltt-dive-in-block-guide',
		LTT_DIVE_IN_URI . '/assets/js/admin/block-guide.js',
		array(),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : LTT_DIVE_IN_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ltt_dive_in_enqueue_block_guide_assets' );

/**
 * Format a guide's "updated" date (Y-m-d) with the site's date format.
 *
 * @param array $guide Guide content.
 * @return string Formatted date, or an empty string when missing or invalid.
 */
function ltt_dive_in_block_guide_updated( $guide ) {
	$value = isset( $guide['updated'] ) && is_string( $guide['updated'] ) ? $guide['updated'] : '';
	$date  = $value ? DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() ) : false;

	if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
		return '';
	}

	return (string) wp_date( get_option( 'date_format' ), $date->getTimestamp() );
}

/**
 * Render a live preview of a block sample with its toolbar.
 *
 * @param string $slug    Block folder slug.
 * @param string $variant Variant key, or "default".
 * @param string $label   Human-readable name for the previewed layout.
 */
function ltt_dive_in_render_block_guide_preview( $slug, $variant, $label ) {
	if ( ! ltt_dive_in_block_guide_has_sample( $slug, $variant ) ) {
		return;
	}

	$preview_url = ltt_dive_in_block_guide_preview_url( $slug, $variant );
	$post_type   = get_post_type_object( ltt_dive_in_block_guide_practice_post_type( $slug ) );
	$can_try     = $post_type && current_user_can( $post_type->cap->create_posts );
	?>
	<div class="ltt-block-guide__preview" data-block-guide-preview>
		<div class="ltt-block-guide__preview-toolbar">
			<div class="ltt-block-guide__devices" role="group" aria-label="<?php esc_attr_e( 'Preview size', 'ltt-dive-in' ); ?>" data-preview-devices hidden>
				<button type="button" class="button" aria-pressed="true" data-preview-device="desktop"><?php esc_html_e( 'Desktop', 'ltt-dive-in' ); ?></button>
				<button type="button" class="button" aria-pressed="false" data-preview-device="mobile"><?php esc_html_e( 'Mobile', 'ltt-dive-in' ); ?></button>
			</div>
			<div class="ltt-block-guide__preview-actions">
				<a class="button" href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Open full size', 'ltt-dive-in' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?></span>
				</a>
				<?php if ( $can_try ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="ltt_dive_in_block_guide_practice">
						<input type="hidden" name="block" value="<?php echo esc_attr( $slug ); ?>">
						<input type="hidden" name="variant" value="<?php echo esc_attr( $variant ); ?>">
						<?php wp_nonce_field( 'ltt_dive_in_block_guide_practice' ); ?>
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Try this block', 'ltt-dive-in' ); ?>
							<span class="screen-reader-text">
								<?php
								/* translators: %s: layout name. */
								echo esc_html( sprintf( __( 'with the %s sample in a new practice draft', 'ltt-dive-in' ), $label ) );
								?>
							</span>
						</button>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<div class="ltt-block-guide__stage" data-preview-stage>
			<iframe
				class="ltt-block-guide__frame"
				src="<?php echo esc_url( $preview_url ); ?>"
				title="<?php /* translators: %s: layout name. */ echo esc_attr( sprintf( __( 'Preview: %s', 'ltt-dive-in' ), $label ) ); ?>"
				loading="lazy"
				data-preview-frame
			></iframe>
		</div>
		<p class="ltt-block-guide__preview-note"><?php esc_html_e( 'This is the real block with sample text and photos from the Media Library. "Try this block" creates a new draft with this sample and opens it in the editor, so you can change anything without affecting the live site.', 'ltt-dive-in' ); ?></p>
	</div>
	<?php
}

/**
 * Collect theme blocks that are registered, keyed by their folder slug.
 *
 * Each entry holds the registered block type and its optional guide content
 * from blocks/{slug}/guide.php.
 *
 * @return array[]
 */
function ltt_dive_in_get_block_guide_entries() {
	$registry = WP_Block_Type_Registry::get_instance();
	$entries  = array();

	foreach ( (array) glob( LTT_DIVE_IN_DIR . '/blocks/*/block.json' ) as $metadata_file ) {
		$metadata = wp_json_file_decode( $metadata_file, array( 'associative' => true ) );

		if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
			continue;
		}

		$block_type = $registry->get_registered( $metadata['name'] );

		if ( ! $block_type || 'ltt-blocks' !== $block_type->category ) {
			continue;
		}

		$slug       = basename( dirname( $metadata_file ) );
		$guide_file = dirname( $metadata_file ) . '/guide.php';
		$guide      = file_exists( $guide_file ) ? include $guide_file : array();

		$entries[ $slug ] = array(
			'slug'  => $slug,
			'type'  => $block_type,
			'guide' => is_array( $guide ) ? $guide : array(),
		);
	}

	uasort(
		$entries,
		static function ( $a, $b ) {
			return strnatcasecmp( (string) $a['type']->title, (string) $b['type']->title );
		}
	);

	return $entries;
}

/**
 * Get the Dashicons class for a block icon.
 *
 * @param WP_Block_Type $block_type Block type.
 * @return string
 */
function ltt_dive_in_block_guide_icon_class( $block_type ) {
	$icon = is_string( $block_type->icon ) && preg_match( '/^[a-z0-9-]+$/', $block_type->icon ) ? $block_type->icon : 'block-default';

	return 'dashicons dashicons-' . $icon;
}

/**
 * Get the Block Guide URL, optionally for one block.
 *
 * @param string $slug Block folder slug.
 * @return string
 */
function ltt_dive_in_block_guide_url( $slug = '' ) {
	$args = array( 'page' => 'ltt-dive-in-block-guide' );

	if ( $slug ) {
		$args['block'] = $slug;
	}

	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

/**
 * Render the Block Guide admin page.
 */
function ltt_dive_in_render_block_guide_page() {
	if ( ! current_user_can( ltt_dive_in_block_guide_capability() ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to view this page.', 'ltt-dive-in' ) );
	}

	$entries = ltt_dive_in_get_block_guide_entries();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view selection.
	$current = isset( $_GET['block'] ) ? sanitize_key( wp_unslash( $_GET['block'] ) ) : '';

	echo '<div class="wrap ltt-block-guide">';

	if ( $current && isset( $entries[ $current ] ) ) {
		ltt_dive_in_render_block_guide_detail( $entries[ $current ] );
	} else {
		ltt_dive_in_render_block_guide_index( $entries );
	}

	echo '</div>';
}

/**
 * Render the list of blocks.
 *
 * @param array[] $entries Guide entries.
 */
function ltt_dive_in_render_block_guide_index( $entries ) {
	?>
	<h1><?php esc_html_e( 'Block Guide', 'ltt-dive-in' ); ?></h1>
	<p class="ltt-block-guide__lead">
		<?php esc_html_e( 'These are the custom blocks built for this site. You can find all of them in the block inserter under "LTT Blocks". Open any block below to see what it is for, which layouts it has, and what each field does.', 'ltt-dive-in' ); ?>
	</p>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only status after a redirect.
	if ( isset( $_GET['practice_trashed'] ) ) :
		$trashed = absint( $_GET['practice_trashed'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="notice notice-success inline" role="status">
			<p>
				<?php
				/* translators: %d: number of drafts. */
				echo esc_html( sprintf( _n( '%d practice draft moved to the trash.', '%d practice drafts moved to the trash.', $trashed, 'ltt-dive-in' ), $trashed ) );
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php $practice_count = count( ltt_dive_in_block_guide_practice_drafts() ); ?>
	<?php if ( $practice_count ) : ?>
		<div class="ltt-block-guide__practice">
			<p>
				<?php
				/* translators: %d: number of drafts. */
				echo esc_html( sprintf( _n( 'You have %d practice draft from "Try this block".', 'You have %d practice drafts from "Try this block".', $practice_count, 'ltt-dive-in' ), $practice_count ) );
				?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ltt_dive_in_block_guide_trash_practice">
				<?php wp_nonce_field( 'ltt_dive_in_block_guide_trash_practice' ); ?>
				<button type="submit" class="button"><?php esc_html_e( 'Move them to the trash', 'ltt-dive-in' ); ?></button>
			</form>
		</div>
	<?php endif; ?>

	<?php if ( ! $entries ) : ?>
		<p><?php esc_html_e( 'No theme blocks are registered right now.', 'ltt-dive-in' ); ?></p>
		<?php
		return;
	endif;
	?>

	<ul class="ltt-block-guide__grid">
		<?php foreach ( $entries as $entry ) : ?>
			<?php
			$block_type = $entry['type'];
			$has_guide  = ! empty( $entry['guide']['summary'] );
			?>
			<li class="ltt-block-guide__card">
				<span class="ltt-block-guide__card-icon <?php echo esc_attr( ltt_dive_in_block_guide_icon_class( $block_type ) ); ?>" aria-hidden="true"></span>
				<h2 class="ltt-block-guide__card-title">
					<a href="<?php echo esc_url( ltt_dive_in_block_guide_url( $entry['slug'] ) ); ?>"><?php echo esc_html( $block_type->title ); ?></a>
				</h2>
				<?php if ( $block_type->description ) : ?>
					<p class="ltt-block-guide__card-description"><?php echo esc_html( $block_type->description ); ?></p>
				<?php endif; ?>
				<?php $preview_count = count( ltt_dive_in_block_guide_samples( $entry['slug'] ) ); ?>
				<ul class="ltt-block-guide__badges">
					<li class="ltt-block-guide__status<?php echo $has_guide ? ' is-ready' : ''; ?>">
						<?php echo $has_guide ? esc_html__( 'Guide ready', 'ltt-dive-in' ) : esc_html__( 'Guide coming soon', 'ltt-dive-in' ); ?>
					</li>
					<?php if ( $preview_count ) : ?>
						<li class="ltt-block-guide__status is-preview">
							<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
							<?php
							echo esc_html(
								1 === $preview_count
									? __( 'Live preview', 'ltt-dive-in' )
									/* translators: %d: number of layouts with a live preview. */
									: sprintf( _n( '%d live preview', '%d live previews', $preview_count, 'ltt-dive-in' ), $preview_count )
							);
							?>
						</li>
					<?php endif; ?>
				</ul>
				<?php $updated = ltt_dive_in_block_guide_updated( $entry['guide'] ); ?>
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
	<?php
}

/**
 * Render a list of strings as an unordered list.
 *
 * @param string[] $items Items; limited HTML is allowed.
 * @param string   $class List class.
 */
function ltt_dive_in_render_block_guide_list( $items, $class = '' ) {
	$items = array_filter( (array) $items, 'is_string' );

	if ( ! $items ) {
		return;
	}

	echo '<ul class="' . esc_attr( trim( 'ltt-block-guide__list ' . $class ) ) . '">';

	foreach ( $items as $item ) {
		echo '<li>' . wp_kses_post( $item ) . '</li>';
	}

	echo '</ul>';
}

/**
 * Render a field reference table.
 *
 * @param array[] $fields Rows with label, description, and optional required flag.
 * @param string  $caption Table caption.
 */
function ltt_dive_in_render_block_guide_fields( $fields, $caption ) {
	$fields = array_filter(
		(array) $fields,
		static function ( $field ) {
			return is_array( $field ) && ! empty( $field['label'] );
		}
	);

	if ( ! $fields ) {
		return;
	}
	?>
	<table class="widefat striped ltt-block-guide__fields">
		<caption class="screen-reader-text"><?php echo esc_html( $caption ); ?></caption>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Field', 'ltt-dive-in' ); ?></th>
				<th scope="col"><?php esc_html_e( 'What it does', 'ltt-dive-in' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $fields as $field ) : ?>
				<tr>
					<th scope="row">
						<?php echo esc_html( $field['label'] ); ?>
						<span class="ltt-block-guide__requirement"><?php echo empty( $field['required'] ) ? esc_html__( 'Optional', 'ltt-dive-in' ) : esc_html__( 'Required', 'ltt-dive-in' ); ?></span>
					</th>
					<td><?php echo wp_kses_post( $field['description'] ?? '' ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render one block's guide.
 *
 * @param array $entry Guide entry.
 */
function ltt_dive_in_render_block_guide_detail( $entry ) {
	$block_type = $entry['type'];
	$guide      = $entry['guide'];
	?>
	<p class="ltt-block-guide__back">
		<a href="<?php echo esc_url( ltt_dive_in_block_guide_url() ); ?>">
			<span aria-hidden="true">&larr;</span> <?php esc_html_e( 'All blocks', 'ltt-dive-in' ); ?>
		</a>
	</p>

	<header class="ltt-block-guide__header">
		<span class="ltt-block-guide__header-icon <?php echo esc_attr( ltt_dive_in_block_guide_icon_class( $block_type ) ); ?>" aria-hidden="true"></span>
		<div>
			<h1><?php echo esc_html( $block_type->title ); ?></h1>
			<?php if ( ! empty( $guide['summary'] ) ) : ?>
				<p class="ltt-block-guide__lead"><?php echo wp_kses_post( $guide['summary'] ); ?></p>
			<?php elseif ( $block_type->description ) : ?>
				<p class="ltt-block-guide__lead"><?php echo esc_html( $block_type->description ); ?></p>
			<?php endif; ?>
			<?php $updated = ltt_dive_in_block_guide_updated( $guide ); ?>
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

	<?php if ( empty( $guide['summary'] ) ) : ?>
		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'The full guide for this block is still being written.', 'ltt-dive-in' ); ?></p>
		</div>
		<?php
		return;
	endif;
	?>

	<?php if ( ! empty( $guide['where'] ) ) : ?>
		<p class="ltt-block-guide__where"><strong><?php esc_html_e( 'Where you can use it:', 'ltt-dive-in' ); ?></strong> <?php echo wp_kses_post( $guide['where'] ); ?></p>
	<?php endif; ?>

	<?php if ( empty( $guide['variants'] ) && ltt_dive_in_block_guide_has_sample( $entry['slug'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-block-guide-preview">
			<h2 id="ltt-block-guide-preview"><?php esc_html_e( 'Preview', 'ltt-dive-in' ); ?></h2>
			<?php ltt_dive_in_render_block_guide_preview( $entry['slug'], 'default', $block_type->title ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['steps'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-block-guide-steps">
			<h2 id="ltt-block-guide-steps"><?php esc_html_e( 'How to add it', 'ltt-dive-in' ); ?></h2>
			<ol class="ltt-block-guide__list ltt-block-guide__steps">
				<?php foreach ( (array) $guide['steps'] as $step ) : ?>
					<li><?php echo wp_kses_post( $step ); ?></li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['fields'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-block-guide-shared-fields">
			<h2 id="ltt-block-guide-shared-fields"><?php echo esc_html( $guide['fields_heading'] ?? __( 'Fields', 'ltt-dive-in' ) ); ?></h2>
			<?php if ( ! empty( $guide['fields_intro'] ) ) : ?>
				<p><?php echo wp_kses_post( $guide['fields_intro'] ); ?></p>
			<?php endif; ?>
			<?php ltt_dive_in_render_block_guide_fields( $guide['fields'], $guide['fields_heading'] ?? __( 'Fields', 'ltt-dive-in' ) ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['variants'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-block-guide-variants">
			<h2 id="ltt-block-guide-variants"><?php esc_html_e( 'Layouts', 'ltt-dive-in' ); ?></h2>
			<?php if ( ! empty( $guide['variants_intro'] ) ) : ?>
				<p><?php echo wp_kses_post( $guide['variants_intro'] ); ?></p>
			<?php endif; ?>

			<nav class="ltt-block-guide__toc" aria-label="<?php esc_attr_e( 'Layouts in this block', 'ltt-dive-in' ); ?>">
				<ul>
					<?php foreach ( (array) $guide['variants'] as $key => $variant ) : ?>
						<li><a href="#ltt-variant-<?php echo esc_attr( sanitize_html_class( $key ) ); ?>"><?php echo esc_html( $variant['name'] ?? $key ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<?php foreach ( (array) $guide['variants'] as $key => $variant ) : ?>
				<?php $variant_id = 'ltt-variant-' . sanitize_html_class( $key ); ?>
				<article id="<?php echo esc_attr( $variant_id ); ?>" class="ltt-block-guide__variant" aria-labelledby="<?php echo esc_attr( $variant_id ); ?>-title">
					<h3 id="<?php echo esc_attr( $variant_id ); ?>-title"><?php echo esc_html( $variant['name'] ?? $key ); ?></h3>
					<?php if ( ! empty( $variant['description'] ) ) : ?>
						<p><?php echo wp_kses_post( $variant['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $variant['best_for'] ) ) : ?>
						<p><strong><?php esc_html_e( 'Works well for:', 'ltt-dive-in' ); ?></strong> <?php echo wp_kses_post( $variant['best_for'] ); ?></p>
					<?php endif; ?>
					<?php ltt_dive_in_render_block_guide_preview( $entry['slug'], (string) $key, $variant['name'] ?? (string) $key ); ?>
					<?php
					ltt_dive_in_render_block_guide_fields(
						$variant['fields'] ?? array(),
						/* translators: %s: layout name. */
						sprintf( __( '%s fields', 'ltt-dive-in' ), $variant['name'] ?? $key )
					);
					?>
					<?php if ( ! empty( $variant['notes'] ) ) : ?>
						<?php ltt_dive_in_render_block_guide_list( $variant['notes'], 'ltt-block-guide__notes' ); ?>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['tips'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-block-guide-tips">
			<h2 id="ltt-block-guide-tips"><?php esc_html_e( 'Tips', 'ltt-dive-in' ); ?></h2>
			<?php ltt_dive_in_render_block_guide_list( $guide['tips'] ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $guide['troubleshooting'] ) ) : ?>
		<section class="ltt-block-guide__section" aria-labelledby="ltt-block-guide-troubleshooting">
			<h2 id="ltt-block-guide-troubleshooting"><?php esc_html_e( 'If something looks off', 'ltt-dive-in' ); ?></h2>
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
