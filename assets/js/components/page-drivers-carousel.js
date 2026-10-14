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
	const strings = window.ltt_dive_in_page_driver_carousel || {};
	const getSlideMonthTerms = function ( slide ) {
		return ( slide.dataset.pageDriverMonthTerms || '' ).split( ' ' ).filter( Boolean );
	};

	const initializeCarousel = function ( carousel ) {
		if ( 'true' === carousel.dataset.pageDriverCarouselInitialized ) {
			return;
		}

		const viewport = carousel.querySelector( '.page-drivers__carousel-viewport' );
		const wrapper = carousel.querySelector( '.swiper-wrapper' );
		const previous = carousel.querySelector( '[data-page-driver-carousel-previous]' );
		const next = carousel.querySelector( '[data-page-driver-carousel-next]' );
		const pagination = carousel.querySelector( '[data-page-driver-carousel-pagination]' );
		const driver = carousel.closest( '[data-page-driver]' );
		const allSlides = driver ? Array.from( driver.querySelectorAll( '[data-page-driver-card]' ) ) : [];
		const monthSelect = driver && driver.querySelector( '[data-page-driver-month-select]' );
		const status = driver && driver.querySelector( '[data-page-driver-status]' );
		const mobileCarousel = window.matchMedia( '(max-width: 767.98px)' );
		const isSixPlus = driver && driver.classList.contains( 'page-drivers--six-plus-up' );
		const isMonthly = driver && driver.classList.contains( 'page-drivers--monthly' );
		let activeTerm = 'all';
		let swiper;

		if ( ! viewport || ! wrapper || ! previous || ! next || ! pagination ) {
			return;
		}

		carousel.dataset.pageDriverCarouselInitialized = 'true';

		const updatePaginationState = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			instance.pagination.bullets.forEach( function ( bullet, index ) {
				const isCurrent = bullet.classList.contains( 'is-active' );
				let label = ( strings.goToDestination || 'Go to destination %d' ).replace( '%d', String( index + 1 ) );

				if ( isMonthly && monthSelect && allSlides[ index ] ) {
					const slideTerms = getSlideMonthTerms( allSlides[ index ] );
					const option = Array.from( monthSelect.options ).find( function ( item ) {
						return slideTerms.includes( item.value );
					} );

					if ( option ) {
						label = ( strings.goToMonth || 'Go to %s' ).replace( '%s', option.text );
					}
				}

				bullet.setAttribute( 'aria-current', isCurrent ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', label );
			} );
		};

		const updateActiveMonthStatus = function ( instance ) {
			if ( ! isMonthly || ! monthSelect || ! allSlides[ instance.realIndex ] ) {
				return;
			}

			const slideTerms = getSlideMonthTerms( allSlides[ instance.realIndex ] );
			const option = Array.from( monthSelect.options ).find( function ( item ) {
				return slideTerms.includes( item.value );
			} );

			if ( ! option || ! status ) {
				return;
			}

			status.textContent = ( strings.monthShown || '%s shown.' ).replace( '%s', option.text );
		};

		const getVisibleSlides = function () {
			return allSlides.filter( function ( slide ) {
				const terms = ( slide.dataset.pageDriverTerms || '' ).split( ' ' );

				return 'all' === activeTerm || terms.includes( activeTerm );
			} );
		};

		const handleMonthlyPaginationClick = function ( event ) {
			const clickTarget = event.target;
			const bullet = clickTarget instanceof Element
				? clickTarget.closest( '.ltt-carousel-indicator' )
				: null;

			if (
				! swiper ||
				! isMonthly ||
				! swiper.params.loop ||
				swiper.animating ||
				! bullet ||
				! pagination.contains( bullet )
			) {
				return;
			}

			const targetIndex = Array.from( swiper.pagination.bullets ).indexOf( bullet );
			const currentIndex = Array.from( swiper.pagination.bullets ).findIndex( function ( paginationBullet ) {
				return paginationBullet.classList.contains( 'is-active' );
			} );

			if ( targetIndex < 0 || targetIndex === currentIndex ) {
				return;
			}

			event.preventDefault();
			// Keep looped edge slides mounted throughout the dot transition.
			swiper.slideToLoop( targetIndex );
		};

		const createSwiper = function () {
			const visibleSlides = getVisibleSlides();
			const hasMultipleSlides = visibleSlides.length > 1;
			const isFiltered = 'all' !== activeTerm;
			const useLoop = isMonthly && hasMultipleSlides && ! isFiltered;
			const initialMonthIndex = isMonthly && monthSelect ? allSlides.findIndex( function ( slide ) {
				return getSlideMonthTerms( slide ).includes( monthSelect.value );
			} ) : 0;

			if ( isMonthly ) {
				driver.classList.add( 'is-enhanced' );
			}

			swiper = new window.Swiper( viewport, {
				initialSlide: Math.max( 0, initialMonthIndex ),
				slidesPerView: 'auto',
				spaceBetween: 11,
				speed: reducedMotion ? 0 : 350,
				centeredSlides: hasMultipleSlides && ( mobileCarousel.matches || ( isMonthly && ! isFiltered ) ),
				centerInsufficientSlides: isSixPlus || ( isMonthly && isFiltered ),
				loop: useLoop,
				rewind: false,
				watchOverflow: true,
				watchSlidesProgress: true,
				keyboard: {
					enabled: true,
					onlyInViewport: true,
				},
				a11y: {
					enabled: true,
					prevSlideMessage: isMonthly ? ( strings.previousMonth || 'Previous month' ) : ( strings.previous || 'Previous destinations' ),
					nextSlideMessage: isMonthly ? ( strings.nextMonth || 'Next month' ) : ( strings.next || 'Next destinations' ),
				},
				navigation: {
					prevEl: previous,
					nextEl: next,
				},
				pagination: {
					el: pagination,
					clickable: ! useLoop,
					bulletElement: 'button',
					bulletClass: 'ltt-carousel-indicator',
					bulletActiveClass: 'is-active',
					renderBullet: function ( index, className ) {
						return '<button class="' + className + '" type="button"></button>';
					},
				},
					on: {
					init: function ( instance ) {
						updatePaginationState( instance );
						updateActiveMonthStatus( instance );
					},
					slideChange: function ( instance ) {
						updatePaginationState( instance );
						updateActiveMonthStatus( instance );
					},
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
		pagination.addEventListener( 'click', handleMonthlyPaginationClick );

		if ( isMonthly && monthSelect ) {
			monthSelect.addEventListener( 'change', function () {
				const targetIndex = allSlides.findIndex( function ( slide ) {
					return getSlideMonthTerms( slide ).includes( monthSelect.value );
				} );

				if ( ! swiper || targetIndex < 0 || targetIndex === swiper.realIndex ) {
					return;
				}

				if ( swiper.params.loop ) {
					swiper.slideToLoop( targetIndex );
				} else {
					swiper.slideTo( targetIndex );
				}
			} );
		}

		if ( driver ) {
			driver.addEventListener( 'ltt:page-driver-filtered', function ( event ) {
				activeTerm = event.detail && event.detail.termId ? event.detail.termId : 'all';
				rebuildCarousel();
			} );
		}

		mobileCarousel.addEventListener( 'change', rebuildCarousel );
	};

	const initializeCarouselsWithin = function ( root ) {
		if ( root.matches && root.matches( '[data-page-driver-carousel]' ) ) {
			initializeCarousel( root );
		}

		if ( root.querySelectorAll ) {
			root.querySelectorAll( '[data-page-driver-carousel]' ).forEach( initializeCarousel );
		}
	};

	initializeCarouselsWithin( document );

	// ACF refreshes server-rendered block previews when editors change fields.
	// Initialize those new previews as well as markup present at page load.
	if ( window.MutationObserver && document.documentElement ) {
		const previewObserver = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				mutation.addedNodes.forEach( function ( node ) {
					if ( node instanceof Element ) {
						initializeCarouselsWithin( node );
					}
				} );
			} );
		} );

		// Gutenberg may replace the iframe body while mounting or refreshing ACF
		// previews. Observe the document element so those replacements remain visible.
		previewObserver.observe( document.documentElement, { childList: true, subtree: true } );
	}
}() );
