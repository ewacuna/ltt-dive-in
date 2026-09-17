( function ( $, acf ) {
	'use strict';
	const fieldKey = 'field_ltt_dive_in_activities_items';
	const lockKey = 'ltt-dive-in-activities-count';
	const sync = function () {
		if ( ! window.wp || ! window.wp.data ) { return; }
		const editor = window.wp.data.dispatch( 'core/editor' );
		const invalid = Array.from( document.querySelectorAll( '[data-key="' + fieldKey + '"]' ) ).some( function ( field ) { return 4 !== field.querySelectorAll( '.values-list .acf-rel-item' ).length; } );
		if ( invalid ) { editor.lockPostSaving( lockKey ); return; }
		editor.unlockPostSaving( lockKey );
	};
	const update = function ( $field ) {
		const count = $field.find( '.values-list .acf-rel-item' ).length;
		let $error = $field.find( '.ltt-dive-in-activities-count-error' );
		if ( 4 !== count ) {
			if ( ! $error.length ) { $error = $( '<div class="acf-notice -error ltt-dive-in-activities-count-error" role="alert"><p></p></div>' ); $field.find( '.acf-input' ).append( $error ); }
			$error.find( 'p' ).text( 'Select exactly four activities. Currently selected: ' + count + '.' );
		} else { $error.remove(); }
		sync();
	};
	acf.addAction( 'ready append', function ( $el ) { $el.find( '[data-key="' + fieldKey + '"]' ).each( function () { update( $( this ) ); } ); } );
	$( document ).on( 'change', '[data-key="' + fieldKey + '"] input', function () { update( $( this ).closest( '[data-key]' ) ); } );
	acf.addAction( 'remove', function () { window.setTimeout( sync, 0 ); } );
}( jQuery, acf ) );
