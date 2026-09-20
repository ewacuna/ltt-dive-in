/**
 * Swiper enhancement for Feature Image Driver carousels.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const strings = window.ltt_dive_in_feature_image_driver || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.querySelectorAll( '[data-feature-image-driver-carousel]' ).forEach( function ( carousel ) {
		const viewport = carousel.querySelector( '.feature-image-driver__carousel-viewport' );
		const slides = carousel.querySelectorAll( '.feature-image-driver__slide' );
		const previous = carousel.querySelector( '[data-feature-image-driver-previous]' );
		const next = carousel.querySelector( '[data-feature-image-driver-next]' );
		const pagination = carousel.querySelector( '[data-feature-image-driver-pagination]' );

		if ( ! viewport || viewport.classList.contains( 'swiper-initialized' ) || ! slides.length ) {
			return;
		}

		const formatSlideLabel = function ( index ) {
			const template = strings.goToSlide || 'Go to feature %d';

			return template.replace( '%d', index + 1 );
		};

		const updatePaginationState = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			instance.pagination.bullets.forEach( function ( bullet, index ) {
				const isCurrent = index === instance.realIndex;

				bullet.setAttribute( 'aria-current', isCurrent ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', formatSlideLabel( index ) );
			} );
		};

		new window.Swiper( viewport, {
			slidesPerView: 1,
			spaceBetween: 0,
			speed: reducedMotion ? 0 : 350,
			loop: slides.length > 1,
			watchOverflow: true,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: {
				enabled: true,
				prevSlideMessage: strings.previousSlide || 'Previous feature',
				nextSlideMessage: strings.nextSlide || 'Next feature',
			},
			navigation: previous && next ? {
				prevEl: previous,
				nextEl: next,
			} : undefined,
			pagination: pagination ? {
				el: pagination,
				clickable: true,
				bulletElement: 'button',
				bulletClass: 'ltt-carousel-indicator',
				bulletActiveClass: 'is-active',
				renderBullet: function ( index, className ) {
					return '<button class="' + className + '" type="button"></button>';
				},
			} : undefined,
			on: {
				init: updatePaginationState,
				slideChange: updatePaginationState,
				paginationUpdate: updatePaginationState,
			},
		} );
	} );
}() );
