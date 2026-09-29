/** Show section links as a disclosure when the desktop row cannot fit. */
( function () {
	'use strict';

	const header = document.querySelector( '.site-header' );
	const site = document.querySelector( '#page' );
	const section = header && header.querySelector( '.ltt-section-navigation' );
	if ( ! section || ! site ) {
		return;
	}

	const toggle = section.querySelector( '.ltt-section-navigation__toggle' );
	const menu = section.querySelector( '.ltt-section-navigation__menu' );
	const compactQuery = window.matchMedia( '(max-width: 1399.98px)' );
	const mobileQuery = window.matchMedia( '(max-width: 991.98px)' );
	const updateHeight = function () {
		site.style.setProperty( '--ltt-dive-in-header-height', header.getBoundingClientRect().height + 'px' );
	};

	if ( toggle && menu ) {
		let activeAnimation = null;
		const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		const setExpanded = function ( expanded, restoreFocus, animate ) {
			const startHeight = menu.hidden ? 0 : menu.getBoundingClientRect().height;
			const startOpacity = menu.hidden ? 0 : Number.parseFloat( window.getComputedStyle( menu ).opacity );
			if ( activeAnimation ) {
				activeAnimation.cancel();
				activeAnimation = null;
			}

			toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			if ( restoreFocus ) {
				toggle.focus();
			}
			menu.inert = compactQuery.matches && ! expanded;
			if ( ! compactQuery.matches || ! animate || reduceMotion.matches || ! menu.animate ) {
				menu.hidden = compactQuery.matches && ! expanded;
				menu.classList.remove( 'is-animating' );
				updateHeight();
				return;
			}

			menu.hidden = false;
			const endHeight = expanded ? menu.scrollHeight : 0;
			menu.classList.add( 'is-animating' );
			const animation = menu.animate(
				[
					{ height: startHeight + 'px', opacity: startOpacity },
					{ height: endHeight + 'px', opacity: expanded ? 1 : 0 },
				],
				{ duration: 240, easing: 'ease-in-out', fill: 'forwards' }
			);
			activeAnimation = animation;
			animation.onfinish = function () {
				if ( activeAnimation !== animation ) {
					return;
				}
				menu.hidden = ! expanded;
				animation.cancel();
				activeAnimation = null;
				menu.classList.remove( 'is-animating' );
				updateHeight();
			};
			updateHeight();
		};
		const updateMode = function () {
			const focusInMenu = menu.contains( document.activeElement );
			const focusOnToggle = document.activeElement === toggle;
			toggle.hidden = ! compactQuery.matches;
			setExpanded( false, compactQuery.matches && focusInMenu, false );
			if ( ! compactQuery.matches && focusOnToggle ) {
				const firstLink = menu.querySelector( 'a' );
				if ( firstLink ) {
					firstLink.focus();
				}
			}
		};

		section.classList.add( 'is-enhanced' );
		updateMode();
		compactQuery.addEventListener( 'change', updateMode );
		toggle.addEventListener( 'click', function () {
			setExpanded( 'true' !== toggle.getAttribute( 'aria-expanded' ), false, true );
		} );
		section.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && compactQuery.matches && 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
				event.preventDefault();
				event.stopPropagation();
				setExpanded( false, true, true );
			}
		} );
		menu.addEventListener( 'click', function ( event ) {
			if ( compactQuery.matches && event.target.closest( 'a' ) ) {
				setExpanded( false, true, false );
			}
		} );
		const primaryToggle = header.querySelector( '[data-menu-button]' );
		if ( primaryToggle ) {
			primaryToggle.addEventListener( 'click', function () {
				if ( mobileQuery.matches ) {
					setExpanded( false, false, false );
				}
			} );
		}
	}

	updateHeight();
	if ( 'ResizeObserver' in window ) {
		new ResizeObserver( updateHeight ).observe( header );
	} else {
		window.addEventListener( 'resize', updateHeight );
		if ( document.fonts ) {
			document.fonts.ready.then( updateHeight );
		}
	}
}() );
