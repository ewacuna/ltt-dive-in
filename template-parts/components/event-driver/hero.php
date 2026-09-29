<?php
/**
 * Hero Featured Events (Figma nodes 1:11652 desktop, 1:11653 mobile).
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config          = $args['config'];
$section_id      = $args['section_id'];
$is_preview      = ! empty( $args['is_preview'] );
$preview_message = $args['preview_message'];
$events          = ltt_dive_in_get_event_driver_hero_events( $config );

if ( is_wp_error( $events ) || ! $events ) {
	$preview_message( is_wp_error( $events ) ? __( 'Events could not be loaded from Seeker. Check Settings → Events.', 'ltt-dive-in' ) : __( 'There are no upcoming events to feature. Flag events as featured in Seeker or choose events manually.', 'ltt-dive-in' ) );
	return;
}

$has_controls = count( $events ) > 1;
$classes      = array( 'event-driver', 'event-driver--hero', $args['class_name'] );

if ( $is_preview ) {
	$classes[] = 'event-driver--preview';
}

$link_tag = $is_preview ? 'span' : 'a';
$meta_icons = array(
	'date'      => 'event-calendar.svg',
	'venue'     => 'event-location.svg',
	'organizer' => 'event-organizer.svg',
);
?>
<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $section_id . '-title' ); ?>"<?php echo $has_controls && ! $is_preview ? ' data-event-driver-hero' : ''; ?>>
	<h2 id="<?php echo esc_attr( $section_id . '-title' ); ?>" class="screen-reader-text"><?php esc_html_e( 'Featured events', 'ltt-dive-in' ); ?></h2>
	<div class="event-driver__hero-viewport swiper" data-event-driver-viewport>
		<div class="event-driver__hero-track swiper-wrapper">
			<?php foreach ( $events as $index => $event ) : ?>
				<?php
				$tag  = ! empty( $event['categories'][0]['name'] ) ? $event['categories'][0]['name'] : '';
				$meta = array_filter(
					array(
						'date'      => ltt_dive_in_format_event_date( $event ),
						'venue'     => $event['venue'],
						'organizer' => $event['organizer'],
					)
				);
				$actions = array_filter(
					array(
						array(
							'label' => $config['primary_label'],
							'url'   => $event['ticket_url'],
							/* translators: %s: event name. */
							'name'  => sprintf( __( 'tickets for %s', 'ltt-dive-in' ), $event['title'] ),
						),
						array(
							'label' => $config['secondary_label'],
							'url'   => $event['url'],
							/* translators: %s: event name. */
							'name'  => sprintf( __( 'about %s', 'ltt-dive-in' ), $event['title'] ),
						),
					),
					static function ( $action ) {
						return $action['url'] && $action['label'];
					}
				);
				?>
				<article class="event-driver__hero-slide swiper-slide" aria-labelledby="<?php echo esc_attr( $section_id . '-event-' . ( $index + 1 ) ); ?>">
					<div class="event-driver__hero-media" aria-hidden="true">
						<?php echo ltt_dive_in_get_event_image( $event, '100vw', 0 === $index ? 'eager' : 'lazy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by ltt_dive_in_get_event_image(). ?>
					</div>
					<div class="event-driver__hero-content">
						<?php if ( $tag ) : ?>
							<p class="event-driver__tag"><?php echo esc_html( $tag ); ?></p>
						<?php endif; ?>
						<h3 id="<?php echo esc_attr( $section_id . '-event-' . ( $index + 1 ) ); ?>" class="event-driver__hero-title"><?php echo esc_html( $event['title'] ); ?></h3>
						<?php if ( $event['description'] ) : ?>
							<p class="event-driver__hero-description"><?php echo esc_html( ltt_dive_in_get_event_excerpt( $event, 16 ) ); ?></p>
						<?php endif; ?>
						<?php if ( $meta ) : ?>
							<ul class="event-driver__hero-meta">
								<?php foreach ( $meta as $key => $value ) : ?>
									<li class="event-driver__hero-meta-item">
										<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $meta_icons[ $key ] ) ); ?>" alt="" width="24" height="24" />
										<?php if ( 'date' === $key ) : ?>
											<span class="screen-reader-text"><?php esc_html_e( 'Date:', 'ltt-dive-in' ); ?></span>
											<time datetime="<?php echo esc_attr( ltt_dive_in_get_event_datetime_attribute( $event ) ); ?>"><?php echo esc_html( $value ); ?></time>
										<?php elseif ( 'venue' === $key ) : ?>
											<span class="screen-reader-text"><?php esc_html_e( 'Venue:', 'ltt-dive-in' ); ?></span>
											<span><?php echo esc_html( $value ); ?></span>
										<?php else : ?>
											<span class="screen-reader-text"><?php esc_html_e( 'Organizer:', 'ltt-dive-in' ); ?></span>
											<span><?php echo esc_html( $value ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( $actions ) : ?>
							<div class="event-driver__hero-actions">
								<?php foreach ( $actions as $action ) : ?>
									<<?php echo esc_attr( $link_tag ); ?> class="ltt-button ltt-button--glass"<?php if ( ! $is_preview ) : ?> href="<?php echo esc_url( $action['url'] ); ?>"<?php endif; ?>>
										<?php echo esc_html( $action['label'] ); ?><span class="screen-reader-text"><?php echo esc_html( ': ' . $action['name'] ); ?></span>
									</<?php echo esc_attr( $link_tag ); ?>>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<?php if ( $has_controls ) : ?>
		<div class="event-driver__hero-controls">
			<button class="event-driver__hero-control event-driver__hero-control--previous ltt-slider-navigation ltt-slider-navigation--image" type="button" data-event-driver-previous aria-label="<?php esc_attr_e( 'Previous featured event', 'ltt-dive-in' ); ?>"<?php echo $is_preview ? ' disabled' : ''; ?>>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-previous.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
			<div class="event-driver__hero-pagination ltt-carousel-indicators" data-event-driver-pagination>
				<?php foreach ( $events as $index => $event ) : ?>
					<span class="ltt-carousel-indicator<?php echo 0 === $index ? ' is-active' : ''; ?>" aria-hidden="true"></span>
				<?php endforeach; ?>
			</div>
			<button class="event-driver__hero-control event-driver__hero-control--next ltt-slider-navigation ltt-slider-navigation--image" type="button" data-event-driver-next aria-label="<?php esc_attr_e( 'Next featured event', 'ltt-dive-in' ); ?>"<?php echo $is_preview ? ' disabled' : ''; ?>>
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/carousel-arrow-next.svg' ) ); ?>" alt="" aria-hidden="true" />
			</button>
		</div>
	<?php endif; ?>
	<?php ltt_dive_in_print_event_schema( $events ); ?>
</section>
