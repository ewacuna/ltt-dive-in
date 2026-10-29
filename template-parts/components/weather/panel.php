<?php
/**
 * One location's panel in the Current Conditions weather module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$location = (string) ( $args['location']['name'] ?? '' );
$report   = $args['report'] ?? new WP_Error( 'ltt_dive_in_weather_report_missing' );
$view     = (string) ( $args['view'] ?? 'current' );
$views    = array(
	'current'  => __( 'Current Weather', 'ltt-dive-in' ),
	'forecast' => __( '10-Day Forecast', 'ltt-dive-in' ),
	'history'  => __( 'Historical Data', 'ltt-dive-in' ),
);
$view     = isset( $views[ $view ] ) ? $view : 'current';
?>
<div id="<?php echo esc_attr( $args['id'] ); ?>" class="current-weather__panel" data-weather-panel<?php echo is_wp_error( $report ) ? ' data-weather-unavailable' : ''; ?><?php echo empty( $args['active'] ) ? ' hidden' : ''; ?>>
	<h2 class="screen-reader-text">
		<?php
		/* translators: %s: weather location name. */
		echo esc_html( sprintf( __( 'Current weather in %s', 'ltt-dive-in' ), $location ) );
		?>
	</h2>

	<?php if ( is_wp_error( $report ) ) : ?>
		<p class="current-weather__message"><?php esc_html_e( 'Current weather is unavailable right now. Please check back soon.', 'ltt-dive-in' ); ?></p>
	<?php else : ?>
		<?php
		$timezone = new DateTimeZone( $report['timezone'] );
		$current  = $report['current'];
		$icon     = ltt_dive_in_get_weather_icon( $current['condition_id'], $current['icon'] );
		$wind     = '';

		if ( null !== $current['wind_speed'] ) {
			/* translators: %d: wind speed in miles per hour. */
			$wind = sprintf( __( '%d mph', 'ltt-dive-in' ), $current['wind_speed'] );

			if ( null !== $current['wind_from'] && $current['wind_speed'] > 0 ) {
				$wind .= ' ' . ltt_dive_in_get_wind_direction( $current['wind_from'] );
			}
			if ( null !== $current['wind_gust'] && $current['wind_gust'] > $current['wind_speed'] ) {
				/* translators: %d: wind gust speed in miles per hour. */
				$wind .= sprintf( __( ', gusts %d', 'ltt-dive-in' ), $current['wind_gust'] );
			}
		}

		$details = array(
			__( 'Feels like', 'ltt-dive-in' ) => null !== $current['feels_like'] ? $current['feels_like'] . '°' : '',
			__( 'Humidity', 'ltt-dive-in' )   => null !== $current['humidity'] ? $current['humidity'] . '%' : '',
			__( 'Wind', 'ltt-dive-in' )       => $wind,
			/* translators: %s: visibility in miles. */
			__( 'Visibility', 'ltt-dive-in' ) => null !== $current['visibility'] ? sprintf( __( '%s mi', 'ltt-dive-in' ), number_format_i18n( $current['visibility'] ) ) : '',
			__( 'UV index', 'ltt-dive-in' )   => null !== $current['uv_index'] ? (string) $current['uv_index'] : '',
			/* translators: %s: barometric pressure in inches of mercury. */
			__( 'Pressure', 'ltt-dive-in' )   => null !== $current['pressure'] ? sprintf( __( '%s in', 'ltt-dive-in' ), number_format_i18n( $current['pressure'], 1 ) ) : '',
			__( 'Sunrise', 'ltt-dive-in' )    => null !== $current['sunrise'] ? wp_date( 'g:i A', $current['sunrise'], $timezone ) : '',
			__( 'Sunset', 'ltt-dive-in' )     => null !== $current['sunset'] ? wp_date( 'g:i A', $current['sunset'], $timezone ) : '',
		);
		?>
		<div id="<?php echo esc_attr( $args['id'] . '-current' ); ?>" class="current-weather__view" data-weather-view="current"<?php echo 'current' === $view ? '' : ' hidden'; ?>>
			<div class="current-weather__summary">
				<div class="current-weather__now">
					<div class="current-weather__place">
						<?php if ( $icon ) : ?>
							<img class="current-weather__icon<?php echo 'openweather' === $icon['source'] ? ' current-weather__openweather-icon' : ''; ?>" src="<?php echo esc_url( $icon['src'] ); ?>" alt="" width="<?php echo esc_attr( $icon['width'] ); ?>" height="<?php echo esc_attr( $icon['height'] ); ?>">
						<?php endif; ?>
						<p class="current-weather__location"><?php echo esc_html( $location ); ?></p>
					</div>

					<div class="current-weather__reading">
						<p class="current-weather__temperature">
							<span aria-hidden="true"><?php echo esc_html( $current['temperature'] . '°' ); ?></span>
							<span class="screen-reader-text">
								<?php
								/* translators: %d: temperature in degrees Fahrenheit. */
								echo esc_html( sprintf( __( '%d degrees Fahrenheit', 'ltt-dive-in' ), $current['temperature'] ) );
								?>
							</span>
						</p>
						<?php if ( $current['description'] ) : ?>
							<p class="current-weather__condition"><?php echo esc_html( $current['description'] ); ?></p>
						<?php endif; ?>
						<p class="current-weather__range">
							<?php
							/* translators: %d: today's high temperature. */
							echo '<span>' . esc_html( sprintf( __( 'high %d°', 'ltt-dive-in' ), $current['high'] ) ) . '</span> | <span>';
							/* translators: %d: today's low temperature. */
							echo esc_html( sprintf( __( 'low %d°', 'ltt-dive-in' ), $current['low'] ) ) . '</span>';
							?>
						</p>
					</div>
				</div>

				<dl class="current-weather__details">
					<?php foreach ( $details as $label => $value ) : ?>
						<div class="current-weather__detail">
							<dt><?php echo esc_html( $label ); ?></dt>
							<dd>
								<?php if ( '' === $value ) : ?>
									<span aria-hidden="true">—</span>
									<span class="screen-reader-text"><?php esc_html_e( 'Not available', 'ltt-dive-in' ); ?></span>
								<?php else : ?>
									<?php echo esc_html( $value ); ?>
								<?php endif; ?>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>

			<?php if ( $report['daily'] ) : ?>
				<h3 class="screen-reader-text">
					<?php
					/* translators: %d: number of forecast days. */
					echo esc_html( sprintf( _n( '%d-day forecast', '%d-day forecast', count( $report['daily'] ), 'ltt-dive-in' ), count( $report['daily'] ) ) );
					?>
				</h3>
				<ol class="current-weather__forecast">
					<?php foreach ( $report['daily'] as $day ) : ?>
						<?php
						get_template_part(
							'template-parts/components/weather/day',
							null,
							array(
								'day'      => $day,
								'timezone' => $timezone,
							)
						);
						?>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>

		<div id="<?php echo esc_attr( $args['id'] . '-forecast' ); ?>" class="current-weather__view" data-weather-view="forecast"<?php echo 'forecast' === $view ? '' : ' hidden'; ?>>
			<h3 class="screen-reader-text">
				<?php
				/* translators: %s: weather location name. */
				echo esc_html( sprintf( __( '10-day forecast for %s', 'ltt-dive-in' ), $location ) );
				?>
			</h3>
			<?php if ( ! empty( $report['forecast'] ) ) : ?>
				<ol class="current-weather__forecast current-weather__forecast--extended">
					<?php foreach ( $report['forecast'] as $index => $day ) : ?>
						<?php
						get_template_part(
							'template-parts/components/weather/day',
							null,
							array(
								'day'      => $day,
								'timezone' => $timezone,
								'today'    => 0 === $index,
							)
						);
						?>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="current-weather__message"><?php esc_html_e( 'The 10-day forecast is unavailable right now. Please check back soon.', 'ltt-dive-in' ); ?></p>
			<?php endif; ?>
		</div>

		<div id="<?php echo esc_attr( $args['id'] . '-history' ); ?>" class="current-weather__view" data-weather-view="history"<?php echo 'history' === $view ? '' : ' hidden'; ?>>
			<?php
			get_template_part(
				'template-parts/components/weather/history',
				null,
				array(
					'id'       => $args['id'] . '-history',
					'location' => $location,
					'history'  => ltt_dive_in_get_weather_history( $args['location']['latitude'], $args['location']['longitude'], $report['timezone'] ),
				)
			);
			?>
		</div>

		<ul class="current-weather__views" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: weather location name. */ __( 'Weather views for %s', 'ltt-dive-in' ), $location ) ); ?>">
			<?php foreach ( $views as $key => $label ) : ?>
				<li>
					<a
						class="ltt-button ltt-button--dark-standard"
						href="<?php echo esc_url( ( 'current' === $key ? $args['url'] : add_query_arg( 'weather_view', $key, $args['url'] ) ) . '#current-weather' ); ?>"
						aria-controls="<?php echo esc_attr( $args['id'] . '-' . $key ); ?>"
						data-weather-view-link="<?php echo esc_attr( $key ); ?>"
						<?php echo $key === $view ? 'aria-current="true"' : ''; ?>
					><?php echo esc_html( $label ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="screen-reader-text">
			<?php
			/* translators: %s: time the weather was observed. */
			echo esc_html( sprintf( __( 'Updated %s.', 'ltt-dive-in' ), wp_date( 'g:i A', $report['observed_at'], $timezone ) ) );
			?>
		</p>
	<?php endif; ?>
</div>
