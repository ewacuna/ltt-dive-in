/**
 * Progressive PhotoSwipe enhancement for theme-owned image galleries.
 */
( function () {
	'use strict';

	const reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	const getIconMarkup = function ( url, className ) {
		return '<img class="' + className + '" src="' + url + '" alt="" aria-hidden="true">';
	};

	const getSameOriginModuleUrl = function ( moduleUrl ) {
		const url = new URL( moduleUrl, window.location.href );

		// Dynamic imports require CORS when WordPress's canonical host differs from the current local alias.
		if ( url.origin !== window.location.origin ) {
			url.protocol = window.location.protocol;
			url.host = window.location.host;
		}

		return url.href;
	};

	const getInspiredPadding = function ( viewportSize ) {
		const mobile = window.matchMedia( '(max-width: 767.98px)' ).matches;
		const horizontal = mobile ? 32 : Math.max( 16, ( viewportSize.x - 1152 ) / 2 );
		const minimumTop = mobile ? 72 : 64;
		const minimumBottom = mobile ? 192 : 128;
		const imageHeight = mobile
			? Math.min( 310, Math.max( 224, viewportSize.y - minimumTop - minimumBottom ) )
			: Math.min( 640, Math.max( 384, viewportSize.y - minimumTop - minimumBottom ) );
		const top = Math.max( minimumTop, ( viewportSize.y - imageHeight ) / 2 );

		return {
			left: horizontal,
			right: horizontal,
			top: top,
			bottom: Math.max( 0, viewportSize.y - top - imageHeight ),
		};
	};

	const getInspiredInitialZoom = function ( zoomLevels ) {
		if ( ! zoomLevels.elementSize || ! zoomLevels.panAreaSize ) {
			return zoomLevels.fill;
		}

		return Math.max(
			zoomLevels.panAreaSize.x / zoomLevels.elementSize.x,
			zoomLevels.panAreaSize.y / zoomLevels.elementSize.y
		);
	};

	const positionMobileControls = function ( pswp, imageTop ) {
		const root = pswp.element;

		if ( ! window.matchMedia( '(max-width: 767.98px)' ).matches ) {
			root.style.removeProperty( '--ltt-photoswipe-controls-top' );
			return false;
		}

		if ( ! Number.isFinite( imageTop ) ) {
			const slide = pswp.currSlide;
			const image = slide && slide.container ? slide.container.querySelector( '.pswp__img' ) : null;

			if ( ! image ) {
				return false;
			}

			const rootRect = root.getBoundingClientRect();
			const imageRect = image.getBoundingClientRect();

			if ( ! imageRect.width || ! imageRect.height ) {
				return false;
			}

			imageTop = imageRect.top - rootRect.top;
		}

		const controlsHeight = 38;
		const imageGap = 24;
		const controlsTop = Math.max( 12, imageTop - controlsHeight - imageGap );

		root.style.setProperty( '--ltt-photoswipe-controls-top', controlsTop + 'px' );

		return true;
	};

	const normalizeImages = function ( images ) {
		return images.map( function ( image ) {
			return {
				src: image.full,
				srcset: image.srcset || '',
				width: Number( image.width ) || 1,
				height: Number( image.height ) || 1,
				thumbCropped: true,
				alt: image.alt || '',
				title: image.title || '',
				description: image.description || '',
			};
		} );
	};

	const parseImages = function ( source ) {
		try {
			const images = JSON.parse( source.textContent );

			return Array.isArray( images ) ? normalizeImages( images ) : [];
		} catch ( error ) {
			return [];
		}
	};

	document.querySelectorAll( '[data-ltt-photoswipe]' ).forEach( function ( cluster ) {
		const source = cluster.querySelector( '[data-ltt-photoswipe-data]' );
		const lightboxModule = cluster.dataset.photoswipeLightboxModule ? getSameOriginModuleUrl( cluster.dataset.photoswipeLightboxModule ) : '';
		const coreModule = cluster.dataset.photoswipeCoreModule ? getSameOriginModuleUrl( cluster.dataset.photoswipeCoreModule ) : '';
		const inspired = 'true' === cluster.dataset.photoswipeInspired;
		const locationIcon = cluster.dataset.photoswipeLocationIcon || '';
		let images = source ? parseImages( source ) : [];
		let instancePromise;
		let swipeStart;
		let trigger;

		if ( ! source || ! lightboxModule || ! coreModule || ! images.length ) {
			return;
		}

		const getThumb = function ( index ) {
			return cluster.querySelector( '[data-ltt-photoswipe-trigger][data-image-index="' + index + '"] img' );
		};

		const syncCarouselToImage = function ( index, alignSnap ) {
			const viewport = cluster.querySelector( '.static-image-cluster__carousel-viewport' );
			const carousel = viewport ? viewport.closest( '[data-static-image-cluster-carousel]' ) : null;
			const swiper = viewport && viewport.swiper ? viewport.swiper : null;
			const target = cluster.querySelector( '[data-ltt-photoswipe-trigger][data-image-index="' + index + '"]' );

			if ( ! carousel || ! swiper || swiper.destroyed || ! target ) {
				return null;
			}

			const viewportRect = viewport.getBoundingClientRect();
			const targetRect = target.getBoundingClientRect();
			const isFullyVisible = targetRect.left >= viewportRect.left && targetRect.right <= viewportRect.right;

			if ( ! isFullyVisible || ( alignSnap && swiper.activeIndex !== index ) ) {
				carousel.classList.add( 'is-carousel-engaged' );
				swiper.update();
				swiper.slideTo( index, 0 );
			}

			return target;
		};

		const getTouchPoint = function ( event ) {
			if ( event.type && event.type.endsWith( 'cancel' ) ) {
				return null;
			}

			if ( 'touch' === event.pointerType ) {
				return { x: event.clientX, y: event.clientY, id: event.pointerId };
			}

			if ( event.changedTouches && event.changedTouches.length ) {
				const touch = event.changedTouches[0];

				return { x: touch.clientX, y: touch.clientY, id: touch.identifier };
			}

			return null;
		};

		const renderCaption = function ( caption, location, pswp ) {
			const item = pswp.currSlide ? pswp.currSlide.data : {};
			const hasDescription = Boolean( item.description );
			const hasTitle = Boolean( item.title );

			caption.hidden = ! hasDescription;
			location.hidden = ! hasTitle;
			caption.replaceChildren();
			location.replaceChildren();

			if ( hasTitle ) {
				const icon = document.createElement( 'img' );
				const title = document.createElement( 'h2' );

				icon.src = locationIcon;
				icon.alt = '';
				icon.setAttribute( 'aria-hidden', 'true' );
				title.className = 'ltt-photoswipe__title';
				title.textContent = item.title;
				location.append( icon, title );
			}

			if ( hasDescription ) {
				const description = document.createElement( 'p' );

				description.className = 'ltt-photoswipe__description';
				description.textContent = item.description;
				caption.append( description );
			}
		};

		const positionInspiredContent = function ( caption, location, pswp ) {
			const root = pswp.element;
			const rootRect = root.getBoundingClientRect();

			if ( ! rootRect.width || ! rootRect.height ) {
				return;
			}

			const padding = getInspiredPadding( { x: rootRect.width, y: rootRect.height } );
			const imageBottom = rootRect.height - padding.bottom;
			const imageCenter = padding.top + ( imageBottom - padding.top ) / 2;

			root.style.setProperty( '--ltt-photoswipe-image-center', imageCenter + 'px' );
			positionMobileControls( pswp, padding.top );
			caption.style.left = padding.left + 'px';
			caption.style.right = padding.right + 'px';
			caption.style.top = padding.top + 'px';
			caption.style.bottom = padding.bottom + 'px';
			location.style.left = padding.left + 'px';
			location.style.right = padding.right + 'px';
			location.style.top = imageBottom + 'px';
		};

		const createLightbox = function () {
			if ( ! instancePromise ) {
				instancePromise = import( lightboxModule )
					.then( function ( module ) {
						const PhotoSwipeLightbox = module.default;
						const lightbox = new PhotoSwipeLightbox(
							{
								dataSource: images,
								pswpModule: function () { return import( coreModule ); },
								mainClass: 'ltt-photoswipe' + ( inspired ? ' ltt-photoswipe--inspired' : '' ),
								arrowPrevSVG: getIconMarkup( cluster.dataset.photoswipeArrowIcon || '', 'ltt-photoswipe__arrow-icon' ),
								arrowNextSVG: getIconMarkup( cluster.dataset.photoswipeArrowIcon || '', 'ltt-photoswipe__arrow-icon' ),
								closeSVG: getIconMarkup( cluster.dataset.photoswipeCloseIcon || '', 'ltt-photoswipe__close-icon' ),
								zoom: false,
								paddingFn: inspired ? getInspiredPadding : undefined,
								initialZoomLevel: inspired ? getInspiredInitialZoom : undefined,
								pinchToClose: ! inspired,
								loop: true,
								showHideAnimationType: reduceMotion ? 'none' : 'zoom',
								returnFocus: false,
								closeTitle: 'Close image viewer',
								arrowPrevTitle: 'Previous image',
								arrowNextTitle: 'Next image',
								indexIndicatorSep: ' / ',
							}
						);

						lightbox.on( 'pointerDown', function ( event ) {
							const point = getTouchPoint( event.originalEvent );
							const pswp = lightbox.pswp;
							const slide = pswp ? pswp.currSlide : null;

							if ( ! point || ! slide || swipeStart || Math.abs( slide.currZoomLevel - slide.zoomLevels.initial ) > 0.01 ) {
								swipeStart = undefined;
								return;
							}

							swipeStart = {
								x: point.x,
								y: point.y,
								id: point.id,
								index: pswp.currIndex,
							};
						} );

						lightbox.on( 'pointerUp', function ( event ) {
							const point = getTouchPoint( event.originalEvent );
							const start = swipeStart;

							swipeStart = undefined;
							if ( ! point || ! start || point.id !== start.id ) {
								return;
							}

							const horizontalDistance = point.x - start.x;
							const verticalDistance = point.y - start.y;

							if ( Math.abs( horizontalDistance ) < 48 || Math.abs( horizontalDistance ) <= Math.abs( verticalDistance ) * 1.25 ) {
								return;
							}

							window.requestAnimationFrame( function () {
								const pswp = lightbox.pswp;

								if ( ! pswp || pswp.currIndex !== start.index || pswp.potentialIndex !== start.index ) {
									return;
								}

								pswp.goTo( start.index + ( horizontalDistance < 0 ? 1 : -1 ) );
							} );
						} );

						lightbox.addFilter( 'thumbEl', function ( thumb, item, index ) {
							return getThumb( index ) || thumb;
						} );

						lightbox.addFilter( 'placeholderSrc', function ( placeholder, slide ) {
							const thumb = getThumb( slide.index );

							return thumb ? thumb.currentSrc || thumb.src : placeholder;
						} );

						if ( inspired ) {
							let frameMask;

							lightbox.on( 'zoomLevelsUpdate', function ( event ) {
								const zoomLevels = event.zoomLevels;

								zoomLevels.min = zoomLevels.initial;
								zoomLevels.secondary = Math.max( zoomLevels.secondary, zoomLevels.initial );
							} );

							const setFrameMask = function ( pswp, thumbnail, animate ) {
								if ( ! frameMask || ! pswp.element ) {
									return;
								}

								const rootRect = pswp.element.getBoundingClientRect();
								const padding = getInspiredPadding( { x: rootRect.width, y: rootRect.height } );
								const thumb = thumbnail ? getThumb( pswp.currIndex ) : null;
								const thumbRect = thumb ? thumb.getBoundingClientRect() : null;
								const insets = thumbRect && thumbRect.width && thumbRect.height
									? [
										thumbRect.top - rootRect.top,
										rootRect.right - thumbRect.right,
										rootRect.bottom - thumbRect.bottom,
										thumbRect.left - rootRect.left,
									]
									: [ padding.top, padding.right, padding.bottom, padding.left ];
								const duration = pswp.element.style.getPropertyValue( '--pswp-transition-duration' ).trim() || '0ms';

								frameMask.style.transition = animate ? 'clip-path ' + duration + ' ' + pswp.options.easing : 'none';
								frameMask.style.setProperty( '--ltt-photoswipe-frame-clip', 'inset(' + insets.map( function ( inset ) { return inset + 'px'; } ).join( ' ' ) + ')' );
							};

							lightbox.on( 'initialLayout', function () {
								const pswp = lightbox.pswp;

								// Keep the crop in viewport coordinates while PhotoSwipe transforms its image container.
								frameMask = document.createElement( 'div' );
								frameMask.className = 'ltt-photoswipe__frame-mask';
								pswp.container.before( frameMask );
								frameMask.append( pswp.container );
								setFrameMask( pswp, true, false );
							} );

							lightbox.on( 'initialZoomIn', function () {
								setFrameMask( lightbox.pswp, false, true );
							} );

							lightbox.on( 'initialZoomInEnd', function () {
								const pswp = lightbox.pswp;

								setFrameMask( pswp, false, false );
							} );

							lightbox.on( 'initialZoomOut', function () {
								setFrameMask( lightbox.pswp, true, true );
							} );

							lightbox.on( 'close', function () {
								lightbox.pswp.element.classList.remove( 'ltt-photoswipe--content-ready' );
							} );

							lightbox.on( 'resize', function () {
								setFrameMask( lightbox.pswp, false, false );
							} );

							lightbox.on( 'uiRegister', function () {
								let caption;
								let location;
								let layoutFrame;

								const updateInspiredContent = function ( pswp ) {
									if ( ! caption || ! location ) {
										return;
									}

									renderCaption( caption, location, pswp );
									window.cancelAnimationFrame( layoutFrame );
									layoutFrame = window.requestAnimationFrame( function () {
										positionInspiredContent( caption, location, pswp );
									} );
								};

								lightbox.pswp.ui.registerElement(
									{
										name: 'ltt-caption',
										className: 'ltt-photoswipe__caption',
										appendTo: 'root',
										onInit: function ( element, pswp ) {
											caption = element;
											updateInspiredContent( pswp );
										},
									}
								);
								lightbox.pswp.ui.registerElement(
									{
										name: 'ltt-location',
										className: 'ltt-photoswipe__location',
										appendTo: 'root',
										onInit: function ( element, pswp ) {
											location = element;
											updateInspiredContent( pswp );
											pswp.on( 'change', function () {
												updateInspiredContent( pswp );
											} );
											pswp.on( 'resize', function () {
												updateInspiredContent( pswp );
											} );
											pswp.on( 'initialZoomInEnd', function () {
												updateInspiredContent( pswp );
											} );
										},
									}
								);
							} );
						}

						lightbox.on( 'afterInit', function () {
							const close = lightbox.pswp.element.querySelector( '.pswp__button--close' );
							const pswp = lightbox.pswp;
							let controlsFrame;
							let revealReady = false;
							let openingFinished = false;

							const updateControls = function ( imageTop, revealControls ) {
								revealReady = revealReady || Boolean( revealControls );
								window.cancelAnimationFrame( controlsFrame );
								controlsFrame = window.requestAnimationFrame( function () {
									const controlsPositioned = positionMobileControls( pswp, imageTop );

									if ( revealReady ) {
										if ( inspired ) {
											pswp.element.classList.add( 'ltt-photoswipe--content-ready' );
										}

										if ( controlsPositioned || ! window.matchMedia( '(max-width: 767.98px)' ).matches ) {
											pswp.element.classList.add( 'ltt-photoswipe--controls-ready' );
										}
									}
								} );
							};

							updateControls();
							pswp.on( 'change', function () { updateControls(); } );
							pswp.on( 'change', function () { syncCarouselToImage( pswp.currIndex, openingFinished ); } );
							pswp.on( 'resize', function () { updateControls(); } );
							pswp.on( 'initialZoomInEnd', function () {
								openingFinished = true;
								updateControls( undefined, true );
								syncCarouselToImage( pswp.currIndex, true );
							} );
							pswp.on( 'close', function () {
								revealReady = false;
								window.cancelAnimationFrame( controlsFrame );
							} );

							if ( close ) {
								close.classList.add( 'is-initial-focus' );
								close.addEventListener( 'blur', function () {
									close.classList.remove( 'is-initial-focus' );
								}, { once: true } );
								close.focus();
							}
						} );

						lightbox.on( 'close', function () {
							lightbox.pswp.element.classList.add( 'ltt-photoswipe--closing' );
							const returnTarget = syncCarouselToImage( lightbox.pswp.currIndex, true ) || trigger;
							const viewport = cluster.querySelector( '.static-image-cluster__carousel-viewport' );
							const swiper = viewport && viewport.swiper ? viewport.swiper : null;
							const a11yParams = swiper && swiper.params ? swiper.params.a11y : null;
							const scrollOnFocus = a11yParams ? a11yParams.scrollOnFocus : undefined;

							swipeStart = undefined;

							window.setTimeout( function () {
								if ( ! returnTarget || ! document.contains( returnTarget ) ) {
									return;
								}

								if ( a11yParams ) {
									a11yParams.scrollOnFocus = false;
								}

								try {
									returnTarget.focus( { preventScroll: true } );
								} finally {
									if ( a11yParams ) {
										a11yParams.scrollOnFocus = scrollOnFocus;
									}
								}
							}, reduceMotion ? 0 : 350 );
						} );

						lightbox.init();

						return lightbox;
					} );
			}

			return instancePromise;
		};

		cluster.addEventListener( 'click', function ( event ) {
			const target = event.target instanceof Element ? event.target.closest( '[data-ltt-photoswipe-trigger]' ) : null;

			if ( ! target || ! cluster.contains( target ) || 0 !== event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
				return;
			}

			event.preventDefault();
			trigger = target;

			createLightbox()
				.then( function ( lightbox ) {
					const index = Number.parseInt( target.dataset.imageIndex, 10 ) || 0;

					lightbox.loadAndOpen( index, images, { x: event.clientX, y: event.clientY } );
				} )
				.catch( function () {
					window.location.assign( target.href );
				} );
		} );

		cluster.addEventListener( 'ltt-photoswipe:items-added', function ( event ) {
			const additions = event.detail && Array.isArray( event.detail.items ) ? event.detail.items : [];

			if ( ! additions.length ) {
				return;
			}

			images = images.concat( normalizeImages( additions ) );
			source.textContent = JSON.stringify( images );
		} );
	} );
} )();
