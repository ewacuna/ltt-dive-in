/**
 * Block-local authoring, validation and keyboard ordering for filter groups.
 */
( function ( $, acf ) {
	'use strict';

	if ( ! acf ) {
		return;
	}
	const settings = window.ltt_dive_in_page_driver_editor || {};
	const strings = settings.groupStrings || {};
	const prefix = 'field_ltt_dive_in_page_driver_';
	const states = new WeakMap();
	const lockKey = 'ltt-dive-in-up-driver-filter-groups';
	let noticeId = 0;
	const selector = function ( suffix ) { return '[data-key="' + prefix + suffix + '"]'; };
	const top = function ( $fields, suffix ) { return $fields.children( selector( suffix ) ); };
	const checked = function ( $field ) { return Boolean( $field.find( 'input[type="checkbox"]' ).prop( 'checked' ) ); };
	const rows = function ( $groups ) { return $groups.find( '.acf-repeater:first > table > tbody > .acf-row' ).not( '.acf-clone, .acf-deleted' ); };
	const pageIds = function ( $field ) {
		return $field.find( '.values-list .acf-rel-item' ).map( function () { return String( $( this ).data( 'id' ) ); } ).get();
	};
	const format = function ( text, values ) {
		return ( text || '' ).replace( /\{(\w+)\}/g, function ( match, key ) { return String( values[ key ] ?? '' ); } );
	};
	const owner = function ( $element ) {
		const $groups = $element.closest( selector( 'filter_groups' ) );
		return $groups.length ? $groups.closest( '.acf-fields' ) : $element.closest( '.acf-fields' );
	};
	// ACF Blocks replace their data with serialized enabled inputs. Mirror only
	// disabled editorial controls so changing modes cannot erase dormant values.
	const dormantValues = function ( $fields ) {
		const values = [];
		[ 'tiles', 'toggle_terms', 'taxonomy', 'filter_groups', 'cards_per_filter' ].forEach( function ( suffix ) {
			top( $fields, suffix ).find( 'input:disabled, select:disabled, textarea:disabled' ).each( function () {
				if ( ! this.name || $( this ).closest( '.acf-clone' ).length ||
					( [ 'checkbox', 'radio' ].includes( this.type ) && ! this.checked ) ||
					[ 'button', 'submit', 'file' ].includes( this.type ) ) { return; }
				const value = $( this ).val();
				( Array.isArray( value ) ? value : [ value ] ).forEach( ( item ) => {
					if ( item !== null ) { values.push( { name: this.name, value: item } ); }
				} );
			} );
		} );
		return values;
	};
	const preserveDormantFields = function ( $fields ) {
		if ( ! top( $fields, 'layout' ).length ) { return; }
		$fields.children( '.ltt-driver-preserved-fields' ).remove();
		const $mirror = $( '<div class="ltt-driver-preserved-fields" hidden aria-hidden="true"></div>' );
		dormantValues( $fields ).forEach( function ( item ) {
			$( '<input type="hidden">' ).attr( 'name', item.name ).val( item.value ).appendTo( $mirror );
		} );
		$fields.append( $mirror );
	};
	const isActive = function ( $fields ) {
		const layout = top( $fields, 'layout' ).find( 'select' ).val();
		return [ 'one_up', 'two_up', 'three_up', 'four_up', 'five_up' ].includes( layout ) &&
			checked( top( $fields, 'enable_toggles' ) ) && checked( top( $fields, 'cards_per_filter' ) );
	};
	const syncLock = function () {
		const editor = window.wp && window.wp.data && window.wp.data.dispatch( 'core/editor' );
		if ( ! editor || ! editor.lockPostSaving ) {
			return;
		}
		const invalid = document.querySelector( '[data-ltt-group-invalid="true"]' );
		editor[ invalid ? 'lockPostSaving' : 'unlockPostSaving' ]( lockKey );
		if ( editor.lockPostAutosaving ) {
			editor[ invalid ? 'lockPostAutosaving' : 'unlockPostAutosaving' ]( lockKey );
		}
	};
	const showErrors = function ( $groups, errors ) {
		let $notice = $groups.children( '.acf-input' ).children( '.ltt-driver-group-errors' );
		if ( ! $notice.length ) {
			$notice = $( '<div class="acf-notice -error ltt-driver-group-errors" role="alert"></div>' );
			$groups.children( '.acf-input' ).prepend( $notice );
			const id = ( $groups.attr( 'id' ) || 'ltt-groups-' + Math.random().toString( 36 ).slice( 2 ) ) + '-errors';
			$notice.attr( 'id', id );
			$groups.attr( 'aria-describedby', id );
		}
		const text = errors.join( '\n' );
		if ( $notice.data( 'message' ) !== text ) {
			$notice.empty().data( 'message', text );
			errors.forEach( function ( error ) { $notice.append( $( '<p></p>' ).text( error ) ); } );
		}
		$notice.prop( 'hidden', ! errors.length ).toggle( errors.length > 0 );
	};
	const showLoadError = function ( $groups ) {
		showErrors( $groups, [ strings.loadError ] );
		const $notice = $groups.find( '.ltt-driver-group-errors' );
		$notice.find( '[data-ltt-retry]' ).remove();
		$( '<button type="button" class="button" data-ltt-retry></button>' ).text( strings.retry ).appendTo( $notice );
	};
	const showRowErrors = function ( $groups, errors ) {
		rows( $groups ).each( function ( index ) {
			const $row = $( this );
			const messages = errors[ index ] || [];
			const $term = $row.find( selector( 'filter_term' ) );
			const $pages = $row.find( selector( 'filter_pages' ) );
			let $notice = $pages.children( '.acf-input' ).children( '.ltt-driver-row-error' );
			if ( ! $notice.length ) {
				$notice = $( '<p class="description ltt-driver-row-error"></p>' ).appendTo( $pages.children( '.acf-input' ) );
			}
			if ( $notice.data( 'owner' ) !== $pages[0] ) {
				$notice.attr( 'id', 'ltt-driver-group-row-error-' + ( ++noticeId ) ).data( 'owner', $pages[0] );
			}
			$notice.text( messages.join( ' ' ) ).prop( 'hidden', ! messages.length );
			const $controls = $term.find( 'select' ).add( $pages.find( '[data-filter="s"], .values-list .acf-rel-item' ) );
			$controls.each( function () {
				const ids = ( $( this ).attr( 'aria-describedby' ) || '' ).split( ' ' ).filter( function ( id ) { return id && ! id.startsWith( 'ltt-driver-group-row-error-' ); } );
				if ( messages.length ) { ids.push( $notice.attr( 'id' ) ); }
				$( this ).attr( 'aria-describedby', ids.join( ' ' ) ).attr( 'aria-invalid', messages.length ? 'true' : 'false' );
			} );
		} );
	};
	const moveButtons = function ( $parent, kind, name, first, last ) {
		let $tools = $parent.children( '.ltt-driver-order' );
		if ( ! $tools.length ) {
			$tools = $( '<span class="ltt-driver-order"></span>' ).appendTo( $parent );
			[ 'up', 'down' ].forEach( function ( direction ) {
				$( '<button type="button" class="button button-small"></button>' )
					.attr( { 'data-ltt-move': direction, 'data-ltt-kind': kind } ).text( strings[ direction ] ).appendTo( $tools );
			} );
		}
		$tools.find( 'button' ).each( function () {
			const direction = $( this ).attr( 'data-ltt-move' );
			$( this ).attr( 'aria-label', format( strings[ direction + 'Label' ], { name: name } ) )
				.prop( 'disabled', direction === 'up' ? first : last );
		} );
	};
	const updateRows = function ( $fields, state ) {
		const $groups = top( $fields, 'filter_groups' );
		const layout = top( $fields, 'layout' ).find( 'select' ).val();
		const limit = ( settings.tileLimits[ layout ] || { max: 1 } ).max;
		const $rows = rows( $groups );
		$rows.each( function ( index ) {
			const $row = $( this );
			const $term = $row.find( selector( 'filter_term' ) ).first();
			const $select = $term.find( 'select' );
			const selected = String( $select.val() || '' );
			const oldLabel = $select.find( 'option:selected' ).text();
			if ( state.terms ) {
				$select.empty().append( $( '<option></option>' ).val( '' ).text( strings.chooseFilter ) );
				Object.entries( state.terms ).forEach( function ( entry ) {
					$select.append( $( '<option></option>' ).val( entry[0] ).text( entry[1] ) );
				} );
				if ( selected && ! Object.prototype.hasOwnProperty.call( state.terms, selected ) ) {
					$select.append( $( '<option></option>' ).val( selected ).text( oldLabel || format( strings.unavailable, { id: selected } ) ) );
				}
				$select.val( selected );
			}
			const name = selected ? $select.find( 'option:selected' ).text() : format( strings.filterPosition, { count: index + 1 } );
			moveButtons( $term.children( '.acf-input' ), 'group', name, index === 0, index === $rows.length - 1 );
			const $pages = $row.find( selector( 'filter_pages' ) ).first();
			const field = acf.getField( $pages );
			if ( field ) {
				field.set( 'min', 0 );
				field.set( 'max', limit );
			}
			const $items = $pages.find( '.values-list > li' );
			let $counter = $pages.children( '.acf-input' ).children( '.ltt-driver-counter' );
			if ( ! $counter.length ) {
				$counter = $( '<p class="description ltt-driver-counter" role="status"></p>' ).prependTo( $pages.children( '.acf-input' ) );
			}
			const count = pageIds( $pages ).length;
			$counter.text( format( strings.counter, { count: count, limit: limit } ) + ( count >= limit ? ' ' + strings.limitReached : '' ) );
			$items.each( function ( cardIndex ) {
				const $item = $( this );
				const title = $item.find( '.acf-rel-item' ).clone().children().remove().end().text();
				moveButtons( $item, 'card', title, cardIndex === 0, cardIndex === $items.length - 1 );
			} );
		} );
	};
	const collect = function ( $fields ) {
		const data = {};
		[ 'layout', 'taxonomy' ].forEach( function ( suffix ) { data[ 'ltt_dive_in_page_driver_' + suffix ] = top( $fields, suffix ).find( 'select' ).val() || ''; } );
		[ 'enable_toggles', 'cards_per_filter' ].forEach( function ( suffix ) { data[ 'ltt_dive_in_page_driver_' + suffix ] = checked( top( $fields, suffix ) ) ? 1 : 0; } );
		data.ltt_dive_in_page_driver_tiles = pageIds( top( $fields, 'tiles' ) );
		data.ltt_dive_in_page_driver_toggle_terms = top( $fields, 'toggle_terms' ).find( 'input[type="checkbox"]:checked' ).map( function () { return this.value; } ).get();
		data.ltt_dive_in_page_driver_filter_groups = rows( top( $fields, 'filter_groups' ) ).map( function () {
			return { term: $( this ).find( selector( 'filter_term' ) + ' select' ).val() || '', pages: pageIds( $( this ).find( selector( 'filter_pages' ) ) ) };
		} ).get();
		return data;
	};
	const sync = function ( $fields ) {
		if ( ! $fields.length || ! top( $fields, 'layout' ).length ) {
			return;
		}
		let state = states.get( $fields[0] );
		if ( ! state ) {
			state = { version: 0, active: null, terms: null, initialised: false, filling: false };
			states.set( $fields[0], state );
		}
		if ( state.filling ) {
			return;
		}
		preserveDormantFields( $fields );
		clearTimeout( state.timer );
		state.version += 1;
		const version = state.version;
		if ( state.xhr ) {
			state.xhr.abort();
		}
		const $groups = top( $fields, 'filter_groups' );
		const active = isActive( $fields );
		if ( active !== state.active ) {
			state.active = active;
			$( document ).trigger( 'ltt:page-driver-mode', [ $fields ] );
		}
		if ( ! active ) {
			$groups.removeAttr( 'data-ltt-group-invalid aria-busy' );
			showErrors( $groups, [] );
			showRowErrors( $groups, {} );
			syncLock();
			return;
		}
		const taxonomy = top( $fields, 'taxonomy' ).find( 'select' ).val();
		if ( taxonomy !== state.taxonomy ) {
			state.taxonomy = taxonomy;
			state.terms = null;
		}
		updateRows( $fields, state );
		$groups.attr( { 'data-ltt-group-invalid': 'true', 'aria-busy': 'true' } );
		syncLock();
		state.timer = window.setTimeout( function () {
			state.xhr = $.post( settings.ajaxUrl, { action: 'ltt_dive_in_page_driver_group_feedback', nonce: settings.nonce, data: collect( $fields ) } );
			const fail = function () {
				if ( version !== state.version || ! $fields[0].isConnected ) { return; }
				showLoadError( $groups );
			};
			state.xhr.done( function ( response ) {
				if ( version !== state.version || ! $fields[0].isConnected ) { return; }
				if ( ! response || ! response.success ) { fail(); return; }
				state.terms = response.data.terms || {};
				if ( ! state.initialised && ! rows( $groups ).length && taxonomy ) {
					state.initialised = true;
					state.filling = true;
					const repeater = acf.getField( $groups );
					const suggestions = response.data.suggestions.length ? response.data.suggestions : [ { term: '', pages: [] }, { term: '', pages: [] } ];
					suggestions.forEach( function ( suggestion ) {
						const $row = repeater.add();
						if ( ! $row ) { return; }
						const $select = $row.find( selector( 'filter_term' ) + ' select' );
						$select.append( $( '<option></option>' ).val( suggestion.term ).text( state.terms[ suggestion.term ] || '' ) ).val( String( suggestion.term ) );
						const field = acf.getField( $row.find( selector( 'filter_pages' ) ) );
						suggestion.pages.forEach( function ( page ) { field.$list( 'values' ).append( field.newValue( { id: page.id, text: acf.escHtml( page.text ) } ) ); } );
						field.$input().trigger( 'change' );
						field.set( 'paged', 1 );
						field.fetch();
					} );
					state.filling = false;
					sync( $fields );
					return;
				}
				state.initialised = state.initialised || rows( $groups ).length > 0;
				updateRows( $fields, state );
				const errors = response.data.errors || [];
				showErrors( $groups, errors );
				showRowErrors( $groups, response.data.rows || {} );
				$groups.attr( 'data-ltt-group-invalid', errors.length ? 'true' : 'false' );
				syncLock();
			} ).fail( function ( xhr, status ) { if ( status !== 'abort' ) { fail(); } } ).always( function () {
				if ( version === state.version ) { $groups.removeAttr( 'aria-busy' ); }
			} );
		}, 200 );
	};

	acf.addFilter( 'relationship_ajax_data', function ( data, field ) {
		if ( field.get( 'key' ) !== prefix + 'filter_pages' ) { return data; }
		const $fields = owner( field.$el );
		data.ltt_nonce = settings.nonce;
		data.ltt_taxonomy = top( $fields, 'taxonomy' ).find( 'select' ).val() || '';
		data.ltt_term = field.$el.closest( '.acf-row' ).find( selector( 'filter_term' ) + ' select' ).val() || '';
		window.setTimeout( function () {
			const xhr = field.get( 'xhr' );
			if ( ! xhr ) { return; }
			xhr.fail( function ( response, status ) {
				if ( status === 'abort' || field.get( 'xhr' ) !== xhr || ! isActive( $fields ) ) { return; }
				showLoadError( top( $fields, 'filter_groups' ) );
			} );
		}, 0 );
		return data;
	} );
	acf.addAction( 'ready append', function ( $el ) {
		$el.find( selector( 'layout' ) ).addBack( selector( 'layout' ) ).each( function () { sync( $( this ).closest( '.acf-fields' ) ); } );
	} );
	acf.addAction( 'disable_field enable_field', function ( field ) {
		preserveDormantFields( owner( field.$el ) );
	} );
	acf.addAction( 'remove', function () { window.setTimeout( syncLock, 0 ); } );
	$( document ).on( 'change', selector( 'layout' ) + ', ' + selector( 'taxonomy' ) + ', ' + selector( 'enable_toggles' ) + ', ' + selector( 'cards_per_filter' ) + ', ' + selector( 'filter_groups' ), function ( event ) {
		const $fields = owner( $( event.target ) );
		if ( $( event.target ).closest( selector( 'filter_term' ) ).length || $( event.target ).closest( selector( 'taxonomy' ) ).length ) {
			rows( top( $fields, 'filter_groups' ) ).each( function () {
				const field = acf.getField( $( this ).find( selector( 'filter_pages' ) ) );
				if ( field && isActive( $fields ) ) { field.set( 'paged', 1 ); field.fetch(); }
			} );
		}
		sync( $fields );
	} );
	$( document ).on( 'click', '[data-ltt-retry]', function () {
		const $fields = owner( $( this ) );
		$( this ).remove();
		rows( top( $fields, 'filter_groups' ) ).each( function () {
			const field = acf.getField( $( this ).find( selector( 'filter_pages' ) ) );
			if ( field ) { field.set( 'paged', 1 ); field.fetch(); }
		} );
		sync( $fields );
	} );
	$( document ).on( 'click', '[data-ltt-move]', function () {
		const $button = $( this );
		const $fields = owner( $button );
		const group = $button.attr( 'data-ltt-kind' ) === 'group';
		const $item = $button.closest( group ? '.acf-row' : 'li' );
		const up = $button.attr( 'data-ltt-move' ) === 'up';
		const $other = up ? $item.prev() : $item.next();
		if ( ! $other.length || $other.hasClass( 'acf-clone' ) ) { return; }
		if ( up ) { $item.insertBefore( $other ); } else { $item.insertAfter( $other ); }
		if ( group ) { acf.getField( top( $fields, 'filter_groups' ) ).render(); }
		$item.find( 'input' ).first().trigger( 'change' );
		sync( $fields );
		const $fallback = $item.find( '[data-ltt-move="' + ( up ? 'down' : 'up' ) + '"]' ).first();
		( $button.prop( 'disabled' ) ? $fallback : $button ).trigger( 'focus' );
	} );
}( jQuery, window.acf ) );
