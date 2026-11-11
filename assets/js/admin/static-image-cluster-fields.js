( function ( $, acf ) {
	'use strict';

	const variantKey = 'field_ltt_dive_in_static_image_cluster_variant';
	const introKey = 'field_ltt_dive_in_static_image_cluster_intro';
	const imagesKey = 'field_ltt_dive_in_static_image_cluster_images';
	const detailsKey = 'field_ltt_dive_in_static_image_cluster_image_details';
	const settings = window.ltt_dive_in_static_image_cluster_editor || {};
	const limits = settings.imageLimits || {};
	const savingLockKey = 'ltt-dive-in-static-image-cluster-count';

	const syncSavingLock = function () {
		if ( ! window.wp || ! window.wp.data ) {
			return;
		}

		const editor = window.wp.data.dispatch( 'core/editor' );
		const invalid = document.querySelector(
			'[data-key="' + imagesKey + '"][data-ltt-image-count-invalid="true"], ' +
			'[data-key="' + introKey + '"][data-ltt-intro-invalid="true"]'
		);

		if ( invalid ) {
			editor.lockPostSaving( savingLockKey );
			if ( editor.lockPostAutosaving ) {
				editor.lockPostAutosaving( savingLockKey );
			}
		} else {
			editor.unlockPostSaving( savingLockKey );
			if ( editor.unlockPostAutosaving ) {
				editor.unlockPostAutosaving( savingLockKey );
			}
		}
	};

	const updateIntroductionRequirement = function ( $fields ) {
		const variant = $fields.find( '[data-key="' + variantKey + '"] select' ).first().val();
		const $field = $fields.find( '[data-key="' + introKey + '"]' ).first();

		if ( ! $field.length ) {
			return;
		}

		const required = 'hero_caption' === variant;
		const $input = $field.find( 'textarea' ).first();
		const editor = window.tinymce && window.tinymce.get( $input.attr( 'id' ) );
		const content = editor && ! editor.isHidden() ? editor.getContent() : ( $input.val() || '' );
		const text = $( '<div>' ).html( content ).text().replace( /\u00a0/g, ' ' ).trim();
		const valid = ! required || text.length > 0;
		let $requiredMark = $field.find( '> .acf-label label > .acf-required' );
		let $error = $field.find( '> .acf-input > .ltt-static-image-cluster-intro-error' );

		$field.attr( 'data-ltt-intro-invalid', valid ? 'false' : 'true' );
		$input.attr( 'aria-required', required ? 'true' : 'false' );

		if ( required && ! $requiredMark.length ) {
			$requiredMark = $( '<span class="acf-required">*</span>' );
			$field.find( '> .acf-label label' ).first().append( $requiredMark );
		} else if ( ! required ) {
			$requiredMark.remove();
		}

		if ( ! valid ) {
			if ( ! $error.length ) {
				$error = $( '<div class="acf-notice -error ltt-static-image-cluster-intro-error" role="alert"><p></p></div>' );
				$field.children( '.acf-input' ).append( $error );
			}
			$error.find( 'p' ).text( settings.introductionRequired || 'Introduction is required for Hero With Caption.' );
		} else {
			$error.remove();
		}
	};

	const syncImageDetails = function ( $fields, imageCount, limit ) {
		const $details = $fields.find( '[data-key="' + detailsKey + '"]' ).first();
		if ( ! $details.length ) { return; }
		const requiredCount = limit && limit.display ? limit.display : imageCount;
		let detailCount = $details.find( '> .acf-input > .acf-repeater > table > tbody > .acf-row:not(.acf-clone)' ).length;
		while ( detailCount < requiredCount ) {
			$details.find( '> .acf-input > .acf-repeater > .acf-actions [data-event="add-row"]' ).trigger( 'click' );
			detailCount++;
		}
	};

	const updateField = function ( $field ) {
		if ( ! $field.length ) {
			return;
		}

		const $fields = $field.closest( '.acf-fields' );
		const variant = $fields.find( '[data-key="' + variantKey + '"] select' ).first().val();
		const limit = limits[ variant ];

		if ( ! limit ) {
			return;
		}

		const count = $field.find( '.acf-gallery-attachment' ).length;
		const valid = count >= limit.min;
		const message = ( settings.requirementPrefix || 'This variant requires ' ) + limit.label + '. ' +
			( settings.currentlySelected || 'Currently selected: ' ) + count + '.';
		let $notice = $field.find( '> .acf-input > .ltt-static-image-cluster-count-notice' );
		let $error = $field.find( '> .acf-input > .ltt-static-image-cluster-count-error' );

		$field.attr( 'data-ltt-image-count-invalid', valid ? 'false' : 'true' );

		if ( ! $notice.length ) {
			$notice = $( '<p class="description ltt-static-image-cluster-count-notice"></p>' );
			$field.children( '.acf-input' ).prepend( $notice );
		}

		$notice.text( message );

		if ( ! valid ) {
			if ( ! $error.length ) {
				$error = $( '<div class="acf-notice -error ltt-static-image-cluster-count-error" role="alert"><p></p></div>' );
				$field.children( '.acf-input' ).append( $error );
			}
			$error.find( 'p' ).text( message );
		} else {
			$error.remove();
		}

		updateIntroductionRequirement( $fields );
		if ( 'inspired' === variant ) {
			syncImageDetails( $fields, count, limit );
		}
		syncSavingLock();
	};

	const updateWithin = function ( $el ) {
		const $context = $el ? $( $el ) : $( document );
		$context.find( '[data-key="' + imagesKey + '"]' )
			.addBack( '[data-key="' + imagesKey + '"]' )
			.each( function () {
				updateField( $( this ) );
			} );
		syncSavingLock();
	};

	acf.addAction( 'wysiwyg_tinymce_init', function ( editor, id, init, field ) {
		if ( field.get( 'key' ) !== introKey ) {
			return;
		}

		editor.on( 'input change undo redo SetContent', function () {
			updateIntroductionRequirement( field.$el.closest( '.acf-fields' ) );
			syncSavingLock();
		} );
		updateIntroductionRequirement( field.$el.closest( '.acf-fields' ) );
		syncSavingLock();
	} );

	acf.addAction( 'ready', updateWithin );
	acf.addAction( 'append', updateWithin );
	acf.addAction( 'remove', function () {
		window.setTimeout( function () { updateWithin(); }, 0 );
	} );
	acf.addAction( 'sortstop', updateWithin );

	$( document ).on( 'change', '[data-key="' + variantKey + '"] select', function () {
		updateField( $( this ).closest( '.acf-fields' ).find( '[data-key="' + imagesKey + '"]' ).first() );
	} );

	$( document ).on( 'input change', '[data-key="' + introKey + '"] textarea', function () {
		const $fields = $( this ).closest( '.acf-fields' );

		updateIntroductionRequirement( $fields );
		syncSavingLock();
	} );

	$( document ).on( 'change', '[data-key="' + imagesKey + '"] input', function () {
		updateField( $( this ).closest( '[data-key="' + imagesKey + '"]' ) );
	} );

	$( document ).on( 'click', '[data-key="' + imagesKey + '"] .acf-button, [data-key="' + imagesKey + '"] [data-name="remove"]', function () {
		window.setTimeout( function () { updateWithin(); }, 250 );
	} );

} )( jQuery, acf );
