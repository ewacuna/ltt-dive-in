/**
 * Keep the 404 search clear control aligned independently of native mobile search UI.
 */
( function () {
	'use strict';

	const input = document.querySelector( '#ltt-error-search' );
	const clearButton = document.querySelector( '[data-error-search-clear]' );
	const searchBar = input ? input.closest( '.ltt-search-bar--error' ) : null;

	if ( ! input || ! clearButton || ! searchBar ) {
		return;
	}

	const updateClearButton = function () {
		clearButton.hidden = '' === input.value;
	};

	input.addEventListener( 'input', updateClearButton );
	input.addEventListener( 'change', updateClearButton );
	clearButton.addEventListener( 'click', function () {
		input.value = '';
		input.focus();
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	} );

	updateClearButton();
	searchBar.classList.add( 'has-custom-clear' );
}() );
