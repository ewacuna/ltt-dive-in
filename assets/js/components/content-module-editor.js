/**
 * Per-list bullet styles for the Content Module Copy Block visual editor.
 * The button is present only in that ACF field's custom toolbar.
 *
 * @package LTT_Dive_In
 */
(function () {
	'use strict';

	if ( 'undefined' === typeof tinymce ) {
		return;
	}

	tinymce.PluginManager.add( 'ltt_copy_list_style', function ( editor ) {
		function selectedList() {
			if ( ! editor.selection || ! editor.getBody() ) {
				return null;
			}

			return editor.dom.getParent( editor.selection.getNode(), 'ul' );
		}

		function setStyle( useIcon ) {
			var list = selectedList();

			if ( ! list ) {
				return;
			}

			editor.undoManager.transact( function () {
				editor.dom.removeClass( list, 'ltt-copy-list--icon' );

				if ( useIcon ) {
					editor.dom.addClass( list, 'ltt-copy-list--icon' );
					editor.dom.setAttrib( list, 'role', 'list' );
				} else {
					editor.dom.setAttrib( list, 'role', null );
				}
			} );

			editor.nodeChanged();
		}

		editor.addButton( 'ltt_copy_list_style', {
			text: editor.settings.lttCopyListStyleLabel || 'List style',
			icon: false,
			type: 'menubutton',
			menu: [
				{
					text: editor.settings.lttCopyListIconLabel || 'Icon bullets',
					onclick: function () {
						setStyle( true );
					}
				},
				{
					text: editor.settings.lttCopyListRoundLabel || 'Round bullets',
					onclick: function () {
						setStyle( false );
					}
				}
			],
			onPostRender: function () {
				var button = this;

				editor.on( 'NodeChange', function () {
					button.disabled( ! selectedList() );
				} );

				button.disabled( ! selectedList() );
			}
		} );

		editor.on( 'init', function () {
			var toolbar = String( editor.settings.toolbar1 || '' );

			if ( toolbar.indexOf( 'ltt_copy_list_style' ) !== -1 ) {
				editor.dom.addClass( editor.getBody(), 'content-module__rich-text' );
			}
		} );
	} );
}());
