( function ( wp ) {
	'use strict';

	const settings = window.ltt_dive_in_ai_summary || {};
	const i18n = settings.i18n || {};

	wp.domReady( function () {
		const mount = document.querySelector( '.ltt-dive-in-ai-summary-tools' );
		const summaryField = document.querySelector( '.acf-field[data-key="field_ltt_dive_in_ai_summary"] textarea' );

		if ( ! mount || ! summaryField ) {
			return;
		}

		const notes = document.createElement( 'div' );
		const live = document.createElement( 'p' );

		live.setAttribute( 'role', 'status' );
		live.className = 'ltt-dive-in-ai-summary-tools__status';
		mount.append( notes );

		if ( ! settings.hasKey ) {
			const message = document.createElement( 'p' );

			message.textContent = i18n.noKey;
			notes.append( message );

			if ( settings.settingsUrl ) {
				const link = document.createElement( 'a' );

				link.href = settings.settingsUrl;
				link.textContent = i18n.openSettings;
				notes.append( link );
			}

			return;
		}

		const button = document.createElement( 'button' );
		const token = document.createElement( 'input' );
		let state = settings.state || {};
		let lastGenerated = null;
		let busy = false;

		button.type = 'button';
		button.className = 'button button-secondary';
		token.type = 'hidden';
		token.name = 'ltt_dive_in_ai_summary_token';
		mount.append( button, live, token );

		const editor = function () {
			return wp.data.select( 'core/editor' );
		};

		const hasSummary = function () {
			return '' !== summaryField.value.trim();
		};

		const render = function () {
			notes.replaceChildren();
			button.textContent = hasSummary() ? i18n.regenerate : i18n.generate;

			const add = function ( text, type ) {
				const note = document.createElement( 'div' );
				const paragraph = document.createElement( 'p' );

				note.className = type ? 'notice inline notice-' + type : '';
				paragraph.textContent = text;
				note.append( paragraph );
				notes.append( note );
			};

			if ( state.stale ) {
				add( i18n.stale, 'warning' );
			}

			if ( state.has_record && state.generated_at ) {
				add( i18n.generatedAt.replace( '%s', state.generated_at ) );
			}

			if ( state.has_record && state.edited ) {
				add( i18n.edited );
			}
		};

		const request = function ( action, extra ) {
			const body = new URLSearchParams( Object.assign( {
				action: action,
				nonce: settings.nonce,
				post_id: settings.postId,
				content: editor().getEditedPostContent(),
				summary: summaryField.value,
			}, extra || {} ) );

			return window.fetch( settings.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body,
			} ).then( function ( response ) {
				return response.json().catch( function () {
					return { success: false };
				} );
			} ).then( function ( result ) {
				if ( ! result || ! result.success ) {
					throw new Error( result && result.data && result.data.message ? result.data.message : i18n.requestFailed );
				}

				return result.data;
			} );
		};

		const refresh = function () {
			return request( 'ltt_dive_in_ai_summary_status' ).then( function ( data ) {
				state = data;
				render();

				return data;
			} );
		};

		// Ask before replacing text that is not an unchanged AI draft.
		const confirmReplace = function () {
			if ( ! hasSummary() ) {
				return Promise.resolve( true );
			}

			if ( null !== lastGenerated && lastGenerated === summaryField.value ) {
				return Promise.resolve( true );
			}

			return refresh().then( function ( data ) {
				return ! data.edited || window.confirm( i18n.confirm );
			} );
		};

		const setValue = function ( field, value ) {
			field.value = value;
			field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		};

		button.addEventListener( 'click', function () {
			if ( busy ) {
				return;
			}

			busy = true;
			button.setAttribute( 'aria-disabled', 'true' );

			confirmReplace().then( function ( confirmed ) {
				if ( ! confirmed ) {
					return;
				}

				live.textContent = i18n.generating;

				return request( 'ltt_dive_in_ai_summary_generate', {
					title: editor().getEditedPostAttribute( 'title' ) || '',
				} ).then( function ( data ) {
					setValue( summaryField, data.summary );
					token.value = data.token;
					lastGenerated = summaryField.value;
					state = Object.assign( {}, state, { stale: false, edited: false } );
					render();
					live.textContent = i18n.generated;
				} );
			} ).catch( function ( error ) {
				live.textContent = error.message || i18n.requestFailed;
			} ).finally( function () {
				busy = false;
				button.removeAttribute( 'aria-disabled' );
			} );
		} );

		// Meta boxes save after the post; refresh once they are stored.
		const editPost = wp.data.select( 'core/edit-post' );
		let wasSaving = false;

		wp.data.subscribe( function () {
			const saving = editPost && 'function' === typeof editPost.isSavingMetaBoxes
				? editPost.isSavingMetaBoxes()
				: editor().isSavingPost() && ! editor().isAutosavingPost();

			if ( wasSaving && ! saving ) {
				token.value = '';
				lastGenerated = null;
				refresh().catch( function () {} );
			}

			wasSaving = saving;
		} );

		render();
	} );
}( window.wp ) );
