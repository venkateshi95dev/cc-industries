define([
           'Magento_Checkout/js/view/summary/abstract-total',
           'Magento_Checkout/js/model/totals'
       ], function (Component, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: '' +
                      'Crimson_MachOrderFees/checkout/summary/totals'
        },
        totals: totals.totals(),

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0,
                segment;

            if (this.totals) {
                segment = totals.getSegment(this.getFeeCode());

                if (segment) {
                    price = segment.value;
                }
            }

            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        /**
         * @return {Boolean}
         */
        isAvailable: function () {
            return this.isFullMode() && this.getPureValue() != 0; //eslint-disable-line eqeqeq
        }
    });
});
