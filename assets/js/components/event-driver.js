/**
 * Event Driver interactions.
 *
 * - Hero Featured Events: one-slide Swiper with arrows and dots.
 * - Events Carousel: free-width card carousel with arrows and dots.
 * - Listed Events: filters and Load More without page reloads, and a card
 *   carousel below 768px. In the carousel, Load More is hidden: the next batch
 *   loads near the last cards, and Next (or a swipe) at the end loads and
 *   advances. The server-rendered GET form remains the fallback.
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

	/**
	 * Show at most `max` dots: a window that follows the active dot, with the
	 * edge dots smaller when more dots are hidden beyond them.
	 *
	 * @param {Object} instance Swiper instance.
	 * @param {number} max      Visible dots.
	 */
	const limitBullets = function ( instance, max ) {
		const bullets = instance.pagination && instance.pagination.bullets ? Array.from( instance.pagination.bullets ) : [];
		const active = Math.max( 0, bullets.findIndex( function ( bullet ) {
			return bullet.classList.contains( 'is-active' );
		} ) );
		const first = Math.max( 0, Math.min( active - Math.floor( max / 2 ), bullets.length - max ) );
		const last = first + max - 1;

		bullets.forEach( function ( bullet, index ) {
			bullet.hidden = index < first || index > last;
			bullet.classList.toggle( 'is-edge', ( index === first && first > 0 ) || ( index === last && last < bullets.length - 1 ) );
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
	 * @param {HTMLElement} root       Section containing the carousel controls.
	 * @param {number}      maxBullets Optional maximum number of visible dots.
	 * @return {Object|null} Swiper instance.
	 */
	const createCardCarousel = function ( root, maxBullets ) {
		const viewport = root.querySelector( '[data-event-driver-viewport]' );
		const controls = root.querySelector( '[data-event-driver-controls]' );
		const previous = root.querySelector( '[data-event-driver-previous]' );
		const next = root.querySelector( '[data-event-driver-next]' );
		const pagination = root.querySelector( '[data-event-driver-pagination]' );
		const expandsOnInteraction = root.hasAttribute( 'data-event-driver-carousel' );

		if ( ! window.Swiper || ! viewport || ! controls || ! previous || ! next || ! pagination ) {
			return null;
		}

		const toggleControls = function ( instance ) {
			// Widening can make every card fit. Keep focus visible if its controls disappear.
			if ( expandsOnInteraction && instance.isLocked && controls.contains( document.activeElement ) ) {
				const link = instance.slides[ instance.activeIndex ].querySelector( 'a[href]' );

				if ( link ) {
					link.focus( { preventScroll: true } );
				}
			}

			controls.hidden = instance.isLocked;
		};

		const engageCarousel = function ( instance ) {
			if ( ! expandsOnInteraction || root.classList.contains( 'event-driver--carousel-engaged' ) ) {
				return;
			}

			root.classList.add( 'event-driver--carousel-engaged' );
			instance.update();
		};

		controls.hidden = false;

		const instance = new window.Swiper( viewport, {
			slidesPerView: 'auto',
			spaceBetween: 24,
			watchSlidesProgress: true,
			watchOverflow: true,
			// Rewind instead of loop: loop reorders slides, which would break Load More appends.
			rewind: true,
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
				sliderFirstMove: engageCarousel,
				setTranslate: function ( instance ) {
					if ( expandsOnInteraction ) {
						setSlidesInert( instance );
					}
				},
				slideChangeTransitionStart: setSlidesInert,
				lock: toggleControls,
				unlock: toggleControls,
				paginationUpdate: function ( instance ) {
					updatePaginationState( instance );

					if ( maxBullets ) {
						limitBullets( instance, maxBullets );
					}
				},
				paginationRender: function ( instance ) {
					updatePaginationState( instance );

					if ( maxBullets ) {
						limitBullets( instance, maxBullets );
					}
				},
			},
		} );

		if ( expandsOnInteraction ) {
			root.addEventListener( 'click', function ( event ) {
				if ( root.classList.contains( 'event-driver--carousel-engaged' ) ) {
					return;
				}

				const button = event.target.closest( 'button' );
				const bulletIndex = Array.from( instance.pagination.bullets ).indexOf( button );

				if ( button !== previous && button !== next && -1 === bulletIndex ) {
					return;
				}

				const restoreBulletFocus = -1 !== bulletIndex && document.activeElement === button;

				// Updating the width can replace pagination buttons. Handle this first action
				// here so Swiper uses the new dimensions and does not navigate twice.
				event.preventDefault();
				event.stopPropagation();
				engageCarousel( instance );

				if ( button === previous ) {
					instance.slidePrev();
				} else if ( button === next ) {
					instance.slideNext();
				} else {
					instance.slideTo( bulletIndex );
				}

				if ( restoreBulletFocus && ! controls.hidden ) {
					const bullet = instance.pagination.bullets[ Math.min( bulletIndex, instance.pagination.bullets.length - 1 ) ];

					if ( bullet ) {
						bullet.focus( { preventScroll: true } );
					}
				}
			}, true );
		}

		return instance;
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
		const next = section.querySelector( '[data-event-driver-next]' );
		const nextLabel = next ? next.getAttribute( 'aria-label' ) : '';
		let offset = loadMore ? parseInt( loadMore.dataset.offset, 10 ) || 0 : 0;
		let carousel = null;
		let controller = null;
		let filtering = false;
		let loadingMore = false;
		let prefetchPaused = false;
		let pendingAdvance = -1;
		let touchStartedAtEnd = false;
		let requestSequence = 0;
		let searchTimer = 0;
		let lastSearch = search ? search.value.trim() : '';

		if ( ! form || ! grid || ! empty || ! strings.endpoint || ! window.fetch ) {
			return;
		}

		const syncEmptyState = function () {
			const count = grid.querySelectorAll( '[data-event-card]' ).length;

			empty.hidden = count > 0;

			if ( ! count ) {
				const filters = new FormData( form );
				const wholeMonth = empty.dataset.monthEmpty && ! filters.getAll( 'event_category[]' ).length && ! filters.get( 'event_date' ) && ! ( filters.get( 'event_search' ) || '' ).toString().trim();

				empty.textContent = wholeMonth ? empty.dataset.monthEmpty : strings.noResults || '';
			}

			return count;
		};

		const hasMore = function () {
			return !! loadMore && ! loadMore.hidden;
		};

		/* Busy state on Next while a visitor waits at the last card for the next batch. */
		const syncNextState = function () {
			if ( ! next ) {
				return;
			}

			const waiting = !! carousel && loadingMore && -1 !== pendingAdvance;

			next.classList.toggle( 'is-loading', waiting );
			next.setAttribute( 'aria-busy', waiting ? 'true' : 'false' );
			next.setAttribute( 'aria-label', waiting ? strings.loadingMore || nextLabel : nextLabel );
		};

		const prefetch = function () {
			if ( ! carousel || prefetchPaused || loadingMore || filtering || ! hasMore() || document.hidden ) {
				return;
			}

			if ( carousel.isEnd || carousel.activeIndex >= carousel.slides.length - 2 ) {
				appendEvents( 'prefetch' );
			}
		};

		/* Next at the last card: load the next batch and move to it. */
		const advancePastEnd = function () {
			if ( loadingMore ) {
				pendingAdvance = grid.children.length;
				syncNextState();
				return;
			}

			appendEvents( 'advance' );
		};

		const syncCarousel = function () {
			const shouldRun = !! ( mobileQuery && mobileQuery.matches && grid.children.length );

			if ( shouldRun && ! carousel ) {
				carousel = createCardCarousel( section, 5 );

				if ( carousel ) {
					carousel.on( 'slideChange', prefetch );
					carousel.on( 'reachEnd', prefetch );
					carousel.on( 'touchStart', function ( instance ) {
						touchStartedAtEnd = instance.isEnd;
					} );
					carousel.on( 'touchEnd', function ( instance ) {
						if ( touchStartedAtEnd && instance.isEnd && instance.touches.diff < -40 && ( hasMore() || loadingMore ) && ! filtering ) {
							advancePastEnd();
						}

						touchStartedAtEnd = false;
					} );
				}
			} else if ( ! shouldRun && carousel ) {
				carousel.destroy( true, true );
				carousel = null;
				pendingAdvance = -1;
				clearSlidesInert( section );
				section.querySelector( '[data-event-driver-controls]' ).hidden = true;
			} else if ( carousel ) {
				carousel.update();
			}

			section.classList.toggle( 'event-driver--paged-carousel', !! carousel );
			syncNextState();
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

			data.getAll( 'event_category[]' ).filter( Boolean ).forEach( function ( category ) {
				params.append( 'category[]', category );
			} );
			const sequence = ++requestSequence;
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
			} ).then( function ( response ) {
				if ( sequence !== requestSequence ) {
					const error = new Error( 'Superseded request' );
					error.name = 'AbortError';
					throw error;
				}
				return response;
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
			filtering = true;
			loadingMore = false;
			prefetchPaused = false;
			pendingAdvance = -1;
			syncNextState();
			grid.classList.add( 'is-loading' );
			grid.setAttribute( 'aria-busy', 'true' );
			announce( strings.loading || '' );

			if ( loadMore ) { loadMore.disabled = true; }
			const pending = request( 0 );
			const sequence = requestSequence;

			pending.then( function ( response ) {
				grid.innerHTML = response.html || '';
				const eventCount = syncEmptyState();
				setHasMore( response.hasMore, response.nextOffset );

				if ( carousel ) {
					carousel.update();
					carousel.slideTo( 0, 0 );
				}

				syncCarousel();
				announce( eventCount ? format( strings.showing, [ eventCount, Math.max( response.total, eventCount ) ] ) : empty.textContent );
			} ).catch( function ( error ) {
				if ( error && 'AbortError' === error.name ) {
					return;
				}

				if ( sequence !== requestSequence ) { return; }
				if ( loadMore ) { loadMore.hidden = true; }
				announce( strings.error || '' );
			} ).finally( function () {
				if ( sequence !== requestSequence ) { return; }
				filtering = false;
				grid.classList.remove( 'is-loading' );
				grid.removeAttribute( 'aria-busy' );
			} );
		};

		/**
		 * Append the next batch.
		 *
		 * @param {string} mode `button` (Load More), `advance` (Next or a swipe at
		 *                      the last card) or `prefetch` (silent, near the end).
		 */
		const appendEvents = function ( mode ) {
			if ( loadingMore || filtering || ! hasMore() ) {
				return;
			}

			loadingMore = true;
			loadMore.disabled = true;

			if ( 'advance' === mode ) {
				pendingAdvance = grid.children.length;
			}

			if ( 'prefetch' !== mode ) {
				announce( strings.loading || '' );
			}

			syncNextState();

			const pending = request( offset );
			const sequence = requestSequence;
			pending.then( function ( response ) {
				const firstIndex = grid.children.length;
				const advance = pendingAdvance === firstIndex;

				grid.insertAdjacentHTML( 'beforeend', response.html || '' );
				const eventCount = syncEmptyState();
				setHasMore( response.hasMore, response.nextOffset );
				prefetchPaused = false;
				syncCarousel();

				const firstNew = grid.children[ firstIndex ];

				if ( carousel ) {
					if ( advance && firstNew ) {
						carousel.slideTo( firstIndex );
					}
				} else if ( firstNew ) {
					const link = firstNew.querySelector( '.ltt-event-card__link' );

					if ( link ) {
						link.focus();
					} else {
						firstNew.tabIndex = -1;
						firstNew.focus();
					}
				}

				if ( 'prefetch' !== mode || advance ) {
					announce( eventCount ? format( strings.loaded, [ response.count ] ) : empty.textContent );
				}
			} ).catch( function ( error ) {
				if ( sequence !== requestSequence ) { return; }
				loadMore.disabled = false;

				if ( error && 'AbortError' === error.name ) {
					return;
				}

				// Stop prefetching after a failure; Next at the end retries.
				prefetchPaused = true;

				if ( 'prefetch' !== mode || -1 !== pendingAdvance ) {
					announce( strings.error || '' );
				}
			} ).finally( function () {
				if ( sequence !== requestSequence ) { return; }
				loadingMore = false;
				pendingAdvance = -1;
				syncNextState();
			} );
		};

		/* Runs before Swiper's own handler, which would rewind to the first card. */
		section.addEventListener( 'click', function ( event ) {
			if ( ! carousel || filtering || ! next || ! event.target.closest( '[data-event-driver-next]' ) ) {
				return;
			}

			if ( ! carousel.isEnd || ! ( hasMore() || loadingMore ) ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			advancePastEnd();
		}, true );

		document.addEventListener( 'visibilitychange', function () {
			if ( ! document.hidden ) {
				prefetch();
			}
		} );

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			if ( event.submitter && event.submitter === loadMore ) {
				appendEvents( 'button' );
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
					appendEvents( 'button' );
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

		syncEmptyState();
		syncCarousel();
	};

	document.querySelectorAll( '[data-event-driver-hero]' ).forEach( initHero );
	document.querySelectorAll( '[data-event-driver-carousel]' ).forEach( function ( section ) {
		createCardCarousel( section );
	} );
	document.querySelectorAll( '[data-event-driver-listed]' ).forEach( initListed );
}() );
