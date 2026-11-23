/**
 * Block Guide live previews: device switch and scaled, auto-height frames.
 */
( function () {
	'use strict';

	const WIDTHS = { desktop: 1440, mobile: 375 };
	const BASE_HEIGHTS = { desktop: 900, mobile: 812 };
	const MAX_HEIGHT = 4000;

	const measure = function ( frame, device ) {
		let doc;

		try {
			doc = frame.contentDocument;
		} catch ( error ) {
			return 0;
		}

		if ( ! doc || ! doc.body ) {
			return 0;
		}

		// Measure against a fixed viewport so content sized in vh cannot grow
		// the frame on every pass.
		frame.style.height = BASE_HEIGHTS[ device ] + 'px';

		const main = doc.querySelector( 'main' ) || doc.body;
		const bottom = main.getBoundingClientRect().bottom + ( doc.defaultView ? doc.defaultView.scrollY : 0 );

		// The frame grows to fit, so its own scrollbar is never needed.
		doc.documentElement.style.overflow = 'hidden';

		return Math.min( MAX_HEIGHT, Math.ceil( bottom ) );
	};

	const setup = function ( preview ) {
		const frame = preview.querySelector( '[data-preview-frame]' );
		const stage = preview.querySelector( '[data-preview-stage]' );
		const devices = preview.querySelector( '[data-preview-devices]' );

		if ( ! frame || ! stage ) {
			return;
		}

		let device = 'desktop';
		let height = 0;

		const layout = function () {
			const width = WIDTHS[ device ];
			const available = stage.clientWidth;
			const scale = Math.min( 1, available / width );
			const measured = measure( frame, device );

			height = measured || height || BASE_HEIGHTS[ device ];

			frame.style.width = width + 'px';
			frame.style.height = height + 'px';
			frame.style.transform = 'scale(' + scale + ')';
			frame.style.left = Math.max( 0, ( available - width * scale ) / 2 ) + 'px';
			stage.style.height = Math.ceil( height * scale ) + 'px';
		};

		const relayoutSoon = function () {
			layout();
			window.setTimeout( layout, 400 );
			window.setTimeout( layout, 1500 );
		};

		preview.classList.add( 'is-enhanced' );

		if ( devices ) {
			devices.hidden = false;
			devices.querySelectorAll( '[data-preview-device]' ).forEach( function ( button ) {
				button.addEventListener( 'click', function () {
					device = button.getAttribute( 'data-preview-device' );
					devices.querySelectorAll( '[data-preview-device]' ).forEach( function ( other ) {
						other.setAttribute( 'aria-pressed', other === button ? 'true' : 'false' );
					} );
					relayoutSoon();
				} );
			} );
		}

		frame.addEventListener( 'load', relayoutSoon );
		window.addEventListener( 'resize', layout );
		layout();
	};

	document.querySelectorAll( '[data-block-guide-preview]' ).forEach( setup );
}() );
