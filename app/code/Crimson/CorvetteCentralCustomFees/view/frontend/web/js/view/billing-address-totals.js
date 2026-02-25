define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/get-totals'
], function (Component, quote, getTotalsAction) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            // Subscribe once to billingAddress changes
            var subscription = quote.billingAddress.subscribe(function (newAddress) {
                // Only fire when we actually have an address (e.g. postcode exists)
                if (newAddress && newAddress.postcode) {
                    // Trigger totals collect
                    getTotalsAction([]);
                    // Dispose so we never fire again
                    subscription.dispose();
                }
            });
            return this;
        }
    });
});
