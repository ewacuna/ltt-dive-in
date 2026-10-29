/** Show the selected city's weather panel and view without reloading the page. */
( function () {
	'use strict';

	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function isPlainClick( event ) {
		return ! event.defaultPrevented && 0 === event.button && ! event.metaKey && ! event.ctrlKey && ! event.shiftKey && ! event.altKey;
	}

	function setCurrent( items, current ) {
		items.forEach( function ( item ) {
			if ( item === current ) {
				item.setAttribute( 'aria-current', 'true' );
			} else {
				item.removeAttribute( 'aria-current' );
			}
		} );
	}

	function showView( panel, view ) {
		panel.querySelectorAll( '[data-weather-view]' ).forEach( function ( item ) {
			item.hidden = item.dataset.weatherView !== view;
		} );

		const links = panel.querySelectorAll( '[data-weather-view-link]' );
		setCurrent( links, Array.prototype.find.call( links, function ( link ) {
			return link.dataset.weatherViewLink === view;
		} ) );
	}

	function updateUrl( href, view ) {
		const url = new URL( href );
		url.hash = '';
		if ( view && 'current' !== view ) {
			url.searchParams.set( 'weather_view', view );
		}
		window.history.replaceState( window.history.state, '', url );
	}

	function announce( module, message, value ) {
		const status = module.querySelector( '[data-current-weather-status]' );
		if ( status ) {
			status.textContent = status.dataset[ message ].replace( '%s', value );
		}
	}

	document.addEventListener( 'click', function ( event ) {
		const link = event.target.closest( '[data-weather-view-link]' );
		if ( ! link || ! isPlainClick( event ) ) {
			return;
		}

		const module = link.closest( '[data-current-weather]' );
		const panel = link.closest( '[data-weather-panel]' );

		if ( ! module || ! panel ) {
			return;
		}

		event.preventDefault();
		showView( panel, link.dataset.weatherViewLink );
		updateUrl( link.href );
		announce( module, 'viewMessage', link.textContent.trim() );
		link.scrollIntoView( { behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'nearest' } );
	} );

	document.addEventListener( 'click', function ( event ) {
		const tile = event.target.closest( '[data-weather-location]' );
		if ( ! tile || ! isPlainClick( event ) ) {
			return;
		}

		const module = tile.closest( '[data-current-weather]' );
		const panel = module ? document.getElementById( tile.getAttribute( 'aria-controls' ) ) : null;

		if ( ! panel || panel.hasAttribute( 'data-weather-unavailable' ) ) {
			return;
		}

		event.preventDefault();

		// Keep the view the visitor had open, such as the 10-day forecast.
		const openView = module.querySelector( '[data-weather-panel]:not([hidden]) [data-weather-view]:not([hidden])' );
		const view = openView ? openView.dataset.weatherView : 'current';

		module.querySelectorAll( '[data-weather-panel]' ).forEach( function ( item ) {
			item.hidden = item !== panel;
		} );
		showView( panel, view );

		const tiles = module.querySelectorAll( '[data-weather-location]' );
		tiles.forEach( function ( item ) {
			item.classList.toggle( 'is-active', item === tile );
		} );
		setCurrent( tiles, tile );

		updateUrl( tile.href, view );
		announce( module, 'message', tile.dataset.weatherName );

		if ( panel.getBoundingClientRect().top < 0 ) {
			panel.scrollIntoView( { behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'start' } );
		}
	} );
}() );
