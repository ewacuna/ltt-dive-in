<?php
/**
 * Site footer.
 *
 * @package LTT_Dive_In
 */

$footer_assets_uri      = LTT_DIVE_IN_URI . '/assets/images/footer';
$faq_link               = ltt_dive_in_get_footer_option( 'ltt_dive_in_footer_faq_link', array() );
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
										<a href="<?php echo esc_url( $social_link['url'] ); ?>" aria-label="<?php echo esc_attr( $social_link['label'] ); ?>">
											<img src="<?php echo esc_url( $footer_assets_uri . '/' . $social_link['slug'] . '.svg' ); ?>" alt="" width="28" height="28">
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
					<?php endif; ?>
				</section>

				<div class="site-footer__partners" data-footer-section="partners">
					<img class="site-footer__brand-logo" src="<?php echo esc_url( $footer_assets_uri . '/lake-tahoe-travel-white.svg' ); ?>" alt="<?php esc_attr_e( 'Lake Tahoe Travel', 'ltt-dive-in' ); ?>" width="194" height="78">
					<div class="site-footer__partner-divider" aria-hidden="true"></div>

					<div class="site-footer__partner-row site-footer__partner-row--primary">
						<img src="<?php echo esc_url( $footer_assets_uri . '/north-tahoe-community-alliance.svg' ); ?>" alt="<?php esc_attr_e( 'North Tahoe Community Alliance', 'ltt-dive-in' ); ?>" width="186" height="59">
						<img src="<?php echo esc_url( $footer_assets_uri . '/travel-north-tahoe-nevada.svg' ); ?>" alt="<?php esc_attr_e( 'Travel North Tahoe Nevada', 'ltt-dive-in' ); ?>" width="102" height="86">
					</div>

					<div class="site-footer__partner-row site-footer__partner-row--secondary">
						<img src="<?php echo esc_url( $footer_assets_uri . '/travel-nevada.svg' ); ?>" alt="<?php esc_attr_e( 'Travel Nevada', 'ltt-dive-in' ); ?>" width="81" height="66">
						<img src="<?php echo esc_url( $footer_assets_uri . '/visit-california.svg' ); ?>" alt="<?php esc_attr_e( 'Visit California', 'ltt-dive-in' ); ?>" width="135" height="44">
					</div>

					<div class="site-footer__partner-row site-footer__partner-row--supporting">
						<img src="<?php echo esc_url( $footer_assets_uri . '/usa.svg' ); ?>" alt="<?php esc_attr_e( 'USA', 'ltt-dive-in' ); ?>" width="93" height="38">
						<img src="<?php echo esc_url( $footer_assets_uri . '/leave-no-trace.svg' ); ?>" alt="<?php esc_attr_e( 'Leave No Trace', 'ltt-dive-in' ); ?>" width="118" height="32">
					</div>
				</div>

				<div class="site-footer__navigation" data-footer-section="navigation">
					<?php if ( has_nav_menu( 'footer_navigation' ) || ( is_array( $faq_link ) && ! empty( $faq_link['url'] ) && ! empty( $faq_link['title'] ) ) ) : ?>
						<nav aria-label="<?php esc_attr_e( 'Footer navigation', 'ltt-dive-in' ); ?>">
							<?php if ( is_array( $faq_link ) && ! empty( $faq_link['url'] ) && ! empty( $faq_link['title'] ) ) : ?>
								<a class="site-footer__faq-link" href="<?php echo esc_url( $faq_link['url'] ); ?>"<?php echo ! empty( $faq_link['target'] ) ? ' target="' . esc_attr( $faq_link['target'] ) . '" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $faq_link['title'] ); ?></a>
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
