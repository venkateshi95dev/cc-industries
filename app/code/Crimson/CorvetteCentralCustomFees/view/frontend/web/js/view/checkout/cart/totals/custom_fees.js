
define(
    [
        'Crimson_CorvetteCentralCustomFees/js/view/checkout/summary/custom_fees'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            /**
             * @override
             */
            isDisplayed: function (fee_code) {
                return this.getPureValue(fee_code) > 0;
            }
        });
    }
);
