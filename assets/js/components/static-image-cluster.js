/**
 * Progressive loading and inline details for Inspired galleries, plus existing cluster carousels.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_static_image_cluster || {};
	const mobileBreakpoint = '(max-width: 767.98px)';
	const reducedMotionQuery = '(prefers-reduced-motion: reduce)';
	let activeInlineDetails = null;

	/**
	 * @typedef {Object} InspiredImage
	 * @property {string} src Full-size image URL.
	 * @property {string} srcset Responsive image candidates.
	 * @property {number} width Image width in pixels.
	 * @property {number} height Image height in pixels.
	 * @property {string} alt Alternative text.
	 * @property {string} title Editorial title.
	 * @property {string} description Editorial description.
	 */

	/**
	 * @typedef {Object} InspiredLoadMoreResponse
	 * @property {string} html Markup for the next image batch.
	 * @property {InspiredImage[]} images Image records corresponding to the markup.
	 * @property {number} nextOffset Offset to use for the next request.
	 * @property {boolean} hasMore Whether another batch is available.
	 */

	/**
	 * @typedef {Object} InlineDetailsController
	 * @property {(records: Object[], batch: Element) => void} appendImages Add and prepare a newly loaded batch.
	 */

	/**
	 * Normalize REST and initial gallery records to the inline panel's private shape.
	 *
	 * @param {Object[]} records Raw image records from WordPress.
	 * @return {InspiredImage[]} Normalized records in gallery order.
	 */
	function normalizeImages( records ) {
		return records.map( function ( image ) {
			return {
				src: image.full || image.src || '',
				srcset: image.srcset || '',
				width: Number( image.width ) || 1,
				height: Number( image.height ) || 1,
				alt: image.alt || '',
				title: image.title || '',
				description: image.description || '',
			};
		} );
	}

	/**
	 * Read the initial six image records used by inline detail.
	 *
	 * @param {Element} cluster Inspired Gallery root.
	 * @return {InspiredImage[]} Initial records, or an empty array for invalid JSON.
	 */
	function readInitialImages( cluster ) {
		const source = cluster.querySelector( '[data-ltt-photoswipe-data]' );

		if ( ! source ) {
			return [];
		}

		try {
			const records = JSON.parse( source.textContent );

			return Array.isArray( records ) ? normalizeImages( records ) : [];
		} catch ( error ) {
			return [];
		}
	}

	/**
	 * Initialize inline behavior for one gallery. Image data remains private to this controller.
	 *
	 * @param {Element} cluster Gallery root.
	 * @param {Element} gallery Gallery batch container.
	 * @return {InlineDetailsController|null} Inline controller, if its required markup exists.
	 */
	function initializeInlineDetails( cluster, gallery ) {
		const details = cluster.querySelector( '[data-inspired-mobile-details]' );

		if ( ! details ) {
			return null;
		}

		const imageElement = details.querySelector( '[data-inspired-details-image]' );
		const descriptionElement = details.querySelector( '[data-inspired-details-description]' );
		const titleElement = details.querySelector( '[data-inspired-details-title]' );
		const statusElement = details.querySelector( '[data-inspired-details-status]' );
		const closeButton = details.querySelector( '[data-inspired-details-close]' );
		const moreButton = details.querySelector( '[data-inspired-details-more]' );
		const mobileQuery = window.matchMedia( mobileBreakpoint );
		const reducedMotion = window.matchMedia( reducedMotionQuery );
		const stickyElements = Array.from( document.querySelectorAll( '#wpadminbar, .site-header' ) );
		let images = readInitialImages( cluster );
		let activeTrigger = null;
		let selectedIndex = -1;
		let selectionToken = 0;
		let closeTimer = 0;
		let layoutFrame = 0;
		let currentStickyOffset = -1;

		if ( ! imageElement || ! descriptionElement || ! titleElement || ! closeButton || ! moreButton ) {
			return null;
		}

		/** Update the control state for the current viewport-specific inline interaction. */
		function updateTriggerAttributes( trigger ) {
			const description = trigger.querySelector( '[data-inspired-card-description]' );

			if ( mobileQuery.matches ) {
				trigger.removeAttribute( 'aria-haspopup' );
				trigger.setAttribute( 'aria-controls', details.id );
				trigger.setAttribute( 'aria-expanded', String( Number( trigger.dataset.imageIndex ) === selectedIndex && ! details.hidden ) );
				trigger.removeAttribute( 'aria-describedby' );
				return;
			}

			trigger.removeAttribute( 'aria-controls' );
			trigger.removeAttribute( 'aria-expanded' );
			trigger.removeAttribute( 'aria-haspopup' );
			trigger.removeAttribute( 'aria-describedby' );

			if ( description && description.id ) {
				trigger.setAttribute( 'aria-describedby', description.id );
			}
		}

		/**
		 * Convert uninitialized image links into inline action buttons once.
		 *
		 * @param {ParentNode} root Gallery or newly inserted batch.
		 */
		function prepareTriggers( root ) {
			root.querySelectorAll( 'a[data-ltt-photoswipe-trigger]' ).forEach( function ( link ) {
				const button = document.createElement( 'button' );
				const description = link.querySelector( '[data-inspired-card-description]' );
				const actionLabel = link.dataset.inlineActionLabel;
				const hadFocus = document.activeElement === link;

				Array.from( link.attributes ).forEach( function ( attribute ) {
					if ( ! [ 'href', 'target', 'rel' ].includes( attribute.name ) ) {
						button.setAttribute( attribute.name, attribute.value );
					}
				} );
				button.type = 'button';
				button.removeAttribute( 'data-inline-action-label' );

				if ( actionLabel ) {
					button.setAttribute( 'aria-label', actionLabel );
				}

				if ( description ) {
					description.id = cluster.id + '-description-' + link.dataset.imageIndex;
				}

				button.append( ...link.childNodes );
				link.replaceWith( button );
				updateTriggerAttributes( button );

				if ( hadFocus ) {
					button.focus( { preventScroll: true } );
				}
			} );
		}

		/** Find the lower edge of a fixed or sticky site header currently occupying the viewport. */
		function getStickyOffset() {
			let offset = 0;

			stickyElements.forEach( function ( element ) {
				const bounds = element.getBoundingClientRect();
				const position = window.getComputedStyle( element ).position;

				if ( ( 'fixed' === position || 'sticky' === position ) && bounds.top <= offset + 1 && bounds.bottom > offset ) {
					offset = Math.max( offset, bounds.bottom );
				}
			} );

			return offset;
		}

		/** Return the visual viewport bounds, accounting for mobile browser chrome. */
		function getVisibleViewport() {
			const viewport = window.visualViewport;
			const top = viewport ? viewport.offsetTop : 0;

			return {
				top: top,
				bottom: top + ( viewport ? viewport.height : window.innerHeight ),
			};
		}

		/** Update sticky positioning, focus clearance, and the description disclosure. */
		function updateDetailsLayout() {
			if ( details.hidden ) {
				return;
			}

			const headerOffset = getStickyOffset();
			const detailsHeight = details.scrollHeight;
			currentStickyOffset = headerOffset;
			details.style.setProperty( '--inspired-details-sticky-top', headerOffset + 'px' );
			details.classList.add( 'is-sticky' );
			details.style.setProperty( '--inspired-details-height', detailsHeight + 'px' );
			cluster.style.setProperty( '--inspired-details-focus-offset', ( headerOffset + detailsHeight + 5 ) + 'px' );
			updateReadMore();
		}

		/** Coalesce viewport and scroll measurements into one animation frame. */
		function scheduleLayoutUpdate() {
			if ( layoutFrame ) {
				return;
			}

			layoutFrame = window.requestAnimationFrame( function () {
				layoutFrame = 0;
				updateDetailsLayout();
			} );
		}

		/** Check the changing header offset during scroll without remeasuring stable layouts. */
		function checkStickyOffset() {
			if ( details.hidden || layoutFrame ) {
				return;
			}

			layoutFrame = window.requestAnimationFrame( function () {
				layoutFrame = 0;

				if ( getStickyOffset() !== currentStickyOffset ) {
					updateDetailsLayout();
				}
			} );
		}

		/** Release the selection-height lock before recalculating after a viewport change. */
		function handleViewportChange() {
			details.style.removeProperty( 'min-height' );
			scheduleLayoutUpdate();
		}

		/** Keep the Read more control in sync with the actual clamped description height. */
		function updateReadMore() {
			if ( ! moreButton || ! descriptionElement ) {
				return;
			}

			const expanded = details.classList.contains( 'is-expanded' );
			const clamped = descriptionElement.scrollHeight > descriptionElement.clientHeight + 1;

			moreButton.hidden = ! clamped && ! expanded;
			moreButton.textContent = expanded ? ( strings.readLess || 'Read less' ) : ( strings.readMore || 'Read more' );
			moreButton.setAttribute( 'aria-expanded', String( expanded ) );
		}

		/** Keep a focused control clear of the header, sticky detail panel, and viewport edges. */
		function revealFocusedElement( element ) {
			if ( ! element ) {
				return;
			}

			const bounds = element.getBoundingClientRect();
			const viewport = getVisibleViewport();
			let top = Math.max( getStickyOffset(), viewport.top );
			let adjustment = 0;

			if ( details.classList.contains( 'is-sticky' ) ) {
				top = Math.max( top, details.getBoundingClientRect().bottom + 5 );
			}

			if ( bounds.top < top ) {
				adjustment = bounds.top - top;
			} else if ( bounds.bottom > viewport.bottom ) {
				adjustment = bounds.bottom - viewport.bottom;
			}

			if ( adjustment ) {
				window.scrollBy( {
					top: adjustment,
					behavior: reducedMotion.matches ? 'auto' : 'smooth',
				} );
			}
		}

		/**
		 * Close the panel and invalidate any image request still in flight.
		 *
		 * @param {boolean} restoreFocus Return focus to the active card.
		 * @param {boolean} animate Animate the panel close unless motion is reduced.
		 */
		function closeDetails( restoreFocus, animate ) {
			if ( details.hidden ) {
				return;
			}

			const shouldAnimate = Boolean( animate && ! reducedMotion.matches );

			if ( activeInlineDetails === details ) {
				activeInlineDetails = null;
			}

			window.clearTimeout( closeTimer );
			selectionToken += 1;
			details.classList.remove( 'is-open', 'is-expanded', 'is-error', 'is-sticky' );
			details.classList.toggle( 'is-closing', shouldAnimate );
			details.style.removeProperty( 'min-height' );
			details.setAttribute( 'aria-busy', 'false' );
			details.setAttribute( 'aria-hidden', 'true' );
			details.inert = true;
			gallery.querySelectorAll( '[data-ltt-photoswipe-trigger]' ).forEach( function ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			} );

			/** Finish hiding the panel once its optional close animation ends. */
			function finishClose() {
				details.hidden = true;
				details.classList.remove( 'is-closing' );
				details.style.removeProperty( '--inspired-details-height' );
				details.style.removeProperty( 'min-height' );
				cluster.style.removeProperty( '--inspired-details-focus-offset' );

				if ( restoreFocus && activeTrigger ) {
					activeTrigger.focus( { preventScroll: true } );
					revealFocusedElement( activeTrigger );
				}
			}

			if ( shouldAnimate ) {
				closeTimer = window.setTimeout( finishClose, 300 );
				return;
			}

			finishClose();
		}

		/**
		 * Load one image into the shared panel. A token ensures the last rapid selection wins.
		 *
		 * @param {HTMLButtonElement} trigger Selected card.
		 * @param {boolean} moveFocus Whether keyboard activation moves focus into the panel.
		 */
		function selectImage( trigger, moveFocus ) {
			const index = Number( trigger.dataset.imageIndex );
			const image = images[ index ];

			if ( ! image ) {
				return;
			}

			const wasHidden = details.hidden;
			const token = ++selectionToken;
			const previousHeight = wasHidden ? 0 : details.getBoundingClientRect().height;

			activeTrigger = trigger;
			selectedIndex = index;
			activeInlineDetails = details;
			window.clearTimeout( closeTimer );
			details.classList.remove( 'is-closing' );
			details.inert = false;
			details.setAttribute( 'aria-hidden', 'false' );

			if ( wasHidden ) {
				details.hidden = false;
				window.requestAnimationFrame( function () {
					details.classList.add( 'is-open' );
				} );
			} else {
				details.style.minHeight = Math.ceil( previousHeight ) + 'px';
				details.classList.add( 'is-open', 'is-changing' );
			}

			gallery.querySelectorAll( '[data-ltt-photoswipe-trigger]' ).forEach( function ( item ) {
				item.setAttribute( 'aria-expanded', String( item === trigger ) );
			} );
			details.classList.remove( 'is-error', 'is-expanded' );
			details.setAttribute( 'aria-busy', 'true' );
			titleElement.textContent = '';
			descriptionElement.textContent = '';
			imageElement.removeAttribute( 'src' );
			imageElement.removeAttribute( 'srcset' );

			if ( moreButton ) {
				moreButton.hidden = true;
			}

			updateDetailsLayout();

			const preload = new window.Image();
			preload.onload = function () {
				if ( token !== selectionToken ) {
					return;
				}

				imageElement.src = image.src;
				imageElement.srcset = image.srcset;
				descriptionElement.textContent = image.description;
				titleElement.textContent = image.title || image.alt;
				details.classList.remove( 'is-changing', 'is-error' );
				details.setAttribute( 'aria-busy', 'false' );

				if ( statusElement ) {
					statusElement.textContent = ( strings.selectedImage || 'Image %1$d selected: %2$s' )
						.replace( '%1$d', String( index + 1 ) )
						.replace( '%2$s', image.title || image.alt );
				}

				updateDetailsLayout();
				window.requestAnimationFrame( updateReadMore );
				window.requestAnimationFrame( function () {
					if ( wasHidden ) {
						revealFocusedElement( trigger );
					}
				} );

				if ( moveFocus ) {
					details.setAttribute( 'tabindex', '-1' );
					details.focus( { preventScroll: true } );
				}
			};

			preload.onerror = function () {
				if ( token !== selectionToken ) {
					return;
				}

				titleElement.textContent = '';
				descriptionElement.textContent = strings.imageError || 'This image could not be loaded. Select it again to retry.';
				details.classList.add( 'is-error' );
				details.classList.remove( 'is-changing' );
				details.setAttribute( 'aria-busy', 'false' );

				if ( statusElement ) {
					statusElement.textContent = descriptionElement.textContent;
				}

				updateDetailsLayout();
			};
			preload.src = image.src;
		}

		/** Close a mobile detail panel before desktop hover/focus interaction resumes. */
		function updateViewportSemantics() {
			if ( ! mobileQuery.matches && ! details.hidden ) {
				closeDetails( false, false );
			}

			gallery.querySelectorAll( '[data-ltt-photoswipe-trigger]' ).forEach( updateTriggerAttributes );
		}

		/** Append batch data and upgrade only the links in that new batch. */
		function appendImages( records, batch ) {
			images.push.apply( images, normalizeImages( records ) );
			prepareTriggers( batch );
		}

		prepareTriggers( gallery );
		mobileQuery.addEventListener( 'change', updateViewportSemantics );
		window.addEventListener( 'resize', handleViewportChange );
		window.addEventListener( 'orientationchange', handleViewportChange );

		if ( window.visualViewport ) {
			window.visualViewport.addEventListener( 'resize', handleViewportChange );
		}

		window.addEventListener( 'scroll', function () {
			checkStickyOffset();
		}, { passive: true } );

		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( scheduleLayoutUpdate );
		}

		gallery.addEventListener( 'click', function ( event ) {
			const trigger = event.target instanceof Element ? event.target.closest( 'button[data-ltt-photoswipe-trigger]' ) : null;

			if ( ! trigger || ! gallery.contains( trigger ) || ! mobileQuery.matches ) {
				return;
			}

			event.preventDefault();
			selectImage( trigger, event.detail === 0 );
		} );

		closeButton.addEventListener( 'click', function () {
			closeDetails( true, true );
		} );

		moreButton.addEventListener( 'click', function () {
			const expanded = ! details.classList.contains( 'is-expanded' );

			details.style.removeProperty( 'min-height' );
			details.classList.toggle( 'is-expanded', expanded );
			updateReadMore();
			scheduleLayoutUpdate();
			window.requestAnimationFrame( function () {
				if ( moreButton.hidden && activeTrigger ) {
					activeTrigger.focus( { preventScroll: true } );
				} else {
					moreButton.focus();
				}
			} );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && activeInlineDetails === details && ! details.hidden && mobileQuery.matches ) {
				closeDetails( true, true );
			}
		} );

		return { appendImages: appendImages };
	}

	/**
	 * Initialize progressive loading for one Inspired Gallery.
	 *
	 * @param {Element} cluster Gallery root.
	 * @param {Element} gallery Gallery batch container.
	 * @param {InlineDetailsController|null} inlineController Inline-detail controller, when active.
	 */
	function initializeLoadMore( cluster, gallery, inlineController ) {
		const button = cluster.querySelector( '[data-inspired-load-more]' );
		const endpoint = strings.loadMoreEndpoint;

		if ( ! button || ! endpoint ) {
			return;
		}

		const label = button.querySelector( '[data-inspired-load-more-label]' );
		const icon = button.querySelector( '[data-inspired-load-more-icon]' );
		const status = button.querySelector( '[data-inspired-load-more-status]' );
		const defaultLabel = label ? label.textContent : '';

		/** Set the loading presentation and announce the current request state. */
		function setLoading( loading ) {
			button.disabled = loading;
			button.toggleAttribute( 'aria-busy', loading );

			if ( label ) {
				label.textContent = loading ? ( strings.loading || 'Loading…' ) : defaultLabel;
			}

			if ( icon ) {
				icon.hidden = loading;
			}
		}

		button.addEventListener( 'click', function () {
			if ( button.disabled ) {
				return;
			}

			const configuredEndpoint = new URL( endpoint, window.location.href );
			const requestUrl = new URL( configuredEndpoint.pathname + configuredEndpoint.search, window.location.origin );
			requestUrl.searchParams.set( 'post', button.dataset.loadMorePost || '' );
			requestUrl.searchParams.set( 'block', button.dataset.loadMoreBlock || '' );
			requestUrl.searchParams.set( 'offset', button.dataset.loadMoreOffset || '0' );
			setLoading( true );

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
					button.dataset.loadMoreOffset = String( payload.nextOffset || 0 );
					const batch = gallery.lastElementChild;

					if ( inlineController && batch ) {
						inlineController.appendImages( payload.images, batch );
					}

					if ( ! inlineController ) {
						cluster.dispatchEvent( new CustomEvent( 'ltt-photoswipe:items-added', { detail: { items: payload.images } } ) );
					}

					if ( payload.hasMore ) {
						setLoading( false );
						if ( status ) {
							status.textContent = '';
						}
						return;
					}

					if ( button.contains( document.activeElement ) && payload.nextOffset > 0 ) {
						const lastTrigger = gallery.querySelector( '[data-ltt-photoswipe-trigger][data-image-index="' + ( payload.nextOffset - 1 ) + '"]' );

						if ( lastTrigger ) {
							lastTrigger.focus();
						}
					}

					button.remove();
				} )
				.catch( function () {
					setLoading( false );

					if ( status ) {
						status.textContent = strings.loadMoreError || 'More images could not be loaded. Please try again.';
					}
				} );
		} );
	}

	/** Initialize Inspired Gallery behavior without touching unrelated cluster variants. */
	function initializeInspiredGallery( cluster ) {
		const gallery = cluster.querySelector( '.static-image-cluster__gallery' );

		if ( ! gallery ) {
			return;
		}

		const inlineController = 'inline_detail' === cluster.dataset.imageDisplay
			? initializeInlineDetails( cluster, gallery )
			: null;

		initializeLoadMore( cluster, gallery, inlineController );
	}

	document.querySelectorAll( '.static-image-cluster--inspired' ).forEach( initializeInspiredGallery );
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
		let swiperInstance;

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

		const engageFullWidth = function () {
			if ( carousel.classList.contains( 'is-carousel-engaged' ) ) {
				return;
			}

			carousel.classList.add( 'is-carousel-engaged' );

			if ( swiperInstance ) {
				window.requestAnimationFrame( function () {
					swiperInstance.update();
				} );
			}
		};

		carousel.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-static-image-cluster-carousel-previous], [data-static-image-cluster-carousel-next], [data-static-image-cluster-carousel-pagination] .ltt-carousel-indicator' ) ) {
				engageFullWidth();
			}
		}, true );

		swiperInstance = new window.Swiper( viewport, {
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
				sliderFirstMove: engageFullWidth,
				slideNextTransitionStart: engageFullWidth,
				slidePrevTransitionStart: engageFullWidth,
				slideChange: updatePaginationState,
				paginationUpdate: updatePaginationState,
			},
		} );
	} );
} )();
