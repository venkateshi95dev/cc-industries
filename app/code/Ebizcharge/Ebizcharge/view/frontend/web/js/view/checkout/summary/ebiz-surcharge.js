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
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'jquery',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
    ],
    function (ko, Component, $, priceUtils, quote) {
        'use strict';
        if(typeof window.checkoutConfig.payment.ebizcharge === "undefined"){
            return Component.extend({ });
        }

        return Component.extend({
            defaults: {
                template: 'Ebizcharge_Ebizcharge/checkout/summary/surcharge'
            },
            surchargeEnabled: false,
            surchargeAmount: ko.observable(null),
            surchargeCaption: ko.observable(''),
            surchargeAPICaption: ko.observable(null),
            surchargePercentage: ko.observable(null),
            surchargeAPIPercentage: ko.observable(null),
            surchargeAmountWithSign: ko.observable(null),
            surchargeSessionDataUrl: '',
            paymentMethodForm: null,
            surchargeSummarySection: null,
            recurringSurchargeAmount: 0,

            /**
             * Initialize method
             */
            initialize: function () {
                this._super();

                let checkoutConfig = window.checkoutConfig.payment.ebizcharge;
                window.checkoutConfig.payment.ebizcharge.surchargeAmount = 0;
                //this.surchargeEnabled = typeof (checkoutConfig.surchargeSettings.surchargeEnabled) !==
                //'undefined' ? checkoutConfig.surchargeSettings.surchargeEnabled : false;
                this.surchargeAPICaption = typeof (checkoutConfig.surchargeSettings.surchargeCaption) !==
                'undefined' ? checkoutConfig.surchargeSettings.surchargeCaption : '';
                this.surchargeAPIPercentage = typeof (checkoutConfig.surchargeSettings.surchargePercentage) !==
                'undefined' ? checkoutConfig.surchargeSettings.surchargePercentage : '';
                this.surchargeEnabled = checkoutConfig.surchargeEnabled;
                this.surchargeSessionDataUrl = typeof (checkoutConfig.surchargeSessionDataUrl) !==
                'undefined' ? checkoutConfig.surchargeSessionDataUrl : '';
                this.paymentMethodForm = $(document.getElementById('ebizcharge_ebizcharge-form'));
                this.surchargeSummarySection = $(document.getElementById('ebiz-summary-surcharge'));
                this.recurringSurchargeSpan = $(document.getElementById('product-item-surcharge'));
            },

            /**
             * Show Surcharge Amount with Caption
             *
             * @returns {boolean}
             */
            showSurchargeAmount: function () {
                return this.surchargeEnabled;
            },

            /**
             * Show surcharge amount with caption on checkout summary section
             */
            showSurcharge: function (respData) {
                let self = this;
                let surchargeSessionDataUrl = this.surchargeSessionDataUrl;
                //let checkoutForm = this.paymentMethodForm;
                let surchargeSummarySection = this.surchargeSummarySection;

                if (!this.surchargeEnabled || !surchargeSessionDataUrl) {
                    self.unsetSurchargeValues();
                }

                if (respData.surchargeAmount != null &&
                    respData.surchargeCaption &&
                    respData.surchargePercentage) {
                    self.surchargeAmount(respData.surchargeAmount);
                    self.surchargeCaption(
                        respData.surchargeCaption + ' (' + respData.surchargePercentage + ')'
                    );
                    self.surchargePercentage(respData.surchargePercentage);
                    self.surchargeAmountWithSign(respData.surchargeAmountWithSign);
                    window.checkoutConfig.payment.ebizcharge.surchargeAmount = respData.surchargeAmount;
                    surchargeSummarySection.show();
                    let surchargeAmount = priceUtils.formatPrice(respData.surchargeAmount, {});

                    this.recurringSurchargeSpan.find(".product-item-surcharge").text(surchargeAmount);

                } else {
                    self.unsetSurchargeValues();

                }

            },

            /**
             * Unset surcharge values & hide surcharge from checkout summary section
             */
            unsetSurchargeValues: function () {
                this.surchargeAmount(null);
                this.surchargeCaption(null);
                this.surchargePercentage(null);
                this.surchargeAmountWithSign(null);

                this.surchargeSummarySection.hide();
            },

            /**
             * Unset surcharge values & hide surcharge from checkout summary section
             */
            setSurchargeValuesForNew: function () {
                if (!this.surchargeEnabled) {
                    return;
                }

                let surchargeCaption = this.surchargeAPICaption ? this.surchargeAPICaption +
                    (this.surchargeAPIPercentage ? ' (' + this.surchargeAPIPercentage + '%)' : '') : '';
                let price = 0;

                this.surchargeAmount(price);
                this.surchargeCaption(surchargeCaption);
                this.surchargePercentage(this.surchargeAPIPercentage);
                this.surchargeAmountWithSign(priceUtils.formatPriceLocale(
                    price,
                    quote.getPriceFormat()
                ));
                window.checkoutConfig.payment.ebizcharge.surchargeAmount = price;
                this.surchargeSummarySection.show();
            }
        });
    }
);
