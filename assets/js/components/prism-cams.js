/**
 * Prism Cam viewers for the live-cameras tab.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const READY_TIMEOUT = 15000;
	let loading = null;

	const loadStyle = function ( href ) {
		if ( ! href || document.getElementById( 'ltt-dive-in-prism-css' ) ) {
			return;
		}

		const link = document.createElement( 'link' );
		link.id = 'ltt-dive-in-prism-css';
		link.rel = 'stylesheet';
		link.href = href;
		document.head.appendChild( link );
	};

	const loadScript = function ( src ) {
		if ( ! loading ) {
			loading = new Promise( function ( resolve, reject ) {
				const script = document.createElement( 'script' );
				script.id = 'ltt-dive-in-prism-js';
				script.src = src;
				script.async = true;
				script.addEventListener( 'load', resolve, { once: true } );
				script.addEventListener( 'error', reject, { once: true } );
				document.body.appendChild( script );
			} );
		}

		return loading;
	};

	const watch = function ( viewer ) {
		const embed = viewer.querySelector( '[data-prism-embed]' );

		if ( ! embed ) {
			return;
		}

		const isRendered = function () {
			return !! embed.querySelector( '.luma-preview img, .luma-preview canvas, .luma-preview [style*="background-image"]' );
		};
		let timer = 0;
		const observer = new MutationObserver( function () {
			if ( isRendered() ) {
				observer.disconnect();
				window.clearTimeout( timer );
				viewer.classList.add( 'is-ready' );
			}
		} );

		observer.observe( embed, { childList: true, subtree: true, attributes: true } );
		timer = window.setTimeout( function () {
			observer.disconnect();
		}, READY_TIMEOUT );
	};

	const init = function () {
		const viewers = Array.from( document.querySelectorAll( '[data-prism-viewer]:not([data-prism-started])' ) );

		if ( ! viewers.length ) {
			return;
		}

		viewers.forEach( function ( viewer ) {
			viewer.dataset.prismStarted = 'true';
			watch( viewer );
		} );

		loadStyle( viewers[ 0 ].dataset.prismStyle );

		const alreadyLoaded = !! loading;

		loadScript( viewers[ 0 ].dataset.prismScript ).then( function () {
			const core = window.Iris && window.Iris.Core;

			// The provider renders once per page load; new viewers swapped in by seamless navigation need another pass.
			if ( alreadyLoaded && core && 'function' === typeof core.init ) {
				core.initTriggered = false;
				core.init();
			}
		} ).catch( function () {} );
	};

	init();
	document.addEventListener( 'ltt:content-replaced', init );
}() );
