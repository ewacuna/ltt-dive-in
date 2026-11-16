<?php
/**
 * Events page introduction.
 *
 * Expected arguments:
 * - title: Page title, rendered as the page H1.
 * - tag: Optional label above the title.
 * - text: Optional introduction.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = isset( $args['title'] ) ? str_replace( array( "\r", "\n" ), ' ', (string) $args['title'] ) : '';
$tag   = isset( $args['tag'] ) ? (string) $args['tag'] : '';
$text  = isset( $args['text'] ) ? (string) $args['text'] : '';

if ( '' === $title ) {
	return;
}
?>
<header class="events-intro">
	<div class="events-intro__inner">
		<?php if ( '' !== $tag ) : ?>
			<p class="events-intro__tag ltt-tag"><?php echo esc_html( $tag ); ?></p>
		<?php endif; ?>
		<h1 class="events-intro__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( '' !== $text ) : ?>
			<p class="events-intro__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</div>
</header>
