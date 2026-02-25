define(['jquery', 'domReady!'], function ($) {
    'use strict';

    return function (config, element) {
        const $select = $(element);
        const $form   = $(config.selector);
        const LABEL   = (config.attribute_label || '').trim();
        const KEY     = 'narrow';

        const getUrlParam = k => new URLSearchParams(window.location.search).get(k);

        $select.on('change', () => {
            $form.find('input[name=q]').trigger('input');
        });

        $form.on('submit', function () {
            const val     = $.trim($select.val());
            let   current = getUrlParam(KEY) || '';

            let pairs = [];
            if (current) {
                try { 
                    pairs = JSON.parse(decodeURIComponent(current)); 
                } catch {
                    return;
                }
            }

            pairs = pairs.filter(p => p[0] !== LABEL);

            if (val) { 
                pairs.push([LABEL, val]); 
            }

            $(this).find('input[name="' + KEY + '"]').remove();

            if (!pairs.length) { 
                return; 
            }

            const value = JSON.stringify(pairs);

            $(this).append(
                $('<input>', {
                    type: 'hidden',
                    name: KEY,
                    value: value
                })
            );
        });

        const raw = getUrlParam(KEY);
        if (!raw) { 
            return; 
        }

        try {
            const hit = JSON.parse(decodeURIComponent(raw)).find(p => p[0] === LABEL);
            if (hit) {
                $select.val(hit[1]);
            }
        } catch {
            return;
        }
    };
});
