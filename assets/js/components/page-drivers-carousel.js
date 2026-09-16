/**
 * Swiper enhancement for the carousel Up Driver variants.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.querySelectorAll( '[data-page-driver-carousel]' ).forEach( function ( carousel ) {
		const viewport = carousel.querySelector( '.page-drivers__carousel-viewport' );
		const wrapper = carousel.querySelector( '.swiper-wrapper' );
		const previous = carousel.querySelector( '[data-page-driver-carousel-previous]' );
		const next = carousel.querySelector( '[data-page-driver-carousel-next]' );
		const pagination = carousel.querySelector( '[data-page-driver-carousel-pagination]' );
		const driver = carousel.closest( '[data-page-driver]' );
		const allSlides = driver ? Array.from( driver.querySelectorAll( '[data-page-driver-card]' ) ) : [];
		const mobileCarousel = window.matchMedia( '(max-width: 767.98px)' );
		const isMonthly = driver && driver.classList.contains( 'page-drivers--monthly' );
		let activeTerm = 'all';
		let swiper;

		if ( ! viewport || ! wrapper || ! previous || ! next || ! pagination ) {
			return;
		}

		const updatePaginationState = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			instance.pagination.bullets.forEach( function ( bullet, index ) {
				const isCurrent = index === instance.realIndex;

				bullet.setAttribute( 'aria-current', isCurrent ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', 'Go to destination ' + ( index + 1 ) );
			} );
		};

		const getVisibleSlides = function () {
			return allSlides.filter( function ( slide ) {
				const terms = ( slide.dataset.pageDriverTerms || '' ).split( ' ' );

				return 'all' === activeTerm || terms.includes( activeTerm );
			} );
		};

		const createSwiper = function () {
			const useLoop = mobileCarousel.matches;
			const visibleSlides = getVisibleSlides();

			swiper = new window.Swiper( viewport, {
				slidesPerView: 'auto',
				spaceBetween: 11,
				speed: reducedMotion ? 0 : 350,
				centeredSlides: mobileCarousel.matches || isMonthly,
				loop: true,
				rewind: ! useLoop && visibleSlides.length > 1,
				watchOverflow: true,
				watchSlidesProgress: true,
				keyboard: {
					enabled: true,
					onlyInViewport: true,
				},
				a11y: {
					enabled: true,
					prevSlideMessage: 'Previous destinations',
					nextSlideMessage: 'Next destinations',
				},
				navigation: {
					prevEl: previous,
					nextEl: next,
				},
				pagination: {
					el: pagination,
					clickable: true,
					bulletElement: 'button',
					bulletClass: 'ltt-carousel-indicator',
					bulletActiveClass: 'is-active',
					renderBullet: function ( index, className ) {
						return '<button class="' + className + '" type="button"></button>';
					},
				},
				on: {
					init: updatePaginationState,
					slideChange: updatePaginationState,
					paginationUpdate: updatePaginationState,
				},
			} );
		};

		const rebuildCarousel = function () {
			if ( swiper ) {
				swiper.destroy( true, true );
			}

			wrapper.replaceChildren( ...getVisibleSlides() );
			createSwiper();
		};

		rebuildCarousel();

		if ( driver ) {
			driver.addEventListener( 'ltt:page-driver-filtered', function ( event ) {
				activeTerm = event.detail && event.detail.termId ? event.detail.termId : 'all';
				rebuildCarousel();
			} );
		}

		mobileCarousel.addEventListener( 'change', rebuildCarousel );
	} );
}() );
