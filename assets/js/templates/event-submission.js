/**
 * Mount the Seeker event submission form with the theme styles.
 *
 * Seeker's widget script defines window.SubmitFormWidget and renders the form
 * in its own shadow root; the theme styles reach it through `customCss`.
 *
 * The widget also changes the host page, so this script:
 * - keeps it from loading its Google Fonts (the theme passes Gotham instead);
 * - removes the title, meta, and link tags it writes into the page head, and
 *   keeps the WordPress title and canonical link;
 * - names fields that have only a placeholder, for assistive technology.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const settings = window.ltt_dive_in_event_submission || {};
	const WIDGET_HEAD_ATTRIBUTE = 'data-calendar-widget-seo';
	const WIDGET_FONTS_ID = 'cw-google-fonts';

	// The widget skips its font stylesheet when an element with this ID exists.
	function blockWidgetFonts() {
		if ( document.getElementById( WIDGET_FONTS_ID ) ) {
			return;
		}

		const marker = document.createElement( 'meta' );

		marker.id = WIDGET_FONTS_ID;
		marker.name = 'ltt-seeker-widget-fonts';
		marker.content = 'theme';
		document.head.appendChild( marker );
	}

	function protectHead() {
		const title = document.title;
		const canonical = document.querySelector( 'link[rel="canonical"]' );
		const canonicalCopy = canonical ? canonical.cloneNode() : null;
		const titleElement = document.querySelector( 'title' );

		new window.MutationObserver( function () {
			document.head.querySelectorAll( '[' + WIDGET_HEAD_ATTRIBUTE + ']' ).forEach( function ( node ) {
				node.remove();
			} );

			if ( canonicalCopy && ! document.head.querySelector( 'link[rel="canonical"]' ) ) {
				document.head.appendChild( canonicalCopy.cloneNode() );
			}
		} ).observe( document.head, { childList: true } );

		if ( titleElement ) {
			new window.MutationObserver( function () {
				if ( document.title !== title ) {
					document.title = title;
				}
			} ).observe( titleElement, { childList: true, characterData: true, subtree: true } );
		}
	}

	function nameFields( root ) {
		root.querySelectorAll( 'input:not([type="hidden"]), textarea, select' ).forEach( function ( field ) {
			const placeholder = ( field.getAttribute( 'placeholder' ) || '' ).trim();

			if ( ! placeholder || field.hasAttribute( 'aria-label' ) || field.hasAttribute( 'aria-labelledby' ) || ( field.labels && field.labels.length ) ) {
				return;
			}

			field.setAttribute( 'aria-label', placeholder.replace( /\s*\*$/, '' ) );

			if ( /\*$/.test( placeholder ) ) {
				field.setAttribute( 'aria-required', 'true' );
			}
		} );
	}

	function watchFields( target ) {
		const timer = window.setInterval( function () {
			const host = Array.prototype.find.call( target.querySelectorAll( '*' ), function ( element ) {
				return element.shadowRoot;
			} );

			if ( ! host ) {
				return;
			}

			window.clearInterval( timer );
			nameFields( host.shadowRoot );
			new window.MutationObserver( function () {
				nameFields( host.shadowRoot );
			} ).observe( host.shadowRoot, { childList: true, subtree: true } );
		}, 200 );

		window.setTimeout( function () {
			window.clearInterval( timer );
		}, 15000 );
	}

	function mount( container ) {
		const status = container.querySelector( '[data-event-submission-status]' );
		const target = document.createElement( 'div' );

		if ( ! window.SubmitFormWidget || ! container.dataset.calendarId || ! container.dataset.formId ) {
			if ( status ) {
				status.textContent = container.dataset.errorMessage || 'The submission form could not be loaded. Please try again later.';
			}

			return;
		}

		target.id = 'seeker-submission-form';
		target.className = 'event-submission__widget';
		container.appendChild( target );

		window.SubmitFormWidget.mount( target, {
			calendarId: container.dataset.calendarId,
			formId: container.dataset.formId,
			customCss: settings.customCss || '',
		} );

		watchFields( target );

		if ( status ) {
			status.remove();
		}
	}

	function init() {
		const containers = document.querySelectorAll( '[data-event-submission]' );

		if ( ! containers.length ) {
			return;
		}

		blockWidgetFonts();
		protectHead();
		containers.forEach( mount );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
