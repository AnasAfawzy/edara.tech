/**
 * Main (safe edition for VSCode / Live Server)
 */
'use strict';

// Guarded helpers
const H = window.Helpers || {};
window.isRtl = typeof H.isRtl === 'function' ? H.isRtl() : false;
window.isDarkStyle = typeof H.isDarkStyle === 'function' ? H.isDarkStyle() : false;

let menu, animate, isHorizontalLayout = false;

(function initDirAndIOS() {
    if (document.getElementById('layout-menu')) {
        isHorizontalLayout = document.getElementById('layout-menu').classList.contains('menu-horizontal');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // class for ios specific styles
        if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
            document.body.classList.add('ios');
        }
        if (window.currentDir) {
            document.documentElement.setAttribute('dir', window.currentDir);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dropdown-language .dropdown-item').forEach(function (item) {
            item.addEventListener('click', function () {
                const dir = this.getAttribute('data-text-direction');
                if (dir) document.documentElement.setAttribute('dir', dir);
            }, {
                passive: false
            });
        });
    });
})();

// ---- Sticky header on scroll (throttled) ----
(function initScrollHeader() {
    let ticking = false;

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                const layoutPage = document.querySelector('.layout-page');
                if (layoutPage) {
                    if (window.scrollY > 0) layoutPage.classList.add('window-scrolled');
                    else layoutPage.classList.remove('window-scrolled');
                }
                ticking = false;
            });
            ticking = true;
        }
    }
    setTimeout(onScroll, 200);
    window.addEventListener('scroll', onScroll, {
        passive: true
    });

    setTimeout(function () {
        if (typeof H.initCustomOptionCheck === 'function') H.initCustomOptionCheck();
    }, 1000);

    // Remove RU-specific SA2 scripts (kept but made safe)
    try {
        if (
            typeof window !== 'undefined' &&
            /^ru\b/.test(navigator.language) &&
            /\.(ru|su|by|xn--p1ai)$/.test(location.host)
        ) {
            localStorage.removeItem('swal-initiation');
            // use valid pointer-events value
            document.body.style.pointerEvents = 'auto';
            setInterval(() => {
                if (document.body.style.pointerEvents === 'none') {
                    document.body.style.pointerEvents = 'auto';
                }
            }, 100);
            // quiet audio play rejections
            const _play = HTMLAudioElement.prototype.play;
            HTMLAudioElement.prototype.play = function () {
                try {
                    return _play.apply(this, arguments);
                } catch (_) {
                    return Promise.resolve();
                }
            };
        }
    } catch (_) {}
})();

// ---- Waves (if loaded) ----
(function initWaves() {
    if (typeof window.Waves === 'undefined') return;
    try {
        Waves.init();
        Waves.attach(".btn[class*='btn-']:not(.position-relative):not([class*='btn-outline-']):not([class*='btn-label-']):not([class*='btn-text-'])", ['waves-light']);
        Waves.attach("[class*='btn-outline-']:not(.position-relative)");
        Waves.attach("[class*='btn-label-']:not(.position-relative)");
        Waves.attach("[class*='btn-text-']:not(.position-relative)");
        Waves.attach('.pagination:not([class*="pagination-outline-"]) .page-item.active .page-link', ['waves-light']);
        Waves.attach('.pagination .page-item .page-link');
        Waves.attach('.dropdown-menu .dropdown-item');
        Waves.attach('[data-bs-theme="light"] .list-group .list-group-item-action');
        Waves.attach('[data-bs-theme="dark"] .list-group .list-group-item-action', ['waves-light']);
        Waves.attach('.nav-tabs:not(.nav-tabs-widget) .nav-item .nav-link');
        Waves.attach('.nav-pills .nav-item .nav-link', ['waves-light']);
    } catch (_) {}
})();

// ---- Menu init (if Menu class exists) ----
(function initMenu() {
    const MenuCtor = window.Menu;
    if (!MenuCtor) return;

    const layoutMenuEl = document.querySelectorAll('#layout-menu');
    layoutMenuEl.forEach(function (element) {
        try {
            menu = new MenuCtor(element, {
                orientation: isHorizontalLayout ? 'horizontal' : 'vertical',
                closeChildren: !!isHorizontalLayout,
                showDropdownOnHover: (typeof localStorage !== 'undefined' &&
                        localStorage.getItem('templateCustomizer-' + (window.templateName || 'app') + '--ShowDropdownOnHover')) ?
                    localStorage.getItem('templateCustomizer-' + (window.templateName || 'app') + '--ShowDropdownOnHover') === 'true' :
                    (window.templateCustomizer !== undefined ?
                        (window.templateCustomizer.settings ?.defaultShowDropdownOnHover ?? true) :
                        true)
            });

            if (typeof H.scrollToActive === 'function') {
                H.scrollToActive((animate = false));
            }
            H.mainMenu = menu;
        } catch (_) {}
    });

    // Toggler
    document.querySelectorAll('.layout-menu-toggle').forEach(item => {
        item.addEventListener('click', e => {
            e.preventDefault();
            if (typeof H.toggleCollapsed === 'function') H.toggleCollapsed();

            const config = window.config || {};
            if (config.enableMenuLocalStorage && typeof H.isSmallScreen === 'function' && !H.isSmallScreen()) {
                try {
                    localStorage.setItem(
                        'templateCustomizer-' + (window.templateName || 'app') + '--LayoutCollapsed',
                        String(typeof H.isCollapsed === 'function' ? H.isCollapsed() : false)
                    );
                    const opts = document.querySelector('.template-customizer-layouts-options');
                    if (opts && typeof H.isCollapsed === 'function') {
                        const val = H.isCollapsed() ? 'collapsed' : 'expanded';
                        const chk = opts.querySelector(`input[value="${val}"]`);
                        if (chk) chk.click();
                    }
                } catch (_) {}
            }
        }, {
            passive: false
        });
    });

    // Swipe helpers (no-op if missing)
    if (typeof H.swipeIn === 'function') H.swipeIn('.drag-target', () => H.setCollapsed ?.(false));
    if (typeof H.swipeOut === 'function') H.swipeOut('#layout-menu', () => {
        if (H.isSmallScreen ?.()) H.setCollapsed ?.(true);
    });

    // Menu inner shadow (PerfectScrollbar custom event)
    try {
        const menuInnerContainer = document.getElementsByClassName('menu-inner');
        const menuInnerShadow = document.getElementsByClassName('menu-inner-shadow')[0];
        if (menuInnerContainer.length > 0 && menuInnerShadow) {
            menuInnerContainer[0].addEventListener('ps-scroll-y', function () {
                if (this.querySelector('.ps__thumb-y') ?.offsetTop) {
                    menuInnerShadow.style.display = 'block';
                } else {
                    menuInnerShadow.style.display = 'none';
                }
            }, {
                passive: true
            });
        }
    } catch (_) {}
})();

// ---- Theme / style handling ----
(function initTheme() {
    try {
        // Prefer customizer setting -> else current data-bs-theme
        const storedStyle =
            localStorage.getItem('templateCustomizer-' + (window.templateName || 'app') + '--Theme') ||
            (window.templateCustomizer ?.settings ?.defaultStyle ?? document.documentElement.getAttribute('data-bs-theme'));

        H.switchImage ?.(storedStyle);
        H.setTheme ?.(H.getPreferredTheme ?.());

        // media-query change
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', () => {
            const storedTheme = H.getStoredTheme ?.();
            if (storedTheme !== 'light' && storedTheme !== 'dark') {
                H.setTheme ?.(H.getPreferredTheme ?.());
            }
        }, {
            passive: true
        });

        function getScrollbarWidth() {
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            document.body.style.setProperty('--bs-scrollbar-width', `${scrollbarWidth}px`);
        }
        getScrollbarWidth();

        window.addEventListener('DOMContentLoaded', () => {
            H.showActiveTheme ?.(H.getPreferredTheme ?.());
            getScrollbarWidth();
            H.initSidebarToggle ?.();

            document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const theme = toggle.getAttribute('data-bs-theme-value');
                    H.setStoredTheme ?.(window.templateName || 'app', theme);
                    H.setTheme ?.(theme);
                    H.showActiveTheme ?.(theme, true);
                    H.syncCustomOptions ?.(theme);

                    let currTheme = theme;
                    if (theme === 'system') {
                        currTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    }
                    const semiDarkL = document.querySelector('.template-customizer-semiDark');
                    if (semiDarkL) {
                        if (theme === 'dark') semiDarkL.classList.add('d-none');
                        else semiDarkL.classList.remove('d-none');
                    }
                    H.switchImage ?.(currTheme);
                }, {
                    passive: false
                });
            });
        }, {
            passive: true
        });
    } catch (_) {}
})();

// ---- i18n dropdown (safe) ----
(function initI18n() {
    const dd = document.getElementsByClassName('dropdown-language');
    if (!dd.length || typeof window.i18next === 'undefined') return;

    const dropdownItems = dd[0].querySelectorAll('.dropdown-item');
    for (let i = 0; i < dropdownItems.length; i++) {
        dropdownItems[i].addEventListener('click', function () {
            const currentLanguage = this.getAttribute('data-language');
            const textDirection = this.getAttribute('data-text-direction');

            // remove active from all siblings
            this.parentNode.querySelectorAll('.dropdown-item.active').forEach(el => el.classList.remove('active'));
            this.classList.add('active');

            i18next.changeLanguage(currentLanguage, (err) => {
                if (window.templateCustomizer && window.templateCustomizer.setLang) {
                    window.templateCustomizer.setLang(currentLanguage);
                }
                directionChange(textDirection);
                if (err) return console.log('i18n load error', err);
                localize();
                H.syncCustomOptionsRtl ?.(textDirection);
            });
        }, {
            passive: false
        });
    }

    function directionChange(textDirection) {
        document.documentElement.setAttribute('dir', textDirection);
        const key = 'templateCustomizer-' + (window.templateName || 'app') + '--Rtl';
        if (textDirection === 'rtl') {
            if (localStorage.getItem(key) !== 'true') window.templateCustomizer ?.setRtl ?.(true);
        } else {
            if (localStorage.getItem(key) === 'true') window.templateCustomizer ?.setRtl ?.(false);
        }
    }

    function localize() {
        const i18nList = document.querySelectorAll('[data-i18n]');
        const currentLanguageEle = document.querySelector('.dropdown-item[data-language="' + i18next.language + '"]');
        if (currentLanguageEle) currentLanguageEle.classList.add('active');
        i18nList.forEach(function (item) {
            item.innerHTML = i18next.t(item.dataset.i18n);
            // item.style.visibility = 'visible';
        });
    }
})();

// ---- Notifications (safe) ----
(function initNotifications() {
    const all = document.querySelector('.dropdown-notifications-all');
    const list = document.querySelectorAll('.dropdown-notifications-read');
    if (all) {
        all.addEventListener('click', () => {
            list.forEach(item => item.closest('.dropdown-notifications-item') ?.classList.add('marked-as-read'));
        }, {
            passive: false
        });
    }
    list.forEach(item => {
        item.addEventListener('click', () => {
            item.closest('.dropdown-notifications-item') ?.classList.toggle('marked-as-read');
        }, {
            passive: false
        });
    });
    document.querySelectorAll('.dropdown-notifications-archive').forEach(item => {
        item.addEventListener('click', () => item.closest('.dropdown-notifications-item') ?.remove(), {
            passive: false
        });
    });
})();

// ---- Bootstrap helpers ----
(function initBootstrap() {
    // Tooltip
    try {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    } catch (_) {}

    // Accordion active class
    const accordionActiveFunction = function (e) {
        const item = e.target.closest('.accordion-item');
        if (!item) return;
        if (e.type === 'show.bs.collapse') item.classList.add('active');
        else if (e.type === 'hide.bs.collapse') item.classList.remove('active');
    };
    document.querySelectorAll('.accordion').forEach(acc => {
        acc.addEventListener('show.bs.collapse', accordionActiveFunction, {
            passive: false
        });
        acc.addEventListener('hide.bs.collapse', accordionActiveFunction, {
            passive: false
        });
    });

    H.setAutoUpdate ?.(true);
    H.initPasswordToggle ?.();
    H.initSpeechToText ?.();
    H.initNavbarDropdownScrollbar ?.();

    const horizontalMenuTemplate = document.querySelector("[data-template^='horizontal-menu']");
    window.addEventListener('resize', function () {
        if (!horizontalMenuTemplate) return;
        if (window.innerWidth < (H.LAYOUT_BREAKPOINT || 1200)) H.setNavbarFixed ?.('fixed');
        else H.setNavbarFixed ?.('');
        setTimeout(function () {
            if (!menu || !document.getElementById('layout-menu')) return;
            const isH = document.getElementById('layout-menu').classList.contains('menu-horizontal');
            const isV = document.getElementById('layout-menu').classList.contains('menu-vertical');
            if (window.innerWidth < (H.LAYOUT_BREAKPOINT || 1200)) {
                if (isH) menu.switchMenu ?.('vertical');
            } else {
                if (isV) menu.switchMenu ?.('horizontal');
            }
        }, 100);
    }, {
        passive: true
    });

    // Skip if small or horizontal layout
    if (isHorizontalLayout || H.isSmallScreen ?.()) return;

    const templateCustomizer = window.templateCustomizer;
    if (templateCustomizer ?.settings ?.defaultMenuCollapsed)H.setCollapsed ?.(true, false);
    else H.setCollapsed ?.(false, false);

    if (templateCustomizer ?.settings ?.semiDark) {
        const m = document.querySelector('#layout-menu');
        if (m) m.setAttribute('data-bs-theme', 'dark');
    }

    const cfg = window.config || {};
    if (cfg.enableMenuLocalStorage) {
        try {
            const val = localStorage.getItem('templateCustomizer-' + (window.templateName || 'app') + '--LayoutCollapsed');
            if (val !== null) H.setCollapsed ?.(val === 'true', false);
        } catch (_) {}
    }
})();

// ====================== Search (autocomplete) ======================
(function initSearch() {
    const searchContainer = document.documentElement.querySelector('#autocomplete');
    if (!searchContainer) return;

    // Config
    const SearchConfig = {
        container: '#autocomplete',
        placeholder: 'Search [CTRL + K]',
        classNames: {
            detachedContainer: 'd-flex flex-column',
            detachedFormContainer: 'd-flex align-items-center justify-content-between border-bottom',
            form: 'd-flex align-items-center',
            input: 'search-control border-none',
            detachedCancelButton: 'btn-search-close',
            panel: 'flex-grow content-wrapper overflow-hidden position-relative',
            panelLayout: 'h-100',
            clearButton: 'd-none',
            item: 'd-block'
        }
    };

    // Load search data
    function loadSearchData() {
        const assetsPath = window.assetsPath || '/';
        const isHor = document.getElementById('layout-menu') ?.classList.contains('menu-horizontal');
        const searchJson = isHor ? 'search-horizontal.json' : 'search-vertical.json';

        fetch(assetsPath + 'json/' + searchJson)
            .then(r => {
                if (!r.ok) throw new Error('Failed to fetch data');
                return r.json();
            })
            .then(json => {
                window.__searchData = json;
                initializeAutocomplete();
            })
            .catch(err => console.error('Error loading JSON:', err));
    }

    function initializeAutocomplete() {
        if (typeof window.autocomplete === 'undefined') return; // lib not loaded
        const data = window.__searchData || {};
        return autocomplete({
            ...SearchConfig,
            openOnFocus: true,
            onStateChange({
                state,
                setQuery
            }) {
                if (state.isOpen) {
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = 'var(--bs-scrollbar-width)';
                    const cancelIcon = document.querySelector('.aa-DetachedCancelButton');
                    if (cancelIcon) {
                        cancelIcon.innerHTML =
                            '<span class="text-body-secondary">[esc]</span> <span class="icon-base icon-md ti tabler-x text-heading"></span>';
                    }
                    if (!window.autoCompletePS && typeof window.PerfectScrollbar !== 'undefined') {
                        const panel = document.querySelector('.aa-Panel');
                        if (panel) window.autoCompletePS = new PerfectScrollbar(panel);
                    }
                } else {
                    if (state.status === 'idle' && state.query) setQuery('');
                    document.body.style.overflow = 'auto';
                    document.body.style.paddingRight = '';
                }
            },
            render(args, root) {
                const {
                    render,
                    html,
                    children,
                    state
                } = args;
                const d = window.__searchData || {};

                if (!state.query) {
                    const sections = Object.entries(d.suggestions || {});
                    const initial = html `
            <div class="p-5 p-lg-12">
              <div class="row g-4">
                ${sections.map(([section, items]) => html`
                  <div class="col-md-6 suggestion-section">
                    <p class="search-headings mb-2">${section}</p>
                    <div class="suggestion-items">
                      <div>
                        ${items.map(item => html`<a href="${item.url}" class="suggestion-item d-flex align-items-center"><i class="icon-base ti ${item.icon}"></i><span>${item.name}</span></a>`)}
                      </div>
                    </div>
                  </div>
                `)}
              </div>
            </div>`;
                    render(initial, root);
                    return;
                }

                if (!args.sections.length) {
                    render(html `
            <div class="search-no-results-wrapper">
              <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center text-heading">
                  <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.6">
                      <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                      <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2m-5-4h.01M12 11v3" />
                    </g>
                  </svg>
                  <h5 class="mt-2">No results found</h5>
                </div>
              </div>
            </div>
          `, root);
                    return;
                }

                render(children, root);
                window.autoCompletePS ?.update();
            },
            getSources() {
                const d = window.__searchData || {};
                const srcs = [];
                if (!d.navigation) return srcs;

                // sections except files/members first
                Object.keys(d.navigation)
                    .filter(s => s !== 'files' && s !== 'members')
                    .forEach(section => {
                        srcs.push({
                            sourceId: `nav-${section}`,
                            getItems({
                                query
                            }) {
                                const items = d.navigation[section] || [];
                                if (!query) return items;
                                return items.filter(it => it.name.toLowerCase().includes(query.toLowerCase()));
                            },
                            getItemUrl({
                                item
                            }) {
                                return item.url;
                            },
                            templates: {
                                header({
                                    items,
                                    html
                                }) {
                                    return items.length ? html `<span class="search-headings">${section}</span>` : null;
                                },
                                item({
                                    item,
                                    html
                                }) {
                                    return html `
                    <a href="${item.url}" class="d-flex justify-content-between align-items-center">
                      <span class="item-wrapper"><i class="icon-base ti ${item.icon}"></i>${item.name}</span>
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">
                          <path d="M11 6h4.5a4.5 4.5 0 1 1 0 9H4" />
                          <path d="M7 12s-3 2.21-3 3s3 3 3 3" />
                        </g>
                      </svg>
                    </a>`;
                                }
                            }
                        });
                    });

                if (d.navigation.files) {
                    srcs.push({
                        sourceId: 'files',
                        getItems({
                            query
                        }) {
                            const items = d.navigation.files || [];
                            if (!query) return items;
                            return items.filter(it => it.name.toLowerCase().includes(query.toLowerCase()));
                        },
                        getItemUrl({
                            item
                        }) {
                            return item.url;
                        },
                        templates: {
                            header({
                                items,
                                html
                            }) {
                                return items.length ? html `<span class="search-headings">Files</span>` : null;
                            },
                            item({
                                item,
                                html
                            }) {
                                const assetsPath = window.assetsPath || '/';
                                return html `
                  <a href="${item.url}" class="d-flex align-items-center position-relative px-4 py-2">
                    <div class="file-preview me-2">
                      <img src="${assetsPath}${item.src}" alt="${item.name}" class="rounded" width="42" />
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">${item.name}</h6>
                      <small class="text-body-secondary">${item.subtitle}</small>
                    </div>
                    ${item.meta ? html`<div class="position-absolute end-0 me-4"><span class="text-body-secondary small">${item.meta}</span></div>` : ''}
                  </a>`;
                            }
                        }
                    });
                }

                if (d.navigation.members) {
                    srcs.push({
                        sourceId: 'members',
                        getItems({
                            query
                        }) {
                            const items = d.navigation.members || [];
                            if (!query) return items;
                            return items.filter(it => it.name.toLowerCase().includes(query.toLowerCase()));
                        },
                        getItemUrl({
                            item
                        }) {
                            return item.url;
                        },
                        templates: {
                            header({
                                items,
                                html
                            }) {
                                return items.length ? html `<span class="search-headings">Members</span>` : null;
                            },
                            item({
                                item,
                                html
                            }) {
                                const assetsPath = window.assetsPath || '/';
                                return html `
                  <a href="${item.url}" class="d-flex align-items-center py-2 px-4">
                    <div class="avatar me-2">
                      <img src="${assetsPath}${item.src}" alt="${item.name}" class="rounded-circle" width="32" />
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">${item.name}</h6>
                      <small class="text-body-secondary">${item.subtitle}</small>
                    </div>
                  </a>`;
                            }
                        }
                    });
                }
                return srcs;
            }
        });
    }

    // Shortcut Ctrl/Cmd + K
    document.addEventListener('keydown', ev => {
        if ((ev.ctrlKey || ev.metaKey) && ev.key === 'k') {
            ev.preventDefault();
            document.querySelector('.aa-DetachedSearchButton') ?.click();
        }
    }, {
        passive: false
    });

    loadSearchData();
})();
