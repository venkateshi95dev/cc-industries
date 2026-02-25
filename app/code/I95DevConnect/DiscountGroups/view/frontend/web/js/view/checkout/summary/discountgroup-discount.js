define(
    [
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils'
    ],
    function ($,Component,quote,totals,priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'I95DevConnect_DiscountGroups/checkout/summary/discountgroup-discount'
            },
            totals: quote.getTotals(),
            isDisplayedCustomdiscountTotal : function () {
                var price = 0;
                if (this.totals) {
                    var segment = totals.getSegment('discountgroup');
                    if (segment) {
                        price = segment['value'];
                    }
                }
               return price !== 0;
            },
            getCustomdiscountTotal : function () {
                var price = 0;
                if (this.totals) {
                    var segment = totals.getSegment('discountgroup');
                    if (segment) {
                        price = segment['value'];
                    }
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
