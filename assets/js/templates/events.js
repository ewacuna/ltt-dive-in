/**
 * Events page browser.
 *
 * Enhances the server-rendered GET form: filters apply without reloading,
 * Load More appends results, the view switch re-renders the loaded events,
 * the filters panel collapses on wider screens or opens as a bottom sheet
 * below 768px, and Quick View shows an event's details in place: over its
 * row in the grid, or instead of its card in the list and on small screens.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const settings = window.ltt_dive_in_events || {};
	const mobileQuery = window.matchMedia( '(max-width: 767.98px)' );
	const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
	const wideQuery = window.matchMedia( '(min-width: 992px)' );
	const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

	function format( template, values ) {
		let index = 0;

		return String( template || '' ).replace( /%(\d\$)?d/g, function ( match, position ) {
			const value = position ? values[ parseInt( position, 10 ) - 1 ] : values[ index++ ];

			return String( value );
		} );
	}

	function initBrowser( root ) {
		const form = root.querySelector( '[data-events-form]' );
		const panel = root.querySelector( '[data-events-filters]' );
		const results = root.querySelector( '[data-events-results]' );

		if ( ! form || ! panel || ! results || ! root.dataset.endpoint || ! window.fetch ) {
			return;
		}

		const showButton = root.querySelector( '.events-toolbar__filters' );
		const hideButton = root.querySelector( '.events-filters__hide' );
		const toggles = root.querySelectorAll( '[data-events-filters-toggle]' );
		const status = root.querySelector( '[data-events-status]' );
		const empty = root.querySelector( '[data-events-empty]' );
		const error = root.querySelector( '[data-events-error]' );
		const loadMore = root.querySelector( '[data-events-load-more]' );
		const viewInput = root.querySelector( '[data-events-view-input]' );
		const viewLinks = root.querySelectorAll( '[data-events-view]' );
		const range = root.querySelector( '[data-events-range]' );
		const datesToggle = root.querySelector( '[data-events-dates-toggle]' );
		const dates = root.querySelector( '[data-events-dates]' );
		const fromInput = form.querySelector( '[name="event_from"]' );
		const toInput = form.querySelector( '[name="event_to"]' );
		const categoriesToggle = root.querySelector( '[data-events-categories-toggle]' );
		const categoriesList = root.querySelector( '.events-filters__categories' );
		const categoriesDropdown = root.querySelector( '[data-events-categories-dropdown]' );
		const categoriesPanel = root.querySelector( '[data-events-categories-panel]' );
		const clearLink = root.querySelector( '[data-events-clear]' );
		const searchInput = form.querySelector( '[name="event_search"]' );
		const backdrop = document.createElement( 'div' );
		let collapsed = ! wideQuery.matches;
		let sheetOpen = false;
		let controller = null;
		let searchTimer = 0;
		let quickView = null;

		backdrop.className = 'events-browser__backdrop';
		backdrop.setAttribute( 'aria-hidden', 'true' );
		root.appendChild( backdrop );
		root.classList.add( 'is-enhanced' );
		toggles.forEach( function ( toggle ) {
			toggle.hidden = false;
		} );

		/* Filters panel and bottom sheet. */

		function syncToggles() {
			const expanded = mobileQuery.matches ? sheetOpen : ! collapsed;

			toggles.forEach( function ( toggle ) {
				toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			} );
			root.classList.toggle( 'is-filters-collapsed', collapsed );
			root.classList.toggle( 'is-sheet-open', mobileQuery.matches && sheetOpen );
			document.documentElement.classList.toggle( 'has-events-sheet', mobileQuery.matches && sheetOpen );

			if ( mobileQuery.matches ) {
				panel.setAttribute( 'role', 'dialog' );
				panel.setAttribute( 'aria-modal', 'true' );
				panel.inert = ! sheetOpen;
			} else {
				panel.removeAttribute( 'role' );
				panel.removeAttribute( 'aria-modal' );
				panel.inert = false;
			}
		}

		function openSheet() {
			sheetOpen = true;
			syncToggles();
			window.requestAnimationFrame( function () {
				hideButton.focus();
			} );
		}

		function closeSheet( restoreFocus ) {
			sheetOpen = false;
			syncToggles();

			if ( restoreFocus && showButton ) {
				showButton.focus();
			}
		}

		function togglePanel( event ) {
			if ( mobileQuery.matches ) {
				if ( sheetOpen ) {
					closeSheet( true );
				} else {
					openSheet();
				}

				return;
			}

			collapsed = ! collapsed;
			syncToggles();
			( collapsed ? showButton : hideButton ).focus();
			event.preventDefault();
		}

		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', togglePanel );
		} );

		backdrop.addEventListener( 'click', function () {
			closeSheet( true );
		} );

		panel.addEventListener( 'keydown', function ( event ) {
			if ( ! mobileQuery.matches || ! sheetOpen ) {
				return;
			}

			if ( 'Escape' === event.key && ! event.defaultPrevented ) {
				closeSheet( true );
				return;
			}

			if ( 'Tab' !== event.key ) {
				return;
			}

			const focusable = Array.prototype.filter.call( panel.querySelectorAll( focusableSelector ), function ( element ) {
				return element.offsetParent !== null;
			} );

			if ( ! focusable.length ) {
				return;
			}

			if ( event.shiftKey && document.activeElement === focusable[ 0 ] ) {
				focusable[ focusable.length - 1 ].focus();
				event.preventDefault();
			} else if ( ! event.shiftKey && document.activeElement === focusable[ focusable.length - 1 ] ) {
				focusable[ 0 ].focus();
				event.preventDefault();
			}
		} );

		mobileQuery.addEventListener( 'change', function () {
			sheetOpen = false;
			syncToggles();
		} );

		/* Dates and categories disclosures. */

		if ( datesToggle && dates ) {
			datesToggle.hidden = false;
			dates.hidden = ! ( fromInput && fromInput.value );
			datesToggle.setAttribute( 'aria-expanded', dates.hidden ? 'false' : 'true' );
			datesToggle.addEventListener( 'click', function () {
				dates.hidden = ! dates.hidden;
				datesToggle.setAttribute( 'aria-expanded', dates.hidden ? 'false' : 'true' );

				if ( ! dates.hidden && fromInput ) {
					fromInput.focus();
				}
			} );
		}

		if ( categoriesToggle && categoriesList ) {
			const label = categoriesToggle.querySelector( '[data-events-categories-label]' );
			const setExpanded = function ( expanded ) {
				categoriesList.classList.toggle( 'is-collapsed', ! expanded );
				categoriesToggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
				label.textContent = expanded ? categoriesToggle.dataset.labelLess : categoriesToggle.dataset.labelMore;
			};

			categoriesToggle.hidden = false;
			setExpanded( !! categoriesList.querySelector( '[data-events-category-extra] input:checked' ) );
			categoriesToggle.addEventListener( 'click', function () {
				setExpanded( 'true' !== categoriesToggle.getAttribute( 'aria-expanded' ) );
			} );
		}

		// Below 768px the categories open as a multiselect menu.
		function syncCategoriesSummary() {
			if ( ! categoriesDropdown ) {
				return;
			}

			const count = form.querySelectorAll( '[data-events-filter="category"]:checked' ).length;
			const summary = categoriesDropdown.querySelector( '[data-events-categories-summary]' );

			summary.textContent = count ? categoriesDropdown.dataset.labelSelected.replace( '%d', count ) : categoriesDropdown.dataset.labelEmpty;
		}

		if ( categoriesDropdown && categoriesPanel ) {
			const categoriesField = categoriesDropdown.closest( 'fieldset' );
			const setDropdown = function ( expanded ) {
				categoriesDropdown.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
				categoriesPanel.classList.toggle( 'is-open', expanded );
			};

			categoriesDropdown.hidden = false;
			syncCategoriesSummary();
			categoriesDropdown.addEventListener( 'click', function () {
				setDropdown( 'true' !== categoriesDropdown.getAttribute( 'aria-expanded' ) );
			} );

			// Escape closes the menu before the bottom sheet.
			categoriesField.addEventListener( 'keydown', function ( event ) {
				if ( 'Escape' === event.key && categoriesPanel.classList.contains( 'is-open' ) ) {
					event.preventDefault();
					setDropdown( false );
					categoriesDropdown.focus();
				}
			} );

			categoriesField.addEventListener( 'focusout', function ( event ) {
				if ( event.relatedTarget && ! categoriesField.contains( event.relatedTarget ) ) {
					setDropdown( false );
				}
			} );

			document.addEventListener( 'click', function ( event ) {
				if ( ! categoriesField.contains( event.target ) ) {
					setDropdown( false );
				}
			} );

			mobileQuery.addEventListener( 'change', function () {
				setDropdown( false );
			} );
		}

		/* Quick View. */

		function enableQuickViewButtons() {
			results.querySelectorAll( '[data-event-quick-view-open]' ).forEach( function ( button ) {
				button.hidden = false;
			} );
		}

		function positionQuickView() {
			if ( quickView && quickView.overlay ) {
				quickView.panel.style.top = quickView.card.offsetTop + 'px';
				quickView.panel.style.minHeight = quickView.card.offsetHeight + 'px';
			}
		}

		function closeQuickView( restoreFocus ) {
			if ( ! quickView ) {
				return;
			}

			const current = quickView;

			quickView = null;
			current.panel.remove();
			current.card.hidden = false;
			current.covered.forEach( function ( card ) {
				card.inert = false;
			} );
			current.trigger.setAttribute( 'aria-expanded', 'false' );

			if ( restoreFocus ) {
				current.trigger.focus();
			}
		}

		function openQuickView( card, trigger ) {
			const template = card.querySelector( '[data-event-quick-view-template]' );

			if ( ! template || ! template.content.firstElementChild ) {
				return;
			}

			closeQuickView( false );

			const panel = template.content.firstElementChild.cloneNode( true );
			const overlay = results.classList.contains( 'events-browser__grid--grid' ) && ! mobileQuery.matches;
			const covered = [];

			panel.id = 'events-quick-view';
			trigger.setAttribute( 'aria-controls', panel.id );
			trigger.setAttribute( 'aria-expanded', 'true' );
			card.after( panel );

			if ( overlay ) {
				panel.classList.add( 'ltt-event-quick-view--overlay' );
				Array.prototype.forEach.call( results.children, function ( item ) {
					if ( item !== panel && item.offsetTop === card.offsetTop ) {
						item.inert = true;
						covered.push( item );
					}
				} );
			} else {
				card.hidden = true;
			}

			quickView = {
				card: card,
				trigger: trigger,
				panel: panel,
				overlay: overlay,
				covered: covered,
			};
			positionQuickView();

			if ( overlay && ! reducedMotion.matches ) {
				const right = results.clientWidth - card.offsetLeft - card.offsetWidth;

				panel.style.clipPath = 'inset(0 ' + right + 'px 0 ' + card.offsetLeft + 'px)';
				panel.getBoundingClientRect();
				panel.style.transition = 'clip-path 350ms ease-out';
				panel.style.clipPath = 'inset(0 0 0 0)';
			}

			panel.querySelector( '[data-event-quick-view-close]' ).addEventListener( 'click', function () {
				closeQuickView( true );
			} );
			panel.addEventListener( 'keydown', function ( event ) {
				if ( 'Escape' === event.key ) {
					event.preventDefault();
					closeQuickView( true );
				}
			} );
			panel.focus( { preventScroll: true } );
			panel.scrollIntoView( { block: 'nearest', behavior: reducedMotion.matches ? 'auto' : 'smooth' } );
		}

		results.addEventListener( 'click', function ( event ) {
			const trigger = event.target.closest( '[data-event-quick-view-open]' );

			if ( ! trigger ) {
				return;
			}

			event.preventDefault();

			if ( quickView && quickView.trigger === trigger ) {
				closeQuickView( true );
			} else {
				openQuickView( trigger.closest( '[data-event-card]' ), trigger );
			}
		} );

		window.addEventListener( 'resize', positionQuickView );
		mobileQuery.addEventListener( 'change', function () {
			closeQuickView( false );
		} );
		enableQuickViewButtons();

		/* Requests. */

		function getParams() {
			const params = new URLSearchParams();

			new FormData( form ).forEach( function ( value, key ) {
				if ( 'event_page' !== key && '' !== value && ! ( 'event_sort' === key && 'featured' === value ) ) {
					params.append( key, value );
				}
			} );

			return params;
		}

		function updateUrl( params ) {
			const query = params.toString();

			window.history.replaceState( null, '', window.location.pathname + ( query ? '?' + query : '' ) + window.location.hash );
		}

		function announce( message ) {
			if ( status ) {
				status.textContent = message;
			}
		}

		function request( params, offset, limit ) {
			const query = new URLSearchParams( params );

			if ( offset ) {
				query.set( 'offset', String( offset ) );
			}

			if ( limit ) {
				query.set( 'limit', String( limit ) );
			}

			if ( controller ) {
				controller.abort();
			}

			controller = new AbortController();
			results.setAttribute( 'aria-busy', 'true' );
			announce( settings.loading );

			return window.fetch( root.dataset.endpoint + ( root.dataset.endpoint.indexOf( '?' ) === -1 ? '?' : '&' ) + query.toString(), {
				credentials: 'same-origin',
				headers: { Accept: 'application/json' },
				signal: controller.signal,
			} ).then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'Request failed' );
				}

				return response.json();
			} ).finally( function () {
				results.removeAttribute( 'aria-busy' );
			} );
		}

		function updateMeta( data ) {
			if ( loadMore ) {
				loadMore.dataset.offset = String( data.nextOffset );
				loadMore.hidden = ! data.hasMore;
				loadMore.disabled = false;
			}

			if ( range ) {
				range.textContent = data.rangeLabel || range.dataset.emptyLabel;
			}

			error.hidden = true;
		}

		function handleError( requestError ) {
			if ( requestError && 'AbortError' === requestError.name ) {
				return;
			}

			error.hidden = false;
			announce( settings.error );

			if ( loadMore ) {
				loadMore.disabled = false;
			}
		}

		function refresh( limit ) {
			const params = getParams();

			updateUrl( params );

			return request( params, 0, limit ).then( function ( data ) {
				closeQuickView( false );
				results.innerHTML = data.html;
				enableQuickViewButtons();
				empty.hidden = data.count > 0;
				updateMeta( data );
				announce( data.count ? format( settings.showing, [ results.children.length, data.total ] ) : settings.noResults );
			} ).catch( handleError );
		}

		function appendMore() {
			const params = getParams();
			const before = results.children.length;

			loadMore.disabled = true;

			request( params, parseInt( loadMore.dataset.offset, 10 ) || 0 ).then( function ( data ) {
				results.insertAdjacentHTML( 'beforeend', data.html );
				enableQuickViewButtons();
				updateMeta( data );
				announce( format( settings.loaded, [ data.count ] ) );

				const first = results.children[ before ];
				const link = first ? first.querySelector( 'a' ) : null;

				if ( link ) {
					link.focus();
				}
			} ).catch( handleError );
		}

		/* Filter input. */

		// Clear Filters shows only while a filter is chosen.
		function syncClear() {
			if ( ! clearLink ) {
				return;
			}

			clearLink.hidden = ! Array.prototype.some.call( form.elements, function ( input ) {
				const filter = input.dataset ? input.dataset.eventsFilter : '';

				if ( ! filter || 'sort' === filter ) {
					return false;
				}

				return 'checkbox' === input.type || 'radio' === input.type ? input.checked : '' !== input.value.trim();
			} );
		}

		form.addEventListener( 'change', function ( event ) {
			const filter = event.target.dataset ? event.target.dataset.eventsFilter : '';

			if ( ! filter || 'search' === filter ) {
				syncClear();
				return;
			}

			if ( 'period' === filter && fromInput && toInput ) {
				fromInput.value = '';
				toInput.value = '';
			}

			if ( ( 'from' === filter || 'to' === filter ) && fromInput ) {
				form.querySelectorAll( '[name="event_period"]' ).forEach( function ( radio ) {
					radio.checked = false;
				} );

				if ( toInput ) {
					toInput.min = fromInput.value || fromInput.min;
				}
			}

			syncClear();
			syncCategoriesSummary();

			// The bottom sheet applies its choices with its Apply button.
			if ( ! mobileQuery.matches ) {
				refresh();
			}
		} );

		if ( searchInput ) {
			searchInput.addEventListener( 'input', function () {
				syncClear();
				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( refresh, 400 );
			} );
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			window.clearTimeout( searchTimer );

			if ( loadMore && event.submitter === loadMore ) {
				appendMore();
				return;
			}

			refresh();

			if ( mobileQuery.matches && sheetOpen ) {
				closeSheet( true );
			}
		} );

		if ( clearLink ) {
			clearLink.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				form.querySelectorAll( 'input[type="checkbox"], input[type="radio"]' ).forEach( function ( input ) {
					input.checked = false;
				} );
				form.querySelectorAll( 'input[type="date"], input[type="search"]' ).forEach( function ( input ) {
					input.value = '';
				} );

				const region = form.querySelector( 'select[name="event_region"]' );

				if ( region && region.value ) {
					region.value = '';
					region.dispatchEvent( new Event( 'change', { bubbles: false } ) );
				}

				syncClear();
				syncCategoriesSummary();

				// The link hides itself, so keep focus in the panel.
				const firstFilter = form.querySelector( '[data-events-filter="period"]' ) || searchInput;

				if ( firstFilter ) {
					firstFilter.focus();
				}

				if ( ! mobileQuery.matches ) {
					refresh();
				}
			} );
		}

		viewLinks.forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				const view = link.dataset.eventsView;

				event.preventDefault();

				if ( 'true' === link.getAttribute( 'aria-current' ) ) {
					return;
				}

				viewLinks.forEach( function ( other ) {
					if ( other === link ) {
						other.setAttribute( 'aria-current', 'true' );
					} else {
						other.removeAttribute( 'aria-current' );
					}
				} );
				viewInput.value = 'list' === view ? 'list' : '';
				results.className = results.className.replace( /events-browser__grid--\w+/, 'events-browser__grid--' + view );
				refresh( parseInt( loadMore ? loadMore.dataset.offset : 0, 10 ) || 0 );
			} );
		} );

		syncToggles();
	}

	function init() {
		document.querySelectorAll( '[data-events-browser]' ).forEach( initBrowser );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
