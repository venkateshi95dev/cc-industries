define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
 
        rendererList.push(
            {
                type: 'silk_payment',
                component: 'Silk_Payment/js/view/payment/method-renderer/llamacoin'
            }
        );
 
        /** Add view logic here if needed */
        return Component.extend({});
    });