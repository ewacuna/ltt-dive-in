/**
 * Event detail view.
 *
 * - Photo gallery: one-photo Swiper with arrows and dots.
 * - Description: shortened with a Read More toggle when it is long.
 * - Upcoming Dates: a disclosure that starts open.
 *
 * Without JavaScript every photo, the full description, and every date stay
 * visible.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_event_detail || {};
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initGallery( gallery ) {
		const viewport = gallery.querySelector( '[data-event-gallery-viewport]' );
		const controls = gallery.querySelector( '[data-event-gallery-controls]' );
		const previous = gallery.querySelector( '[data-event-gallery-previous]' );
		const next = gallery.querySelector( '[data-event-gallery-next]' );
		const pagination = gallery.querySelector( '[data-event-gallery-pagination]' );

		if ( ! window.Swiper || ! viewport || ! controls ) {
			return;
		}

		const setSlidesInert = function ( instance ) {
			instance.slides.forEach( function ( slide, index ) {
				slide.inert = index !== instance.activeIndex;
			} );
		};

		controls.hidden = false;
		gallery.classList.add( 'is-ready' );

		new window.Swiper( viewport, {
			slidesPerView: 1,
			spaceBetween: 0,
			rewind: true,
			speed: reducedMotion ? 0 : 350,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: {
				enabled: true,
				containerRoleDescriptionMessage: strings.carousel || 'carousel',
				itemRoleDescriptionMessage: strings.slide || 'slide',
				slideLabelMessage: strings.slideLabel || '{{index}} of {{slidesLength}}',
				prevSlideMessage: previous.getAttribute( 'aria-label' ),
				nextSlideMessage: next.getAttribute( 'aria-label' ),
				paginationBulletMessage: strings.goToPhoto || 'Go to photo {{index}}',
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
					return '<button class="' + className + '" type="button" aria-label="' + String( strings.goToPhoto || 'Go to photo {{index}}' ).replace( '{{index}}', index + 1 ) + '"></button>';
				},
			},
			on: {
				afterInit: setSlidesInert,
				slideChange: setSlidesInert,
				paginationUpdate: function ( instance ) {
					instance.pagination.bullets.forEach( function ( bullet ) {
						bullet.setAttribute( 'aria-current', bullet.classList.contains( 'is-active' ) ? 'true' : 'false' );
					} );
				},
			},
		} );
	}

	function initDescription( description ) {
		const toggle = document.querySelector( '[data-event-description-toggle][aria-controls="' + description.id + '"]' );

		if ( ! toggle ) {
			return;
		}

		description.classList.add( 'is-collapsed' );

		// Short descriptions fit within the clamp and need no toggle.
		if ( description.scrollHeight <= description.clientHeight + 1 ) {
			description.classList.remove( 'is-collapsed' );
			return;
		}

		toggle.hidden = false;
		toggle.addEventListener( 'click', function () {
			const expanded = 'true' !== toggle.getAttribute( 'aria-expanded' );

			description.classList.toggle( 'is-collapsed', ! expanded );
			toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			toggle.textContent = expanded ? toggle.dataset.labelLess : toggle.dataset.labelMore;
		} );
	}

	function initDates( section ) {
		const toggle = section.querySelector( '[data-event-dates-toggle]' );
		const panel = toggle ? document.getElementById( toggle.getAttribute( 'aria-controls' ) ) : null;

		if ( ! panel ) {
			return;
		}

		section.classList.add( 'is-enhanced' );
		toggle.addEventListener( 'click', function () {
			const expanded = 'true' !== toggle.getAttribute( 'aria-expanded' );

			toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			panel.hidden = ! expanded;
		} );
	}

	function init() {
		document.querySelectorAll( '[data-event-gallery]' ).forEach( initGallery );
		document.querySelectorAll( '[data-event-description]' ).forEach( initDescription );
		document.querySelectorAll( '[data-event-dates]' ).forEach( initDates );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
