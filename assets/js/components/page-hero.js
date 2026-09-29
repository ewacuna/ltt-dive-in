/** Keep the Figma mobile H1 size unless a whole word needs more room. */
( function () {
	'use strict';

	const title = document.querySelector( '.page-hero__title' );
	if ( ! title ) {
		return;
	}

	const context = document.createElement( 'canvas' ).getContext( '2d' );
	if ( ! context ) {
		return;
	}

	const mobileQuery = window.matchMedia( '(max-width: 575.98px)' );
	const words = title.textContent.trim().split( /\s+/u );
	const fitTitle = function () {
		title.style.removeProperty( 'font-size' );
		if ( ! mobileQuery.matches || ! words.length ) {
			return;
		}

		const style = window.getComputedStyle( title );
		const rootSize = Number.parseFloat( window.getComputedStyle( document.documentElement ).fontSize );
		const maxSize = rootSize * 4;
		const minSize = rootSize * 2.25;
		const availableWidth = title.parentElement.getBoundingClientRect().width;
		if ( ! availableWidth ) {
			return;
		}

		context.font = style.fontStyle + ' ' + style.fontWeight + ' ' + maxSize + 'px ' + style.fontFamily;
		const letterSpacing = Number.parseFloat( style.letterSpacing ) || 0;
		const widestWord = Math.max( ...words.map( function ( word ) {
			const visibleWord = 'uppercase' === style.textTransform ? word.toUpperCase() : word;
			return context.measureText( visibleWord ).width + Math.max( 0, visibleWord.length - 1 ) * letterSpacing;
		} ) );
		const fittedSize = widestWord > availableWidth ? maxSize * ( availableWidth - 2 ) / widestWord : maxSize;
		title.style.fontSize = Math.max( minSize, Math.min( maxSize, fittedSize ) ) + 'px';
	};

	fitTitle();
	window.addEventListener( 'resize', fitTitle );
	if ( document.fonts ) {
		document.fonts.ready.then( fitTitle );
	}
}() );
