define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'payware',
                component: 'Crimson_Payware/js/view/payment/method-renderer/payware'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    });