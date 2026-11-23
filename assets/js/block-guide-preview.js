/**
 * Block Guide preview: keep visitors on the preview.
 *
 * Links and form submissions are blocked so sample buttons do not navigate and
 * sample forms never send entries. Components that handle their own clicks
 * (accordions, carousels, lightboxes) still work because the event keeps
 * propagating; only the browser's default navigation is cancelled.
 */
( function () {
	'use strict';

	const settings = window.ltt_dive_in_block_guide_preview || {};
	let notice = null;
	let timer = 0;

	const announce = function ( message ) {
		if ( ! notice ) {
			notice = document.createElement( 'div' );
			notice.setAttribute( 'role', 'status' );
			notice.className = 'ltt-block-guide-preview-notice';
			Object.assign( notice.style, {
				position: 'fixed',
				left: '50%',
				bottom: '24px',
				zIndex: '100000',
				maxWidth: 'calc(100% - 32px)',
				padding: '10px 16px',
				borderRadius: '4px',
				background: '#1d2327',
				color: '#fff',
				font: '14px/1.4 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
				transform: 'translateX(-50%)',
				boxShadow: '0 2px 8px rgba(0, 0, 0, 0.3)',
				opacity: '0',
				transition: 'opacity 0.2s',
				pointerEvents: 'none',
			} );
			document.body.appendChild( notice );
		}

		notice.textContent = message;
		notice.style.opacity = '1';
		window.clearTimeout( timer );
		timer = window.setTimeout( function () {
			notice.style.opacity = '0';
		}, 2500 );
	};

	document.addEventListener(
		'click',
		function ( event ) {
			const link = event.target.closest ? event.target.closest( 'a[href]' ) : null;

			if ( ! link ) {
				return;
			}

			const href = link.getAttribute( 'href' ) || '';

			// In-page anchors (skip links, jump links) are harmless, and
			// lightbox triggers open the image in place.
			if ( '#' === href.charAt( 0 ) || link.hasAttribute( 'data-ltt-photoswipe-trigger' ) ) {
				return;
			}

			event.preventDefault();
			announce( settings.linkMessage || 'Links are turned off in this preview.' );
		},
		true
	);

	document.addEventListener(
		'submit',
		function ( event ) {
			event.preventDefault();
			event.stopImmediatePropagation();
			announce( settings.formMessage || 'Forms are turned off in this preview. Nothing was sent.' );
		},
		true
	);
}() );
