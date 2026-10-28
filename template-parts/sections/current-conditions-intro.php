<?php
/**
 * Current Conditions tab intro.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = (string) ( $args['title'] ?? '' );
$text  = (string) ( $args['text'] ?? '' );

if ( ! $title ) {
	return;
}
?>
<header class="current-conditions-intro">
	<div class="current-conditions-intro__container">
		<h1 class="current-conditions-intro__title"><?php echo esc_html( $title ); ?></h1>

		<?php if ( $text ) : ?>
			<p class="current-conditions-intro__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</div>
</header>
