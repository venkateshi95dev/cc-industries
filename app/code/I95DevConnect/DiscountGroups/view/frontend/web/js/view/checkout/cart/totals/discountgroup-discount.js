define(
    [
    'I95DevConnect_DiscountGroups/js/view/checkout/summary/discountgroup-discount'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            /**
             * @override
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);