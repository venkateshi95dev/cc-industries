define([
    'Magento_Tax/js/view/checkout/summary/shipping',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
         isTruckShip: function () {
             return !!window.checkoutConfig.is_truckship;
         },
         getTruckShipMessage: function () {
            return window.checkoutConfig.is_truckship_message;
         },
         getTruckShipMessageUnderShippingMethod: function () {
             return window.checkoutConfig.truck_ship_message_under_shipping_method
         },
    });
});
