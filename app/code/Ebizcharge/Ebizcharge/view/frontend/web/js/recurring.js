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
        'underscore',
        'mage/url',
        'mage/validation',
        'mage/mage',
        'mage/translate',
        'mage/calendar',
        'Ebizcharge_Ebizcharge/js/popup/validation-response',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/model/messageList'
    ],
    function ($, modal, _, url, validation, mage, $tr, cal, AvsCvvValidation, customerData, globalMessageList) {

        /**
         * adding recurring
         *
         */
        return function (configModelData) {

            /**
             *
             * @type {*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement}
             */

            /** Subscription Model **/

            /**
             * Subscription Model
             * @type {{recurringFrequency: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), _init: _init, renderRemoveProductSubscriptions: renderRemoveProductSubscriptions, recurringStartDate: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), recurringIndefinitely: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), recurringEndDate: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), resetProductSubscriptions(*), unRegisteredPopupCustomer: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), popUpOptions: {buttons: {text: *, class: string, click: (function(*): void)}[], responsive: boolean, innerScroll: boolean, type: string, title: *}, subscriptionControlOptions: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), addSubscribedProductToCart: addSubscribedProductToCart, customerLoginUrl: *, customerLoginButton: *, renderAddProductSubscriptions: renderAddProductSubscriptions, customerRegisterUrl: *, customerRegisterButton: *, productAddToCartForm: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), customerId: *, redirectToPageUrl: redirectToPageUrl, productSubscriptionForm: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement)}}
             */
            let SubscriptionModel = {
                dialgPopUpOptions: null,
                productActionUrl: configModelData.productActionUrl,
                productUpdateActionUrl: configModelData.productUpdateActionUrl,
                productUpdateDbActionUrl: configModelData.productUpdateDbActionUrl,
                currentProductType: configModelData.currentProductType,
                currentProductId: configModelData.currentProductId,
                getLoggedInCustomerId: configModelData.getLoggedInCustomerId,
                sendDbUpdate: configModelData.sendDbUpdate,
                cartItemID: configModelData.cartItemID,
                cartProductID: configModelData.cartProductID,
                unRegisteredPopupCustomer: $(document.getElementById(configModelData.unRegisteredCustomerPopup)),
                productSubscriptionForm: $(document.getElementById(configModelData.subscriptionProductForm)),
                customerLoginUrl: configModelData.customerLoginButtonUrl,
                customerRegisterUrl: configModelData.customerRegisterButtonUrl,
                customerLoginButton: configModelData.customerLoginButton,
                customerRegisterButton: configModelData.customerRegisterButton,
                subscriptionControlOptions: $("." + configModelData.subscriptionControlOptions),
                productAddToCartForm: $(document.getElementById(configModelData.productAddtoCartForm)),
                productAddToCartButton: $(document.getElementById(configModelData.productAddToCartButton)),
                productUpdateCartButton: $(document.getElementById(configModelData.productUpdateCartButton)),
                customerId: configModelData.getLoggedInCustomerId,
                recurringFrequency: $(document.getElementById(configModelData.recurringFrequency)),
                recurringStartDate: $(document.getElementById(configModelData.recurringStartDate)),
                recurringEndDate: $(document.getElementById(configModelData.recurringEndDate)),
                recurringIndefinitely: $(document.getElementById(configModelData.recurringIndefinitely)),
                invalidFrequencyError: $(document.getElementById(configModelData.invalidFrequencyError)),
                invalidDatesError: $(document.getElementById(configModelData.invalidDatesError)),
                mageError: $(document.getElementById(configModelData.mageError)),
                errorPopupModelAlreadyAdded: $(document.getElementById(configModelData.errorPopupModelAlreadyAdded)),
                errorPopupModelAlreadyExists: $(document.getElementById(configModelData.errorPopupModelAlreadyExists)),
                /**
                 * Edit Subscriptions Variables
                 */
                updateSubscribeUrl: configModelData.update_subscribe_url,
                unSubscribeUrl: configModelData.un_subscribe_url,
                recurringCustomerId: configModelData.recurring_customer_id,
                paymentMethodsDropdown: $(document.getElementById(configModelData.payment_methods_dropdown)),
                recurringPaymentMethodPanel: $(document.getElementById(configModelData.recurring_payment_method_panel)),
                recurringIndefinitelyChk: $(document.getElementById(configModelData.recurring_indefinitely)),
                isEndDateRecurringEnabled: configModelData.recEndDateDisabled,
                expireDate: $(document.getElementById(configModelData.expire_date)),
                recStartDate: $(document.getElementById(configModelData.recurring_start_date)),
                recEndDatePanel: $(document.getElementById(configModelData.recurring_end_date_panel)),
                cvv2CodeTxt: $(document.getElementById(configModelData.recurring_cvv2)),
                cvv2CodeLabel: $(document.getElementById(configModelData.recurring_cvv2_label)),
                recurringCvv2LabelSpan: $("."+configModelData.recurring_cvv2_label_span),
                saveSubscriptionButton: $(document.getElementById(configModelData.save_subscription_button)),
                unSubscribeButton: $(document.getElementById(configModelData.un_subscribe_button)),
                saveSubscriptionModel: $(document.getElementById(configModelData.save_subscription_model)),
                confirmSubscriptionModel: $(document.getElementById(configModelData.confirm_subscription_model)),
                suspendSubscriptionModel: $(document.getElementById(configModelData.suspend_subscription_model)),
                actionToolbarButtons: $(document.getElementById(configModelData.action_toolbar_buttons)),
                suspendButton: $(document.getElementById(configModelData.suspend_button)),
                subscriptionUpdateForm: $(document.getElementById(configModelData.subscription_update_form)),
                subscription_url: $(document.getElementById(configModelData.subscription_url)),
                currentPaymentType: "ACH",
                isSubscriptionEnabled: false,
                recurringUpdatePaymentmethod: $('#recurring-update-payment-method'),
                paymentMethodTabCc: $('#payment-tab-cc'),
                paymentMethodTabAch: $('#payment-tab-cc'),
                ebizOption: $('#ebiz_option'),
                ebizOptionType: $('#ebzc_option_type'),
                paymentOptionType: $('#payment_option_type'),
                paymentMethodsPanel: $('.payment-method-panel'),
                recurringPaymentMethodOption: '',
                recurringPaymentMethodOptionType: '',
                isDefaultCc: $("#is_default_cc"),
                isDefaultAch: $("#is_default_ach"),
                isDefault: $("#is_default"),
                reSubscribeChk: "re-subscribe-chk",
                paymentFormInput: $(".payment-form-input"),
                reSubscribeBtn: $("#re-subscription-btn"),
                isBankAccountsEnabled: $("#ach_allowed"),
                isCreditCardsEnabled: $("#credit_cards_allowed"),
                cardAvsValidationUrl: configModelData.cardAvsValidationUrl,
                isAvsCvvZipEnabled: configModelData.isAvsCvvZipEnabled,
                addNewPaymentMethodCheckbox: 'recurring-update-payment-method',
                pciAddNewMethodResponseUrl: configModelData.pciAddNewMethodResponseUrl,

                /**
                 * Initialized
                 * Product Subscription Model
                 *
                 * @param $
                 * @private
                 */
                _init: function ($) {

                    let dataForm = $('#subscription-update-form');

                    let ignore = null;

                    dataForm.mage('validation', {
                        rules: {},
                        messages: {},
                        ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden',

                        errorPlacement: function (error, element) {
                            if (element.is('#cc_exp_month')) {
                                jQuery('#cc_exp_year').after(error);
                            } else {
                                element.after(error);
                            }
                        }
                    });

                    let paymentStartDate = $("#payment-start-date");
                    paymentStartDate.bind("keypress click", function (evt) {

                        $("#recurring-start-date").trigger("click");
                    });

                    let paymentExpireDate = $("#payment-expire-date");
                    paymentExpireDate.bind("keypress click", function (evt) {

                        $("#recurring-expire-date").trigger("click");
                    });


                    /**
                     * redirecting to Customer Login Page
                     */
                    SubscriptionModel.redirectToPageUrl(SubscriptionModel.customerLoginButton, SubscriptionModel.customerLoginUrl,);

                    /**
                     * redirecting to Register Page
                     */
                    SubscriptionModel.redirectToPageUrl(SubscriptionModel.customerRegisterButton, SubscriptionModel.customerRegisterUrl);

                    /**
                     * render DateTimeDialog to start date and expiry date
                     */
                 //   SubscriptionModel.renderDateTimeDialog($, configModelData.recurringDateDialog, configModelData.recurringStartDate, configModelData.recurringEndDate);

                    SubscriptionModel.recurringFrequency.on("change", function(){
                        let selFrequency = SubscriptionModel.recurringFrequency.val();
                        let frequencyDays = SubscriptionModel.convertDaysFromRecurringFrequency($, selFrequency);
                        SubscriptionModel.renderDateTimeDialog($, configModelData.recurringDateDialog, configModelData.recurringStartDate, configModelData.recurringEndDate, frequencyDays);
                    });

                    /**
                     * recurring end date update payment method
                     */
                    SubscriptionModel.renderRecurringEndDate($);

                    /**
                     * Render Submit Subscription
                     */
                    SubscriptionModel.renderSubmitSubscription($);

                    /**
                     * Render Cvv Code Panel
                     *
                     */

                    SubscriptionModel.renderCvvCodePanel($);

                    /**
                     * Change Payment Method
                     */
                    SubscriptionModel.changePaymentMethod($);

                    /**
                     * Add new Payment Method
                     */
                    SubscriptionModel.addNewPaymentMethod($);

                    /**
                     * Subscription Model
                     * Add Susbscribed Product To Cart
                     */
                    SubscriptionModel.addSubscribedProductToCart($, false);

                    /**
                     * Update Recurring Payment Method
                     */
                    SubscriptionModel.updateRecurringPaymentMethod($, false);

                    /**
                     * render Re Subscriptions
                     */
                    SubscriptionModel.renderReSubscription($);

                    /**
                     * Render DateTime Calendar
                     */
                    SubscriptionModel.renderDateTimeCalendar($, "#recurring-start-date");

                    /**
                     * Render Datetime Calendar
                     */
                    SubscriptionModel.renderDateTimeCalendar($, "#recurring-expire-date");


                    /**
                     * Recurring start Date
                     */
                    $("#recurring-start-date").bind("change", function (evt) {
                        SubscriptionModel.validateRecurringDates($, "#recurring-start-date", "#recurring-expire-date");
                    });
                    /**
                     * Recurring Expire Date
                     */
                    $("#recurring-expire-date").bind("change", function (evt) {
                        SubscriptionModel.validateRecurringDates($, "#recurring-start-date", "#recurring-expire-date");
                    });

                },
                /**
                 * renderDateTimeCalendar
                 *
                 * @param $
                 * @param inputTxtId
                 */
                renderDateTimeCalendar: function ($, inputTxtObj = null) {

                    let minDate = new Date();
                    minDate.setDate(minDate.getDate() + 1);
                   // minDate.setDate(minDate.getDate());

                    if (inputTxtObj === "#recurring-expire-date") {
                        let minDate = new Date($("#recurring-start-date").val());
                        minDate.setDate(minDate.getDate() + 1);
                       // minDate.setDate(minDate.getDate());
                    }
                    $(inputTxtObj).calendar({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'Y-mm-dd',
                        showButtonPanel: true,
                        currentText: $tr('Go Today'),
                        closeText: $tr('Close'),
                        minDate: minDate,
                        showWeek: true,
                        showOn: "both"
                    });


                },
                /**
                 *
                 * @param $
                 * @param startDateId
                 * @param endDateId
                 * @returns {boolean}
                 */
                validateRecurringDates: function ($, startDateId = null, endDateId = null) {

                    let isValid = true;
                    let startDateTime = $(startDateId).val();
                    let expiryDateTime = $(endDateId).val();


                    let startDate = new Date(startDateTime);
                    let endDate = new Date(expiryDateTime);

                    if (startDate > endDate) {
                        alert("Oops, the Expiry recurring date is greater than the start date.");
                        startDate.setDate(startDate.getDate() + 1);
                       // startDate.setDate(startDate.getDate());
                        let expiryDate = $.datepicker.formatDate("yy-mm-dd", startDate);
                        $(endDateId).val(expiryDate);
                        isValid = false;
                    }
                    return isValid;
                },
                /**
                 * render Re subscriptions
                 * @param $
                 */
                renderReSubscription: function ($) {

                    /**
                     * Resubscribe CheckBox
                     */
                    let reSubScribeChkBox = $("#" + SubscriptionModel.reSubscribeChk);
                    let subscriptionRecurringForm = SubscriptionModel.productSubscriptionForm;
                    let recurringFormArray = subscriptionRecurringForm.serializeArray();
                    let endRecurringDate = SubscriptionModel.recurringEndDate;
                    let startRecurringDate = SubscriptionModel.recurringStartDate;

                    /**
                     * Resubscription Check box
                     */
                    reSubScribeChkBox.bind("click keypress", function (evt) {
                        if (reSubScribeChkBox.is(":checked")) {
                            endRecurringDate.removeClass("error");
                            $(".payment-form-input").prop("disabled", false);
                            $("#current-payment-method-panel").show();
                        } else {
                            $(".payment-form-input").prop("disabled", true);
                            $("#current-payment-method-panel").hide();

                        }
                    });

                },


                /**
                 * Change Payment Method
                 * @param $
                 */
                changePaymentMethod: function ($) {
                    $('#recurring-payment-methods-dropdown').bind("change", function (evt) {
                        SubscriptionModel.renderCvvCodePanel($);
                        if ($(this).val() === "") {
                            return false;
                        } else {
                            $(this).removeClass("mage-error");
                        }
                        $('.mage-error').hide();

                        if (SubscriptionModel.currentPaymentType == "CC") {
                          //  $('#recurring-cvv2').show().removeAttr('disabled');
                           // $('#recurring-cvv2-label').show();
                            SubscriptionModel.cvv2CodeTxt.show().removeAttr('disabled');
                            SubscriptionModel.cvv2CodeLabel.show();

                            SubscriptionModel.ebizOptionType.val("CC");
                            SubscriptionModel.ebizOption.val("CC");
                            SubscriptionModel.paymentOptionType.val("saved");

                        } else {
                          //  $('#recurring-cvv2').hide().attr('disabled', 'disabled');
                           // $('#recurring-cvv2-label').hide();
                            SubscriptionModel.cvv2CodeTxt.hide().attr('disabled', 'disabled');
                            SubscriptionModel.cvv2CodeLabel.hide();

                            SubscriptionModel.ebizOptionType.val("ACH");
                            SubscriptionModel.ebizOption.val("ACH");
                            SubscriptionModel.paymentOptionType.val("saved");
                        }
                    });


                },

                /**
                 * add new pyament Method
                 * @param $
                 */
                addNewPaymentMethod: function ($) {
                    $('#recurring-update-payment-method').bind('click keypress', function () {
                        $('#add-new-method').toggle(this.checked);
                        $('#recurring-cvv2, #recurring-payment-methods-dropdown').prop('disabled', this.checked);

                    });

                },
                /**
                 * Update Recurring Payment Method
                 * @param $
                 */
                updateRecurringPaymentMethod($) {
                    /**
                     * Subscription Model
                     */
                    SubscriptionModel.paymentMethodsPanel.bind("click keypress", function (evt) {

                        let targetId = evt.target.id;
                        let tabTypeCreditCard = SubscriptionModel.paymentMethodTabCc;
                        let tabTypeAch = SubscriptionModel.paymentMethodTabAch;
                        let updateRecurringPaymentMethod = SubscriptionModel.recurringUpdatePaymentmethod;
                        let ebizPaymentMethodOption = SubscriptionModel.ebizOption;
                        let ebizPaymentMethodOptionType = SubscriptionModel.paymentOptionType;


                        if (targetId === "recurring-update-payment-method") {

                            if (updateRecurringPaymentMethod.is(":checked")) {

                                if (SubscriptionModel.recurringPaymentMethodOptionType === '') {
                                    SubscriptionModel.recurringPaymentMethodOptionType = "new-cc";
                                }

                                if (SubscriptionModel.recurringPaymentMethodOptionType === "new-cc") {
                                    tabTypeCreditCard.trigger("click");

                                }
                                if (SubscriptionModel.recurringPaymentMethodOptionType === "new-ach") {
                                    tabTypeAch.trigger("click");
                                }

                                let isCreditCardsAllowed = parseInt(SubscriptionModel.isCreditCardsEnabled.val());
                                let isAchAllowed = parseInt(SubscriptionModel.isBankAccountsEnabled.val());


                                if (isCreditCardsAllowed === 1 && isAchAllowed === 0) {
                                    tabTypeCreditCard.trigger("click");
                                    SubscriptionModel.paymentOptionType.val("new-cc");
                                    SubscriptionModel.ebizOptionType.val("CC");
                                    SubscriptionModel.ebizOption.val("CC");

                                }
                                if (isCreditCardsAllowed === 0 && isAchAllowed === 1) {
                                    tabTypeAch.trigger("click");
                                    SubscriptionModel.paymentOptionType.val("new-ach");
                                    SubscriptionModel.ebizOptionType.val("ACH");
                                    SubscriptionModel.ebizOption.val("ACH");

                                }
                                if (isCreditCardsAllowed === 1 && isAchAllowed === 1) {
                                    tabTypeCreditCard.trigger("click");
                                    SubscriptionModel.paymentOptionType.val("new-cc");
                                    SubscriptionModel.ebizOptionType.val("CC");
                                    SubscriptionModel.ebizOption.val("CC");
                                }


                            } else {
                                SubscriptionModel.recurringPaymentMethodOptionType = SubscriptionModel.paymentOptionType.val();
                                SubscriptionModel.paymentOptionType.val("saved");
                                SubscriptionModel.ebizOptionType.val("saved");
                                SubscriptionModel.ebizOption.val("saved");
                                SubscriptionModel.isDefault.val(0);

                            }
                        }

                        if (targetId === "is_default_cc") {

                            if (SubscriptionModel.isDefaultCc.is(":checked")) {
                                SubscriptionModel.isDefault.val(1);
                                SubscriptionModel.isDefaultAch.prop("checked", false);
                            } else {
                                SubscriptionModel.isDefault.val(0);

                            }

                        }

                        if (targetId === "is_default_ach") {
                            if (SubscriptionModel.isDefaultAch.is(":checked")) {
                                SubscriptionModel.isDefault.val(1);
                                SubscriptionModel.isDefaultCc.prop("checked", false);
                            } else {
                                SubscriptionModel.isDefault.val(0);
                            }
                        }

                        if (targetId === "payment-tab-cc") {
                            SubscriptionModel.ebizOptionType.val("CC");
                            SubscriptionModel.ebizOption.val("CC");
                            SubscriptionModel.paymentOptionType.val("new-cc");
                            SubscriptionModel.recurringPaymentMethodOptionType = "new-cc";
                            SubscriptionModel.isDefault.val(0);
                            SubscriptionModel.isDefaultCc.prop("checked", false);
                            SubscriptionModel.isDefaultAch.prop("checked", false);

                        }
                        if (targetId === "payment-tab-ach") {
                            SubscriptionModel.ebizOptionType.val("ACH");
                            SubscriptionModel.ebizOption.val("ACH");
                            SubscriptionModel.paymentOptionType.val("new-ach");
                            SubscriptionModel.recurringPaymentMethodOptionType = "new-ach";
                            SubscriptionModel.isDefault.val(0);
                            SubscriptionModel.isDefaultCc.prop("checked", false);
                            SubscriptionModel.isDefaultAch.prop("checked", false);
                        }
                    });
                },

                /**
                 * Change Payment Method
                 *
                 * @param $
                 */
                renderCvvCodePanel: function ($) {

                    let selectedPaymentMethod = SubscriptionModel.paymentMethodsDropdown.val();

                    if (typeof (selectedPaymentMethod) !== "undefined") {
                        let paymentMethod = selectedPaymentMethod.split("|");
                        let paymentMethodType = paymentMethod[2];

                        if (paymentMethodType == "CC") {
                            SubscriptionModel.currentPaymentType = "CC";
                        } else {
                            SubscriptionModel.currentPaymentType = "ACH";
                        }
                    }


                },

                /**
                 * Render recurring End date
                 * @param $
                 */
                renderRecurringEndDate: function ($) {

                    let recurringIndefinitChk = SubscriptionModel.recurringIndefinitelyChk;

                    /**
                     * Payment Method Check box toggling
                     */
                    recurringIndefinitChk.bind("click keypress", function (evt) {

                        SubscriptionModel.recEndDatePanel.show();
                        if (recurringIndefinitChk.is(":checked")) {
                            SubscriptionModel.recEndDatePanel.hide();
                        }
                    });
                },

                /**
                 * Dialog Box rendering
                 *
                 * @param $
                 * @param popUpTitle
                 * @param popUpButtons
                 * @param popUpType
                 * @param isResponse
                 * @param isInnerScroll
                 * @returns {null|{buttons: {}[], responsive: boolean, innerScroll: boolean, type: string, title: *}}
                 */
                renderPopUpDialogBoxOptions: function ($, popUpTitle = "", popUpButtons = "", popUpType = "popup", isResponse = true, isInnerScroll = true) {

                    if (popUpTitle === "") {
                        popUpTitle = "Product Subscription"
                    }
                    if (popUpButtons == "") {
                        let loginUrl = url.build('customer/account/login');
                        popUpButtons = [
                            {
                                text: $.mage.__("Log In"),
                                class: "action action-buttons submit primary sub_btn",
                                click: function (evt) {
                                    window.location.href = loginUrl;
                                }
                            }, {
                                text: $.mage.__("Cancel"),
                                class: "action action-buttons submit sub_btn",
                                click: function (evt) {
                                    this.closeModal();
                                }
                            }
                        ];
                    }

                    SubscriptionModel.dialgPopUpOptions = {
                        type: popUpType,
                        responsive: isResponse,
                        innerScroll: isInnerScroll,
                        title: $.mage.__(popUpTitle),
                        buttons: popUpButtons
                    }

                    return SubscriptionModel.dialgPopUpOptions;
                },
                /**
                 * Render Dialog Box
                 *
                 * @param $
                 * @param divObj
                 */
                renderDialogBox: function ($, divObj) {
                    /**
                     * Opening Model
                     */
                    modal(SubscriptionModel.dialgPopUpOptions, divObj);
                    /**
                     * object Modeling
                     */
                    divObj.modal("openModal");
                },
                /**
                 * Close Dialog Box
                 *
                 * @param $
                 * @param divObj
                 */
                closeDialogBox: function ($, divObj) {
                    /**
                     * Close Model
                     */
                    divObj.modal("closeModal");
                },

                /**
                 * Validate Avs Cvv Zip Code
                 */
                validateAvsCvvZipCode: function () {
                    let subscriptionForm = SubscriptionModel.subscriptionUpdateForm;
                    let isAvsCvvZipEnabled = SubscriptionModel.isAvsCvvZipEnabled;

                    if (isAvsCvvZipEnabled) {
                        subscriptionForm.append($('<input>').attr({
                            type: 'hidden',
                            id: 'is_ajax',
                            name: 'is_ajax',
                            value: '1'
                        }));
                        EBizChargeAvsCvvValidator.validateAvsCvvActionUrl = SubscriptionModel.cardAvsValidationUrl;
                        EBizChargeAvsCvvValidator.popupModal = modal;
                        EBizChargeAvsCvvValidator.popupModelId = "avs-cvv-warnings-panel";
                        EBizChargeAvsCvvValidator.popupTitle = $.mage.__("Security mismatch dialog box...");
                        EBizChargeAvsCvvValidator.avsAddressMsg = "avs-address-msg";
                        EBizChargeAvsCvvValidator.avsPostalCodeMsg = "avs-postal-code-msg";
                        EBizChargeAvsCvvValidator.cvvCvv2Msg = "cvv-cvv2-msg";
                        EBizChargeAvsCvvValidator.cardMessageResponse = "card-message-response";
                        EBizChargeAvsCvvValidator.avsSaveActionButton = "avs-response-save-button";

                        /** Check if the AVS and CVV are enabled **/
                        subscriptionForm.trigger('processStop');

                        EBizChargeAvsCvvValidator.popUpButtons = [
                            {
                                text: $.mage.__('Continue subscribe anyway'),
                                class: 'action primary avs-response-save-button',
                                click: function (evt) {
                                    SubscriptionModel.subscriptionUpdateForm.submit();
                                    /**
                                     * Suspend all buttons
                                     */
                                    SubscriptionModel.disableActionButtons($, false);
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
                            subscriptionForm,
                            modal,
                            customerData,
                            globalMessageList
                        );
                    }

                    // if (avsCvvZipPromise !== undefined && !avsCvvZipPromise) {
                    //     /**
                    //      * Suspend all buttons
                    //      */
                    //     SubscriptionModel.disableActionButtons($, false);
                    // }

                    return !avsCvvZipPromise;
                },

                /**
                 * Process to add payment method via PCI Compliance
                 */
                processPciCompliance: function () {
                    let subscriptionForm = SubscriptionModel.subscriptionUpdateForm;
                    let formData = EBizChargeGatewayModel.getFormFields(subscriptionForm.serializeArray());
                    let achForm = $('#tab-ach').css('display') !== 'none';
                    let paymentMethodFormData = EBizChargeGatewayModel.prepareEbizFormData(formData, achForm);
                    EBizSoapApiClientModel.prepareInitParams($, paymentMethodFormData);

                    /**
                     * Add New Payment Method via PCI Compliance
                     */
                    EBizSoapApiClientModel.addCustomerPaymentMethod($, paymentMethodFormData).then(
                        (addCustomerPaymentMethodResp) => {
                            let paymentMethodId = addCustomerPaymentMethodResp.payment_method_id;
                            let error = addCustomerPaymentMethodResp.error;

                            if (error === false && paymentMethodId) {
                                /**
                                 * Save response in session and on success submit form
                                 */
                                EBizSoapApiClientModel.sendAjaxProcessRequest(
                                    $,
                                    SubscriptionModel.pciAddNewMethodResponseUrl,
                                    addCustomerPaymentMethodResp
                                ).then((successResponse) => {
                                    if (typeof (successResponse['error']) !== undefined && successResponse['error'] === false) {
                                        subscriptionForm.submit();
                                    }
                                }).catch((failResponse) => {
                                    $('body').trigger('processStop').loader('hide');
                                });
                            }
                        }
                    ).catch((addCustomerPaymentMethodError) => {
                        /**
                         * Show message on fail
                         */
                        $('body').trigger('processStop').loader('hide');
                        EBizChargeAvsCvvValidator.showResponseMessage(
                            addCustomerPaymentMethodError,
                            customerData,
                            globalMessageList
                        );
                        return false;
                    });
                },

                /**
                 * Render Subscription Model
                 * @param $
                 */
                renderSubmitSubscription: function ($) {

                    SubscriptionModel.cvv2CodeTxt.addClass("required");
                    SubscriptionModel.cvv2CodeLabel.addClass("label-info");

                    /**
                     * Scription Model Action Tool Bar Button
                     */
                    SubscriptionModel.actionToolbarButtons.bind("click keypress", function (evt) {
                        let targetId = evt.target.id;
                        let actionLabel = "-label";
                        let saveSubscriptionButton = "save-subscription-button";
                        let unSubscribeButton = "un-subscribe-button";
                        let suspendButton = "suspend-button";

                        let dataForm = $('#subscription-update-form');

                        dataForm.validation('isValid');

                        if (targetId == unSubscribeButton || targetId == unSubscribeButton + actionLabel) {
                            SubscriptionModel.cvv2CodeTxt.removeClass("required");
                            SubscriptionModel.recurringCvv2LabelSpan.removeClass("label-required");
                            SubscriptionModel.recurringCvv2LabelSpan.hide();

                        }
                        if (targetId == suspendButton || targetId == suspendButton + actionLabel) {
                            SubscriptionModel.cvv2CodeTxt.removeClass("required");
                            SubscriptionModel.recurringCvv2LabelSpan.removeClass("label-required");
                            SubscriptionModel.recurringCvv2LabelSpan.hide();


                        }
                        if (  targetId == saveSubscriptionButton ||
                            targetId == saveSubscriptionButton + actionLabel ||
                            targetId == "re-subscription-btn") {

                            SubscriptionModel.cvv2CodeTxt.addClass("required");
                            SubscriptionModel.recurringCvv2LabelSpan.addClass("label-required");
                            SubscriptionModel.recurringCvv2LabelSpan.show();


                        }

                        if (!$('#subscription-update-form').valid()) {

                            return true;
                        }

                        /**
                         * if Save subscription Button got hit
                         */
                        if (
                            targetId == saveSubscriptionButton ||
                            targetId == saveSubscriptionButton + actionLabel ||
                            targetId == "re-subscription-btn"
                        ) {

                            let endRecurringDate = $("#recurring-expire-date");
                            let today = new Date();
                            let endDate = new Date(endRecurringDate.val());

                            if (today > endDate) {
                                endRecurringDate.addClass("error");
                                alert($.mage.__("Expiry date is not valid."));
                                return false;
                            }


                            let saveSubscriptionTitle = $.mage.__("Update Subscription Dialog box");
                            let saveDialogBoxButtons = [
                                {
                                    text: $.mage.__("Yes, Update"),
                                    class: "action action-buttons submit primary sub_btn",
                                    click: function (evt) {
                                        SubscriptionModel.subscription_url.val("subscription_save");

                                        /**
                                         * Submit Form
                                         */
                                        if (document.getElementById(SubscriptionModel.addNewPaymentMethodCheckbox).checked && $('#tab-cc').css('display') !== 'none') {
                                            SubscriptionModel.validateAvsCvvZipCode();
                                            // if (EBizChargeAvsCvvValidator.isPciComplianceEnabled) {
                                            //     SubscriptionModel.processPciCompliance();
                                            // } else if ($('#tab-cc').css('display') !== 'none') {
                                            //     SubscriptionModel.validateAvsCvvZipCode();
                                            // }
                                        } else {
                                            /**
                                             * Suspend all buttons
                                             */
                                            SubscriptionModel.disableActionButtons($, false);

                                            /**
                                             * Submit Form
                                             */
                                            SubscriptionModel.subscriptionUpdateForm.submit();
                                        }
                                        this.closeModal();
                                    }
                                },
                                {
                                    text: $.mage.__("Cancel"),
                                    class: "action action-buttons submit sub_btn",
                                    click: function (evt) {
                                        /**
                                         * Suspend all buttons
                                         */
                                        SubscriptionModel.disableActionButtons($, true);
                                        this.closeModal();
                                    }
                                }

                            ];

                            /**
                             *
                             * @type {{buttons: {}[], responsive: boolean, innerScroll: boolean, type: string, title: *}}
                             */
                            let popupOptions = SubscriptionModel.renderPopUpDialogBoxOptions($, saveSubscriptionTitle, saveDialogBoxButtons);
                            /**
                             * Render Dialog Box
                             */
                            SubscriptionModel.renderDialogBox($, SubscriptionModel.saveSubscriptionModel);

                        }

                        /**
                         * if Save subscription Button got hit
                         */
                        if (targetId == suspendButton || targetId == suspendButton + actionLabel) {

                            SubscriptionModel.cvv2CodeTxt.removeClass("required");
                            SubscriptionModel.cvv2CodeLabel.removeClass("label-info");

                            let suspendSubscriptionTitle = $.mage.__("Suspend Subscription Dialog box");
                            let suspendDialogBoxButtons = [
                                {
                                    text: $.mage.__("Yes, Suspend"),
                                    class: "action action-buttons submit primary sub_btn",
                                    click: function (evt) {
                                        SubscriptionModel.subscription_url.val("subscription_suspend");
                                        /**
                                         * Suspend all buttons
                                         */
                                        SubscriptionModel.disableActionButtons($, false);
                                        /**
                                         * Submit Form
                                         */
                                        SubscriptionModel.subscriptionUpdateForm.submit();
                                        this.closeModal();
                                    }
                                },
                                {
                                    text: $.mage.__("Cancel"),
                                    class: "action action-buttons submit sub_btn",
                                    click: function (evt) {
                                        /**
                                         * Suspend all buttons
                                         */
                                        SubscriptionModel.disableActionButtons($, true);
                                        this.closeModal();
                                    }
                                }

                            ];

                            /**
                             *
                             * @type {{buttons: {}[], responsive: boolean, innerScroll: boolean, type: string, title: *}}
                             */
                            let popupOptions = SubscriptionModel.renderPopUpDialogBoxOptions($, suspendSubscriptionTitle, suspendDialogBoxButtons);
                            /**
                             * Render Dialog Box
                             */
                            SubscriptionModel.renderDialogBox($, SubscriptionModel.suspendSubscriptionModel);

                        }


                        /**
                         * If unsubscription button got hit
                         */
                        if (targetId == unSubscribeButton || targetId == unSubscribeButton + actionLabel) {

                            let unSubscribeTitle = $.mage.__("Unsubscribe Subscription Dialog box");


                            let unSubDialogBoxButtons = [
                                {
                                    text: $.mage.__("Yes, Unsubscribe"),
                                    class: "action action-buttons submit primary sub_btn",
                                    click: function (evt) {
                                        SubscriptionModel.subscription_url.val("subscription_unsubscribe");

                                        /**
                                         * Suspend all buttons
                                         */
                                        SubscriptionModel.disableActionButtons($, false);

                                        /**
                                         * Submit Form
                                         */
                                        SubscriptionModel.subscriptionUpdateForm.submit();
                                        this.closeModal();
                                    }
                                },
                                {
                                    text: $.mage.__("Cancel"),
                                    class: "action action-buttons submit sub_btn",
                                    click: function (evt) {
                                        /**
                                         * Suspend all buttons
                                         */
                                        SubscriptionModel.disableActionButtons($, true);
                                        this.closeModal();
                                    }
                                }

                            ];

                            /**
                             *
                             * @type {{buttons: {}[], responsive: boolean, innerScroll: boolean, type: string, title: *}}
                             */
                            let popupOptions = SubscriptionModel.renderPopUpDialogBoxOptions($, unSubscribeTitle, unSubDialogBoxButtons);
                            /**
                             * Render Dialog Box
                             */
                            SubscriptionModel.renderDialogBox($, SubscriptionModel.confirmSubscriptionModel);
                        }

                    });

                },
                /**
                 * DisabledActionButtons
                 * @param $
                 * @param isEnabledFlag
                 */
                disableActionButtons: function ($, isEnabledFlag = false) {
                    let actionButtons = [
                        SubscriptionModel.saveSubscriptionButton,
                        SubscriptionModel.unSubscribeButton,
                        SubscriptionModel.suspendButton
                    ];
                    /** loop through to enable / disable **/
                    $.each(actionButtons, function (key, obj) {
                        obj.prop("disabled", true);

                        if (isEnabledFlag == true) {
                            obj.prop("disabled", false);
                        }

                    });

                },

                /**
                 * Redirect To Page URL
                 *
                 * @param buttonId
                 * @param buttonUrl
                 */
                redirectToPageUrl: function (buttonId, buttonUrl) {
                    $(document.getElementById(buttonId)).bind("click keypress", function (evt) {
                        window.location.href = buttonUrl;
                        SubscriptionModel.unRegisteredPopupCustomer.modal("closeModal");

                        return false;
                    });
                },
                /**
                 * Add Subscribed Product To Cart
                 * @param $
                 * @param isDefault
                 */
                addSubscribedProductToCart: function ($, isDefault) {
                    /**
                     * Binding Event with Subscription
                     */
                    SubscriptionModel.subscriptionControlOptions.bind("click keypress", function (evt) {
                        /**
                         * is Subscription Selected
                         * @type {number}
                         */
                        let isSubscriptionSelected = parseInt(this.value);

                        // console.log(isSubscriptionSelected);

                        /**
                         * is Subscription Selected
                         */
                        if (parseInt(isSubscriptionSelected) === 1) {
                            if (parseInt(SubscriptionModel.customerId) === 0) {
                                /**
                                 * Render UnregisterCustomer Error Dialog Box
                                 */
                                SubscriptionModel.renderUnregisterCustomerErrorDialogBox($);
                                return false;
                            }
                            /**
                             * Subscription Model reset Options
                             */
                            SubscriptionModel.prepareProductSubscriptions($, isSubscriptionSelected);
                            /**
                             * Render Subscriptions
                             * Add Subscribed Product
                             */
                            SubscriptionModel.renderAddProductSubscriptions($);


                        } else {
                            /**
                             * Render UnSubscribe
                             * Products
                             */
                            /**
                             * Subscription Model reset Options
                             */
                            SubscriptionModel.prepareProductSubscriptions($, isSubscriptionSelected);


                        }
                    });


                    /**
                     * Product Add to Cart Button
                     * Hit and Add to Product Cart
                     */
                    SubscriptionModel.addItemsToCartWithSubscriptions($, SubscriptionModel.productAddToCartButton, SubscriptionModel.productUpdateCartButton, "add-to-cart");


                },
                /**
                 * Render Dialog Box
                 * @param $
                 */
                renderUnregisterCustomerErrorDialogBox($) {

                    /**
                     *
                     * @type {{buttons: {}[], responsive: boolean, innerScroll: boolean, type: string, title: *}}
                     */
                    let popupOptions = SubscriptionModel.renderPopUpDialogBoxOptions($);
                    /**
                     * Render Dialog Box
                     */
                    SubscriptionModel.renderDialogBox($, SubscriptionModel.unRegisteredPopupCustomer);
                },

                /**
                 * Render Confirm Subscription Dialog box
                 * @param $
                 */
                renderConfirmSubscriptionDialogBox: function ($) {
                    SubscriptionModel.renderDialogBox($, SubscriptionModel.errorPopupModelAlreadyExists);

                },

                /**
                 * already exists Dialog Box
                 *
                 * @param $
                 */
                renderAlreadyExistsErrorsDialogBox: function ($) {
                    SubscriptionModel.renderDialogBox($, SubscriptionModel.errorPopupModelAlreadyExists);
                },

                /**
                 * already Product Added To Cart
                 *
                 * @param $
                 */
                renderAlreadyAddedErrorsDialogBox: function ($) {
                    SubscriptionModel.renderDialogBox($, SubscriptionModel.errorPopupModelAlreadyAdded);
                },

                /**
                 * Render DateTimeDialog
                 *
                 * @param $
                 * @param parentDivId
                 * @param startDateInputId
                 * @param endDateInputId
                 * @param frequencyDays
                 */
                renderDateTimeDialog: function ($, parentDivId, startDateInputId, endDateInputId, frequencyDays=0) {

                    /**
                     * Defina MinDate
                     *
                     * @type {Date}
                     */
                    let minDate = new Date();
                    minDate.setDate(minDate.getDate() + 1);
                   // minDate.setDate(minDate.getDate());

                    $(document.getElementById(parentDivId)).dateRange({
                        buttonText: '',
                        dateFormat: 'Y-mm-dd',
                        minDate: minDate,
                        from: {
                            id: startDateInputId
                        },
                        to: {
                            id: endDateInputId
                        }
                    });
                    $(document.getElementById(startDateInputId)).attr("autocomplete", "off");
                    $(document.getElementById(endDateInputId)).attr("autocomplete", "off");
                },
                /**
                 * Render Add Product
                 * Subscriptions
                 * @param $
                 */
                renderAddProductSubscriptions: function ($) {

                    /**
                     * Subscription Form
                     */
                    SubscriptionModel.productSubscriptionForm.show();
                    /**
                     * Subscription Model Render Indefintely
                     */
                    SubscriptionModel.renderRecurringIndefinetly($);


                },


                /**
                 * Recurring Indefintely
                 *
                 * @param $
                 */
                renderRecurringIndefinetly: function ($) {
                    /**
                     * Add Recurring Indefinitely
                     */
                    SubscriptionModel.recurringIndefinitely.bind("click keypress", function (evt) {
                        if ($(this).is(":checked")) {
                            SubscriptionModel.recurringEndDate.val("").hide().prop("disabled", true);
                            SubscriptionModel.recurringEndDate.parent().parent().hide();
                        } else {
                            SubscriptionModel.recurringEndDate.show().prop("disabled", false);
                            SubscriptionModel.recurringEndDate.parent().parent().show();
                        }

                    });
                },
                /**
                 * Add items to Cart With Subscription
                 * @param $
                 * @param cartHandlerButtonId
                 */
                addItemsToCartWithSubscriptions: function ($, cartHandlerButtonId, updateHandlerButtonId, buttonType = "add-to-cart") {
                    /**
                     * required Assets
                     *
                     * @type {{endDate: (jQuery|HTMLElement), startDate: (jQuery|HTMLElement), frequency: (jQuery|HTMLElement)}}
                     */
                    let requiredKeys = {
                        frequency: SubscriptionModel.recurringFrequency,
                        startDate: SubscriptionModel.recurringStartDate,
                        endDate: SubscriptionModel.recurringEndDate,
                        indefinitRecurring: SubscriptionModel.recurringIndefinitely
                    }
                    /**
                     * Hiding Error
                     */
                    $.each(requiredKeys, function (key, obj) {
                        $(obj).bind("click keypress change", function (evt) {
                            SubscriptionModel.mageError.hide();
                        });
                    });


                    /**
                     * cartButtonHit
                     */
                    let cartButtonHit = $(cartHandlerButtonId, updateHandlerButtonId).bind("click keypress", function (evt) {

                        evt.preventDefault();

                        /**
                         * Is Indefinit Frequency Set
                         */
                        let isIndefinite = requiredKeys.indefinitRecurring.is(":checked");

                        /**
                         * Is Valid Subscriptino Item
                         * @type {boolean}
                         */
                        let isValidSubscriptionItem = SubscriptionModel.validateSubscribedProduct($, requiredKeys, isIndefinite);

                        let cartActionUrl = SubscriptionModel.productActionUrl;

                        /**
                         * Button Type
                         */
                        if (buttonType === "update-to-cart") {
                            cartActionUrl = SubscriptionModel.productUpdateActionUrl;
                        }

                        /**
                         * Add to Cart Obj
                         * @type {{payLoad: {}, ajaxMethod: string, dataType: string, actionUrl: *, IsShowLoader: boolean, beforeAjaxLoader: string}}
                         */
                        let addToCartObj = {
                            actionUrl: cartActionUrl,
                            payLoad: {
                                productid: SubscriptionModel.currentProductId,
                                productparentid: SubscriptionModel.currentProductId,
                                customerId: SubscriptionModel.customerId,
                                newSdate: requiredKeys.startDate.val()
                            },
                            dataType: "JSON",
                            IsShowLoader: false,
                            ajaxMethod: "POST"
                        }

                        if (!SubscriptionModel.isSubscriptionEnabled) {
                            //   return false;
                        }

                        SubscriptionModel.productAddToCartForm.submit();

                    });

                },

                /**
                 * Render Show Loader
                 * @param $
                 */
                renderShowLoader: function ($) {
                    //  SubscriptionModel.productAddToCartButton.html("<span>adding...</span>");
                    //  $("#product-addtocart-button").prop("disabled", true);


                },

                /**
                 * render AjaxHttpRequest
                 *
                 * @param $
                 * @param actionUrl
                 * @param payLoad
                 * @param dataType
                 * @param IsShowLoader
                 * @param ajaxMethod
                 * @returns {Promise<unknown>}
                 */
                renderAjaxHttpRequest: function ($, actionUrl = "", payLoad = {}, dataType = "JSON", IsShowLoader = false, ajaxMethod = "POST") {

                    /**
                     * productAddedToCartAjaxResp
                     */
                    return productAddedToCartAjaxResp = new Promise((successAjaxObj, errorAjaxObj) => {
                        /**
                         * Rendering Show Loader
                         */

                        $.ajax(
                            {
                                method: ajaxMethod,
                                url: actionUrl,
                                data: payLoad,
                                async: false,
                                dataType: dataType,
                                //showLoader: IsShowLoader,
                                //  showLoader: true,

                                beforeSend: function (httpRequest) {
                                    SubscriptionModel.renderShowLoader($);
                                },
                                error: function (ajaxError) {
                                    errorAjaxObj(ajaxError)
                                }

                            }).done(function (ajaxRespData) {

                            successAjaxObj(ajaxRespData);


                        }); /**end of Ajax Call **/
                    });

                },
                /**
                 *  Validate Subscribed Product
                 *
                 * @param $
                 * @param requiredKeys
                 * @param isIndefinite
                 * @returns {boolean}
                 */
                validateSubscribedProduct: function ($, requiredKeys, isIndefinite) {

                    let isValid = true;

                    let frequency = requiredKeys.frequency.val();
                    let startDate = requiredKeys.startDate.val();
                    let endDate = requiredKeys.endDate.val();

                    /**
                     * Check if Subscription is valid
                     */
                    if (SubscriptionModel.isSubscriptionEnabled === false) {
                        return true;
                    }
                    /**
                     * required Keys
                     */
                    if (!isIndefinite && frequency == "" && startDate == "" && endDate == "") {
                        SubscriptionModel.invalidFrequencyError.html($.mage.__("Please select valid frequency type.")).show();
                        SubscriptionModel.invalidDatesError.html($.mage.__("Please select valid start & end dates.")).show();
                        return false;
                    }

                    /**
                     * required Keys
                     */
                    if (frequency == "") {
                        SubscriptionModel.invalidFrequencyError.html($.mage.__("Please select valid frequency type.")).show();
                        return false;
                    }
                    /**
                     * required Keys
                     */
                    if (startDate == "") {
                        SubscriptionModel.invalidDatesError.html($.mage.__("Please select valid start date.")).show();
                        return false;
                    }

                    /**
                     * required Keys
                     */
                    if (endDate == "" && !isIndefinite) {
                        SubscriptionModel.invalidDatesError.html($.mage.__("Please select valid End date.")).show();
                        return false;
                    }

                    /**
                     *
                     * @type {{difference: number, differenceInDays: number}}
                     */
                    let dateDifference = SubscriptionModel.getDateDifference(startDate, endDate);
                    let frequencyDays = Math.round(SubscriptionModel.convertDaysFromRecurringFrequency($, frequency));

                    /**
                     * if frequency Days are
                     *  less than the selected days
                     */
                    if (!isIndefinite && dateDifference.differenceInDays < frequencyDays) {
                        SubscriptionModel.invalidDatesError.html($.mage.__("Please select valid dates, selection and frequency does not match.")).show();
                        return false;
                    }


                    return isValid;
                },

                /**
                 * Get Date Difference
                 *
                 * @param startDate
                 * @param endDate
                 * @returns {{difference: number, differenceInDays: number}}
                 */
                getDateDifference: function (startDate, endDate) {

                    let dateDiffResp = {
                        difference: 0,
                        differenceInDays: 0
                    }
                    let startDateTime = new Date(startDate);
                    let endDateTime = new Date(endDate);

                    let diffInTime = Math.round(endDateTime - startDateTime);
                    let diffInDays = Math.round(diffInTime / (1000 * 60 * 60 * 24));
                    dateDiffResp.difference = diffInTime;
                    dateDiffResp.differenceInDays = diffInDays;

                    return dateDiffResp

                },
                /**
                 * Reset Product Subscription
                 * Form
                 *
                 * @param $
                 */
                prepareProductSubscriptions($, isOptionEnabled = 0) {

                    if (isOptionEnabled == 0) {

                        SubscriptionModel.isSubscriptionEnabled = false;
                        /**
                         *
                         * Subscription Recurring Reset
                         */
                        SubscriptionModel.recurringFrequency.val("");
                        SubscriptionModel.recurringStartDate.val("");
                        SubscriptionModel.recurringEndDate.val("");

                        /**
                         * Recurring Indefintely
                         */
                        if (SubscriptionModel.recurringIndefinitely.is(":checked")) {
                            SubscriptionModel.recurringIndefinitely.trigger("click");
                        }
                        SubscriptionModel.recurringIndefinitely.prop("checked", false);
                        /**
                         * reset Product Subscription Form
                         */
                        SubscriptionModel.productSubscriptionForm.find("input").prop("disabled", true);
                        SubscriptionModel.productSubscriptionForm.find("select").prop("disabled", true);

                        SubscriptionModel.productSubscriptionForm.hide();

                    } else {
                        SubscriptionModel.isSubscriptionEnabled = true;
                        /**
                         * reset Product Subscription Form
                         */

                        SubscriptionModel.productSubscriptionForm.find("input").prop("disabled", false);
                        SubscriptionModel.productSubscriptionForm.find("select").prop("disabled", false);


                        SubscriptionModel.productSubscriptionForm.show();
                    }
                },

                /**
                 * Convert Days from
                 * Recurring Frequency
                 *
                 * @param $
                 * @param frequencyVal
                 * @returns {number}
                 */
                convertDaysFromRecurringFrequency: function ($, frequencyVal) {

                    /**
                     *
                     * @type {number}
                     */
                    let responseDays = 0;

                    /**
                     * Frequency Val
                     */
                    switch (frequencyVal) {
                        case "daily":
                            responseDays = 1;
                            break;
                        case "weekly":
                            responseDays = 7;
                            break;
                        case "bi-weekly":
                        case "bi-monthly":
                            responseDays = 14;
                            break;
                        case "four-week":
                            responseDays = 28;
                            break;
                        case "monthly":
                            responseDays = 30;
                            break;
                        case "two-month":
                            responseDays = 60;
                            break;
                        case "quarterly":
                        case "three-month":
                        case "90-days":
                            responseDays = 90;
                            break;
                        case "four-month":
                            responseDays = 120;
                            break;
                        case "five-month":
                            responseDays = 150;
                            break;
                        case "bi-annually":
                        case "six-month":
                        case "180-days":
                            responseDays = 180;
                            break;
                        case "annually":
                            responseDays = 365;
                            break;
                        default:
                            responseDays = 30;
                    }

                    return responseDays;
                }


            }/** end of Subscription model**/


            /**
             * Initializing the Model
             */
            SubscriptionModel._init($);

        }

    });
