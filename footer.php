<?php
/**
 * Site footer.
 *
 * @package LTT_Dive_In
 */

$footer_assets_uri = LTT_DIVE_IN_URI . '/assets/images/footer';
$faq_link          = ltt_dive_in_get_footer_option( 'ltt_dive_in_footer_faq_link', array() );
$faq_url           = '';
$faq_title         = __( 'Frequently Asked Questions', 'ltt-dive-in' );
$faq_target        = '';

if ( is_array( $faq_link ) ) {
	$faq_url = ! empty( $faq_link['url'] ) ? $faq_link['url'] : '';

	if ( ! empty( $faq_link['title'] ) ) {
		$faq_title = $faq_link['title'];
	}

	if ( ! empty( $faq_link['target'] ) && '_blank' === $faq_link['target'] ) {
		$faq_target = '_blank';
	}
}

$newsletter_heading     = ltt_dive_in_get_footer_option( 'ltt_dive_in_footer_newsletter_heading', __( 'Subscribe', 'ltt-dive-in' ) );
$newsletter_description = ltt_dive_in_get_footer_option(
	'ltt_dive_in_footer_newsletter_description',
	__( 'Get trip ideas and seasonal guides delivered to your inbox.', 'ltt-dive-in' )
);
$newsletter_disclaimer  = ltt_dive_in_get_footer_option(
	'ltt_dive_in_footer_newsletter_disclaimer',
	__( 'By subscribing you agree to our Privacy Policy and consent to receive updates from Lake Tahoe Travel.', 'ltt-dive-in' )
);
$social_links           = ltt_dive_in_get_footer_social_links();
$newsletter_form        = ltt_dive_in_get_footer_newsletter_form();
$footer_logos           = ltt_dive_in_get_footer_logos();
$footer_logo_columns    = $footer_logos['columns'];
$has_partner_logos      = (bool) array_filter( $footer_logo_columns );
$has_footer_logos       = ! empty( $footer_logos['brand'] ) || $has_partner_logos;
?>
	<footer id="colophon" class="site-footer">
		<div class="site-footer__container">
			<div class="site-footer__content" data-footer-content>
				<section class="site-footer__newsletter" data-footer-section="newsletter" aria-labelledby="footer-newsletter-heading">
					<div class="site-footer__newsletter-copy">
						<h2 id="footer-newsletter-heading" class="site-footer__heading"><?php echo esc_html( $newsletter_heading ); ?></h2>
						<p><?php echo esc_html( $newsletter_description ); ?></p>
					</div>

					<?php if ( $newsletter_form ) : ?>
						<div class="site-footer__newsletter-form">
							<?php echo $newsletter_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted widget or registered integration output. ?>
						</div>
						<p class="site-footer__newsletter-disclaimer"><?php echo esc_html( $newsletter_disclaimer ); ?></p>
					<?php endif; ?>

					<?php if ( $social_links ) : ?>
						<nav class="site-footer__social" aria-label="<?php esc_attr_e( 'Social media', 'ltt-dive-in' ); ?>">
							<ul>
								<?php foreach ( $social_links as $social_link ) : ?>
									<li>
										<a href="<?php echo esc_url( $social_link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php /* translators: %s: social network name. */ echo esc_attr( sprintf( __( '%s (opens in a new tab)', 'ltt-dive-in' ), $social_link['label'] ) ); ?>">
											<img src="<?php echo esc_url( $footer_assets_uri . '/' . $social_link['slug'] . '.svg' ); ?>" alt="" width="28" height="28">
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
					<?php endif; ?>
				</section>

				<div class="site-footer__partners" data-footer-section="partners"<?php echo $has_footer_logos ? '' : ' hidden'; ?>>
					<?php if ( ! empty( $footer_logos['brand'] ) ) : ?>
						<?php
						echo wp_get_attachment_image(
							$footer_logos['brand']['attachment_id'],
							'full',
							false,
							array(
								'class'   => $footer_logos['brand']['class'],
								'loading' => 'lazy',
							)
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core-generated image markup.
						?>
					<?php endif; ?>

					<?php if ( ! empty( $footer_logos['brand'] ) && $has_partner_logos ) : ?>
						<div class="site-footer__partner-divider" aria-hidden="true"></div>
					<?php endif; ?>

					<?php if ( $has_partner_logos ) : ?>
						<div class="site-footer__partner-columns">
							<?php foreach ( $footer_logo_columns as $column_name => $column_logos ) : ?>
								<?php if ( $column_logos ) : ?>
										<div class="site-footer__partner-column site-footer__partner-column--<?php echo esc_attr( $column_name ); ?>">
											<?php foreach ( $column_logos as $column_logo ) : ?>
												<div class="site-footer__partner-item">
													<?php
													echo wp_get_attachment_image(
														$column_logo['attachment_id'],
														'full',
														false,
														array(
															'class'   => $column_logo['class'],
															'loading' => 'lazy',
														)
													); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core-generated image markup.
													?>
												</div>
											<?php endforeach; ?>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="site-footer__navigation" data-footer-section="navigation">
					<?php if ( has_nav_menu( 'footer_navigation' ) || $faq_url ) : ?>
						<nav aria-label="<?php esc_attr_e( 'Footer navigation', 'ltt-dive-in' ); ?>">
							<?php if ( $faq_url ) : ?>
								<a class="site-footer__faq-link ltt-button ltt-button--dark-tertiary" href="<?php echo esc_url( $faq_url ); ?>"<?php echo $faq_target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $faq_title ); ?></a>
							<?php endif; ?>

							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'footer_navigation',
									'container'      => false,
									'menu_class'     => 'site-footer__menu',
									'menu_id'        => 'footer-navigation-menu',
									'fallback_cb'    => false,
									'depth'          => 1,
								)
							);
							?>
						</nav>
					<?php endif; ?>
				</div>
			</div>

			<div class="site-footer__credits" data-footer-credits>
				<p class="site-footer__copyright" data-footer-credit="copyright">
					<?php
					$footer_text = get_theme_mod( 'ltt_dive_in_footer_text' );
					if ( $footer_text ) {
						echo esc_html( $footer_text );
					} else {
						printf(
							/* translators: %1$s: current year, %2$s: site name. */
							esc_html__( '© %1$s %2$s. All rights reserved.', 'ltt-dive-in' ),
							esc_html( wp_date( 'Y' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
					}
					?>
				</p>

				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<nav class="site-footer__legal" data-footer-credit="legal" aria-label="<?php esc_attr_e( 'Legal', 'ltt-dive-in' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => 'site-footer__legal-menu',
								'menu_id'        => 'footer-legal-menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
