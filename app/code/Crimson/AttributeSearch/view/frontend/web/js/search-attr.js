define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";

    return function (config, element) {
        const select = $(element);
        const searchForm = $(config.selector);

        const params = new URLSearchParams(window.location.search);
        const selectedAttr = params.get(config.attribute_filter);

        if (selectedAttr) {
            $(select).val(selectedAttr);
        }

        select.on('change', function () {
            const searchInput = searchForm.find('input[name=q]');
            searchInput.trigger('input');
        });

        $(searchForm).on('submit', function (e) {
            const attributeValue = select.val();

            if (attributeValue) {
                $(this).append(`<input type="hidden" name="${config.attribute_filter}" value="${attributeValue}">`);
            }
        });
    };
});
