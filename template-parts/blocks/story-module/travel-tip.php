<?php
/**
 * Story Module — Responsible Travel Tip.
 *
 * Styles: assets/css/components/story-module/travel-tip.css.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `travel_tip` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields = $args['fields'] ?? array();
$id     = $args['id'] ?? wp_unique_id( 'story-module-' );
$label  = trim( (string) ( $fields['label'] ?? '' ) );
$title  = trim( (string) ( $fields['title'] ?? '' ) );
$body   = trim( (string) ( $fields['body'] ?? '' ) );

if ( '' === $label ) {
	$label = __( 'Responsible Travel Tip', 'ltt-dive-in' );
}

if ( '' === $title && '' === $body ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add a title and tip to preview the Responsible Travel Tip.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<aside id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $id . ( $title ? '-title' : '-label' ) ); ?>">
	<div class="story-travel-tip">
		<div class="story-travel-tip__header">
			<p id="<?php echo esc_attr( $id ); ?>-label" class="story-travel-tip__label">
				<img class="story-travel-tip__icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/story-module/travel-tip-icon.svg' ) ); ?>" width="40" height="39" alt="" aria-hidden="true">
				<span><?php echo esc_html( $label ); ?></span>
			</p>
			<?php if ( $title ) : ?>
				<h3 id="<?php echo esc_attr( $id ); ?>-title" class="story-travel-tip__title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
		</div>
		<?php if ( $body ) : ?>
			<div class="story-travel-tip__body"><?php echo wp_kses_post( $body ); ?></div>
		<?php endif; ?>
	</div>
</aside>
