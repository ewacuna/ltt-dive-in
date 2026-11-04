/**
 * Filter Page Driver tiles already rendered in the document.
 */
( function () {
	'use strict';

	const drivers = document.querySelectorAll( '[data-page-driver]' );
	const strings = window.ltt_dive_in_page_drivers || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	const setCardVisibility = function ( card, visible ) {
		if ( reducedMotion ) {
			card.classList.remove( 'is-filtering-in', 'is-filtering-out' );
			card.hidden = ! visible;
			return;
		}

		if ( visible ) {
			if ( ! card.hidden ) {
				return;
			}

			card.hidden = false;
			card.classList.add( 'is-filtering-in' );

			window.requestAnimationFrame( function () {
				card.classList.remove( 'is-filtering-in' );
			} );
			return;
		}

		card.classList.remove( 'is-filtering-in', 'is-filtering-out' );
		card.hidden = true;
	};

	drivers.forEach( function ( driver ) {
		const filters = Array.from( driver.querySelectorAll( '[data-page-driver-filter]' ) );
		const cards = Array.from( driver.querySelectorAll( '[data-page-driver-card]' ) );
		const grid = driver.querySelector( '[data-page-driver-grid]' );
		const status = driver.querySelector( '[data-page-driver-status]' );
		const groupMode = driver.dataset.pageDriverGroupMode === 'true';
		const isCarousel = Boolean( driver.querySelector( '[data-page-driver-carousel]' ) );

		if ( ! filters.length || ! cards.length ) {
			return;
		}

		const activateFilter = function ( activeFilter ) {
			const termId = activeFilter.dataset.pageDriverFilter;
			let visibleCount = 0;

			if ( grid ) {
				grid.classList.toggle( 'is-filtered', 'all' !== termId );
				grid.removeAttribute( 'data-visible-count' );
			}

			filters.forEach( function ( filter ) {
				const selected = filter === activeFilter;
				if ( groupMode ) {
					if ( selected ) {
						filter.setAttribute( 'aria-current', 'true' );
					} else {
						filter.removeAttribute( 'aria-current' );
					}
				} else {
					filter.setAttribute( 'aria-pressed', selected ? 'true' : 'false' );
				}
				filter.classList.toggle( 'is-selected', selected );
			} );

			cards.forEach( function ( card ) {
				const terms = ( card.dataset.pageDriverTerms || '' ).split( ' ' );
				const visible = 'all' === termId || terms.includes( termId );

				if ( ! isCarousel ) {
					setCardVisibility( card, visible );
				}

				if ( visible ) {
					visibleCount += 1;
				}
			} );

			if ( grid && ! isCarousel && 'all' !== termId && visibleCount > 0 && visibleCount <= 3 ) {
				grid.dataset.visibleCount = String( visibleCount );
			}

			if ( status && strings.destinationSingular && strings.destinationPlural ) {
				status.textContent = ( groupMode ? activeFilter.textContent.trim() + ': ' : '' ) + visibleCount + ' ' + ( 1 === visibleCount ? strings.destinationSingular : strings.destinationPlural );
			}

			driver.dispatchEvent( new CustomEvent( 'ltt:page-driver-filtered', { detail: { termId: termId } } ) );
		};

		filters.forEach( function ( filter ) {
			filter.addEventListener( 'click', function ( event ) {
				if ( groupMode ) {
					if ( event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0 ) {
						return;
					}
					event.preventDefault();
					const url = new URL( window.location.href );
					url.searchParams.set( 'ltt_up_filter[' + driver.id + ']', filter.dataset.pageDriverFilter );
					window.history.replaceState( window.history.state, '', url );
				}
				activateFilter( filter );
			} );
		} );
	} );
}() );
