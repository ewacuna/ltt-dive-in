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
