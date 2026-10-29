<?php
/**
 * Historical Data view of a Current Conditions weather panel.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$history = $args['history'];

$tables = array(
	'seasons' => array(
		'title'  => __( 'Past Seasons', 'ltt-dive-in' ),
		'text'   => __( 'Check out historical information from the last few seasons:', 'ltt-dive-in' ),
		'header' => __( 'Season', 'ltt-dive-in' ),
	),
	'dates'   => array(
		'title'  => __( 'This date in past years', 'ltt-dive-in' ),
		'text'   => __( 'Check out historical information from the last few years:', 'ltt-dive-in' ),
		'header' => __( 'Year', 'ltt-dive-in' ),
	),
);
$columns = array(
	'high'          => __( 'High', 'ltt-dive-in' ),
	'low'           => __( 'Low', 'ltt-dive-in' ),
	'precipitation' => __( 'Precipitation', 'ltt-dive-in' ),
	'wind'          => __( 'Wind Speed', 'ltt-dive-in' ),
	'snow'          => __( 'Snowfall', 'ltt-dive-in' ),
);

$has_data = false;

foreach ( $tables as $key => $table ) :
	$title_id = $args['id'] . '-' . $key . '-title';
	$text_id  = $args['id'] . '-' . $key . '-text';
	$ready    = array_filter(
		$history[ $key ],
		static function ( $row ) {
			return ! $row['pending'];
		}
	);
	$has_data = $has_data || $ready;
	?>
	<div class="current-weather__history">
		<h3 id="<?php echo esc_attr( $title_id ); ?>" class="current-weather__history-title"><?php echo esc_html( $table['title'] ); ?></h3>
		<p id="<?php echo esc_attr( $text_id ); ?>" class="current-weather__history-text"><?php echo esc_html( $table['text'] ); ?></p>
		<?php if ( ! $ready ) : ?>
			<p class="current-weather__history-pending"><?php esc_html_e( 'This information is still being collected. Please check back soon.', 'ltt-dive-in' ); ?></p>
		<?php else : ?>
			<div class="current-weather__table-wrap">
				<table class="current-weather__table" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" aria-describedby="<?php echo esc_attr( $text_id ); ?>">
					<thead>
						<tr>
							<th scope="col"><?php echo esc_html( $table['header'] ); ?></th>
							<?php foreach ( $columns as $label ) : ?>
								<th scope="col"><?php echo esc_html( $label ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $history[ $key ] as $row ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
								<?php if ( $row['pending'] ) : ?>
									<td colspan="<?php echo esc_attr( count( $columns ) ); ?>"><?php esc_html_e( 'Still being collected', 'ltt-dive-in' ); ?></td>
								<?php else : ?>
									<?php foreach ( array_keys( $columns ) as $column ) : ?>
										<td>
											<?php
											$value = $row[ $column ];

											if ( null === $value ) {
												echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'Not available', 'ltt-dive-in' ) . '</span>';
											} elseif ( 'wind' === $column ) {
												/* translators: %d: wind speed in miles per hour. */
												echo esc_html( sprintf( __( '%d mph', 'ltt-dive-in' ), $value ) );
											} elseif ( in_array( $column, array( 'precipitation', 'snow' ), true ) && $value > 0 && $value < 0.05 ) {
												echo esc_html__( '< 0.1 in', 'ltt-dive-in' );
											} elseif ( in_array( $column, array( 'precipitation', 'snow' ), true ) ) {
												/* translators: %s: amount in inches. */
												echo esc_html( sprintf( __( '%s in', 'ltt-dive-in' ), number_format_i18n( $value, $value > 0 ? 1 : 0 ) ) );
											} else {
												echo esc_html( $value . '°' );
											}
											?>
										</td>
									<?php endforeach; ?>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
<?php endforeach; ?>

<?php if ( $has_data ) : ?>
	<p class="current-weather__history-note">
		<?php
		/* translators: 1: first archive year, 2: last archive year. */
		echo esc_html( sprintf( __( 'Seasons average %1$d–%2$d, with precipitation and snowfall as season totals. Precipitation and snowfall are measured as liquid water.', 'ltt-dive-in' ), $history['first_year'], $history['last_year'] ) );
		?>
	</p>
<?php endif; ?>
