<?php
/**
 * Content Module ACF block renderer.
 *
 * @package LTT_Dive_In
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview Whether the block is being rendered in the editor.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) ) {
	return;
}

$variants = array(
	'side_by_side',
	'copy_block',
	'icon_block',
	'newsletter_signup',
	'meetings_request',
	'stats',
	'comparison_table',
	'testimonial_block',
);
$variant  = (string) get_field( 'ltt_dive_in_content_module_variant' );
$is_preview = ! empty( $is_preview );

if ( ! in_array( $variant, $variants, true ) ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Choose a Content Module variant to begin.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

if ( 'testimonial_block' === $variant ) {
	$testimonial_block_id = ! empty( $block['id'] ) ? sanitize_html_class( $block['id'] ) : sanitize_html_class( wp_unique_id() );
	$testimonial_id       = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'content-module-' . $testimonial_block_id;

	get_template_part(
		'template-parts/blocks/testimonials',
		null,
		array(
			'rows'       => get_field( 'ltt_dive_in_content_module_testimonials' ),
			'block'      => $block,
			'id'         => $testimonial_id,
			'is_preview' => $is_preview,
		)
	);

	return;
}

$block_id = ! empty( $block['id'] ) ? sanitize_html_class( $block['id'] ) : sanitize_html_class( wp_unique_id() );
$id       = ! empty( $block['anchor'] ) ? sanitize_html_class( $block['anchor'] ) : 'content-module-' . $block_id;
$classes  = array( 'content-module', 'content-module--' . $variant );
$heading  = '';
$bg_image = 0;
$stats_background_color = '';
$newsletter_aside_heading = '';
$newsletter_aside_copy    = '';
$newsletter_aside_cta     = array();

if ( ! empty( $block['align'] ) && 'full' === $block['align'] ) {
	$classes[] = 'alignfull';
}

if ( $is_preview ) {
	$classes[] = 'content-module--preview';
}

$get_text = static function ( $value ) {
	return is_string( $value ) ? trim( $value ) : '';
};

$get_link = static function ( $value ) {
	if ( ! is_array( $value ) || empty( $value['title'] ) || empty( $value['url'] ) ) {
		return array();
	}

	$url = esc_url_raw( trim( (string) $value['url'] ) );

	if ( ! $url ) {
		return array();
	}

	return array(
		'title'  => trim( (string) $value['title'] ),
		'url'    => $url,
		'target' => ! empty( $value['target'] ) ? (string) $value['target'] : '',
	);
};

$get_image_id = static function ( $value ) {
	$image_id = absint( $value );

	return $image_id && wp_attachment_is_image( $image_id ) ? $image_id : 0;
};

$render_background = static function ( $image_id ) {
	if ( ! $image_id ) {
		return;
	}

	echo '<div class="content-module__background" aria-hidden="true">';
	echo wp_get_attachment_image( $image_id, 'full', false, array( 'class' => 'content-module__background-image', 'alt' => '', 'loading' => 'lazy', 'sizes' => '100vw' ) );
	echo '<span class="content-module__background-overlay"></span>';
	echo '</div>';
};

$render_cta = static function ( $link, $modifier = 'primary-outline', $extra_class = '' ) use ( $get_link ) {
	$link = $get_link( $link );

	if ( ! $link ) {
		return;
	}

	$target = '_blank' === $link['target'] ? ' target="_blank" rel="noopener noreferrer"' : '';
	$class  = trim( 'ltt-button ltt-button--' . $modifier . ' ' . $extra_class );

	echo '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $link['url'] ) . '"' . $target . '>' . esc_html( $link['title'] ) . '</a>';
};

$render_copy = static function ( $copy, $class = '' ) use ( $get_text ) {
	$copy = $get_text( $copy );

	if ( $copy ) {
		echo '<div class="' . esc_attr( trim( 'content-module__copy ' . $class ) ) . '">' . wpautop( esc_html( $copy ) ) . '</div>';
	}
};

$render_form_disclosure = static function ( $copy ) {
	if ( ! is_string( $copy ) || '' === trim( $copy ) ) {
		return;
	}

	$copy = trim( $copy );

	if ( false !== strpos( $copy, '<' ) ) {
		$disclosure = wp_kses_post( $copy );
	} else {
		$parts      = preg_split( '/(\[[^\]]+\]\([^)]+\))/', $copy, -1, PREG_SPLIT_DELIM_CAPTURE );
		$disclosure = '';

		foreach ( $parts as $part ) {
			if ( preg_match( '/^\[([^\]]+)\]\(([^)]+)\)$/', $part, $matches ) ) {
				$url = esc_url( trim( $matches[2] ) );

				if ( $url ) {
					$disclosure .= '<a href="' . $url . '">' . esc_html( $matches[1] ) . '</a>';
				} else {
					$disclosure .= esc_html( $part );
				}
			} else {
				$disclosure .= esc_html( $part );
			}
		}

		$disclosure = '<p>' . $disclosure . '</p>';
	}

	echo '<div class="content-module__form-disclosure">' . $disclosure . '</div>';
};

$form_id = 0;

if ( in_array( $variant, array( 'newsletter_signup', 'meetings_request' ), true ) ) {
	$form_field = 'newsletter_signup' === $variant ? 'ltt_dive_in_content_module_newsletter_form' : 'ltt_dive_in_content_module_meetings_form';
	$form_id    = absint( get_field( $form_field ) );
	$form       = $form_id && class_exists( 'GFAPI' ) ? GFAPI::get_form( $form_id ) : false;

	if ( ! function_exists( 'gravity_form' ) || ! is_array( $form ) || empty( $form['is_active'] ) ) {
		if ( $is_preview ) {
			echo '<p>' . esc_html__( 'Select an active Gravity Form to preview this lead-capture module.', 'ltt-dive-in' ) . '</p>';
		}

		return;
	}
}

switch ( $variant ) {
	case 'side_by_side':
		$heading  = $get_text( get_field( 'ltt_dive_in_content_module_side_title' ) );
		$bg_image = $get_image_id( get_field( 'ltt_dive_in_content_module_side_background' ) );
		break;
	case 'icon_block':
		$heading = $get_text( get_field( 'ltt_dive_in_content_module_icon_heading' ) );
		break;
	case 'newsletter_signup':
		$heading  = $get_text( get_field( 'ltt_dive_in_content_module_newsletter_heading' ) );
		$bg_image = $get_image_id( get_field( 'ltt_dive_in_content_module_newsletter_background' ) );
		$newsletter_aside_heading = $get_text( get_field( 'ltt_dive_in_content_module_newsletter_aside_heading' ) );
		$newsletter_aside_copy    = $get_text( get_field( 'ltt_dive_in_content_module_newsletter_aside_copy', false, false ) );
		$newsletter_aside_cta     = $get_link( get_field( 'ltt_dive_in_content_module_newsletter_aside_cta' ) );
		break;
	case 'meetings_request':
		$heading  = $get_text( get_field( 'ltt_dive_in_content_module_meetings_heading' ) );
		$bg_image = $get_image_id( get_field( 'ltt_dive_in_content_module_meetings_background' ) );
		break;
	case 'stats':
		$heading                = $get_text( get_field( 'ltt_dive_in_content_module_stats_heading' ) );
		$stats_background_color = $get_text( get_field( 'field_ltt_dive_in_content_module_stats_background_color' ) );
		$bg_image               = $get_image_id( get_field( 'ltt_dive_in_content_module_stats_background' ) );
		break;
	case 'comparison_table':
		$heading = $get_text( get_field( 'ltt_dive_in_content_module_comparison_heading' ) );
		break;
}

if ( $heading ) {
	if ( $bg_image ) {
		$classes[] = 'content-module--image-background';
	} elseif ( 'stats' === $variant && in_array( $stats_background_color, array( 'navy', 'gold', 'burgundy', 'red', 'forest', 'green', 'blue', 'cyan' ), true ) ) {
		$classes[] = 'content-module--stats-bg-' . $stats_background_color;
	} else {
		$classes[] = 'content-module--white-background';
	}
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $heading ? ' aria-labelledby="' . esc_attr( $id . '-heading' ) . '"' : ''; ?>>
	<?php $render_background( $bg_image ); ?>
	<?php if ( 'side_by_side' === $variant ) : ?>
		<div class="content-module__container content-module__side-by-side">
			<div class="content-module__side-title">
				<h2 id="<?php echo esc_attr( $id . '-heading' ); ?>" class="content-module__heading"><?php echo esc_html( $heading ); ?></h2>
			</div>
			<div class="content-module__side-detail">
				<?php $render_copy( get_field( 'ltt_dive_in_content_module_side_body', false, false ) ); ?>
				<?php
				$ctas = get_field( 'ltt_dive_in_content_module_side_ctas' );
				$ctas = is_array( $ctas ) ? array_slice( $ctas, 0, 3 ) : array();
				if ( $ctas ) :
					?>
					<div class="content-module__actions">
						<?php foreach ( $ctas as $cta ) : ?>
							<?php $render_cta( $cta['link'] ?? array(), $bg_image ? 'glass' : 'primary-outline', 'content-module__cta' ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

	<?php elseif ( 'copy_block' === $variant ) : ?>
		<div class="content-module__container content-module__copy-block">
			<div class="content-module__rich-text"><?php echo wp_kses_post( get_field( 'ltt_dive_in_content_module_copy_content' ) ); ?></div>
		</div>

	<?php elseif ( 'icon_block' === $variant ) : ?>
		<div class="content-module__container content-module__icon-block">
			<header class="content-module__section-header">
				<h2 id="<?php echo esc_attr( $id . '-heading' ); ?>" class="content-module__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php $render_copy( get_field( 'ltt_dive_in_content_module_icon_intro', false, false ) ); ?>
				<?php $render_cta( get_field( 'ltt_dive_in_content_module_icon_cta' ), 'primary-outline', 'content-module__header-cta' ); ?>
			</header>
			<?php
			$blurbs = array();

			if ( have_rows( 'ltt_dive_in_content_module_icon_blurbs' ) ) {
				while ( have_rows( 'ltt_dive_in_content_module_icon_blurbs' ) && count( $blurbs ) < 4 ) {
					the_row();
					$blurbs[] = array(
						'icon'  => get_sub_field( 'icon', false ),
						'title' => get_sub_field( 'title', false ),
						'copy'  => get_sub_field( 'copy', false ),
					);
				}
			}
			?>
			<?php if ( $blurbs ) : ?>
				<ul class="content-module__icon-list content-module__icon-list--<?php echo esc_attr( count( $blurbs ) ); ?>" role="list">
					<?php foreach ( $blurbs as $blurb ) : ?>
						<?php
						$icon_value = $blurb['icon'] ?? 0;
						$icon_id    = absint( $icon_value );
						$icon_uri   = '';
						$title      = $get_text( $blurb['title'] ?? '' );
						$copy       = $get_text( $blurb['copy'] ?? '' );

						if ( $icon_id && 'image/svg+xml' === get_post_mime_type( $icon_id ) ) {
							$icon_uri = wp_get_attachment_url( $icon_id );
						}
						?>
						<?php if ( $title && $copy ) : ?>
							<li class="content-module__icon-item">
								<?php if ( $icon_uri ) : ?>
									<img class="content-module__icon" src="<?php echo esc_url( $icon_uri ); ?>" alt="" aria-hidden="true" />
								<?php endif; ?>
								<h3 class="content-module__icon-title"><?php echo esc_html( $title ); ?></h3>
								<p class="content-module__icon-copy"><?php echo esc_html( $copy ); ?></p>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

	<?php elseif ( 'newsletter_signup' === $variant ) : ?>
		<?php $has_newsletter_aside = $newsletter_aside_heading || $newsletter_aside_copy || $newsletter_aside_cta; ?>
		<div class="content-module__container content-module__lead-capture content-module__lead-capture--newsletter<?php echo $has_newsletter_aside ? ' content-module__lead-capture--has-aside' : ' content-module__lead-capture--no-aside'; ?>">
			<div class="content-module__lead-column">
				<div class="content-module__lead-copy">
					<h2 id="<?php echo esc_attr( $id . '-heading' ); ?>" class="content-module__heading"><?php echo esc_html( $heading ); ?></h2>
					<?php $render_copy( get_field( 'ltt_dive_in_content_module_newsletter_copy', false, false ) ); ?>
				</div>
				<div class="content-module__form content-module__form--newsletter" data-content-module-form>
					<?php gravity_form( $form_id, false, false, false, null, true, 0, true ); ?>
					<?php $render_form_disclosure( get_field( 'ltt_dive_in_content_module_form_disclosure', false, false ) ); ?>
				</div>
			</div>
			<?php if ( $has_newsletter_aside ) : ?>
				<div class="content-module__newsletter-aside">
					<?php if ( $newsletter_aside_heading ) : ?>
						<h3 class="content-module__newsletter-aside-heading"><?php echo esc_html( $newsletter_aside_heading ); ?></h3>
					<?php endif; ?>
					<?php $render_copy( $newsletter_aside_copy, 'content-module__newsletter-aside-copy' ); ?>
					<?php $render_cta( $newsletter_aside_cta, $bg_image ? 'glass' : 'primary-outline', 'content-module__newsletter-aside-cta' ); ?>
				</div>
			<?php endif; ?>
		</div>

	<?php elseif ( 'meetings_request' === $variant ) : ?>
		<div class="content-module__container content-module__lead-capture content-module__lead-capture--meetings">
			<div class="content-module__lead-copy">
				<h2 id="<?php echo esc_attr( $id . '-heading' ); ?>" class="content-module__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php $render_copy( get_field( 'ltt_dive_in_content_module_meetings_copy', false, false ) ); ?>
			</div>
			<div class="content-module__form content-module__form--meetings" data-content-module-form>
				<?php gravity_form( $form_id, false, false, false, null, true, 0, true ); ?>
				<?php $render_form_disclosure( get_field( 'ltt_dive_in_content_module_form_disclosure', false, false ) ); ?>
			</div>
		</div>

	<?php elseif ( 'stats' === $variant ) : ?>
		<div class="content-module__container content-module__stats">
			<header class="content-module__section-header">
				<h2 id="<?php echo esc_attr( $id . '-heading' ); ?>" class="content-module__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php $render_copy( get_field( 'ltt_dive_in_content_module_stats_intro', false, false ) ); ?>
			</header>
			<?php
			$stats = get_field( 'ltt_dive_in_content_module_stats' );
			$stats = is_array( $stats ) ? array_slice( $stats, 0, 4 ) : array();
			?>
			<?php if ( $stats ) : ?>
				<ul class="content-module__stats-list content-module__stats-list--<?php echo esc_attr( count( $stats ) ); ?>" role="list">
					<?php foreach ( $stats as $stat ) : ?>
						<?php
					$number = $get_text( $stat['number'] ?? '' );
					$label  = $get_text( $stat['label'] ?? '' );
					?>
						<?php if ( $number && $label ) : ?>
							<li class="content-module__stat">
								<p class="content-module__stat-number"><?php echo esc_html( $number ); ?></p>
								<p class="content-module__stat-label"><?php echo esc_html( $label ); ?></p>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

	<?php elseif ( 'comparison_table' === $variant ) : ?>
		<div class="content-module__container content-module__comparison">
			<header class="content-module__comparison-header">
				<h2 id="<?php echo esc_attr( $id . '-heading' ); ?>" class="content-module__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php $render_copy( get_field( 'ltt_dive_in_content_module_comparison_intro', false, false ) ); ?>
			</header>
			<?php
			$columns = get_field( 'ltt_dive_in_content_module_comparison_columns' );
			$rows    = get_field( 'ltt_dive_in_content_module_comparison_rows' );
			$feature_heading = $get_text( get_field( 'ltt_dive_in_content_module_comparison_feature_heading' ) );
			$columns = is_array( $columns ) ? array_slice( $columns, 0, 4 ) : array();
			$rows    = is_array( $rows ) ? array_slice( $rows, 0, 5 ) : array();
			$valid_columns = count( $columns ) >= 2 && count( $columns ) <= 4;

			foreach ( $columns as $column ) {
				if ( ! is_array( $column ) || '' === $get_text( $column['label'] ?? '' ) ) {
					$valid_columns = false;
					break;
				}
			}

			$rows = array_values(
				array_filter(
					$rows,
					static function ( $row ) use ( $get_text ) {
						return is_array( $row ) && '' !== $get_text( $row['label'] ?? '' );
					}
				)
			);
			?>
			<?php if ( $valid_columns && count( $rows ) >= 1 ) : ?>
				<div class="content-module__table-scroll<?php echo 4 === count( $columns ) ? ' content-module__table-scroll--columns-4' : ''; ?>"<?php echo 4 === count( $columns ) ? ' tabindex="0" role="region" aria-label="' . esc_attr__( 'Comparison table. Scroll horizontally to view all columns.', 'ltt-dive-in' ) . '"' : ''; ?>>
					<table class="content-module__table content-module__table--columns-<?php echo esc_attr( count( $columns ) ); ?>">
						<thead>
							<tr>
								<th class="content-module__feature-heading" scope="col">
									<?php if ( $feature_heading ) : ?>
										<span class="content-module__feature-label"><?php echo esc_html( $feature_heading ); ?></span>
									<?php else : ?>
										<span class="screen-reader-text"><?php esc_html_e( 'Features', 'ltt-dive-in' ); ?></span>
									<?php endif; ?>
								</th>
								<?php foreach ( $columns as $column ) : ?>
									<?php
									$column_label       = $get_text( $column['label'] ?? '' );
									$column_description = $get_text( $column['short_description'] ?? '' );
									$icon_id            = absint( $column['icon'] ?? 0 );
									$icon_uri           = $icon_id && 'image/svg+xml' === get_post_mime_type( $icon_id ) ? wp_get_attachment_url( $icon_id ) : '';
									?>
									<th class="content-module__column-heading" scope="col">
										<?php if ( $icon_uri ) : ?>
											<img class="content-module__comparison-icon" src="<?php echo esc_url( $icon_uri ); ?>" alt="" aria-hidden="true" />
										<?php endif; ?>
										<span><?php echo esc_html( $column_label ); ?></span>
										<?php if ( $column_description ) : ?>
											<span class="content-module__column-description"><?php echo esc_html( $column_description ); ?></span>
										<?php endif; ?>
									</th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $row ) : ?>
								<?php $row_label = $get_text( $row['label'] ?? '' ); ?>
								<tr>
									<th scope="row" class="content-module__row-heading"><?php echo esc_html( $row_label ); ?></th>
									<?php foreach ( array_slice( array( 'column_one', 'column_two', 'column_three', 'column_four' ), 0, count( $columns ) ) as $column_key ) : ?>
										<?php
										$cell = isset( $row[ $column_key ] ) && is_array( $row[ $column_key ] ) ? $row[ $column_key ] : array();
										$type = isset( $cell['type'] ) && in_array( $cell['type'], array( 'text', 'included', 'not_included' ), true ) ? $cell['type'] : 'text';
										?>
										<td class="content-module__cell">
											<?php if ( 'text' === $type ) : ?>
												<?php echo esc_html( $get_text( $cell['text'] ?? '' ) ); ?>
											<?php else : ?>
												<img class="content-module__cell-icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/content-module/' . ( 'included' === $type ? 'comparison-check.svg' : 'comparison-close.svg' ) ) ); ?>" alt="" aria-hidden="true" />
												<span class="screen-reader-text"><?php echo 'included' === $type ? esc_html__( 'Included', 'ltt-dive-in' ) : esc_html__( 'Not included', 'ltt-dive-in' ); ?></span>
											<?php endif; ?>
										</td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php elseif ( $is_preview ) : ?>
				<p><?php esc_html_e( 'Add at least two column labels and one comparison row to preview this table.', 'ltt-dive-in' ); ?></p>
			<?php endif; ?>
			<div class="content-module__comparison-actions">
				<?php $render_cta( get_field( 'ltt_dive_in_content_module_comparison_cta' ) ); ?>
			</div>
		</div>

	<?php endif; ?>
</section>
