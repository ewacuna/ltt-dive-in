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
		const filterSelect = driver.querySelector( '[data-page-driver-filter-select]' );
		const cards = Array.from( driver.querySelectorAll( '[data-page-driver-card]' ) );
		const status = driver.querySelector( '[data-page-driver-status]' );
		const isCarousel = Boolean( driver.querySelector( '[data-page-driver-carousel]' ) );

		if ( ( ! filters.length && ! filterSelect ) || ! cards.length ) {
			return;
		}

		const activateFilter = function ( activeFilter ) {
			const termId = activeFilter.dataset.pageDriverFilter || activeFilter.value;
			let visibleCount = 0;

			filters.forEach( function ( filter ) {
				const selected = filter === activeFilter;
				filter.setAttribute( 'aria-pressed', selected ? 'true' : 'false' );
				filter.classList.toggle( 'is-selected', selected );
			} );

			if ( filterSelect && filterSelect.value !== termId ) {
				filterSelect.value = termId;
			}

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

			if ( status && strings.destinationSingular && strings.destinationPlural ) {
				status.textContent = visibleCount + ' ' + ( 1 === visibleCount ? strings.destinationSingular : strings.destinationPlural );
			}

			driver.dispatchEvent( new CustomEvent( 'ltt:page-driver-filtered', { detail: { termId: termId } } ) );
		};

		filters.forEach( function ( filter ) {
			filter.addEventListener( 'click', function () {
				activateFilter( filter );
			} );
		} );

		if ( filterSelect ) {
			filterSelect.addEventListener( 'change', function () {
				activateFilter( filterSelect );
			} );
		}
	} );
}() );
