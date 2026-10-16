/**
 * Native horizontal scrolling for the Listing Module's mobile card layout.
 */
(function () {
    'use strict';

    function initListingModule(section) {
        const grid = section.querySelector('.ltt-listing-module__grid');
        const arrows = section.querySelector('.ltt-listing-module__arrows');
        const facetControls = section.querySelectorAll('[data-listing-select-id]');
        const status = section.querySelector('[data-listing-status]');
        let awaitingRefresh = false;
        let restoreFocusControl = null;

        function facetOptionLabel(option) {
            // FacetWP appends a live result count to each value option.
            const label = option.value ? option.text.replace(/\s+\([\d,]+\)\s*$/, '') : option.text;
            return label.replace(/\s+/g, ' ').trim();
        }

        if (!grid) {
            return;
        }

        function closeFacet(control, restoreFocus) {
            const trigger = control.querySelector('.ltt-select__trigger');
            const menu = control.querySelector('.ltt-select__menu');

            if (!trigger || !menu) {
                return;
            }

            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');

            if (restoreFocus) {
                trigger.focus({ preventScroll: true });
            }
        }

        function syncFacetSelects() {
            facetControls.forEach(function (control) {
                const select = control.querySelector('select.facetwp-dropdown');

                if (!select) {
                    return;
                }

                const selectId = control.dataset.listingSelectId;
                const labelId = selectId + '-label';
                const currentId = selectId + '-current';
                const menuId = selectId + '-options';
                let trigger = control.querySelector('.ltt-select__trigger');
                let menu = control.querySelector('.ltt-select__menu');

                select.id = selectId;
                select.setAttribute('aria-controls', control.dataset.listingResultsId);
                select.setAttribute('aria-labelledby', labelId);
                select.removeAttribute('aria-label');

                if (!trigger) {
                    trigger = document.createElement('button');
                    trigger.type = 'button';
                    trigger.className = 'ltt-select__trigger';
                    trigger.setAttribute('aria-controls', menuId);
                    trigger.setAttribute('aria-labelledby', labelId + ' ' + currentId);
                    trigger.setAttribute('aria-expanded', 'false');

                    const current = document.createElement('span');
                    current.id = currentId;
                    current.className = 'ltt-listing-module__filter-value';
                    trigger.appendChild(current);

                    const icon = document.createElement('span');
                    icon.className = 'ltt-select__trigger-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    const nativeIcon = control.querySelector('.ltt-select__native-icon img');

                    if (nativeIcon) {
                        icon.appendChild(nativeIcon.cloneNode(true));
                    }

                    trigger.appendChild(icon);
                    control.appendChild(trigger);
                    trigger.addEventListener('click', function () {
                        const willOpen = menu.hidden;

                        facetControls.forEach(function (other) {
                            closeFacet(other, false);
                        });
                        menu.hidden = !willOpen;
                        trigger.setAttribute('aria-expanded', String(willOpen));
                    });
                }

                if (!menu) {
                    menu = document.createElement('div');
                    menu.id = menuId;
                    menu.className = 'ltt-select__menu';
                    menu.setAttribute('role', 'group');
                    menu.setAttribute('aria-labelledby', labelId);
                    menu.hidden = true;
                    control.appendChild(menu);
                    menu.addEventListener('click', function (event) {
                        const option = event.target.closest('button[data-listing-value]');

                        if (!option || option.disabled) {
                            return;
                        }

                        const activeSelect = control.querySelector('select.facetwp-dropdown');

                        if (!activeSelect) {
                            return;
                        }

                        activeSelect.value = option.dataset.listingValue;
                        closeFacet(control, true);
                        activeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }

                const selected = select.options[select.selectedIndex] || select.options[0];
                const selectedLabel = selected ? facetOptionLabel(selected) : '';
                trigger.querySelector('#' + currentId).textContent = selectedLabel;
                trigger.title = selectedLabel;
                trigger.disabled = !Array.from(select.options).some(function (option) {
                    return '' !== option.value && !option.disabled;
                });

                const buttons = Array.from(select.options).filter(function (option) {
                    return '' !== option.value || '' !== select.value;
                }).map(function (option) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'ltt-select__option';
                    button.dataset.listingValue = option.value;
                    button.textContent = facetOptionLabel(option);
                    button.disabled = option.disabled;
                    button.setAttribute('aria-pressed', String(option.value === select.value));
                    return button;
                });

                menu.replaceChildren(...buttons);
                select.setAttribute('aria-hidden', 'true');
                select.tabIndex = -1;
                control.classList.add('is-enhanced');

                if (control.lttActiveSelect !== select) {
                    control.lttActiveSelect = select;
                    select.addEventListener('change', syncFacetSelects);
                }
            });
        }

        syncFacetSelects();

        if (facetControls.length) {
            document.addEventListener('pointerdown', function (event) {
                facetControls.forEach(function (control) {
                    if (!control.contains(event.target)) {
                        closeFacet(control, false);
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if ('Escape' !== event.key) {
                    return;
                }

                facetControls.forEach(function (control) {
                    const menu = control.querySelector('.ltt-select__menu');

                    if (menu && !menu.hidden) {
                        closeFacet(control, true);
                        event.preventDefault();
                    }
                });
            });

            section.querySelectorAll('.ltt-listing-module__filter-label').forEach(function (label) {
                label.addEventListener('click', function (event) {
                    const control = label.closest('.ltt-listing-module__filter').querySelector('.ltt-listing-module__filter-control');

                    if (control.classList.contains('is-enhanced')) {
                        event.preventDefault();
                        control.querySelector('.ltt-select__trigger').focus();
                    }
                });
            });

            document.addEventListener('facetwp-refresh', function () {
                restoreFocusControl = Array.from(facetControls).find(function (control) {
                    return control.contains(document.activeElement);
                }) || null;
                facetControls.forEach(function (control) {
                    closeFacet(control, false);
                });
                awaitingRefresh = true;
                grid.setAttribute('aria-busy', 'true');

                if (status) {
                    status.textContent = '';
                }
            });

            document.addEventListener('facetwp-loaded', function () {
                // FacetWP replaces the native selects after each refresh. Rebuild
                // the shared select presentation from their current options.
                queueMicrotask(function () {
                    syncFacetSelects();

                    if (restoreFocusControl) {
                        const trigger = restoreFocusControl.querySelector('.ltt-select__trigger');

                        if (trigger) {
                            trigger.focus({ preventScroll: true });
                        }
                        restoreFocusControl = null;
                    }
                });
                grid.removeAttribute('aria-busy');

                if (awaitingRefresh && status) {
                    grid.scrollLeft = 0;
                    status.textContent = section.dataset.listingResultsUpdated;
                    awaitingRefresh = false;
                }

                if (arrows) {
                    update();
                }
            });
        }

        if (!arrows) {
            return;
        }

        const previous = arrows.querySelector('[data-listing-previous]');
        const next = arrows.querySelector('[data-listing-next]');
        const mobile = window.matchMedia('(max-width: 767.98px)');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        if (!previous || !next) {
            return;
        }

        function update() {
            const scrollable = mobile.matches && grid.scrollWidth > grid.clientWidth + 2;
            arrows.hidden = !scrollable;

            if (!scrollable) {
                return;
            }

            previous.disabled = grid.scrollLeft <= 2;
            next.disabled = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 2;
        }

        function move(direction) {
            const card = grid.querySelector('.ltt-listing-module__card');

            if (!card) {
                return;
            }

            const gap = parseFloat(window.getComputedStyle(grid).gap) || 0;
            grid.scrollBy({
                left: direction * (card.getBoundingClientRect().width + gap),
                behavior: reducedMotion.matches ? 'auto' : 'smooth'
            });
        }

        previous.addEventListener('click', function () { move(-1); });
        next.addEventListener('click', function () { move(1); });
        grid.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    function init() {
        document.querySelectorAll('.ltt-listing-module').forEach(initListingModule);
    }

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
}());
