define(['Crimson_MachOrderFees/js/checkout/view/summary/core_charge_total'], function (Component) {
    'use strict';
    return Component.extend({
        isDisplayed: function () {
            return this.getPureValue() !== 0;
        }
    });
});
