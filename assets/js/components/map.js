/**
 * Mapbox enhancement for the Map block.
 *
 * Places are native buttons placed on the map. Selecting one shows its details
 * in a card and announces the change; Escape or the close button returns focus
 * to the marker. When Mapbox cannot load, the text list of places is shown.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	// Whole lake, padded so the shoreline is never cropped.
	const LAKE_BOUNDS = [ [ -120.17, 38.92 ], [ -119.92, 39.26 ] ];
	const strings = window.ltt_dive_in_map || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function duration( value ) {
		return reducedMotion && reducedMotion.matches ? 0 : value;
	}

	function format( template, value ) {
		return String( template || '' ).replace( '%s', value );
	}

	function initMap( root ) {
		const frame = root.querySelector( '[data-ltt-map-frame]' );
		const canvas = root.querySelector( '[data-ltt-map-canvas]' );
		const fallback = root.querySelector( '[data-ltt-map-fallback]' );
		const card = root.querySelector( '[data-ltt-map-card]' );
		const cardTitle = root.querySelector( '[data-ltt-map-card-title]' );
		const cardRegion = root.querySelector( '[data-ltt-map-card-region]' );
		const cardText = root.querySelector( '[data-ltt-map-card-text]' );
		const cardLink = root.querySelector( '[data-ltt-map-card-link]' );
		const cardClose = root.querySelector( '[data-ltt-map-card-close]' );
		const resetButton = root.querySelector( '[data-ltt-map-reset]' );
		const status = root.querySelector( '[data-ltt-map-status]' );

		if ( ! frame || ! canvas || ! fallback || ! card || ! cardTitle || ! cardClose || ! resetButton ) {
			return;
		}

		let places = [];

		try {
			places = JSON.parse( root.dataset.places || '[]' );
		} catch ( error ) {
			places = [];
		}

		let map = null;
		let loaded = false;
		let activeMarker = null;

		function showFallback( reason ) {
			if ( reason && window.console ) {
				window.console.warn( '[LTT Map]', reason );
			}

			root.classList.add( 'is-fallback' );
			frame.hidden = true;
			fallback.hidden = false;

			if ( map ) {
				map.remove();
				map = null;
			}
		}

		function announce( message ) {
			if ( ! status ) {
				return;
			}

			status.textContent = '';
			window.requestAnimationFrame( function () {
				status.textContent = message;
			} );
		}

		function initialBounds() {
			const bounds = new window.mapboxgl.LngLatBounds( LAKE_BOUNDS[ 0 ], LAKE_BOUNDS[ 1 ] );

			places.forEach( function ( place ) {
				bounds.extend( place.lngLat );
			} );

			return bounds;
		}

		function setText( element, value ) {
			if ( ! element ) {
				return;
			}

			element.textContent = value || '';
			element.hidden = ! value;
		}

		function setLink( link ) {
			if ( ! cardLink ) {
				return;
			}

			cardLink.hidden = ! link || ! link.url;
			cardLink.textContent = '';

			if ( cardLink.hidden ) {
				cardLink.removeAttribute( 'href' );
				return;
			}

			cardLink.href = link.url;
			cardLink.append( link.title );

			if ( '_blank' === link.target ) {
				const note = document.createElement( 'span' );

				cardLink.target = '_blank';
				cardLink.rel = 'noopener';
				note.className = 'screen-reader-text';
				note.textContent = ' ' + ( strings.newTab || '(opens in a new tab)' );
				cardLink.append( note );
			} else {
				cardLink.removeAttribute( 'target' );
				cardLink.removeAttribute( 'rel' );
			}
		}

		function closeCard( returnFocus ) {
			if ( card.hidden ) {
				return;
			}

			card.hidden = true;

			if ( activeMarker ) {
				activeMarker.element.setAttribute( 'aria-expanded', 'false' );

				if ( returnFocus ) {
					activeMarker.element.focus();
				}
			}

			activeMarker = null;
		}

		function selectPlace( entry ) {
			if ( activeMarker ) {
				activeMarker.element.setAttribute( 'aria-expanded', 'false' );
			}

			activeMarker = entry;
			entry.element.setAttribute( 'aria-expanded', 'true' );
			cardTitle.textContent = entry.place.name;
			setText( cardRegion, entry.place.region );
			setText( cardText, entry.place.description );
			setLink( entry.place.link );
			card.hidden = false;
			map.easeTo( {
				center: entry.place.lngLat,
				zoom: Math.max( map.getZoom(), 10.5 ),
				duration: duration( 600 ),
			} );
			announce( format( strings.selected || '%s selected. Details are shown on the map.', entry.place.name ) );
		}

		function createMarkers() {
			places.forEach( function ( place ) {
				const element = document.createElement( 'button' );
				const dot = document.createElement( 'span' );
				const label = document.createElement( 'span' );
				const entry = { place: place, element: element };

				element.type = 'button';
				element.className = 'ltt-map__marker';
				element.setAttribute( 'aria-label', place.region ? place.name + ', ' + place.region : place.name );
				element.setAttribute( 'aria-controls', card.id );
				element.setAttribute( 'aria-expanded', 'false' );
				dot.className = 'ltt-map__marker-dot';
				dot.setAttribute( 'aria-hidden', 'true' );
				label.className = 'ltt-map__marker-label';
				label.setAttribute( 'aria-hidden', 'true' );
				label.textContent = place.name;
				element.append( dot, label );

				element.addEventListener( 'click', function ( event ) {
					event.stopPropagation();

					if ( activeMarker === entry ) {
						closeCard( true );
					} else {
						selectPlace( entry );
					}
				} );

				new window.mapboxgl.Marker( { element: element, anchor: 'left' } ).setLngLat( place.lngLat ).addTo( map );
			} );
		}

		if ( ! window.mapboxgl ) {
			showFallback( 'Mapbox GL JS could not be loaded.' );
			return;
		}

		if ( ! root.dataset.accessToken ) {
			showFallback( 'No Mapbox access token is configured.' );
			return;
		}

		if ( 'function' === typeof window.mapboxgl.supported && ! window.mapboxgl.supported() ) {
			showFallback( 'This browser does not support WebGL.' );
			return;
		}

		window.mapboxgl.accessToken = root.dataset.accessToken;

		try {
			map = new window.mapboxgl.Map( {
				container: canvas,
				style: root.dataset.mapStyle,
				bounds: initialBounds(),
				fitBoundsOptions: { padding: 40 },
				minZoom: 8.5,
				maxZoom: 15,
				cooperativeGestures: true,
				dragRotate: false,
				pitchWithRotate: false,
			} );
		} catch ( error ) {
			showFallback( error && error.message );
			return;
		}

		map.touchZoomRotate.disableRotation();
		map.addControl( new window.mapboxgl.NavigationControl( { showCompass: false } ), 'top-right' );

		map.on( 'load', function () {
			loaded = true;
			map.getCanvas().setAttribute( 'aria-label', strings.canvas || 'Lake Tahoe map. Use the arrow keys to pan and plus or minus to zoom.' );
			resetButton.hidden = false;
			createMarkers();
		} );

		map.on( 'error', function ( event ) {
			const error = event && event.error;
			const code = error && error.status;

			if ( loaded ) {
				return;
			}

			if ( 401 === code ) {
				showFallback( 'Mapbox rejected the access token (401).' );
			} else if ( 403 === code || 404 === code ) {
				showFallback( 'The access token cannot load the map style or tilesets (' + code + '). Check the token account and URL restrictions.' );
			} else {
				showFallback( ( error && error.message ) || 'The map could not load.' );
			}
		} );

		map.on( 'click', function () {
			closeCard( false );
		} );

		cardClose.addEventListener( 'click', function () {
			closeCard( true );
		} );

		resetButton.addEventListener( 'click', function () {
			closeCard( false );
			map.fitBounds( initialBounds(), { padding: 40, duration: duration( 600 ) } );
			announce( strings.reset || 'Map view reset to the whole lake.' );
		} );

		// Document-level so Escape also works after a mouse click, which may not
		// move focus to the marker (Safari). Only an open card reacts.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! card.hidden ) {
				closeCard( true );
			}
		} );
	}

	document.querySelectorAll( '[data-ltt-map]' ).forEach( initMap );
}() );
