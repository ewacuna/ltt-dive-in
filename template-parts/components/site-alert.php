<?php
/**
 * Site alert banner.
 *
 * @package LTT_Dive_In
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alert = $args['alert'] ?? null;

if ( ! is_array( $alert ) ) {
	return;
}

$icon_svgs = array(
	'warning' => '<svg viewBox="0 0 27 24" width="27" height="24" fill="currentColor" focusable="false" aria-hidden="true"><path d="M26.5847 20.1873L15.6635 1.27076C15.2108 0.487027 14.3675 0 13.4621 0C12.5567 0 11.7134 0.487027 11.2611 1.27076L0.339888 20.1869C-0.113227 20.9706 -0.113227 21.9451 0.339472 22.7292C0.792147 23.513 1.63547 24 2.54044 24H24.3837C25.2887 24 26.132 23.513 26.5851 22.7288C27.0374 21.9447 27.0374 20.9706 26.5847 20.1873ZM13.4621 21.1766C12.4699 21.1766 11.6656 20.3722 11.6656 19.38C11.6656 18.3878 12.4699 17.5834 13.4621 17.5834C14.4543 17.5834 15.2587 18.3878 15.2587 19.38C15.2587 20.3722 14.4544 21.1766 13.4621 21.1766ZM15.2674 6.94765L14.6592 15.4169C14.6141 16.0447 14.0916 16.5311 13.4621 16.5311C12.8327 16.5311 12.3102 16.0447 12.2651 15.4169L11.6569 6.94765C11.5816 5.89968 12.4115 5.00806 13.4621 5.00806C14.5128 5.00806 15.3427 5.89968 15.2674 6.94765Z"/></svg>',
);

$icon_markup = '';

if ( $alert['icon_url'] ) {
	$icon_markup = '<span class="ltt-site-alert__icon ltt-site-alert__icon--custom" style="' . esc_attr( '--ltt-site-alert-icon: url("' . esc_url_raw( $alert['icon_url'] ) . '")' ) . '" aria-hidden="true"></span>';
} elseif ( isset( $icon_svgs[ $alert['icon'] ] ) ) {
	$icon_markup = '<span class="ltt-site-alert__icon" aria-hidden="true">' . $icon_svgs[ $alert['icon'] ] . '</span>';
}

$link       = $alert['link'];
$opens_new  = $link && '_blank' === ( $link['target'] ?? '' );
$classes    = array(
	'ltt-site-alert',
	'ltt-site-alert--' . $alert['type'],
	empty( $alert['sticky'] ) ? 'ltt-site-alert--static' : 'ltt-site-alert--sticky',
);
$color_style = '--ltt-site-alert-background: ' . $alert['colors']['background'] . '; --ltt-site-alert-text: ' . $alert['colors']['text'] . ';';
?>
<div
	id="ltt-site-alert"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	style="<?php echo esc_attr( $color_style ); ?>"
	role="region"
	aria-label="<?php echo esc_attr( $alert['name'] ); ?>"
	data-site-alert
	data-version="<?php echo esc_attr( $alert['version'] ); ?>"
>
	<div class="ltt-site-alert__frame">
		<div class="ltt-site-alert__inner">
			<button
				class="ltt-site-alert__toggle"
				type="button"
				aria-expanded="true"
				aria-controls="ltt-site-alert-content"
				data-site-alert-toggle
			>
				<?php echo $icon_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-owned SVG and an escaped style attribute. ?>
				<span class="ltt-site-alert__toggle-text">
					<span class="ltt-site-alert__toggle-action" data-expanded-text="<?php esc_attr_e( 'Click to collapse', 'ltt-dive-in' ); ?>" data-collapsed-text="<?php esc_attr_e( 'Click to expand', 'ltt-dive-in' ); ?>"><?php esc_html_e( 'Click to collapse', 'ltt-dive-in' ); ?></span>
					<strong><?php echo esc_html( $alert['name'] ); ?></strong>
				</span>
				<svg class="ltt-site-alert__chevrons" viewBox="0 0 24 32" width="24" height="32" fill="currentColor" focusable="false" aria-hidden="true">
					<path class="ltt-site-alert__chevron-up" d="M15.7135 15L12 8.6338L8.28653 15H5L12 3L19 15H15.7135Z"/>
					<path class="ltt-site-alert__chevron-down" d="M5 17H8.28653L12 23.3662L15.7135 17H19L12 29L5 17Z"/>
				</svg>
			</button>

			<div class="ltt-site-alert__desktop-icon">
				<?php echo $icon_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-owned SVG and an escaped style attribute. ?>
			</div>

			<div id="ltt-site-alert-content" class="ltt-site-alert__content">
				<div class="ltt-site-alert__content-inner">
					<p class="ltt-site-alert__message">
						<?php if ( $alert['label'] ) : ?>
							<strong class="ltt-site-alert__label"><?php echo esc_html( $alert['label'] ); ?></strong>
						<?php endif; ?>
						<?php echo esc_html( $alert['message'] ); ?>
					</p>
					<?php if ( $link ) : ?>
						<a
							class="ltt-site-alert__link"
							href="<?php echo esc_url( $link['url'] ); ?>"
							<?php if ( $opens_new ) : ?>
								target="_blank" rel="noopener noreferrer"
							<?php endif; ?>
						><?php echo esc_html( $link['title'] ); ?><?php if ( $opens_new ) : ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'ltt-dive-in' ); ?></span><?php endif; ?></a>
					<?php endif; ?>
				</div>
			</div>

			<button class="ltt-site-alert__close" type="button" data-site-alert-close>
				<span class="screen-reader-text">
					<?php
					/* translators: %s: alert type name. */
					echo esc_html( sprintf( __( 'Dismiss %s', 'ltt-dive-in' ), strtolower( $alert['name'] ) ) );
					?>
				</span>
				<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" focusable="false" aria-hidden="true"><path d="M13.7618 11.9993L23.6349 2.1277C24.1217 1.64086 24.1217 0.851958 23.6349 0.365125C23.148 -0.121708 22.3591 -0.121708 21.8723 0.365125L12.0007 10.2367L2.1277 0.365125C1.64086 -0.121708 0.851958 -0.121708 0.365125 0.365125C-0.121708 0.851958 -0.121708 1.64086 0.365125 2.1277L10.2367 11.9993L0.365125 21.8723C-0.121708 22.3591 -0.121708 23.148 0.365125 23.6349C0.608542 23.8783 0.926743 24 1.24641 24C1.56608 24 1.88428 23.8783 2.1277 23.6349L11.9993 13.7633L21.8708 23.6349C22.1143 23.8783 22.4325 24 22.7521 24C23.0718 24 23.39 23.8783 23.6334 23.6349C24.1202 23.148 24.1202 22.3591 23.6334 21.8723L13.7618 12.0007V11.9993Z"/></svg>
			</button>
		</div>
	</div>
</div>
<?php
// Apply the saved dismissed and collapsed states before the first paint.
wp_print_inline_script_tag(
	"(function(){var a=document.getElementById('ltt-site-alert');if(!a){return;}try{var v=a.getAttribute('data-version'),d=new Date(),t=d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate(),x=JSON.parse(localStorage.getItem('ltt-dive-in-site-alert-dismissed')||'null');if(x&&x.version===v&&x.date===t){a.classList.add('is-dismissed');}if(localStorage.getItem('ltt-dive-in-site-alert-collapsed')===v){a.classList.add('is-collapsed');}}catch(e){}})();"
);
