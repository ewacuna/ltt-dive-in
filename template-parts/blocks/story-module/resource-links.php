<?php
/**
 * Story Module — Resource Links.
 *
 * @package LTT_Dive_In
 *
 * @var array $args {
 *     @type string   $id         Section ID.
 *     @type string[] $classes    Root classes from the Story Module renderer.
 *     @type array    $fields     Values of the `resource_links` field group.
 *     @type bool     $is_preview Whether the editor is rendering a preview.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fields     = $args['fields'] ?? array();
$id         = $args['id'] ?? wp_unique_id( 'story-module-' );
$is_preview = ! empty( $args['is_preview'] );
$title      = trim( (string) ( $fields['title'] ?? '' ) );
$intro      = trim( (string) ( $fields['intro'] ?? '' ) );
$links      = array();

foreach ( array_slice( (array) ( $fields['links'] ?? array() ), 0, 4 ) as $row ) {
	$link  = is_array( $row ) ? ( $row['link'] ?? null ) : null;
	$label = is_array( $link ) ? trim( (string) ( $link['title'] ?? '' ) ) : '';
	$url   = is_array( $link ) ? esc_url( (string) ( $link['url'] ?? '' ) ) : '';

	if ( '' !== $label && '' !== $url ) {
		$links[] = array(
			'label'  => $label,
			'url'    => $url,
			'target' => '_blank' === ( $link['target'] ?? '' ),
		);
	}
}

if ( '' === $title || ! $links ) {
	if ( $is_preview ) {
		echo '<p>' . esc_html__( 'Add a title and at least one link to preview the Resource Links.', 'ltt-dive-in' ) . '</p>';
	}

	return;
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', (array) ( $args['classes'] ?? array() ) ) ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="story-resource-links">
		<h2 id="<?php echo esc_attr( $id ); ?>-title" class="story-resource-links__title"><?php echo esc_html( $title ); ?></h2>
		<?php if ( $intro ) : ?>
			<p class="story-resource-links__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
		<ul class="story-resource-links__list">
			<?php foreach ( $links as $link ) : ?>
				<li class="story-resource-links__item">
					<?php if ( $is_preview ) : ?>
						<span class="ltt-button ltt-button--primary-outline"><?php echo esc_html( $link['label'] ); ?></span>
					<?php else : ?>
						<a class="ltt-button ltt-button--primary-outline" href="<?php echo esc_url( $link['url'] ); ?>"<?php if ( $link['target'] ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo esc_html( $link['label'] ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
