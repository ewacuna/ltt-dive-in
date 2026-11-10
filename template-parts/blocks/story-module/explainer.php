<?php
/**
 * Story Module — Explainer Q&A.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `explainer` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields   = $args['fields'] ?? array();
$id       = $args['id'] ?? wp_unique_id( 'story-module-' );
$question = trim( (string) ( $fields['question'] ?? '' ) );
$answer   = trim( (string) ( $fields['answer'] ?? '' ) );

if ( '' === $question || '' === $answer ) {
	if ( ! empty( $args['is_preview'] ) ) {
		echo '<p>' . esc_html__( 'Add a question and answer to preview the Explainer Q&A.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-question">
	<div class="story-explainer">
		<h3 id="<?php echo esc_attr( $id ); ?>-question" class="story-explainer__question"><?php echo esc_html( $question ); ?></h3>
		<div class="story-explainer__answer"><?php echo wp_kses_post( $answer ); ?></div>
	</div>
</section>
