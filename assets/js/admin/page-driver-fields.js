( function ( $, acf ) {
	'use strict';

	const taxonomyKey = 'field_ltt_dive_in_page_driver_taxonomy';
	const termsKey = 'field_ltt_dive_in_page_driver_toggle_terms';
	const toggleKey = 'field_ltt_dive_in_page_driver_enable_toggles';
	const layoutKey = 'field_ltt_dive_in_page_driver_layout';
	const tilesKey = 'field_ltt_dive_in_page_driver_tiles';
	const editorSettings = window.ltt_dive_in_page_driver_editor || {};
	const layoutCounts = editorSettings.tileLimits || {};
	const savingLockKey = 'ltt-dive-in-up-driver-tile-count';

	const syncSavingLock = function () {
		if ( ! window.wp || ! window.wp.data ) {
			return;
		}

		const editor = window.wp.data.dispatch( 'core/editor' );

		if ( ! editor.lockPostSaving || ! editor.unlockPostSaving ) {
			return;
		}

		const hasInvalidDriver = Array.from( document.querySelectorAll( '[data-key="' + layoutKey + '"]' ) ).some( function ( layoutField ) {
			const layout = layoutField.querySelector( 'select' ).value;
			const limits = layoutCounts[ layout ];
			const fields = layoutField.closest( '.acf-fields' );
			const tilesField = fields && fields.querySelector( '[data-key="' + tilesKey + '"]' );
			const selectedCount = tilesField ? tilesField.querySelectorAll( '.values-list .acf-rel-item' ).length : 0;

			return limits && ( selectedCount < limits.min || selectedCount > limits.max );
		} );

		const hasInvalidFilterCoverage = document.querySelector( '[data-key="' + termsKey + '"][data-ltt-dive-in-filter-coverage-invalid="true"]' );
		const hasInvalidFilterCount = document.querySelector( '[data-key="' + termsKey + '"][data-ltt-dive-in-filter-count-invalid="true"]' );

		if ( hasInvalidDriver || hasInvalidFilterCoverage || hasInvalidFilterCount ) {
			editor.lockPostSaving( savingLockKey );
			if ( editor.lockPostAutosaving ) {
				editor.lockPostAutosaving( savingLockKey );
			}
			return;
		}

		editor.unlockPostSaving( savingLockKey );
		if ( editor.unlockPostAutosaving ) {
			editor.unlockPostAutosaving( savingLockKey );
		}
	};

	const updateTileLimits = function ( $layoutField ) {
		const $fields = $layoutField.closest( '.acf-fields' );
		const $tilesField = $fields.find( '[data-key="' + tilesKey + '"]' );
		const layout = $layoutField.find( 'select' ).val();
		const limits = layoutCounts[ layout ];
		const relationshipField = acf.getField( $tilesField );

		if ( ! $tilesField.length || ! limits || ! relationshipField ) {
			return;
		}

		relationshipField.set( 'min', limits.min );
		relationshipField.set( 'max', limits.max );

		const selectedCount = $tilesField.find( '.values-list .acf-rel-item' ).length;
		const isValid = selectedCount >= limits.min && selectedCount <= limits.max;
		let message = ( editorSettings.selectPages || 'Select ' ) + limits.label + '.';

		if ( selectedCount && ! isValid ) {
			message += ' ' + ( editorSettings.currentlySelected || 'Currently selected: ' ) + selectedCount + '.';
		}

		let $notice = $tilesField.find( '.ltt-dive-in-page-driver-count-notice' );

		if ( ! $notice.length ) {
			$notice = $( '<p class="description ltt-dive-in-page-driver-count-notice"></p>' );
			$tilesField.find( '.acf-input' ).prepend( $notice );
		}

		$notice.text( message );

		let $error = $tilesField.find( '.ltt-dive-in-page-driver-count-error' );

		if ( ! isValid ) {
			if ( ! $error.length ) {
				$error = $( '<div class="acf-notice -error ltt-dive-in-page-driver-count-error" role="alert"><p></p></div>' );
				$tilesField.find( '.acf-input' ).append( $error );
			}

			$error.find( 'p' ).text( message );
		} else {
			$error.remove();
		}

		syncSavingLock();
	};

	const getInputName = function ( $termsField ) {
		return $termsField.find( 'input[type="checkbox"]' ).first().attr( 'name' ) || $termsField.find( 'input[type="hidden"]' ).first().attr( 'name' );
	};

	const clearAndHideTerms = function ( $termsField, name ) {
		const inputName = name.replace( /\[\]$/, '' );

		$termsField.find( '.acf-input' ).html( '<input type="hidden" name="' + inputName + '" value="">' );
		$termsField.hide();
	};

	const clearFilterCoverage = function ( $termsField ) {
		$termsField.data( 'lttDiveInCoverageRequest', ( $termsField.data( 'lttDiveInCoverageRequest' ) || 0 ) + 1 );
		$termsField.removeAttr( 'data-ltt-dive-in-filter-coverage-invalid' );
		$termsField.find( '.ltt-dive-in-page-driver-filter-coverage-error' ).remove();
		syncSavingLock();
	};

	const clearFilterValueCount = function ( $termsField ) {
		$termsField.removeAttr( 'data-ltt-dive-in-filter-count-invalid' );
		$termsField.find( '.ltt-dive-in-page-driver-filter-count-error' ).remove();
		syncSavingLock();
	};

	const updateFilterValueCount = function ( $termsField ) {
		const $fields = $termsField.closest( '.acf-fields' );
		const filtersEnabled = $fields.find( '[data-key="' + toggleKey + '"] input[type="checkbox"]' ).prop( 'checked' );
		const layout = $fields.find( '[data-key="' + layoutKey + '"] select' ).val();
		const taxonomy = $fields.find( '[data-key="' + taxonomyKey + '"] select' ).val();
		const count = $termsField.find( 'input[type="checkbox"]:checked' ).length;

		if ( ! filtersEnabled || 'monthly' === layout || ! taxonomy ) {
			clearFilterValueCount( $termsField );
			return;
		}

		const isValid = count >= 2 && count <= 5;
		let $error = $termsField.find( '.ltt-dive-in-page-driver-filter-count-error' );

		$termsField.attr( 'data-ltt-dive-in-filter-count-invalid', isValid ? 'false' : 'true' );

		if ( ! isValid ) {
			if ( ! $error.length ) {
				$error = $( '<div class="acf-notice -error ltt-dive-in-page-driver-filter-count-error" role="alert"><p></p></div>' );
				$termsField.find( '.acf-input' ).append( $error );
			}

			$error.find( 'p' ).text( editorSettings.filterValueCount || 'Choose between two and five filter values.' );
		} else {
			$error.remove();
		}

		syncSavingLock();
	};

	const updateFilterCoverage = function ( $termsField ) {
		const $fields = $termsField.closest( '.acf-fields' );
		const filtersEnabled = $fields.find( '[data-key="' + toggleKey + '"] input[type="checkbox"]' ).prop( 'checked' );
		const taxonomy = $fields.find( '[data-key="' + taxonomyKey + '"] select' ).val();
		const terms = $termsField.find( 'input[type="checkbox"]:checked' ).map( function () { return this.value; } ).get();
		const tiles = $fields.find( '[data-key="' + tilesKey + '"] .values-list .acf-rel-item' ).map( function () { return $( this ).data( 'id' ); } ).get();

		if ( ! filtersEnabled || ! taxonomy || ! terms.length ) {
			clearFilterCoverage( $termsField );
			return;
		}

		const requestId = ( $termsField.data( 'lttDiveInCoverageRequest' ) || 0 ) + 1;

		$termsField.data( 'lttDiveInCoverageRequest', requestId );

		$.post( ltt_dive_in_page_driver_editor.ajaxUrl, {
			action: 'ltt_dive_in_page_driver_filter_coverage',
			nonce: ltt_dive_in_page_driver_editor.nonce,
			taxonomy: taxonomy,
			terms: terms,
			tiles: tiles
		} ).done( function ( response ) {
			if ( ! response.success || requestId !== $termsField.data( 'lttDiveInCoverageRequest' ) ) {
				return;
			}

			const uncoveredTerms = ( response.data && response.data.uncoveredTerms ) || {};
			const uncoveredNames = Object.values( uncoveredTerms );
			const isValid = ! uncoveredNames.length;
			let $error = $termsField.find( '.ltt-dive-in-page-driver-filter-coverage-error' );

			$termsField.attr( 'data-ltt-dive-in-filter-coverage-invalid', isValid ? 'false' : 'true' );

			if ( ! isValid ) {
				if ( ! $error.length ) {
					$error = $( '<div class="acf-notice -error ltt-dive-in-page-driver-filter-coverage-error" role="alert"><p></p></div>' );
					$termsField.find( '.acf-input' ).append( $error );
				}

				$error.find( 'p' ).text( ( editorSettings.filtersWithoutPages || 'These filter values are not assigned to any selected hub page: ' ) + uncoveredNames.join( ', ' ) + '.' );
			} else {
				$error.remove();
			}

			syncSavingLock();
		} );
	};

	const updateTerms = function ( $taxonomyField, resetSelection ) {
		const $fields = $taxonomyField.closest( '.acf-fields' );
		const $termsField = $fields.find( '[data-key="' + termsKey + '"]' );
		const taxonomy = $taxonomyField.find( 'select' ).val();
		const $checkboxes = $termsField.find( 'input[type="checkbox"]' );
		const selected = resetSelection ? [] : $checkboxes.filter( ':checked' ).map( function () { return this.value; } ).get();
		const name = getInputName( $termsField );

		if ( ! $termsField.length || ! name ) {
			return;
		}

		if ( ! taxonomy ) {
			clearAndHideTerms( $termsField, name );
			clearFilterCoverage( $termsField );
			clearFilterValueCount( $termsField );
			$taxonomyField.removeData( 'lttDiveInTaxonomy' );
			return;
		}

		if ( resetSelection ) {
			const inputName = name.replace( /\[\]$/, '' );

			$termsField.find( '.acf-input' ).html( '<input type="hidden" name="' + inputName + '" value=""><p class="description">Loading terms…</p>' );
			clearFilterCoverage( $termsField );
		}

		const requestId = ( $taxonomyField.data( 'lttDiveInTermsRequest' ) || 0 ) + 1;

		$termsField.show().attr( 'aria-busy', 'true' );
		$taxonomyField.data( 'lttDiveInTaxonomy', taxonomy );
		$taxonomyField.data( 'lttDiveInTermsRequest', requestId );

		$.post( ltt_dive_in_page_driver_editor.ajaxUrl, {
			action: 'ltt_dive_in_page_driver_terms',
			nonce: ltt_dive_in_page_driver_editor.nonce,
			taxonomy: taxonomy
		} ).done( function ( response ) {
			if ( ! response.success || requestId !== $taxonomyField.data( 'lttDiveInTermsRequest' ) ) {
				return;
			}

			const inputName = name.replace( /\[\]$/, '' );
			const terms = response.data || {};
			let html = '<input type="hidden" name="' + inputName + '" value="">';

			if ( Object.keys( terms ).length ) {
				html += '<ul class="acf-checkbox-list acf-bl">';
				$.each( terms, function ( id, label ) {
					const checked = selected.includes( String( id ) ) ? ' checked' : '';
					html += '<li><label><input type="checkbox" name="' + inputName + '[]" value="' + id + '"' + checked + '> ' + $( '<span>' ).text( label ).html() + '</label></li>';
				} );
				html += '</ul>';
			} else {
				html += '<p class="description">No terms are available for this taxonomy.</p>';
			}

			$termsField.find( '.acf-input' ).html( html );
			updateFilterCoverage( $termsField );
			updateFilterValueCount( $termsField );
		} ).always( function () {
			if ( requestId === $taxonomyField.data( 'lttDiveInTermsRequest' ) ) {
				$termsField.removeAttr( 'aria-busy' );
			}
		} );
	};

	acf.addAction( 'ready append', function ( $el ) {
		$el.find( '[data-key="' + taxonomyKey + '"]' ).each( function () {
			updateTerms( $( this ), false );
		} );

		$el.find( '[data-key="' + layoutKey + '"]' ).each( function () {
			updateTileLimits( $( this ) );
		} );

		$el.find( '[data-key="' + termsKey + '"]' ).each( function () {
			updateFilterCoverage( $( this ) );
			updateFilterValueCount( $( this ) );
		} );
	} );

	acf.addAction( 'remove', function () {
		window.setTimeout( syncSavingLock, 0 );
	} );

	$( document ).on( 'change', '[data-key="' + taxonomyKey + '"] select', function () {
		const $taxonomyField = $( this ).closest( '[data-key]' );
		const previousTaxonomy = $taxonomyField.data( 'lttDiveInTaxonomy' );

		updateTerms( $taxonomyField, previousTaxonomy && previousTaxonomy !== this.value );
	} );

	$( document ).on( 'change', '[data-key="' + layoutKey + '"] select', function () {
		const $layoutField = $( this ).closest( '[data-key]' );

		updateTileLimits( $layoutField );
		updateFilterValueCount( $layoutField.closest( '.acf-fields' ).find( '[data-key="' + termsKey + '"]' ) );
	} );

	$( document ).on( 'change', '[data-key="' + tilesKey + '"] input', function () {
		const $tilesField = $( this ).closest( '[data-key]' );
		const $layoutField = $tilesField.closest( '.acf-fields' ).find( '[data-key="' + layoutKey + '"]' );

		updateTileLimits( $layoutField );
		updateFilterCoverage( $tilesField.closest( '.acf-fields' ).find( '[data-key="' + termsKey + '"]' ) );
	} );

	$( document ).on( 'change', '[data-key="' + termsKey + '"] input[type="checkbox"]', function () {
		const $termsField = $( this ).closest( '[data-key]' );

		updateFilterCoverage( $termsField );
		updateFilterValueCount( $termsField );
	} );

	$( document ).on( 'change', '[data-key="' + toggleKey + '"] input[type="checkbox"]', function () {
		const $termsField = $( this ).closest( '.acf-fields' ).find( '[data-key="' + termsKey + '"]' );

		updateFilterCoverage( $termsField );
		updateFilterValueCount( $termsField );
	} );
}( jQuery, acf ) );
