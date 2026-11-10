<?php
/**
 * Story Module — Article Conclusion.
 *
 * Closing copy centered between two navy rules, with an optional CTA. It has
 * no heading: it closes the article rather than starting a new section.
 *
 * Figma: Blog Templates `464gNuDz4jnsCACmxyGie3`, closing “Copy Block” in
 * “Round-Up • Listicle: Fine Dining” (desktop `2991:82280`, mobile
 * `2991:83101`).
 * Styles: assets/css/components/story-module/conclusion.css.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `conclusion` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields     = $args['fields'] ?? array();
$id         = $args['id'] ?? wp_unique_id( 'story-module-' );
$is_preview = ! empty( $args['is_preview'] );
$body       = trim( (string) ( $fields['content'] ?? '' ) );
$cta        = $fields['cta'] ?? null;
$has_cta    = is_array( $cta ) && '' !== trim( (string) ( $cta['title'] ?? '' ) ) && ! empty( $cta['url'] ) && (bool) esc_url( $cta['url'] );

if ( '' === $body ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add content to preview the Article Conclusion.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>">
	<div class="story-conclusion">
		<div class="story-conclusion__body"><?php echo wp_kses_post( $body ); ?></div>
		<?php if ( $has_cta && $is_preview ) : ?>
			<span class="ltt-button ltt-button--primary-outline story-conclusion__cta"><?php echo esc_html( trim( $cta['title'] ) ); ?></span>
		<?php elseif ( $has_cta ) : ?>
			<a class="ltt-button ltt-button--primary-outline story-conclusion__cta" href="<?php echo esc_url( $cta['url'] ); ?>"<?php if ( '_blank' === ( $cta['target'] ?? '' ) ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo esc_html( trim( $cta['title'] ) ); ?></a>
		<?php endif; ?>
	</div>
</div>
