/**
 * Position Gravity Forms consent fields beside the button on desktop and above it on mobile.
 *
 * @package LTT_Dive_In
 */
(function () {
	'use strict';

	var formRootSelector = '.content-module__form--newsletter, .content-module__form--meetings';
	var consentFieldSelector = '.gfield--type-consent, .gfield--type-checkbox';

	/**
	 * Identify a Gravity Forms consent field, including a single-choice checkbox.
	 *
	 * @param {Element} field Gravity Forms field wrapper.
	 * @return {boolean} Whether the field represents consent.
	 */
	function isConsentField( field ) {
		if ( field.classList.contains( 'gfield--type-consent' ) ) {
			return true;
		}

		if ( field.querySelectorAll( 'input[type="checkbox"]' ).length !== 1 ) {
			return false;
		}

		var label = ( field.textContent || '' ).replace( /\s+/g, ' ' ).toLowerCase();
		return /consent|agree|agreement|newsletter|receive|subscribe|privacy|marketing|updates|contacted/.test( label );
	}

	/**
	 * Place consent fields beside the submit control on desktop and above it on mobile.
	 *
	 * @param {Element} root Content Module form wrapper.
	 * @return {void}
	 */
	function placeConsentForViewport( root ) {
		var wrappers = root.querySelectorAll( '.gform_wrapper' );

		Array.prototype.forEach.call( wrappers, function ( wrapper ) {
			var fields = wrapper.querySelector( '.gform_fields' );
			var footer = wrapper.querySelector( '.gform_footer' );
			var submitButton = footer && footer.querySelector( '.gform_button' );

			if ( ! fields || ! footer || ! submitButton ) {
				return;
			}

			var consentFields = Array.prototype.filter.call( fields.querySelectorAll( consentFieldSelector ), isConsentField );
			var consentList = footer.querySelector( '.content-module__form-consent-group' );

			if ( ! consentList ) {
				if ( ! consentFields.length ) {
					return;
				}

				consentList = document.createElement( 'div' );
				consentList.className = 'content-module__form-consent-group';
				footer.appendChild( consentList );
			}

			if ( consentFields.length ) {
				while ( consentList.firstChild ) {
					consentList.removeChild( consentList.firstChild );
				}

				Array.prototype.forEach.call( consentFields, function ( consentField ) {
					consentList.appendChild( consentField );
				} );
			}

			if ( window.matchMedia( '(max-width: 767.98px)' ).matches ) {
				if ( consentList.nextElementSibling !== submitButton ) {
					footer.insertBefore( consentList, submitButton );
				}
			} else if ( submitButton.nextElementSibling !== consentList ) {
				submitButton.insertAdjacentElement( 'afterend', consentList );
			}
		} );
	}

	/**
	 * Find Content Module form wrappers in a newly inserted node.
	 *
	 * @param {Node} node Added DOM node.
	 * @return {void}
	 */
	function processAddedNode( node ) {
		if ( node.nodeType !== 1 ) {
			return;
		}

		if ( node.matches( formRootSelector ) ) {
			placeConsentForViewport( node );
		}

		Array.prototype.forEach.call( node.querySelectorAll( formRootSelector ), placeConsentForViewport );
	}

	/**
	 * Initialize existing modules and keep up with Gravity Forms AJAX re-renders.
	 *
	 * @return {void}
	 */
	function initialize() {
		function updateConsentPositions() {
			Array.prototype.forEach.call( document.querySelectorAll( formRootSelector ), placeConsentForViewport );
		}

		updateConsentPositions();

		var viewportQuery = window.matchMedia( '(max-width: 767.98px)' );
		if ( viewportQuery.addEventListener ) {
			viewportQuery.addEventListener( 'change', updateConsentPositions );
		} else if ( viewportQuery.addListener ) {
			viewportQuery.addListener( updateConsentPositions );
		}

		if ( ! window.MutationObserver || ! document.body ) {
			return;
		}

		var observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				var target = mutation.target.nodeType === 1 ? mutation.target : mutation.target.parentElement;
				var root = target && target.closest ? target.closest( formRootSelector ) : null;

				if ( root ) {
					placeConsentForViewport( root );
					return;
				}

				Array.prototype.forEach.call( mutation.addedNodes, processAddedNode );
			} );
		} );

		observer.observe( document.body, { childList: true, subtree: true } );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize );
	} else {
		initialize();
	}
})();
