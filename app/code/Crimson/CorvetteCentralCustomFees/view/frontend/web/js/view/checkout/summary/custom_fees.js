define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Crimson_CorvetteCentralCustomFees/checkout/summary/custom_fees'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            isDisplayed: function(fee_code) {
                return this.isFullMode() && this.getPureValue(fee_code) > 0;
            },

            getValue: function(fee_code) {
                var price = 0;
                if (totals.getSegment(fee_code)) {
                    price = totals.getSegment(fee_code).value;
                }
                return this.getFormattedPrice(price);
            },
            getPureValue: function(fee_code) {
                var price = 0;
                if (totals.getSegment(fee_code)) {
                    price = totals.getSegment(fee_code).value;
                }
                return price;
            },
            getCanadaTaxTitle: function (){
                if (totals.getSegment('canada_taxes')) {
                    return  totals.getSegment('canada_taxes').title;
                }
            }
        });
    }
);
