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
        'chosen',
        'mage/mage',
        'mage/calendar',
        'mage/backend/validation',
        'jquery/validate',
        'Ebizcharge_Ebizcharge/js/addCustomerCard',
        'Ebizcharge_Ebizcharge/js/subscriptions/subscription-processor'

    ], function ($, modal, url, chosen, mage, clanedar, mageValidation, jvalidate, addCustomerCardModel, SubscriptionProcessor) {

        'use_strict';

        /**
         * Suggessions Processor
         * @type {{suggestionListRows: string, init: (function(*=): SuggessionsProcessor), getInputFocus: getInputFocus, getSuggessionsByAjax: (function(*=, *=, *=): Promise<unknown>), productSearchBox: string, actionUrl: string, timeoutDuration: number, delayAtKeyUp: (function(...[*]=)), customerSuggessionBox: string, selectSuggessionValue: selectSuggessionValue, productSuggessionBox: string, checkSuggessions: checkSuggessions, customerId: string, customerSearchBox: string, removeSuggessionSelector: removeSuggessionSelector}}
         */
        let SuggessionsProcessor = {
            actionUrl: '',
            timeoutDuration: 500,
            customerSuggessionBox: '#customers-suggesstion-box',
            suggestionListRows: '#suggestion-list-rows',
            customerId: '#selectdivCustomer',
            customerSearchBox: '#customer-search-box',
            productSearchBox: '#product-search-box',
            productSuggessionBox: '#product-suggesstion-box',
            selectdivProduct: '#selectdivProduct',
            defaultDropdownItems: 20,
            dropdownProductListings: "",
            dropdownCustomersListings: "",
            subscriptionFormObj: $("#subscription-add-form"),
            cvvCardCode: "payment_cc_cid_new",
            popupModel: null,
            adminCardModel: null,
            isCardCodeValidated: false,
            isAvsCvvZipEnabled: null,
            avsCvvValidationActionUrl: null,
            subscribePreAction: document.getElementsByClassName("subscribe-pre-action"),


            /**
             * Custom Data
             *
             * @param $
             * @param customData
             * @returns {SuggessionsProcessor}
             */
            init: function ($, customData) {

                this.popupModel = modal;
                this.adminCustomerCardModel = addCustomerCardModel;
                this.subscriptionFormObj = $("#subscription-add-form");
                this.isAvsCvvZipEnabled = customData.isAvsCvvZipEnabled;
                this.avsCvvValidationActionUrl = customData.validateAvsCvvActionUrl;

                /**
                 * Disable pre subscription actions
                 */
                SuggessionsProcessor.disablePreSubscriptionActions($);


                /** fetching listings on Keypress **/
                let mainContainer = $('#container');

                /**
                 * check Suggessions
                 */
                SuggessionsProcessor.checkSuggessions($, mainContainer);

                /** select Customers suggession value **/
                let customerSuggessionPanel = $(SuggessionsProcessor.customerSuggessionBox);
                let customerKeywordsBox = $(SuggessionsProcessor.customerSearchBox);
                let customerIdBox = $(SuggessionsProcessor.customerId);

                /** suggession selector **/
                SuggessionsProcessor.selectSuggessionValue($, customerSuggessionPanel, customerKeywordsBox, customerIdBox);

                /** select Products suggession value **/
                let productSuggessionPanel = $(SuggessionsProcessor.productSuggessionBox);
                let productKeywordsBox = $(SuggessionsProcessor.productSearchBox);
                let productIdBox = $(SuggessionsProcessor.selectdivProduct);

                /** suggession selector **/
                SuggessionsProcessor.selectSuggessionValue($, productSuggessionPanel, productKeywordsBox, productIdBox);


                /** select input
                 * box getting Customer Search Box
                 * selected  **/
                SuggessionsProcessor.getInputFocus($, SuggessionsProcessor.customerSearchBox);

                /**
                 * select Product Search Box on
                 * click
                 */
                SuggessionsProcessor.getInputFocus($, SuggessionsProcessor.productSearchBox);

                /** remove Suggession Selector **/
                SuggessionsProcessor.removeSuggessionSelector($);

                /**
                 * By Default set listings
                 * @type {jQuery}
                 */
                SuggessionsProcessor.dropdownProductListings = $(SuggessionsProcessor.productSuggessionBox).html();

                /**
                 * by Default Set Values
                 * @type {jQuery}
                 */
                SuggessionsProcessor.dropdownCustomersListings = $(SuggessionsProcessor.customerSuggessionBox).html();

                /**
                 * render card code Validator
                 */
                SuggessionsProcessor.renderCardCodeValidate($);

                return this;
            },
            /**
             * Disable Pre Subscription actions
             * @param $
             */
            disablePreSubscriptionActions: function ($) {

                let subscribePreActionFields = $(this.subscribePreAction);
                let productSearchBox = $(this.productSearchBox);
                let productSuggessionBox = $(this.productSuggessionBox);
                let customerSearchBox = $(this.customerSearchBox);
                let customerSuggessionBox = $(this.customerSuggessionBox);

                /**
                 * Property disabled
                 */
                subscribePreActionFields.prop("disabled", true);

                /**
                 * Enabling back subscription form
                 */
                this.subscriptionFormObj.on("click keypress", function (evt) {
                    if (productSearchBox.val() !== "" && customerSearchBox.val() !== "") {
                        subscribePreActionFields.prop("disabled", false);
                    }
                });

            },

            /**
             *
             * Get Input Focus
             * */
            getInputFocus: function ($, inputElem) {
                $(inputElem).bind('click keypress', function (evt) {
                    // $(this).select();
                });
            },
            /**
             *
             * Remove Suggession Selector
             * */
            removeSuggessionSelector: function ($) {
                $('#subscription-add-form').bind('click keypress', function (evt) {
                    let targetElemId = evt.target.id;
                    let suggessionHide = true;

                    if (targetElemId === "product-search-box") {
                        suggessionHide = false;
                    } else if (targetElemId === "customer-search-box") {
                        suggessionHide = false;
                    }
                    if (suggessionHide) {
                        $('.suggession-container').hide();
                    }

                });
            },

            /**
             * is Card Validated
             *
             * @param $
             * @param customerData
             * @returns {boolean}
             */
            renderCardCodeValidate: function ($) {

                /** is Card CodeValidated **/
                SuggessionsProcessor.isCardCodeValidated = true;

                /**
                 * Bind Event on Click or KeyPress
                 */
                SuggessionsProcessor.subscriptionFormObj.bind("click keypress paste", function (evt) {
                    let targetId = evt.target.id;
                    let evtTarget = document.getElementById(targetId);
                    let targetTxt = $(evtTarget);

                    /** Card Code form Fields **/
                    if (targetId === SuggessionsProcessor.cvvCardCode) {
                        let cvvLen = 4;
                        if (targetTxt.val().length >= cvvLen) {
                            SuggessionsProcessor.isCardCodeValidated = false;
                            return false;
                        }
                    }
                });

                return SuggessionsProcessor.isCardCodeValidated;
            },

            /**
             *
             * Select Suggession Value
             * */
            selectSuggessionValue: function ($, suggessionContainer, keywordsElem, idsElem) {


                suggessionContainer.bind('click keypress', function (evt) {
                    let isRowLi = $(evt.target).is('li');
                    if (isRowLi) {
                        let elemKey = $(evt.target).attr('data-key');
                        let elemValue = $(evt.target).attr('data-value');

                        keywordsElem.val(elemKey);
                        idsElem.val(elemValue);
                        suggessionContainer.hide();

                        if (idsElem.attr('id') == 'selectdivCustomer') {
                            $(idsElem).trigger('change');
                        }

                    }
                });
            },
            /**
             * Delay Key Up
             */
            delayAtKeyUp: (function () {
                var vtimer = 100;
                return function (callbackFunc, msDuration) {
                    clearTimeout(vtimer);
                    vtimer = setTimeout(callbackFunc, msDuration);
                };
            })(),
            /**
             * Check Suggessions
             * @param $
             * @param elem
             */
            checkSuggessions: function ($, elem) {

                /**
                 * Binding the element
                 * for Listners
                 */
                $(elem).bind('click keyup keypress', function (evt) {

                    /** Fetching Target Element **/
                    var targetElem = evt.target;
                    var targetElemId = evt.target.id;
                    var actionUrl = $('#suggessions_search_action_url').val();

                    if (evt.type === "click") {
                        let client = {
                            x: evt.pageX,
                            y: evt.pageY
                        };


                        if (targetElemId === "product-search-box") {

                            $(SuggessionsProcessor.productSuggessionBox).html(SuggessionsProcessor.dropdownProductListings);
                            $(SuggessionsProcessor.productSuggessionBox).show();
                            if ($(SuggessionsProcessor.customerSuggessionBox).is(":visible")) {
                                $(SuggessionsProcessor.customerSuggessionBox).hide();
                            }

                        }
                        if (targetElemId === "customer-search-box") {

                            $(SuggessionsProcessor.customerSuggessionBox).html(SuggessionsProcessor.dropdownCustomersListings);
                            $(SuggessionsProcessor.customerSuggessionBox).show();

                            if ($(SuggessionsProcessor.productSuggessionBox).is(":visible")) {
                                $(SuggessionsProcessor.productSuggessionBox).hide();
                            }

                        }

                    }
                    if (evt.type === "keyup") {
                        /**
                         * products Search box
                         */
                        if (targetElemId === "product-search-box") {
                            SuggessionsProcessor.renderSuggessionDropDown(
                                $,
                                targetElem,
                                actionUrl,
                                "products",
                                SuggessionsProcessor.productSuggessionBox,
                                SuggessionsProcessor.defaultDropdownItems
                            );
                        }

                        /**
                         * Customer Search Box
                         */
                        if (targetElemId === "customer-search-box") {
                            SuggessionsProcessor.renderSuggessionDropDown(
                                $,
                                targetElem,
                                actionUrl,
                                "customers",
                                SuggessionsProcessor.customerSuggessionBox,
                                SuggessionsProcessor.defaultDropdownItems
                            );
                        }


                    }
                });
            },
            /**
             * Render Suggessions Dropdown
             *
             * @param $
             * @param targetElem
             * @param actionUrl
             * @param suggesionType
             * @param callBackSuggessionsDropdown
             */
            renderSuggessionDropDown: function ($, targetElem, actionUrl, suggesionType = "products", callBackSuggessionsDropdown, defaultItems) {

                var vTimer = 100;
                let targetElementVal = targetElem.value;
                targetElementVal += targetElem.value;


                if (targetElementVal.length > 2) {
                    /**
                     * Key Up Timer
                     */
                    var keyUpTimer = SuggessionsProcessor.delayAtKeyUp(
                        function () {
                            var searchBoxKeywords = targetElem.value;

                            var formData = {
                                keywords: searchBoxKeywords,
                                search_type: suggesionType,
                                default_items: defaultItems
                            };

                            /** sending Suggessions
                             * Processor
                             * **/
                            SuggessionsProcessor.getSuggessionsByAjax($, actionUrl, formData)
                                .then(
                                    (ajaxSuggessions) => {
                                        $(callBackSuggessionsDropdown).html(ajaxSuggessions);
                                        $(callBackSuggessionsDropdown).show();
                                        if (suggesionType == "customers") {
                                            SuggessionsProcessor.dropdownCustomersListings = $(callBackSuggessionsDropdown).html();
                                        }
                                        if (suggesionType == "products") {
                                            SuggessionsProcessor.dropdownProductListings = $(callBackSuggessionsDropdown).html();
                                        }

                                    }
                                )
                                .catch(
                                    (ajaxException) => {

                                        $(callBackSuggessionsDropdown).html('');
                                        $(callBackSuggessionsDropdown).hide();
                                        if (suggesionType == "customers") {
                                            SuggessionsProcessor.dropdownCustomersListings = $(callBackSuggessionsDropdown).html();
                                        }
                                        if (suggesionType == "products") {
                                            SuggessionsProcessor.dropdownProductListings = $(callBackSuggessionsDropdown).html();
                                        }
                                    }
                                );
                        }, SuggessionsProcessor.timeoutDuration
                    );
                }
            },
            validateAvsCvvAndSubmit: function ($, customerData) {

                SuggessionsProcessor.isAvsCvvZipEnabled = customerData.isAvsCvvEnabled;
                SuggessionsProcessor.avsCvvValidationActionUrl = customerData.validateAvsCvvActionUrl;

                /** create
                 * order form **/
                let subscriptionFormObj = SuggessionsProcessor.subscriptionFormObj;

                if (SuggessionsProcessor.isAvsCvvZipEnabled === "1") {
                    EBizChargeCardValidator.validateAvsCvvActionUrl = SuggessionsProcessor.avsCvvValidationActionUrl;
                    EBizChargeCardValidator.popupModal = modal;
                    EBizChargeCardValidator.popUpModalId = "avs-cvv-warnings-panel";
                    EBizChargeCardValidator.popupTitle = $.mage.__("Security mismatch dialog box...");
                    EBizChargeCardValidator.avsAddressMsg = "avs-address-msg";
                    EBizChargeCardValidator.avsPostalCodeMsg = "avs-postal-code-msg";
                    EBizChargeCardValidator.cvvCvv2Msg = "cvv-cvv2-msg";
                    EBizChargeCardValidator.cardMessageResponse = "card-message-response";
                    EBizChargeCardValidator.avsSaveActionButton = "avs-response-save-button";
                    EBizChargeCardValidator.avsDeclineMsgDivId = "ebiz-avs-cvv-msg-div";
                    EBizChargeCardValidator.avsDeclineMsgId = "avs-cvv-msg";
                    EBizChargeCardValidator.addCardDivClass = "ebiz-admin-add-card";

                    EBizChargeCardValidator.popUpButtons = [
                        {
                            text: $.mage.__("Continue subscribe anyway"),
                            class: 'action- scalable save avs-response-save-button primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                            id: 'avs-response-save-button',
                            click: function (evt) {
                                subscriptionFormObj.append($('<input>').attr({
                                    type: 'hidden',
                                    id: 'save_card_anyway',
                                    name: 'save_card_anyway',
                                    value: '1'
                                }));
                                subscriptionFormObj.submit();
                                this.closeModal();
                            }
                        }, {
                            text: $.mage.__("Cancel"),
                            class: 'action- scalable save avs-response-cancel-button primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                            id: 'avs-response-cancel-button',
                            click: function (evt) {
                                this.closeModal();
                            }
                        }
                    ];
                    let avsCvvZipPromise = EBizChargeCardValidator.processValidAddToCardAction($, EBizChargeCardValidator.validateAvsCvvActionUrl, subscriptionFormObj, modal);


                }

                return false;

            },

            /**
             * Get Suggessions By Ajax
             * @param $
             * @param ajaxUrl
             * @param formData
             * @returns {Promise<unknown>}
             */
            getSuggessionsByAjax: function ($, ajaxUrl, formData) {
                /** Defining the Promise when Ajax Request gets completed **/
                return new Promise((suggessionsSuccess, errorException) => {

                    let imageLoader = $('#search_image_loader_src').val();
                    let suggessionsResponse =
                        $.ajax(
                            {
                                url: ajaxUrl,
                                type: "POST",
                                data: formData,
                                dataType: "html",
                                //    showLoader:true,
                                // async: true,
                                beforeSend: function () {
                                    if (formData.search_type === 'customers') {
                                        $(SuggessionsProcessor.customerSuggessionBox).html('<ul id="suggestion-list-rows"><li class="search-loader"><img src="' + imageLoader + '" height="20" /></li></ul>');
                                        $(SuggessionsProcessor.customerSuggessionBox).show();
                                    } else {
                                        $(SuggessionsProcessor.productSuggessionBox).html('<ul id="suggestion-list-rows"><li class="search-loader"><img src="' + imageLoader + '" height="20" /></li></ul>');
                                        $(SuggessionsProcessor.productSuggessionBox).show();
                                    }

                                },
                                contentType: "application/x-www-form-urlencoded; charset=UTF-8"

                            }).done(resp => {
                            // console.log(resp);
                            suggessionsSuccess(resp);
                        }).fail(e => {
                            errorException(e);
                        });

                });
            }

        }


        return function (customData) {

            const frequencyDays = {
                'daily': 1,
                'weekly': 7,
                'bi-weekly': 14,
                'bi-monthly': 14,
                'four-week': 28,
                'monthly': 30,
                'two-month': 60,
                'quarterly': 90,
                'three-month': 90,
                '90-days': 90,
                'four-month': 120,
                'five-month': 150,
                'bi-annually': 180,
                'six-month': 180,
                '180-days': 180,
                'annually': 365,
                'default': 30,
            };

            $(document).ready(function () {

              //  $(".chosen").chosen();
                url.setBaseUrl(BASE_URL);
                var str = $('#sub_btn').text();

                /**
                 * Options
                 *
                 * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
                 */
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    title: 'Confirm',
                    buttons: [{
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    }, {
                        text: $.mage.__('Yes, Save'),
                        class: 'btn-background-del',
                        click: function () {
                            $('#subscription-add-form').submit();
                        }
                    }]
                }
                /**
                 * options Confirm Del
                 *
                 * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
                 */
                var optionsConfirmDel = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    title: 'Delete',
                    buttons: [{
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    }, {
                        text: $.mage.__('Yes, Delete'),
                        class: 'btn-background-del',
                        click: function () {
                            $('#delsub').submit();
                        }
                    }]
                };
                /**
                 * options Confirm
                 *
                 * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
                 */
                var optionsConfirm = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    title: 'Subscription action',
                    buttons: [{
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    }, {
                        text: $.mage.__('Yes, ' + str),
                        class: 'btn-background-del',
                        click: function () {
                            $('#unsub').submit();
                        }
                    }]
                };

                /**
                 * option Address
                 *
                 * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): (undefined)}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
                 */
                var optionsAddress = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    title: 'Customer Address',
                    buttons: [{
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    }, {
                        text: $.mage.__('Save Address'),
                        class: 'btn-background-del',
                        click: function (evt) {
                            //  if (!$('#recurringAddressForm').mage('validation', {})) return;
                            if (!$('#recurringAddressForm').valid()) {
                                return;
                            }

                            var customerIdAddress = $('#customerIdAddress').val();
                            var addressActionUrl = $('#addressActionUrl').val();
                            var loadCustomerAddressUrl = $('#loadCustomerAddressUrl').val();
                            url.setBaseUrl(BASE_URL);

                            $.ajax({
                                type: "POST",
                                url: addressActionUrl,
                                dataType: "html",
                                data: $("#recurringAddressForm").serialize(),//only input
                                showLoader: true,
                                success: function (response) {
                                    if (response == 1) {
                                        $('.btn-background-cancel').click();
                                        $.ajax({
                                            type: "POST",
                                            url: loadCustomerAddressUrl,
                                            dataType: "json",
                                            data: {customer_id: customerIdAddress},//only input
                                            showLoader: true,
                                            success: function (data) {
                                                $('#addressBill').empty();
                                                $('#addressShip').empty();

                                                if (data.html_data !== undefined && data.html_data) {
                                                    $('#addressBill').append(data.html_data);
                                                    $('#addressShip').append(data.html_data);

                                                } else {
                                                    $('#addressBill').append('<option value="">No Address found</option>');
                                                    $('#addressShip').append('<option value="">No Address found</option>');
                                                }

                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }]
                };

                /**
                 * options Address Alert
                 *
                 * @type {{buttons: [{text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
                 */
                var optionsAddressAlert = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    title: 'Recurring action',
                    buttons: [{
                        text: $.mage.__('Ok'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };

                $(".sub_btn").click(function () {
                    var str = $('#sub_btn').text();

                    str = str.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                        return letter.toLowerCase();
                    });

                    $('#myModelConfirm').modal('openModal');
                });

                $("#address_btn").click(function () {
                    var customerId = $('#selectdivCustomer').val();

                    if (customerId !== "" && customerId !== null) {
                        $('#customerIdAddress').val(customerId);
                        $("#recurringAddressForm").trigger("reset");
                        $('#optionsAddress').modal('openModal');
                    } else {

                        $('#optionsAddressAlert').html('<div>Please select a valid customer and then add address button.</div>');
                        $('#optionsAddressAlert').modal('openModal');
                        document.getElementById("selectdivCustomer").style.borderColor = "red";
                    }
                });

                let popup = modal(options, $('#myModel'));
                let popupConfirmDel = modal(optionsConfirmDel, $('#myModelConfirmDel'));
                let popupConfirm = modal(optionsConfirm, $('#myModelConfirm'));
                let popupAddress = modal(optionsAddress, $('#optionsAddress'));
                let popupAddressAlert = modal(optionsAddressAlert, $('#optionsAddressAlert'));


                let minDate = new Date();
                minDate.setDate(minDate.getDate() + 1);

                $('#dates').dateRange({
                    buttonText: '',
                    dateFormat: 'Y-mm-dd',
                    minDate: minDate,
                    from: {
                        id: 'start_date'
                    },
                    to: {
                        id: 'expire_date'
                    }
                });

                /**
                 * recurring indefinitely
                 * update
                 */
                $('#rec_indefinitely').click(function () {
                    $("#end-date-div").toggle();
                    $('#expire_date').prop('disabled', function (i, val) {
                        return !val;
                    });
                });

                $('#selectdivPayment').change(function () {
                    $('#payment_method_name').val($('#selectdivPayment option:selected').text());
                });

                $("#selectdivCustomer").change(function () {
                    let customerId = $("#selectdivCustomer").val();
                    let loadCustomerUrl = $('#loadCustomerUrl').val();
                    let loadCustomerAddressUrl = $('#loadCustomerAddressUrl').val();

                    $.ajax({
                        method: "POST",
                        url: loadCustomerUrl,
                        data: {
                            customer_id: customerId
                        },
                        dataType: "json",
                        showLoader: true,
                        success: function (data) {
                            $('#selectdivPayment').empty();

                            if (data.html_data !== undefined && data.html_data) {
                                $('#selectdivPayment').append(data.html_data);
                                $('#payment_method_name').val($('#selectdivPayment option:selected').text());
                            } else {
                                $('#selectdivPayment').append('<option value="">No payment method found</option>');
                            }
                        },
                        complete: function () {
                        },
                        error: function (result) {
                            $('#selectdivPayment').empty();
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: loadCustomerAddressUrl,
                        dataType: "json",
                        data: {customer_id: customerId},//only input
                        showLoader: true,
                        success: function (data) {
                            $('#addressBill').empty();
                            $('#addressShip').empty();
                            if (data.html_data !== undefined && data.html_data) {
                                $('#addressBill').append(data.html_data);
                                $('#addressBill').trigger("chosen:updated");
                                $('#addressShip').append(data.html_data);
                                $('#addressShip').trigger("chosen:updated");

                            } else {
                                $('#addressBill').append('<option value="">No Address found</option>');
                                $('#addressBill').trigger("chosen:updated");
                                $('#addressShip').append('<option value="">No Address found</option>');
                                $('#addressShip').trigger("chosen:updated");
                            }
                        }
                    });
                });

                let cardNewOption = $("#card_new");
                let bankNewOption = $("#bank_new");
                let cardSaveOption = $("#card_saved");
                let bankSaveOption = $("#bank_saved");
                let savedCardPanel = $("#my_card_saved");
                let savedBankPanel = $("#my_bank_saved");


                $("#card_saved").bind("click keypress", function () {
                    var loadCustomerUrl = $('#loadCustomerUrl').val();

                    var customerId = $("#selectdivCustomer").val();

                   savedCardPanel.find("select, input").prop("disabled", false);
                    if(bankNewOption.is(":checked")){
                        savedBankPanel.find("select, input").prop("disabled", true);
                    }

                    $.ajax({
                        method: "POST",
                        url: loadCustomerUrl,
                        data: {customer_id: customerId, ebizs_option: 'cc'},
                        dataType: "json",
                        showLoader: true,
                        success: function (data) {
                            $('#selectdivPayment').empty();
                            if (data.html_data !== undefined && data.html_data) {
                                $('#selectdivPayment').append(data.html_data);
                                $('#payment_method_name').val($('#selectdivPayment option:selected').text());
                            } else {
                                $('#selectdivPayment').append('<option value="">No payment method found</option>');
                            }
                        },
                        error: function (result) {
                            //alert("No data found!");
                            $('#selectdivPayment').empty();
                            $('#payment_method_name').val("");
                        }
                    });

                });

                $("#bank_saved").bind("click keypress", function () {
                    var loadCustomerUrl = $('#loadCustomerUrl').val();

                    var customerId = $("#selectdivCustomer").val();

                    savedBankPanel.find("select, input").prop("disabled", false);
                    if(cardNewOption.is(":checked")){
                        savedCardPanel.find("select, input").prop("disabled", true);
                    }

                    $.ajax({
                        method: "POST",
                        url: loadCustomerUrl,
                        data: {customer_id: customerId, ebizs_option: 'check'},
                        dataType: "json",
                        showLoader: true,
                        success: function (data) {
                            $('#selectdivPaymentAch').empty();

                            if (data.html_data !== undefined && data.html_data) {
                                $('#selectdivPaymentAch').append(data.html_data);
                                $('#payment_method_name').val($('#selectdivPaymentAch option:selected').text());
                            } else {
                                $('#selectdivPaymentAch').append('<option value="">No payment method found</option>');
                            }
                        },
                        error: function (result) {
                            $('#selectdivPaymentAch').empty();
                        }
                    });

                });
                let currentPaymentOption = $(document.getElementById("ebzc_option"));
                let currentPaymentOptionType = $(document.getElementById("ebzc_option_type"));
                let currPaymentMethodIdOption = $(document.getElementById("curr_payment_method_id"));
                let currPaymentMethodNameOption = $(document.getElementById("curr_payment_method_name"));

                $('.pay-select').bind('click keypress', function () {

                    let selectedTab = $(this).attr("for");

                    currentPaymentOption.val($(this).attr("for"));

                    if (selectedTab === "credit_card") {
                        jQuery("#card_new").trigger("click");
                    }

                    $('#' + $(this).data("tab") + ' fieldset').prop('disabled', false);
                    $('#' + $(this).data("disable") + ' fieldset').prop('disabled', true);
                })

                /**
                 * Payment Methods Panel
                 */
                $(document.getElementById("payment-methods-panel")).bind("click keypress", function (evt) {

                    let targetId = evt.target.id;

                    //console.log(targetId);
                    if (targetId === "card_new" || targetId === "tab-credit-card" || targetId === "ui-id-1") {
                        currentPaymentOption.val("new");
                        currentPaymentOptionType.val("credit_card");
                        currPaymentMethodIdOption.val("");
                        currPaymentMethodNameOption.val("");

                    }
                    if (targetId === "tab-ach" || targetId === "ui-id-2") {
                        currentPaymentOption.val("new");
                        currentPaymentOptionType.val("ach");
                        currPaymentMethodIdOption.val("");
                        currPaymentMethodNameOption.val("");
                    }

                });

                $("#tab_pay_by_bank fieldset").prop('disabled', true);
                /**
                 * select div payments via ach
                 */
                $("#selectdivPayment", "#selectdivPaymentAch").on("change", function(evt){
                    let pm = this.value.length > 0 ? this.value.split("||"): [];
                    let pmId = "";
                    let pmName = "";
                    if(pm !== ""){
                        pmId = pm[0];
                        pmName = pm[1];
                    }
                    if (this.value.length > 0) {
                        currPaymentMethodIdOption.val(pmId);
                        currPaymentMethodNameOption.val(pmName);
                    } else {
                        currPaymentMethodNameOption.val("");
                        currPaymentMethodIdOption.val("");
                    }

                });

                $('.card_option').bind('click keypress ready change', function () {
                    let savedOptions = $("#selectdivPayment");
                    let pm = (typeof savedOptions !== "undefined") && (savedOptions.val().length > 0) ?savedOptions.val().split("||"): [];

                    let pmId = "";
                    let pmName = "";

                    if(pm !== ""){
                        pmId = pm[0];
                        pmName = pm[1];
                    }

                    if (this.value === 'new') {
                        currentPaymentOption.val("new");
                        currPaymentMethodIdOption.val("");
                        currPaymentMethodNameOption.val("");

                        $('.my_card_new').show();
                        $('.my_card_saved').hide();
                        $('#my_card_saved').prop('disabled', true);
                        $('#my_card_new').prop('disabled', false);

                    } else if (this.value === 'saved') {
                        currentPaymentOption.val("saved");
                        currPaymentMethodIdOption.val(pmId);
                        currPaymentMethodNameOption.val(pmName);

                        $('.my_card_new').hide();
                        $('.my_card_saved').show();
                        $('#my_card_new').prop('disabled', true);
                        $('#my_card_saved').prop('disabled', false);
                        $('#my_bank_new').prop('disabled', true);
                        $('#my_bank_saved').prop('disabled', true);
                    }
                });


                $('.bank_option').bind('click keypress ready change', function () {
                    let savedOptions = $("#selectdivPaymentAch");
                    let pm = (typeof savedOptions !== "undefined" && savedOptions.val().length) > 0 ?savedOptions.val().split("||"): [];
                    let pmId = "";
                    let pmName = "";

                    if(pm !== ""){
                        pmId = pm[0];
                        pmName = pm[1];
                    }
                    if (this.value === 'bank_new') {
                        currentPaymentOption.val("new");
                        currPaymentMethodIdOption.val("");
                        currPaymentMethodNameOption.val("");

                        $('.my_bank_new').show();
                        $('.my_bank_saved').hide();
                        $("#my_bank_new").prop('disabled', false);
                        $("#my_bank_saved").prop('disabled', true);

                    } else if (this.value === 'bank_saved') {
                        currentPaymentOption.val("saved");
                        currPaymentMethodIdOption.val(pmId);
                        currPaymentMethodNameOption.val(pmName);

                        $('.my_bank_new').hide();
                        $('.my_bank_saved').show();
                        $("#my_bank_new").prop('disabled', true);
                        $("#my_bank_saved").prop('disabled', false);
                    }
                });


                $('#country').bind('click keypress ready change', function () {
                    let regionJson = customData.regionJson;
                    let countryCode = $(this).val();
                    if (regionJson[countryCode]) {
                        $('#region_id').css('display', 'block')
                            .removeAttr('disabled')
                        $('#region_id_text').css('display', 'none')
                            .attr('disabled', true);
                        let html_code = '<option value="">Please select a region, state or province.</option>';
                        $('#region_id').html(html_code);
                        $.each(regionJson[countryCode], $.proxy(function (key, value) {
                            let regionId = key;
                            let regionData = value.name;
                            html_code = '<option value="' + regionId + '">' + regionData + '</option>';
                            $('#region_id').append(html_code);
                        }, this));
                    } else {
                        $('#region_id').css('display', 'none')
                            .attr('disabled', true);
                        $('#region_id_text').css('display', 'block')
                            .removeAttr('disabled');
                    }

                });

                let dataForm = $('#subscription-add-form');
                let ignore = null;

                dataForm.mage('validation', {
                    rules: {
                        'expire_date': {
                            validateFrequencyDateRange: true,
                        }
                    },
                    messages: {
                        'expire_date': {
                            validateFrequencyDateRange: 'Please select valid dates for selected frequency.',
                        }
                    },
                    ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden',
                    errorPlacement: function (error, element) {

                        if (element.is('#start_date')) {
                            jQuery('#start_date').siblings('button:first').after(error);
                        } else if (element.is('#expire_date')) {
                            jQuery('#expire_date').siblings('button:first').after(error);
                        } else if (element.is('#payment_exp_new')) {
                            jQuery('#expire-block').after(error);
                        } else {
                            element.after(error);
                        }
                    },
                    submitHandler: function (form) {

                        let customer_id = $("#selectdivCustomer").val();
                        let product_id = $("#selectdivProduct").val();
                        let start_date = $("#start_date").val();
                        let end_date = $("#expire_date").val();
                        let rec_indefinite = $("#rec_indefinitely").val();
                        let recurringAlreadyExistUrl = $('#recurringAlreadyExistUrl').val();


                        /*** recurring Already Date **/
                        $.ajax({
                            method: "POST",
                            url: recurringAlreadyExistUrl,
                            data: {
                                customer_id: customer_id,
                                product_id: product_id,
                                start_date: start_date,
                                end_date: end_date,
                                rec_indefinite: rec_indefinite
                            },
                            dataType: "json",
                            showLoader: true,
                            success: function (data) {
                                if (data.status === 'P') {
                                    $('body').trigger('processStart');
                                    form.submit();
                                } else {
                                    $('#optionsAddressAlert').html("<div>" + data.message + "</div>");
                                    $('#optionsAddressAlert').modal('openModal');
                                }
                            },
                            error: function (error) {
                                console.log(error);
                            }
                        });
                    }
                }).find('input:text').attr('autocomplete', 'off');

                $('button#save-subscription').bind("click keypress", function (evt) {

                    dataForm.validation('isValid');

                    if ($('#subscription-add-form').valid()) {

                        let isAddNewCardSelected = false;
                        let paymentOptionType = $("#credit_card_type");

                        if (paymentOptionType.val() !== "new") {
                            $('#subscription-add-form').submit();

                        } else {
                            /**
                             * Initialize Precessor
                             */
                            SuggessionsProcessor.validateAvsCvvAndSubmit($, customData);
                        }
                    }
                });

            });

            /**
             * Validate Add Method
             */
            $.validator.addMethod(
                'validateFrequencyDateRange', function (value) {
                    let days = 0;

                    if ($('#rec_indefinitely').prop("checked") === false) {

                        let frequency = $("#freqId").val();
                        let startDate = $("#start_date").val();
                        let endDate = $("#expire_date").val();

                        days = frequencyDays[frequency];

                        let frequencyDay = days;

                        const diffInMs = new Date(endDate) - new Date(startDate)
                        const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

                        if (Math.round(diffInDays) < Math.round(frequencyDay)) {
                            return false;

                        }
                    }
                    return true;
                },
                $.mage.__('Please select valid dates for selected frequency.')
            );

            /**
             * Initialize Precessor
             */
            SuggessionsProcessor.init($, customData);

            /**
             * Quote Processor Custom Data
             */
            SubscriptionProcessor._init($, customData);

        }

    })
;
