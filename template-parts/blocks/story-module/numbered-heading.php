<?php
/**
 * Story Module — Numbered Heading.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `numbered_heading` field group.
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
$intro  = trim( (string) ( $fields['intro'] ?? '' ) );

if ( '' === $title ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add a number and title to preview the Numbered Heading.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>">
	<div class="story-numbered-heading">
		<?php if ( $number ) : ?>
			<p class="story-numbered-heading__number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $number ) ); ?></p>
		<?php endif; ?>
		<div class="story-numbered-heading__text">
			<h2 class="story-numbered-heading__title">
				<?php if ( $number ) : ?>
					<span class="screen-reader-text"><?php echo esc_html( number_format_i18n( $number ) ); ?>. </span>
				<?php endif; ?>
				<?php echo esc_html( $title ); ?>
			</h2>
			<?php if ( $intro ) : ?>
				<p class="story-numbered-heading__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
