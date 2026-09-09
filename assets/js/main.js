/**
 * Keep the footer's visual and reading order aligned at each breakpoint.
 */
( function () {
	'use strict';

	const footerContent = document.querySelector( '[data-footer-content]' );
	const newsletter = document.querySelector( '[data-footer-section="newsletter"]' );
	const partners = document.querySelector( '[data-footer-section="partners"]' );
	const navigation = document.querySelector( '[data-footer-section="navigation"]' );
	const credits = document.querySelector( '[data-footer-credits]' );
	const copyright = document.querySelector( '[data-footer-credit="copyright"]' );
	const legal = document.querySelector( '[data-footer-credit="legal"]' );

	if ( ! footerContent || ! newsletter || ! partners || ! navigation ) {
		return;
	}

	const mobileQuery = window.matchMedia( '(max-width: 767.98px)' );

	const updateFooterOrder = function ( event ) {
		if ( event.matches ) {
			footerContent.append( navigation, newsletter, partners );

			if ( credits && copyright && legal ) {
				credits.append( legal, copyright );
			}
		} else {
			footerContent.append( newsletter, partners, navigation );

			if ( credits && copyright && legal ) {
				credits.append( copyright, legal );
			}
		}
	};

	updateFooterOrder( mobileQuery );
	mobileQuery.addEventListener( 'change', updateFooterOrder );
}() );
