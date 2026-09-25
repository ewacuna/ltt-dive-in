/**
 * Progressive PhotoSwipe enhancement for theme-owned image galleries.
 */
( function () {
	'use strict';

	const reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	const getIconMarkup = function ( url, className ) {
		return '<img class="' + className + '" src="' + url + '" alt="" aria-hidden="true">';
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

	const normalizeImages = function ( images ) {
		return images.map( function ( image ) {
			return {
				src: image.full,
				srcset: image.srcset || '',
				width: Number( image.width ) || 1,
				height: Number( image.height ) || 1,
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
		const lightboxModule = cluster.dataset.photoswipeLightboxModule;
		const coreModule = cluster.dataset.photoswipeCoreModule;
		const inspired = 'true' === cluster.dataset.photoswipeInspired;
		const locationIcon = cluster.dataset.photoswipeLocationIcon || '';
		let images = source ? parseImages( source ) : [];
		let instancePromise;
		let trigger;

		if ( ! source || ! lightboxModule || ! coreModule || ! images.length ) {
			return;
		}

		const getThumb = function ( index ) {
			return cluster.querySelector( '[data-ltt-photoswipe-trigger][data-image-index="' + index + '"] img' );
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

			root.style.setProperty( '--ltt-photoswipe-image-top', padding.top + 'px' );
			root.style.setProperty( '--ltt-photoswipe-image-right', padding.right + 'px' );
			root.style.setProperty( '--ltt-photoswipe-image-bottom', padding.bottom + 'px' );
			root.style.setProperty( '--ltt-photoswipe-image-left', padding.left + 'px' );
			root.style.setProperty( '--ltt-photoswipe-image-center', imageCenter + 'px' );
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
								loop: true,
								showHideAnimationType: reduceMotion ? 'none' : 'zoom',
								returnFocus: false,
								closeTitle: 'Close image viewer',
								arrowPrevTitle: 'Previous image',
								arrowNextTitle: 'Next image',
								indexIndicatorSep: ' / ',
							}
						);

						lightbox.addFilter( 'thumbEl', function ( thumb, item, index ) {
							return getThumb( index ) || thumb;
						} );

						lightbox.addFilter( 'placeholderSrc', function ( placeholder, slide ) {
							const thumb = getThumb( slide.index );

							return thumb ? thumb.currentSrc || thumb.src : placeholder;
						} );

						if ( inspired ) {
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

							if ( close ) {
								close.focus();
							}
						} );

						lightbox.on( 'close', function () {
							const returnTarget = trigger;

							window.setTimeout( function () {
								if ( returnTarget && document.contains( returnTarget ) ) {
									returnTarget.focus();
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
