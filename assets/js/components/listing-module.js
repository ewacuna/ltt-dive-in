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
        let awaitingLoadMore = false;
        let restoreFocusControl = null;
        let restoreSearchSelection = null;
        let restoreSearchButtonFocus = false;
        let restoreLoadMoreFocus = false;
        let restoreClearFocus = false;
        let requestSequence = 0;
        let activeRequest = null;
        let loadMorePending = false;
        let facetwpAjaxHookInstalled = false;
        let searchDelayTimer = null;
        let skipNextSearchDelay = false;

        function facetOptionLabel(option) {
            // FacetWP appends a live result count to each value option.
            const label = option.value ? option.text.replace(/\s+\([\d,]+\)\s*$/, '') : option.text;
            return label.replace(/\s+/g, ' ').trim();
        }

        if (!grid) {
            return;
        }

        function clearErrorStatus() {
            if (!status) {
                return;
            }

            status.classList.remove('ltt-listing-module__status--error');
            status.classList.add('screen-reader-text');
            status.replaceChildren();
        }

        function stopFacetwpLoading() {
            if (window.FWP && 'function' === typeof window.FWP.toggleOverlay) {
                window.FWP.toggleOverlay('off');
                window.FWP.active_facet = null;
            }
        }

        function announceVisibleResults() {
            if (!status) {
                return;
            }

            const count = grid.querySelectorAll('.ltt-listing-module__card').length;
            status.textContent = count + ' ' + (1 === count ? section.dataset.listingResultSingular : section.dataset.listingResultPlural);

            if (0 === count) {
                status.textContent = section.dataset.listingNoResults;
            }
        }

        function showRequestError(request) {
            if (!status || !request) {
                return;
            }

            grid.removeAttribute('aria-busy');
            awaitingRefresh = false;
            awaitingLoadMore = false;
            restoreFocusControl = null;
            restoreSearchSelection = null;
            restoreSearchButtonFocus = false;
            restoreLoadMoreFocus = false;
            restoreClearFocus = false;
            status.classList.remove('screen-reader-text');
            status.classList.add('ltt-listing-module__status--error');
            status.replaceChildren(document.createTextNode(
                request.isLoadMore
                    ? section.dataset.listingLoadMoreError
                    : section.dataset.listingUpdateError
            ));

            if (!request.isLoadMore) {
                const retry = document.createElement('button');
                retry.type = 'button';
                retry.className = 'ltt-listing-module__retry';
                retry.textContent = section.dataset.listingRetry;
                retry.addEventListener('click', function () {
                    clearErrorStatus();
                    if (window.FWP) {
                        window.FWP.refresh();
                    }
                });
                status.appendChild(retry);
            }
        }

        function normalizeSearchValue(value) {
            if (Array.isArray(value)) {
                return value.length ? String(value[0]) : '';
            }

            return value ? String(value) : '';
        }

        function clearSearchDelay() {
            window.clearTimeout(searchDelayTimer);
            searchDelayTimer = null;
        }

        function installSearchDelay() {
            const searchName = section.dataset.listingSearchName;
            const searchLogic = window.FWP && window.FWP.logic && window.FWP.logic.search;

            if (!searchName || !searchLogic || !searchLogic.delay_refresh || searchLogic.delay_refresh.lttListingModule) {
                return Boolean(searchLogic && searchLogic.delay_refresh && searchLogic.delay_refresh.lttListingModule);
            }

            const facetwpDelayRefresh = searchLogic.delay_refresh;
            const listingDelayRefresh = function (facetName) {
                if (facetName !== searchName) {
                    return facetwpDelayRefresh.apply(this, arguments);
                }

                if (skipNextSearchDelay) {
                    skipNextSearchDelay = false;
                    return;
                }

                clearSearchDelay();
                searchDelayTimer = window.setTimeout(function () {
                    searchDelayTimer = null;
                    if (window.FWP && !window.FWP.is_refresh) {
                        window.FWP.frozen_facets[searchName] = 'soft';
                        window.FWP.autoload();
                    }
                }, 500);
            };

            listingDelayRefresh.lttListingModule = true;
            searchLogic.delay_refresh = listingDelayRefresh;
            return true;
        }

        function searchRequestIsCurrent(input) {
            const searchName = section.dataset.listingSearchName;
            return Boolean(
                activeRequest &&
                activeRequest.id === requestSequence &&
                normalizeSearchValue(activeRequest.facets[searchName]) === input.value
            );
        }

        function syncSearchControls() {
            const input = section.querySelector('.facetwp-search');

            if (!input) {
                return;
            }

            installSearchDelay();

            input.id = section.id + '-search-input';
            input.setAttribute('aria-controls', grid.id);

            const facet = input.closest('.facetwp-type-search');
            const icon = facet ? facet.querySelector('.facetwp-icon') : null;

            if (!icon) {
                return;
            }

            if ('BUTTON' === icon.tagName) {
                icon.type = 'button';
                icon.setAttribute('aria-label', section.dataset.listingSearchLabel);
                icon.setAttribute('aria-controls', grid.id);
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = icon.className;
            button.setAttribute('aria-label', section.dataset.listingSearchLabel);
            button.setAttribute('aria-controls', grid.id);

            const image = document.createElement('img');
            image.src = section.dataset.listingSearchIcon;
            image.alt = '';
            button.appendChild(image);
            icon.replaceWith(button);
        }

        function syncResetControls() {
            section.querySelectorAll('.facetwp-reset[data-values]').forEach(function (button) {
                // FacetWP initializes reset buttons only during its first
                // loaded event. Listing's no-results control can be inserted
                // by a later AJAX render, so keep its target list current.
                button._facets = button.dataset.values.split(',').filter(Boolean);
            });
        }

        function getResponseObject(response) {
            if ('object' === typeof response && response) {
                return response;
            }

            if ('string' !== typeof response) {
                return null;
            }

            try {
                return JSON.parse(response);
            } catch (error) {
                const jsonStart = response.indexOf('{"facets');

                if (-1 === jsonStart) {
                    return null;
                }

                try {
                    return JSON.parse(response.slice(jsonStart));
                } catch (parseError) {
                    return null;
                }
            }
        }

        function installFacetwpAjaxHook() {
            if (facetwpAjaxHookInstalled || !window.FWP || !window.FWP.hooks) {
                return;
            }

            facetwpAjaxHookInstalled = true;
            window.FWP.hooks.addFilter('facetwp/ajax_settings', function (settings) {
                const request = activeRequest;

                if (!request) {
                    return settings;
                }

                const previousDone = settings.done;
                const previousFail = settings.fail;

                settings.done = function (response) {
                    if (request.id !== requestSequence) {
                        return;
                    }

                    const responseObject = getResponseObject(response);
                    if (!responseObject || !responseObject.settings) {
                        if (request.isLoadMore && window.FWP) {
                            const previousPage = Math.max(1, request.page - 1);
                            window.FWP.load_more_paged = previousPage;
                            window.FWP.paged = previousPage;
                            window.FWP.is_load_more = false;
                            window.FWP.soft_refresh = false;
                            window.FWP.is_refresh = false;
                        }

                        const loadButton = section.querySelector('.facetwp-load-more');
                        if (loadButton) {
                            loadButton.disabled = false;
                            loadButton.dataset.listingLoading = 'false';
                            if (loadButton.dataset.listingLabel) {
                                loadButton.textContent = loadButton.dataset.listingLabel;
                            }
                        }

                        showRequestError(request);
                        stopFacetwpLoading();
                        activeRequest = null;
                        loadMorePending = false;
                        return;
                    }

                    const searchName = section.dataset.listingSearchName;
                    const searchInput = searchName ? section.querySelector('.facetwp-facet-' + searchName + ' .facetwp-search') : null;
                    if (searchInput && normalizeSearchValue(request.facets[searchName]) !== searchInput.value) {
                        // A response for the previous text must not replace the
                        // user's newer input or card results.
                        window.FWP.facets[searchName] = searchInput.value;
                        window.FWP.frozen_facets[searchName] = 'soft';
                        if (window.FWP.logic && window.FWP.logic.search) {
                            window.FWP.logic.search.delay_refresh(searchName);
                        }
                        activeRequest = null;
                        loadMorePending = false;
                        return;
                    }

                    if (typeof previousDone === 'function') {
                        previousDone.call(this, response);
                    }
                    activeRequest = null;
                    loadMorePending = false;
                };

                settings.fail = function (error) {
                    if (typeof previousFail === 'function') {
                        previousFail.call(this, error);
                    }

                    if (request.id !== requestSequence) {
                        return;
                    }

                    if (request.isLoadMore && window.FWP) {
                        const previousPage = Math.max(1, request.page - 1);
                        window.FWP.load_more_paged = previousPage;
                        window.FWP.paged = previousPage;
                        window.FWP.is_load_more = false;
                        window.FWP.soft_refresh = false;
                        window.FWP.is_refresh = false;
                    }

                    const loadButton = section.querySelector('.facetwp-load-more');
                    if (loadButton) {
                        loadButton.disabled = false;
                        loadButton.dataset.listingLoading = 'false';
                        if (loadButton.dataset.listingLabel) {
                            loadButton.textContent = loadButton.dataset.listingLabel;
                        }
                    }

                    loadMorePending = false;
                    showRequestError(request);
                    stopFacetwpLoading();
                    activeRequest = null;
                };

                return settings;
            });
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
        syncSearchControls();
        syncResetControls();

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
        }

        document.addEventListener('facetwp-refresh', function () {
            if (!window.FWP) {
                return;
            }

            installFacetwpAjaxHook();

            const isLoadMore = Boolean(
                window.FWP.is_load_more &&
                window.FWP.soft_refresh &&
                Number(window.FWP.paged) > 1 &&
                Number(window.FWP.paged) === Number(window.FWP.load_more_paged)
            );

            if (!isLoadMore && window.FWP.is_load_more) {
                // A filter or search changed while a Load More request was in
                // flight. The next response must replace the cards, not append.
                window.FWP.is_load_more = false;
                window.FWP.load_more_paged = 1;
                window.FWP.paged = 1;
            }

            requestSequence += 1;
            activeRequest = {
                id: requestSequence,
                isLoadMore: isLoadMore,
                page: isLoadMore ? Number(window.FWP.paged) : 1,
                facets: JSON.parse(JSON.stringify(window.FWP.facets || {}))
            };
            awaitingRefresh = true;
            awaitingLoadMore = isLoadMore;
            loadMorePending = isLoadMore;

            restoreFocusControl = Array.from(facetControls).find(function (control) {
                return control.contains(document.activeElement);
            }) || null;

            const focusedSearch = section.querySelector('.facetwp-search:focus');
            restoreSearchSelection = focusedSearch ? {
                start: focusedSearch.selectionStart,
                end: focusedSearch.selectionEnd
            } : null;
            restoreSearchButtonFocus = Boolean(section.querySelector('.facetwp-type-search .facetwp-icon:focus'));

            if (restoreClearFocus) {
                restoreClearFocus = false;
                restoreFocusControl = facetControls[0] || null;
                if (!restoreFocusControl && section.querySelector('.facetwp-search')) {
                    restoreSearchSelection = { start: 0, end: 0 };
                }
            }

            facetControls.forEach(function (control) {
                closeFacet(control, false);
            });

            clearErrorStatus();
            grid.setAttribute('aria-busy', 'true');

            if (status) {
                status.textContent = isLoadMore ? section.dataset.listingLoadingMore : section.dataset.listingLoading;
            }
        });

        section.addEventListener('click', function (event) {
            const resetButton = event.target.closest('.facetwp-reset[data-values]');

            if (resetButton) {
                event.preventDefault();
                // This reset control can be rendered by an AJAX response,
                // after FacetWP's one-time setup of its built-in reset buttons.
                // Handle it here so the selected Listing facets always reset.
                event.stopPropagation();
                clearSearchDelay();
                restoreClearFocus = true;

                const facetsToReset = resetButton.dataset.values.split(',').map(function (name) {
                    return name.trim();
                }).filter(Boolean);

                // The no-results markup is returned through FacetWP's AJAX
                // template and may not retain every configured target in its
                // data-values attribute. Derive this block's live controls,
                // clear their native values, and reset those exact facets.
                section.querySelectorAll('[data-listing-select-id] .facetwp-facet[data-name], .facetwp-type-search[data-name]').forEach(function (facet) {
                    facetsToReset.push(facet.dataset.name);

                    const dropdown = facet.querySelector('select.facetwp-dropdown');
                    const search = facet.querySelector('.facetwp-search');

                    if (dropdown) {
                        dropdown.value = '';
                    }
                    if (search) {
                        search.value = '';
                    }
                });

                const searchName = section.dataset.listingSearchName;
                if (searchName && !facetsToReset.includes(searchName)) {
                    facetsToReset.push(searchName);
                }

                const uniqueFacets = Array.from(new Set(facetsToReset));
                if (window.FWP && 'function' === typeof window.FWP.reset && uniqueFacets.length) {
                    window.FWP.reset(uniqueFacets);
                }
                return;
            }

            const searchButton = event.target.closest('.facetwp-icon');
            if (searchButton && searchButton.closest('.facetwp-type-search')) {
                const input = searchButton.closest('.facetwp-type-search').querySelector('.facetwp-search');
                clearSearchDelay();
                if (input && searchRequestIsCurrent(input)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                return;
            }

            const button = event.target.closest('.facetwp-load-more');

            if (!button) {
                return;
            }

            const searchInput = section.querySelector('.facetwp-search');
            const searchName = section.dataset.listingSearchName;
            if (searchDelayTimer && searchInput && searchName) {
                clearSearchDelay();
                const currentSearch = window.FWP && window.FWP.facets ? window.FWP.facets[searchName] : '';
                if (normalizeSearchValue(currentSearch) !== searchInput.value) {
                    if (window.FWP) {
                        window.FWP.frozen_facets[searchName] = 'soft';
                        window.FWP.autoload();
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }
            }

            if (loadMorePending) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            button.dataset.listingLabel = button.textContent.trim();
            button.dataset.listingLoading = 'true';
            button.disabled = true;
            restoreLoadMoreFocus = document.activeElement === button;
        }, true);

        section.addEventListener('change', function (event) {
            if (event.target.matches && event.target.matches('select.facetwp-dropdown')) {
                clearSearchDelay();
            }
        }, true);

        section.addEventListener('keydown', function (event) {
            const input = event.target.closest('.facetwp-search');
            if (!input || 'Enter' !== event.key || !installSearchDelay()) {
                return;
            }

            const searchName = section.dataset.listingSearchName;
            clearSearchDelay();
            skipNextSearchDelay = true;

            if (searchRequestIsCurrent(input)) {
                event.preventDefault();
                return;
            }

            if (window.FWP) {
                window.FWP.frozen_facets[searchName] = 'soft';
                window.FWP.autoload();
                event.preventDefault();
            }
        });

        document.addEventListener('facetwp-loaded', function () {
            installFacetwpAjaxHook();
            // FacetWP replaces the native selects after each refresh. Rebuild
            // their shared select presentation after its new markup is ready.
            queueMicrotask(function () {
                syncFacetSelects();
                syncSearchControls();
                syncResetControls();

                if (restoreFocusControl) {
                    const trigger = restoreFocusControl.querySelector('.ltt-select__trigger');

                    if (trigger) {
                        trigger.focus({ preventScroll: true });
                    }
                    restoreFocusControl = null;
                } else if (restoreSearchSelection) {
                    const input = section.querySelector('.facetwp-search');

                    if (input) {
                        input.focus({ preventScroll: true });
                        if (null !== restoreSearchSelection.start && null !== restoreSearchSelection.end) {
                            input.setSelectionRange(restoreSearchSelection.start, restoreSearchSelection.end);
                        }
                    }
                    restoreSearchSelection = null;
                } else if (restoreSearchButtonFocus) {
                    const button = section.querySelector('.facetwp-type-search .facetwp-icon');

                    if (button) {
                        button.focus({ preventScroll: true });
                    }
                    restoreSearchButtonFocus = false;
                } else if (restoreLoadMoreFocus) {
                    const button = section.querySelector('.facetwp-load-more:not(.facetwp-hidden)');

                    if (button) {
                        button.focus({ preventScroll: true });
                    } else if (status) {
                        status.focus({ preventScroll: true });
                    }
                    restoreLoadMoreFocus = false;
                }
            });

            grid.removeAttribute('aria-busy');

            if (awaitingRefresh && status) {
                if (!awaitingLoadMore) {
                    grid.scrollLeft = 0;
                }
                clearErrorStatus();
                announceVisibleResults();
                awaitingRefresh = false;
                awaitingLoadMore = false;
            }

            section.querySelectorAll('.facetwp-load-more').forEach(function (button) {
                button.disabled = false;
                button.dataset.listingLoading = 'false';
                if (!button.dataset.listingLabel) {
                    button.dataset.listingLabel = button.textContent.trim();
                }
            });
            loadMorePending = false;

            if (arrows) {
                update();
            }
        });

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
