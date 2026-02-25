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

define(
    [
        'jquery',
        'ko',
        'underscore',
        'mage/utils/wrapper',
        'Magento_Tax/js/view/checkout/summary/grand-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'mage/url',
        'Magento_Catalog/js/price-utils',
    ], function ($, ko, _, wrapper, grandTotal, quote, totals, mageUrl, priceUtils) {
        'use strict';

        /**
         * Free method filter
         * @param {Object} paymentMethod
         * @return {Boolean}
         */
        var paymentServicesMethods = [
                'payment_services_paypal_smart_buttons',
                'payment_services_paypal_apple_pay',
                'payment_services_paypal_google_pay',
                'payment_services_paypal_hosted_fields'
            ],

            /**
             * Check if payment method is from payment services.
             *
             * @param {String} needleName
             * @param {String} paymentMethod
             * @returns {Boolean}
             */
            isPaymentServicesButtonsPaymentMethod = function (needleName, paymentMethod) {
                return paymentMethod.method === needleName;
            },


            /**
             * Check if Apple Pay method is available.
             */
            checkApplePayAvailability = function () {

            },

            /**
             *
             * @type {{setPaymentMethods: (function(Function, Array): *)}}
             */
            extender = {
                /**
                 * Filter hidden payment methods.
                 *
                 * @param {Function} originFn - Original method.
                 * @param {Array} methods
                 */
                setPaymentMethods: function (originFn, methods) {
                    let self = this;
                    var paymentServicesButtonMethodIndex;
                    var superObj = this;
                    let sameShippingBillingAddress = $("#billing-address-same-as-shipping-ebizcharge_ebizcharge");
                    let webhostedUrl = "";
                    let grandTotalTxt = $(".table-totals").find(".grand");
                    let isConfigSameBillingShippingAddresses = typeof window.checkoutConfig.payment.ebizcharge !== "undefined" ? checkoutConfig.payment.ebizcharge.isSameBillingShippingAddresses: false;

                    if(isConfigSameBillingShippingAddresses){
                        let autoSelectShippingAddress = setInterval(function () {
                            let ebizchargeSelected = $("#ebizcharge_ebizcharge");
                            if (ebizchargeSelected.length > 0 && ebizchargeSelected.is(":checked")) {
                                let sameShippingBillingAddress = $("#billing-address-same-as-shipping-ebizcharge_ebizcharge");
                                let sameShippingBillingAddressParent = sameShippingBillingAddress.parent();
                                let isSameBillingShipping = superObj.isBillingShippingAddressesSame(quote, totals);

                                if (sameShippingBillingAddress.length > 0 && !sameShippingBillingAddress.is(":checked")) {
                                    sameShippingBillingAddress.trigger("click");
                                    sameShippingBillingAddressParent.find("#billing-address-same-as-shipping-ebizcharge_ebizcharge").css('pointer-events', 'none');
                                    sameShippingBillingAddressParent.find("label").css('pointer-events', 'none');
                                    clearInterval(autoSelectShippingAddress);
                                }
                            }

                        }, 10);
                    }

                    if (typeof window.checkoutConfig.payment.ebizcharge !== "undefined" && window.checkoutConfig.payment.ebizcharge.paymentFormType === "2") {
                        const renderIframeInterval = setInterval(function () {

                            var csrfToken = window.checkoutConfig.formKey;
                            var hostedProIframe = document.getElementById("ebizcharge-payment-webform");

                            if (typeof hostedProIframe !== "undefined" && hostedProIframe !== null) {
                                /**
                                 * Fetching checkout WebFormURL
                                 */
                                $.ajax({
                                    url: mageUrl.build('ebizcharge/checkout/renderwebhostedformurl/form_key/' + csrfToken),
                                    type: 'POST',
                                    beforeSend: function () {
                                        // hostedProIframe.src = window.checkoutConfig.payment.ebizcharge.getLogoUrl;
                                    },
                                    data: {
                                        'key': 'value',
                                        'form_key': csrfToken // Include the CSRF token
                                    },
                                    dataType: 'json',
                                    success: function (responseData) {
                                        let hostedUrl = responseData.hosted_pro_url;
                                        if(typeof (window.checkoutConfig.payment.ebizcharge) !== "undefined"){
                                            window.checkoutConfig.payment.ebizcharge.checkoutWebHostedFormUrl = hostedUrl;
                                            this.checkoutWebHostedFormUrl = hostedUrl;
                                        }
                                        //  console.log(responseData);
                                        if (responseData && responseData.error === false) {
                                            hostedProIframe.src = hostedUrl;
                                            self.isWebhostedFormRendered = true;
                                        }

                                    },
                                    error: function (xhr, status, error) {
                                        console.log(error);
                                        window.checkoutConfig.payment.ebizcharge.checkoutWebHostedFormUrl = hostedUrl;
                                    }
                                });
                                clearInterval(renderIframeInterval);

                            }
                        }, 500);
                    }


                    return originFn(methods);
                },
                /**
                 * is WebhHostedForm is rendered
                 */
                isWebhostedFormRendered: false,
                /**
                 *
                 * @param quote
                 * @param totals
                 * @returns {*}
                 */
                isBillingShippingAddressesSame: function (quote, totals) {

                    // Observable to manage the checkbox state
                    var isSameAsBilling = ko.observable(false);
                    var superObj = this;

                    // Subscribe to the address changes and update the checkbox state
                    quote.shippingAddress.subscribe(function () {
                        isSameAsBilling(superObj.areAddressesSame());
                    });

                    quote.billingAddress.subscribe(function () {
                        isSameAsBilling(superObj.areAddressesSame());
                    });

                    return {
                        isSameAsBilling: isSameAsBilling
                    }
                },
                /**
                 *
                 * @returns {boolean}
                 */
                areAddressesSame: function () {
                    var shippingAddress = quote.shippingAddress();
                    var billingAddress = quote.billingAddress();

                    if (!shippingAddress || !billingAddress) {
                        return false;
                    }

                    return shippingAddress.street === billingAddress.street &&
                        shippingAddress.city === billingAddress.city &&
                        shippingAddress.postcode === billingAddress.postcode &&
                        shippingAddress.countryId === billingAddress.countryId &&
                        shippingAddress.region === billingAddress.region;
                },
            };

        return function (target) {
            return wrapper.extend(target, extender);
        };
    });
