<?php
/**
 * Story Module — Itinerary Day.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `itinerary_day` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields = $args['fields'] ?? array();
$id     = $args['id'] ?? wp_unique_id( 'story-module-' );
$number = isset( $fields['number'] ) && is_numeric( $fields['number'] ) ? absint( $fields['number'] ) : 0;
$title  = trim( (string) ( $fields['title'] ?? '' ) );

if ( ! $number || '' === $title ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add a day number and title to preview the Itinerary Day.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>">
	<h3 class="story-itinerary-day">
		<span class="story-itinerary-day__label">
			<span class="story-itinerary-day__word"><?php esc_html_e( 'Day', 'ltt-dive-in' ); ?></span>
			<span class="story-itinerary-day__number"><?php echo esc_html( number_format_i18n( $number ) ); ?></span>
		</span>
		<span class="screen-reader-text">: </span>
		<span class="story-itinerary-day__title"><?php echo esc_html( $title ); ?></span>
	</h3>
</div>
