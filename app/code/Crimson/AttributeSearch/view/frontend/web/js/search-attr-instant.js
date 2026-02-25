define(['jquery'], function ($) {
    'use strict';

    return function () {

        const SEARCH_FORM_SELECTOR       = '#search_mini_form';
        const FACET_DROPDOWN_SELECTOR    = '.attribute-dropdown';

        const STORAGE_KEY                = 'fs_pending_narrow';
        const STORAGE_TTL_MS             = 3_600_000;   // 1 hour

        const SERP_PATH_REGEX            = /\/instantsearchplus\/result/;
        const FAST_SIMON_EVENT_NAME      = 'send-fast-simon-params';

        const now = () => Date.now();

        const loadSavedFilters = () => {
            try {
                const raw = localStorage.getItem(STORAGE_KEY) || 'null';
                const obj = JSON.parse(raw);

                const isFresh =
                    obj && obj.ts && obj.narrow &&
                    now() - obj.ts < STORAGE_TTL_MS;

                return isFresh ? obj.narrow : null;
            } catch {
                clearFiltersInStorage();
                return null;
            }
        };

        const saveFiltersToStorage = (filters) => {
            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({ ts: now(), narrow: filters })
            );
        }

        const clearFiltersInStorage = () => {
            localStorage.removeItem(STORAGE_KEY);
        }

        const collectCurrentFilters = () => {
            const result = {};

            $(FACET_DROPDOWN_SELECTOR).each(function () {
                const value = $(this).val();
                if (!value) { return; }

                const label = $(this).data('facetLabel');
                (result[label] = result[label] || []).push(value);
            });

            return result;
        }

        function registerFiltersHook(filters) {
            const narrowWithSets = Object.fromEntries(
                Object.entries(filters).map(([label, vals]) => [
                    label,
                    new Set(vals)
                ])
            );

            function hooks () {
                window.SerpOptions.registerHook(
                    'serp-filters',
                    (params) => updateFiltersValidator(narrowWithSets, params)
                );
            }

            if (window.SerpOptions) {
                hooks();
            } else {
                window.addEventListener('fast-serp-ready', hooks, { once: true });
            }
        };

        function updateFiltersValidator(narrow, params) {
            if(params && params.facets.length && narrowExists(narrow, params.facets)) {
                const current = window.SerpOptions.getNarrow?.() || {};
                if (!Object.keys(current).length > 0) {
                    updateFilters(narrow);
                }
            }
        }

        function narrowExists(narrow, facets) {
            const labelsPresent = new Set(
                facets.map(f => f.facet || f.label || f.name)
            );

            return Object.keys(narrow).every(label => labelsPresent.has(label));
        }

        function updateFilters(narrow) {
            document.dispatchEvent(
                new CustomEvent(FAST_SIMON_EVENT_NAME, { detail: { narrow } })
            );
        }

        (function restoreUI () {
            const savedFilters = loadSavedFilters();
            if (!savedFilters) { return; }

            Object.entries(savedFilters).forEach(([label, values]) => {
                $(FACET_DROPDOWN_SELECTOR).filter(function () {
                    return $(this).data('facetLabel') === label;
                }).val(values[0]).trigger('change');
            });

            if (SERP_PATH_REGEX.test(location.pathname)) {
                registerFiltersHook(savedFilters);
            }
        })();

        $(SEARCH_FORM_SELECTOR).on('submit', function (e) {
            // no need to reload page when only param changes
            const isOnSerp       = SERP_PATH_REGEX.test(location.pathname);
            const inputQuery   = e.target.querySelector('input[name=q]')?.value.trim() || '';
            const urlQuery     = new URLSearchParams(location.search).get('q')?.trim() || '';
            const isSameQuery  = inputQuery === urlQuery;
            const shouldCancel = isOnSerp && isSameQuery;
            if(isOnSerp && shouldCancel && !inputQuery) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }

            const currentFilters = collectCurrentFilters();
            const hasSelections  = Object.keys(currentFilters).length > 0;

            if (isOnSerp) {
                const filters = hasSelections ? currentFilters : emptyFilters();

                const narrowWithSets = Object.fromEntries(
                    Object.entries(filters).map(
                        ([label, values]) => [label, new Set(values)]
                    )
                );
                updateFilters(narrowWithSets)
            }
        });

        function emptyFilters() {
            const labels = $(FACET_DROPDOWN_SELECTOR)
                .map((_, el) => $(el).data('facetLabel'))
                .get();
            const unique = Array.from(new Set(labels));

            const selected = collectCurrentFilters();
            const narrow = {};
            for (const label of unique) {
                const vals = selected[label] || [];
                const arr = Array.isArray(vals) ? vals : (vals ? [vals] : []);
                narrow[label] = new Set(arr);
            }
            return narrow;
        }

        $(FACET_DROPDOWN_SELECTOR).on('change', function () {
            const filters = collectCurrentFilters();
            const hasAny  = Object.keys(filters).length > 0;

            if (hasAny) {
                saveFiltersToStorage(filters);
            } else {
                clearFiltersInStorage();
            }

            $(SEARCH_FORM_SELECTOR).find('input[name=q]').trigger('input');
        });
    };
});
