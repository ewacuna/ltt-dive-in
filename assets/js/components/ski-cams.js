/**
 * Ski cams resort filters.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const init = function () {
		const root = document.querySelector( '[data-ski-cams]' );

		if ( ! root || root.dataset.skiCamsReady ) {
			return;
		}

		root.dataset.skiCamsReady = 'true';

		const filters = Array.from( root.querySelectorAll( '[data-ski-cams-filter]' ) );
		const panels = Array.from( root.querySelectorAll( '[data-ski-cams-panel]' ) );
		const status = root.querySelector( '[data-ski-cams-status]' );
		const message = root.dataset.skiCamsMessage || 'Showing %s cams.';

		filters.forEach( function ( filter ) {
			filter.addEventListener( 'click', function ( event ) {
				if ( event.defaultPrevented || 0 !== event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
					return;
				}

				const slug = filter.dataset.skiCamsFilter;
				const panel = panels.find( function ( item ) {
					return item.dataset.skiCamsPanel === slug;
				} );

				if ( ! panel ) {
					return;
				}

				event.preventDefault();

				panels.forEach( function ( item ) {
					item.hidden = item !== panel;
				} );
				filters.forEach( function ( item ) {
					if ( item === filter ) {
						item.setAttribute( 'aria-current', 'true' );
					} else {
						item.removeAttribute( 'aria-current' );
					}
				} );

				if ( window.history && window.history.replaceState ) {
					const url = new URL( window.location.href );
					url.searchParams.set( 'ski_resort', slug );
					window.history.replaceState( window.history.state, '', url );
				}

				if ( status ) {
					status.textContent = '';
					window.setTimeout( function () {
						status.textContent = message.replace( '%s', panel.dataset.skiCamsName || '' );
					}, 50 );
				}
			} );
		} );
	};

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	document.addEventListener( 'ltt:content-replaced', init );
}() );
