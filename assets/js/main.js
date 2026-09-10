/**
 * Manage the mobile menu and the primary navigation's nested disclosure panels.
 */
( function () {
	'use strict';

	const header = document.querySelector( '.site-header' );
	const navigation = document.querySelector( '#header-navigation' );
	const primaryMenu = document.querySelector( '#primary-menu' );
	const menuButton = document.querySelector( '[data-menu-button]' );
	const mobileQuery = window.matchMedia( '(max-width: 991.98px)' );

	if ( ! header || ! navigation || ! primaryMenu || ! menuButton ) {
		return;
	}

	const menuLabel = menuButton.querySelector( '[data-menu-label]' );
	const submenuToggles = Array.from( primaryMenu.querySelectorAll( '[data-menu-toggle]' ) );
	const pageSiblings = header.parentElement ? Array.from( header.parentElement.children ).filter( function ( element ) {
		return element !== header;
	} ) : [];

	const getControlledSubmenu = function ( toggle ) {
		return document.getElementById( toggle.getAttribute( 'aria-controls' ) );
	};

	const updateOpenState = function () {
		const topLevelOpen = Array.from( primaryMenu.children ).some( function ( item ) {
			const toggle = item.querySelector( ':scope > [data-menu-toggle]' );
			return toggle && 'true' === toggle.getAttribute( 'aria-expanded' );
		} );

		primaryMenu.classList.toggle( 'has-open-submenu', topLevelOpen );
		header.classList.toggle( 'has-open-submenu', topLevelOpen );
	};

	const setSubmenuExpanded = function ( toggle, expanded ) {
		const submenu = getControlledSubmenu( toggle );
		const parentList = toggle.closest( 'ul' );

		if ( ! submenu ) {
			return;
		}

		toggle.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
		toggle.closest( '.menu-item' ).classList.toggle( 'is-submenu-open', expanded );
		submenu.hidden = ! expanded;

		if ( parentList ) {
			const hasOpenChild = Array.from( parentList.children ).some( function ( item ) {
				const childToggle = item.querySelector( ':scope > [data-menu-toggle]' );
				return childToggle && 'true' === childToggle.getAttribute( 'aria-expanded' );
			} );

			parentList.classList.toggle( 'has-open-child', hasOpenChild );
		}

		updateOpenState();
	};

	const closeDescendantSubmenus = function ( container ) {
		Array.from( container.querySelectorAll( '[data-menu-toggle][aria-expanded="true"]' ) ).reverse().forEach( function ( toggle ) {
			setSubmenuExpanded( toggle, false );
		} );
	};

	const closeSiblingSubmenus = function ( toggle ) {
		const parentList = toggle.closest( 'ul' );

		if ( ! parentList ) {
			return;
		}

		Array.from( parentList.children ).forEach( function ( item ) {
			const siblingToggle = item.querySelector( ':scope > [data-menu-toggle]' );

			if ( siblingToggle && siblingToggle !== toggle && 'true' === siblingToggle.getAttribute( 'aria-expanded' ) ) {
				closeDescendantSubmenus( item );
				setSubmenuExpanded( siblingToggle, false );
			}
		} );
	};

	const closeMobileMenu = function ( restoreFocus ) {
		navigation.classList.remove( 'is-open' );
		menuButton.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'has-open-menu' );
		pageSiblings.forEach( function ( element ) {
			element.removeAttribute( 'inert' );
		} );

		if ( menuLabel ) {
			menuLabel.textContent = menuLabel.dataset.openLabel;
		}

		closeDescendantSubmenus( primaryMenu );

		if ( restoreFocus ) {
			menuButton.focus();
		}
	};

	const openMobileMenu = function () {
		navigation.classList.add( 'is-open' );
		menuButton.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'has-open-menu' );
		pageSiblings.forEach( function ( element ) {
			element.setAttribute( 'inert', '' );
		} );

		if ( menuLabel ) {
			menuLabel.textContent = menuLabel.dataset.closeLabel;
		}

		const firstMenuControl = primaryMenu.querySelector( 'a, button' );

		if ( firstMenuControl ) {
			firstMenuControl.focus();
		}
	};

	submenuToggles.forEach( function ( toggle ) {
		const submenu = getControlledSubmenu( toggle );

		if ( submenu ) {
			submenu.hidden = true;
		}

		toggle.addEventListener( 'click', function () {
			const willExpand = 'true' !== toggle.getAttribute( 'aria-expanded' );

			if ( willExpand ) {
				closeSiblingSubmenus( toggle );
			} else {
				closeDescendantSubmenus( toggle.closest( '.menu-item' ) );
			}

			setSubmenuExpanded( toggle, willExpand );
		} );
	} );

	primaryMenu.addEventListener( 'click', function ( event ) {
		const panelControl = event.target.closest( '[data-menu-close], [data-menu-back]' );

		if ( panelControl ) {
			const submenu = panelControl.closest( '.sub-menu' );
			const controller = submenu ? document.getElementById( submenu.getAttribute( 'aria-labelledby' ) ) : null;

			if ( controller ) {
				closeDescendantSubmenus( submenu );
				setSubmenuExpanded( controller, false );
				controller.focus();
			}

			return;
		}

		if ( mobileQuery.matches && event.target.closest( 'a' ) ) {
			closeMobileMenu( false );
		}
	} );

	menuButton.addEventListener( 'click', function () {
		if ( 'true' === menuButton.getAttribute( 'aria-expanded' ) ) {
			closeMobileMenu( true );
		} else {
			openMobileMenu();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			const expandedToggles = submenuToggles.filter( function ( toggle ) {
				return 'true' === toggle.getAttribute( 'aria-expanded' );
			} );

			if ( expandedToggles.length ) {
				const deepestToggle = expandedToggles[ expandedToggles.length - 1 ];
				closeDescendantSubmenus( deepestToggle.closest( '.menu-item' ) );
				setSubmenuExpanded( deepestToggle, false );
				deepestToggle.focus();
			} else if ( mobileQuery.matches && 'true' === menuButton.getAttribute( 'aria-expanded' ) ) {
				closeMobileMenu( true );
			}
		}

		if ( 'Tab' === event.key && mobileQuery.matches && 'true' === menuButton.getAttribute( 'aria-expanded' ) ) {
			const focusable = Array.from( header.querySelectorAll( 'a[href], button:not([disabled]), input:not([disabled])' ) ).filter( function ( element ) {
				return null !== element.offsetParent;
			} );

			if ( ! focusable.length ) {
				return;
			}

			const first = focusable[ 0 ];
			const last = focusable[ focusable.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	mobileQuery.addEventListener( 'change', function () {
		closeMobileMenu( false );
	} );
}() );

/**
 * Keep the footer's visual and reading order aligned at each breakpoint.
 */
( function () {
	'use strict';

	const footerContent = document.querySelector( '[data-footer-content]' );
	const newsletter = document.querySelector( '[data-footer-section="newsletter"]' );
	const partners = document.querySelector( '[data-footer-section="partners"]' );
	const navigation = document.querySelector( '[data-footer-section="navigation"]' );
	const credits = document.querySelector( '[data-footer-credits]' );
	const copyright = document.querySelector( '[data-footer-credit="copyright"]' );
	const legal = document.querySelector( '[data-footer-credit="legal"]' );

	if ( ! footerContent || ! newsletter || ! partners || ! navigation ) {
		return;
	}

	const mobileQuery = window.matchMedia( '(max-width: 767.98px)' );

	const updateFooterOrder = function ( event ) {
		if ( event.matches ) {
			footerContent.append( navigation, newsletter, partners );

			if ( credits && copyright && legal ) {
				credits.append( legal, copyright );
			}
		} else {
			footerContent.append( newsletter, partners, navigation );

			if ( credits && copyright && legal ) {
				credits.append( copyright, legal );
			}
		}
	};

	updateFooterOrder( mobileQuery );
	mobileQuery.addEventListener( 'change', updateFooterOrder );
}() );
