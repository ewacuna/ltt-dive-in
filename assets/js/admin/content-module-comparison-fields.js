( function ( $, acf ) {
	'use strict';

	const variantKey = 'field_ltt_dive_in_content_module_variant';
	const columnsKey = 'field_ltt_dive_in_content_module_comparison_columns';
	const rowsKey = 'field_ltt_dive_in_content_module_comparison_rows';
	const columnLabelKey = 'field_ltt_dive_in_content_module_comparison_column_label';
	const settings = window.ltt_dive_in_content_module_comparison_editor || {};
	const cellGroups = [
		{
			key: 'field_ltt_dive_in_content_module_comparison_cell_one',
			type: 'field_ltt_dive_in_content_module_comparison_cell_one_type',
			text: 'field_ltt_dive_in_content_module_comparison_cell_one_text',
		},
		{
			key: 'field_ltt_dive_in_content_module_comparison_cell_two',
			type: 'field_ltt_dive_in_content_module_comparison_cell_two_type',
			text: 'field_ltt_dive_in_content_module_comparison_cell_two_text',
		},
		{
			key: 'field_ltt_dive_in_content_module_comparison_cell_three',
			type: 'field_ltt_dive_in_content_module_comparison_cell_three_type',
			text: 'field_ltt_dive_in_content_module_comparison_cell_three_text',
		},
		{
			key: 'field_ltt_dive_in_content_module_comparison_cell_four',
			type: 'field_ltt_dive_in_content_module_comparison_cell_four_type',
			text: 'field_ltt_dive_in_content_module_comparison_cell_four_text',
		},
	];
	const states = new WeakMap();
	let tokenCounter = 0;

	const rootFor = function ( field ) {
		return field.closest( '.acf-block-component' ) || field.closest( '.acf-fields' );
	};

	const getField = function ( root, key ) {
		return root.querySelector( '[data-key="' + key + '"]' );
	};

	const getRows = function ( field ) {
		return field ? Array.from( field.querySelectorAll( '.acf-repeater > table > tbody > .acf-row:not(.acf-clone)' ) ) : [];
	};

	const fieldValue = function ( row, key, selector ) {
		const field = row.querySelector( '[data-key="' + key + '"]' );
		const input = field && field.querySelector( selector );
		return input ? input.value : '';
	};

	const setFieldValue = function ( row, key, selector, value ) {
		const field = row.querySelector( '[data-key="' + key + '"]' );
		const input = field && field.querySelector( selector );

		if ( input && input.value !== value ) {
			$( input ).val( value ).trigger( 'change' );
		}
	};

	const readCell = function ( row, cell ) {
		const group = row.querySelector( '[data-key="' + cell.key + '"]' );

		if ( ! group ) {
			return { type: 'text', text: '' };
		}

		return {
			type: fieldValue( group, cell.type, 'select' ) || 'text',
			text: fieldValue( group, cell.text, 'input[type="text"]' ),
		};
	};

	const writeCell = function ( row, cell, value ) {
		const group = row.querySelector( '[data-key="' + cell.key + '"]' );

		if ( ! group ) {
			return;
		}

		setFieldValue( group, cell.type, 'select', value.type || 'text' );
		setFieldValue( group, cell.text, 'input[type="text"]', value.text || '' );
	};

	const currentHeaderRows = function ( field, state ) {
		return getRows( field ).map( function ( row ) {
			if ( ! state.rowTokens.has( row ) ) {
				state.rowTokens.set( row, 'column-' + ( ++tokenCounter ) );
			}

			return {
				element: row,
				token: state.rowTokens.get( row ),
				label: fieldValue( row, columnLabelKey, 'input[type="text"]' ).trim(),
			};
		} );
	};

	const updateCellGroups = function ( root, columns ) {
		getRows( getField( root, rowsKey ) ).forEach( function ( row ) {
			cellGroups.forEach( function ( cell, index ) {
				const group = row.querySelector( '[data-key="' + cell.key + '"]' );
				if ( ! group ) {
					return;
				}

				const isInactive = index >= columns.length;
				group.hidden = isInactive;
				group.style.display = isInactive ? 'none' : '';
				group.setAttribute( 'aria-hidden', isInactive ? 'true' : 'false' );
				if ( isInactive ) {
					writeCell( row, cell, { type: 'text', text: '' } );
				}

				const label = group.querySelector( '.acf-label label' );
				if ( label ) {
					const columnLabel = columns[ index ] && columns[ index ].label;
					const text = 'Column ' + ( index + 1 ) + ( columnLabel ? ': ' + columnLabel : '' );
					const textNode = Array.from( label.childNodes ).find( function ( node ) {
						return Node.TEXT_NODE === node.nodeType;
					} );
					if ( textNode ) {
						textNode.nodeValue = text;
					} else {
						label.insertBefore( document.createTextNode( text ), label.firstChild );
					}
				}
			} );
		} );
	};

	const updateMoveControls = function ( columnsField, columns ) {
		getRows( columnsField ).forEach( function ( row, index ) {
			const handle = row.querySelector( 'td.acf-row-handle.order' );
			if ( ! handle ) {
				return;
			}

			let controls = handle.querySelector( '.ltt-dive-in-comparison-column-order' );
			if ( ! controls ) {
				controls = document.createElement( 'span' );
				controls.className = 'ltt-dive-in-comparison-column-order';

				[ 'up', 'down' ].forEach( function ( direction ) {
					const button = document.createElement( 'button' );
					button.type = 'button';
					button.className = 'button button-small ltt-dive-in-comparison-column-move';
					button.setAttribute( 'data-direction', direction );
					controls.appendChild( button );
				} );

				handle.appendChild( controls );
			}

			const label = columns[ index ] && columns[ index ].label ? columns[ index ].label : String( index + 1 );
			controls.querySelectorAll( 'button[data-direction]' ).forEach( function ( button ) {
				const isUp = 'up' === button.getAttribute( 'data-direction' );
				button.textContent = isUp ? ( settings.moveUpShort || 'Up' ) : ( settings.moveDownShort || 'Down' );
				button.setAttribute( 'aria-label', ( isUp ? ( settings.moveUpLabel || 'Move up' ) : ( settings.moveDownLabel || 'Move down' ) ) + ' ' + label );
				button.disabled = isUp ? 0 === index : index === columns.length - 1;
			} );
		} );
	};

	const ensureInitialRows = function ( columnsField, rowsField ) {
		let count = getRows( columnsField ).length;
		const addColumn = columnsField.querySelector( '.acf-repeater > .acf-actions [data-event="add-row"]' );

		while ( count < 2 && addColumn ) {
			const previousCount = count;
			addColumn.click();
			count = getRows( columnsField ).length;
			if ( count <= previousCount ) {
				break;
			}
		}

		count = getRows( rowsField ).length;
		const addRow = rowsField.querySelector( '.acf-repeater > .acf-actions [data-event="add-row"]' );

		while ( count < 1 && addRow ) {
			const previousCount = count;
			addRow.click();
			count = getRows( rowsField ).length;
			if ( count <= previousCount ) {
				break;
			}
		}
	};

	const updateRoot = function ( root ) {
		const variant = getField( root, variantKey );
		const columnsField = getField( root, columnsKey );
		const rowsField = getField( root, rowsKey );

		if ( ! variant || ! columnsField || ! rowsField || ! variant.querySelector( 'select' ) || 'comparison_table' !== variant.querySelector( 'select' ).value ) {
			return;
		}

		let state = states.get( root );
		if ( ! state ) {
			state = { rowTokens: new WeakMap(), order: [], observer: null, scheduled: false, initializing: false };
			states.set( root, state );
		}

		if ( state.initializing ) {
			return;
		}

		state.initializing = true;
		try {
			ensureInitialRows( columnsField, rowsField );
		} finally {
			state.initializing = false;
		}
		const currentColumns = currentHeaderRows( columnsField, state );
		const currentOrder = currentColumns.map( function ( column ) { return column.token; } );

		if ( state.order.length && currentOrder.join( '|\n' ) !== state.order.join( '|\n' ) ) {
			getRows( rowsField ).forEach( function ( row ) {
				const oldValues = new Map();
				state.order.forEach( function ( token, index ) {
					oldValues.set( token, readCell( row, cellGroups[ index ] ) );
				} );

				currentOrder.forEach( function ( token, index ) {
					writeCell( row, cellGroups[ index ], oldValues.get( token ) || { type: 'text', text: '' } );
				} );
				cellGroups.slice( currentOrder.length ).forEach( function ( cell ) {
					writeCell( row, cell, { type: 'text', text: '' } );
				} );
			} );
		}

		state.order = currentOrder;
		updateCellGroups( root, currentColumns );
		updateMoveControls( columnsField, currentColumns );

		if ( ! state.observer ) {
			const body = columnsField.querySelector( '.acf-repeater > table > tbody' );
			if ( body ) {
				state.observer = new MutationObserver( function () {
					if ( state.scheduled ) {
						return;
					}
					state.scheduled = true;
					window.setTimeout( function () {
						state.scheduled = false;
						updateRoot( root );
					}, 0 );
				} );
				state.observer.observe( body, { childList: true } );
			}
		}
	};

	const updateWithin = function ( context ) {
		const element = context && context[ 0 ] ? context[ 0 ] : context;
		const roots = new Set();
		const fields = element && element.querySelectorAll ? element.querySelectorAll( '[data-key="' + variantKey + '"]' ) : document.querySelectorAll( '[data-key="' + variantKey + '"]' );

		Array.from( fields ).forEach( function ( field ) {
			const root = rootFor( field );
			if ( root ) {
				roots.add( root );
			}
		} );

		if ( element && element.matches && element.matches( '[data-key="' + variantKey + '"]' ) ) {
			const root = rootFor( element );
			if ( root ) {
				roots.add( root );
			}
		}

		if ( element && element.closest ) {
			const root = rootFor( element );
			if ( root && getField( root, variantKey ) ) {
				roots.add( root );
			}
		}

		roots.forEach( updateRoot );
	};

	acf.addAction( 'ready append sortstop', updateWithin );
	acf.addAction( 'remove', function () {
		window.setTimeout( function () { updateWithin( document ); }, 0 );
	} );

	$( document ).on( 'change', '[data-key="' + variantKey + '"] select', function () {
		updateWithin( this );
	} );
	$( document ).on( 'input change', '[data-key="' + columnLabelKey + '"] input[type="text"]', function () {
		updateWithin( this );
	} );
	$( document ).on( 'mousedown touchstart', '.ltt-dive-in-comparison-column-move', function ( event ) {
		event.stopPropagation();
	} );
	$( document ).on( 'click', '.ltt-dive-in-comparison-column-move', function ( event ) {
		event.preventDefault();
		event.stopPropagation();
		const root = rootFor( this );
		const columnsField = root && getField( root, columnsKey );
		const row = this.closest( '.acf-row:not(.acf-clone)' );
		const rows = columnsField ? getRows( columnsField ) : [];
		const index = rows.indexOf( row );
		const direction = this.getAttribute( 'data-direction' );
		const targetIndex = 'up' === direction ? index - 1 : index + 1;

		if ( ! columnsField || index < 0 || targetIndex < 0 || targetIndex >= rows.length ) {
			return;
		}

		const target = rows[ targetIndex ];
		const tbody = row.parentNode;
		if ( 'up' === direction ) {
			tbody.insertBefore( row, target );
		} else {
			tbody.insertBefore( row, target.nextSibling );
		}

		getRows( columnsField ).forEach( function ( orderedRow, orderedIndex ) {
			const number = orderedRow.querySelector( '.acf-row-number' );
			if ( number ) {
				number.textContent = String( orderedIndex + 1 );
			}
		} );

		const repeaterInput = columnsField.querySelector( '.acf-repeater-hidden-input' );
		if ( repeaterInput ) {
			$( repeaterInput ).trigger( 'change' );
		}

		acf.doAction( 'sortstop', $( row ), $( target ) );
		updateRoot( root );
	} );
}( jQuery, acf ) );
