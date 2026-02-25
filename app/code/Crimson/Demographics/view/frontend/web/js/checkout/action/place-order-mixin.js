define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    // targetModule = vendor/magento/module-checkout/view/frontend/web/js/action/place-order.js
    return function ( targetModule ) {

        return wrapper.wrap(targetModule, function (originalAction, paymentData, messageContainer) {

            let enabled = window.checkoutConfig.demographics_enabled == 1 ? true : false;

            if (enabled && $("input[name='demographics-code[]']").length) {
                if (!paymentData.additional_data) {
                    paymentData.additional_data = {};
                }
                let demo = [];
                $("input[name='demographics-code[]']").each(function() {
                    if ($(this).is(':checked')) {
                        demo.push($(this).val());
                    }
                });

                paymentData.additional_data.demographics_code = '';
                if (demo) {
                    $.each(demo, function (key, value) {
                        paymentData.additional_data.demographics_code += value + ',';

                    });
                    paymentData.additional_data.demographics_code = paymentData.additional_data.demographics_code.replace(/,\s*$/, "");
                }
            }

            return originalAction(paymentData, messageContainer);
        });

    };

});
