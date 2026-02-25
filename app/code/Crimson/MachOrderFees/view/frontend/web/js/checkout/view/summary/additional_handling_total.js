define([
   'Crimson_MachOrderFees/js/checkout/view/summary/abstract-fee-total'
], function (Component) {
    'use strict';

    return Component.extend({
        getFeeCode: function () {
            return 'additional_handling';
        }
    });
});
