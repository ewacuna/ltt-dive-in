( function () {
	'use strict';

	const closeDelay = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 0 : 220;

	document.querySelectorAll( '[data-ltt-lightbox]' ).forEach( function ( dialog ) {
		const source = dialog.querySelector( '[data-ltt-lightbox-data]' );

		if ( ! source || typeof dialog.showModal !== 'function' ) {
			return;
		}

		let items;

		try {
			items = JSON.parse( source.textContent );
		} catch ( error ) {
			return;
		}

		const image = dialog.querySelector( '[data-ltt-lightbox-image]' );
		const title = dialog.querySelector( '[data-ltt-lightbox-title]' );
		const description = dialog.querySelector( '[data-ltt-lightbox-description]' );
		const caption = dialog.querySelector( '.ltt-lightbox__caption' );
		const location = dialog.querySelector( '[data-ltt-lightbox-location]' );
		const count = dialog.querySelector( '[data-ltt-lightbox-count]' );
		const close = dialog.querySelector( '[data-ltt-lightbox-close]' );
		let index = 0;
		let trigger;

		if ( ! Array.isArray( items ) || ! image || ! close ) {
			return;
		}

		const show = function ( requested ) {
			index = ( requested + items.length ) % items.length;

			const item = items[ index ];

			image.src = item.full;
			image.alt = item.alt;
			title.textContent = item.title || '';
			description.textContent = item.description || '';

			if ( caption ) {
				caption.hidden = ! item.description;
			}

			if ( location ) {
				location.hidden = ! item.title;
			}

			count.textContent = ( index + 1 ) + ' / ' + items.length;
		};

		const closeDialog = function () {
			if ( ! dialog.open ) {
				return;
			}

			dialog.classList.add( 'is-closing' );
			window.setTimeout( function () {
				dialog.close();
			}, closeDelay );
		};

		const openDialog = function ( button ) {
			trigger = button;
			show( Number.parseInt( button.dataset.imageIndex, 10 ) || 0 );
			dialog.showModal();
			close.focus();
		};

		document.querySelectorAll( '[data-ltt-lightbox-trigger][aria-controls="' + dialog.id + '"]' ).forEach( function ( button ) {
			button.dataset.lttLightboxBound = 'true';
			button.addEventListener( 'click', function () {
				openDialog( button );
			} );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! ( event.target instanceof Element ) ) {
				return;
			}

			const button = event.target.closest( '[data-ltt-lightbox-trigger][aria-controls="' + dialog.id + '"]' );

			if ( ! button || 'true' === button.dataset.lttLightboxBound ) {
				return;
			}

			button.dataset.lttLightboxBound = 'true';
			openDialog( button );
		} );

		dialog.addEventListener( 'ltt-lightbox:items-added', function ( event ) {
			const additions = event.detail && Array.isArray( event.detail.items ) ? event.detail.items : [];

			if ( ! additions.length ) {
				return;
			}

			items = items.concat( additions );
			source.textContent = JSON.stringify( items );
		} );

		dialog.querySelectorAll( '[data-ltt-lightbox-previous]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				show( index - 1 );
			} );
		} );

		dialog.querySelectorAll( '[data-ltt-lightbox-next]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				show( index + 1 );
			} );
		} );

		close.addEventListener( 'click', closeDialog );
		dialog.addEventListener( 'cancel', function ( event ) {
			event.preventDefault();
			closeDialog();
		} );
		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				closeDialog();
			}
		} );
		dialog.addEventListener( 'keydown', function ( event ) {
			if ( 'ArrowLeft' === event.key ) {
				event.preventDefault();
				show( index - 1 );
			}

			if ( 'ArrowRight' === event.key ) {
				event.preventDefault();
				show( index + 1 );
			}
		} );
		dialog.addEventListener( 'close', function () {
			dialog.classList.remove( 'is-closing' );
			image.removeAttribute( 'src' );

			if ( trigger && document.contains( trigger ) ) {
				trigger.focus();
			}
		} );
	} );
} )();
