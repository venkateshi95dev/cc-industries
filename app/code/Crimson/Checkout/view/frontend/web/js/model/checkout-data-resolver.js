define([
    'underscore',
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-method'
], function (_, wrapper, pickup, quote, checkoutData, selectShippingMethodAction) {
    'use strict';

    return function (checkoutDatResolver) {
        checkoutDatResolver.resolveShippingRates = wrapper.wrapSuper(checkoutDatResolver.resolveShippingRates, function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                availableRate = false;

            //Customization: checking if freeshipping is present to be selected automatically
            let freeShippingPresent = ratesData.find(x => x.carrier_code === "freeshipping");
            if (ratesData.length > 1 && freeShippingPresent) {
                //set freeshipping rate if it is present
                selectShippingMethodAction(freeShippingPresent);
                checkoutData.setSelectedShippingRate(freeShippingPresent['carrier_code'] + '_' + freeShippingPresent['method_code']);

                return;
            }

            //-****************************
            // Here is the customization, if instore or amstorepickup is enabled
            // AND is the only method we don't want to select it automatically
            if (ratesData.length === 1
                && !quote.shippingMethod()
                && ratesData[0].carrier_code !== 'instore'
                && ratesData[0].carrier_code !== 'amstorepickup'
            ) {
                //set shipping rate if we have only one available shipping rate
                selectShippingMethodAction(ratesData[0]);

                return;
            }

            if (quote.shippingMethod()) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] == quote.shippingMethod()['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == quote.shippingMethod()['method_code']; //eslint-disable-line eqeqeq
                });
            }

            if (!availableRate && selectedShippingRate) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] + '_' + rate['method_code'] === selectedShippingRate;
                });
            }

            if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                availableRate = _.find(ratesData, function (rate) {
                    var selectedShippingMethod = window.checkoutConfig.selectedShippingMethod;

                    return rate['carrier_code'] == selectedShippingMethod['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == selectedShippingMethod['method_code']; //eslint-disable-line eqeqeq
                });
            }

            //Unset selected shipping method if not available
            if (!availableRate) {
                selectShippingMethodAction(null);
            } else {
                selectShippingMethodAction(availableRate);
            }
        });

        return checkoutDatResolver;
    };
});
