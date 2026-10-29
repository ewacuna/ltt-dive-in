( function () {
	'use strict';

	const nav = document.querySelector( '#section-navigation[data-seamless-navigation]' );
	const status = document.querySelector( '[data-seamless-status]' );
	if ( ! nav || ! status || ! window.fetch || ! window.DOMParser || ! window.AbortController || ! document.querySelector( '[data-seamless-region]' ) ) {
		return;
	}

	const regions = Array.from( document.querySelectorAll( '[data-seamless-region]' ), function ( region ) {
		return '[data-seamless-region="' + region.dataset.seamlessRegion + '"]';
	} );
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

	const syncAdminBar = function ( next ) {
		const nextLinks = new Map();

		next.querySelectorAll( '#wpadminbar li[id] > a.ab-item[href]' ).forEach( function ( link ) {
			nextLinks.set( link.parentElement.id, link.getAttribute( 'href' ) );
		} );
		document.querySelectorAll( '#wpadminbar li[id] > a.ab-item[href]' ).forEach( function ( link ) {
			const href = nextLinks.get( link.parentElement.id );
			if ( href ) {
				link.setAttribute( 'href', href );
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
			syncAdminBar( next );

			if ( push ) {
				window.history.pushState( { lttSeamless: true }, '', url );
			}
			currentPath = window.location.pathname;
			updateCurrentLink();

			if ( nav.getBoundingClientRect().top < 0 ) {
				nav.scrollIntoView( { behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'start' } );
			}
			const heading = document.querySelector( 'main h1' );
			if ( heading ) {
				heading.setAttribute( 'tabindex', '-1' );
				heading.focus( { preventScroll: true } );
			}
			status.textContent = status.dataset.message.replace( '%s', heading ? heading.textContent.trim() : document.title );

			document.dispatchEvent( new CustomEvent( 'ltt:content-replaced', { detail: { url: url } } ) );
		} catch ( error ) {
			if ( 'AbortError' !== error.name ) {
				window.location.assign( url );
			}
		}
	};

	nav.addEventListener( 'click', function ( event ) {
		const link = event.target.closest( 'a[href]' );
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
		if ( window.location.pathname !== currentPath ) {
			load( window.location.href, false );
		}
	} );
}() );
