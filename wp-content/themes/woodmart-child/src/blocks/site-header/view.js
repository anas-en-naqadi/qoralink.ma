/**
 * Qoralink Site Header Frontend Interactive Script (view.js)
 *
 * Block: qora/site-header
 * Target Path: wp-content/themes/woodmart-child/src/blocks/site-header/view.js
 * Compiled Output: wp-content/themes/woodmart-child/build/blocks/site-header/view.js
 * Author: Worker M2.1 (Based on Explorer M2.3 Specification)
 *
 * Responsibilities:
 * 1. Mobile Off-Canvas Drawer: Toggle open / close state
 * 2. Accessible Backdrop: Click & touch dismissal
 * 3. Keyboard Accessibility: Escape key dismissal & focus trap (WAI-ARIA WCAG 2.1 AA)
 * 4. Focus Restoration: Returns keyboard focus to trigger button upon close
 * 5. Body Scroll Locking: Adds/removes .qora-drawer-open to prevent background scroll
 * 6. Live WooCommerce Cart Fragments: Reactive badge counter sync and pulse animation
 */

(function () {
    'use strict';

    // Global singleton guard to prevent duplicate initialization
    // if script is loaded by both block.json viewScript and child theme enqueue
    if (window.__QORA_SITE_HEADER_INITIALIZED__) {
        return;
    }
    window.__QORA_SITE_HEADER_INITIALIZED__ = true;

    /**
     * Initializes the Qoralink Site Header frontend interactivity.
     */
    function initQoraSiteHeader() {
        const drawer = document.getElementById('qora-mobile-drawer') || document.querySelector('.qora-mobile-drawer');
        const backdrop = document.getElementById('qora-drawer-backdrop') || document.querySelector('.qora-drawer-backdrop');

        if (!drawer) {
            initLiveProductSearch();
            return;
        }

        // Hoist backdrop and drawer directly into document.body to prevent any
        // ancestor overflow, transform, or stacking context clipping issues.
        if (backdrop && backdrop.parentElement !== document.body) {
            document.body.appendChild(backdrop);
        }

        if (drawer.parentElement !== document.body) {
            document.body.appendChild(drawer);
        }

        let lastFocusedElement = null;
        let lastInteractionTime = 0;
        let drawerOpenedAt = 0;
        let isTransitioning = false;

        /**
         * Returns an array of keyboard-focusable DOM elements within a container.
         *
         * @param {HTMLElement} container
         * @returns {HTMLElement[]}
         */
        function getFocusableElements(container) {
            if (!container) return [];
            const selector = [
                'a[href]',
                'button:not([disabled])',
                'input:not([disabled])',
                'select:not([disabled])',
                'textarea:not([disabled])',
                '[tabindex]:not([tabindex="-1"])'
            ].join(', ');

            return Array.from(container.querySelectorAll(selector)).filter(function (el) {
                return el.offsetWidth > 0 || el.offsetHeight > 0 || el === document.activeElement;
            });
        }

        /**
         * Opens the mobile off-canvas drawer.
         */
        function openDrawer() {
            if (drawer.classList.contains('is-open')) return;

            lastFocusedElement = document.activeElement;
            drawerOpenedAt = Date.now();
            lastInteractionTime = Date.now();
            isTransitioning = true;

            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');

            if (backdrop) {
                backdrop.classList.add('is-open');
                backdrop.setAttribute('aria-hidden', 'false');
            }

            const toggles = document.querySelectorAll(
                '[data-toggle="qora-mobile-drawer"], .qora-site-header__mobile-toggle, .qora-mobile-toggle'
            );
            toggles.forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'true');
            });

            document.body.classList.add('qora-drawer-open');

            setTimeout(function () {
                isTransitioning = false;
            }, 350);

            // Shift focus into drawer
            const closeBtn = drawer.querySelector('.qora-drawer-close, .qora-mobile-drawer__close, [data-close-drawer]');
            requestAnimationFrame(function () {
                const focusables = getFocusableElements(drawer);
                if (closeBtn && typeof closeBtn.focus === 'function') {
                    closeBtn.focus();
                } else if (focusables.length > 0) {
                    focusables[0].focus();
                }
            });
        }

        /**
         * Closes the mobile off-canvas drawer.
         */
        function closeDrawer() {
            if (!drawer.classList.contains('is-open')) return;

            lastInteractionTime = Date.now();
            isTransitioning = true;

            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');

            if (backdrop) {
                backdrop.classList.remove('is-open');
                backdrop.setAttribute('aria-hidden', 'true');
            }

            const toggles = document.querySelectorAll(
                '[data-toggle="qora-mobile-drawer"], .qora-site-header__mobile-toggle, .qora-mobile-toggle'
            );
            toggles.forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });

            document.body.classList.remove('qora-drawer-open');

            setTimeout(function () {
                isTransitioning = false;
            }, 350);

            // Restore focus to original trigger element
            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        }

        /**
         * Toggles the drawer state with debounce and transition locking.
         *
         * @param {Event} [e]
         */
        function handleToggle(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const now = Date.now();
            if (now - lastInteractionTime < 300 || isTransitioning) {
                return;
            }

            if (drawer.classList.contains('is-open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        }

        // Direct binding to toggle buttons
        function bindToggleButtons() {
            const toggles = document.querySelectorAll(
                '[data-toggle="qora-mobile-drawer"], .qora-site-header__mobile-toggle, .qora-mobile-toggle'
            );
            toggles.forEach(function (btn) {
                if (btn.__qoraToggleBound) return;
                btn.__qoraToggleBound = true;

                btn.addEventListener('click', handleToggle);
                btn.addEventListener('touchend', function (e) {
                    handleToggle(e);
                }, { passive: false });
            });
        }
        bindToggleButtons();

        // Delegated click handler on document for maximum reliability
        document.addEventListener('click', function (e) {
            const toggle = e.target.closest(
                '[data-toggle="qora-mobile-drawer"], .qora-site-header__mobile-toggle, .qora-mobile-toggle'
            );
            if (toggle) {
                handleToggle(e);
                return;
            }

            // Close button inside drawer (strictly not the backdrop itself)
            const closeTarget = e.target.closest(
                '[data-close-drawer], [data-dismiss="drawer"], .qora-drawer-close, .qora-mobile-drawer__close'
            );
            if (closeTarget && closeTarget !== backdrop && drawer.classList.contains('is-open')) {
                e.preventDefault();
                e.stopPropagation();
                closeDrawer();
                return;
            }
        });

        // Backdrop click & touch dismissal with ghost-click shield
        if (backdrop) {
            function handleBackdropDismissal(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                const now = Date.now();
                // CRITICAL: Ignore any backdrop click/touch within 450ms of opening!
                // This eliminates synthetic click bleed-through from the initial tap.
                if (now - drawerOpenedAt < 450 || isTransitioning) {
                    return;
                }
                closeDrawer();
            }

            backdrop.addEventListener('click', handleBackdropDismissal);
            backdrop.addEventListener('touchend', handleBackdropDismissal, { passive: false });
        }

        // Keyboard handling: Escape key dismissal & Focus Trapping
        document.addEventListener('keydown', function (e) {
            if (!drawer.classList.contains('is-open')) {
                return;
            }

            // 1. Escape key dismissal
            if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
                e.preventDefault();
                closeDrawer();
                return;
            }

            // 2. Focus Trap on Tab key
            if (e.key === 'Tab' || e.keyCode === 9) {
                const focusables = getFocusableElements(drawer);
                if (focusables.length === 0) {
                    e.preventDefault();
                    return;
                }

                const firstEl = focusables[0];
                const lastEl = focusables[focusables.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === firstEl) {
                        e.preventDefault();
                        lastEl.focus();
                    }
                } else {
                    if (document.activeElement === lastEl) {
                        e.preventDefault();
                        firstEl.focus();
                    }
                }
            }
        });

        // Auto-close on viewport expansion beyond tablet breakpoint (>= 1025px)
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1025 && drawer.classList.contains('is-open')) {
                closeDrawer();
            }
        });

        // ---------------------------------------------------------------------
        // Mobile Drawer Category Accordions
        // ---------------------------------------------------------------------
        function initMobileCategoryAccordions() {
            drawer.addEventListener('click', function (e) {
                const toggleBtn = e.target.closest('.qora-mobile-accordion-toggle');
                if (!toggleBtn) return;

                e.preventDefault();
                e.stopPropagation();

                const isSub = toggleBtn.classList.contains('qora-mobile-accordion-toggle--sub');
                const parentLi = isSub
                    ? toggleBtn.closest('.qora-mobile-submenu__item')
                    : toggleBtn.closest('.qora-mobile-menu__item');

                if (!parentLi) return;

                const submenu = isSub
                    ? parentLi.querySelector('.qora-mobile-subsubmenu')
                    : parentLi.querySelector('.qora-mobile-submenu');

                if (!submenu) return;

                const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true' || parentLi.classList.contains('is-open');

                if (isExpanded) {
                    // Collapse
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    parentLi.classList.remove('is-open');

                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    void submenu.offsetHeight; // trigger reflow
                    submenu.style.maxHeight = '0px';
                    submenu.style.opacity = '0';

                    setTimeout(function () {
                        if (!parentLi.classList.contains('is-open')) {
                            submenu.setAttribute('hidden', '');
                            submenu.style.display = 'none';
                        }
                    }, 240);
                } else {
                    // Expand
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    parentLi.classList.add('is-open');

                    submenu.removeAttribute('hidden');
                    submenu.style.display = 'block';
                    submenu.style.maxHeight = '0px';
                    submenu.style.opacity = '0';
                    void submenu.offsetHeight; // trigger reflow

                    submenu.style.maxHeight = (submenu.scrollHeight + 30) + 'px';
                    submenu.style.opacity = '1';

                    // If nested within level 1 submenu, expand parent container maxHeight as well
                    if (isSub) {
                        const grandParentSub = parentLi.closest('.qora-mobile-submenu');
                        if (grandParentSub && grandParentSub.style.maxHeight && grandParentSub.style.maxHeight !== 'none') {
                            grandParentSub.style.maxHeight = (grandParentSub.scrollHeight + submenu.scrollHeight + 40) + 'px';
                        }
                    }

                    setTimeout(function () {
                        if (parentLi.classList.contains('is-open')) {
                            submenu.style.maxHeight = 'none';
                            if (isSub) {
                                const grandParentSub = parentLi.closest('.qora-mobile-submenu');
                                if (grandParentSub) grandParentSub.style.maxHeight = 'none';
                            }
                        }
                    }, 260);
                }
            });

            // Ensure all category submenus start strictly closed on initial sidebar open
            drawer.querySelectorAll('.qora-mobile-menu__item, .qora-mobile-submenu__item').forEach(function (li) {
                li.classList.remove('is-open');
            });
            drawer.querySelectorAll('.qora-mobile-accordion-toggle').forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
            drawer.querySelectorAll('.qora-mobile-submenu, .qora-mobile-subsubmenu').forEach(function (sub) {
                sub.setAttribute('hidden', '');
                sub.style.display = 'none';
                sub.style.maxHeight = '0px';
                sub.style.opacity = '0';
            });
        }
        initMobileCategoryAccordions();

        // ---------------------------------------------------------------------
        // WooCommerce Live Cart Fragment Reactivity
        // ---------------------------------------------------------------------

        /**
         * Ensures cart badges only show numeric count and triggers pulse animation.
         */
        function pulseCartBadges() {
            const badges = document.querySelectorAll(
                '.qora-cart-badge, .qora-cart-count'
            );
            badges.forEach(function (badge) {
                const match = (badge.textContent || '').match(/\d+/);
                badge.textContent = match ? match[0] : '0';
                badge.classList.remove('is-updated');
                void badge.offsetWidth;
                badge.classList.add('is-updated');
            });
        }

        pulseCartBadges();

        // Listen for WooCommerce jQuery fragment update events
        if (window.jQuery) {
            window.jQuery(document.body).on(
                'added_to_cart removed_from_cart wc_fragments_refreshed wc_fragments_loaded',
                function (event, fragments) {
                    pulseCartBadges();
                }
            );
        }

        // Native DOM custom event listeners (for vanilla AJAX or custom dispatches)
        document.addEventListener('wc_fragments_refreshed', pulseCartBadges);
        document.addEventListener('added_to_cart', pulseCartBadges);
        document.addEventListener('removed_from_cart', pulseCartBadges);

        // Cross-tab synchronization via localStorage
        window.addEventListener('storage', function (e) {
            if (e.key && e.key.indexOf('wc_cart_hash') !== -1) {
                pulseCartBadges();
            }
        });

        // ---------------------------------------------------------------------
        // Cart Side Drawer Opener (opens sidebar without page refresh)
        // ---------------------------------------------------------------------
        document.addEventListener('click', function (e) {
            const opener = e.target.closest('.cart-widget-opener, .qora-cart-link');
            if (opener) {
                if (document.body.classList.contains('woocommerce-cart') || document.body.classList.contains('woocommerce-checkout')) {
                    return;
                }
                e.preventDefault();

                if (typeof closeDrawer === 'function') {
                    closeDrawer();
                }

                const sideCart = document.querySelector('.cart-widget-side');
                const closeSide = document.querySelector('.wd-close-side');
                if (sideCart) {
                    sideCart.classList.add('wd-opened');
                    if (closeSide) {
                        closeSide.classList.add('wd-close-side-opened');
                    }
                    if (window.jQuery) {
                        window.jQuery(sideCart).trigger('wdOpenSide');
                    }
                }
            }

            if (e.target.closest('.close-side-widget') || e.target.closest('.wd-close-side')) {
                const sideCart = document.querySelector('.cart-widget-side');
                const closeSide = document.querySelector('.wd-close-side');
                if (sideCart) {
                    sideCart.classList.remove('wd-opened');
                    if (window.jQuery) {
                        window.jQuery(sideCart).trigger('wdCloseSide');
                    }
                }
                if (closeSide) {
                    closeSide.classList.remove('wd-close-side-opened');
                }
            }
        });

        // ---------------------------------------------------------------------
        // Auto-open Cart Drawer on Product Card "Add to Cart" + Remove "Voir le panier"
        // ---------------------------------------------------------------------
        function openCartSideDrawer() {
            if (document.body.classList.contains('woocommerce-cart') || document.body.classList.contains('woocommerce-checkout')) {
                return;
            }
            const sideCart = document.querySelector('.cart-widget-side');
            const closeSide = document.querySelector('.wd-close-side');
            if (sideCart) {
                sideCart.classList.add('wd-opened');
                if (closeSide) {
                    closeSide.classList.add('wd-close-side-opened');
                }
                if (window.jQuery) {
                    window.jQuery(sideCart).trigger('wdOpenSide');
                }
            }
        }

        function cleanAddedToCartLinks() {
            document.querySelectorAll('.added_to_cart, .qora-showcase-card .added_to_cart').forEach(function (el) {
                el.remove();
            });
            document.querySelectorAll('.qora-showcase-card__cart-btn.loading').forEach(function (btn) {
                btn.classList.remove('loading');
            });
        }

        document.addEventListener('click', function (e) {
            const cartBtn = e.target.closest('.qora-showcase-card__cart-btn.ajax_add_to_cart');
            if (cartBtn) {
                cartBtn.classList.add('loading');
            }
        });

        if (window.jQuery) {
            window.jQuery(document.body).on('added_to_cart', function () {
                cleanAddedToCartLinks();
                setTimeout(function () {
                    openCartSideDrawer();
                    cleanAddedToCartLinks();
                }, 100);
            });
        }
        document.addEventListener('added_to_cart', function () {
            cleanAddedToCartLinks();
            setTimeout(function () {
                openCartSideDrawer();
                cleanAddedToCartLinks();
            }, 100);
        });

        // ---------------------------------------------------------------------
        // Live WooCommerce Product Search Autocomplete (5 items + Enter/Click submit)
        // ---------------------------------------------------------------------
        initLiveProductSearch();
    }

    /**
     * Escapes HTML special characters for safe DOM insertion.
     *
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Escapes regex special characters.
     *
     * @param {string} str
     * @returns {string}
     */
    function escapeRegExp(str) {
        return String(str || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Wraps matching search query substrings in <mark class="qora-search-highlight">.
     *
     * @param {string} text
     * @param {string} query
     * @returns {string}
     */
    function highlightMatch(text, query) {
        const safeText = escapeHtml(text);
        const cleanQuery = String(query || '').trim();
        if (!cleanQuery) {
            return safeText;
        }
        try {
            const pattern = new RegExp('(' + escapeRegExp(cleanQuery) + ')', 'gi');
            return safeText.replace(pattern, '<mark class="qora-search-highlight">$1</mark>');
        } catch (err) {
            return safeText;
        }
    }

    /**
     * Initializes live product autocomplete on all Qoralink search bars
     * (both desktop header and mobile drawer).
     */
    function initLiveProductSearch() {
        const config = window.qoraHeaderConfig || {};
        const limit = Number(config.limit) || 5;
        const minChars = 1;
        const i18n = Object.assign(
            {
                searching: 'Searching products...',
                noResults: 'No products found for',
                viewAll: 'View all results',
                suggestions: 'Suggested Products'
            },
            config.i18n || {}
        );

        const searchContainers = document.querySelectorAll(
            '[data-qora-live-search], .qora-header-search, .qora-drawer-search'
        );
        if (!searchContainers.length) {
            return;
        }

        const queryCache = new Map();

        /**
         * Fetches matching products from REST API with AJAX fallback.
         *
         * @param {string} term
         * @param {AbortSignal} signal
         * @returns {Promise<Object>}
         */
        function fetchProducts(term, signal) {
            const cacheKey = term.toLowerCase();
            if (queryCache.has(cacheKey)) {
                return Promise.resolve(queryCache.get(cacheKey));
            }

            const restBase = config.restUrl || '/wp-json/qora/v1/search-products';
            const sep = restBase.indexOf('?') !== -1 ? '&' : '?';
            const url = restBase + sep + 'q=' + encodeURIComponent(term) + '&limit=' + encodeURIComponent(limit);

            return fetch(url, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                signal: signal
            })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('REST status ' + res.status);
                    }
                    return res.json();
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') {
                        throw err;
                    }
                    // Fallback to admin-ajax if REST route is blocked
                    const ajaxBase = config.ajaxUrl || '/wp-admin/admin-ajax.php';
                    const ajaxSep = ajaxBase.indexOf('?') !== -1 ? '&' : '?';
                    const fallbackUrl =
                        ajaxBase +
                        ajaxSep +
                        'action=qora_live_product_search&q=' +
                        encodeURIComponent(term) +
                        '&limit=' +
                        encodeURIComponent(limit);

                    return fetch(fallbackUrl, {
                        method: 'GET',
                        headers: { Accept: 'application/json' },
                        signal: signal
                    })
                        .then(function (r) {
                            return r.json();
                        })
                        .then(function (json) {
                            return json && json.data ? json.data : json;
                        });
                })
                .then(function (data) {
                    if (data && Array.isArray(data.items)) {
                        queryCache.set(cacheKey, data);
                    }
                    return data;
                });
        }

        searchContainers.forEach(function (container, containerIdx) {
            const form = container.querySelector('form');
            const input = container.querySelector('.qora-search-field, input[name="s"]');
            const wrapper = container.querySelector('.qora-search-input-wrapper');
            if (!form || !input || !wrapper) {
                return;
            }

            let clearBtn = container.querySelector('.qora-search-clear');
            if (!clearBtn) {
                clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'qora-search-clear';
                clearBtn.setAttribute('aria-label', 'Clear search');
                clearBtn.hidden = true;
                clearBtn.innerHTML =
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                wrapper.insertBefore(clearBtn, wrapper.querySelector('.qora-search-submit'));
            }

            let dropdown = container.querySelector('.qora-search-dropdown');
            if (!dropdown) {
                dropdown = document.createElement('div');
                dropdown.id = 'qora-search-listbox-' + containerIdx;
                dropdown.className = 'qora-search-dropdown';
                dropdown.setAttribute('role', 'listbox');
                dropdown.hidden = true;
                form.appendChild(dropdown);
            }

            let debounceTimer = null;
            let abortController = null;
            let activeIndex = -1;
            let currentOptions = [];

            function updateClearButton() {
                const hasText = input.value.trim().length > 0;
                clearBtn.hidden = !hasText;
            }

            function closeDropdown() {
                dropdown.hidden = true;
                dropdown.classList.remove('is-open');
                input.setAttribute('aria-expanded', 'false');
                input.removeAttribute('aria-activedescendant');
                activeIndex = -1;
                currentOptions = [];
            }

            function openDropdown() {
                dropdown.hidden = false;
                dropdown.classList.add('is-open');
                input.setAttribute('aria-expanded', 'true');
            }

            function setHighlight(index) {
                currentOptions = Array.from(
                    dropdown.querySelectorAll('.qora-search-item, .qora-search-view-all')
                );
                currentOptions.forEach(function (opt, idx) {
                    if (idx === index) {
                        opt.classList.add('is-active');
                        opt.setAttribute('aria-selected', 'true');
                        if (opt.id) {
                            input.setAttribute('aria-activedescendant', opt.id);
                        }
                        opt.scrollIntoView({ block: 'nearest' });
                    } else {
                        opt.classList.remove('is-active');
                        opt.setAttribute('aria-selected', 'false');
                    }
                });
                if (index < 0) {
                    input.removeAttribute('aria-activedescendant');
                }
            }

            function renderLoading() {
                dropdown.innerHTML =
                    '<div class="qora-search-status qora-search-status--loading">' +
                    '<span class="qora-search-status__dot"></span>' +
                    '<span>' + escapeHtml(i18n.searching) + '</span>' +
                    '</div>';
                openDropdown();
            }

            function renderResults(data, rawQuery) {
                const items = data && Array.isArray(data.items) ? data.items.slice(0, limit) : [];
                const total = data && typeof data.total === 'number' ? data.total : items.length;
                const searchUrl =
                    (data && data.search_url) ||
                    (form.getAttribute('action') || '/') +
                        '?s=' +
                        encodeURIComponent(rawQuery) +
                        '&post_type=product';

                activeIndex = -1;

                if (!items.length) {
                    dropdown.innerHTML =
                        '<div class="qora-search-status qora-search-status--empty">' +
                        '<p class="qora-search-empty-title">' +
                        escapeHtml(i18n.noResults) +
                        ' <strong>&ldquo;' +
                        escapeHtml(rawQuery) +
                        '&rdquo;</strong></p>' +
                        '</div>';
                    openDropdown();
                    currentOptions = [];
                    return;
                }

                let html =
                    '<div class="qora-search-dropdown__header">' +
                    '<span class="qora-search-dropdown__heading">' +
                    escapeHtml(i18n.suggestions) +
                    '</span>' +
                    '<span class="qora-search-dropdown__count">' +
                    escapeHtml(String(items.length)) +
                    ' / ' +
                    escapeHtml(String(total)) +
                    '</span>' +
                    '</div>' +
                    '<div class="qora-search-dropdown__list">';

                items.forEach(function (item, idx) {
                    const optId = dropdown.id + '-opt-' + idx;
                    const brandHtml = item.brand
                        ? '<span class="qora-search-item__brand">' + escapeHtml(item.brand) + '</span>'
                        : '';
                    const specsHtml = item.specs
                        ? '<span class="qora-search-item__specs">' + escapeHtml(item.specs) + '</span>'
                        : '';
                    const priceHtml = item.price_html
                        ? '<span class="qora-search-item__price">' + item.price_html + '</span>'
                        : '';

                    html +=
                        '<a id="' +
                        escapeHtml(optId) +
                        '" href="' +
                        escapeHtml(item.url) +
                        '" class="qora-search-item" role="option" aria-selected="false">' +
                        '<span class="qora-search-item__thumb">' +
                        '<img src="' +
                        escapeHtml(item.image) +
                        '" alt="' +
                        escapeHtml(item.title) +
                        '" loading="lazy" />' +
                        '</span>' +
                        '<span class="qora-search-item__info">' +
                        brandHtml +
                        '<span class="qora-search-item__title">' +
                        highlightMatch(item.title, rawQuery) +
                        '</span>' +
                        specsHtml +
                        '</span>' +
                        '<span class="qora-search-item__meta">' +
                        priceHtml +
                        '</span>' +
                        '</a>';
                });

                html += '</div>';

                const viewAllId = dropdown.id + '-opt-viewall';
                html +=
                    '<a id="' +
                    escapeHtml(viewAllId) +
                    '" href="' +
                    escapeHtml(searchUrl) +
                    '" class="qora-search-view-all" role="option" aria-selected="false">' +
                    '<span>' +
                    escapeHtml(i18n.viewAll) +
                    ' &ldquo;<strong>' +
                    escapeHtml(rawQuery) +
                    '</strong>&rdquo; (' +
                    escapeHtml(String(total)) +
                    ')</span>' +
                    '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
                    '<line x1="5" y1="12" x2="19" y2="12"></line>' +
                    '<polyline points="12 5 19 12 12 19"></polyline>' +
                    '</svg>' +
                    '</a>';

                dropdown.innerHTML = html;
                openDropdown();
                currentOptions = Array.from(
                    dropdown.querySelectorAll('.qora-search-item, .qora-search-view-all')
                );
            }

            function triggerSearch(rawVal) {
                const term = String(rawVal || '').trim();
                updateClearButton();

                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }
                if (abortController) {
                    abortController.abort();
                    abortController = null;
                }

                if (term.length < minChars) {
                    wrapper.classList.remove('is-loading');
                    closeDropdown();
                    return;
                }

                debounceTimer = setTimeout(function () {
                    abortController = new AbortController();
                    wrapper.classList.add('is-loading');

                    if (dropdown.hidden) {
                        renderLoading();
                    }

                    fetchProducts(term, abortController.signal)
                        .then(function (data) {
                            wrapper.classList.remove('is-loading');
                            if (input.value.trim() !== term) {
                                return;
                            }
                            renderResults(data, term);
                        })
                        .catch(function (err) {
                            if (err && err.name === 'AbortError') {
                                return;
                            }
                            wrapper.classList.remove('is-loading');
                            closeDropdown();
                        });
                }, 200);
            }

            // Input typing listener
            input.addEventListener('input', function () {
                activeIndex = -1;
                triggerSearch(input.value);
            });

            // Re-open cached suggestions when focusing an input that already has text
            input.addEventListener('focus', function () {
                updateClearButton();
                const term = input.value.trim();
                if (term.length >= minChars) {
                    if (dropdown.innerHTML.trim() !== '') {
                        openDropdown();
                    } else {
                        triggerSearch(term);
                    }
                }
            });

            // Clear button click
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                input.value = '';
                updateClearButton();
                wrapper.classList.remove('is-loading');
                closeDropdown();
                input.focus();
            });

            // Keyboard navigation (ArrowDown, ArrowUp, Enter, Escape)
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' || e.key === 'Esc') {
                    if (!dropdown.hidden) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeDropdown();
                    }
                    return;
                }

                if (e.key === 'ArrowDown') {
                    if (dropdown.hidden) {
                        if (input.value.trim().length >= minChars) {
                            triggerSearch(input.value);
                        }
                        return;
                    }
                    if (currentOptions.length > 0) {
                        e.preventDefault();
                        activeIndex = activeIndex + 1 >= currentOptions.length ? 0 : activeIndex + 1;
                        setHighlight(activeIndex);
                    }
                    return;
                }

                if (e.key === 'ArrowUp') {
                    if (!dropdown.hidden && currentOptions.length > 0) {
                        e.preventDefault();
                        activeIndex = activeIndex - 1 < -1 ? currentOptions.length - 1 : activeIndex - 1;
                        setHighlight(activeIndex);
                    }
                    return;
                }

                if (e.key === 'Enter') {
                    // If user highlighted a specific item in the dropdown with arrow keys, navigate to it.
                    // Otherwise (user didn't click/highlight a product and hit Enter), let the form submit!
                    if (!dropdown.hidden && activeIndex >= 0 && currentOptions[activeIndex]) {
                        e.preventDefault();
                        const targetHref = currentOptions[activeIndex].getAttribute('href');
                        if (targetHref) {
                            window.location.href = targetHref;
                        }
                    }
                }
            });

            // Form submit handler (Enter without product selection OR Search Icon click)
            form.addEventListener('submit', function (e) {
                const term = input.value.trim();
                if (!term) {
                    e.preventDefault();
                    input.focus();
                    return;
                }
                input.value = term;
                closeDropdown();
            });

            // Dismiss dropdown when clicking outside this search container
            document.addEventListener('click', function (e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            updateClearButton();
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQoraSiteHeader);
    } else {
        initQoraSiteHeader();
    }
})();

