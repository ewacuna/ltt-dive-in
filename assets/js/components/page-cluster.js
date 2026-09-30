/** Mobile Swiper enhancement for the Features List Grid variant. */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const strings = window.ltt_dive_in_page_cluster || {};
	const mobile = window.matchMedia( '(max-width: 767.98px)' );
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	document.querySelectorAll( '.page-cluster--features' ).forEach( function ( cluster ) {
		const viewport = cluster.querySelector( '.page-cluster__viewport' );
		const wrapper = cluster.querySelector( '.page-cluster__grid' );
		const cards = wrapper ? Array.from( wrapper.children ) : [];
		const controls = cluster.querySelector( '.page-cluster__controls' );
		const previous = controls && controls.querySelector( '[data-cluster-previous]' );
		const next = controls && controls.querySelector( '[data-cluster-next]' );
		const pagination = controls && controls.querySelector( '[data-cluster-pagination]' );
		let swiper;

		if ( ! viewport || ! wrapper || ! controls || ! previous || ! next || ! pagination || cards.length < 2 ) {
			return;
		}

		const update = function ( instance ) {
			controls.hidden = instance.isLocked || instance.snapGrid.length < 2;
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}
			instance.pagination.bullets.forEach( function ( bullet, index ) {
				bullet.setAttribute( 'aria-current', index === instance.snapIndex ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', ( strings.goToFeature || 'Go to feature %d' ).replace( '%d', index + 1 ) );
				bullet.setAttribute( 'aria-controls', wrapper.id );
			} );
		};

		const sync = function () {
			if ( ! mobile.matches ) {
				if ( swiper ) {
					swiper.destroy( true, true );
					swiper = null;
					viewport.classList.remove( 'swiper' );
					wrapper.classList.remove( 'swiper-wrapper' );
					cards.forEach( function ( card ) { card.classList.remove( 'swiper-slide' ); } );
					pagination.replaceChildren();
				}
				controls.hidden = true;
				return;
			}

			if ( swiper ) {
				return;
			}

			viewport.classList.add( 'swiper' );
			wrapper.classList.add( 'swiper-wrapper' );
			cards.forEach( function ( card ) { card.classList.add( 'swiper-slide' ); } );
			swiper = new window.Swiper( viewport, {
				slidesPerView: 'auto',
				spaceBetween: 11,
				speed: reducedMotion.matches ? 0 : 350,
				watchOverflow: true,
				keyboard: {
					enabled: true,
					onlyInViewport: true,
				},
				a11y: {
					enabled: true,
					prevSlideMessage: strings.previous || 'Previous feature',
					nextSlideMessage: strings.next || 'Next feature',
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
					init: update,
					slideChange: update,
					paginationUpdate: update,
					resize: update,
				},
			} );
		};

		if ( mobile.addEventListener ) {
			mobile.addEventListener( 'change', sync );
		} else {
			mobile.addListener( sync );
		}
		sync();
	} );
}() );
