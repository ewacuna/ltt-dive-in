( function ( wp ) {
	'use strict';

	const settings = window.ltt_dive_in_media_tags || {};
	const terms = Array.isArray( settings.terms ) ? settings.terms : [];

	/**
	 * Add a Media Tag <select> to the media grid and media modal toolbars.
	 */
	const addGridFilter = function () {
		if ( ! terms.length || ! wp || ! wp.media || ! wp.media.view || ! wp.media.view.AttachmentsBrowser ) {
			return;
		}

		const filterId = 'ltt-media-tag-attachment-filter';

		const MediaTagFilter = wp.media.view.AttachmentFilters.extend( {
			id: filterId,

			createFilters: function () {
				const filters = {
					all: {
						text: settings.allLabel,
						props: { ltt_media_tag: null },
						priority: 10,
					},
				};

				terms.forEach( function ( term, index ) {
					filters[ 'term-' + term.slug ] = {
						text: term.name,
						props: { ltt_media_tag: term.slug },
						priority: 20 + index,
					};
				} );

				this.filters = filters;
			},
		} );

		const AttachmentsBrowser = wp.media.view.AttachmentsBrowser;

		wp.media.view.AttachmentsBrowser = AttachmentsBrowser.extend( {
			createToolbar: function () {
				AttachmentsBrowser.prototype.createToolbar.apply( this, arguments );

				// Match core: grid mode always shows filters; modals only when enabled.
				if ( ! this.controller.isModeActive( 'grid' ) && ! this.options.filters ) {
					return;
				}

				// The filter is a <select>, so a label must be rendered before it.
				this.toolbar.set( 'lttMediaTagFilterLabel', new wp.media.view.Label( {
					value: settings.filterLabel,
					attributes: { for: filterId },
					priority: -74,
				} ).render() );

				this.toolbar.set( 'lttMediaTagFilter', new MediaTagFilter( {
					controller: this.controller,
					model: this.collection.props,
					priority: -74,
				} ).render() );
			},
		} );
	};

	/**
	 * Narrow the attachment-details tag checklist as the editor types.
	 *
	 * The details panel is re-rendered for every attachment, so events are
	 * delegated from the document.
	 */
	const addChecklistSearch = function () {
		const searchSelector = '.ltt-media-tags__search';

		document.addEventListener( 'input', function ( event ) {
			const search = event.target;

			if ( ! search.matches || ! search.matches( searchSelector ) ) {
				return;
			}

			const field = search.closest( '.ltt-media-tags' );
			const status = field ? field.querySelector( '.ltt-media-tags__status' ) : null;
			const query = search.value.trim().toLocaleLowerCase();
			let shown = 0;

			if ( ! field ) {
				return;
			}

			field.querySelectorAll( '.ltt-media-tags__option' ).forEach( function ( option ) {
				const matches = ! query || option.textContent.toLocaleLowerCase().includes( query );

				option.hidden = ! matches;
				shown += matches ? 1 : 0;
			} );

			if ( status ) {
				if ( ! query ) {
					status.textContent = '';
				} else if ( ! shown ) {
					status.textContent = settings.noMatches || '';
				} else if ( 1 === shown ) {
					status.textContent = settings.match || '';
				} else {
					status.textContent = ( settings.matches || '%d' ).replace( '%d', shown );
				}
			}
		} );

		// The search box is not a saved field; keep it from triggering an attachment save.
		document.addEventListener( 'change', function ( event ) {
			if ( event.target.matches && event.target.matches( searchSelector ) ) {
				event.stopPropagation();
			}
		}, true );

		// Enter in the search box must not submit the details form.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' === event.key && event.target.matches && event.target.matches( searchSelector ) ) {
				event.preventDefault();
			}
		} );
	};

	addGridFilter();
	addChecklistSearch();
}( window.wp ) );
