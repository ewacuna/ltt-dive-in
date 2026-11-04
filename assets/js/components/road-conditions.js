/**
 * Road conditions module: state links, highway filter, and card disclosures.
 *
 * The server renders every state and card, the state links navigate, and the
 * highway form submits with GET, so the module works without JavaScript. This
 * script switches views in place, keeps the URL shareable, and announces changes.
 * It initializes on load and again when seamless navigation swaps the module in.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const init = function () {
		const root = document.querySelector( '[data-road-conditions]' );

		if ( ! root || root.dataset.roadConditionsReady ) {
			return;
		}

		root.dataset.roadConditionsReady = 'true';

		const links = Array.from( root.querySelectorAll( '[data-road-state-link]' ) );
		const panels = Array.from( root.querySelectorAll( '[data-road-state-panel]' ) );
		const forms = Array.from( root.querySelectorAll( '[data-road-highway-form]' ) );
		const status = root.querySelector( '[data-road-conditions-status]' );

		const announce = function ( message ) {
			if ( ! status || ! message ) {
				return;
			}

			status.textContent = '';
			window.setTimeout( function () {
				status.textContent = message;
			}, 50 );
		};

		const getForm = function ( state ) {
			return forms.find( function ( form ) {
				return form.dataset.roadHighwayForm === state;
			} );
		};

		const getSelect = function ( state ) {
			const form = getForm( state );

			return form ? form.querySelector( '[data-road-highway-select]' ) : null;
		};

		const updateUrl = function ( state, highway ) {
			if ( ! window.history || ! window.history.replaceState ) {
				return;
			}

			const url = new URL( window.location.href );

			url.searchParams.set( 'road_state', state );

			if ( highway ) {
				url.searchParams.set( 'highway', highway );
			} else {
				url.searchParams.delete( 'highway' );
			}

			window.history.replaceState( null, '', url.toString() );
		};

		const filterCards = function ( state ) {
			const select = getSelect( state );
			const highway = select ? select.value : '';
			const panel = panels.find( function ( item ) {
				return item.dataset.roadStatePanel === state;
			} );

			if ( panel ) {
				panel.querySelectorAll( '[data-road-highway]' ).forEach( function ( card ) {
					card.hidden = Boolean( highway ) && card.dataset.roadHighway !== highway;
				} );
			}

			return highway;
		};

		const activateState = function ( state ) {
			links.forEach( function ( link ) {
				if ( link.dataset.roadStateLink === state ) {
					link.setAttribute( 'aria-current', 'true' );
				} else {
					link.removeAttribute( 'aria-current' );
				}
			} );

			panels.forEach( function ( panel ) {
				panel.hidden = panel.dataset.roadStatePanel !== state;
			} );

			forms.forEach( function ( form ) {
				form.hidden = form.dataset.roadHighwayForm !== state;
			} );

			updateUrl( state, filterCards( state ) );
		};

		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				const state = link.dataset.roadStateLink;
				const panel = panels.find( function ( item ) {
					return item.dataset.roadStatePanel === state;
				} );

				if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || ! panel ) {
					return;
				}

				event.preventDefault();

				if ( 'true' === link.getAttribute( 'aria-current' ) ) {
					return;
				}

				activateState( state );

				if ( status ) {
					announce( ( status.dataset.stateMessage || '' ).replace( '%s', panel.dataset.roadStateLabel || link.textContent.trim() ) );
				}
			} );
		} );

		forms.forEach( function ( form ) {
			const state = form.dataset.roadHighwayForm;
			const select = form.querySelector( '[data-road-highway-select]' );
			const apply = form.querySelector( '[data-road-highway-apply]' );

			if ( apply ) {
				apply.hidden = true;
			}

			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
			} );

			if ( ! select ) {
				return;
			}

			select.addEventListener( 'change', function () {
				const highway = filterCards( state );
				const option = select.options[ select.selectedIndex ];

				updateUrl( state, highway );

				if ( status ) {
					announce( highway && option ? ( status.dataset.highwayMessage || '' ).replace( '%s', option.text ) : status.dataset.allMessage );
				}
			} );
		} );

		root.querySelectorAll( '[data-road-card-toggle]' ).forEach( function ( toggle ) {
			const details = document.getElementById( toggle.getAttribute( 'aria-controls' ) );

			if ( ! details ) {
				return;
			}

			toggle.hidden = false;
			toggle.addEventListener( 'click', function () {
				const expanded = 'true' !== toggle.getAttribute( 'aria-expanded' );

				toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
				details.hidden = ! expanded;
			} );
		} );
	};

	init();
	document.addEventListener( 'ltt:content-replaced', init );
}() );
