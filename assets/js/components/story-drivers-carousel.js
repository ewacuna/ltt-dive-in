/**
 * Story Drivers: feature slideshow and 3+ Up carousel.
 *
 * On devices without hover, the carousel reveals the active tile's details
 * once the carousel scrolls into the middle of the viewport, so only one tile
 * is expanded at a time and no tap is required.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	if ( ! window.Swiper ) {
		return;
	}

	const strings = window.ltt_dive_in_story_drivers_carousel || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const noHover = window.matchMedia ? window.matchMedia( '(hover: none)' ) : null;

	const format = function ( template, value ) {
		return ( template || '' ).replace( '%d', String( value ) );
	};

	const initFeature = function ( feature ) {
		const viewport = feature.querySelector( '.story-drivers__feature-viewport' );
		const previous = feature.querySelector( '[data-story-feature-previous]' );
		const next = feature.querySelector( '[data-story-feature-next]' );
		const pagination = feature.querySelector( '[data-story-feature-pagination]' );

		if ( ! viewport || viewport.classList.contains( 'swiper-initialized' ) ) {
			return;
		}

		const updatePaginationState = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			instance.pagination.bullets.forEach( function ( bullet, index ) {
				bullet.setAttribute( 'aria-current', index === instance.realIndex ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', format( strings.goToStory || 'Go to story %d', index + 1 ) );
			} );
		};

		new window.Swiper( viewport, {
			slidesPerView: 1,
			spaceBetween: 0,
			speed: reducedMotion ? 0 : 350,
			loop: true,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: {
				enabled: true,
				prevSlideMessage: strings.previousStory || 'Previous story',
				nextSlideMessage: strings.nextStory || 'Next story',
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
	};

	const initCarousel = function ( section ) {
		const carousel = section.querySelector( '[data-story-carousel]' );
		const viewport = carousel ? carousel.querySelector( '.story-drivers__carousel-viewport' ) : null;
		const track = carousel ? carousel.querySelector( '[data-story-carousel-track]' ) : null;
		const filters = Array.prototype.slice.call( section.querySelectorAll( '[data-story-filter]' ) );
		const status = section.querySelector( '[data-story-filter-status]' );
		let inRevealBand = false;

		if ( ! viewport || ! track || viewport.classList.contains( 'swiper-initialized' ) ) {
			return;
		}

		const allSlides = Array.prototype.slice.call( track.children );
		let swiper = null;

		const syncExpanded = function ( instance ) {
			const shouldReveal = noHover && noHover.matches && inRevealBand;

			if ( ! instance ) {
				return;
			}

			instance.slides.forEach( function ( slide, index ) {
				slide.classList.toggle( 'is-expanded', shouldReveal && index === instance.activeIndex );
			} );
		};

		const createSwiper = function () {
			return new window.Swiper( viewport, {
				slidesPerView: 'auto',
				spaceBetween: 11,
				speed: reducedMotion ? 0 : 350,
				watchOverflow: true,
				keyboard: {
					enabled: true,
					onlyInViewport: true,
				},
				a11y: {
					enabled: true,
					prevSlideMessage: strings.previousStories || 'Previous stories',
					nextSlideMessage: strings.nextStories || 'Next stories',
				},
				navigation: {
					prevEl: carousel.querySelector( '[data-story-carousel-previous]' ),
					nextEl: carousel.querySelector( '[data-story-carousel-next]' ),
				},
				breakpoints: {
					768: { slidesPerView: 2 },
					992: { slidesPerView: 3 },
				},
				on: {
					init: syncExpanded,
					slideChange: syncExpanded,
				},
			} );
		};

		/*
		 * Fewer than three stories cannot fill the carousel, so they use the
		 * 2 Up row presentation (one story spans the row) instead of leaving
		 * empty slide positions.
		 */
		const render = function ( slides ) {
			const isRow = slides.length < 3;

			if ( swiper ) {
				swiper.destroy( true, true );
				swiper = null;
			}

			track.replaceChildren.apply( track, slides );

			slides.forEach( function ( slide ) {
				const image = slide.querySelector( '.ltt-story-card__media img' );

				slide.classList.remove( 'is-expanded' );
				slide.classList.toggle( 'ltt-story-card--tile', ! isRow );

				if ( image && image.sizes ) {
					image.dataset.carouselSizes = image.dataset.carouselSizes || image.sizes;
					image.sizes = isRow ? '(max-width: 767.98px) 100vw, ' + Math.ceil( 1436 / slides.length * 1.5 ) + 'px' : image.dataset.carouselSizes;
				}
			} );

			if ( isRow ) {
				carousel.dataset.visibleCount = String( slides.length );
				return;
			}

			delete carousel.dataset.visibleCount;
			swiper = createSwiper();
		};

		render( allSlides );

		/* The “defined position” for the touch reveal is the middle of the viewport. */
		if ( window.IntersectionObserver ) {
			new window.IntersectionObserver( function ( entries ) {
				inRevealBand = entries[ entries.length - 1 ].isIntersecting;
				syncExpanded( swiper );
			}, { rootMargin: '-35% 0px -35% 0px' } ).observe( carousel );
		}

		if ( noHover ) {
			const onHoverChange = function () {
				syncExpanded( swiper );
			};

			if ( noHover.addEventListener ) {
				noHover.addEventListener( 'change', onHoverChange );
			} else if ( noHover.addListener ) {
				noHover.addListener( onHoverChange );
			}
		}

		filters.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				const value = button.dataset.storyFilter;
				const matches = allSlides.filter( function ( slide ) {
					return 'all' === value || ( slide.dataset.storyTerms || '' ).split( ' ' ).indexOf( value ) !== -1;
				} );

				filters.forEach( function ( item ) {
					item.setAttribute( 'aria-pressed', item === button ? 'true' : 'false' );
				} );

				render( matches );

				if ( status ) {
					status.textContent = format( 1 === matches.length ? strings.storyShown : strings.storiesShown, matches.length );
				}
			} );
		} );
	};

	document.querySelectorAll( '[data-story-feature]' ).forEach( initFeature );
	document.querySelectorAll( '[data-story-carousel-section]' ).forEach( initCarousel );
}() );
