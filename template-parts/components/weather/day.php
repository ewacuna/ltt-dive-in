<?php
/**
 * One day in a Current Conditions weather forecast list.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$day      = $args['day'];
$timezone = $args['timezone'];
$day_icon = ltt_dive_in_get_weather_icon( $day['condition_id'], $day['icon'] );
?>
<li class="current-weather__day">
	<p class="current-weather__day-name">
		<?php if ( ! empty( $args['today'] ) ) : ?>
			<?php esc_html_e( 'Today', 'ltt-dive-in' ); ?>
		<?php else : ?>
			<span aria-hidden="true"><?php echo esc_html( wp_date( 'D', $day['time'], $timezone ) ); ?></span>
			<span class="screen-reader-text"><?php echo esc_html( wp_date( 'l', $day['time'], $timezone ) ); ?></span>
		<?php endif; ?>
	</p>
	<span class="current-weather__day-icon">
		<?php if ( $day_icon ) : ?>
			<img<?php echo 'openweather' === $day_icon['source'] ? ' class="current-weather__openweather-icon"' : ''; ?> src="<?php echo esc_url( $day_icon['src'] ); ?>" alt="" width="<?php echo esc_attr( $day_icon['width'] ); ?>" height="<?php echo esc_attr( $day_icon['height'] ); ?>" loading="lazy">
		<?php endif; ?>
	</span>
	<p class="current-weather__day-temperatures">
		<span class="current-weather__high" aria-hidden="true"><?php echo esc_html( $day['high'] . '°' ); ?></span>
		<span class="current-weather__low" aria-hidden="true"><?php echo esc_html( $day['low'] . '°' ); ?></span>
		<span class="screen-reader-text">
			<?php
			/* translators: 1: weather description, 2: high temperature, 3: low temperature. */
			echo esc_html( sprintf( __( '%1$s, high %2$d°, low %3$d°', 'ltt-dive-in' ), $day['description'], $day['high'], $day['low'] ) );
			?>
		</span>
	</p>
</li>
