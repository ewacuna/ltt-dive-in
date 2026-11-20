/**
 * Site Alert settings: reset both colors to the type's defaults when the type changes.
 */
( function ( $, acf ) {
	'use strict';

	if ( ! acf ) {
		return;
	}

	const defaults = window.ltt_dive_in_site_alert_defaults || {};
	const typeKey = 'field_ltt_dive_in_site_alert_type';
	const colorKeys = {
		background: 'field_ltt_dive_in_site_alert_background_hex',
		text: 'field_ltt_dive_in_site_alert_text_hex'
	};

	const setColor = function ( key, color ) {
		const field = acf.getField( key );
		const $input = field && field.$el ? field.$el.find( 'input[type="text"]' ).first() : $();

		if ( ! $input.length || ! color ) {
			return;
		}

		if ( $input.hasClass( 'wp-color-picker' ) ) {
			$input.wpColorPicker( 'color', color );
		}

		$input.val( color ).trigger( 'change' );
		field.$el.find( 'input[type="hidden"]' ).first().val( color );
	};

	acf.addAction( 'ready_field/key=' + typeKey, function ( field ) {
		field.$el.on( 'change', 'input[type="radio"]', function () {
			const typeDefaults = defaults[ this.value ];

			if ( ! typeDefaults ) {
				return;
			}

			setColor( colorKeys.background, typeDefaults.background );
			setColor( colorKeys.text, typeDefaults.text );
		} );
	} );
}( jQuery, window.acf ) );
