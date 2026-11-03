<?php
/**
 * Generic route sign for a road conditions card.
 *
 * Decorative: the card heading names the highway. Shapes are generic
 * Interstate, U.S. Route, and State Route signs with the route number.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shield = (string) ( $args['shield'] ?? 'state' );
$number = absint( $args['number'] ?? 0 );

if ( ! $number ) {
	return;
}

$digits    = strlen( (string) $number );
$font_size = 3 === $digits ? 18 : ( 1 === $digits ? 28 : 24 );
?>
<?php if ( 'interstate' === $shield ) : ?>
	<svg class="road-shield road-shield--interstate" width="62" height="63" viewBox="0 0 62 63" aria-hidden="true" focusable="false">
		<path d="M31 1.5c8.4 4 18.4 4.6 28.5.8 1.6 7.2 1.9 14.4.8 21.6C58.1 40.8 47.6 54.4 31 61.5 14.4 54.4 3.9 40.8 1.7 23.9.6 16.7.9 9.5 2.5 2.3 12.6 6.1 22.6 5.5 31 1.5Z" fill="#ffffff" />
		<path d="M31 5.2c7.6 3.4 16.4 4 25.4 1.2.9 4.8 1.2 9.6.8 14.3H4.8c-.4-4.7-.1-9.5.8-14.3 9 2.8 17.8 2.2 25.4-1.2Z" fill="#B82025" />
		<path d="M4.9 23.7h52.2C55.2 38.5 45.8 50.3 31 57.2 16.2 50.3 6.8 38.5 4.9 23.7Z" fill="#263B84" />
		<text x="31" y="41" fill="#ffffff" font-size="<?php echo esc_attr( (string) $font_size ); ?>" font-weight="700" text-anchor="middle" dominant-baseline="middle"><?php echo esc_html( (string) $number ); ?></text>
	</svg>
<?php elseif ( 'us' === $shield ) : ?>
	<svg class="road-shield road-shield--us" width="62" height="56" viewBox="0 0 62 56" aria-hidden="true" focusable="false">
		<path d="M5 3h52c.8 3 3 4.6 3 4.6-1.6 3.4-2 6.5-.6 10.6 3.2 9.4 1.4 19.3-5.6 25.7C48 49.2 39.6 51 31 54.5 22.4 51 14 49.2 8.2 43.9 1.2 37.5-.6 27.6 2.6 18.2 4 14.1 3.6 11 2 7.6 2 7.6 4.2 6 5 3Z" fill="#ffffff" stroke="#000000" stroke-width="2.5" stroke-linejoin="round" />
		<text x="31" y="30" fill="#000000" font-size="<?php echo esc_attr( (string) $font_size ); ?>" font-weight="700" text-anchor="middle" dominant-baseline="middle"><?php echo esc_html( (string) $number ); ?></text>
	</svg>
<?php else : ?>
	<svg class="road-shield road-shield--state" width="62" height="56" viewBox="0 0 62 56" aria-hidden="true" focusable="false">
		<rect x="4" y="4" width="54" height="48" rx="14" fill="#ffffff" stroke="#000000" stroke-width="2.5" />
		<text x="31" y="29" fill="#000000" font-size="<?php echo esc_attr( (string) $font_size ); ?>" font-weight="700" text-anchor="middle" dominant-baseline="middle"><?php echo esc_html( (string) $number ); ?></text>
	</svg>
<?php endif; ?>
