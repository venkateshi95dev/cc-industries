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
        'underscore',
        'jquery/validate',
        'mage/translate',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Ui/js/modal/modal'

    ],
    /**
     * UI Card Validator
     * @param $
     * @param Ui
     * @param jqValidator
     * @param translator
     * @param validator
     * @param modal
     * @returns {function(...[*]=)}
     */
    function ($, Ui, _, jqValidator, translator, validator, modal) {

        /**
         * Return Custom Card Validator
         */
        return function (customData) {
            EBizChargeCardValidator.popupModal = modal;
            /**
             * Card Validator Init
             */
            let cardValidatorInit = EBizChargeCardValidator._init($, _, customData, Ui, jqValidator, translator, validator, modal);

        }
    });

/**
 *  Ebizcharge Card Validator
 * @type {{admin_card_form: string, _init: EBizChargeCardValidator._init}}
 */
let EBizChargeCardValidator = {

    adminCardFormObj: null,
    adminCardFormId: null,
    formFields: null,
    saveFormButton: null,
    isCardValidated: false,
    validateAvsCvvActionUrl: null,
    cvvAvsAddToCardModel: null,
    popupModal: null,
    avsCvvWarningsPanel: null,
    avsAddressMsg: null,
    avsPostalCodeMsg: null,
    cvvCvv2Msg: null,
    addCardSaveButton: null,
    addCardCancelButton: null,
    closeCvvAvsButton: null,
    popUpButtons: null,
    popUpModalId: null,
    popupTitle: null,
    isCardValid: false,
    cardMessageResponse: null,
    avsSaveActionButton: null,
    avsDeclineMsgDivId: null,
    avsDeclineMsgId: null,
    addCardDivClass: null,

    /**
     * Init the Card Validator
     * @param $
     * @param customData
     * @param Ui
     * @param jqValidator
     * @param translator
     * @param validator
     * @private
     */
    _init: function ($, _, customData, Ui, jqValidator, translator, validator, modal) {

        /**
         * admin Card Form
         *  Variables
         * **/
        let adminCardFormId = document.getElementById(customData.adminCardForm.id);
        let saveFormButtonId = document.getElementById(customData.adminCardForm.fields.saveFormBtn);

        this.adminCardFormObj = $(adminCardFormId);
        this.adminCardFormId = customData.adminCardForm.id;
        this.formFields = customData.adminCardForm.fields;
        this.saveFormButton = $(saveFormButtonId);
        this.validateAvsCvvActionUrl = customData.adminCardForm.validateAvsCvvActionUrl;
        this.cvvAvsAddToCardModel = customData.adminCardForm.fields.cvvAvsAddToCardModel;
        this.popupModal = modal;
        this.avsCvvWarningsPanel = customData.adminCardForm.fields.avsCvvWarningsPanel;
        this.avsAddressMsg = customData.adminCardForm.fields.avsAddressMsg;
        this.avsPostalCodeMsg = customData.adminCardForm.fields.avsPostalCodeMsg;
        this.cvvCvv2Msg = customData.adminCardForm.fields.cvvCvv2Msg;
        this.addCardSaveButton = customData.adminCardForm.fields.addCardSaveButton;
        this.addCardCancelButton = customData.adminCardForm.fields.addCardCancelButton;
        this.closeCvvAvsButton = customData.adminCardForm.fields.closeCvvAvsButton;
        this.cardMessageResponse = customData.adminCardForm.fields.cardMessageResponse;
        this.avsSaveActionButton = customData.adminCardForm.fields.avsSaveActionButton;
        this.avsDeclineMsgDivId = customData.adminCardForm.fields.avsDeclineMsgDivId;
        this.avsDeclineMsgId = customData.adminCardForm.fields.avsDeclineMsgId;
        this.addCardDivClass = customData.adminCardForm.fields.addCardDivClass;

        this.popUpButtons = [
            {
                text: $.mage.__("Save card anyway"),
                class: 'action- scalable save avs-response-save-button primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                id: 'avs-response-save-button',
                click: function (evt) {
                    EBizChargeCardValidator.adminCardFormObj.append($('<input>').attr({
                        type: 'hidden',
                        id: 'save_card_anyway',
                        name: 'save_card_anyway',
                        value: '1'
                    }));
                    EBizChargeCardValidator.adminCardFormObj.submit();
                    this.closeModal();
                }
            }, {
                text: $.mage.__("Cancel adding new card"),
                class: 'action- scalable save primary avs-response-cancel-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                id: 'avs-response-cancel-button',
                click: function (evt) {
                    this.closeModal();
                }
            }
        ];
        this.popUpModalId = "avs-cvv-warnings-panel";
        this.popupTitle = $.mage.__("Security mismatch dialog box...");


        /**
         * Render Validate of Form
         */
        this.renderValidate($, customData);


        /**
         * Render Validate Date
         */
        this.renderDateValidator($, customData);

        /**
         * Close AVS Cvv Warnings
         */
        this.closeAvsCvvWarnings($, customData);

        /**
         * Submit Add to Card Action form
         */
        this.submitAddToCardAction($, customData);

        /**
         * validate Action Form
         */
        this.validateSubmitAction($, customData, modal);


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
     * render Avs CVV Warnings Popup
     * @param $
     */
    renderAvsCvvWarningsPopup: function ($, modal = null) {

        let popupModelId = EBizChargeCardValidator.popUpModalId;
        // let popupModelId = EBizChargeCardValidator.popUpModalId;
        let popupTitle = EBizChargeCardValidator.popupTitle;
        let popupButtons = EBizChargeCardValidator.popUpButtons;
        /**
         * render popup
         * @type {{buttons: *[], responsive: boolean, innerScroll: boolean, type: string, title: string}}
         */
        let popupOptions = EBizChargeCardValidator.prepareAvsCvvWarningsPopupOptions($, popupTitle, popupButtons);

        let popup = EBizChargeCardValidator.popupModal(popupOptions, $(document.getElementById(popupModelId)));

        $(document.getElementById(popupModelId)).modal('openModal');

        return false;
    },

    /**
     * Submit Add to Card Action
     *
     * @param $
     * @param customData
     */
    submitAddToCardAction: function ($, customData) {
        /**
         * Save Card Button
         *
         * @type {jQuery|HTMLElement}
         */
        let saveCardButton = $(document.getElementById(EBizChargeCardValidator.addCardSaveButton));

        /**
         * Save Card Button
         */
        saveCardButton.bind("keypress click", function (evt) {

            EBizChargeCardValidator.adminCardFormObj.valid() && $("body").trigger("processStart");

            /**
             * Admin Card Form Object
             */
            EBizChargeCardValidator.adminCardFormObj.submit();
        });

    },

    /**
     * REnder Date Validator
     * @param $
     * @param customerData
     */
    renderDateValidator: function ($, customerData) {
        /**
         * Custom method to validate card expire date
         */
        $.validator.addMethod(
            'validate-expire-date', function (value) {

                let expYear = value;
                let expMonth = $(document.getElementById(EBizChargeCardValidator.formFields.cardExpMonth)).val();
                const today = new Date();
                const expireDate = new Date();

                expireDate.setFullYear(expYear, expMonth, 0);

                if (expireDate < today) {
                    return false;
                }
                return true;
            },

            $.mage.__("Please enter a valid expire date.")
        );
    },

    /**
     * Validate Submit Action Form
     * @param $
     * @param customerData
     */
    validateSubmitAction: function ($, customerData, modal) {
        /**
         * Save Form Button
         */
        this.saveFormButton.bind("click keypress", function (evt) {

            let isFormValid = EBizChargeCardValidator.adminCardFormObj.valid();
            let ajaxAddCardUrl = EBizChargeCardValidator.validateAvsCvvActionUrl;

            if (!isFormValid) {
                return false;
            }
            let creditCardForm = EBizChargeCardValidator.adminCardFormObj;
            let formParams = creditCardForm.serializeArray();
            let pageAction = "new";
            $.each(formParams, function (key, val) {
                if (formParams[key].name === 'action') {
                    pageAction = formParams[key].value;
                }
            });

            if (pageAction === 'edit') {
                if (typeof EBizChargeCardValidator.adminCardFormObj !== null) {
                    EBizChargeCardValidator.adminCardFormObj.valid();
                }
                $("body").trigger("processStart");
                creditCardForm.submit();
                return ;
            }

            //  console.log(evt.target.id);
            /**
             * Process Validate CVV AVS Card
             */
            let validateCvvAvsCard = EBizChargeCardValidator.processValidAddToCardAction($, ajaxAddCardUrl, creditCardForm, modal);

            return false;

        });
    },

    /**
     *
     * @param $
     * @param ajaxAddCardUrl
     * @param creditCardForm
     * @param modal
     */
    processValidAddToCardAction: function ($, ajaxAddCardUrl = null, creditCardForm = null, modal = null) {

        $(document.getElementById(EBizChargeCardValidator.avsDeclineMsgDivId)).hide();

        /**
         *
         * @type {Promise<unknown>}
         */
        let validateCvvAvsCardPromiseResp = EBizChargeCardValidator.processValidateCvvAvsCardAction($, ajaxAddCardUrl, creditCardForm);

        /**
         * ValidateCvvAvsCardResp
         */
        validateCvvAvsCardPromiseResp.then((validCardCvvAvsResp) => {

            // console.log(validCardCvvAvsResp);
            // let popup = EBizChargeCardValidator.renderAvsCvvWarningsPopup($, EBizChargeCardValidator.cvvAvsAddToCardModel);
            let respJSON = JSON.parse(validCardCvvAvsResp);
            let respData = respJSON.resp_data;
            let avsResponse = respJSON.resp_data;

            let isError = false;


            if (respData.error === true) {

                isError = true;
                // $(document.getElementById(EBizChargeCardValidator.avsCvvWarningsPanel)).show();
                /**
                 * assign Data
                 */
                EBizChargeCardValidator.assignAvsCvvMessages($, avsResponse, isError);

                if (respData.result_code !== 'D') {
                    /**
                     * Showing POPUP
                     */
                    EBizChargeCardValidator.renderAvsCvvWarningsPopup($, modal);
                } else {
                    let currentUrl = window.location.href;

                    if (!currentUrl.includes('order_create')) {
                        $(document.getElementById(EBizChargeCardValidator.avsDeclineMsgId)).html(respData.message);
                        $(document.getElementsByClassName(EBizChargeCardValidator.addCardDivClass)).css({
                            'margin-top': '0',
                            'padding-top': '0'
                        });
                        $(document.getElementById(EBizChargeCardValidator.avsDeclineMsgDivId)).show();
                    } else {
                        window.location.reload();
                    }
                }
            }

            /**
             * if every thing is OK and Credit Card is Correct
             */
            if (respData.error === false) {

                if (typeof(EBizChargeCardValidator.adminCardFormObj) !== "undefined" && EBizChargeCardValidator.adminCardFormObj !== null) {

                    EBizChargeCardValidator.adminCardFormObj.valid();
                }
                $("body").trigger("processStart");
                /**
                 * Admin Card Form Object
                 */
                $(creditCardForm).submit();
            }

            return isError;


        });

        validateCvvAvsCardPromiseResp.catch((notValidCvvAvsResp) => {
            //  let popup = EBizChargeCardValidator.renderAvsCvvWarningsPopup($, EBizChargeCardValidator.cvvAvsAddToCardModel);

            let respData = JSON.parse(notValidCvvAvsResp);
            let isError = true;

            $(document.getElementById(EBizChargeCardValidator.avsCvvWarningsPanel)).show();

            /**
             * assign Data
             */
            // EBizChargeCardValidator.assignAvsCvvMessages($,  respData, isError);
        });

    },

    /**
     * Response Assign to Messages
     * @param $
     * @param respData
     * @param isError
     */
    assignAvsCvvMessages: function ($, respData, isError = false) {

        /**
         * Avs Response
         * @type {number}
         */
        let avsResp = setTimeout(function (evt) {
            let avsAddressMsg = $(document.getElementById(EBizChargeCardValidator.avsAddressMsg));
            let avsZipCodeMsg = $(document.getElementById(EBizChargeCardValidator.avsPostalCodeMsg));
            let cvvCvv2Msg = $(document.getElementById(EBizChargeCardValidator.cvvCvv2Msg));
            let cardMessageResponse = $(document.getElementById(EBizChargeCardValidator.cardMessageResponse));
            let saveActionButton = $(document.getElementsByClassName(EBizChargeCardValidator.avsSaveActionButton));

            saveActionButton.removeAttr("disabled");

            if (respData.valid === false) {
                cardMessageResponse.show();

                if (respData.result_code === "D" ) {
                    saveActionButton.attr("disabled", "disabled");
                }
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
        },50);

    },
    /**
     *
     * @param $
     * @param customData
     */
    closeAvsCvvWarnings: function ($, customData) {

        /**
         * Close CVV AVS Button
         */
        $(document.getElementById(EBizChargeCardValidator.closeCvvAvsButton)).bind("click keypress", function (evt) {
            $(document.getElementById(EBizChargeCardValidator.avsCvvWarningsPanel)).hide();
        });
        /**
         * Close Cancel Button
         */
        $(document.getElementById(EBizChargeCardValidator.addCardCancelButton)).bind("click keypress", function (evt) {
            $(document.getElementById(EBizChargeCardValidator.avsCvvWarningsPanel)).hide();
        });
    },

    /**
     *
     * @param $
     * @param ajaxCardUrl
     * @param creditCardForm
     * @returns {Promise<unknown>|boolean}
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
                        //  console.log((respData));
                        processSuccess(respData);
                    },
                    /**
                     * Done
                     * @param respData
                     */
                    done: function (respData) {

                        // processSuccess(respData);
                    },
                    /**
                     * When Complete the Whole Process
                     * @param respData
                     */
                    complete: function (respData) {
                        creditCardForm.trigger('processStop');

                        //  processSuccess(respData);
                    },
                    /**
                     * Fail When Error
                     *
                     * @param error
                     */
                    fail: function (error) {
                        // console.log(error);
                        processFail(error);
                    },
                    /**
                     * Error
                     * @param error
                     */
                    error: function (error) {
                        //  console.log(error);
                        processFail(error);
                    }
                }
            );

        }); /** end of Ajax Promise **/

        return cvvAvsValidatorPromise;

    },

    /**
     *
     * @param $
     * @param ccNumberId
     */
    cleanAlphaChars: function ($, ccNumberId) {
        /**
         * Clean Cc Number
         * @type {number}
         */
        let cleanCcNumber = setTimeout(function () {
            let ccNumberTxt = $(document.getElementById(ccNumberId));

            /** Init Val **/
            let ccNumberInitVal = ccNumberTxt.val();
            let ccNumberVal = ccNumberInitVal.replace(/[^0-9]/g, "");

            /**
             * Cc Number and Cc Number inital val
             */
            if (ccNumberVal !== ccNumberInitVal) {
                ccNumberTxt.val(ccNumberVal);
            }
            /**
             * Clear Time Out
             */
            clearTimeout(cleanCcNumber);

        }, 10);
    },

    /**
     *
     * @param $
     * @param customerData
     * @returns {boolean}
     */
    renderValidate: function ($, customerData) {

        /** is Card Validated **/
        this.isCardValidated = false;

        /**
         * Bind Event on Click or KeyPress
         */
        this.adminCardFormObj.bind("click keypress paste", function (evt) {

            let targetId = evt.target.id;
            let targetTxt = $(document.getElementById(targetId));

            let formFields = EBizChargeCardValidator.formFields;

            if (targetId === formFields.cardNumber) {

                /**
                 * Clean Credit Card Number
                 */
                EBizChargeCardValidator.cleanAlphaChars($, formFields.cardNumber);

                if (targetTxt.val().length >= 16) {
                    return false;
                }
                if (targetTxt.val().length >= 14) {
                    let cardNumber = targetTxt.val();
                }

            }
            /** Card Code form Fields **/
            if (targetId === formFields.cardCode) {
                let cardType = $(document.getElementById(formFields.cardType));

                let cvvLen = 4;
                if (targetTxt.val().length >= cvvLen) {
                    return false;
                }
            }


        });

        return this.isCardValidated;
    }
}
