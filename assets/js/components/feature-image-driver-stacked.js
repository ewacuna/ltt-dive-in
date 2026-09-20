/**
 * Mobile disclosure behavior for the Light Stacked Feature Driver.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const mobileQuery = window.matchMedia( '(max-width: 767.98px)' );
	const drivers = document.querySelectorAll( '[data-feature-image-driver-stacked-mobile]' );

	const setExpanded = function ( driver, activeCard, isMobile ) {
		driver.querySelectorAll( '.feature-image-driver__stacked-card' ).forEach( function ( card ) {
			const expanded = card === activeCard;
			const trigger = card.querySelector( '[data-feature-image-driver-stacked-expand]' );
			const panel = card.querySelector( '.feature-image-driver__stacked-card-panel' );

			card.classList.toggle( 'is-expanded', expanded );

			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			}

			if ( panel ) {
				if ( expanded ) {
					panel.hidden = false;
				}

				if ( isMobile && ! expanded ) {
					panel.setAttribute( 'inert', '' );
				} else {
					panel.removeAttribute( 'inert' );
				}
			}
		} );
	};

	drivers.forEach( function ( driver ) {
		const cards = Array.from( driver.querySelectorAll( '.feature-image-driver__stacked-card' ) );

		if ( ! cards.length ) {
			return;
		}

		const sync = function () {
			const activeCard = driver.querySelector( '.feature-image-driver__stacked-card.is-expanded' ) || cards[ 0 ];

			setExpanded( driver, activeCard, mobileQuery.matches );
		};

		driver.querySelectorAll( '[data-feature-image-driver-stacked-expand]' ).forEach( function ( trigger ) {
			trigger.addEventListener( 'click', function () {
				if ( ! mobileQuery.matches ) {
					return;
				}

				const card = trigger.closest( '.feature-image-driver__stacked-card' );

				if ( card ) {
					setExpanded( driver, card, true );

					const title = card.querySelector( '.feature-image-driver__stacked-card-title' );

					if ( title ) {
						title.setAttribute( 'tabindex', '-1' );
						title.focus( { preventScroll: true } );
					}
				}
			} );
		} );

		sync();

		if ( mobileQuery.addEventListener ) {
			mobileQuery.addEventListener( 'change', sync );
		} else {
			mobileQuery.addListener( sync );
		}
	} );
}() );
