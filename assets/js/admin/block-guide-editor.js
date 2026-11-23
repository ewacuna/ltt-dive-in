/**
 * Block Guide link in the editor sidebar for theme blocks.
 *
 * Adds a "Block Guide" panel to the selected block's settings with a link to
 * its guide, pointing at the layout the block currently uses when known.
 */
( function ( wp, settings ) {
	'use strict';

	if ( ! wp || ! settings || ! settings.blocks ) {
		return;
	}

	const el = wp.element.createElement;
	const Fragment = wp.element.Fragment;
	const InspectorControls = wp.blockEditor.InspectorControls;
	const PanelBody = wp.components.PanelBody;
	const ExternalLink = wp.components.ExternalLink;
	const __ = wp.i18n.__;
	const sprintf = wp.i18n.sprintf;

	const currentVariant = function ( guide, attributes ) {
		const data = attributes && attributes.data;

		if ( ! guide.field || ! data ) {
			return null;
		}

		const value = data[ guide.field.name ] !== undefined ? data[ guide.field.name ] : data[ guide.field.key ];

		return value && guide.variants[ value ] ? guide.variants[ value ] : null;
	};

	const GuidePanel = function ( props ) {
		const guide = settings.blocks[ props.name ];
		const variant = currentVariant( guide, props.attributes );
		const url = variant ? guide.url + '#' + variant.anchor : guide.url;

		return el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Block Guide', 'ltt-dive-in' ), initialOpen: true },
				el(
					'p',
					null,
					variant
						? sprintf(
							/* translators: %s: layout name. */
							variant.preview
								? __( 'What each field does in the %s layout, with tips and a live preview.', 'ltt-dive-in' )
								: __( 'What each field does in the %s layout, with tips.', 'ltt-dive-in' ),
							variant.name
						)
						: ( guide.preview
							? __( 'What each field does, with tips and a live preview.', 'ltt-dive-in' )
							: __( 'What each field does, with tips.', 'ltt-dive-in' ) )
				),
				el(
					ExternalLink,
					{ href: url },
					variant
						? sprintf(
							/* translators: %s: layout name. */
							__( 'Open the %s guide', 'ltt-dive-in' ),
							variant.name
						)
						: __( 'Open the guide for this block', 'ltt-dive-in' )
				)
			)
		);
	};

	const withGuideLink = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( ! props.isSelected || ! settings.blocks[ props.name ] ) {
				return el( BlockEdit, props );
			}

			// Rendered before the block so the panel sits above the block's fields.
			return el( Fragment, null, el( GuidePanel, props ), el( BlockEdit, props ) );
		};
	}, 'withLttBlockGuideLink' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'ltt-dive-in/block-guide-link', withGuideLink );
}( window.wp, window.ltt_dive_in_block_guide_editor ) );
