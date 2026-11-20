/**
 * Site alert: daily dismissal on desktop, remembered collapse on mobile.
 */
( function () {
	'use strict';

	const alert = document.querySelector( '[data-site-alert]' );

	if ( ! alert ) {
		return;
	}

	const DISMISSED_KEY = 'ltt-dive-in-site-alert-dismissed';
	const COLLAPSED_KEY = 'ltt-dive-in-site-alert-collapsed';
	const version = alert.getAttribute( 'data-version' ) || '';
	const toggle = alert.querySelector( '[data-site-alert-toggle]' );
	const closeButton = alert.querySelector( '[data-site-alert-close]' );
	const content = alert.querySelector( '.ltt-site-alert__content' );
	const toggleAction = alert.querySelector( '.ltt-site-alert__toggle-action' );
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	const root = document.documentElement;

	const today = function () {
		const now = new Date();

		return now.getFullYear() + '-' + ( now.getMonth() + 1 ) + '-' + now.getDate();
	};

	const store = function ( key, value ) {
		try {
			if ( null === value ) {
				window.localStorage.removeItem( key );
			} else {
				window.localStorage.setItem( key, value );
			}
		} catch ( error ) {
			// Storage can be unavailable; the alert still works for this page view.
		}
	};

	const updateOffset = function () {
		const isSticky = 'sticky' === window.getComputedStyle( alert ).position;
		const height = isSticky && alert.offsetParent ? alert.getBoundingClientRect().height : 0;

		root.style.setProperty( '--ltt-site-alert-height', Math.round( height ) + 'px' );
	};

	const syncToggle = function () {
		const collapsed = alert.classList.contains( 'is-collapsed' );

		if ( toggle ) {
			toggle.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
		}

		if ( toggleAction ) {
			toggleAction.textContent = collapsed ? toggleAction.dataset.collapsedText : toggleAction.dataset.expandedText;
		}
	};

	const afterTransition = function ( element, property, callback ) {
		if ( reducedMotion.matches ) {
			callback();
			return;
		}

		let done = false;
		const finish = function ( event ) {
			if ( done || ( event && ( event.target !== element || event.propertyName !== property ) ) ) {
				return;
			}

			done = true;
			element.removeEventListener( 'transitionend', finish );
			callback();
		};

		element.addEventListener( 'transitionend', finish );
		window.setTimeout( finish, 600 );
	};

	if ( toggle && content ) {
		syncToggle();

		toggle.addEventListener( 'click', function () {
			const collapse = ! alert.classList.contains( 'is-collapsed' );

			alert.classList.add( 'is-toggling' );
			alert.classList.toggle( 'is-collapsed', collapse );
			store( COLLAPSED_KEY, collapse ? version : null );
			syncToggle();

			afterTransition( content, 'grid-template-rows', function () {
				alert.classList.remove( 'is-toggling' );
				updateOffset();
			} );
		} );
	}

	if ( closeButton ) {
		closeButton.addEventListener( 'click', function () {
			store( DISMISSED_KEY, JSON.stringify( { version: version, date: today() } ) );
			alert.classList.add( 'is-closing' );

			afterTransition( alert, 'grid-template-rows', function () {
				alert.classList.add( 'is-dismissed' );
				alert.classList.remove( 'is-closing' );
				updateOffset();
			} );

			// The button is about to disappear; keep keyboard focus at the top of the page.
			const nextFocus = document.querySelector( '#masthead a, #masthead button' );

			if ( nextFocus ) {
				nextFocus.focus( { preventScroll: true } );
			}
		} );
	}

	updateOffset();

	if ( 'ResizeObserver' in window ) {
		new window.ResizeObserver( updateOffset ).observe( alert );
	} else {
		window.addEventListener( 'resize', updateOffset );
	}
}() );
