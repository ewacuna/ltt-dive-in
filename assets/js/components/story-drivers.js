/**
 * Story Drivers: Hero + Page Grid filtering and Load More.
 *
 * The filters are a GET form, so they work without JavaScript. This script
 * replaces full-page submissions with requests to the
 * ltt-dive-in/v1/story-driver-stories endpoint.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_story_drivers || {};

	if ( ! strings.endpoint || ! window.fetch || ! window.URLSearchParams ) {
		return;
	}

	const format = function ( template, values ) {
		let index = 0;

		return ( template || '' ).replace( /%(\d\$)?d/g, function ( match, position ) {
			const value = position ? values[ parseInt( position, 10 ) - 1 ] : values[ index ];

			index += 1;

			return String( value );
		} );
	};

	const initGrid = function ( section ) {
		const form = section.querySelector( '[data-story-grid-filters]' );
		const grid = section.querySelector( '[data-story-grid-results]' );
		const empty = section.querySelector( '[data-story-grid-empty]' );
		const status = section.querySelector( '[data-story-grid-status]' );
		const loadMore = section.querySelector( '[data-story-grid-load-more]' );
		const search = form ? form.querySelector( '[data-story-grid-filter="search"]' ) : null;
		const perPage = parseInt( section.dataset.perPage, 10 ) || 9;
		let offset = loadMore ? parseInt( loadMore.dataset.offset, 10 ) || 0 : 0;
		let controller = null;
		let searchTimer = 0;
		let lastSearch = search ? search.value.trim() : '';

		if ( ! form || ! grid ) {
			return;
		}

		const announce = function ( message ) {
			if ( status ) {
				status.textContent = message;
			}
		};

		const request = function ( requestOffset ) {
			const data = new FormData( form );
			const params = new URLSearchParams( {
				offset: String( requestOffset ),
				per_page: String( perPage ),
				exclude: section.dataset.exclude || '',
				category: ( data.get( 'story_category' ) || '' ).toString(),
				tag: ( data.get( 'story_tag' ) || '' ).toString(),
				search: ( data.get( 'story_search' ) || '' ).toString().trim(),
			} );

			if ( controller ) {
				controller.abort();
			}

			controller = window.AbortController ? new window.AbortController() : null;

			return window.fetch( strings.endpoint + ( strings.endpoint.indexOf( '?' ) === -1 ? '?' : '&' ) + params.toString(), {
				credentials: 'same-origin',
				headers: { Accept: 'application/json' },
				signal: controller ? controller.signal : undefined,
			} ).then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Request failed' );
				}

				return response.json();
			} );
		};

		const setHasMore = function ( hasMore, nextOffset ) {
			offset = nextOffset;

			if ( loadMore ) {
				loadMore.hidden = ! hasMore;
				loadMore.disabled = false;
			}
		};

		const applyFilters = function () {
			grid.classList.add( 'is-loading' );
			grid.setAttribute( 'aria-busy', 'true' );
			announce( strings.loading || '' );

			request( 0 ).then( function ( response ) {
				grid.innerHTML = response.html || '';

				if ( empty ) {
					empty.hidden = response.count > 0;
				}

				setHasMore( response.has_more, response.count );
				announce( response.count ? format( strings.showing, [ response.count, Math.max( response.total, response.count ) ] ) : strings.noResults || '' );
			} ).catch( function ( error ) {
				if ( error && 'AbortError' === error.name ) {
					return;
				}

				announce( strings.error || '' );
			} ).finally( function () {
				grid.classList.remove( 'is-loading' );
				grid.removeAttribute( 'aria-busy' );
			} );
		};

		const appendStories = function () {
			loadMore.disabled = true;
			announce( strings.loading || '' );

			request( offset ).then( function ( response ) {
				const firstIndex = grid.children.length;

				grid.insertAdjacentHTML( 'beforeend', response.html || '' );
				setHasMore( response.has_more, offset + response.count );

				/* Keep keyboard users at the start of the stories they asked for. */
				const firstNew = grid.children[ firstIndex ];
				const link = firstNew ? firstNew.querySelector( '.ltt-story-card__link' ) : null;

				if ( link ) {
					link.focus();
				}

				announce( format( strings.loaded, [ response.count ] ) );
			} ).catch( function ( error ) {
				loadMore.disabled = false;

				if ( error && 'AbortError' === error.name ) {
					return;
				}

				announce( strings.error || '' );
			} );
		};

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			if ( loadMore && event.submitter === loadMore ) {
				appendStories();
				return;
			}

			window.clearTimeout( searchTimer );
			lastSearch = search ? search.value.trim() : '';
			applyFilters();
		} );

		form.addEventListener( 'change', function ( event ) {
			if ( event.target.matches( 'select[data-story-grid-filter]' ) ) {
				applyFilters();
			}
		} );

		if ( search ) {
			search.addEventListener( 'input', function () {
				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function () {
					const value = search.value.trim();

					if ( value === lastSearch || ( value.length > 0 && value.length < 2 ) ) {
						return;
					}

					lastSearch = value;
					applyFilters();
				}, 400 );
			} );
		}

		/* Load More sits outside the form; browsers without `submitter` still land here. */
		if ( loadMore ) {
			loadMore.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				if ( ! loadMore.disabled ) {
					appendStories();
				}
			} );
		}
	};

	document.querySelectorAll( '[data-story-grid]' ).forEach( initGrid );
}() );
