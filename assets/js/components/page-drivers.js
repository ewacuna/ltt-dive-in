/**
 * Filter Page Driver tiles already rendered in the document.
 */
( function () {
	'use strict';

	const drivers = document.querySelectorAll( '[data-page-driver]' );
	const strings = window.ltt_dive_in_page_drivers || {};

	drivers.forEach( function ( driver ) {
		const filters = Array.from( driver.querySelectorAll( '[data-page-driver-filter]' ) );
		const cards = Array.from( driver.querySelectorAll( '[data-page-driver-card]' ) );
		const status = driver.querySelector( '[data-page-driver-status]' );

		if ( ! filters.length || ! cards.length ) {
			return;
		}

		const activateFilter = function ( activeFilter ) {
			const termId = activeFilter.dataset.pageDriverFilter;
			let visibleCount = 0;

			filters.forEach( function ( filter ) {
				const selected = filter === activeFilter;
				filter.setAttribute( 'aria-pressed', selected ? 'true' : 'false' );
				filter.classList.toggle( 'is-selected', selected );
			} );

			cards.forEach( function ( card ) {
				const terms = ( card.dataset.pageDriverTerms || '' ).split( ' ' );
				const visible = 'all' === termId || terms.includes( termId );

				card.hidden = ! visible;

				if ( visible ) {
					visibleCount += 1;
				}
			} );

			if ( status && strings.destinationSingular && strings.destinationPlural ) {
				status.textContent = visibleCount + ' ' + ( 1 === visibleCount ? strings.destinationSingular : strings.destinationPlural );
			}
		};

		filters.forEach( function ( filter ) {
			filter.addEventListener( 'click', function () {
				activateFilter( filter );
			} );
		} );
	} );
}() );
