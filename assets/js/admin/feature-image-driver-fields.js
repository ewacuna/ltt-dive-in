( function ( $, acf ) {
	'use strict';

	const variantKey = 'field_ltt_dive_in_feature_image_driver_variant';
	const itemsKey = 'field_ltt_dive_in_feature_image_driver_items';
	const enabledKey = 'field_ltt_dive_in_feature_image_driver_item_enabled';
	const activeRequiredKeys = [
		'field_ltt_dive_in_feature_image_driver_item_image',
		'field_ltt_dive_in_feature_image_driver_item_title'
	];
	const editorSettings = window.ltt_dive_in_feature_image_driver_editor || {};
	const itemLimits = editorSettings.itemLimits || {};
	const savingLockKey = 'ltt-dive-in-feature-image-driver-item-count';

	const getRows = function ( $itemsField ) {
		return $itemsField.find( '.acf-row:not(.acf-clone)' ).filter( function () {
			return $( this ).closest( '[data-key="' + itemsKey + '"]' )[ 0 ] === $itemsField[ 0 ];
		} );
	};

	const getActiveCount = function ( $itemsField ) {
		return getRows( $itemsField ).filter( function () {
			return $( this ).find( '[data-key="' + enabledKey + '"] input[type="checkbox"]' ).first().prop( 'checked' );
		} ).length;
	};

	const updateActiveItemRequirements = function ( $itemsField ) {
		getRows( $itemsField ).each( function () {
			const $row = $( this );
			const isActive = $row.find( '[data-key="' + enabledKey + '"] input[type="checkbox"]' ).first().prop( 'checked' );

			activeRequiredKeys.forEach( function ( fieldKey ) {
				const $field = $row.find( '[data-key="' + fieldKey + '"]' );
				const $label = $field.children( '.acf-label' ).children( 'label' );

				$field.toggleClass( 'required', isActive );
				$label.find( '.acf-required.ltt-dive-in-active-required' ).remove();

				if ( isActive ) {
					$label.append( '<span class="acf-required ltt-dive-in-active-required">*</span>' );
				}
			} );
		} );
	};

	const addRemoveItemControls = function ( $itemsField ) {
		const $rows = getRows( $itemsField );
		const canRemove = $rows.length > 1;

		$rows.each( function () {
			const $row = $( this );
			let $button = $row.find( '.ltt-dive-in-feature-image-driver-remove-item' );

			if ( ! $button.length ) {
				$button = $( '<button class="button button-secondary ltt-dive-in-feature-image-driver-remove-item" type="button"></button>' );
				$row.children( '.acf-fields' ).append( $button );
			}

			$button.text( editorSettings.removeItem || 'Remove feature item' );
			$button.prop( 'disabled', ! canRemove );
			$button.attr( 'title', canRemove ? '' : ( editorSettings.minimumItem || 'At least one feature item is required.' ) );
		} );
	};

	const syncSavingLock = function () {
		if ( ! window.wp || ! window.wp.data ) {
			return;
		}

		const editor = window.wp.data.dispatch( 'core/editor' );

		if ( ! editor.lockPostSaving || ! editor.unlockPostSaving ) {
			return;
		}

		const hasInvalidBlock = document.querySelector( '[data-key="' + itemsKey + '"][data-ltt-dive-in-item-count-invalid="true"]' );

		if ( hasInvalidBlock ) {
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

	const updateItemLimits = function ( $itemsField ) {
		if ( ! $itemsField.length ) {
			return;
		}

		updateActiveItemRequirements( $itemsField );
		addRemoveItemControls( $itemsField );

		const $fields = $itemsField.closest( '.acf-fields' );
		const variant = $fields.find( '[data-key="' + variantKey + '"] select' ).first().val();
		const limits = itemLimits[ variant ];

		if ( ! limits ) {
			return;
		}

		const activeCount = getActiveCount( $itemsField );
		const isValid = activeCount >= limits.min && activeCount <= limits.max;
		const instruction = ( editorSettings.requirementPrefix || 'This variant requires ' ) + limits.label + '.';
		const message = instruction + ' ' + ( editorSettings.currentlyActive || 'Currently active: ' ) + activeCount + '.';
		let $notice = $itemsField.find( '> .acf-input > .ltt-dive-in-feature-image-driver-count-notice' );
		let $error = $itemsField.find( '> .acf-input > .ltt-dive-in-feature-image-driver-count-error' );

		$itemsField.attr( 'data-ltt-dive-in-item-count-invalid', isValid ? 'false' : 'true' );

		if ( ! $notice.length ) {
			$notice = $( '<p class="description ltt-dive-in-feature-image-driver-count-notice"></p>' );
			$itemsField.children( '.acf-input' ).prepend( $notice );
		}

		$notice.text( message );

		if ( ! isValid ) {
			if ( ! $error.length ) {
				$error = $( '<div class="acf-notice -error ltt-dive-in-feature-image-driver-count-error" role="alert"><p></p></div>' );
				$itemsField.children( '.acf-input' ).append( $error );
			}

			$error.find( 'p' ).text( message );
		} else {
			$error.remove();
		}

		syncSavingLock();
	};

	const updateWithin = function ( $el ) {
		// ACF's ready action does not pass a context; append does.
		const $context = $el ? $( $el ) : $( document );
		const $itemsFields = $context.find( '[data-key="' + itemsKey + '"]' )
			.addBack( '[data-key="' + itemsKey + '"]' )
			.add( $context.closest( '[data-key="' + itemsKey + '"]' ) );

		$itemsFields.each( function () {
			updateItemLimits( $( this ) );
		} );
	};

	acf.addAction( 'ready', updateWithin );
	acf.addAction( 'append', updateWithin );
	acf.addAction( 'remove', function ( $el ) {
		const $itemsField = $el.closest( '[data-key="' + itemsKey + '"]' );
		window.setTimeout( function () {
			updateItemLimits( $itemsField );
		}, 0 );
	} );
	acf.addAction( 'sortstop', function ( $el ) {
		updateItemLimits( $el.closest( '[data-key="' + itemsKey + '"]' ) );
	} );

	$( document ).on( 'change', '[data-key="' + variantKey + '"] select, [data-key="' + enabledKey + '"] input[type="checkbox"]', function () {
		let $itemsField = $( this ).closest( '[data-key="' + itemsKey + '"]' );

		if ( ! $itemsField.length ) {
			$itemsField = $( this ).closest( '.acf-fields' ).find( '[data-key="' + itemsKey + '"]' ).first();
		}

		updateItemLimits( $itemsField );
	} );

	$( document ).on( 'click', '.ltt-dive-in-feature-image-driver-remove-item', function () {
		const $row = $( this ).closest( '.acf-row' );
		const $nativeRemove = $row.find( '[data-event="remove-row"]' ).first();

		if ( $nativeRemove.length ) {
			$nativeRemove.trigger( 'click' );
		}
	} );
} )( jQuery, acf );
