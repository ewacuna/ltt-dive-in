/**
 * Alpine state for in-page scroll links such as the hero "Scroll for more" cue.
 *
 * Scrolls past the hero to the link's fragment target without adding the hash to the URL.
 * The native href remains the no-JavaScript fallback. Scrolling inherits the
 * global CSS scroll-behavior, so reduced-motion preferences still apply.
 *
 * @package LTT_Dive_In
 */
( function () {
	'use strict';

	document.addEventListener( 'alpine:init', function () {
		window.Alpine.data( 'lttScrollLink', function () {
			return {
				scroll: function ( event ) {
					const id = decodeURIComponent( this.$el.hash.slice( 1 ) );
					const target = id ? document.getElementById( id ) : null;

					if ( ! target ) {
						return;
					}

					event.preventDefault();

					// Land exactly below the section that holds the link so no sliver of it stays visible.
					const section = this.$el.closest( 'section' );
					const edge = section ? section.getBoundingClientRect().bottom : target.getBoundingClientRect().top;
					window.scrollTo( { top: Math.ceil( window.scrollY + edge ) } );

					// Move keyboard and screen-reader context to the new section, as hash navigation would.
					if ( ! target.hasAttribute( 'tabindex' ) ) {
						target.setAttribute( 'tabindex', '-1' );
					}
					target.focus( { preventScroll: true } );
				},
			};
		} );
	} );
}() );
