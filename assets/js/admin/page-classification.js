( function ( wp ) {
	'use strict';

	const settings = window.ltt_dive_in_page_classification || {};
	const taxonomies = settings.taxonomies || [];

	wp.domReady( function () {
		const editor = wp.data.dispatch( 'core/editor' );

		if ( ! editor || 'function' !== typeof editor.removeEditorPanel ) {
			return;
		}

		taxonomies.forEach( function ( taxonomy ) {
			editor.removeEditorPanel( 'taxonomy-panel-' + taxonomy );
		} );
	} );
}( window.wp ) );
