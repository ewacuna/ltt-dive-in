/**
 * Accessible lightbox enhancement for Static Image Cluster blocks.
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_static_image_cluster || {};

	document.querySelectorAll( '.static-image-cluster--inspired' ).forEach( function ( cluster ) {
		const loadMore = cluster.querySelector( '[data-inspired-load-more]' );
		const gallery = cluster.querySelector( '.static-image-cluster__gallery' );
		const status = loadMore ? loadMore.querySelector( '[data-inspired-load-more-status]' ) : null;
		const label = loadMore ? loadMore.querySelector( '[data-inspired-load-more-label]' ) : null;
		const icon = loadMore ? loadMore.querySelector( '[data-inspired-load-more-icon]' ) : null;
		const defaultLabel = label ? label.textContent : '';

		if ( ! loadMore || ! gallery || ! strings.loadMoreEndpoint ) {
			return;
		}

		loadMore.addEventListener( 'click', function () {
			if ( loadMore.disabled ) {
				return;
			}

			const requestUrl = new URL( strings.loadMoreEndpoint, window.location.origin );

			requestUrl.searchParams.set( 'post', loadMore.dataset.loadMorePost || '' );
			requestUrl.searchParams.set( 'block', loadMore.dataset.loadMoreBlock || '' );
			requestUrl.searchParams.set( 'dialog', loadMore.dataset.loadMoreDialog || '' );
			requestUrl.searchParams.set( 'offset', loadMore.dataset.loadMoreOffset || '0' );
			loadMore.disabled = true;
			loadMore.setAttribute( 'aria-busy', 'true' );

			if ( label ) {
				label.textContent = strings.loading || 'Loading…';
			}

			if ( icon ) {
				icon.hidden = true;
			}

			if ( status ) {
				status.textContent = strings.loading || 'Loading…';
			}

			window.fetch( requestUrl.toString(), { credentials: 'same-origin' } )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'Unable to load gallery images.' );
					}

					return response.json();
				} )
				.then( function ( payload ) {
					if ( ! payload || ! payload.html || ! Array.isArray( payload.images ) ) {
						throw new Error( 'Invalid gallery response.' );
					}

					gallery.insertAdjacentHTML( 'beforeend', payload.html );
					loadMore.dataset.loadMoreOffset = String( payload.nextOffset || 0 );

					const dialog = cluster.querySelector( '[data-ltt-lightbox]' );

					if ( dialog ) {
						dialog.dispatchEvent( new CustomEvent( 'ltt-lightbox:items-added', { detail: { items: payload.images } } ) );
					}

					if ( payload.hasMore ) {
						loadMore.disabled = false;
						loadMore.removeAttribute( 'aria-busy' );

						if ( label ) {
							label.textContent = defaultLabel;
						}

						if ( icon ) {
							icon.hidden = false;
						}

						if ( status ) {
							status.textContent = '';
						}
					} else {
						loadMore.remove();
					}
				} )
				.catch( function () {
					loadMore.disabled = false;
					loadMore.removeAttribute( 'aria-busy' );

					if ( label ) {
						label.textContent = defaultLabel;
					}

					if ( icon ) {
						icon.hidden = false;
					}

					if ( status ) {
						status.textContent = strings.loadMoreError || 'More images could not be loaded. Please try again.';
					}
				} );
		} );
	} );

	document.querySelectorAll( '.static-image-cluster--has-lightbox' ).forEach( function ( cluster ) {
		const dialog = cluster.querySelector( '[data-static-image-cluster-lightbox]' );
		const dataElement = dialog ? dialog.querySelector( '[data-static-image-cluster-data]' ) : null;

		if ( ! dialog || ! dataElement || typeof dialog.showModal !== 'function' ) {
			return;
		}

		let images;

		try {
			images = JSON.parse( dataElement.textContent );
		} catch ( error ) {
			return;
		}

		if ( ! Array.isArray( images ) || images.length < 3 ) {
			return;
		}

		const triggers = cluster.querySelectorAll( '[data-static-image-cluster-trigger]' );
		const image = dialog.querySelector( '[data-static-image-cluster-image]' );
		const caption = dialog.querySelector( '[data-static-image-cluster-caption]' );
		const count = dialog.querySelector( '[data-static-image-cluster-count]' );
		const close = dialog.querySelector( '[data-static-image-cluster-close]' );
		const previous = dialog.querySelector( '[data-static-image-cluster-previous]' );
		const next = dialog.querySelector( '[data-static-image-cluster-next]' );
		let currentIndex = 0;
		let returnFocus = null;
		let touchStartX = null;

		const showImage = function ( requestedIndex ) {
			currentIndex = ( requestedIndex + images.length ) % images.length;
			const current = images[ currentIndex ];

			image.src = current.full;
			image.alt = current.alt;
			if ( current.srcset ) {
				image.srcset = current.srcset;
				image.sizes = '90vw';
			} else {
				image.removeAttribute( 'srcset' );
				image.removeAttribute( 'sizes' );
			}
			caption.textContent = current.caption || '';
			count.textContent = ( strings.imageCount || 'Image %1$d of %2$d' )
				.replace( '%1$d', currentIndex + 1 )
				.replace( '%2$d', images.length );
		};

		triggers.forEach( function ( trigger ) {
			trigger.addEventListener( 'click', function () {
				returnFocus = trigger;
				showImage( Number.parseInt( trigger.dataset.imageIndex, 10 ) || 0 );
				dialog.showModal();
				close.focus();
			} );
		} );

		previous.addEventListener( 'click', function () {
			showImage( currentIndex - 1 );
		} );

		next.addEventListener( 'click', function () {
			showImage( currentIndex + 1 );
		} );

		close.addEventListener( 'click', function () {
			dialog.close();
		} );

		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				showImage( currentIndex - 1 );
			} else if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				showImage( currentIndex + 1 );
			}
		} );

		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );

		dialog.addEventListener( 'close', function () {
			image.removeAttribute( 'src' );
			image.removeAttribute( 'srcset' );
			image.removeAttribute( 'sizes' );

			if ( returnFocus && document.contains( returnFocus ) ) {
				returnFocus.focus();
			}
		} );

		dialog.addEventListener( 'touchstart', function ( event ) {
			if ( 1 === event.changedTouches.length ) {
				touchStartX = event.changedTouches[ 0 ].clientX;
			}
		}, { passive: true } );

		dialog.addEventListener( 'touchend', function ( event ) {
			if ( null === touchStartX || 1 !== event.changedTouches.length ) {
				touchStartX = null;
				return;
			}

			const distance = event.changedTouches[ 0 ].clientX - touchStartX;
			touchStartX = null;

			if ( Math.abs( distance ) < 50 ) {
				return;
			}

			showImage( currentIndex + ( distance < 0 ? 1 : -1 ) );
		}, { passive: true } );
	} );

	if ( ! window.Swiper ) {
		return;
	}

	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	document.querySelectorAll( '[data-static-image-cluster-carousel]' ).forEach( function ( carousel ) {
		const viewport = carousel.querySelector( '.static-image-cluster__carousel-viewport' );
		const slides = carousel.querySelectorAll( '.static-image-cluster__item' );
		const previous = carousel.querySelector( '[data-static-image-cluster-carousel-previous]' );
		const next = carousel.querySelector( '[data-static-image-cluster-carousel-next]' );
		const pagination = carousel.querySelector( '[data-static-image-cluster-carousel-pagination]' );

		if ( ! viewport || viewport.classList.contains( 'swiper-initialized' ) || ! slides.length ) {
			return;
		}

		const updatePaginationState = function ( instance ) {
			if ( ! instance.pagination || ! instance.pagination.bullets ) {
				return;
			}

			instance.pagination.bullets.forEach( function ( bullet, index ) {
				bullet.setAttribute( 'aria-current', index === instance.snapIndex ? 'true' : 'false' );
				bullet.setAttribute( 'aria-label', ( strings.goToPosition || 'Go to gallery position %d' ).replace( '%d', index + 1 ) );
			} );
		};

		new window.Swiper( viewport, {
			slidesPerView: 'auto',
			spaceBetween: 11,
			speed: reducedMotion ? 0 : 350,
			rewind: slides.length > 1,
			watchOverflow: true,
			keyboard: {
				enabled: true,
				onlyInViewport: true,
			},
			a11y: {
				enabled: true,
				prevSlideMessage: strings.previousImages || 'Previous images',
				nextSlideMessage: strings.nextImages || 'Next images',
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
} )();
