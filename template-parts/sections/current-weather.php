<?php
/**
 * Current Conditions weather module: the selected location's panel and the city tiles.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$locations = $args['locations'] ?? array();
$active    = (string) ( $args['active'] ?? '' );

if ( ! $locations || ! isset( $locations[ $active ] ) ) {
	return;
}

$permalink = get_permalink();
$reports   = array();

foreach ( $locations as $slug => $location ) {
	$reports[ $slug ] = ltt_dive_in_get_weather_report( $location['latitude'], $location['longitude'], false, $slug === $active );
}
?>
<section id="current-weather" class="current-weather" aria-label="<?php esc_attr_e( 'Current weather', 'ltt-dive-in' ); ?>" data-current-weather>
	<svg class="current-weather__filters" aria-hidden="true" focusable="false" width="0" height="0">
		<filter id="current-weather-navy" color-interpolation-filters="sRGB">
			<feFlood flood-color="#073959" />
			<feComposite in2="SourceAlpha" operator="in" />
		</filter>
	</svg>

	<?php
	foreach ( $locations as $slug => $location ) {
		get_template_part(
			'template-parts/components/weather/panel',
			null,
			array(
				'id'       => 'current-weather-' . $slug,
				'location' => $location,
				'report'   => $reports[ $slug ],
				'active'   => $slug === $active,
			)
		);
	}
	?>

	<p class="screen-reader-text" role="status" data-current-weather-status data-message="<?php /* translators: %s: weather location name. */ echo esc_attr__( 'Showing weather for %s.', 'ltt-dive-in' ); ?>"></p>

	<?php if ( count( $locations ) > 1 ) : ?>
		<ul class="current-weather__locations" aria-label="<?php esc_attr_e( 'Weather locations', 'ltt-dive-in' ); ?>">
			<?php foreach ( $locations as $slug => $location ) : ?>
				<li>
					<?php
					get_template_part(
						'template-parts/components/weather/tile',
						null,
						array(
							'location' => $location,
							'report'   => $reports[ $slug ],
							'active'   => $slug === $active,
							'panel_id' => 'current-weather-' . $slug,
							'url'      => add_query_arg( 'location', $slug, $permalink ) . '#current-weather',
						)
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
