
define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';
    return function (Component) {
        return Component.extend({

            getEta: function (method) {
                if ('extension_attributes' in method
                    && 'delivery_date' in method.extension_attributes
                    && !_.isEmpty(method.extension_attributes.delivery_date)
                ) {
                    if ($('#eta-important-message').length
                        && !$('#eta-important-message').is(':visible')
                    ) {
                        $('#eta-important-message').show();
                    }

                    return method.extension_attributes.delivery_date;
                }
            },
        });
    }
});
