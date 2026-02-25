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
    'jquery',
    'underscore',
    'Magento_Ui/js/modal/modalToggle',
    'mage/translate',
    'domReady',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/model/messageList',
    'Ebizcharge_Ebizcharge/js/model/ebizcharge-gateway-model',
    'Ebizcharge_Ebizcharge/js/checkout/gateway/ebizcharge-gateway'
], function ($, _, modalToggle, trans, domReady, modal, customerData, globalMessageList, EbizchargeGatewayModel, EbizchargeGateway) {
    'use strict';

    return function (configModelData) {
        /**
         * Validating of AVS CVV Code
         */
        return EBizChargeAvsCvvValidator._init($, _, modalToggle, trans, domReady, modal, configModelData, customerData, globalMessageList, EbizchargeGatewayModel, EbizchargeGateway);
    }
});

/**
 *
 * @type {{_init: EBizChargeAvsCvvValidator._init, cardAvsValidationUrl: null, creditCardForm: null, preparePopupOptions: (function(*, *=, *=, *=): {buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: *})}}
 */
let EBizChargeAvsCvvValidator = {
    /**
     * initializing hte variables
     */
    cardAvsValidationUrl: null,
    creditCardForm: null,
    popupModelId: null,
    avsAddressMsg: null,
    avsPostalCodeMsg: '',
    cvvCvv2Msg: null,
    popUpModel: null,
    popUpButtons: [],
    forceSubmit: null,
    isCardValid: false,
    cardMessageResponse: null,
    avsSaveActionButton: null,
    isCheckout: null,
    checkoutOrderObj: null,
    isPciComplianceEnabled: false,
    pciAddNewMethodResponseUrl: null,
    configData: null,

    /**
     *
     * @param $
     * @param _
     * @param modalToggle
     * @param trans
     * @param domReady
     * @param modal
     * @param configModelData
     * @param customerData
     * @param globalMessageList
     * @param EbizchargeGatewayModel
     * @param EbizchargeGateway
     * @private
     */
    _init: function ($, _, modalToggle, trans, domReady, modal, configModelData, customerData, globalMessageList, EbizchargeGatewayModel, EbizchargeGateway) {
        //console.log(configModelData);

        this.configData = configModelData;
        /**
         * Setting the values
         */
        this.cardAvsValidationUrl = configModelData.cardAvsValidationUrl;
        this.creditCardForm = $(document.getElementById(configModelData.creditCardForm));
        this.popupModelId = $(document.getElementById(configModelData.avsCvvWarningsPanel)).attr("id");
        this.avsAddressMsg = configModelData.avsAddressMsg;
        this.avsPostalCodeMsg = configModelData.avsPostalCodeMsg;
        this.cvvCvv2Msg = configModelData.cvvCvv2Msg;
        this.forceSubmit = false;
        this.cardMessageResponse = configModelData.cardMessageResponse;
        this.avsSaveActionButton = configModelData.avsSaveActionButton;
        this.isCheckout = false;
        this.checkoutOrderObj = null;
        this.popupTitle = "Security mismatch";
        this.isPciComplianceEnabled = configModelData.isPciComplianceEnabled;
        this.pciAddNewMethodResponseUrl = configModelData.pciAddNewMethodResponseUrl;

        this.popUpButtons = [
            {
                text: $.mage.__("Save card anyway"),
                class: 'action primary scalable save avs-response-save-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                id: 'avs-response-save-button',
                click: function (evt) {
                    EBizChargeAvsCvvValidator.forceSubmit = true;
                    EBizChargeAvsCvvValidator.creditCardForm.submit();
                    this.closeModal();
                }
            }, {
                text: $.mage.__("Cancel adding new card"),
                class: 'action primary scalable save avs-response-cancel-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                id: 'avs-response-cancel-button',
                click: function (evt) {
                    this.closeModal();
                }
            }
        ];
        this.popupModal = modal;

        //console.log([this.avsAddressMsg, this.avsPostalCodeMsg]);

        /**
         * Submit Form
         */
       // EBizChargeAvsCvvValidator.submitFormWithAvsCvv($, customerData, globalMessageList);

        /**
         * Validate input
         */
        EBizChargeAvsCvvValidator.validateInput($, "input");
    },

    /**
     * Validate Input
     *
     * @param $
     * @param inputObj
     */
    validateInput: function ($, inputObj = null) {

        /**
         * Key Press
         */
        $(inputObj).bind('keypress', function (evt) {

            var inputTarget = evt.target;

            if (inputTarget.id === 'ach_route') {
                if (inputTarget.value.length > 8) {
                    return false;
                }
            }
            if (inputTarget.id === 'ach_number') {
                if (inputTarget.value.length > 13) {
                    return false;
                }
            }
        });
    },

    /**
     * Process Validate Cvv AVS Card Action
     *
     * @param $
     * @param customerData
     * @returns {Promise<unknown>}
     */
    processValidateCvvAvsCardAction: function ($, ajaxCardUrl = null, creditCardForm = null) {

        /**
         * if in case of not ajax URL or Card Form
         */
        if (!ajaxCardUrl || !creditCardForm) {
            alert($.mage.__("Error occurred, not a valid credit card form."));
            return false;
        }
        /**
         * prepare Ajax Request
         */

        let cvvAvsValidatorPromise = new Promise((processSuccess, processFail) => {

            /**
             * Add Card Form Data Array
             *
             * @type {jQuery}
             */
            let addCardFormDataArray = $(creditCardForm).serializeArray();


            /**
             * Cvv Avs Validator Request
             * @type {jQuery}
             */
            let cvvAvsValidatorRequest = $.ajax(
                {
                    url: ajaxCardUrl,
                    type: "POST",
                    data: addCardFormDataArray,
                    dataType: "html",
                    cache: false,
                    /**
                     * Before Send
                     *
                     * @param req
                     */
                    beforeSend: function (req) {
                        creditCardForm.valid();
                        creditCardForm.trigger('processStart');
                    },
                    /**
                     * Success when respose is ready
                     * @param respData
                     */
                    success: function (respData) {
                        creditCardForm.trigger('processStop');
                        $('body').loader('hide');
                        //  console.log((respData));
                        processSuccess(respData);

                    },
                    /**
                     * Done
                     * @param respData
                     */
                    done: function (respData) {
                        creditCardForm.trigger('processStop');
                        $('body').loader('hide');
                        // processSuccess(respData);
                    },
                    /**
                     * When Complete the Whole Process
                     * @param respData
                     */
                    complete: function (respData) {
                        creditCardForm.trigger('processStop');
                        $('body').loader('hide');

                        //  processSuccess(respData);
                    },
                    /**
                     * Fail When Error
                     *
                     * @param error
                     */
                    fail: function (error) {
                        // console.log(error);
                        creditCardForm.trigger('processStop');
                        $('body').loader('hide');
                        processFail(error);
                    },
                    /**
                     * Error
                     * @param error
                     */
                    error: function (error) {
                        //  console.log(error);
                        creditCardForm.trigger('processStop');
                        $('body').loader('hide');
                        processFail(error);
                    }
                }
            );

        }); /** end of Ajax Promise **/

        return cvvAvsValidatorPromise;

    },
    /**
     * Credit Card  Form
     *
     * @param $
     * @param customerData
     * @param creditCardForm
     * @param customerData
     * @param globalMessageList
     */
    processValidAddToCardAction: function ($, ajaxAddCardUrl = null, creditCardForm = null, modal = null, customerData, globalMessageList) {

        let isError = false;

        /**
         *
         * @type {Promise<unknown>}
         */
        let validateCvvAvsCardPromiseResp = EBizChargeAvsCvvValidator.processValidateCvvAvsCardAction($, ajaxAddCardUrl, creditCardForm);

        /**
         * ValidateCvvAvsCardResp
         */
        validateCvvAvsCardPromiseResp.then((validCardCvvAvsResp) => {

            //  console.log(validCardCvvAvsResp);
            // let popup = EBizChargeAvsCvvValidator.renderAvsCvvWarningsPopup($, EBizChargeAvsCvvValidator.cvvAvsAddToCardModel);
            let respJSON = JSON.parse(validCardCvvAvsResp);
            let respData = respJSON.resp_data;
            let avsResponse = respJSON.resp_data.response;

            if (respData.error === true) {

                isError = true;
                // $(document.getElementById(EBizChargeAvsCvvValidator.avsCvvWarningsPanel)).show();
                /**
                 * assign Data
                 */
                //EBizChargeAvsCvvValidator.assignAvsCvvMessages($, avsResponse, isError);
                /**
                 * showing POPUP
                 */
                //EBizChargeAvsCvvValidator.renderAvsCvvWarningsPopup($, modal);

                EBizChargeAvsCvvValidator.showResponseMessage(respData, customerData, globalMessageList);
            }

            //console.log(EBizChargeAvsCvvValidator.isCheckout);
            /**
             * if every thing is OK and Credit Card is Correct
             */
            if (respData.error === false) {
                isError = false;
                // EBizChargeAvsCvvValidator.adminCardFormObj.valid() && $("body").trigger("processStart");

                /**
                 * For checkout
                 *
                 * @type {boolean}
                 */
                EBizChargeAvsCvvValidator.forceSubmit = true;
                if (EBizChargeAvsCvvValidator.isCheckout === true) {
                    EBizChargeAvsCvvValidator.checkoutOrderObj.placeOrder();
                    return isError;
                }

                /**
                 * Admin Card Form Object
                 */
                $(creditCardForm).submit();
            }

            return isError;
        });

        validateCvvAvsCardPromiseResp.catch((notValidCvvAvsResp) => {
            //  let popup = EBizChargeAvsCvvValidator.renderAvsCvvWarningsPopup($, EBizChargeAvsCvvValidator.cvvAvsAddToCardModel);

            let respData = JSON.parse(notValidCvvAvsResp);
            let isError = true;

            //$(document.getElementById(EBizChargeAvsCvvValidator.avsCvvWarningsPanel)).show();
            EBizChargeAvsCvvValidator.showResponseMessage(respData, customerData, globalMessageList);

            /**
             * assign Data
             */
            // EBizChargeAvsCvvValidator.assignAvsCvvMessages($,  respData, isError);
        });

        return isError;
    },

    /**
     * Show AVS/CVV validation response message on page
     *
     * @param respData
     * @param customerData
     * @param globalMessageList
     * @returns {boolean}
     */
    showResponseMessage: function (respData, customerData, globalMessageList) {
        let currentUrl = window.location.href;

        if (typeof (respData.message) === undefined) {
            return false;
        }

        let isError = respData.error;
        if (currentUrl.includes('multishipping')) {
            if (isError) {
                globalMessageList.addErrorMessage({
                    message: respData.message
                });
            } else {
                globalMessageList.addSuccessMessage({
                    message: respData.message
                });
            }
        } else {
            let customerMessages = customerData.get('messages')() || {},
                messages = []; //customerMessages.messages || [];

            messages.push({
                text: respData.message,
                type: isError ? 'error' : 'success'
            });

            customerMessages.messages = messages;
            setTimeout(function () {
                customerData.set('messages', customerMessages);
            }.bind(this), 100);
        }
    },

    /**
     * render Avs CVV Warnings Popup
     * @param $
     */
    renderAvsCvvWarningsPopup: function ($, modal = null) {


        let popupTitle = EBizChargeAvsCvvValidator.popupTitle;
        let popupButtons = EBizChargeAvsCvvValidator.popUpButtons;
        /**
         * render popup
         * @type {{buttons: *[], responsive: boolean, innerScroll: boolean, type: string, title: string}}
         */
        let popupOptions = EBizChargeAvsCvvValidator.prepareAvsCvvWarningsPopupOptions($, popupTitle, popupButtons);

        /**
         * Popup
         */
        let popup = EBizChargeAvsCvvValidator.popupModal(popupOptions, $(document.getElementById(EBizChargeAvsCvvValidator.popupModelId)));

        $(document.getElementById(EBizChargeAvsCvvValidator.popupModelId)).modal('openModal');
    },
    /**
     *  Popup Options
     *
     * @param $
     * @param popupTitle
     * @param popupButtons
     * @returns {{buttons: *[], responsive: boolean, innerScroll: boolean, type: string, title: string}}
     */
    prepareAvsCvvWarningsPopupOptions: function ($, popupTitle = "CVV Warnings", popupButtons = []) {

        let popupOptions = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: popupTitle,
            buttons: popupButtons
        };

        return popupOptions;
    },

    /**
     * Response Assign to Messages
     *
     * @param $
     * @param respData
     * @param isError
     */
    assignAvsCvvMessages: function ($, respData, isError = false) {

        let avsResp = setTimeout(function (evt) {
            let avsAddressMsg = $(document.getElementById(EBizChargeAvsCvvValidator.avsAddressMsg));
            let avsZipCodeMsg = $(document.getElementById(EBizChargeAvsCvvValidator.avsPostalCodeMsg));
            let cvvCvv2Msg = $(document.getElementById(EBizChargeAvsCvvValidator.cvvCvv2Msg));
            let cardMessageResponse = $(document.getElementById(EBizChargeAvsCvvValidator.cardMessageResponse));
            let saveActionButton = $(document.getElementsByClassName(EBizChargeAvsCvvValidator.avsSaveActionButton));

            saveActionButton.removeAttr("disabled");
            if (respData.valid === false) {
                cardMessageResponse.show();
                saveActionButton.attr("disabled", "disabled");
            } else {
                cardMessageResponse.hide();
            }

            if (respData.response !== undefined) {
                let avsCvvWarningResp = respData.response;

                let avsAddressErrorMsg = avsCvvWarningResp.avs[0];
                let avsZipCodeErrorMsg = avsCvvWarningResp.avs[1];
                let cvvCvv2ErrorMsg = avsCvvWarningResp.cvv.msg;
                let cardMessage = respData.message;

                avsAddressMsg.html(avsAddressErrorMsg);
                avsZipCodeMsg.html(avsZipCodeErrorMsg);
                cvvCvv2Msg.html(cvvCvv2ErrorMsg);
                cardMessageResponse.html(cardMessage);
            }
        }, 50);

    },

    /**
     * Get default billing address from soap config data
     *
     * @returns {*|{}}
     */
    getDefaultBillingAddress: function () {
        return typeof EBizSoapApiClientModel.soapGateway.customer.billing_address !== undefined ? EBizSoapApiClientModel.soapGateway.customer.billing_address : {};
    },

    /**
     * Get default shipping address from soap config data
     *
     * @returns {*|{}}
     */
    getDefaultShippingAddress: function () {
        return typeof EBizSoapApiClientModel.soapGateway.customer.shipping_address !== undefined ? EBizSoapApiClientModel.soapGateway.customer.shipping_address : {};
    },

    /**
     * submit form with Avs Cvv
     * @param $
     * @param customerData
     * @param globalMessageList
     */
    submitFormWithAvsCvv: function ($, customerData = [], globalMessageList = {}) {

        EBizChargeAvsCvvValidator.creditCardForm.bind("submit", function (evt) {

            if (!EBizChargeAvsCvvValidator.creditCardForm.valid()) {
                evt.preventDefault();
                return false;
            }
            let pageAction = "add";
            let formParams = $(creditCardForm).serializeArray();

            $.each(formParams, function (key, val) {
                if (formParams[key].name === 'action') {
                    pageAction = formParams[key].value;
                }
            });

            if (pageAction === 'edit' || pageAction === 'add') {
                $("body").trigger("processStart");

                return ;
            }

            let isPciEnabled = EBizChargeAvsCvvValidator.isPciComplianceEnabled;
            let form = $(this);
            let formData = form.serializeArray();
            let formFields = EBizChargeGatewayModel.getFormFields(formData);

            if (EBizChargeAvsCvvValidator.forceSubmit) {
                return true;
            }

            if (!isPciEnabled) {
                if (typeof (formFields.form_for) !== undefined && formFields.form_for !== 'ach') {
                    evt.preventDefault();
                    /**
                     * Validate Card AVS CVV
                     */
                    EBizChargeAvsCvvValidator.processValidAddToCardAction($, EBizChargeAvsCvvValidator.cardAvsValidationUrl, EBizChargeAvsCvvValidator.creditCardForm, EBizChargeAvsCvvValidator.popUpModel, customerData, globalMessageList);
                }
            } else {
                evt.preventDefault();
                let ach = formFields.form_for === 'ach';
                let paymentMethodFormData = EBizChargeGatewayModel.prepareEbizFormData(formFields, ach);
                /**
                 * Prepare Form Data
                 */
                EBizSoapApiClientModel.prepareInitParams($, paymentMethodFormData);

                /**
                 * Add customer's new payment method
                 */
                EBizSoapApiClientModel.addCustomerPaymentMethod($, paymentMethodFormData).then(
                    (addCustomerPaymentMethodResp) => {
                        // EBizChargeAvsCvvValidator.showResponseMessage(
                        //     addCustomerPaymentMethodResp,
                        //     customerData,
                        //     globalMessageList
                        // );

                        //console.log(addCustomerPaymentMethodResp);
                        // EBizChargeAvsCvvValidator.renderLoader($,false);
                        // return false;

                        let paymentMethodId = addCustomerPaymentMethodResp.payment_method_id;
                        let error = addCustomerPaymentMethodResp.error;
                        if (error === false && typeof paymentMethodId !== undefined) {
                            /**
                             * Save response in session and on success submit form
                             */
                            EBizSoapApiClientModel.sendAjaxProcessRequest(
                                $,
                                EBizChargeAvsCvvValidator.pciAddNewMethodResponseUrl,
                                addCustomerPaymentMethodResp
                            ).then((successResponse) => {
                                if (typeof (successResponse['error']) !== undefined && successResponse['error'] === false) {
                                    EBizChargeAvsCvvValidator.forceSubmit = true;
                                    form.submit();
                                }
                            }).catch((failResponse) => {
                                EBizChargeAvsCvvValidator.renderLoader($,false);
                            });
                        }
                    }
                ).catch((addCustomerPaymentMethodError) => {

                    EBizChargeAvsCvvValidator.renderLoader($,false);

                    EBizChargeAvsCvvValidator.showResponseMessage(
                        addCustomerPaymentMethodError,
                        customerData,
                        globalMessageList
                    );
                    return false;
                });
            }
        });
    },
    /**
     * Render Loader
     *
     * @param $
     * @param isShown
     */
    renderLoader: function ($, isShown= false) {
        if (isShown === true) {
            $('body').trigger('processStart').loader('show');
        } else {
            $('body').trigger('processStop').loader('hide');
        }

    }
}
