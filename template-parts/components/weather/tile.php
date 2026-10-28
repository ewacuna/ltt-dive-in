<?php
/**
 * City tile that selects a location's panel in the Current Conditions weather module.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$location = $args['location'];
$report   = $args['report'];
$current  = is_wp_error( $report ) ? null : $report['current'];
$icon     = $current ? ltt_dive_in_get_weather_icon( $current['condition_id'], $current['icon'] ) : array();
$selected = $current ? ltt_dive_in_get_weather_icon( $current['condition_id'], $current['icon'], 'light-mode' ) : array();
?>
<a
	class="current-weather-tile<?php echo $args['active'] ? ' is-active' : ''; ?>"
	href="<?php echo esc_url( $args['url'] ); ?>"
	aria-controls="<?php echo esc_attr( $args['panel_id'] ); ?>"
	<?php echo $args['active'] ? 'aria-current="true"' : ''; ?>
	data-weather-location="<?php echo esc_attr( $location['slug'] ); ?>"
	data-weather-name="<?php echo esc_attr( $location['name'] ); ?>"
>
	<span class="current-weather-tile__name"><?php echo esc_html( $location['name'] ); ?></span>

	<?php if ( $current ) : ?>
		<span class="current-weather-tile__reading">
			<span class="current-weather-tile__icon">
				<?php if ( $icon && 'figma' === $icon['source'] ) : ?>
					<img class="current-weather-tile__icon-default" src="<?php echo esc_url( $icon['src'] ); ?>" alt="" width="<?php echo esc_attr( $icon['width'] ); ?>" height="<?php echo esc_attr( $icon['height'] ); ?>" loading="lazy">
					<img class="current-weather-tile__icon-selected" src="<?php echo esc_url( $selected['src'] ); ?>" alt="" width="<?php echo esc_attr( $selected['width'] ); ?>" height="<?php echo esc_attr( $selected['height'] ); ?>" loading="lazy">
				<?php elseif ( $icon ) : ?>
					<img class="current-weather__openweather-icon" src="<?php echo esc_url( $icon['src'] ); ?>" alt="" width="<?php echo esc_attr( $icon['width'] ); ?>" height="<?php echo esc_attr( $icon['height'] ); ?>" loading="lazy">
				<?php endif; ?>
			</span>
			<span class="current-weather-tile__temperature" aria-hidden="true"><?php echo esc_html( $current['temperature'] . '°' ); ?></span>
			<span class="screen-reader-text">
				<?php
				/* translators: %d: temperature in degrees Fahrenheit. */
				echo esc_html( sprintf( __( '%d degrees Fahrenheit', 'ltt-dive-in' ), $current['temperature'] ) );
				?>
			</span>
		</span>
		<?php if ( $current['description'] ) : ?>
			<span class="current-weather-tile__condition"><?php echo esc_html( $current['description'] ); ?></span>
		<?php endif; ?>
	<?php else : ?>
		<span class="current-weather-tile__condition"><?php esc_html_e( 'Weather unavailable', 'ltt-dive-in' ); ?></span>
	<?php endif; ?>

	<?php if ( null !== $location['elevation'] ) : ?>
		<span class="current-weather-tile__elevation">
			<?php
			/* translators: %s: elevation in feet. */
			echo esc_html( sprintf( __( '%s ft', 'ltt-dive-in' ), number_format_i18n( $location['elevation'] ) ) );
			?>
		</span>
	<?php endif; ?>
</a>
