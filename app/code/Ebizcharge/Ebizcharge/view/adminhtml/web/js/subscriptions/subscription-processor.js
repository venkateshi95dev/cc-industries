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
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'mage/mage',
        'mage/calendar',
        'mage/backend/validation',
        'Magento_Catalog/js/price-utils',
        'ko'


    ], function ($, modal, url, mage, calandar, mageValidation, priceUtils, ko) {

        'use_strict';

        return SubscriptionProcessor = {

            selectdivProduct: $(document.getElementById('selectdivProduct')),
            subscriptionFormObj: $(document.getElementById("subscription-add-form")),
            customerSearchBox: $(document.getElementById('customer-search-box')),
            productSearchBox: $(document.getElementById('product-search-box')),
            subscribePreAction: $(document.getElementsByClassName("subscribe-pre-action")),
           // currencySymbol: ko.observable(currency.get('currencySymbol')),

            /**
             * init function
             *
             * @param $
             * @param mageData
             * @private
             */
            _init: function ($, mageData) {
                /**
                 * product Search Box
                 *
                 * @type {HTMLElement}
                 */
                let productSearchSuggessions = $("#suggestion-list-rows li");
                let cardNewOption = $("#card_new");
                let bankNewOption = $("#bank_new");
                let cardSaveOption = $("#card_saved");
                let bankSaveOption = $("#bank_saved");
                let savedCardPanel = $("#my_card_saved");
                let savedBankPanel = $("#my_bank_saved");

                this.subscriptionFormObj.on("click keypress", "li", function (evt) {

                    if (SubscriptionProcessor.customerSearchBox.val() !== "" ) {
                        SubscriptionProcessor.subscribePreAction.prop("disabled", false);
                        if(cardNewOption.is(":checked")){
                            savedCardPanel.find("select, input").prop("disabled", true);
                        }
                        if(bankNewOption.is(":checked")){
                            savedBankPanel.find("select, input").prop("disabled", true);
                        }
                        /**
                         * prepare quote
                         */
                        SubscriptionProcessor.quoteAjaxProcessor($, mageData);
                    }
                });

                /**
                 * for Elements
                 * @type {HTMLCollectionOf<Element>}
                 */
                let formElements = document.getElementsByClassName("quote-updater-event");

                /**
                 * Form Elements on Change update quote
                 */
                $(formElements).on("change", function (evt) {
                    if (SubscriptionProcessor.customerSearchBox.val() !== "" && SubscriptionProcessor.productSearchBox.val() !== "") {
                        SubscriptionProcessor.subscribePreAction.prop("disabled", false);
                        if(cardNewOption.is(":checked")){
                            savedCardPanel.find("select, input").prop("disabled", true);
                        }
                        if(bankNewOption.is(":checked")){
                            savedBankPanel.find("select, input").prop("disabled", true);
                        }
                        /**
                         * prepare quote
                         */
                        SubscriptionProcessor.quoteAjaxProcessor($, mageData);
                    }
                });


            },
            /**
             * quote Ajax Processor
             *
             * @param $
             * @param quoteParams
             */
            quoteAjaxProcessor: function ($, quoteParams = null) {

                let subscriptionProcessorUrl = quoteParams.quoteProcessorActionUrl;
                let subscriptionForm = $(document.getElementById("subscription-add-form"));
                let subscriptionData = subscriptionForm.serializeArray();

                /**
                 * Ajax Processor
                 */
                $.ajax(
                    {
                        type: "POST",
                        url: subscriptionProcessorUrl,
                        dataType: "JSON",
                        data: subscriptionData,
                        showLoader: true,
                        /**
                         * Data
                         * @param processorData
                         */
                        success: function (processorData) {
                            processorData = processorData.html_data;
                            SubscriptionProcessor.setQuoteData($, quoteParams, processorData);
                        },
                        /**
                         * XHR Error
                         * @param xhrError
                         */
                        error: function (xhrError) {
                            console.log(xhrError);
                        }
                    });

            },

            /**
             *
             * @param $
             * @param quoteParams
             * @param quoteData
             */
            setQuoteData: function ($, quoteParams = null, quoteData = null) {

                let quoteItemsHtml = '<br/><table class="product-listing-table" style="border:1px solid #ccc">';
                let quoteItemsInnerHtml = '<tbody></tbody><tr><td>Please select product.</td></tr>';

                let ecSurchargeAmount = document.getElementById("ec-surcharge-amount");
                let ecSurchargePercentage = document.getElementById("ec-surcharge-percentage");
                let taxAmount = document.getElementById("tax-amount");
                let quoteId = document.getElementById("quote-id");
                let shippingAmount = document.getElementById("shipping-amount");
                let quoteItems = $(document.getElementById("subscribed-items"));
                let quoteGrandTotal = document.getElementById("grand-total");
                let priceFormatOptions = {
                    decimalSymbol: '.',
                    groupLength: 3,
                    groupSymbol: ",",
                    integerRequired: false,
                    pattern: "$%s",
                    precision: 2,
                    requiredPrecision: 2
                };

                if (typeof quoteData.entity_id !== "undefined") {
                    let quoteDataItems = typeof quoteData !== "undefined" ? quoteData.items : [];
                    let totalAmount = Number(quoteData.grand_total);
                    ecSurchargeAmount.value = quoteData.ec_surcharge_amount ?? 0;
                    ecSurchargePercentage.value = quoteData.ec_surcharge_percentage ?? 0;
                    taxAmount.value = quoteData.tax_amount ?? 0;
                    quoteId.value = quoteData.entity_id ?? 0;
                    shippingAmount.value = quoteData.shipping_amount ?? 0;
                    quoteGrandTotal.value = quoteData.grand_total ?? 0;

                    if(Number(quoteData.ec_surcharge_amount) > 0){
                        totalAmount = totalAmount + Number(quoteData.ec_surcharge_amount);
                    }

                    if (typeof quoteDataItems !== "undefined" && quoteDataItems.length > 0) {
                        quoteItemsInnerHtml = '<tbody><tr class="itemTr"><td><b>Item Information</b><td><b>Item Sku</b></td><td><b>Subscribed Qty</b></td><td><b>Price</b></td><td><b>Tax</b></td><td><b>Surcharge</b></td> <td><b>Shipping</b></td><td><b>Total Amount</b></td></tr>';
                        $.each(quoteDataItems, function (indx, quoteItem) {
                            if (typeof quoteItem.item_id !== "undefined") {
                                quoteItemsInnerHtml += '<tr>';
                                quoteItemsInnerHtml += '<td>' + quoteItem.name + '</td>';
                                quoteItemsInnerHtml += '<td>' + quoteItem.sku + '</td>';
                                quoteItemsInnerHtml += '<td>' + quoteItem.qty + '</td>';
                                quoteItemsInnerHtml += '<td data-bind="text: formattedPrice">'  + priceUtils.formatPrice(quoteItem.price, priceFormatOptions) + '</td>';
                                quoteItemsInnerHtml += '<td data-bind="text: formattedPrice">'  + priceUtils.formatPrice(quoteItem.tax_amount, priceFormatOptions) + '</td>';
                                quoteItemsInnerHtml += '<td data-bind="text: formattedPrice">'  + priceUtils.formatPrice(quoteData.ec_surcharge_amount, priceFormatOptions) + '</td>';
                                quoteItemsInnerHtml += '<td data-bind="text: formattedPrice">'  + priceUtils.formatPrice(quoteData.shipping_amount, priceFormatOptions) + '</td>';
                                quoteItemsInnerHtml += '<td data-bind="text: formattedPrice">'  + priceUtils.formatPrice(totalAmount, priceFormatOptions) + '</td>';
                                quoteItemsInnerHtml += '</tr>';
                            }
                        });
                    }

                    quoteItemsHtml += quoteItemsInnerHtml
                    quoteItemsHtml += "</tbody>";
                    quoteItemsHtml += '</table>';

                    quoteItems.html(quoteItemsHtml);
                }

            }

        };

    });
