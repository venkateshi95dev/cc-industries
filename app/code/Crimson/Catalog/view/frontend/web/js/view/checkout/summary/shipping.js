/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Tax/js/view/checkout/summary/shipping',
    'Magento_Checkout/js/model/quote'
], function ($, Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Crimson_Catalog/checkout/summary/shipping'
        },
        isTruckShip: function () {
            return !!window.checkoutConfig.is_truckship;
        },
        getTruckShipMessage: function () {
            return window.checkoutConfig.is_truckship_message;
        }
    });
});
