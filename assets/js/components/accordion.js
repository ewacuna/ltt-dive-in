/**
 * Manage reusable accordion groups.
 */
( function () {
	'use strict';

	const accordions = document.querySelectorAll( '[data-accordion]' );

	if ( ! accordions.length ) {
		return;
	}

	const getPanel = function ( trigger ) {
		return document.getElementById( trigger.getAttribute( 'aria-controls' ) );
	};

	const setExpanded = function ( trigger, expanded ) {
		const panel = getPanel( trigger );
		const item = trigger.closest( '.global-accordion__item' );
		const accordion = trigger.closest( '[data-accordion]' );

		if ( ! panel ) {
			return;
		}

		trigger.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
		panel.setAttribute( 'aria-hidden', expanded ? 'false' : 'true' );

		if ( expanded ) {
			panel.removeAttribute( 'inert' );
		} else {
			panel.setAttribute( 'inert', '' );
		}

		if ( accordion && accordion.classList.contains( 'is-ready' ) ) {
			panel.hidden = false;
		} else {
			panel.hidden = ! expanded;
		}

		if ( item ) {
			item.classList.toggle( 'is-expanded', expanded );
		}
	};

	accordions.forEach( function ( accordion ) {
		const triggers = Array.from( accordion.querySelectorAll( '[data-accordion-trigger]' ) );

		if ( ! triggers.length ) {
			return;
		}

		const collapseOthers = function ( activeTrigger ) {
			triggers.forEach( function ( trigger ) {
				if ( trigger !== activeTrigger ) {
					setExpanded( trigger, false );
				}
			} );
		};

		triggers.forEach( function ( trigger, index ) {
			setExpanded( trigger, 'true' === trigger.getAttribute( 'aria-expanded' ) );

			trigger.addEventListener( 'click', function () {
				const willExpand = 'true' !== trigger.getAttribute( 'aria-expanded' );

				if ( willExpand ) {
					collapseOthers( trigger );
				}

				setExpanded( trigger, willExpand );
			} );

			trigger.addEventListener( 'keydown', function ( event ) {
				let targetIndex = null;

				if ( 'Escape' === event.key && 'true' === trigger.getAttribute( 'aria-expanded' ) ) {
					event.preventDefault();
					setExpanded( trigger, false );
					trigger.focus();
					return;
				}

				if ( 'ArrowDown' === event.key ) {
					targetIndex = ( index + 1 ) % triggers.length;
				} else if ( 'ArrowUp' === event.key ) {
					targetIndex = ( index - 1 + triggers.length ) % triggers.length;
				} else if ( 'Home' === event.key ) {
					targetIndex = 0;
				} else if ( 'End' === event.key ) {
					targetIndex = triggers.length - 1;
				}

				if ( null !== targetIndex ) {
					event.preventDefault();
					triggers[ targetIndex ].focus();
				}
			} );
		} );

		accordion.classList.add( 'is-ready' );

		triggers.forEach( function ( trigger ) {
			const panel = getPanel( trigger );

			if ( panel ) {
				panel.hidden = false;
			}
		} );
	} );
}() );
