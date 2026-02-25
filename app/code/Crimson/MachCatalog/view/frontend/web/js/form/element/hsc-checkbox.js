define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox',
    'underscore',
    'mage/translate',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Checkout/js/model/quote',
    'ko'
], function ($, SingleCheckbox, _, $t, cartCache, quote, ko) {
    'use strict';

    var machGroundShippingMethod = ["mach_501", "mach_207", "mach_208"];
    var minQuoteGrandTotalHsc = 75;

    return SingleCheckbox.extend({

        _showHsc: ko.observable(false),

        /** @inheritdoc */
        initialize: function () {
            var self = this;

            this._super();

            quote.shippingMethod.subscribe(function (shippingMethodObject) {
                var shippingAddress = quote.shippingAddress();
                self.showHscCheckbox(shippingMethodObject,shippingAddress);
            });

            return this;
        },

         //We only care about the case when the customer can decides HSC, other cases will be validated when
         //the Order is sent to Mach
         showHscCheckbox: function (shippingMethodObject,address) {
             var shippingMethod = shippingMethodObject ?
                 shippingMethodObject['carrier_code'] + '_' + shippingMethodObject['method_code'] : null;
             if (!shippingMethod || !address || (address && address.countryId != "US")) {
                 this._showHsc(false);
                 this.resetHscCheckbox();
                 return;
             }

             var allowedShippingMethodsHsc = !!($.inArray(shippingMethod, machGroundShippingMethod));
             var quoteGrandTotal = !!(quote.totals()['subtotal'] > minQuoteGrandTotalHsc);
             var result = !!(window.checkoutConfig.has_backorders && !window.checkoutConfig.is_dropship &&
                 allowedShippingMethodsHsc && quoteGrandTotal);

             this._showHsc(result);
             if (!result) {
                 this.resetHscCheckbox();
             }

             return this;
         },

        resetHscCheckbox: function () {
            this.checked(false);
        },

         onExtendedValueChanged: function (newExportedValue) {
             this._super(newExportedValue);

             var address = cartCache.get('address');
             if (!address) {
                 address = {};
             }

             if (!address.hasOwnProperty('extensionAttributes')) {
                 address.extensionAttributes = {};
             }

             address.extensionAttributes.mach_hsc = newExportedValue;

             cartCache.set('address', address);
         },
    });
});
