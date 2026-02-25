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

// define([
//     'Magento_Tax/js/view/checkout/summary/grand-total',
//     'Magento_Checkout/js/model/quote',
//     'Magento_Checkout/js/model/totals'
// ], function (grandTotal, quote, totals) {
//     'use strict';
//
//     var grandTotalMixin = {
//         totals: quote.getTotals(),
//
//         /**
//          * @override
//          */
//         // getValue: function () {
//         //     let price = 0;
//         //
//         //     if (this.totals()) {
//         //         price = totals.getSegment('grand_total').value + 2;
//         //     }
//         //
//         //     return this.getFormattedPrice(price);
//         // }
//         isBaseGrandTotalDisplayNeeded: function () {
//             return false;
//         }
//     };
//
//     return function (target) {
//         return target.extend(grandTotalMixin);
//     };
// });

define(
    [
        'jquery',
        'uiComponent',
        'mage/translate',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Ebizcharge_Ebizcharge/js/view/checkout/summary/ebiz-surcharge'
    ], function ($, Component, $t, priceUtils, quote, totals, surcharge) {
        'use strict';
        if(typeof window.checkoutConfig.payment.ebizcharge === "undefined"){
            var grandTotalMixin = {};
        }else {
            /**
             *
             * @type {{getValue: (function(): *), defaults: {template: string}, renderRecurringTotal: (function(): number), renderShippingAmount: grandTotalMixin.renderShippingAmount, getQuote: (function(): *), totals: *}}
             */
            var grandTotalMixin = {
                defaults: {
                    template: 'Magento_Tax/checkout/summary/grand-total'
                },
                totals: quote.getTotals(),

                /**
                 * @override
                 */
                getValue: function () {
                    let price = 0;
                    let isRecurringEnabled = typeof (window.checkoutConfig.payment.ebizcharge) !== "undefined" ? window.checkoutConfig.payment.ebizcharge.isRecurringEnabled : false;

                    if (this.totals()) {
                        let surchargeAmount = surcharge().surchargeAmount();//window.checkoutConfig.quoteData;

                        price = Number(totals.getSegment('grand_total').value) + Number((surchargeAmount ?
                            surchargeAmount : 0));
                        let grandTotal = price;

                        this.renderShippingAmount();
                        if (isRecurringEnabled) {
                            price = this.renderRecurringTotal(grandTotal);
                        }
                    }
                    return this.getFormattedPrice(price);
                },

                /**
                 *
                 * @returns {number}
                 */
                renderRecurringTotal: function (grandTotal) {

                    let mainObj = this;
                    let surchargeAmount = surcharge().surchargeAmount();//window.checkoutConfig.quoteData;
                    let orderTotals = quote.getTotals()();

                    let quoteItemData = window.checkoutConfig.quoteItemData;
                    let totalsAmountHtml = $(".totals").find(".price");
                    let totalsShippingAMountHtml = $(".shipping").find(".amount");
                    let totalsAmountTaxAmountHtml = $(".totals-tax").find(".amount").find(".price") ? $(".totals-tax").find(".amount").find(".price") : $(".totals-tax").find(".price");
                    let totalsTaxAmount = $(".totals-tax").find(".price");
                    let grantTotalsTitleHtml = $(".grand").find(".mark").find("strong");
                    let miniCartItemsTitle = $(".items-in-cart").find(".title");
                    let miniCartItems = $(".items-in-cart").find(".minicart-items");
                    let miniCartItemsWrapper = $(".minicart-items-wrapper").find(".minicart-items");
                    let recurredGrandTotal = $(document.getElementById("recurred-grand-total"));
                    let paymentFormType = typeof window.checkoutConfig.payment !== "undefined" ? window.checkoutConfig.payment.ebizcharge.paymentFormType : 1;


                    let recurringTotalAmount = 0;
                    let recurredItems = 0;
                    let unRecurredItems = 0;
                    let isRecurringOnly = false;

                    if (Number(paymentFormType) === 2) {
                        $(".grand").hide();
                    }

                    if (typeof quoteItemData !== "undefined") {
                        $.each(quoteItemData, function (key, quoteItem) {
                            if (typeof quoteItem.recurring.frequency !== "undefined" && quoteItem.recurring.frequency !== "") {
                                recurringTotalAmount += Number(quoteItem.price);
                                recurredItems++;
                            } else {
                                unRecurredItems++;
                            }
                        });

                    }
                    let shippingAmount = typeof quote.shippingMethod() && quote.shippingMethod() ? quote.shippingMethod().amount : 0;

                    if (Number(unRecurredItems) === 0 && recurringTotalAmount > 0) {
                        grandTotal = Number(grandTotal);

                        // if (!recurredGrandTotal.length) {

                        if (typeof document.getElementById("recurred-grand-total") !== "undefined") {
                            $(document.getElementById("recurred-grand-total")).remove();
                        }
                        miniCartItemsWrapper.append("<div id='recurred-grand-total'><h3><strong class='grand totals '>Order Total :  " + this.getFormattedPrice(grandTotal) + "</strong></h3></div>")
                        //}
                        grandTotal = 0;
                        if (!miniCartItems.is(":visible")) {
                            miniCartItemsTitle.trigger("click");
                        }
                        isRecurringOnly = true;
                    }
                    if (Number(recurredItems) > 0) {
                        grantTotalsTitleHtml.text("Total Due Today");
                        if (!miniCartItems.is(":visible")) {
                            miniCartItemsTitle.trigger("click");
                        }
                    }
                    if (isRecurringOnly) {
                        const taxAmount = setInterval(function () {
                            let totalsAmountTaxAmountHtml = $(".totals-tax").find(".amount").find(".price") ? $(".totals-tax").find(".amount").find(".price") : $(".totals-tax").find(".price");
                            let totalsTaxAmount = $(".totals-tax").find(".price");
                            totalsAmountHtml.html(mainObj.getFormattedPrice(0));
                            totalsTaxAmount.html(mainObj.getFormattedPrice(0));
                            totalsShippingAMountHtml.html(mainObj.getFormattedPrice(0));
                            totalsAmountTaxAmountHtml.html(mainObj.getFormattedPrice(0));

                            clearInterval(taxAmount);
                        }, 200);
                        grantTotalsTitleHtml.text("Total Due Today");
                    }

                    return grandTotal;

                },
                /**
                 *
                 * @returns {*}
                 */
                getEbizcharge: function () {
                    return typeof window.checkoutConfig.payment.ebizcharge !== "undefined" ? window.checkoutConfig.payment.ebizcharge : null;
                },
                /**
                 *
                 * @returns {boolean}
                 */
                isSurchargeEnabled: function () {
                    let isSurchargeEnabled = false;
                    let ebizcharge = this.getEbizcharge();
                    if (ebizcharge) {
                        isSurchargeEnabled = typeof (ebizcharge.surchargeEnabled) !== "undefined" ? ebizcharge.surchargeEnabled : false;
                    }
                    return isSurchargeEnabled;
                },

                /**
                 * render shipping Amount
                 */
                renderShippingAmount: function () {
                    return typeof quote.shippingMethod() && quote.shippingMethod() ? quote.shippingMethod().amount : 0;

                },
                /**
                 * get Quote
                 * @returns {*}
                 */
                getQuote: function () {
                    return quote;
                }


            };
        }

        /**
         *  get Grand Total
         */
        return function (grandTotal) {
            return grandTotal.extend(grandTotalMixin);
        };
    });
