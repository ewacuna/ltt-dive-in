/**
 * Swiper fades the 404 and controlled-503 background photos.
 * The first photo is in the HTML and remains visible without JavaScript.
 */
( function () {
	'use strict';

	const page = document.querySelector( '.ltt-error-page[data-background-images]' );
	const layer = page?.querySelector( '.ltt-error-page__background' );
	const track = layer?.querySelector( '.ltt-error-page__background-track' );
	if ( ! page || ! layer || ! track ) {
		return;
	}

	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	const interval = Math.min( 60, Math.max( 5, Number( page.dataset.backgroundInterval ) || 10 ) ) * 1000;
	const requests = new WeakMap();
	let swiper = null;
	let generation = 0;

	function slides() {
		return Array.from( track.children ).filter( ( slide ) => slide.classList.contains( 'swiper-slide' ) );
	}

	function imageSize( image ) {
		const ratio = Number( image.dataset.ratio ) || 1;
		return Math.ceil( Math.max( page.clientWidth, page.clientHeight * ratio ) ) + 'px';
	}

	function loadImage( image ) {
		if ( requests.has( image ) ) {
			return requests.get( image );
		}

		const request = new Promise( ( resolve, reject ) => {
			if ( image.complete && image.naturalWidth ) {
				resolve();
				return;
			}

		let timeout;
			const finish = ( success ) => {
				window.clearTimeout( timeout );
				image.removeEventListener( 'load', onLoad );
				image.removeEventListener( 'error', onError );
				( success ? resolve : reject )();
			};
			const onLoad = () => finish( true );
			const onError = () => finish( false );
			image.addEventListener( 'load', onLoad );
			image.addEventListener( 'error', onError );
			timeout = window.setTimeout( onError, 15000 );

			if ( image.dataset.src ) {
				image.sizes = imageSize( image );
				if ( image.dataset.srcset ) {
					image.srcset = image.dataset.srcset;
				}
				image.src = image.dataset.src;
			} else if ( image.complete ) {
				onError();
			}
		} );

		requests.set( image, request );
		return request;
	}

	function restoreFallback() {
		if ( swiper ) {
			swiper.destroy( true, true );
		}
		layer.remove();
		page.removeAttribute( 'data-background-images' );
	}

	async function prepareNext() {
		while ( swiper && swiper.slides.length > 1 ) {
			const index = ( swiper.activeIndex + 1 ) % swiper.slides.length;
			const slide = swiper.slides[ index ];
			try {
				await loadImage( slide.querySelector( 'img' ) );
				return true;
			} catch ( error ) {
				swiper.removeSlide( index );
			}
		}
		return false;
	}

	async function arm() {
		const request = ++generation;
		swiper.autoplay.stop();
		if ( reducedMotion.matches || document.hidden ) {
			return;
		}

		const ready = await prepareNext();
		if ( request === generation && ready && ! swiper.destroyed && ! document.hidden && ! reducedMotion.matches ) {
			swiper.autoplay.start();
		}
	}

	function startSwiper() {
		if ( swiper || ! window.Swiper || slides().length < 2 || reducedMotion.matches ) {
			return;
		}

		swiper = new window.Swiper( layer, {
			init: false,
			effect: 'fade',
			fadeEffect: { crossFade: true },
			speed: 300,
			slidesPerView: 1,
			rewind: true,
			allowTouchMove: false,
			simulateTouch: false,
			a11y: false,
			autoplay: { delay: interval, disableOnInteraction: false },
			on: { slideChangeTransitionEnd: arm },
		} );
		swiper.init();
		swiper.autoplay.stop();
		arm();
	}

	document.addEventListener( 'visibilitychange', () => {
		if ( swiper ) {
			if ( document.hidden ) {
				++generation;
				swiper.autoplay.stop();
			} else {
				arm();
			}
		}
	} );

	const onMotionChange = () => {
		if ( reducedMotion.matches ) {
			++generation;
			if ( swiper ) {
				swiper.autoplay.stop();
				swiper.slideTo( 0, 0 );
			}
		} else if ( swiper ) {
			arm();
		} else {
			startSwiper();
		}
	};
	if ( reducedMotion.addEventListener ) {
		reducedMotion.addEventListener( 'change', onMotionChange );
	} else {
		reducedMotion.addListener( onMotionChange );
	}

	window.addEventListener( 'resize', () => {
		track.querySelectorAll( 'img[src][data-ratio]' ).forEach( ( image ) => {
			image.sizes = imageSize( image );
		} );
	} );

	( async () => {
		while ( slides().length ) {
			const first = slides()[0];
			try {
				await loadImage( first.querySelector( 'img' ) );
				startSwiper();
				return;
			} catch ( error ) {
				first.remove();
			}
		}
		restoreFallback();
	} )();
}() );
