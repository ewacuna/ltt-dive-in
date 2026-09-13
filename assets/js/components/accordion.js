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

	const toggleAccordions = document.querySelectorAll( '[data-toggle-accordion]' );

	toggleAccordions.forEach( function ( toggleAccordion ) {
		const tabs = Array.from( toggleAccordion.querySelectorAll( '[data-toggle-tab]' ) );

		if ( tabs.length < 2 ) {
			return;
		}

		const activateTab = function ( activeTab, moveFocus ) {
			tabs.forEach( function ( tab ) {
				const selected = tab === activeTab;
				const panel = document.getElementById( tab.getAttribute( 'aria-controls' ) );

				tab.setAttribute( 'aria-selected', selected ? 'true' : 'false' );
				tab.setAttribute( 'tabindex', selected ? '0' : '-1' );

				if ( panel ) {
					panel.hidden = ! selected;

					if ( selected ) {
						panel.removeAttribute( 'inert' );
					} else {
						panel.setAttribute( 'inert', '' );
					}
				}
			} );

			if ( moveFocus ) {
				activeTab.focus();
			}
		};

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function () {
				activateTab( tab, false );
			} );

			tab.addEventListener( 'keydown', function ( event ) {
				let targetIndex = null;

				if ( 'ArrowRight' === event.key ) {
					targetIndex = ( index + 1 ) % tabs.length;
				} else if ( 'ArrowLeft' === event.key ) {
					targetIndex = ( index - 1 + tabs.length ) % tabs.length;
				} else if ( 'Home' === event.key ) {
					targetIndex = 0;
				} else if ( 'End' === event.key ) {
					targetIndex = tabs.length - 1;
				}

				if ( null !== targetIndex ) {
					event.preventDefault();
					activateTab( tabs[ targetIndex ], true );
				}
			} );
		} );

		activateTab( tabs[ 0 ], false );
	} );
}() );
