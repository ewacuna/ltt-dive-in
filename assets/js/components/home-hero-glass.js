/**
 * Homepage hero glass title.
 *
 * Draws the rendered hero title into canvas masks so two decorative layers can
 * follow the real glyphs: a refraction layer that bends the background video
 * through the letters, and a light layer for the bevel highlight.
 *
 * The refraction copies the current video frame into a canvas cropped to the
 * title and runs it through an SVG displacement filter with CSS `filter`, which
 * every current engine supports (SVG filters in `backdrop-filter` are
 * Chromium-only). The <h1> remains the only text; every generated layer is
 * aria-hidden.
 */
( function () {
	'use strict';

	const heading = document.querySelector( '[data-home-hero-glass]' );

	if ( ! heading || typeof window.CanvasRenderingContext2D === 'undefined' ) {
		return;
	}

	const hero = heading.closest( '.home-hero' );
	const title = heading.querySelector( '.home-hero__title' );
	const refraction = heading.querySelector( '.home-hero__glass--refraction' );
	const light = heading.querySelector( '.home-hero__glass--light' );
	const video = hero ? hero.querySelector( '.home-hero__video' ) : null;

	if ( ! hero || ! title || ! refraction || ! light ) {
		return;
	}

	// Glass values approved for the title at its 9.9rem desktop size; they
	// scale with the rendered font size.
	const GLASS = {
		referenceFontSize: 158.4,
		depth: 24,
		refraction: 0.53,
		dispersion: 0.8,
	};

	const SVG_NS = 'http://www.w3.org/2000/svg';
	const filterId = 'ltt-home-hero-glass-filter';
	let filterParts = null;
	let frameCanvas = null;
	let frameContext = null;
	let layoutFrame = 0;
	let lastSignature = '';
	let geometry = null;
	let heroVisible = true;
	let drawLoop = 0;

	/**
	 * Creates the hidden SVG filter used by the refraction layer.
	 *
	 * @return {Object} Filter elements that change with the title size.
	 */
	function createFilter() {
		const svg = document.createElementNS( SVG_NS, 'svg' );
		svg.setAttribute( 'aria-hidden', 'true' );
		svg.setAttribute( 'focusable', 'false' );
		svg.setAttribute( 'width', '0' );
		svg.setAttribute( 'height', '0' );
		svg.style.position = 'absolute';
		svg.style.overflow = 'hidden';

		const filter = document.createElementNS( SVG_NS, 'filter' );
		filter.setAttribute( 'id', filterId );
		filter.setAttribute( 'filterUnits', 'userSpaceOnUse' );
		filter.setAttribute( 'primitiveUnits', 'userSpaceOnUse' );
		filter.setAttribute( 'color-interpolation-filters', 'sRGB' );
		filter.setAttribute( 'x', '0' );
		filter.setAttribute( 'y', '0' );

		const map = document.createElementNS( SVG_NS, 'feImage' );
		map.setAttribute( 'x', '0' );
		map.setAttribute( 'y', '0' );
		map.setAttribute( 'preserveAspectRatio', 'none' );
		map.setAttribute( 'result', 'map' );
		filter.appendChild( map );

		// Displace each color channel by a slightly different amount so the
		// letter edges split light like the dispersion in the source glass.
		const channels = [
			{ name: 'r', matrix: '1 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 0' },
			{ name: 'g', matrix: '0 0 0 0 0  0 1 0 0 0  0 0 0 0 0  0 0 0 1 0' },
			{ name: 'b', matrix: '0 0 0 0 0  0 0 0 0 0  0 0 1 0 0  0 0 0 1 0' },
		];
		const displacements = {};

		channels.forEach( function ( channel ) {
			const displacement = document.createElementNS( SVG_NS, 'feDisplacementMap' );
			displacement.setAttribute( 'in', 'SourceGraphic' );
			displacement.setAttribute( 'in2', 'map' );
			displacement.setAttribute( 'xChannelSelector', 'R' );
			displacement.setAttribute( 'yChannelSelector', 'G' );
			displacement.setAttribute( 'result', 'displaced-' + channel.name );
			filter.appendChild( displacement );
			displacements[ channel.name ] = displacement;

			const matrix = document.createElementNS( SVG_NS, 'feColorMatrix' );
			matrix.setAttribute( 'in', 'displaced-' + channel.name );
			matrix.setAttribute( 'type', 'matrix' );
			matrix.setAttribute( 'values', channel.matrix );
			matrix.setAttribute( 'result', 'channel-' + channel.name );
			filter.appendChild( matrix );
		} );

		const blendRg = document.createElementNS( SVG_NS, 'feBlend' );
		blendRg.setAttribute( 'in', 'channel-r' );
		blendRg.setAttribute( 'in2', 'channel-g' );
		blendRg.setAttribute( 'mode', 'screen' );
		blendRg.setAttribute( 'result', 'channel-rg' );
		filter.appendChild( blendRg );

		const blendRgb = document.createElementNS( SVG_NS, 'feBlend' );
		blendRgb.setAttribute( 'in', 'channel-rg' );
		blendRgb.setAttribute( 'in2', 'channel-b' );
		blendRgb.setAttribute( 'mode', 'screen' );
		filter.appendChild( blendRgb );

		svg.appendChild( filter );
		document.body.appendChild( svg );

		return {
			filter: filter,
			map: map,
			displacements: displacements,
		};
	}

	/**
	 * Creates the video copy and the hero tint inside the refraction layer.
	 */
	function createRefractionContent() {
		frameCanvas = document.createElement( 'canvas' );
		frameCanvas.className = 'home-hero__glass-frame';
		frameCanvas.style.filter = 'url(#' + filterId + ')';
		frameContext = frameCanvas.getContext( '2d' );

		// The video copy sits above the real overlay, so it needs the same tint.
		const tint = document.createElement( 'span' );
		tint.className = 'home-hero__overlay home-hero__glass-tint';

		refraction.appendChild( frameCanvas );
		refraction.appendChild( tint );
	}

	/**
	 * Returns each visible title character with its box relative to the heading.
	 *
	 * Reading the browser's own layout keeps kerning, wrapping, and alignment
	 * identical to the real <h1>.
	 *
	 * @param {DOMRect} origin Heading box.
	 * @return {Array} Character boxes.
	 */
	function getGlyphBoxes( origin ) {
		const boxes = [];
		const walker = document.createTreeWalker( title, NodeFilter.SHOW_TEXT );
		const range = document.createRange();
		let node = walker.nextNode();

		while ( node ) {
			const text = node.nodeValue;

			for ( let index = 0; index < text.length; index++ ) {
				const character = text[ index ];

				if ( /\s/.test( character ) ) {
					continue;
				}

				range.setStart( node, index );
				range.setEnd( node, index + 1 );

				const rect = range.getBoundingClientRect();

				if ( rect.width && rect.height ) {
					boxes.push( {
						character: character,
						left: rect.left - origin.left,
						top: rect.top - origin.top,
					} );
				}
			}

			node = walker.nextNode();
		}

		return boxes;
	}

	/**
	 * Draws the title glyphs onto a canvas context.
	 *
	 * @param {CanvasRenderingContext2D} context   Target context.
	 * @param {Array}                    boxes     Character boxes.
	 * @param {string}                   font      Canvas font shorthand.
	 * @param {string}                   transform CSS text-transform value.
	 * @param {number}                   pad       Layer padding around the title.
	 */
	function drawGlyphs( context, boxes, font, transform, pad ) {
		context.font = font;
		context.textBaseline = 'alphabetic';
		context.textAlign = 'left';

		boxes.forEach( function ( box ) {
			const glyph = 'uppercase' === transform ? box.character.toLocaleUpperCase() : box.character;
			const metrics = context.measureText( glyph );
			const ascent = metrics.fontBoundingBoxAscent || metrics.actualBoundingBoxAscent;

			context.fillText( glyph, box.left + pad, box.top + pad + ascent );
		} );
	}

	/**
	 * Applies a mask image with the standard and prefixed properties.
	 *
	 * @param {HTMLElement} element Layer element.
	 * @param {string}      url     Data URL.
	 */
	function setMask( element, url ) {
		const value = 'url("' + url + '")';

		element.style.webkitMaskImage = value;
		element.style.maskImage = value;
	}

	/**
	 * Converts one object-position component to a 0–1 ratio.
	 *
	 * @param {string} value Computed component, normally a percentage.
	 * @return {number} Ratio.
	 */
	function positionRatio( value ) {
		const keywords = { left: 0, top: 0, center: 0.5, right: 1, bottom: 1 };

		if ( value in keywords ) {
			return keywords[ value ];
		}

		return value && value.endsWith( '%' ) ? parseFloat( value ) / 100 : 0.5;
	}

	/**
	 * Builds the masks and displacement map, and records the video geometry.
	 */
	function layout() {
		layoutFrame = 0;

		const origin = heading.getBoundingClientRect();
		const width = Math.ceil( origin.width );
		const height = Math.ceil( origin.height );

		if ( ! width || ! height ) {
			return;
		}

		const style = window.getComputedStyle( title );
		const font = [ style.fontStyle, style.fontWeight, style.fontSize, style.fontFamily ].join( ' ' );
		const scale = parseFloat( style.fontSize ) / GLASS.referenceFontSize;
		const depth = Math.max( 4, GLASS.depth * scale );
		const split = GLASS.dispersion * 0.1;

		// feDisplacementMap moves pixels by up to scale / 2; the padding lets
		// edge pixels sample video from outside the title box.
		const displacement = 2 * depth * GLASS.refraction;
		const pad = Math.ceil( ( displacement * ( 1 + split ) ) / 2 ) + 2;
		const layerWidth = width + pad * 2;
		const layerHeight = height + pad * 2;
		const pixelRatio = Math.min( window.devicePixelRatio || 1, 2 );
		const heroRect = hero.getBoundingClientRect();
		const layerLeft = origin.left - pad;
		const layerTop = origin.top - pad;

		updateVideoGeometry( layerLeft, layerTop, layerWidth, layerHeight, pixelRatio );

		const signature = [ width, height, font, title.textContent, pixelRatio ].join( '|' );

		if ( signature === lastSignature ) {
			positionTint( heroRect, layerLeft, layerTop );
			drawFrame();
			return;
		}

		const boxes = getGlyphBoxes( origin );

		if ( ! boxes.length ) {
			return;
		}

		lastSignature = signature;

		// Crisp glyph mask at device resolution.
		const maskCanvas = document.createElement( 'canvas' );
		maskCanvas.width = Math.ceil( layerWidth * pixelRatio );
		maskCanvas.height = Math.ceil( layerHeight * pixelRatio );
		const maskContext = maskCanvas.getContext( '2d' );

		if ( ! maskContext ) {
			return;
		}

		maskContext.scale( pixelRatio, pixelRatio );
		maskContext.fillStyle = '#ffffff';
		drawGlyphs( maskContext, boxes, font, style.textTransform, pad );

		// The bevel is derived at CSS-pixel resolution: a blurred glyph field
		// whose gradient points from each edge toward the letter center.
		const fieldCanvas = document.createElement( 'canvas' );
		fieldCanvas.width = layerWidth;
		fieldCanvas.height = layerHeight;
		const fieldContext = fieldCanvas.getContext( '2d', { willReadFrequently: true } );

		if ( ! fieldContext ) {
			return;
		}

		fieldContext.drawImage( maskCanvas, 0, 0, layerWidth, layerHeight );
		const solid = fieldContext.getImageData( 0, 0, layerWidth, layerHeight ).data;
		const blurred = blurAlpha( solid, layerWidth, layerHeight, Math.max( 1, Math.round( depth / 2 ) ) );

		const mapImage = fieldContext.createImageData( layerWidth, layerHeight );
		const lightImage = fieldContext.createImageData( layerWidth, layerHeight );
		const map = mapImage.data;
		const lightMask = lightImage.data;
		const total = layerWidth * layerHeight;
		const gradientX = new Float32Array( total );
		const gradientY = new Float32Array( total );
		let maxGradient = 0;

		for ( let y = 1; y < layerHeight - 1; y++ ) {
			for ( let x = 1; x < layerWidth - 1; x++ ) {
				const index = y * layerWidth + x;

				if ( solid[ index * 4 + 3 ] < 8 ) {
					continue;
				}

				const gx = blurred[ index + 1 ] - blurred[ index - 1 ];
				const gy = blurred[ index + layerWidth ] - blurred[ index - layerWidth ];

				gradientX[ index ] = gx;
				gradientY[ index ] = gy;
				maxGradient = Math.max( maxGradient, Math.abs( gx ), Math.abs( gy ) );
			}
		}

		maxGradient = maxGradient || 1;

		for ( let index = 0; index < total; index++ ) {
			const offset = index * 4;
			const nx = gradientX[ index ] / maxGradient;
			const ny = gradientY[ index ] / maxGradient;
			const strength = Math.min( 1, Math.sqrt( nx * nx + ny * ny ) );

			map[ offset ] = Math.round( 128 + nx * 127 );
			map[ offset + 1 ] = Math.round( 128 + ny * 127 );
			map[ offset + 2 ] = 128;
			map[ offset + 3 ] = 255;

			lightMask[ offset ] = 255;
			lightMask[ offset + 1 ] = 255;
			lightMask[ offset + 2 ] = 255;
			lightMask[ offset + 3 ] = Math.round( Math.pow( strength, 0.8 ) * solid[ offset + 3 ] );
		}

		fieldContext.putImageData( mapImage, 0, 0 );
		const mapUrl = fieldCanvas.toDataURL( 'image/png' );

		fieldContext.putImageData( lightImage, 0, 0 );
		const lightUrl = fieldCanvas.toDataURL( 'image/png' );
		const maskUrl = maskCanvas.toDataURL( 'image/png' );

		if ( ! filterParts ) {
			filterParts = createFilter();
		}

		filterParts.filter.setAttribute( 'width', String( layerWidth ) );
		filterParts.filter.setAttribute( 'height', String( layerHeight ) );
		filterParts.map.setAttribute( 'width', String( layerWidth ) );
		filterParts.map.setAttribute( 'height', String( layerHeight ) );
		filterParts.map.setAttribute( 'href', mapUrl );
		filterParts.displacements.r.setAttribute( 'scale', ( displacement * ( 1 + split ) ).toFixed( 2 ) );
		filterParts.displacements.g.setAttribute( 'scale', displacement.toFixed( 2 ) );
		filterParts.displacements.b.setAttribute( 'scale', ( displacement * ( 1 - split ) ).toFixed( 2 ) );

		heading.style.setProperty( '--ltt-home-hero-glass-pad', pad + 'px' );
		setMask( refraction, maskUrl );
		setMask( light, lightUrl );
		positionTint( heroRect, layerLeft, layerTop );
		heading.classList.add( 'is-glass-ready' );
		drawFrame();
	}

	/**
	 * Blurs an alpha channel with two separable box-blur passes.
	 *
	 * Canvas `filter` is unavailable in some Safari versions, so the bevel
	 * field is computed directly.
	 *
	 * @param {Uint8ClampedArray} pixels RGBA source.
	 * @param {number}            width  Width.
	 * @param {number}            height Height.
	 * @param {number}            radius Blur radius in pixels.
	 * @return {Float32Array} Blurred alpha values.
	 */
	function blurAlpha( pixels, width, height, radius ) {
		let source = new Float32Array( width * height );
		let target = new Float32Array( width * height );

		for ( let index = 0; index < source.length; index++ ) {
			source[ index ] = pixels[ index * 4 + 3 ];
		}

		for ( let pass = 0; pass < 2; pass++ ) {
			const span = radius * 2 + 1;

			for ( let y = 0; y < height; y++ ) {
				const row = y * width;
				let sum = 0;

				for ( let x = -radius; x <= radius; x++ ) {
					sum += source[ row + Math.min( width - 1, Math.max( 0, x ) ) ];
				}

				for ( let x = 0; x < width; x++ ) {
					target[ row + x ] = sum / span;
					sum += source[ row + Math.min( width - 1, x + radius + 1 ) ];
					sum -= source[ row + Math.max( 0, x - radius ) ];
				}
			}

			for ( let x = 0; x < width; x++ ) {
				let sum = 0;

				for ( let y = -radius; y <= radius; y++ ) {
					sum += target[ Math.min( height - 1, Math.max( 0, y ) ) * width + x ];
				}

				for ( let y = 0; y < height; y++ ) {
					source[ y * width + x ] = sum / span;
					sum += target[ Math.min( height - 1, y + radius + 1 ) * width + x ];
					sum -= target[ Math.max( 0, y - radius ) * width + x ];
				}
			}
		}

		target = null;

		return source;
	}

	/**
	 * Records where the cover-fitted video sits relative to the refraction layer.
	 *
	 * @param {number} layerLeft   Layer left edge in the viewport.
	 * @param {number} layerTop    Layer top edge in the viewport.
	 * @param {number} layerWidth  Layer width.
	 * @param {number} layerHeight Layer height.
	 * @param {number} pixelRatio  Canvas resolution multiplier.
	 */
	function updateVideoGeometry( layerLeft, layerTop, layerWidth, layerHeight, pixelRatio ) {
		if ( ! video || ! frameContext ) {
			geometry = null;
			return;
		}

		const rect = video.getBoundingClientRect();
		const position = window.getComputedStyle( video ).objectPosition.split( ' ' );

		geometry = {
			width: layerWidth,
			height: layerHeight,
			pixelRatio: pixelRatio,
			videoLeft: rect.left - layerLeft,
			videoTop: rect.top - layerTop,
			videoWidth: rect.width,
			videoHeight: rect.height,
			positionX: positionRatio( position[ 0 ] ),
			positionY: positionRatio( position[ 1 ] || position[ 0 ] ),
		};

		const canvasWidth = Math.ceil( layerWidth * pixelRatio );
		const canvasHeight = Math.ceil( layerHeight * pixelRatio );

		if ( frameCanvas.width !== canvasWidth || frameCanvas.height !== canvasHeight ) {
			frameCanvas.width = canvasWidth;
			frameCanvas.height = canvasHeight;
		}
	}

	/**
	 * Aligns the tint copy with the real hero overlay.
	 *
	 * @param {DOMRect} heroRect  Hero box.
	 * @param {number}  layerLeft Layer left edge in the viewport.
	 * @param {number}  layerTop  Layer top edge in the viewport.
	 */
	function positionTint( heroRect, layerLeft, layerTop ) {
		const tint = refraction.querySelector( '.home-hero__glass-tint' );

		if ( ! tint ) {
			return;
		}

		tint.style.left = heroRect.left - layerLeft + 'px';
		tint.style.top = heroRect.top - layerTop + 'px';
		tint.style.width = heroRect.width + 'px';
		tint.style.height = heroRect.height + 'px';
	}

	/**
	 * Copies the visible part of the current video frame behind the title.
	 */
	function drawFrame() {
		if ( ! geometry || ! heroVisible || video.readyState < 2 || ! video.videoWidth ) {
			return;
		}

		const fit = Math.max( geometry.videoWidth / video.videoWidth, geometry.videoHeight / video.videoHeight );
		const drawWidth = video.videoWidth * fit;
		const drawHeight = video.videoHeight * fit;
		const drawLeft = geometry.videoLeft + ( geometry.videoWidth - drawWidth ) * geometry.positionX;
		const drawTop = geometry.videoTop + ( geometry.videoHeight - drawHeight ) * geometry.positionY;

		frameContext.setTransform( geometry.pixelRatio, 0, 0, geometry.pixelRatio, 0, 0 );
		frameContext.clearRect( 0, 0, geometry.width, geometry.height );
		frameContext.drawImage( video, drawLeft, drawTop, drawWidth, drawHeight );

		if ( ! heading.classList.contains( 'is-glass-video-ready' ) ) {
			heading.classList.add( 'is-glass-video-ready' );
		}
	}

	/**
	 * Redraws on each new video frame while the hero is on screen.
	 */
	function startDrawing() {
		if ( ! video || drawLoop ) {
			return;
		}

		if ( 'requestVideoFrameCallback' in video ) {
			const onVideoFrame = function () {
				drawFrame();
				drawLoop = video.requestVideoFrameCallback( onVideoFrame );
			};

			drawLoop = video.requestVideoFrameCallback( onVideoFrame );
			return;
		}

		const onAnimationFrame = function () {
			if ( video.paused || ! heroVisible ) {
				drawLoop = 0;
				return;
			}

			drawFrame();
			drawLoop = window.requestAnimationFrame( onAnimationFrame );
		};

		drawLoop = window.requestAnimationFrame( onAnimationFrame );
	}

	function scheduleLayout() {
		if ( ! layoutFrame ) {
			layoutFrame = window.requestAnimationFrame( layout );
		}
	}

	if ( video ) {
		createRefractionContent();

		[ 'loadeddata', 'seeked', 'pause' ].forEach( function ( type ) {
			video.addEventListener( type, drawFrame );
		} );

		video.addEventListener( 'play', startDrawing );

		if ( 'IntersectionObserver' in window ) {
			new window.IntersectionObserver( function ( entries ) {
				heroVisible = entries[ entries.length - 1 ].isIntersecting;

				if ( heroVisible ) {
					drawFrame();

					if ( ! video.paused ) {
						startDrawing();
					}
				}
			} ).observe( hero );
		}

		if ( ! video.paused ) {
			startDrawing();
		}
	}

	const fontsReady = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();

	fontsReady.then( function () {
		scheduleLayout();

		if ( 'ResizeObserver' in window ) {
			const observer = new window.ResizeObserver( scheduleLayout );

			observer.observe( heading );
			observer.observe( hero );
		} else {
			window.addEventListener( 'resize', scheduleLayout );
		}
	} );
}() );
