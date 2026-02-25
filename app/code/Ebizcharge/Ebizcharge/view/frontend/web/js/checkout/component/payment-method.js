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
        'jquery/ui',
        'Magento_Checkout/js/model/payment/renderer-list',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Ebizcharge_Ebizcharge/js/popup/validation-response',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'ko',
        "uiComponent",
        'domReady!',
        'mage/translate',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/model/messageList',
    ], function ($, jui, paymentList, ccForm, paymentInfo, ccValidator, additionalValidators, AvsCvvValidation, modal, url, ko, uiComponent, dom, translate, customerData, globalMessageList) {
        'use strict';

        return function main(config)
        {
            //  console.log(config);

            /**
             * Ebizcharge Payment Method
             * Renderer
             * @type {{init: init}}
             */
            var EBizChargePaymentMethodRenderer = {

                tab_container: $("." + config.payment_method.tab_container),
                payment_option_types: [
                    'ebiz-payment-option-type-add-new-cards',
                    'ebiz-payment-option-type-saved-cards',
                    'ebiz-payment-option-type-update-cards',
                    'ebiz-payment-option-type-saved-ach',
                    'ebiz-payment-option-type-add-new-ach'
                ],
                isAutoSaveCreditCard: config.payment_method.isAutoSaveCreditCard,

                /**
                 * is Credit Cards are Active and Allowed
                 */
                is_credit_card_allowed: config.payment_method.is_credit_card_active,

                /**
                 * is ACH payment type
                 * is allowed and active
                 *
                 */
                is_ach_allowed: config.payment_method.is_ach_active,

                /**
                 * payment continue method
                 */
                payment_continue_btn: $(document.getElementById("parent-payment-continue")),

                defaults: {
                    multiShippingBillingFormId: 'multishipping-billing-form',
                    goToReviewOrderBtn: 'payment-continue',
                    ebizChargeNewCard: 'ebiz-payment-option-type-add-new-cards',
                    ebizNewCardPanelId: '#ebiz-payment-option-type-add-new-cards-panel',
                    ebizSaveMethodCheckboxId: '#' + window.checkoutConfig.payment.ebizcharge.code + '_save_payment'
                },

                /**
                 * Init Function
                 *
                 * @param $
                 * @param jui
                 * @param paymentList
                 * @param ccForm
                 * @param paymentInfo
                 * @param ccValidator
                 * @param url
                 * @param ko
                 * @param uiComponent
                 * @param dom
                 * @param translate
                 */
                init: function ($, jui, paymentList, ccForm, paymentInfo, ccValidator, url, ko, uiComponent, dom, translate) {

                    /**
                     * render Tabs
                     */
                    this.renderTabs($, jui, translate);

                    /**
                     * Render Tab Panels
                     */
                    this.renderTabPanels($, jui, translate);

                    /**
                     *
                     * Validate Text Size
                     */
                    this.validateTextSize($, jui, translate);

                    /**
                     * is credit card is
                     * allowed
                     * **/
                    if (this.is_credit_card_allowed) {
                        jQuery(document.getElementById("ebiz-tab-pay-by-card")).trigger("click");
                    }

                    /**
                     * if is ACH is
                     * allowed
                     * **/
                    if (this.is_ach_allowed) {
                        jQuery(document.getElementById("ebiz-tab-pay-by-ach")).trigger("click");
                    }

                    /**
                     * if ACH and Credit both are allowed
                     *
                     * **/
                    if (this.is_credit_card_allowed && this.is_ach_allowed) {
                        jQuery(document.getElementById("ebiz-tab-pay-by-card")).trigger("click");
                    }

                    /** if both are disabled **/
                    if (!this.is_ach_allowed && !this.is_credit_card_allowed) {
                        /** payment button gets hide **/
                        this.payment_continue_btn.hide();
                    }

                    /**
                     * Check if AVS and zip code is valid
                     * customerCardModel
                     */
                    this.processValidateAvsAction($);

                    let self = this;
                    $(document.getElementById(self.defaults.multiShippingBillingFormId)).on('submit', function (e) {
                        if (!$(document.getElementById(self.defaults.multiShippingBillingFormId)).valid()) {
                            return false;
                        }
                       if ($('#' + self.defaults.ebizChargeNewCard).is(':checked') && !EBizChargeAvsCvvValidator.forceSubmit /*&& !EBizChargeAvsCvvValidator.isPciComplianceEnabled*/) {
                           e.preventDefault();
                           self.validateAvsCvvZipCode();
                       }
                    });

                    //let self = this;
                    $(document.getElementsByClassName('multishipping-saved-cards')).on('change', function (e) {
                        self.cardChanged(this.value);
                    });
                },

                processValidateAvsAction: function ($) {

                    let orderSubmitButton = $(document.getElementById("submit_order_top_button"));
                    //console.log(orderSubmitButton.attr("id"));

                    orderSubmitButton.bind("click keypress", function (evt) {
                        //console.log(evt.target.id);
                        evt.preventDefault();
                    })
                },

                /**
                 * Validate Avs Cvv Zip Code
                 */
                validateAvsCvvZipCode: function () {
                    let checkoutForm = $(document.getElementById(this.defaults.multiShippingBillingFormId));
                    let ebizMageConfig = window.checkoutConfig.payment.ebizcharge;
                    let isAvsCvvZipEnabled = ebizMageConfig.isAvsCvvZipEnabled;
                    if (isAvsCvvZipEnabled) {
                        checkoutForm.append($('<input>').attr({
                            type: 'hidden',
                            id: 'is_ajax',
                            name: 'is_ajax',
                            value: '1'
                        }));
                        EBizChargeAvsCvvValidator.validateAvsCvvActionUrl = ebizMageConfig.cardAvsCvvValidationUrl;
                        EBizChargeAvsCvvValidator.popupModal = modal;
                        EBizChargeAvsCvvValidator.popupModelId = "avs-cvv-warnings-panel";
                        EBizChargeAvsCvvValidator.popupTitle = $.mage.__("Security mismatch dialog box...");
                        EBizChargeAvsCvvValidator.avsAddressMsg = "avs-address-msg";
                        EBizChargeAvsCvvValidator.avsPostalCodeMsg = "avs-postal-code-msg";
                        EBizChargeAvsCvvValidator.cvvCvv2Msg = "cvv-cvv2-msg";
                        EBizChargeAvsCvvValidator.cardMessageResponse = "card-message-response";
                        EBizChargeAvsCvvValidator.avsSaveActionButton = "avs-response-save-button";
                        /** Check if the AVS and CVV are enabled **/
                        checkoutForm.trigger('processStop');
                        EBizChargeAvsCvvValidator.popUpButtons = [
                            {
                                text: $.mage.__('Review Your Order Anyway'),
                                class: 'action primary avs-response-save-button',
                                click: function (evt) {
                                    EBizChargeAvsCvvValidator.forceSubmit = true;
                                    checkoutForm.submit();
                                    this.closeModal();
                                }
                            }, {
                                text: $.mage.__('Cancel'),
                                class: 'action avs-response-cancel-button',
                                click: function (evt) {
                                    this.closeModal();
                                }
                            }
                        ];
                        var avsCvvZipPromise = EBizChargeAvsCvvValidator.processValidAddToCardAction(
                            $,
                            EBizChargeAvsCvvValidator.validateAvsCvvActionUrl,
                            checkoutForm,
                            modal,
                            customerData,
                            globalMessageList
                        );

                        //if (!avsCvvZipPromise) {
                            //$('#' + this.defaults.multiShippingBillingFormId).trigger('submit');
                            //$(document.getElementById(this.defaults.multiShippingBillingFormId)).unbind('submit').submit();
                        //}
                    }
                },

                /**
                 * validate Text size
                 *
                 * @param $
                 * @param jui
                 * @param translate
                 */
                validateTextSize: function ($, jui, translate) {
                    /** tab_container **/
                    let tab_container = this.tab_container;

                    tab_container.bind("click keypress", function (evt) {
                        let targetObj = $(evt.target);
                        let classLists = targetObj.attr('class') ? targetObj.attr('class').split(/\s+/) : [];

                        let maxLengthClass = "maximum-length";
                        let minLengthClass = "minimum-length";
                        let isValid = true;
                        /**
                         * Loop Through The class lists
                         */
                        $.each(classLists, function (k, className) {
                            if (className.indexOf(maxLengthClass) >= 0) {
                                let maxClassNames = className.split("-");
                                let maxLength = parseInt(maxClassNames[2]);
                                if (targetObj.val().length >= maxLength) {
                                    isValid = false;
                                }
                            }
                        });
                        return isValid;

                    });
                },

                /**
                 * Render Tabs
                 * @param $
                 * @param jui
                 * @param translation
                 */
                renderTabs: function ($, jui, translation) {

                    /**
                     * Ui Tab Titles
                     */
                    let uiTabTitles = config.payment_method.tabs_titles;

                    /**
                     * by default click the default Method
                     */
                    $(document.getElementById(this.payment_option_types[0])).trigger("click");

                    /**
                     * Main EBizCharge
                     * Method Container
                     */
                    $("#main-method-container").bind('click keypress', function (evt) {
                        //console.log(evt.target.id);
                        let current_target = evt.target.id;

                        /**
                         * Tab title Panels to select
                         */
                        $('.tab-label').removeClass('active-tab');
                        /**
                         * Tab Panel Containers Panels
                         */
                        $('.tab-container').removeClass('active-tab-panel');

                        if (current_target !== '') {
                            $(document.getElementById(current_target)).parent().addClass('active-tab');

                            let currentTabTitleId = $(document.getElementById(current_target)).parent().attr('id');
                            let currentTabId = current_target;
                            /**
                             * Render Panels
                             */
                            EBizChargePaymentMethodRenderer.renderPanels($, jui, translation, currentTabId);

                            /**
                             *
                             * EBizcharge Payment Method Renderer
                             */
                            EBizChargePaymentMethodRenderer.selectDefaultMethod($, jui, translation, currentTabId);

                            EBizChargePaymentMethodRenderer.autoSaveCreditCardCheckbox($);
                        }
                    });


                },

                /**
                 * Select Default Method
                 *
                 * @param $
                 * @param jui
                 * @param translation
                 * @param currentTabId
                 */
                selectDefaultMethod: function ($, jui, translation, currentTabId) {
                    let paymentMethods = this.payment_option_types;
                    let currentSelectedMethod = paymentMethods[0];

                    if (currentTabId.indexOf("ach") > 0) {
                        currentSelectedMethod = paymentMethods[4];
                    }

                    $.each(paymentMethods, function (k, tId) {
                        if ($(document.getElementById(tId)).is(":checked")) {
                            currentSelectedMethod = tId;
                        }
                    });
                    $(document.getElementById(currentSelectedMethod)).trigger("click");
                },

                /**
                 * Render Tab Panels
                 * @param $
                 * @param jui
                 * @param translate
                 * @param defaultPanelId
                 */
                renderTabPanels: function ($, jui, translate, defaultPanelId) {
                    this.tab_container.bind('click keypress', function (evt) {
                        // console.log("currentId = "+evt.target.id);
                        let currentPaymentOptionTypeId = evt.target.id;

                        if (currentPaymentOptionTypeId !== '') {
                            /**
                             * update option type
                             */
                            EBizChargePaymentMethodRenderer.updateOptionType($, jui, translate, currentPaymentOptionTypeId);
                        }
                    });

                },

                /**
                 * Update Option Type
                 *
                 * @param $
                 * @param jui
                 * @param translate
                 * @param defaultPanelId
                 */
                updateOptionType: function ($, jui, translate, defaultPanelId) {

                    let self = this;
                    /** each of the payment options **/
                    $.each(this.payment_option_types, function (k, objId) {

                        if (objId == defaultPanelId) {

                            $("#" + objId + "-panel :input").not(self.defaults.ebizSaveMethodCheckboxId)
                                .prop("checked", false);
                            /**
                             * hiding the payment panels
                             */
                            $('.tab-panels').hide();
                            $(".tab-panels :input").not(self.defaults.ebizSaveMethodCheckboxId)
                                .attr("disabled", true);

                            $("#" + objId + "-panel").show().slideDown('slow');
                            $("#" + objId + "-panel :input").not(self.defaults.ebizSaveMethodCheckboxId)
                                .attr("disabled", false);

                            /** ebzc_option update value **/
                            let selectedVal = $("#" + defaultPanelId);
                        }
                    });
                },

                /**
                 * Render Panels
                 * @param $
                 * @param jui
                 * @param translation
                 * @param currentTabId
                 */
                renderPanels: function ($, jui, translation, currentTabId) {

                    if (currentTabId == 'ebiz-tab-pay-by-ach') {
                        $(document.getElementById('pay-by-bank-container')).addClass('active-tab-panel');
                    }
                    if (currentTabId == 'ebiz-tab-pay-by-card') {
                        $(document.getElementById('pay-by-card-container')).addClass('active-tab-panel');
                    }
                },

                /**
                 * Get payment method code
                 *
                 * @returns {string}
                 */
                getCode: function () {
                    return window.checkoutConfig.payment.ebizcharge.code;
                },

                /**
                 * Get saved credits cards
                 *
                 * @returns {[]}
                 */
                getSavedCards: function () {
                    return config.payment_method.savedCards;
                },

                /**
                 * Get selected card data
                 *
                 * @param selectedCardId
                 * @returns {{type: null, cvv: *, paymentMethod: null}}
                 */
                getSelectedCardData: function (selectedCardId = 0) {
                    let cardId = selectedCardId ? selectedCardId : $('#' + this.getCode() + '_ebzc_method').val();
                    let cardType = null;
                    let paymentMethod = null;
                    let savedCards = this.getSavedCards();
                    for (let i = 0; i < savedCards.length; i++) {
                        let obj = savedCards[i];
                        if (obj.MethodID === cardId) {
                            cardType = obj.CardType;
                            paymentMethod = obj;
                            break;
                        }
                    }
                    return {
                        'type': cardType,
                        //'cvv': this.creditCardVerificationNumber().length,
                        'paymentMethod': paymentMethod,
                    };

                },

                /**
                 * Update relevant fields (expiration, billing & zip) against selected card.
                 *
                 * @param selectedCardId
                 */
                updateCardDetails: function (selectedCardId = 0) {
                    let selectedCard = this.getSelectedCardData(selectedCardId);
                    if (selectedCard != null && selectedCard.paymentMethod != null) {
                        let cardData = selectedCard.paymentMethod;
                        let expiryData = cardData.CardExpiration;
                        let expiry = expiryData.split('-');
                        let month = expiry[1] ?? 1;

                        if (month < 10) {
                            month = expiry[1].substr(1);
                        }

                        $('#' + this.getCode() + '_avs_street').val(cardData.AvsStreet);
                        $('#' + this.getCode() + '_avs_zip').val(cardData.AvsZip);
                        $('#' + this.getCode() + '_expiration_yr').val(expiry[0]);
                        $('#' + this.getCode() + '_expiration').val(month);
                    }
                },

                /**
                 * On card change
                 *
                 * @param cardId
                 */
                cardChanged: function (cardId) {
                    if (cardId) {
                        //console.log(cardId);
                        this.updateCardDetails(cardId);
                    }
                },

                /**
                 * To check if auto save card is active then disable save card checkbox
                 *
                 * @param $
                 */
                autoSaveCreditCardCheckbox: function ($) {
                    if (this.isAutoSaveCreditCard) {
                        $(this.defaults.ebizNewCardPanelId + ' ' + this.defaults.ebizSaveMethodCheckboxId)
                            .prop('disabled', 'checked');
                    }
                }
            }

            /**
             * EBizcharge Method
             * Renderer Init Method
             */
            EBizChargePaymentMethodRenderer.init($, jui, paymentList, ccForm, paymentInfo, ccValidator, url, ko, uiComponent, dom, translate);

        }
    });

