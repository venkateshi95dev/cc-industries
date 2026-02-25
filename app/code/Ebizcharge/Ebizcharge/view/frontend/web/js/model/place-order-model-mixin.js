/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

define([
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/payment/place-order-hooks',
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data'
], function (storage, errorProcessor, fullScreenLoader, customerData, hooks, _, wrapper, checkoutData) {
    'use strict';

    return function (placeOrder) {
        return wrapper.wrap(placeOrder, function (originalAction, serviceUrl, payload, messageContainer) {
            var headers = {}, redirectURL = '';

            fullScreenLoader.startLoader();
            _.each(hooks.requestModifiers, function (modifier) {
                modifier(headers, payload);
            });

            return storage.post(
                serviceUrl, JSON.stringify(payload), true, 'application/json', headers
            ).fail(
                function (response) {
                    errorProcessor.process(response, messageContainer);
                    redirectURL = response.getResponseHeader('errorRedirectAction');

                    /**
                     * (Extra check Start)
                     *
                     * To skip redirecting to shipping tab if exception is generated from EBizCharge
                     */
                    if (redirectURL === '#shipping' && checkoutData.getSelectedPaymentMethod() ===
                        checkoutConfig.payment.ebizcharge.code) {
                        redirectURL = window.location.href; //'#payment';
                    }
                    /** Extra check End **/

                    if (redirectURL) {
                        setTimeout(function () {
                            errorProcessor.redirectTo(redirectURL);
                        }, 3000);
                    }
                }
            ).done(
                function (response) {
                    var clearData = {
                        'selectedShippingAddress': null,
                        'shippingAddressFromData': null,
                        'newCustomerShippingAddress': null,
                        'selectedShippingRate': null,
                        'selectedPaymentMethod': null,
                        'selectedBillingAddress': null,
                        'billingAddressFromData': null,
                        'newCustomerBillingAddress': null
                    };

                    if (response.responseType !== 'error') {
                        customerData.set('checkout-data', clearData);
                    }
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                    _.each(hooks.afterRequestListeners, function (listener) {
                        listener();
                    });
                }
            );
        });
    };
});
