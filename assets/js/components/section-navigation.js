/** Slide the section row below the page hero with Swiper on narrower screens. */
( function () {
	'use strict';

	const section = document.querySelector( '.ltt-section-navigation' );
	if ( ! section ) {
		return;
	}

	const viewport = section.querySelector( '.ltt-section-navigation__viewport' );
	const menu = viewport && viewport.querySelector( '.ltt-section-navigation__menu' );
	if ( window.Swiper && viewport && menu ) {
		const compactQuery = window.matchMedia( '(max-width: 1399.98px)' );
		const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		const items = Array.from( menu.children );
		let swiper = null;

		const getCurrentIndex = function () {
			return items.findIndex( function ( item ) {
				return item.querySelector( 'a[aria-current="page"]' );
			} );
		};

		// Fade an edge only while more links sit beyond it.
		const updateEdges = function ( instance ) {
			section.classList.toggle( 'has-previous', ! instance.isLocked && ! instance.isBeginning );
			section.classList.toggle( 'has-next', ! instance.isLocked && ! instance.isEnd );
		};

		const destroy = function () {
			if ( ! swiper ) {
				return;
			}
			swiper.destroy( true, true );
			swiper = null;
			section.classList.remove( 'is-swiper', 'has-previous', 'has-next' );
			viewport.classList.remove( 'swiper', 'swiper-backface-hidden' );
			menu.classList.remove( 'swiper-wrapper' );
			items.forEach( function ( item ) {
				item.classList.remove( 'swiper-slide' );
			} );
		};

		const init = function () {
			if ( swiper ) {
				return;
			}
			section.classList.add( 'is-swiper' );
			viewport.classList.add( 'swiper' );
			menu.classList.add( 'swiper-wrapper' );
			items.forEach( function ( item ) {
				item.classList.add( 'swiper-slide' );
			} );

			const currentIndex = getCurrentIndex();
			swiper = new window.Swiper( viewport, {
				// Swiper's a11y module would replace the list semantics of this navigation.
				a11y: { enabled: false },
				freeMode: { enabled: true, momentum: ! reducedMotion.matches },
				grabCursor: true,
				initialSlide: Math.max( currentIndex, 0 ),
				mousewheel: { forceToAxis: true },
				slidesOffsetAfter: 20,
				slidesOffsetBefore: 20,
				slidesPerView: 'auto',
				speed: reducedMotion.matches ? 0 : 300,
				watchOverflow: true,
				on: {
					afterInit: updateEdges,
					lock: updateEdges,
					progress: updateEdges,
					resize: updateEdges,
					unlock: updateEdges,
				},
			} );
		};

		const updateMode = function () {
			if ( compactQuery.matches ) {
				init();
			} else {
				destroy();
			}
		};

		// Keep keyboard focus visible: slide to a focused link instead of letting the browser scroll the clipped viewport.
		menu.addEventListener( 'focusin', function ( event ) {
			if ( ! swiper ) {
				return;
			}
			viewport.scrollLeft = 0;
			const item = event.target.closest( '.swiper-slide' );
			const index = items.indexOf( item );
			if ( index < 0 ) {
				return;
			}
			// Use Swiper's target translate, not the DOM box, so rapid Tab presses mid-transition measure correctly.
			const left = item.offsetLeft + swiper.translate;
			if ( left < 0 || left + item.offsetWidth > swiper.width ) {
				swiper.slideTo( index, reducedMotion.matches ? 0 : 300 );
			}
		} );

		// Focus scrolling happens after focusin; undo it so only Swiper's transform moves the row.
		viewport.addEventListener( 'scroll', function () {
			if ( swiper && 0 !== viewport.scrollLeft ) {
				viewport.scrollLeft = 0;
			}
		} );

		updateMode();
		compactQuery.addEventListener( 'change', updateMode );
	}
}() );
