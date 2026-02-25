define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';
    return function (Component) {
        return Component.extend({

            getOSCOEta: function (method) {
                if ('extension_attributes' in method &&
                    'osco_delivery_date' in method.extension_attributes &&
                    !_.isEmpty(method.extension_attributes.osco_delivery_date)
                ) {
                    return method.extension_attributes.osco_delivery_date;
                }
            },
        });
    }
});
