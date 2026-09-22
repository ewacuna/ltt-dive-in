/**
 * Progressive enhancement for Video Module blocks.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	const strings = window.ltt_dive_in_video_module || {};
	const reducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	const buildPlayer = function ( source, url, title ) {
		if ( 'self_hosted' === source ) {
			const video = document.createElement( 'video' );
			video.controls = true;
			video.autoplay = true;
			video.playsInline = true;
			video.preload = 'metadata';
			video.src = url;
			video.setAttribute( 'aria-label', title );
			return video;
		}

		const iframe = document.createElement( 'iframe' );
		iframe.src = url;
		iframe.title = title;
		iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
		iframe.allowFullscreen = true;
		iframe.referrerPolicy = 'strict-origin-when-cross-origin';
		return iframe;
	};

	const stopPlayers = function ( scope ) {
		scope.querySelectorAll( 'iframe, video' ).forEach( function ( player ) {
			if ( 'VIDEO' === player.tagName ) {
				player.pause();
			} else {
				player.src = player.src;
			}
		} );
	};

	document.querySelectorAll( '[data-video-module]' ).forEach( function ( module ) {
		module.querySelectorAll( '[data-video-play]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				const wrapper = button.closest( '[data-video-player]' );

				if ( ! wrapper || wrapper.classList.contains( 'is-playing' ) ) {
					return;
				}

				const source = wrapper.dataset.videoSource || '';
				const url = wrapper.dataset.videoUrl || '';
				const title = button.getAttribute( 'aria-label' ) || strings.playerTitle || 'Video player';

				if ( ! source || ! url ) {
					return;
				}

				wrapper.replaceChildren( buildPlayer( source, url, title ) );
				wrapper.classList.add( 'is-playing' );
			} );
		} );

		module.querySelectorAll( '[data-video-filter]' ).forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				const filter = button.dataset.videoFilter || 'all';

				module.querySelectorAll( '[data-video-filter]' ).forEach( function ( option ) {
					const active = option === button;
					option.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				} );

				module.querySelectorAll( '[data-video-episode]' ).forEach( function ( episode ) {
					episode.hidden = 'all' !== filter && episode.dataset.videoEpisode !== filter;
				} );
			} );
		} );

		const dialog = module.querySelector( '[data-video-dialog]' );
		const dialogPlayer = dialog ? dialog.querySelector( '[data-video-dialog-player]' ) : null;
		const dialogTitle = dialog ? dialog.querySelector( '[data-video-dialog-title]' ) : null;
		const closeButton = dialog ? dialog.querySelector( '[data-video-dialog-close]' ) : null;
		let dialogTrigger = null;

		const closeDialog = function () {
			if ( ! dialog || ! dialogPlayer ) {
				return;
			}

			stopPlayers( dialogPlayer );
			dialogPlayer.replaceChildren();

			if ( dialog.open ) {
				dialog.close();
			}
		};

		if ( dialog && dialogPlayer && dialogTitle ) {
			module.querySelectorAll( '[data-video-modal-trigger]' ).forEach( function ( trigger ) {
				trigger.addEventListener( 'click', function () {
					const source = trigger.dataset.videoSource || '';
					const url = trigger.dataset.videoUrl || '';
					const title = trigger.dataset.videoTitle || strings.playerTitle || 'Video player';

					if ( ! source || ! url ) {
						return;
					}

					dialogTrigger = trigger;
					dialogTitle.textContent = title;
					dialogPlayer.replaceChildren( buildPlayer( source, url, title ) );
					dialog.showModal();
				} );
			} );

			if ( closeButton ) {
				closeButton.addEventListener( 'click', closeDialog );
			}

			dialog.addEventListener( 'click', function ( event ) {
				if ( event.target === dialog ) {
					closeDialog();
				}
			} );

			dialog.addEventListener( 'close', function () {
				stopPlayers( dialogPlayer );
				dialogPlayer.replaceChildren();

				if ( dialogTrigger ) {
					dialogTrigger.focus();
				}
			} );
		}

		const carousel = module.querySelector( '[data-video-carousel]' );

		if ( ! carousel ) {
			return;
		}

		const initializeCarousel = function () {
			if ( ! window.Swiper || carousel.dataset.videoCarouselReady ) {
				return;
			}

			const viewport = carousel.querySelector( '[data-video-carousel-viewport]' );
		const track = carousel.querySelector( '[data-video-carousel-track]' );
		const slides = carousel.querySelectorAll( '[data-video-carousel-slide]' );
		const previous = carousel.querySelector( '[data-video-carousel-previous]' );
		const next = carousel.querySelector( '[data-video-carousel-next]' );
		const pagination = carousel.querySelector( '[data-video-carousel-pagination]' );

		if ( ! viewport || ! track || ! previous || ! next || ! pagination || slides.length < 2 ) {
			return;
		}

		carousel.dataset.videoCarouselReady = 'true';

		viewport.classList.add( 'swiper' );
		track.classList.add( 'swiper-wrapper' );
		slides.forEach( function ( slide ) {
			slide.classList.add( 'swiper-slide' );
		} );
		previous.hidden = false;
		next.hidden = false;

		new window.Swiper( viewport, {
			slidesPerView: 1,
			spaceBetween: 32,
			speed: reducedMotion ? 0 : 350,
			rewind: true,
			a11y: {
				enabled: true,
				containerRoleDescriptionMessage: strings.carousel || 'carousel',
				itemRoleDescriptionMessage: strings.slide || 'slide',
				slideLabelMessage: strings.slideLabel || '{{index}} of {{slidesLength}}',
				prevSlideMessage: strings.previous || 'Previous video',
				nextSlideMessage: strings.next || 'Next video',
				paginationBulletMessage: strings.goTo || 'Go to video {{index}}',
			},
			navigation: {
				prevEl: previous,
				nextEl: next,
			},
			pagination: {
				el: pagination,
				clickable: true,
				bulletElement: 'button',
				bulletClass: 'ltt-carousel-indicator',
				bulletActiveClass: 'is-active',
				renderBullet: function ( index, className ) {
					return '<button class="' + className + '" type="button" aria-label="' + ( strings.goTo || 'Go to video {{index}}' ).replace( '{{index}}', index + 1 ) + '"></button>';
				},
			},
			on: {
				slideChangeTransitionStart: function () {
					stopPlayers( carousel );
				},
				paginationUpdate: function ( instance ) {
					if ( ! instance.pagination || ! instance.pagination.bullets ) {
						return;
					}

					instance.pagination.bullets.forEach( function ( bullet, index ) {
						bullet.setAttribute( 'aria-current', index === instance.realIndex ? 'true' : 'false' );
					} );
				},
			},
		} );
		};

		if ( window.Swiper ) {
			initializeCarousel();
		} else {
			window.addEventListener( 'load', initializeCarousel, { once: true } );
		}
	} );
}() );
