<?php
/**
 * Current Conditions hero.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title         = str_replace( array( "\r", "\n", "\u{2028}", "\u{2029}" ), ' ', (string) ( $args['title'] ?? '' ) );
$heading_level = 'p' === ( $args['heading_level'] ?? '' ) ? 'p' : 'h1';
$text_lines    = array_filter( array_map( 'trim', preg_split( '/\R+/u', (string) ( $args['text'] ?? '' ) ) ) );

if ( ! $title ) {
	return;
}
?>
<section class="current-conditions-hero" data-seamless-region="hero">
	<div class="current-conditions-hero__content">
		<<?php echo esc_html( $heading_level ); ?> class="current-conditions-hero__title"><?php echo esc_html( $title ); ?></<?php echo esc_html( $heading_level ); ?>>

		<?php if ( $text_lines ) : ?>
			<p class="current-conditions-hero__text"><?php echo implode( ' <br>', array_map( 'esc_html', $text_lines ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each line is escaped; only the break is markup. ?></p>
		<?php endif; ?>
	</div>
</section>
