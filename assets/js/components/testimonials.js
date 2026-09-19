/**
 * Swiper enhancement for the Testimonials block.
 *
 * The server renders a stacked list with hidden controls; this script adds
 * the Swiper structure, reveals the controls, and never autoplays.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const strings = window.ltt_dive_in_testimonials || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.querySelectorAll( '[data-testimonials-carousel]' ).forEach( function ( carousel ) {
		const viewport = carousel.querySelector( '[data-testimonials-viewport]' );
		const track = carousel.querySelector( '[data-testimonials-track]' );
		const previous = carousel.querySelector( '[data-testimonials-previous]' );
		const next = carousel.querySelector( '[data-testimonials-next]' );
		const pagination = carousel.querySelector( '[data-testimonials-pagination]' );
		const slides = carousel.querySelectorAll( '[data-testimonials-slide]' );

		if ( ! viewport || ! track || ! previous || ! next || ! pagination || slides.length < 2 ) {
			return;
		}

		const updatePaginationState = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			// Swiper skips its bullet labels when renderBullet is customized.
			instance.pagination.bullets.forEach( function ( bullet, index ) {
				bullet.setAttribute( 'aria-current', index === instance.realIndex ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', ( strings.goTo || 'Go to testimonial {{index}}' ).replace( '{{index}}', index + 1 ) );
			} );
		};

		viewport.classList.add( 'swiper' );
		track.classList.add( 'swiper-wrapper' );
		slides.forEach( function ( slide ) {
			slide.classList.add( 'swiper-slide' );
		} );
		[ previous, next, pagination ].forEach( function ( element ) {
			element.hidden = false;
		} );

		new window.Swiper( viewport, {
			slidesPerView: 1,
			spaceBetween: 32,
			speed: reducedMotion ? 0 : 350,
			rewind: true,
			a11y: {
				enabled: true,
				containerRoleDescriptionMessage: strings.carousel || 'carousel',
				itemRoleDescriptionMessage: strings.slide || 'slide',
				slideLabelMessage: strings.slideLabel || '{{index}} of {{slidesLength}}',
				prevSlideMessage: strings.previous || 'Previous testimonial',
				nextSlideMessage: strings.next || 'Next testimonial',
				paginationBulletMessage: strings.goTo || 'Go to testimonial {{index}}',
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
	} );
}() );
