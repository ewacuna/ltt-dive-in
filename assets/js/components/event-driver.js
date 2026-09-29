/**
 * Event Driver interactions.
 *
 * - Hero Featured Events: one-slide Swiper with arrows and dots.
 * - Events Carousel: free-width card carousel with arrows and dots.
 * - Listed Events: filters and Load More without page reloads, and a card
 *   carousel below 768px. The server-rendered GET form remains the fallback.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_event_driver || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const mobileQuery = window.matchMedia ? window.matchMedia( '(max-width: 767.98px)' ) : null;

	const format = function ( template, values ) {
		let index = 0;

		return String( template || '' ).replace( /%(\d\$)?d/g, function ( match, position ) {
			const value = position ? values[ parseInt( position, 10 ) - 1 ] : values[ index ];

			index += 1;

			return value;
		} );
	};

	/*
	 * Swiper 14 leaves off-screen slides in the tab order and the
	 * accessibility tree. `inert` removes both; `swiper-slide-visible`
	 * requires `watchSlidesProgress`.
	 */
	const setSlidesInert = function ( instance ) {
		instance.slides.forEach( function ( slide ) {
			const hidden = ! slide.classList.contains( 'swiper-slide-visible' );

			if ( slide.inert !== hidden ) {
				slide.inert = hidden;
			}
		} );
	};

	const clearSlidesInert = function ( root ) {
		root.querySelectorAll( '.swiper-slide' ).forEach( function ( slide ) {
			slide.inert = false;
		} );
	};

	const paginationOptions = function ( element, label ) {
		return {
			el: element,
			clickable: true,
			bulletElement: 'button',
			bulletClass: 'ltt-carousel-indicator',
			bulletActiveClass: 'is-active',
			renderBullet: function ( index, className ) {
				return '<button class="' + className + '" type="button" aria-label="' + String( label ).replace( '{{index}}', index + 1 ) + '"></button>';
			},
		};
	};

	const updatePaginationState = function ( instance ) {
		if ( ! instance.pagination || ! instance.pagination.bullets ) {
			return;
		}

		instance.pagination.bullets.forEach( function ( bullet ) {
			bullet.setAttribute( 'aria-current', bullet.classList.contains( 'is-active' ) ? 'true' : 'false' );
		} );
	};

	const a11yOptions = function ( previousLabel, nextLabel, bulletLabel ) {
		return {
			enabled: true,
			containerRoleDescriptionMessage: strings.carousel || 'carousel',
			itemRoleDescriptionMessage: strings.slide || 'slide',
			slideLabelMessage: strings.slideLabel || '{{index}} of {{slidesLength}}',
			prevSlideMessage: previousLabel,
			nextSlideMessage: nextLabel,
			paginationBulletMessage: bulletLabel,
		};
	};

	/**
	 * Create a card carousel for a viewport with `.swiper-slide` cards.
	 *
	 * @param {HTMLElement} root Section containing the carousel controls.
	 * @return {Object|null} Swiper instance.
	 */
	const createCardCarousel = function ( root ) {
		const viewport = root.querySelector( '[data-event-driver-viewport]' );
		const controls = root.querySelector( '[data-event-driver-controls]' );
		const previous = root.querySelector( '[data-event-driver-previous]' );
		const next = root.querySelector( '[data-event-driver-next]' );
		const pagination = root.querySelector( '[data-event-driver-pagination]' );

		if ( ! window.Swiper || ! viewport || ! controls || ! previous || ! next || ! pagination ) {
			return null;
		}

		const toggleControls = function ( instance ) {
			controls.hidden = instance.isLocked;
		};

		controls.hidden = false;

		return new window.Swiper( viewport, {
			slidesPerView: 'auto',
			spaceBetween: 24,
			watchSlidesProgress: true,
			watchOverflow: true,
			speed: reducedMotion ? 0 : 350,
			breakpoints: {
				768: {
					spaceBetween: 32,
				},
			},
			a11y: a11yOptions( previous.getAttribute( 'aria-label' ), next.getAttribute( 'aria-label' ), strings.goToEvent || 'Go to event {{index}}' ),
			navigation: {
				prevEl: previous,
				nextEl: next,
			},
			pagination: paginationOptions( pagination, strings.goToEvent || 'Go to event {{index}}' ),
			on: {
				afterInit: function ( instance ) {
					setSlidesInert( instance );
					toggleControls( instance );
				},
				resize: setSlidesInert,
				update: setSlidesInert,
				slideChangeTransitionStart: setSlidesInert,
				lock: toggleControls,
				unlock: toggleControls,
				paginationUpdate: updatePaginationState,
				paginationRender: updatePaginationState,
			},
		} );
	};

	const initHero = function ( section ) {
		const viewport = section.querySelector( '[data-event-driver-viewport]' );
		const previous = section.querySelector( '[data-event-driver-previous]' );
		const next = section.querySelector( '[data-event-driver-next]' );
		const pagination = section.querySelector( '[data-event-driver-pagination]' );

		if ( ! window.Swiper || ! viewport || viewport.classList.contains( 'swiper-initialized' ) ) {
			return;
		}

		new window.Swiper( viewport, {
			slidesPerView: 1,
			spaceBetween: 0,
			speed: reducedMotion ? 0 : 350,
			loop: true,
			watchSlidesProgress: true,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: a11yOptions( previous.getAttribute( 'aria-label' ), next.getAttribute( 'aria-label' ), strings.goToFeatured || 'Go to featured event {{index}}' ),
			navigation: {
				prevEl: previous,
				nextEl: next,
			},
			pagination: paginationOptions( pagination, strings.goToFeatured || 'Go to featured event {{index}}' ),
			on: {
				afterInit: function ( instance ) {
					section.classList.add( 'is-ready' );
					setSlidesInert( instance );
				},
				slideChangeTransitionStart: setSlidesInert,
				resize: setSlidesInert,
				paginationUpdate: updatePaginationState,
				paginationRender: updatePaginationState,
			},
		} );
	};

	const initListed = function ( section ) {
		const form = section.querySelector( '[data-event-driver-filters]' );
		const grid = section.querySelector( '[data-event-driver-grid]' );
		const status = section.querySelector( '[data-event-driver-status]' );
		const empty = section.querySelector( '[data-event-driver-empty]' );
		const loadMore = section.querySelector( '[data-event-driver-load-more]' );
		const search = section.querySelector( '[data-event-driver-filter="search"]' );
		let offset = loadMore ? parseInt( loadMore.dataset.offset, 10 ) || 0 : 0;
		let carousel = null;
		let controller = null;
		let searchTimer = 0;
		let lastSearch = search ? search.value.trim() : '';

		if ( ! form || ! grid || ! strings.endpoint || ! window.fetch ) {
			return;
		}

		const syncCarousel = function () {
			const shouldRun = !! ( mobileQuery && mobileQuery.matches && grid.children.length );

			if ( shouldRun && ! carousel ) {
				carousel = createCardCarousel( section );
			} else if ( ! shouldRun && carousel ) {
				carousel.destroy( true, true );
				carousel = null;
				clearSlidesInert( section );
				section.querySelector( '[data-event-driver-controls]' ).hidden = true;
			} else if ( carousel ) {
				carousel.update();
			}
		};

		const announce = function ( message ) {
			if ( status ) {
				status.textContent = message;
			}
		};

		const request = function ( requestOffset ) {
			const params = new URLSearchParams( {
				post: section.dataset.post,
				block: section.dataset.block,
				offset: String( requestOffset ),
			} );
			const data = new FormData( form );

			params.set( 'category', data.get( 'event_category' ) || '' );
			params.set( 'date', data.get( 'event_date' ) || '' );
			params.set( 'search', ( data.get( 'event_search' ) || '' ).toString().trim() );

			if ( controller ) {
				controller.abort();
			}

			controller = window.AbortController ? new window.AbortController() : null;

			return window.fetch( strings.endpoint + ( strings.endpoint.indexOf( '?' ) === -1 ? '?' : '&' ) + params.toString(), {
				credentials: 'same-origin',
				headers: { Accept: 'application/json' },
				signal: controller ? controller.signal : undefined,
			} ).then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Request failed' );
				}

				return response.json();
			} );
		};

		const setHasMore = function ( hasMore, nextOffset ) {
			offset = nextOffset;

			if ( loadMore ) {
				loadMore.hidden = ! hasMore;
				loadMore.disabled = false;
				loadMore.dataset.offset = String( nextOffset );
			}
		};

		const applyFilters = function () {
			grid.classList.add( 'is-loading' );
			grid.setAttribute( 'aria-busy', 'true' );
			announce( strings.loading || '' );

			request( 0 ).then( function ( response ) {
				grid.innerHTML = response.html || '';
				empty.hidden = response.count > 0;
				setHasMore( response.hasMore, response.nextOffset );

				if ( carousel ) {
					carousel.update();
					carousel.slideTo( 0, 0 );
				}

				syncCarousel();
				announce( response.count ? format( strings.showing, [ response.count, Math.max( response.total, response.count ) ] ) : strings.noResults || '' );
			} ).catch( function ( error ) {
				if ( error && 'AbortError' === error.name ) {
					return;
				}

				announce( strings.error || '' );
			} ).finally( function () {
				grid.classList.remove( 'is-loading' );
				grid.removeAttribute( 'aria-busy' );
			} );
		};

		const appendEvents = function () {
			loadMore.disabled = true;
			announce( strings.loading || '' );

			request( offset ).then( function ( response ) {
				const firstIndex = grid.children.length;

				grid.insertAdjacentHTML( 'beforeend', response.html || '' );
				setHasMore( response.hasMore, response.nextOffset );
				syncCarousel();

				const firstNew = grid.children[ firstIndex ];

				if ( firstNew ) {
					const link = firstNew.querySelector( '.ltt-event-card__link' );

					if ( link ) {
						link.focus();
					} else {
						firstNew.tabIndex = -1;
						firstNew.focus();
					}
				}

				announce( format( strings.loaded, [ response.count ] ) );
			} ).catch( function ( error ) {
				loadMore.disabled = false;

				if ( error && 'AbortError' === error.name ) {
					return;
				}

				announce( strings.error || '' );
			} );
		};

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			if ( event.submitter && event.submitter === loadMore ) {
				appendEvents();
				return;
			}

			window.clearTimeout( searchTimer );
			lastSearch = search ? search.value.trim() : '';
			applyFilters();
		} );

		form.addEventListener( 'change', function ( event ) {
			if ( event.target.matches( 'select[data-event-driver-filter]' ) ) {
				applyFilters();
			}
		} );

		if ( search ) {
			search.addEventListener( 'input', function () {
				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function () {
					const value = search.value.trim();

					if ( value === lastSearch || ( value.length > 0 && value.length < 2 ) ) {
						return;
					}

					lastSearch = value;
					applyFilters();
				}, 400 );
			} );
		}

		/* Browsers without `SubmitEvent.submitter` still reach Load More here. */
		if ( loadMore ) {
			loadMore.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				if ( ! loadMore.disabled ) {
					appendEvents();
				}
			} );
		}

		if ( mobileQuery ) {
			if ( mobileQuery.addEventListener ) {
				mobileQuery.addEventListener( 'change', syncCarousel );
			} else if ( mobileQuery.addListener ) {
				mobileQuery.addListener( syncCarousel );
			}
		}

		syncCarousel();
	};

	document.querySelectorAll( '[data-event-driver-hero]' ).forEach( initHero );
	document.querySelectorAll( '[data-event-driver-carousel]' ).forEach( function ( section ) {
		createCardCarousel( section );
	} );
	document.querySelectorAll( '[data-event-driver-listed]' ).forEach( initListed );
}() );
