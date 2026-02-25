define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/cart/cache'
], function ($, _, wrapper, cartCache) {
    'use strict';

    return function (payloadExtender) {

        return wrapper.wrap(payloadExtender, function (originalAction, addressData) {
            var payload = originalAction(addressData);

            var validationFields = cartCache.get('validationFields');
            if (!validationFields) {
                return payload;
            }

            if (payload.addressInformation.hasOwnProperty('shipping_address')
                && !payload.addressInformation.shipping_address.hasOwnProperty('extensionAttributes')
            ) {
                payload.addressInformation.shipping_address.extensionAttributes = {};
            }

            if (payload.addressInformation.hasOwnProperty('shipping_address')) {
                payload.addressInformation.shipping_address.extensionAttributes.ship_adv = _.has(validationFields, "ship_adv") ? validationFields["ship_adv"] : 0;
                payload.addressInformation.shipping_address.extensionAttributes.ship_adv_date = _.has(validationFields, "ship_adv_date") ? validationFields["ship_adv_date"] : "";
                payload.addressInformation.shipping_address.extensionAttributes.ship_adv_di = _.has(validationFields, "ship_adv_di") ? validationFields["ship_adv_di"] : "";
                payload.addressInformation.shipping_address.extensionAttributes.ship_adv_dpi = _.has(validationFields, "ship_adv_dpi") ? validationFields["ship_adv_dpi"] : "";
            }

            return payload;
        });
    };
});
