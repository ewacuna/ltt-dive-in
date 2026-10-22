/** Swiper enhancement for the End Page Cluster Content Trail. */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const strings = window.ltt_dive_in_end_page_cluster || {};
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	document.querySelectorAll( '[data-end-page-trail]' ).forEach( function ( trail ) {
		const viewport = trail.querySelector( '.end-page-cluster__viewport' );
		const wrapper = trail.querySelector( '.end-page-cluster__track' );
		const controls = trail.querySelector( '[data-end-page-trail-controls]' );
		const previous = controls && controls.querySelector( '[data-end-page-trail-previous]' );
		const next = controls && controls.querySelector( '[data-end-page-trail-next]' );
		const pagination = controls && controls.querySelector( '[data-end-page-trail-pagination]' );

		if ( ! viewport || ! wrapper || ! controls || ! previous || ! next || ! pagination || wrapper.children.length < 2 ) {
			return;
		}

		const update = function ( instance ) {
			controls.hidden = instance.isLocked || instance.snapGrid.length < 2;
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}
			instance.pagination.bullets.forEach( function ( bullet, index ) {
				bullet.setAttribute( 'aria-current', index === instance.snapIndex ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', ( strings.goTo || 'Go to item %d' ).replace( '%d', index + 1 ) );
				bullet.setAttribute( 'aria-controls', wrapper.id );
			} );
		};

		// Swiper's own keyboard module handles arrows, so the no-JS scroll stop leaves the tab order.
		viewport.removeAttribute( 'tabindex' );

		// eslint-disable-next-line no-new
		new window.Swiper( viewport, {
			slidesPerView: 'auto',
			spaceBetween: 6,
			speed: reducedMotion.matches ? 0 : 350,
			watchOverflow: true,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: {
				enabled: true,
				prevSlideMessage: strings.previous || 'Previous items',
				nextSlideMessage: strings.next || 'Next items',
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
			breakpoints: {
				768: {
					spaceBetween: 11,
				},
			},
			on: {
				init: update,
				slideChange: update,
				paginationUpdate: update,
				resize: update,
				lock: update,
				unlock: update,
			},
		} );
	} );
}() );
