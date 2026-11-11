/**
 * Image carousel for the Story Module guide variants.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const strings = window.ltt_dive_in_story_module_guide || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.querySelectorAll( '[data-story-guide-carousel]' ).forEach( function ( carousel ) {
		const viewport = carousel.querySelector( '[data-story-guide-viewport]' );
		const track = carousel.querySelector( '[data-story-guide-track]' );
		const controls = carousel.querySelector( '[data-story-guide-controls]' );
		const previous = carousel.querySelector( '[data-story-guide-previous]' );
		const next = carousel.querySelector( '[data-story-guide-next]' );
		const pagination = carousel.querySelector( '[data-story-guide-pagination]' );
		const slides = track ? Array.from( track.children ) : [];

		if ( ! viewport || ! track || ! controls || ! previous || ! next || ! pagination || slides.length < 2 ) {
			return;
		}

		const update = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			instance.pagination.bullets.forEach( function ( bullet, index ) {
				bullet.setAttribute( 'aria-current', index === instance.realIndex ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', ( strings.goToImage || 'Go to image %d' ).replace( '%d', index + 1 ) );
				bullet.setAttribute( 'aria-controls', track.id );
			} );
		};

		viewport.classList.add( 'swiper' );
		track.classList.add( 'swiper-wrapper' );
		slides.forEach( function ( slide ) {
			slide.classList.add( 'swiper-slide' );
		} );
		controls.hidden = false;

		new window.Swiper( viewport, {
			slidesPerView: 1,
			rewind: true,
			speed: reducedMotion ? 0 : 350,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: {
				enabled: true,
				prevSlideMessage: strings.previous || 'Previous image',
				nextSlideMessage: strings.next || 'Next image',
			},
			navigation: {
				prevEl: previous,
				nextEl: next,
			},
			pagination: {
				el: pagination,
				clickable: true,
				bulletClass: 'ltt-carousel-indicator',
				bulletActiveClass: 'is-active',
				renderBullet: function ( index, className ) {
					return '<button class="' + className + '" type="button"></button>';
				},
			},
			on: {
				init: update,
				slideChange: update,
				paginationUpdate: update,
			},
		} );
	} );
}() );
