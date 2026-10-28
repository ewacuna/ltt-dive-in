/**
 * Swap section-menu pages in place without a full reload.
 *
 * Every tab remains a complete server-rendered page: direct visits, reloads, new tabs, and failures use normal navigation.
 */
( function () {
	'use strict';

	const nav = document.querySelector( '#section-navigation[data-seamless-navigation]' );
	const status = document.querySelector( '[data-seamless-status]' );
	if ( ! nav || ! status || ! window.fetch || ! window.DOMParser || ! window.AbortController ) {
		return;
	}

	// Parts of the page that differ between tabs; the header, hero media, and tab bar stay in place.
	const regions = [ '.page-hero__content', '#page-content' ];
	// Head tags that describe the page itself rather than the site.
	const headTags = 'link[rel="canonical"], meta[name="description"], meta[name="robots"], meta[property^="og:"], meta[name^="twitter:"]';
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	let controller = null;
	let currentPath = window.location.pathname;

	const updateCurrentLink = function () {
		nav.querySelectorAll( 'a' ).forEach( function ( link ) {
			const isCurrent = new URL( link.href ).pathname === window.location.pathname;
			const item = link.closest( 'li' );

			if ( isCurrent ) {
				link.setAttribute( 'aria-current', 'page' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
			if ( item ) {
				item.classList.toggle( 'current-menu-item', isCurrent );
				item.classList.toggle( 'current_page_item', isCurrent );
			}
		} );
	};

	const load = async function ( url, push ) {
		if ( controller ) {
			controller.abort();
		}
		controller = new AbortController();

		const content = document.getElementById( 'page-content' );
		if ( content ) {
			content.setAttribute( 'aria-busy', 'true' );
		}

		try {
			const response = await fetch( url, {
				credentials: 'same-origin',
				headers: { Accept: 'text/html' },
				signal: controller.signal,
			} );
			if ( ! response.ok || response.redirected ) {
				throw new Error( 'Full navigation required.' );
			}

			const next = new DOMParser().parseFromString( await response.text(), 'text/html' );
			const nextNav = next.querySelector( '#section-navigation[data-seamless-navigation]' );
			// Only a page that shares this tab bar can be swapped in place.
			if ( ! nextNav || nextNav.dataset.menu !== nav.dataset.menu ) {
				throw new Error( 'Full navigation required.' );
			}

			const pairs = regions.map( function ( selector ) {
				return [ document.querySelector( selector ), next.querySelector( selector ) ];
			} );
			if ( pairs.some( function ( pair ) {
				return ! pair[ 0 ] || ! pair[ 1 ];
			} ) ) {
				throw new Error( 'Full navigation required.' );
			}

			pairs.forEach( function ( pair ) {
				pair[ 0 ].replaceWith( document.importNode( pair[ 1 ], true ) );
			} );
			document.title = next.title;
			document.head.querySelectorAll( headTags ).forEach( function ( tag ) {
				tag.remove();
			} );
			next.head.querySelectorAll( headTags ).forEach( function ( tag ) {
				document.head.appendChild( document.importNode( tag, true ) );
			} );

			if ( push ) {
				window.history.pushState( { lttSeamless: true }, '', url );
			}
			currentPath = window.location.pathname;
			updateCurrentLink();

			// Keep the tab bar in view, then move focus to the new page's H1 and announce it.
			if ( nav.getBoundingClientRect().top < 0 ) {
				nav.scrollIntoView( { behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'start' } );
			}
			const heading = document.querySelector( 'main h1' );
			if ( heading ) {
				heading.setAttribute( 'tabindex', '-1' );
				heading.focus( { preventScroll: true } );
			}
			status.textContent = status.dataset.message.replace( '%s', heading ? heading.textContent.trim() : document.title );

			// Lets other components initialize markup inside the swapped regions.
			document.dispatchEvent( new CustomEvent( 'ltt:content-replaced', { detail: { url: url } } ) );
		} catch ( error ) {
			if ( 'AbortError' !== error.name ) {
				window.location.assign( url );
			}
		}
	};

	nav.addEventListener( 'click', function ( event ) {
		const link = event.target.closest( 'a[href]' );
		// Leave modified clicks, new-window links, and drags that Swiper cancelled to the browser.
		if ( ! link || event.defaultPrevented || 0 !== event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || ( link.target && '_self' !== link.target ) || link.hasAttribute( 'download' ) ) {
			return;
		}

		const url = new URL( link.href );
		if ( url.origin !== window.location.origin ) {
			return;
		}

		event.preventDefault();
		if ( url.pathname !== window.location.pathname ) {
			load( url.href, true );
		}
	} );

	window.addEventListener( 'popstate', function () {
		// Fragment-only history entries stay on the current page.
		if ( window.location.pathname !== currentPath ) {
			load( window.location.href, false );
		}
	} );
}() );
