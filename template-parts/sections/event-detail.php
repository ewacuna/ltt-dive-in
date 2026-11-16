<?php
/**
 * Event detail: gallery, information, upcoming dates, and related events.
 *
 * Expected arguments:
 * - event: Normalized Seeker event.
 * - details: Event details from ltt_dive_in_get_seeker_event_details().
 * - page_url: Events page URL.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$event    = $args['event'];
$details  = $args['details'];
$page_url = (string) $args['page_url'];
$schedule = ltt_dive_in_get_event_schedule_labels( $event );
$tag      = ! empty( $event['categories'][0]['name'] ) ? $event['categories'][0]['name'] : '';
$address  = $details['address'] ? $details['address'] : $event['venue'];
$flags    = array_filter(
	array(
		'featured'  => ! empty( $event['featured'] ) ? array( __( 'Featured', 'ltt-dive-in' ), 'event-featured.svg', 26 ) : null,
		'recurring' => ! empty( $event['recurring'] ) ? array( __( 'Recurring', 'ltt-dive-in' ), 'event-recurring.svg', 32 ) : null,
	)
);
$facts    = array_filter(
	array(
		__( 'Admission:', 'ltt-dive-in' ) => $event['free'] ? __( 'Free', 'ltt-dive-in' ) : $event['price'],
		__( 'Ages:', 'ltt-dive-in' )      => $details['age'],
		__( 'Format:', 'ltt-dive-in' )    => 'online' === $event['mode'] ? __( 'Online', 'ltt-dive-in' ) : ( 'hybrid' === $event['mode'] ? __( 'In person and online', 'ltt-dive-in' ) : '' ),
	)
);
$actions  = array();

foreach ( $details['tickets'] as $ticket ) {
	$actions[] = array(
		'label'   => 1 === count( $details['tickets'] ) || '' === $ticket['name'] || 'ticket' === strtolower( $ticket['name'] ) ? __( 'Get Tickets', 'ltt-dive-in' ) : $ticket['name'],
		'url'     => $ticket['url'],
		'style'   => 'primary-outline',
		'icon'    => 'event-ticket.svg',
	);
}

if ( $details['online_url'] ) {
	$actions[] = array(
		'label' => __( 'Join Online', 'ltt-dive-in' ),
		'url'   => $details['online_url'],
		'style' => 'primary-outline',
		'icon'  => '',
	);
}

if ( $details['website'] ) {
	$actions[] = array(
		'label' => __( 'Visit Website', 'ltt-dive-in' ),
		'url'   => $details['website'],
		'style' => 'secondary-outline',
		'icon'  => '',
	);
}
?>
<article class="event-detail" aria-labelledby="event-detail-title">
	<div class="event-detail__container">
		<a class="event-detail__back" href="<?php echo esc_url( $page_url ); ?>">
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-back.svg' ) ); ?>" alt="" width="24" height="24" />
			<?php esc_html_e( 'Return to All Events', 'ltt-dive-in' ); ?>
		</a>

		<div class="event-detail__main">
			<?php
			get_template_part(
				'template-parts/components/event-detail/gallery',
				null,
				array(
					'images' => $details['images'],
					'title'  => $event['title'],
				)
			);
			?>

			<div class="event-detail__info">
				<?php if ( $tag ) : ?>
					<p class="event-detail__tag ltt-tag"><?php echo esc_html( $tag ); ?></p>
				<?php endif; ?>

				<h1 id="event-detail-title" class="event-detail__title"><?php echo esc_html( $event['title'] ); ?></h1>

				<?php if ( 'postponed' === $event['status'] || 'rescheduled' === $event['status'] ) : ?>
					<p class="event-detail__status"><?php echo esc_html( 'postponed' === $event['status'] ? __( 'Postponed', 'ltt-dive-in' ) : __( 'Rescheduled', 'ltt-dive-in' ) ); ?></p>
				<?php endif; ?>

				<?php if ( $flags ) : ?>
					<ul class="ltt-event-card__flags event-detail__flags" aria-label="<?php esc_attr_e( 'Event highlights', 'ltt-dive-in' ); ?>">
						<?php foreach ( $flags as $flag_key => $flag ) : ?>
							<li class="ltt-event-card__flag ltt-event-card__flag--<?php echo esc_attr( $flag_key ); ?>">
								<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $flag[1] ) ); ?>" alt="" width="<?php echo esc_attr( (string) $flag[2] ); ?>" height="24" />
								<?php echo esc_html( $flag[0] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<dl class="event-detail__schedule">
					<div>
						<dt><?php esc_html_e( 'Date:', 'ltt-dive-in' ); ?></dt>
						<dd><time datetime="<?php echo esc_attr( ltt_dive_in_get_event_datetime_attribute( $event ) ); ?>"><?php echo esc_html( $schedule['dates'] ); ?></time></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Time:', 'ltt-dive-in' ); ?></dt>
						<dd><?php echo esc_html( $schedule['time'] ); ?></dd>
					</div>
					<?php foreach ( $facts as $fact_label => $fact_value ) : ?>
						<div>
							<dt><?php echo esc_html( $fact_label ); ?></dt>
							<dd><?php echo esc_html( $fact_value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<?php if ( $address ) : ?>
					<p class="event-detail__address">
						<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-address-navy.svg' ) ); ?>" alt="" width="24" height="35" />
						<span>
							<?php if ( $event['venue'] && $details['address'] ) : ?>
								<span class="event-detail__venue"><?php echo esc_html( $event['venue'] ); ?></span>
							<?php endif; ?>
							<?php echo esc_html( $address ); ?>
							<?php if ( $details['directions_url'] ) : ?>
								<a class="event-detail__directions" href="<?php echo esc_url( $details['directions_url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Get Directions', 'ltt-dive-in' ); ?>
									<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?></span>
								</a>
							<?php endif; ?>
						</span>
					</p>
				<?php endif; ?>

				<a class="event-detail__calendar" href="<?php echo esc_url( ltt_dive_in_get_event_reminder_url( $event ) ); ?>" download>
					<?php esc_html_e( 'Add to Calendar', 'ltt-dive-in' ); ?>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/event-add-to-calendar-navy.svg' ) ); ?>" alt="" width="30" height="24" />
				</a>

				<?php if ( $event['organizer'] ) : ?>
					<p class="event-detail__organizer">
						<?php esc_html_e( 'Presented by:', 'ltt-dive-in' ); ?>
						<?php if ( $details['organizer_url'] ) : ?>
							<a href="<?php echo esc_url( $details['organizer_url'] ); ?>"><?php echo esc_html( $event['organizer'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $event['organizer'] ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( '' !== trim( wp_strip_all_tags( $details['description_html'] ) ) ) : ?>
					<div class="event-detail__description" id="event-detail-description" data-event-description>
						<?php echo wpautop( $details['description_html'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized with wp_kses() in ltt_dive_in_get_seeker_event_details(). ?>
					</div>
					<button class="event-detail__more" type="button" aria-controls="event-detail-description" aria-expanded="false" data-event-description-toggle data-label-more="<?php esc_attr_e( 'Read More', 'ltt-dive-in' ); ?>" data-label-less="<?php esc_attr_e( 'Read Less', 'ltt-dive-in' ); ?>" hidden><?php esc_html_e( 'Read More', 'ltt-dive-in' ); ?></button>
				<?php endif; ?>

				<?php if ( $actions ) : ?>
					<div class="event-detail__actions">
						<?php foreach ( $actions as $action ) : ?>
							<a class="ltt-button ltt-button--<?php echo esc_attr( $action['style'] ); ?> event-detail__action" href="<?php echo esc_url( $action['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $action['label'] ); ?>
								<?php if ( $action['icon'] ) : ?>
									<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/icons/' . $action['icon'] ) ); ?>" alt="" width="21" height="19" />
								<?php endif; ?>
								<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php get_template_part( 'template-parts/components/event-detail/upcoming-dates', null, array( 'occurrences' => $details['occurrences'] ) ); ?>
	</div>

	<?php
	get_template_part(
		'template-parts/components/event-driver/carousel',
		null,
		array(
			'config'          => ltt_dive_in_get_event_detail_related_config( $event ),
			'section_id'      => 'event-detail-related',
			'class_name'      => 'event-detail__related',
			'is_preview'      => false,
			'preview_message' => '__return_null',
		)
	);
	?>
	<?php ltt_dive_in_print_event_schema( array( $event ) ); ?>
</article>
