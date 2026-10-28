/** Show the selected city's weather panel without reloading the page. */
( function () {
	'use strict';

	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	document.addEventListener( 'click', function ( event ) {
		const tile = event.target.closest( '[data-weather-location]' );
		if ( ! tile || event.defaultPrevented || 0 !== event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}

		const module = tile.closest( '[data-current-weather]' );
		const panel = module ? document.getElementById( tile.getAttribute( 'aria-controls' ) ) : null;

		if ( ! panel || panel.hasAttribute( 'data-weather-unavailable' ) ) {
			return;
		}

		event.preventDefault();

		module.querySelectorAll( '[data-weather-panel]' ).forEach( function ( item ) {
			item.hidden = item !== panel;
		} );
		module.querySelectorAll( '[data-weather-location]' ).forEach( function ( item ) {
			const isActive = item === tile;
			item.classList.toggle( 'is-active', isActive );
			if ( isActive ) {
				item.setAttribute( 'aria-current', 'true' );
			} else {
				item.removeAttribute( 'aria-current' );
			}
		} );

		const url = new URL( tile.href );
		url.hash = '';
		window.history.replaceState( window.history.state, '', url );

		const status = module.querySelector( '[data-current-weather-status]' );
		if ( status ) {
			status.textContent = status.dataset.message.replace( '%s', tile.dataset.weatherName );
		}

		if ( panel.getBoundingClientRect().top < 0 ) {
			panel.scrollIntoView( { behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'start' } );
		}
	} );
}() );
