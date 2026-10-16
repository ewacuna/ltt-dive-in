/**
 * Mapbox enhancement for the Map block.
 *
 * Follows the client's map prototype: gold destination names open a popup,
 * attraction icons show their name on hover, focus, or tap, and theme-owned
 * roads, trails, shields, and labels are drawn over the Mapbox Studio style.
 * Every marker that does something is a native button. When Mapbox cannot
 * load, the text list of destinations is shown instead.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_map || {};
	const canHover = window.matchMedia && window.matchMedia( '(hover: hover)' );
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' );
	const SNOW = {
		density: 0.25,
		intensity: 0.5,
		color: '#ffffff',
		opacity: 0.6,
		'flake-size': 0.3,
		direction: [ 0, 50 ],
		'center-thinning': 0.2,
	};
	// Destination areas farther than this from every destination stay unlinked.
	const AREA_MATCH_METERS = 5000;

	function parse( value, fallback ) {
		try {
			return JSON.parse( value || '' ) || fallback;
		} catch ( error ) {
			return fallback;
		}
	}

	function element( tag, className, text ) {
		const node = document.createElement( tag );

		if ( className ) {
			node.className = className;
		}

		if ( text ) {
			node.textContent = text;
		}

		return node;
	}

	function decorativeImage( className, src, size ) {
		const image = element( 'img', className );

		image.src = src;
		image.alt = '';
		image.width = size;
		image.height = size;
		// The source images are not all square; contain them in the size box,
		// as the prototype's background-size: contain did.
		image.style.width = size + 'px';
		image.style.height = size + 'px';
		image.decoding = 'async';

		return image;
	}

	function initMap( root ) {
		const frame = root.querySelector( '[data-ltt-map-frame]' );
		const canvas = root.querySelector( '[data-ltt-map-canvas]' );
		const fallback = root.querySelector( '[data-ltt-map-fallback]' );
		const status = root.querySelector( '[data-ltt-map-status]' );

		if ( ! frame || ! canvas || ! fallback ) {
			return;
		}

		const places = parse( root.dataset.places, [] );
		const attractions = parse( root.dataset.attractions, [] );
		const cartography = parse( root.dataset.cartography, {} );
		const view = cartography.view || {};

		let map = null;
		let loaded = false;
		let activeDestination = null;
		let restoreFocus = false;
		let activeArea = null;
		let hoveredArea = null;
		const destinations = [];
		const tooltips = [];

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

		/*
		 * Destination areas.
		 */
		function setAreaState( id, highlighted ) {
			if ( null !== id && map && map.getSource( 'ltt-map-areas' ) ) {
				map.setFeatureState( { source: 'ltt-map-areas', id: id }, { highlighted: highlighted } );
			}
		}

		function selectArea( id ) {
			if ( activeArea === id ) {
				return;
			}

			setAreaState( activeArea, activeArea === hoveredArea );
			activeArea = id;
			setAreaState( activeArea, true );
		}

		function linkAreas( collection ) {
			( collection.features || [] ).forEach( function ( feature, index ) {
				const circle = feature.properties && feature.properties[ '@circle' ];
				const center = circle && circle.center;
				let nearest = null;
				let nearestDistance = AREA_MATCH_METERS;

				feature.id = index;

				if ( ! center ) {
					return;
				}

				destinations.forEach( function ( destination ) {
					const distance = new window.mapboxgl.LngLat( center[ 0 ], center[ 1 ] ).distanceTo( new window.mapboxgl.LngLat( destination.place.lngLat[ 0 ], destination.place.lngLat[ 1 ] ) );

					if ( distance < nearestDistance ) {
						nearest = destination;
						nearestDistance = distance;
					}
				} );

				if ( nearest && null === nearest.areaId ) {
					nearest.areaId = index;
				}
			} );

			return collection;
		}

		function addAreas() {
			if ( ! cartography.areas ) {
				return;
			}

			window.fetch( cartography.areas )
				.then( function ( response ) {
					return response.ok ? response.json() : Promise.reject( response.status );
				} )
				.then( function ( collection ) {
					if ( ! map ) {
						return;
					}

					map.addSource( 'ltt-map-areas', { type: 'geojson', data: linkAreas( collection ) } );
					map.addLayer( {
						id: 'ltt-map-areas-hit',
						type: 'fill',
						source: 'ltt-map-areas',
						paint: { 'fill-opacity': 0.001 },
					} );
					map.addLayer( {
						id: 'ltt-map-areas-glow',
						type: 'line',
						source: 'ltt-map-areas',
						paint: {
							'line-color': '#eeb040',
							'line-width': 10,
							'line-blur': 5,
							'line-opacity': [ 'case', [ 'boolean', [ 'feature-state', 'highlighted' ], false ], 0.25, 0 ],
						},
					} );
					map.addLayer( {
						id: 'ltt-map-areas-fill',
						type: 'fill',
						source: 'ltt-map-areas',
						paint: {
							'fill-color': '#eeb040',
							'fill-opacity': [ 'case', [ 'boolean', [ 'feature-state', 'highlighted' ], false ], 0.4, 0 ],
						},
					} );

					// An area opened before the source loaded can now be shown.
					if ( activeDestination ) {
						selectArea( activeDestination.areaId );
					}

					map.on( 'mousemove', 'ltt-map-areas-hit', function ( event ) {
						const id = event.features && event.features.length ? event.features[ 0 ].id : null;

						if ( id === hoveredArea ) {
							return;
						}

						setAreaState( hoveredArea, hoveredArea === activeArea );
						hoveredArea = id;
						setAreaState( hoveredArea, true );
					} );

					map.on( 'mouseleave', 'ltt-map-areas-hit', function () {
						setAreaState( hoveredArea, hoveredArea === activeArea );
						hoveredArea = null;
					} );

					// Selecting an area opens its destination, the same result
					// keyboard users get from the destination name.
					map.on( 'click', 'ltt-map-areas-hit', function ( event ) {
						const id = event.features && event.features.length ? event.features[ 0 ].id : null;
						const match = destinations.find( function ( destination ) {
							return destination.areaId === id;
						} );

						if ( match ) {
							openDestination( match, false );
						}
					} );
				} )
				.catch( function ( error ) {
					if ( window.console ) {
						window.console.warn( '[LTT Map] Destination areas could not load.', error );
					}
				} );
		}

		/*
		 * Destinations.
		 */
		function popupContent( destination ) {
			const place = destination.place;
			const content = element( 'div', 'ltt-map__popup-body' );
			const titleId = root.id + '-popup-title';
			const close = element( 'button', 'ltt-map__popup-close' );
			const closeIcon = element( 'span', '', '×' );

			content.setAttribute( 'role', 'dialog' );
			content.setAttribute( 'aria-labelledby', titleId );

			close.type = 'button';
			close.setAttribute( 'aria-label', strings.close || 'Close destination details' );
			closeIcon.setAttribute( 'aria-hidden', 'true' );
			close.append( closeIcon );
			close.addEventListener( 'click', function () {
				closeDestination( true );
			} );
			content.append( close );

			if ( place.image && place.image.src ) {
				const image = element( 'img', 'ltt-map__popup-image' );

				image.src = place.image.src;
				image.alt = '';
				image.width = place.image.width;
				image.height = place.image.height;
				image.decoding = 'async';

				if ( place.image.srcset ) {
					image.srcset = place.image.srcset;
					image.sizes = place.image.sizes;
				}

				content.append( image );
			}

			const title = element( 'p', 'ltt-map__popup-title', place.name );

			title.id = titleId;
			content.append( title );

			if ( place.region ) {
				content.append( element( 'p', 'ltt-map__popup-region', place.region ) );
			}

			if ( place.description ) {
				content.append( element( 'p', 'ltt-map__popup-text', place.description ) );
			}

			if ( place.link && place.link.url ) {
				const link = element( 'a', 'ltt-map__popup-link ltt-button ltt-button--dark-standard', place.link.title );

				link.href = place.link.url;

				if ( '_blank' === place.link.target ) {
					link.target = '_blank';
					link.rel = 'noopener';
					link.append( element( 'span', 'screen-reader-text', ' ' + ( strings.newTab || '(opens in a new tab)' ) ) );
				}

				content.append( link );
			}

			return content;
		}

		function closeDestination( returnFocus ) {
			if ( ! activeDestination ) {
				return;
			}

			restoreFocus = returnFocus;
			activeDestination.popup.remove();
		}

		function openDestination( destination, fromKeyboard ) {
			if ( activeDestination === destination ) {
				closeDestination( fromKeyboard );
				return;
			}

			closeDestination( false );
			hideTooltips();
			activeDestination = destination;
			destination.element.setAttribute( 'aria-expanded', 'true' );
			destination.popup.setDOMContent( popupContent( destination ) ).addTo( map );
			selectArea( destination.areaId );

			// Move focus into the popup only for keyboard activation, so a mouse
			// click does not show a focus ring inside it.
			if ( fromKeyboard ) {
				const close = destination.popup.getElement().querySelector( '.ltt-map__popup-close' );

				if ( close ) {
					close.focus();
				}
			}

			announce( ( strings.opened || '%s details opened.' ).replace( '%s', destination.place.name ) );
		}

		function createDestinations() {
			places.forEach( function ( place ) {
				const button = element( 'button', 'ltt-map__destination', place.name );
				const destination = { place: place, element: button, areaId: null };

				button.type = 'button';
				button.setAttribute( 'aria-expanded', 'false' );
				button.setAttribute( 'aria-haspopup', 'dialog' );

				destination.popup = new window.mapboxgl.Popup( {
					closeButton: false,
					closeOnClick: true,
					focusAfterOpen: false,
					maxWidth: 'none',
					offset: 18,
					className: 'ltt-map__popup',
				} ).setLngLat( place.lngLat );

				destination.popup.on( 'close', function () {
					button.setAttribute( 'aria-expanded', 'false' );

					if ( activeDestination === destination ) {
						activeDestination = null;
						selectArea( null );

						if ( restoreFocus ) {
							button.focus();
						}
					}

					restoreFocus = false;
				} );

				button.addEventListener( 'click', function ( event ) {
					event.stopPropagation();
					// detail is 0 when a keyboard activated the button.
					openDestination( destination, 0 === event.detail );
				} );

				new window.mapboxgl.Marker( { element: button, anchor: 'center' } ).setLngLat( place.lngLat ).addTo( map );
				destinations.push( destination );
			} );
		}

		/*
		 * Attractions.
		 */
		function hideTooltips( except ) {
			tooltips.forEach( function ( tooltip ) {
				if ( tooltip !== except ) {
					tooltip.hide();
				}
			} );
		}

		function createAttractions() {
			attractions.forEach( function ( attraction ) {
				const button = element( 'button', 'ltt-map__attraction' );
				const image = decorativeImage( 'ltt-map__attraction-icon', attraction.icon, attraction.size );
				const hoverImage = decorativeImage( 'ltt-map__attraction-icon ltt-map__attraction-icon--hover', attraction.hoverIcon, attraction.size );
				const label = element( 'p', 'ltt-map__tooltip-text', attraction.name );
				let hideTimer = 0;

				// The button's accessible name already carries the attraction
				// name; the visible tooltip repeats it for sighted users.
				label.setAttribute( 'aria-hidden', 'true' );
				button.type = 'button';
				button.setAttribute( 'aria-label', attraction.name );
				button.append( image, hoverImage );

				const popup = new window.mapboxgl.Popup( {
					closeButton: false,
					closeOnClick: false,
					focusAfterOpen: false,
					maxWidth: 'none',
					offset: Math.round( attraction.size / 2 ) + 6,
					className: 'ltt-map__tooltip',
				} ).setLngLat( attraction.lngLat ).setDOMContent( label );

				const tooltip = {
					show: function () {
						window.clearTimeout( hideTimer );
						hideTooltips( tooltip );

						if ( ! popup.isOpen() ) {
							popup.addTo( map );

							// WCAG 1.4.13: the pointer can move onto the tooltip
							// without it disappearing.
							popup.getElement().addEventListener( 'mouseenter', function () {
								window.clearTimeout( hideTimer );
							} );
							popup.getElement().addEventListener( 'mouseleave', tooltip.hideSoon );
						}

						button.classList.add( 'is-active' );
					},
					hide: function () {
						window.clearTimeout( hideTimer );
						popup.remove();
						button.classList.remove( 'is-active' );
					},
					hideSoon: function () {
						window.clearTimeout( hideTimer );
						hideTimer = window.setTimeout( function () {
							if ( document.activeElement !== button ) {
								tooltip.hide();
							}
						}, 200 );
					},
					isOpen: function () {
						return popup.isOpen();
					},
				};

				button.addEventListener( 'mouseenter', function () {
					if ( canHover && canHover.matches ) {
						tooltip.show();
					}
				} );
				button.addEventListener( 'mouseleave', function () {
					if ( canHover && canHover.matches ) {
						tooltip.hideSoon();
					}
				} );
				button.addEventListener( 'focus', tooltip.show );
				button.addEventListener( 'blur', tooltip.hideSoon );
				button.addEventListener( 'click', function ( event ) {
					event.stopPropagation();

					// Touch screens toggle on tap; hover and focus already show it.
					if ( canHover && canHover.matches && 0 !== event.detail ) {
						return;
					}

					if ( tooltip.isOpen() && 0 === event.detail ) {
						tooltip.hide();
					} else {
						tooltip.show();
					}
				} );

				new window.mapboxgl.Marker( { element: button, anchor: 'bottom' } ).setLngLat( attraction.lngLat ).addTo( map );
				tooltips.push( tooltip );
			} );
		}

		/*
		 * Theme cartography.
		 */
		function addLines() {
			( cartography.lines || [] ).forEach( function ( line ) {
				const id = 'ltt-map-line-' + line.id;

				map.addSource( id, { type: 'geojson', data: line.url } );
				map.addLayer( {
					id: id,
					type: 'line',
					source: id,
					paint: { 'line-color': line.color, 'line-width': line.width },
				} );
			} );
		}

		function addLabels() {
			( cartography.shields || [] ).forEach( function ( shield ) {
				const image = decorativeImage( 'ltt-map__shield', shield.image, shield.size );

				new window.mapboxgl.Marker( { element: image, anchor: 'center' } ).setLngLat( shield.lngLat ).addTo( map );
			} );

			( cartography.towns || [] ).forEach( function ( town ) {
				new window.mapboxgl.Marker( { element: element( 'p', 'ltt-map__town', town.name ), anchor: 'center' } ).setLngLat( town.lngLat ).addTo( map );
			} );

			( cartography.gateways || [] ).forEach( function ( gateway ) {
				const label = element( 'p', 'ltt-map__gateway ltt-map__gateway--' + gateway.anchor );

				( gateway.lines || [] ).forEach( function ( line, index ) {
					if ( index ) {
						label.append( document.createElement( 'br' ) );
					}

					label.append( line );
				} );

				if ( gateway.subtitle ) {
					label.append( element( 'span', 'ltt-map__gateway-subtitle', gateway.subtitle ) );
				}

				new window.mapboxgl.Marker( { element: label, anchor: gateway.anchor } ).setLngLat( gateway.lngLat ).addTo( map );
			} );
		}

		/*
		 * Snow, with a play/pause control styled like the native Mapbox
		 * controls. WCAG 2.2.2 requires a way to pause continuous motion.
		 */
		function addSnowControl() {
			if ( ! root.hasAttribute( 'data-snow' ) || 'function' !== typeof map.setSnow ) {
				return;
			}

			const container = element( 'div', 'mapboxgl-ctrl mapboxgl-ctrl-group' );
			const button = element( 'button', 'ltt-map__snow-toggle' );
			const icon = element( 'span', 'mapboxgl-ctrl-icon' );
			let playing = false;

			function setPlaying( on ) {
				const label = on ? strings.pauseSnow || 'Pause snow' : strings.playSnow || 'Play snow';

				playing = on;
				map.setSnow( on ? SNOW : null );
				button.classList.toggle( 'is-paused', ! on );
				button.setAttribute( 'aria-label', label );
				button.title = label;
			}

			icon.setAttribute( 'aria-hidden', 'true' );
			button.type = 'button';
			button.append( icon );
			button.addEventListener( 'click', function () {
				setPlaying( ! playing );
			} );
			container.append( button );

			map.addControl( {
				onAdd: function () {
					return container;
				},
				onRemove: function () {
					container.remove();
				},
			}, 'top-right' );

			setPlaying( ! ( reducedMotion && reducedMotion.matches ) );

			if ( reducedMotion && reducedMotion.addEventListener ) {
				reducedMotion.addEventListener( 'change', function () {
					if ( reducedMotion.matches ) {
						setPlaying( false );
					}
				} );
			}
		}

		/*
		 * Map.
		 */
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
				projection: 'globe',
				center: view.center,
				zoom: view.zoom,
				minZoom: view.minZoom,
				maxZoom: view.maxZoom,
				maxBounds: view.maxBounds,
				// One-finger drags scroll the page on touch screens; two fingers
				// move the map. The mouse wheel never zooms, as in the prototype.
				cooperativeGestures: true,
				scrollZoom: false,
			} );
		} catch ( error ) {
			showFallback( error && error.message );
			return;
		}

		map.touchZoomRotate.disableRotation();
		map.addControl( new window.mapboxgl.NavigationControl(), 'top-right' );

		map.on( 'load', function () {
			loaded = true;
			map.getCanvas().setAttribute( 'aria-label', strings.canvas || 'Lake Tahoe map. Use the arrow keys to pan and plus or minus to zoom.' );
			addLines();
			addLabels();
			createAttractions();
			createDestinations();
			addAreas();
			addSnowControl();
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
			hideTooltips();
		} );

		// Document-level so Escape also works after a mouse click, which may not
		// move focus into the map (Safari). Only open content reacts.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key ) {
				return;
			}

			if ( activeDestination ) {
				closeDestination( true );
			} else {
				hideTooltips();
			}
		} );
	}

	document.querySelectorAll( '[data-ltt-map]' ).forEach( initMap );
}() );
