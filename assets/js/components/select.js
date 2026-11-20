/**
 * Alpine state for theme select controls.
 *
 * Registers before Alpine starts so components can use the texturize-safe
 * lttSelect component name.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';


	/** Enhance a native multiple select. Consumers own filtering and transport. */
	window.lttSelectMultiple = function ( control, settings ) {
		if ( control.lttMultiple ) {
			control.lttMultiple.sync();
			return control.lttMultiple;
		}

		const config = settings || {};
		const id = config.id || control.dataset.selectId;
		const labelId = id + '-label';
		const currentId = id + '-current';
		const menuId = id + '-options';
		const trigger = document.createElement( 'button' );
		const current = document.createElement( 'span' );
		const icon = document.createElement( 'span' );
		const menu = document.createElement( 'div' );
		let nativeSelect = null;

		trigger.type = 'button';
		trigger.className = 'ltt-select__trigger';
		trigger.setAttribute( 'aria-expanded', 'false' );
		trigger.setAttribute( 'aria-controls', menuId );
		trigger.setAttribute( 'aria-labelledby', labelId + ' ' + currentId );
		current.id = currentId;
		icon.className = 'ltt-select__trigger-icon';
		icon.setAttribute( 'aria-hidden', 'true' );
		const nativeIcon = control.querySelector( '.ltt-select__native-icon img' );
		if ( nativeIcon ) {
			icon.appendChild( nativeIcon.cloneNode( true ) );
		}
		trigger.append( current, icon );
		menu.id = menuId;
		menu.className = 'ltt-select__menu';
		menu.hidden = true;
		menu.setAttribute( 'role', 'group' );
		menu.setAttribute( 'aria-labelledby', labelId );
		control.append( trigger, menu );
		control.classList.add( 'ltt-select--multiple' );

		const label = config.label || function ( option ) { return option.text; };
		const close = function ( restoreFocus ) {
			const wasOpen = ! menu.hidden;
			menu.hidden = true;
			trigger.setAttribute( 'aria-expanded', 'false' );
			if ( restoreFocus && wasOpen ) {
				trigger.focus( { preventScroll: true } );
			}
		};
		const sync = function () {
			const select = control.querySelector( 'select[multiple]' );
			if ( ! select ) {
				return;
			}
			select.id = id;
			select.setAttribute( 'aria-labelledby', labelId );
			select.setAttribute( 'aria-hidden', 'true' );
			select.tabIndex = -1;
			if ( nativeSelect !== select ) {
				nativeSelect = select;
				select.addEventListener( 'change', sync );
			}
			const options = Array.from( select.options ).filter( function ( option ) { return option.value !== ''; } );
			const selected = options.filter( function ( option ) { return option.selected; } );
			const placeholder = Array.from( select.options ).find( function ( option ) { return option.value === ''; } );
			current.textContent = selected.length ? ( control.dataset.selectCount || '%d Selected' ).replace( '%d', selected.length ) : ( placeholder ? label( placeholder ) : '' );
			trigger.disabled = ! options.some( function ( option ) { return ! option.disabled || option.selected; } );
			const focused = menu.contains( document.activeElement ) ? document.activeElement : null;
			const scrollTop = menu.scrollTop;
			const rows = new Map( Array.from( menu.children ).map( function ( row ) { return [ row.querySelector( 'input' ).value, row ]; } ) );
			const children = options.map( function ( option ) {
				let row = rows.get( option.value );
				if ( ! row ) {
					row = document.createElement( 'label' );
					row.className = 'ltt-select__option ltt-select__choice';
					const input = document.createElement( 'input' );
					input.type = 'checkbox';
					input.className = 'ltt-select__checkbox';
					input.value = option.value;
					row.append( input, document.createElement( 'span' ) );
				}
				const input = row.querySelector( 'input' );
				input.checked = option.selected;
				input.disabled = option.disabled && ! option.selected;
				row.querySelector( 'span' ).textContent = label( option );
				return row;
			} );
			// Reuse the checkbox nodes so updates can retain keyboard focus.
			if ( children.length !== menu.children.length || children.some( function ( row, index ) { return row !== menu.children[ index ]; } ) ) {
				menu.replaceChildren( ...children );
				if ( focused && menu.contains( focused ) ) {
					focused.focus( { preventScroll: true } );
				} else if ( focused ) {
					trigger.focus( { preventScroll: true } );
				}
			}
			menu.scrollTop = scrollTop;
			control.classList.add( 'is-enhanced' );
		};

		trigger.addEventListener( 'click', function () {
			const willOpen = menu.hidden;
			document.dispatchEvent( new CustomEvent( 'ltt-select-open', { detail: control } ) );
			menu.hidden = ! willOpen;
			trigger.setAttribute( 'aria-expanded', String( willOpen ) );
		} );
		menu.addEventListener( 'change', function ( event ) {
			if ( ! event.target.matches( '.ltt-select__checkbox' ) ) {
				return;
			}
			Array.from( nativeSelect.options ).forEach( function ( option ) {
				if ( option.value === event.target.value ) {
					option.selected = event.target.checked;
				} else if ( ! option.value ) {
					option.selected = false;
				}
			} );
			sync();
			nativeSelect.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
		control.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && ! menu.hidden ) {
				event.preventDefault();
				close( true );
			}
		} );
		document.addEventListener( 'ltt-select-open', function ( event ) {
			if ( event.detail !== control ) { close( false ); }
		} );
		document.addEventListener( 'pointerdown', function ( event ) {
			if ( ! control.contains( event.target ) ) { close( false ); }
		} );
		document.addEventListener( 'focusin', function ( event ) {
			if ( ! control.contains( event.target ) ) { close( false ); }
		} );
		control.addEventListener( 'focusout', function ( event ) {
			if ( event.relatedTarget && ! control.contains( event.relatedTarget ) ) { close( false ); }
		} );
		control.lttMultiple = { sync: sync, close: close };
		sync();
		return control.lttMultiple;
	};

	function initMultipleSelects( root ) {
		root.querySelectorAll( '[data-ltt-select-multiple]' ).forEach( function ( control ) {
			window.lttSelectMultiple( control );
		} );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { initMultipleSelects( document ); } );
	} else {
		initMultipleSelects( document );
	}
	// ACF replaces previews without a full document load.
	if ( window.acf ) {
		window.acf.addAction( 'render_block_preview', function ( block ) {
			initMultipleSelects( block[ 0 ] || block );
		} );
	}

	document.addEventListener( 'alpine:init', function () {
		window.Alpine.data( 'lttSelect', function () {
			return {
				open: false,
				selectedValue: '',
				selectedLabel: '',

				init: function () {
					const select = this.$refs.nativeSelect;

					this.syncSelected();
					this.$el.classList.add( 'is-enhanced' );
					select.setAttribute( 'aria-hidden', 'true' );
					select.tabIndex = -1;
					select.addEventListener( 'change', this.syncSelected.bind( this ) );
				},

				syncSelected: function () {
					const select = this.$refs.nativeSelect;
					const option = select.options[ select.selectedIndex ];

					this.selectedValue = select.value;
					this.selectedLabel = option ? option.text : '';
				},

				close: function () {
					this.open = false;
				},

				toggle: function () {
					this.open = ! this.open;
				},

				choose: function ( event ) {
					const select = this.$refs.nativeSelect;

					select.value = event.currentTarget.dataset.lttSelectValue;
					this.syncSelected();
					this.close();
					select.dispatchEvent( new Event( 'change', { bubbles: true } ) );

					this.$nextTick( function () {
						this.$refs.trigger.focus();
					}.bind( this ) );
				},

				isSelected: function ( value ) {
					return this.selectedValue === value;
				},
			};
		} );
	} );
}() );
