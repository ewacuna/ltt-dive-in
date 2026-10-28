( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.data ) {
		return;
	}

	const store = wp.data.dispatch( 'core/editor' );
	const legacyStore = wp.data.dispatch( 'core/edit-post' );
	if ( store && store.removeEditorPanel ) {
		store.removeEditorPanel( 'featured-image' );
	} else if ( legacyStore && legacyStore.removeEditorPanel ) {
		legacyStore.removeEditorPanel( 'featured-image' );
	}
}( window.wp ) );
