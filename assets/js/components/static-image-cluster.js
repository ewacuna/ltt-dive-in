/**
 * Static Image Cluster interactions.
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_static_image_cluster || {};
	let activeInlineDetails = null;

	document.querySelectorAll( '.static-image-cluster--inspired' ).forEach( function ( cluster ) {
		const loadMore = cluster.querySelector( '[data-inspired-load-more]' );
		const gallery = cluster.querySelector( '.static-image-cluster__gallery' );
		const status = loadMore ? loadMore.querySelector( '[data-inspired-load-more-status]' ) : null;
		const label = loadMore ? loadMore.querySelector( '[data-inspired-load-more-label]' ) : null;
		const icon = loadMore ? loadMore.querySelector( '[data-inspired-load-more-icon]' ) : null;
		const defaultLabel = label ? label.textContent : '';

		if ( ! gallery ) {
			return;
		}

		const details = cluster.querySelector( '[data-inspired-mobile-details]' );
		const detailsImage = details ? details.querySelector( '[data-inspired-details-image]' ) : null;
		const detailsDescription = details ? details.querySelector( '[data-inspired-details-description]' ) : null;
		const detailsTitle = details ? details.querySelector( '[data-inspired-details-title]' ) : null;
		const detailsStatus = details ? details.querySelector( '[data-inspired-details-status]' ) : null;
		const detailsClose = details ? details.querySelector( '[data-inspired-details-close]' ) : null;
		const detailsMore = details ? details.querySelector( '[data-inspired-details-more]' ) : null;
		const inlineMobile = window.matchMedia( '(max-width: 767.98px)' );
		const photoswipeData = cluster.querySelector( '[data-ltt-photoswipe-data]' );
		let inlineImages = [];
		let activeTrigger = null;
		let selectedIndex = -1;
		let selectionToken = 0;
		let closeTimer;
		let stickyHeaderOffset = -1;
		let stickyHeaderFrame = 0;

		try {
			inlineImages = Array.isArray( cluster.lttDiveInGalleryImages )
				? cluster.lttDiveInGalleryImages
				: photoswipeData
					? JSON.parse( photoswipeData.textContent ).map( function ( image ) {
						return {
							src: image.full || image.src || '',
							srcset: image.srcset || '',
							width: Number( image.width ) || 1,
							height: Number( image.height ) || 1,
							alt: image.alt || '',
							title: image.title || '',
							description: image.description || '',
						};
					} )
					: [];
		} catch ( error ) {
			inlineImages = [];
		}

		const getImage = function ( index ) {
			return inlineImages[ index ] || null;
		};

		const updateTriggerAttributes = function ( trigger ) {
			if ( inlineMobile.matches ) {
				trigger.removeAttribute( 'aria-haspopup' );
				trigger.setAttribute( 'aria-controls', details.id );
				trigger.setAttribute( 'aria-expanded', String( Number( trigger.dataset.imageIndex ) === selectedIndex && ! details.hidden ) );
			} else {
				trigger.removeAttribute( 'aria-controls' );
				trigger.removeAttribute( 'aria-expanded' );
				trigger.setAttribute( 'aria-haspopup', 'dialog' );
			}
		};

		const prepareTriggers = function () {
			gallery.querySelectorAll( 'a[data-ltt-photoswipe-trigger]' ).forEach( function ( link ) {
				const button = document.createElement( 'button' );
				const hadFocus = document.activeElement === link;

				Array.from( link.attributes ).forEach( function ( attribute ) {
					if ( attribute.name !== 'href' && attribute.name !== 'target' && attribute.name !== 'rel' ) {
						button.setAttribute( attribute.name, attribute.value );
					}
				} );
				button.type = 'button';
				button.dataset.fallbackHref = link.href;
				button.append( ...link.childNodes );
				link.replaceWith( button );
				updateTriggerAttributes( button );
				if ( hadFocus ) {
					button.focus( { preventScroll: true } );
				}
			} );
		};

		const setTriggerMode = function () {
			if ( ! inlineMobile.matches && ! details.hidden ) {
				closeDetails( true, false );
			}
			gallery.querySelectorAll( '[data-ltt-photoswipe-trigger]' ).forEach( updateTriggerAttributes );
		};

		const getStickyOffset = function () {
			let offset = 0;

			document.querySelectorAll( '#wpadminbar, .site-header' ).forEach( function ( element ) {
				const position = window.getComputedStyle( element ).position;
				const bounds = element.getBoundingClientRect();

				if ( ( position === 'fixed' || position === 'sticky' ) && bounds.top <= offset + 1 && bounds.bottom > offset ) {
					offset = Math.max( offset, bounds.bottom );
				}
			} );

			return offset;
		};

		const getVisibleViewport = function () {
			const viewport = window.visualViewport;
			const top = viewport ? viewport.offsetTop : 0;
			const height = viewport ? viewport.height : window.innerHeight;

			return { top: top, bottom: top + height };
		};

		const updateDetailsLayout = function () {
			if ( ! details || details.hidden ) {
				return;
			}

			const stickyOffset = getStickyOffset();
			stickyHeaderOffset = stickyOffset;
			details.style.setProperty( '--inspired-details-sticky-top', stickyOffset + 'px' );
			details.classList.add( 'is-sticky' );
			details.style.setProperty( '--inspired-details-height', details.scrollHeight + 'px' );
			cluster.style.setProperty( '--inspired-details-focus-offset', ( stickyOffset + details.scrollHeight + 5 ) + 'px' );
			updateReadMore();
		};

		const updateReadMore = function () {
			if ( ! detailsMore || ! detailsDescription ) {
				return;
			}

			const isClamped = detailsDescription.scrollHeight > detailsDescription.clientHeight + 1;
			detailsMore.hidden = ! isClamped && ! details.classList.contains( 'is-expanded' );
			detailsMore.textContent = details.classList.contains( 'is-expanded' ) ? ( strings.readLess || 'Read less' ) : ( strings.readMore || 'Read more' );
			detailsMore.setAttribute( 'aria-expanded', String( details.classList.contains( 'is-expanded' ) ) );
		};

		const focusVisible = function ( element ) {
			if ( ! element ) {
				return;
			}

			const bounds = element.getBoundingClientRect();
			const visibleViewport = getVisibleViewport();
			let top = Math.max( getStickyOffset(), visibleViewport.top );
			if ( details && details.classList.contains( 'is-sticky' ) ) {
				top = Math.max( top, details.getBoundingClientRect().bottom + 5 );
			}
			const bottom = visibleViewport.bottom;
			let adjustment = 0;

			if ( bounds.top < top ) {
				adjustment = bounds.top - top;
			} else if ( bounds.bottom > bottom ) {
				adjustment = bounds.bottom - bottom;
			}

			if ( adjustment ) {
				window.scrollBy( { top: adjustment, behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );
			}
		};

		const closeDetails = function ( restoreFocus, animate ) {
			if ( ! details || details.hidden ) {
				return;
			}

			const shouldAnimate = Boolean( animate && ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );
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
			cluster.querySelectorAll( '[data-ltt-photoswipe-trigger]' ).forEach( function ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			} );

			const finishClose = function () {
				details.hidden = true;
				details.classList.remove( 'is-closing' );
				details.style.removeProperty( '--inspired-details-height' );
				details.style.removeProperty( 'min-height' );
				cluster.style.removeProperty( '--inspired-details-focus-offset' );
				if ( restoreFocus && activeTrigger ) {
					activeTrigger.focus( { preventScroll: true } );
					focusVisible( activeTrigger );
				}
			};

			if ( shouldAnimate ) {
				closeTimer = window.setTimeout( finishClose, 300 );
			} else {
				finishClose();
			}
		};

		const selectImage = function ( trigger, moveFocus ) {
			const index = Number( trigger.dataset.imageIndex );
			const image = getImage( index );

			if ( ! image || ! details ) {
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
				details.classList.add( 'is-open' );
				details.classList.add( 'is-changing' );
			}

			cluster.querySelectorAll( '[data-ltt-photoswipe-trigger]' ).forEach( function ( item ) {
				item.setAttribute( 'aria-expanded', String( item === trigger ) );
			} );
			details.classList.remove( 'is-error', 'is-expanded' );
			details.setAttribute( 'aria-busy', 'true' );
			detailsTitle.textContent = '';
			detailsDescription.textContent = '';
			detailsImage.removeAttribute( 'src' );
			detailsImage.removeAttribute( 'srcset' );
			if ( detailsMore ) {
				detailsMore.hidden = true;
			}
			updateDetailsLayout();

			const preload = new window.Image();
			preload.onload = function () {
				if ( token !== selectionToken ) {
					return;
				}

				detailsImage.src = image.src;
				detailsImage.srcset = image.srcset || '';
				detailsDescription.textContent = image.description || '';
				detailsTitle.textContent = image.title || image.alt || '';
				details.classList.remove( 'is-changing', 'is-error' );
				details.setAttribute( 'aria-busy', 'false' );
				if ( detailsStatus ) {
					detailsStatus.textContent = ( strings.selectedImage || 'Image %1$d selected: %2$s' ).replace( '%1$d', String( index + 1 ) ).replace( '%2$s', image.title || image.alt || '' );
				}
				updateDetailsLayout();
				window.requestAnimationFrame( updateReadMore );
				window.requestAnimationFrame( function () {
					window.requestAnimationFrame( function () {
						if ( wasHidden ) {
							focusVisible( trigger );
						}
					} );
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

				detailsTitle.textContent = '';
				detailsDescription.textContent = strings.imageError || 'This image could not be loaded. Select it again to retry.';
				details.classList.add( 'is-error' );
				details.classList.remove( 'is-changing' );
				details.setAttribute( 'aria-busy', 'false' );
				if ( detailsStatus ) {
					detailsStatus.textContent = detailsDescription.textContent;
				}
				updateDetailsLayout();
			};
			preload.src = image.src;
		};

		if ( details && cluster.dataset.mobileDisplay === 'inline_detail' ) {
			prepareTriggers();
			inlineMobile.addEventListener( 'change', setTriggerMode );
			const handleViewportChange = function () {
				details.style.removeProperty( 'min-height' );
				updateDetailsLayout();
			};

			window.addEventListener( 'resize', handleViewportChange );
			window.addEventListener( 'orientationchange', handleViewportChange );
			if ( window.visualViewport ) {
				window.visualViewport.addEventListener( 'resize', handleViewportChange );
			}
			window.addEventListener( 'scroll', function () {
				if ( details.hidden || stickyHeaderFrame ) {
					return;
				}

				stickyHeaderFrame = window.requestAnimationFrame( function () {
					stickyHeaderFrame = 0;
					const offset = getStickyOffset();

					if ( offset !== stickyHeaderOffset ) {
						updateDetailsLayout();
					}
				} );
			}, { passive: true } );
			if ( document.fonts && document.fonts.ready ) {
				document.fonts.ready.then( updateDetailsLayout );
			}

			// PhotoSwipe's shared click handler delegates mobile activations here.
			cluster.lttDiveInOpenInlineDetails = function ( trigger, event ) {
				if ( ! inlineMobile.matches ) {
					return false;
				}
				selectImage( trigger, event.detail === 0 );
				return true;
			};

			detailsClose.addEventListener( 'click', function () {
				closeDetails( true, true );
			} );

			detailsMore.addEventListener( 'click', function () {
				const expanded = ! details.classList.contains( 'is-expanded' );
				details.style.removeProperty( 'min-height' );
				details.classList.toggle( 'is-expanded', expanded );
				updateReadMore();
				updateDetailsLayout();
				window.requestAnimationFrame( function () {
					if ( detailsMore.hidden && activeTrigger ) {
						activeTrigger.focus( { preventScroll: true } );
					} else {
						detailsMore.focus();
					}
				} );
			} );

			document.addEventListener( 'keydown', function ( event ) {
				if ( event.key === 'Escape' && activeInlineDetails === details && ! details.hidden && inlineMobile.matches ) {
					closeDetails( true, true );
				}
			} );
		}

		if ( ! loadMore || ! strings.loadMoreEndpoint ) {
			return;
		}

		loadMore.addEventListener( 'click', function () {
			if ( loadMore.disabled ) {
				return;
			}

			const requestUrl = new URL( strings.loadMoreEndpoint, window.location.origin );

			requestUrl.searchParams.set( 'post', loadMore.dataset.loadMorePost || '' );
			requestUrl.searchParams.set( 'block', loadMore.dataset.loadMoreBlock || '' );
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
					if ( details ) {
						if ( ! Array.isArray( cluster.lttDiveInGalleryImages ) ) {
							payload.images.forEach( function ( image ) {
								inlineImages.push( {
									src: image.full || image.src || '',
									srcset: image.srcset || '',
									width: Number( image.width ) || 1,
									height: Number( image.height ) || 1,
									alt: image.alt || '',
									title: image.title || '',
									description: image.description || '',
								} );
							} );
							cluster.lttDiveInGalleryImages = inlineImages;
						}
						prepareTriggers();
					}

					cluster.dispatchEvent( new CustomEvent( 'ltt-photoswipe:items-added', { detail: { items: payload.images } } ) );

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
						if ( loadMore.contains( document.activeElement ) ) {
							const lastTrigger = gallery.querySelector( '[data-ltt-photoswipe-trigger][data-image-index="' + ( Number( payload.nextOffset ) - 1 ) + '"]' );
							if ( lastTrigger ) {
								lastTrigger.focus();
							}
						}
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
