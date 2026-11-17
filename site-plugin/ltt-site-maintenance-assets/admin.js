/** Site Maintenance settings and native WordPress Media Library controls. */
( function () {
	'use strict';

	const viewer = document.getElementById( 'ltt-site-maintenance-camera-viewer' );
	if ( ! viewer ) {
		return;
	}

	const rows = document.querySelectorAll( '[data-maintenance-viewer]' );
	const sync = function () {
		rows.forEach( function ( row ) {
			const hidden = row.dataset.maintenanceViewer !== viewer.value;
			row.hidden = hidden;
			row.classList.toggle( 'hidden', hidden );
			row.querySelectorAll( 'input, button' ).forEach( function ( control ) {
				control.disabled = hidden;
			} );
		} );
	};
	viewer.addEventListener( 'change', sync );
	sync();

	const input = document.getElementById( 'ltt-site-maintenance-camera-image' );
	const preview = document.getElementById( 'ltt-site-maintenance-camera-preview' );
	const choose = document.querySelector( '[data-maintenance-select-image]' );
	const remove = document.querySelector( '[data-maintenance-remove-image]' );
	const labels = window.lttSiteMaintenanceAdmin || {};
	let frame;

	if ( ! input || ! preview || ! choose || ! remove || ! window.wp || ! window.wp.media ) {
		return;
	}

	choose.addEventListener( 'click', function () {
		if ( ! frame ) {
			frame = window.wp.media( {
				title: labels.title,
				button: { text: labels.choose },
				library: { type: [ 'image/jpeg', 'image/png', 'image/webp' ] },
				multiple: false,
			} );
			frame.on( 'open', function () {
				const selection = frame.state().get( 'selection' );
				selection.reset();
				if ( Number( input.value ) > 0 ) {
					selection.add( window.wp.media.attachment( input.value ) );
				}
			} );
			frame.on( 'select', function () {
				const attachment = frame.state().get( 'selection' ).first().toJSON();
				const size = attachment.sizes && ( attachment.sizes.thumbnail || attachment.sizes.medium );
				const image = document.createElement( 'img' );
				image.src = size ? size.url : attachment.url;
				image.alt = attachment.alt || '';
				image.width = 150;
				if ( size && size.width && size.height ) {
					image.height = Math.round( 150 * size.height / size.width );
				}
				preview.replaceChildren( image );
				input.value = attachment.id;
				input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				remove.hidden = false;
				remove.classList.remove( 'hidden' );
			} );
			frame.on( 'close', function () {
				choose.focus();
			} );
		}
		frame.open();
	} );

	remove.addEventListener( 'click', function () {
		input.value = '0';
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		preview.replaceChildren();
		remove.hidden = true;
		remove.classList.add( 'hidden' );
		choose.focus();
	} );
}() );
