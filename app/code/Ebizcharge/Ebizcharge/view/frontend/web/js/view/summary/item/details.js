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
        'uiComponent',
        'mage/translate',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Ebizcharge_Ebizcharge/js/view/checkout/summary/ebiz-surcharge',
        'Magento_Customer/js/customer-data',
        'underscore'

    ],
    function ($, Component, $t, priceUtils, quote, totals, ebizSurcharge, customerData, _) {

        "use strict";
        var quoteItemData = window.checkoutConfig.quoteItemData;

        return Component.extend({
                defaults: {
                    template: 'Ebizcharge_Ebizcharge/summary/item/details'
                },
                quoteItemData: quoteItemData,
                /**
                 * Get Value
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                getValue: function (quoteItem) {
                    return quoteItem.name;
                },
                /**
                 *
                 * @returns {*}
                 */
                getEbizSurcharge: function () {
                    return typeof window.checkoutConfig.payment.ebizcharge !== "undefined" ? window.checkoutConfig.payment.ebizcharge : null;
                },

                /**
                 *
                 * @param quoteItem
                 * @returns {{}}
                 */
                getRecurrings: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    let recurringData = {};
                    if (typeof (item.recurring) !== "undefined") {
                        recurringData = quoteItem.recurring
                    }
                    return recurringData;
                },
                /**
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                getRecurringPriceSubtotal: function (quoteItem) {
                    let subScriptionSubtotal = $t("");
                    let item = this.getItem(quoteItem.item_id);
                    let quoteItemData = checkoutConfig.quoteItemData;
                    let itemQty = Number(item.qty);
                    let itemPrice = Number(item.price);
                    let rowSubTotal = Number(itemQty * itemPrice);
                    let taxAmount = Number(item.tax_amount);
                    let shippingAmount = Number(item.ec_shipping_amount);
                    let surchargeAmount = Number(item.ec_surcharge_amount);
                   // console.log([taxAmount, shippingAmount, surchargeAmount]);

                    subScriptionSubtotal = priceUtils.formatPrice(rowSubTotal, {});
                    subScriptionSubtotal += " /Order";

                    return subScriptionSubtotal;
                },

            /**
             *
             * @param quoteItem
             * @returns {*}
             */
                getRecurringPrice: function (quoteItem) {
                    var subScriptionTotal = $t("");
                    var item = this.getItem(quoteItem.item_id);
                    var quoteItemData = checkoutConfig.quoteItemData;
                    var recurringItems = 0;
                    var unrecurringItems = 0;

                    var shippingMethod = this.getShippingMethod();
                    var itemQty = Number(item.qty);
                    var shippingAmount = typeof quote.shippingMethod() && quote.shippingMethod() ? quote.shippingMethod().amount : 0;
                    var itemPrice = 0;
                    var taxAmount = Number(item.tax_amount) ?? 0;
                    var rowTotal = Number(item.row_total_incl_tax);

                    if (typeof (quoteItemData) !== "undefined") {
                        $.each(quoteItemData, function (key, quoteItem) {
                            if (typeof (quoteItem.recurring.frequency) !== "undefined" && quoteItem.recurring.frequency) {
                                recurringItems++;
                            } else {
                                unrecurringItems++;
                            }
                        });
                    }


                    if (typeof (item.recurring) !== "undefined" && typeof (item.price_incl_tax) !== "undefined") {
                        if (this.getFrequency(quoteItem)) {
                            if (rowTotal === 0) {
                                rowTotal = Number(item.price_incl_tax) * Number(itemQty);
                                if (taxAmount === 0) {
                                    taxAmount = (Number(item.tax_percent) * Number(rowTotal)) / 100;
                                }
                                rowTotal += Number(taxAmount);
                            }
                            var shippingAmount = this.getRecurringShipping(quoteItem);
                            if (Number(shippingAmount) > 0) {
                                rowTotal += Number(shippingAmount);
                            }
                        }
                    }

                    subScriptionTotal += priceUtils.formatPrice(rowTotal, {});
                    //  subScriptionTotal += $t(" x") + itemQty;
                    //  subScriptionTotal += $t("=") + priceUtils.formatPrice(item.price_incl_tax, {});
                    subScriptionTotal += $t("/Order");
                    return subScriptionTotal;
                },
                /**
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                getOrderTotals: function (quoteItem) {
                    return quote.getTotals()();
                },
                /**
                 *
                 * @param quoteItem
                 * @returns {number}
                 */
                getTaxAmount: function (quoteItem) {
                    let item = this.getItem(quoteItem.item_id);
                    let taxAmount = typeof item.tax_amount !== "undefined" ? item.tax_amount : 0;
                    console.log([quoteItem, taxAmount]);
                    return taxAmount;
                },


                /**
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                renderTaxAmount: function (quoteItem) {
                    let taxAmount = this.getTaxAmount(quoteItem);
                    return priceUtils.formatPrice(taxAmount, {});
                },
                /**
                 *
                 * @param quoteItem
                 * @returns {{}}
                 */
                getPrice: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    return priceUtils.formatPrice(item.price, {});
                },
            /**
             *
             * @param quoteItem
             * @returns {{}}
             */
            getName: function (quoteItem) {
                let item = this.getItem(quoteItem.item_id);
                return item.name;
            },
                /**
                 * get Recurring Shipping Amount
                 * @returns {*}
                 */
                getRecurringShipping: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var shippingAmountPrice = $t(0);
                    var shippingMethod = this.getShippingMethod();
                    var shippingAmount = typeof quote.shippingMethod() && quote.shippingMethod() ? quote.shippingMethod().amount : 0;
                    var quoteItemData = checkoutConfig.quoteItemData;
                    var totalOrderedItems = checkoutConfig.quoteItemData.length;
                    var isItemBasedShipping = false;
                    var perItemShippingAmount = 0;
                    var recurringItems = 0;
                    var unrecurringItems = 0;
                    var recurredItemsShipping = 0;
                    var remainderShippingAmount = 0;
                    var unrecurringItemsShipping = 0;

                    if (typeof quoteItemData !== "undefined") {
                        $.each(quoteItemData, function (key, quoteItem) {
                            if (typeof (quoteItem.recurring.frequency) !== "undefined" && quoteItem.recurring.frequency) {
                                recurringItems++;
                            } else {
                                unrecurringItems++;
                            }
                        });
                    }
                    if (shippingAmount > 0) {
                        perItemShippingAmount = parseFloat(shippingAmount) / parseFloat(unrecurringItems);
                    }

                    if (typeof shippingMethod !== "undefined" && shippingMethod !== null) {
                        recurredItemsShipping = perItemShippingAmount * Number(recurringItems);
                        if (Number(unrecurringItems) > 0) {
                            unrecurringItemsShipping = parseFloat(shippingAmount) / Number(unrecurringItems);
                        }

                        if (recurredItemsShipping === recurredItemsShipping) {
                            isItemBasedShipping = true;
                        }
                        // console.log(isItemBasedShipping);
                        if (isItemBasedShipping && Number(recurringItems) > 0) {
                            shippingAmount = Number(shippingAmount);
                        }
                        if (isItemBasedShipping && Number(unrecurringItems) > 0) {
                            shippingAmount = Number(shippingAmount) / Number(unrecurringItems);
                        }
                    }

                    return shippingAmount;

                },

                /**
                 * recurringItemShippingAmount
                 * @param quoteItem
                 * @returns {*}
                 */
                recurringItemShippingAmount(quoteItem) {
                    var item = this.getItem(quoteItem.item_id);

                    var shippingAmount = this.getRecurringShipping(quoteItem);
                    var shippingAmountPrice = $t(0);
                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.subscribed) !== "undefined") {
                        if (item.recurring.subscribed) {
                            shippingAmountPrice = $t(priceUtils.formatPrice(shippingAmount, {}));
                        }
                    }
                    return shippingAmountPrice;
                },


                /**
                 *
                 * @returns {boolean}
                 */
                isSurchargeEnabled: function () {
                    var ebizSurcharge = this.getEbizSurcharge();
                    var isSurchargeEnabled = false;

                    if (ebizSurcharge) {
                        isSurchargeEnabled = ebizSurcharge.surchargeEnabled
                    }

                    return isSurchargeEnabled;
                },
                /**
                 *
                 * @returns {*|number}
                 */
                paymentFormType: function () {
                    return typeof window.checkoutConfig.payment !== "undefined" ? window.checkoutConfig.payment.ebizcharge.paymentFormType : "1";

                },

                /**
                 * get Surcharge Percentage
                 * @returns {string}
                 */
                getSurchargePercentage: function () {
                    var surchargePercentage = "0";
                    var ebizSurcharge = this.getEbizSurcharge();
                    if (this.isSurchargeEnabled()) {
                        surchargePercentage = typeof (ebizSurcharge.surchargeSettings.surchargePercentage) !== 'undefined' ? ebizSurcharge.surchargeSettings.surchargePercentage : '0';
                    }
                    return surchargePercentage;

                },


                /**
                 * get Shipping Method
                 *
                 * @returns {*}
                 */
                getShippingMethod: function () {
                    var shippingMethod = typeof (quote.shippingMethod()) !== "undefined" && quote.shippingMethod() ? quote.shippingMethod() : null;
                    return shippingMethod;
                },
                /**
                 * Get Quote
                 * @returns {*}
                 */
                getQuote: function () {
                    var quote = checkoutConfig.quoteData;
                    return quote;
                },

                /**
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                getRecurringSurcharge: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var recurringSurchargeAmount = priceUtils.formatPrice(0, {});
                    var totalOrderedItems = checkoutConfig.quoteItemData;
                    var currentCurrency = window.checkoutConfig.quoteData.currency_code ?? "";
                    var quoteShippingAmount = this.getRecurringShipping(quoteItem);
                    var currSymbol = priceUtils.formatPrice(0, {}).substring(0, 1);
                    var recurredTotalSurchargeAmount = 0;
                    var surchargeAmountRendererHtml = $t(" (" + this.getSurchargePercentage() + "%) ");

                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.subscribed) !== "undefined") {
                        if (item.recurring.subscribed !== false) {
                            var surchargePercentage = this.getSurchargePercentage() ?? 0;
                            var recurringSurchargeOrderTotals = $(document.getElementById("ebiz-summary-surcharge"));
                            recurringSurchargeAmount = recurringSurchargeOrderTotals.find(".price").text() ?? priceUtils.formatPrice(0, {});
                            let recurringTotalOrderedSurchargeAmount = recurringSurchargeAmount.replace(currSymbol, "");
                            if (typeof window.checkoutConfig.payment.ebizcharge.surchargeGrandTotal !== "undefined") {
                                recurringTotalOrderedSurchargeAmount = window.checkoutConfig.payment.ebizcharge.surchargeGrandTotal;
                            }

                            if (parseFloat(recurringTotalOrderedSurchargeAmount) > 0) {
                                let itemPrice = parseFloat(item.price_incl_tax);
                                let itemQty = parseFloat(item.qty);
                                let itemTaxPercentage = parseFloat(item.tax_percent);
                                let itemRowTotal = (itemPrice * itemQty);
                                let itemTaxAmount = parseFloat(itemTaxPercentage * itemRowTotal) / 100;

                                let itemGrandTotal = itemRowTotal + itemTaxAmount + parseFloat(quoteShippingAmount);
                                let surchargeAmount = parseFloat(itemGrandTotal * surchargePercentage) / 100;
                                recurringSurchargeAmount = priceUtils.formatPrice(surchargeAmount, {});

                            }

                        }
                    }

                    surchargeAmountRendererHtml += recurringSurchargeAmount;


                    return surchargeAmountRendererHtml;
                },
            /**
             *
             * @param quoteItem
             * @returns {*}
             */
                renderSurchargeAmount: function(quoteItem){
                    let surchargeAmountRendererHtml = $t(" (" + this.getSurchargePercentage() + "%) ");
                    let surchargeAmount = this.getRecurringSurcharge(quoteItem);
                    surchargeAmountRendererHtml += priceUtils.formatPrice(surchargeAmount, {});
                    return surchargeAmountRendererHtml;
                }
                ,
                /**
                 *
                 * @returns {"undefined"|"object"|"boolean"|"number"|"string"|"function"|"symbol"|"bigint"|number}
                 */
                getSurchargeAmount: function () {
                    var surchargeAmount = typeof window.checkoutConfig.payment.ebizcharge.surchargeAmount !== "undefined" ? window.checkoutConfig.payment.ebizcharge.surchargeAmount : 0;
                    var amount = 0;
                    if (surchargeAmount > 0) {
                        window.checkoutConfig.payment.ebizcharge.surchargeAmount = surchargeAmount;
                        //  amount = window.checkoutConfig.payment.ebizcharge.surchargeAmount;
                    }
                    return amount;

                }
                ,

                /**
                 * get Subscribed
                 *
                 * @param quoteItem
                 * @returns {boolean}
                 */
                getSubscribed: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var isSubscribed = "";
                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.subscribed) !== "undefined") {
                        if (item.recurring.subscribed !== false) {
                            isSubscribed = item.recurring.subscribed;
                        }
                    }
                    let subscribedTitle = $(".upcoming-subscription-title").hide().first().show();

                    item.subscribed = isSubscribed;
                    return isSubscribed;
                }
                ,

                /**
                 * Get Frequency
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                getFrequency: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var frequency = "";
                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.frequency) !== "undefined") {
                        if (item.recurring.frequency !== "") {
                            frequency = item.recurring.frequency;
                        }
                    }
                    return frequency;
                }
                ,
                /**
                 * Get Qty Subscribed
                 *
                 * @param quoteItem
                 * @returns {*}
                 */
                getQtySubscribed: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var item_qty = "";
                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.item_qty) !== "undefined") {
                        if (item.recurring.item_qty !== "") {
                            item_qty = item.recurring.item_qty;
                        }
                    }
                    return item_qty;
                }
                ,

                /**
                 * Get Data
                 *
                 * @param quoteItem
                 */
                getSdate: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var sdate = "";
                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.sdate) !== "undefined") {
                        if (item.recurring.sdate !== "") {
                            sdate = item.recurring.sdate;
                        }
                    }
                    return sdate;
                }
                ,
            /**
             *
             * @param quoteItem
             * @returns {string}
             */
                getEdate: function (quoteItem) {
                    var item = this.getItem(quoteItem.item_id);
                    var edate = "";
                    if (typeof (item.recurring) !== "undefined" && typeof (item.recurring.edate) !== "undefined") {
                        if (item.recurring.sdate !== "") {
                            edate = item.recurring.edate;
                        }
                    }
                    return edate;
                }
                ,

                /**
                 * Get Item
                 *
                 * @param item_id
                 * @returns {null}
                 */
                getItem: function (item_id) {
                    var itemElement = null;
                    _.each(this.quoteItemData, function (element, index) {
                        //this.each(this.quoteItemData, function(element, index)
                        if (Number(element.item_id) === Number(item_id)) {
                            itemElement = element;
                        }
                    });
                    return itemElement;
                }
            }
        )
            ;
    }
);
