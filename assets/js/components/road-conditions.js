/**
 * Road conditions module: state links, highway filter, card disclosures, and
 * traffic cameras (refreshing stills and a live-video dialog).
 *
 * The server renders every state and card, the state links navigate, and the
 * highway form submits with GET, so the module works without JavaScript. This
 * script switches views in place, keeps the URL shareable, and announces changes.
 * It initializes on load and again when seamless navigation swaps the module in.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const init = function () {
		const root = document.querySelector( '[data-road-conditions]' );

		if ( ! root || root.dataset.roadConditionsReady ) {
			return;
		}

		root.dataset.roadConditionsReady = 'true';

		const links = Array.from( root.querySelectorAll( '[data-road-state-link]' ) );
		const panels = Array.from( root.querySelectorAll( '[data-road-state-panel]' ) );
		const forms = Array.from( root.querySelectorAll( '[data-road-highway-form]' ) );
		const status = root.querySelector( '[data-road-conditions-status]' );

		const announce = function ( message ) {
			if ( ! status || ! message ) {
				return;
			}

			status.textContent = '';
			window.setTimeout( function () {
				status.textContent = message;
			}, 50 );
		};

		const getForm = function ( state ) {
			return forms.find( function ( form ) {
				return form.dataset.roadHighwayForm === state;
			} );
		};

		const getSelect = function ( state ) {
			const form = getForm( state );

			return form ? form.querySelector( '[data-road-highway-select]' ) : null;
		};

		const updateUrl = function ( state, highway ) {
			if ( ! window.history || ! window.history.replaceState ) {
				return;
			}

			const url = new URL( window.location.href );

			url.searchParams.set( 'road_state', state );

			if ( highway ) {
				url.searchParams.set( 'highway', highway );
			} else {
				url.searchParams.delete( 'highway' );
			}

			window.history.replaceState( null, '', url.toString() );
		};

		const filterCards = function ( state ) {
			const select = getSelect( state );
			const highway = select ? select.value : '';
			const panel = panels.find( function ( item ) {
				return item.dataset.roadStatePanel === state;
			} );

			if ( panel ) {
				panel.querySelectorAll( '[data-road-highway]' ).forEach( function ( card ) {
					card.hidden = Boolean( highway ) && card.dataset.roadHighway !== highway;

					// The full list starts collapsed; a single chosen highway opens.
					if ( card.roadCardSetExpanded ) {
						card.roadCardSetExpanded( Boolean( highway ) && ! card.hidden );
					}
				} );
			}

			return highway;
		};

		const activateState = function ( state ) {
			links.forEach( function ( link ) {
				if ( link.dataset.roadStateLink === state ) {
					link.setAttribute( 'aria-current', 'true' );
				} else {
					link.removeAttribute( 'aria-current' );
				}
			} );

			panels.forEach( function ( panel ) {
				panel.hidden = panel.dataset.roadStatePanel !== state;
			} );

			forms.forEach( function ( form ) {
				form.hidden = form.dataset.roadHighwayForm !== state;
			} );

			updateUrl( state, filterCards( state ) );
		};

		links.forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				const state = link.dataset.roadStateLink;
				const panel = panels.find( function ( item ) {
					return item.dataset.roadStatePanel === state;
				} );

				if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || ! panel ) {
					return;
				}

				event.preventDefault();

				if ( 'true' === link.getAttribute( 'aria-current' ) ) {
					return;
				}

				activateState( state );

				if ( status ) {
					announce( ( status.dataset.stateMessage || '' ).replace( '%s', panel.dataset.roadStateLabel || link.textContent.trim() ) );
				}
			} );
		} );

		forms.forEach( function ( form ) {
			const state = form.dataset.roadHighwayForm;
			const select = form.querySelector( '[data-road-highway-select]' );
			const apply = form.querySelector( '[data-road-highway-apply]' );

			if ( apply ) {
				apply.hidden = true;
			}

			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();
			} );

			if ( ! select ) {
				return;
			}

			select.addEventListener( 'change', function () {
				const highway = filterCards( state );
				const option = select.options[ select.selectedIndex ];

				updateUrl( state, highway );

				if ( status ) {
					announce( highway && option ? ( status.dataset.highwayMessage || '' ).replace( '%s', option.text ) : status.dataset.allMessage );
				}
			} );
		} );

		root.querySelectorAll( '[data-road-card-toggle]' ).forEach( function ( toggle ) {
			const details = document.getElementById( toggle.getAttribute( 'aria-controls' ) );

			if ( ! details ) {
				return;
			}

			const setExpanded = function ( expanded ) {
				toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
				details.hidden = ! expanded;
			};
			const card = toggle.closest( '[data-road-highway]' );

			if ( card ) {
				card.roadCardSetExpanded = setExpanded;
			}

			toggle.hidden = false;
			toggle.addEventListener( 'click', function () {
				setExpanded( 'true' !== toggle.getAttribute( 'aria-expanded' ) );
			} );
		} );

		panels.forEach( function ( panel ) {
			filterCards( panel.dataset.roadStatePanel );
		} );

		initCameras( root );
	};

	/*
	 * Traffic cameras: stills refresh every minute while they are visible.
	 * Cameras with a live stream open it in a dialog; the others open their
	 * still, enlarged. hls.js is fetched the first time a stream opens.
	 */
	const CAMERA_REFRESH_MS = 60000;
	const CAMERA_START_TIMEOUT_MS = 25000;
	let hlsLoader = null;

	const loadHls = function ( url ) {
		if ( window.Hls ) {
			return Promise.resolve( window.Hls );
		}

		if ( ! url ) {
			return Promise.reject( new Error( 'hls.js unavailable' ) );
		}

		if ( ! hlsLoader ) {
			hlsLoader = new Promise( function ( resolve, reject ) {
				const script = document.createElement( 'script' );

				script.src = url;
				script.async = true;
				script.onload = function () {
					if ( window.Hls ) {
						resolve( window.Hls );
					} else {
						reject( new Error( 'hls.js unavailable' ) );
					}
				};
				script.onerror = function () {
					hlsLoader = null;
					reject( new Error( 'hls.js failed to load' ) );
				};
				document.head.appendChild( script );
			} );
		}

		return hlsLoader;
	};

	const refreshStills = function ( root ) {
		if ( document.hidden || ! root.isConnected ) {
			return;
		}

		root.querySelectorAll( '[data-road-camera-image]' ).forEach( function ( image ) {
			// Skip stills in hidden panels, filtered-out cards, and collapsed details.
			if ( ! image.offsetParent || image.dataset.roadCameraLoading ) {
				return;
			}

			const source = image.dataset.roadCameraSource || image.getAttribute( 'src' );
			const next = new URL( source, window.location.href );
			const loader = new Image();

			image.dataset.roadCameraSource = source;
			image.dataset.roadCameraLoading = 'true';
			next.searchParams.set( 'ltt', String( Date.now() ) );
			loader.referrerPolicy = 'no-referrer';
			loader.onload = function () {
				image.src = loader.src;
				delete image.dataset.roadCameraLoading;

				// Keep the enlarged copy in the dialog current too.
				if ( image.roadCameraCopy && image.roadCameraCopy.isConnected ) {
					image.roadCameraCopy.src = loader.src;
				}
			};
			loader.onerror = function () {
				delete image.dataset.roadCameraLoading;
			};
			loader.src = next.toString();
		} );
	};

	const initCameras = function ( root ) {
		const timer = window.setInterval( function () {
			if ( ! root.isConnected ) {
				window.clearInterval( timer );
				return;
			}

			refreshStills( root );
		}, CAMERA_REFRESH_MS );

		const dialog = root.querySelector( '[data-road-camera-dialog]' );
		const player = dialog ? dialog.querySelector( '[data-road-camera-player]' ) : null;
		const title = dialog ? dialog.querySelector( '[data-road-camera-dialog-title]' ) : null;
		const note = dialog ? dialog.querySelector( '[data-road-camera-dialog-status]' ) : null;
		const close = dialog ? dialog.querySelector( '[data-road-camera-dialog-close]' ) : null;

		if ( ! dialog || ! player || ! title || typeof dialog.showModal !== 'function' ) {
			return;
		}

		// Each opening gets its own video and session, so events from a closed
		// stream can never reach the next one.
		let session = null;
		let trigger = null;

		const setNote = function ( key ) {
			if ( note ) {
				note.textContent = note.dataset[ key ] || '';
			}
		};

		const stop = function () {
			if ( ! session ) {
				return;
			}

			const current = session;

			session = null;
			window.clearTimeout( current.timer );

			if ( current.hls ) {
				current.hls.destroy();
			}

			current.video.pause();
			current.video.removeAttribute( 'src' );
			current.video.load();
			current.video.remove();
		};

		// Show a camera's latest still in the dialog, enlarged.
		const showStill = function ( still ) {
			if ( ! still || ! still.currentSrc ) {
				return;
			}

			const copy = document.createElement( 'img' );

			copy.className = 'road-camera-dialog__still';
			copy.src = still.currentSrc;
			copy.alt = still.dataset.roadCameraAlt || '';
			copy.referrerPolicy = 'no-referrer';
			still.roadCameraCopy = copy;
			player.replaceChildren( copy );
		};

		const play = function ( stream, still ) {
			stop();

			const video = document.createElement( 'video' );
			const current = { video: video, hls: null, retries: 0, timer: 0 };

			session = current;
			video.className = 'road-camera-dialog__video';
			video.controls = true;
			video.muted = true;
			video.playsInline = true;
			player.replaceChildren( video );
			setNote( 'loadingMessage' );

			const isCurrent = function () {
				return session === current && dialog.open;
			};

			const fail = function () {
				if ( ! isCurrent() ) {
					return;
				}

				stop();
				setNote( 'errorMessage' );

				// Show the camera's latest still in place of the unavailable video.
				showStill( still );
			};

			const start = function () {
				const attempt = video.play();

				if ( attempt && typeof attempt.catch === 'function' ) {
					attempt.catch( function () {} );
				}
			};

			video.addEventListener( 'playing', function () {
				if ( isCurrent() ) {
					window.clearTimeout( current.timer );
					setNote( 'sourceMessage' );
				}
			} );

			// A camera Caltrans lists but is not broadcasting can retry for a long time.
			current.timer = window.setTimeout( fail, CAMERA_START_TIMEOUT_MS );

			// hls.js has the timeouts and retries Caltrans needs, so native playback
			// is only for browsers without Media Source Extensions (older iPhones).
			if ( ! window.MediaSource && ! window.ManagedMediaSource && video.canPlayType( 'application/vnd.apple.mpegurl' ) ) {
				video.addEventListener( 'error', fail );
				video.src = stream;
				start();
				return;
			}

			loadHls( root.dataset.roadHls ).then( function ( Hls ) {
				if ( ! isCurrent() ) {
					return;
				}

				if ( ! Hls.isSupported() ) {
					fail();
					return;
				}

				// Caltrans sometimes leaves a playlist request hanging; give up on it
				// quickly and ask again instead of waiting without a limit.
				const playlistPolicy = {
					default: {
						maxTimeToFirstByteMs: 2000,
						maxLoadTimeMs: 8000,
						timeoutRetry: { maxNumRetry: 3, retryDelayMs: 0, maxRetryDelayMs: 0 },
						errorRetry: { maxNumRetry: 2, retryDelayMs: 1000, maxRetryDelayMs: 4000 },
					},
				};
				const hls = new Hls( {
					lowLatencyMode: false,
					manifestLoadPolicy: playlistPolicy,
					playlistLoadPolicy: playlistPolicy,
					fragLoadPolicy: {
						default: {
							maxTimeToFirstByteMs: 5000,
							maxLoadTimeMs: 20000,
							timeoutRetry: { maxNumRetry: 3, retryDelayMs: 0, maxRetryDelayMs: 0 },
							errorRetry: { maxNumRetry: 3, retryDelayMs: 1000, maxRetryDelayMs: 4000 },
						},
					},
				} );

				current.hls = hls;
				hls.on( Hls.Events.ERROR, function ( event, data ) {
					if ( ! data || ! data.fatal || ! isCurrent() ) {
						return;
					}

					// Live camera streams drop now and then: retry twice before giving up.
					if ( current.retries < 2 && Hls.ErrorTypes.NETWORK_ERROR === data.type ) {
						current.retries++;

						if ( Hls.ErrorDetails.MANIFEST_LOAD_ERROR === data.details || Hls.ErrorDetails.MANIFEST_LOAD_TIMEOUT === data.details ) {
							hls.loadSource( stream );
						} else {
							hls.startLoad();
						}
					} else if ( current.retries < 2 && Hls.ErrorTypes.MEDIA_ERROR === data.type ) {
						current.retries++;
						hls.recoverMediaError();
					} else {
						fail();
					}
				} );
				hls.on( Hls.Events.MANIFEST_PARSED, start );
				hls.loadSource( stream );
				hls.attachMedia( video );
			} ).catch( fail );
		};

		// Cameras with a live stream play it; the others open their still, enlarged.
		root.querySelectorAll( '[data-road-camera]' ).forEach( function ( frame ) {
			const image = frame.querySelector( '[data-road-camera-image]' );
			const stream = frame.dataset.roadCameraStream || '';
			const label = frame.dataset.roadCameraLabel || '';
			const provider = frame.dataset.roadCameraSource || '';

			if ( ! image || ! label || frame.querySelector( '.road-card__camera-button' ) ) {
				return;
			}

			const button = document.createElement( 'button' );
			const name = document.createElement( 'span' );
			const icon = document.createElement( 'span' );

			button.type = 'button';
			button.className = 'road-card__camera-button' + ( stream ? '' : ' road-card__camera-button--photo' );
			name.className = 'screen-reader-text';
			name.textContent = ( ( stream ? dialog.dataset.openLabel : dialog.dataset.photoLabel ) || '%s' ).replace( '%s', label );
			icon.className = 'road-card__camera-play';
			icon.setAttribute( 'aria-hidden', 'true' );

			// The button names the action, so the still inside it is decorative.
			image.dataset.roadCameraAlt = image.alt;
			image.alt = '';
			frame.insertBefore( button, image );
			button.append( image, name );

			if ( stream ) {
				button.append( icon );
			}

			button.addEventListener( 'click', function () {
				trigger = button;
				title.textContent = label;
				dialog.classList.toggle( 'road-camera-dialog--photo', ! stream );
				dialog.showModal();

				if ( stream ) {
					play( stream, image );
					return;
				}

				stop();
				showStill( image );

				if ( note ) {
					note.textContent = ( note.dataset.photoMessage || '' ).replace( '%s', provider );
				}
			} );
		} );

		if ( close ) {
			close.addEventListener( 'click', function () {
				dialog.close();
			} );
		}

		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );

		dialog.addEventListener( 'close', function () {
			// The event is queued: skip cleanup if another camera opened meanwhile.
			if ( dialog.open ) {
				return;
			}

			stop();
			player.replaceChildren();

			if ( trigger && trigger.isConnected ) {
				trigger.focus();
			}

			trigger = null;
		} );
	};

	init();
	document.addEventListener( 'ltt:content-replaced', init );
}() );
