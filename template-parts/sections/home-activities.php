<?php
/**
 * Homepage activity driver.
 *
 * @package LTT_Dive_In
 */

$front_page_id = (int) get_queried_object_id();
$section_title = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_activities_heading', $front_page_id ) : '';
$section_copy  = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_activities_copy', $front_page_id ) : '';
$section_tag   = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_activities_tag', $front_page_id ) : '';
$primary_link   = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_activities_primary_link', $front_page_id ) : array();
$secondary_link = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_activities_secondary_link', $front_page_id ) : array();
$activities     = function_exists( 'get_field' ) ? get_field( 'ltt_dive_in_activities_cards', $front_page_id ) : array();

$section_links = array_filter(
	array( $primary_link, $secondary_link ),
	static function ( $link ) {
		return is_array( $link ) && ! empty( $link['title'] ) && ! empty( $link['url'] );
	}
);


if ( ! $section_title || ! $section_copy || ! is_array( $activities ) || 4 !== count( $activities ) ) {
	return;
}

foreach ( $activities as $activity ) {
	if ( empty( $activity['title'] ) || empty( $activity['image'] ) ) {
		return;
	}
}
?>
<section class="home-activities" aria-labelledby="home-activities-title">
	<div class="home-activities__container">
		<header class="home-activities__header">
			<div class="home-activities__intro">
				<?php if ( $section_tag ) : ?>
					<p class="home-activities__tag"><?php echo esc_html( $section_tag ); ?></p>
				<?php endif; ?>
				<h2 id="home-activities-title" class="home-activities__title"><?php echo esc_html( $section_title ); ?></h2>
				<p class="home-activities__copy"><?php echo esc_html( $section_copy ); ?></p>
			</div>
			<?php if ( ! empty( $section_links ) ) : ?>
				<div class="home-activities__actions">
					<?php foreach ( $section_links as $index => $section_link ) : ?>
						<a class="ltt-button ltt-button--<?php echo esc_attr( 0 === $index ? 'primary-outline' : 'secondary-outline' ); ?>" href="<?php echo esc_url( $section_link['url'] ); ?>"<?php echo ! empty( $section_link['target'] ) ? ' target="' . esc_attr( $section_link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $section_link['title'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</header>
	</div>
	<div class="home-activities__grid">
		<?php foreach ( array_slice( $activities, 0, 4 ) as $index => $activity ) : ?>
			<?php
			$title       = ! empty( $activity['title'] ) ? $activity['title'] : '';
			$description = ! empty( $activity['description'] ) ? $activity['description'] : '';
			$link        = ! empty( $activity['link'] ) && is_array( $activity['link'] ) ? $activity['link'] : array();
			$image_id    = (int) $activity['image'];
			?>
			<article class="home-activities__card">
				<div class="home-activities__media" aria-hidden="true">
					<?php
					echo wp_get_attachment_image( $image_id, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) );
					?>
				</div>
				<div class="home-activities__card-content">
					<?php if ( $title ) : ?>
						<h3 class="home-activities__card-title"><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>
					<?php if ( $description ) : ?>
						<p class="home-activities__card-copy"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $link['url'] ) && ! empty( $link['title'] ) ) : ?>
						<a class="home-activities__card-link ltt-button ltt-button--glass" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo ! empty( $link['target'] ) ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $link['title'] ); ?></a>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
