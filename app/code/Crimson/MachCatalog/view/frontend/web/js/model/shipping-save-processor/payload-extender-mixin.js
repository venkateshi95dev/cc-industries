define([
           'jquery',
           'underscore',
           'mage/utils/wrapper',
           'Magento_Checkout/js/model/cart/cache'
       ], function ($, _, wrapper, cartCache) {
    'use strict';

    return function (payloadExtender) {

        /** Override default payloadExtender order action and add agreement_ids to request */
        return wrapper.wrap(payloadExtender, function (originalAction, addressData) {
            var payload = originalAction(addressData);

            var address = cartCache.get('address');
            if (!address) {
                return payload;
            }

            if (payload.addressInformation.hasOwnProperty('shipping_address')
                && !payload.addressInformation.shipping_address.hasOwnProperty('extensionAttributes')
            ) {
                payload.addressInformation.shipping_address.extensionAttributes = {};
            }

            if (payload.addressInformation.hasOwnProperty('shipping_address')) {
                if(typeof payload.addressInformation.shipping_address.extensionAttributes === 'undefined') {
                    payload.addressInformation.shipping_address.extensionAttributes = {};
                }

                if (address.hasOwnProperty('extensionAttributes') && address.extensionAttributes.hasOwnProperty('mach_hsc')) {
                    payload.addressInformation.shipping_address.extensionAttributes.mach_hsc = address.extensionAttributes.mach_hsc;
                } else {
                    payload.addressInformation.shipping_address.extensionAttributes.mach_hsc = 0;
                }
            }

            return payload;
        });
    };
});
