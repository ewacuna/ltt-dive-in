<?php
/**
 * Story Module — Comparison Table.
 *
 * A data table: named rows compared across one to seven columns, each value a
 * check, a cross, or short text. The table keeps its full size on small
 * screens and scrolls sideways inside a focusable region, with the row names
 * fixed at the left (designer's note on the mobile frame).
 *
 * Figma: Blog Templates `464gNuDz4jnsCACmxyGie3`, “Comparison Table” in
 * “Round-Up • Listicle: Fine Dining” (desktop `2991:82159`, mobile
 * `2991:83009`).
 * Styles: assets/css/components/story-module/comparison-table.css.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `comparison_table` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields      = $args['fields'] ?? array();
$id          = $args['id'] ?? wp_unique_id( 'story-module-' );
$caption     = trim( (string) ( $fields['caption'] ?? '' ) );
$row_heading = trim( (string) ( $fields['row_heading'] ?? '' ) );
$columns     = array();
$rows        = array();

foreach ( array_slice( (array) ( $fields['columns'] ?? array() ), 0, 7 ) as $column ) {
	$heading = is_array( $column ) ? trim( (string) ( $column['heading'] ?? '' ) ) : '';

	if ( '' !== $heading ) {
		$columns[] = $heading;
	}
}

foreach ( (array) ( $fields['rows'] ?? array() ) as $row ) {
	$name = is_array( $row ) ? trim( (string) ( $row['name'] ?? '' ) ) : '';

	if ( '' !== $name ) {
		$rows[] = array(
			'name'  => $name,
			'cells' => array_values( (array) ( $row['cells'] ?? array() ) ),
		);
	}
}

if ( ! $columns || ! $rows ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add at least one column and one row to preview the Comparison Table.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}

if ( '' === $caption ) {
	$caption = __( 'Comparison table', 'ltt-dive-in' );
}

$icons = array(
	'yes' => array(
		'file'  => 'comparison-check.svg',
		'size'  => 24,
		'label' => __( 'Yes', 'ltt-dive-in' ),
	),
	'no'  => array(
		'file'  => 'comparison-cross.svg',
		'size'  => 13,
		'label' => __( 'No', 'ltt-dive-in' ),
	),
);
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>">
	<div class="story-comparison" role="region" aria-labelledby="<?php echo esc_attr( $id ); ?>-caption" tabindex="0">
		<table class="story-comparison__table story-comparison__table--columns-<?php echo esc_attr( (string) count( $columns ) ); ?>">
			<caption id="<?php echo esc_attr( $id ); ?>-caption" class="screen-reader-text"><?php echo esc_html( $caption ); ?></caption>
			<thead>
				<tr>
					<th scope="col" class="story-comparison__corner"><?php echo esc_html( $row_heading ); ?></th>
					<?php foreach ( $columns as $heading ) : ?>
						<th scope="col"><?php echo esc_html( $heading ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $row['name'] ); ?></th>
						<?php foreach ( array_keys( $columns ) as $index ) : ?>
							<?php
							$cell  = is_array( $row['cells'][ $index ] ?? null ) ? $row['cells'][ $index ] : array();
							$value = (string) ( $cell['value'] ?? '' );
							$text  = trim( (string) ( $cell['text'] ?? '' ) );
							?>
							<td>
								<?php if ( isset( $icons[ $value ] ) ) : ?>
									<img class="story-comparison__icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/story-module/' . $icons[ $value ]['file'] ) ); ?>" width="<?php echo esc_attr( (string) $icons[ $value ]['size'] ); ?>" height="<?php echo esc_attr( (string) $icons[ $value ]['size'] ); ?>" alt="<?php echo esc_attr( $icons[ $value ]['label'] ); ?>">
								<?php elseif ( 'text' === $value && '' !== $text ) : ?>
									<?php echo esc_html( $text ); ?>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
