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
        'mage/backend/validation',
        'jquery/validate',
        'mage/mage',
        'mage/calendar',
        'Ebizcharge_Ebizcharge/js/addCustomerCard'

    ],
    function ($, modal, mageValidation, jvalidate, addCustomerCardModel) {

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

            /**
             *
             * @type {{_init: _init, renderCardCodeValidate: (function(*): boolean)}}
             */

            let EditSubscriptionProcessor = {

                subscriptionFormObj: $("#subscription-edit-form"),
                isCardCodeValidated: false,
                cvvCardCode: "payment_cc_cid_new",
                reSubscribeChk: "re-subscribe-chk",
                paymentFormInput: $(".payment-form-input"),
                reSubscribeBtn: $("#re-subscription"),
                recurringStartDate: $("#start_date"),
                recurringEndDate: $("#expire_date"),
                popupModel: null,
                adminCardModel: null,
                isAvsCvvZipEnabled: null,
                avsCvvValidationActionUrl: null,

                /**
                 * init
                 *
                 * @param $
                 * @private
                 */
                _init: function ($) {
                    this.popupModel = modal;
                    this.adminCustomerCardModel = addCustomerCardModel;
                    this.subscriptionFormObj = $("#subscription-edit-form");
                    this.isAvsCvvZipEnabled = customData.isAvsCvvZipEnabled;
                    this.avsCvvValidationActionUrl = customData.validateAvsCvvActionUrl;
                    /**
                     * Edit Subscriptions
                     */
                    EditSubscriptionProcessor.renderCardCodeValidate($);
                    EditSubscriptionProcessor.renderReSubscription($);

                },
                /**
                 *
                 * @param $
                 */
                renderReSubscription: function ($) {

                    /**
                     * Resubscribe CheckBox
                     */
                    let reSubScribeChkBox = $("#" + EditSubscriptionProcessor.reSubscribeChk);
                    let subscriptionRecurringForm = EditSubscriptionProcessor.subscriptionFormObj;
                    let recurringFormArray = subscriptionRecurringForm.serializeArray();
                    let endRecurringDate = EditSubscriptionProcessor.recurringEndDate;
                    let startRecurringDate = EditSubscriptionProcessor.recurringStartDate;

                    /**
                     * Resubscription Check box
                     */
                    reSubScribeChkBox.bind("click keypress", function (evt) {
                        if (reSubScribeChkBox.is(":checked")) {
                            endRecurringDate.removeClass("error");
                            $(".payment-form-input").prop("disabled", false);
                        } else {
                            $(".payment-form-input").prop("disabled", true);

                        }
                    });

                    /**
                     * Resubscribe Button
                     */
                    EditSubscriptionProcessor.reSubscribeBtn.bind("click keypress", function (evt) {

                        let today = new Date();
                        let endDate = new Date(endRecurringDate.val());

                        if (today > endDate) {
                            endRecurringDate.addClass("error");
                            alert($.mage.__("Expiry date is not valid."));
                            return false;
                        }

                        EditSubscriptionProcessor.subscriptionFormObj.submit();
                    });

                },


                /**
                 * is Card Code Validated
                 *
                 * @param $
                 * @param customerData
                 * @returns {boolean}
                 */
                renderCardCodeValidate: function ($) {

                    /** is Card CodeValidated **/
                    EditSubscriptionProcessor.isCardCodeValidated = true;

                    /**
                     * Bind Event on Click or KeyPress
                     */
                    EditSubscriptionProcessor.subscriptionFormObj.bind("click keypress paste", function (evt) {
                        let targetId = evt.target.id;
                        let evtTarget = document.getElementById(targetId);
                        let targetTxt = $(evtTarget);

                        /** Card Code form Fields **/
                        if (targetId === EditSubscriptionProcessor.cvvCardCode) {
                            let cvvLen = 4;
                            if (targetTxt.val().length >= cvvLen) {
                                EditSubscriptionProcessor.isCardCodeValidated = false;
                                return false;
                            }
                        }


                    });

                    return EditSubscriptionProcessor.isCardCodeValidated;
                },

                /**
                 * Validate Avs Cvv and Submit
                 *
                 * @param $
                 * @param customData
                 * @returns {boolean}
                 */
                validateAvsCvvAndSubmit: function ($, customerData) {

                    EditSubscriptionProcessor.isAvsCvvZipEnabled = customerData.isAvsCvvEnabled;
                    EditSubscriptionProcessor.avsCvvValidationActionUrl = customerData.validateAvsCvvActionUrl;

                    /** create
                     * order form **/
                    let subscriptionFormObj = EditSubscriptionProcessor.subscriptionFormObj;

                    if (EditSubscriptionProcessor.isAvsCvvZipEnabled === "1") {

                        EBizChargeCardValidator.validateAvsCvvActionUrl = EditSubscriptionProcessor.avsCvvValidationActionUrl;
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
                                class: 'action- scalable save primary avs-response-save-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
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


                }

            }


            $(document).ready(function () {

                /**
                 * init the Edit Subscription Processor
                 */
                EditSubscriptionProcessor._init($);

                /** checking link **/
                $(document).click(function (evt) {

                    if (evt.target.id == 'ebzc_another_billing_address') {

                        if ($('#ebzc_another_billing_address').prop("checked") === true) {
                            $('#select-another-billing-address').show();
                        } else {
                            $('#select-another-billing-address').hide();
                        }
                    }

                    if (evt.target.id === 'ebzc_another_shipping_address') {

                        if ($('#ebzc_another_shipping_address').prop("checked") === true) {
                            $('#select-another-shipping-address').show();
                        } else {
                            $('#select-another-shipping-address').hide();
                        }
                    }

                    let changeStatusBtn = $('#sub_btn');

                    if (evt.target.id === 'add_new_payment_metod') {

                        if ($('#add_new_payment_metod').prop("checked") === true) {
                            $('#payment-methods-panel').hide();
                            $('#payment-methods-panel').show();
                            changeStatusBtn.prop("disabled", true);

                        } else {
                            $('#payment-methods-panel').hide();
                            $('#payment-methods-panel').hide();
                            changeStatusBtn.prop("disabled", false);

                        }
                    }
                });

            });

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
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    },
                    {
                        text: $.mage.__('Yes, Update'),
                        class: 'btn-background-del',
                        click: function () {
                            this.closeModal();
                            $('body').trigger('processStart');
                            $('#subscription-edit-form').submit();
                        }
                    }
                ]
            };

            /**
             *
             * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
             */
            var optionsResubscribe = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                title: 'Re Subscribe your subscription dialog box',
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    },
                    {
                        text: $.mage.__('Yes, Re-Subscribe'),
                        class: 'btn-background-del',
                        click: function () {
                            this.closeModal();
                            $('body').trigger('processStart');
                            $('#subscription-edit-form').submit();
                        }
                    }
                ]
            };


            /**
             * Options Confirm Del
             *
             * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
             */
            var optionsConfirmDel = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                title: 'Unsubscribe',
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    },
                    {
                        text: $.mage.__('Yes, Unsubscribe'),
                        class: 'btn-background-del',
                        click: function (evt) {

                            let recStatus = $("#sub_btn").prop("title");

                            if (recStatus === "Re-Subscribe") {
                                $(".action_status").val(0);
                            }

                            /**
                             * Submit Button
                             */
                            $('#cancel-subscription').submit();
                        }
                    }
                ]
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
                title: 'Subscription Suspension Action',
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        class: 'btn-background-cancel',
                        click: function () {
                            this.closeModal();
                        }
                    },
                    {
                        text: $.mage.__('Yes, ' + str),
                        class: 'btn-background-del',
                        click: function (evt) {

                            let recStatus = $("#sub_btn").prop("title").replace(/\s/g, '');

                            if (recStatus === "Resubscribe") {
                                $(".action_status").val(0);
                            }
                            $('#suspend-subscription').submit();
                        }
                    }
                ]
            };
            /**
             * Options Rec Alert
             *
             * @type {{buttons: [{text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
             */
            var optionsRecAlert = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                title: 'Action',
                buttons: [{
                    text: $.mage.__('Ok'),
                    class: 'btn-background-cancel',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            /**
             * options Address
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
                    click: function () {
                        let selfModel = this;
                        if (!$('#recurringAddressForm').valid()) {
                            return;
                        }

                        /**
                         * Customer id Address
                         * @type {jQuery|string|undefined}
                         */
                        var customerIdAddress = $('#customerIdAddress').val();

                        $.ajax({
                            type: "POST",
                            url: customData.addressActionUrl,
                            dataType: "json",
                            data: $("#recurringAddressForm").serialize(),//only input
                            showLoader: true,
                            success: function (response) {

                                if (response.error === false) {
                                    $('.btn-background-cancel').click();

                                    $.ajax({
                                        type: "POST",
                                        url: customData.loadCustomerAddressActionUrl,
                                        dataType: "json",
                                        data: {
                                            customer_id: customerIdAddress
                                        },//only input
                                        showLoader: true,
                                        success: function (data) {
                                           // $('#addresBill').empty();
                                           // $('#addressShip').empty();
                                            $(".modal-popup").hide();
                                            $(".modals-overlay").hide();
                                            selfModel.closeModal();

                                            if (data.html_data !== undefined && data.html_data) {
                                                $('#addresBill').append(data.html_data);
                                                $('#addressShip').append(data.html_data);

                                            } else {
                                                $('#addresBill').append('<option value="">No Address found</option>');
                                                $('#addressShip').append('<option value="">No Address found</option>');
                                            }
                                        }
                                    });
                                }else{
                                    selfModel.closeModal();
                                }
                            }
                        });
                    }
                }]
            };

            let saveCardPaymentMethod = $('#selectdivPayment');
            let saveAchPaymentMethod = $('#selectdivPaymentAch');
            let paymentMethodName = $('#payment_method_name');
            let replaceNewPaymentMethod = $('#add_new_payment_metod');
            let currentPaymentMethodName = $('#current_payment_method_name');
            let currentPaymentMethodId = $("#eb_rec_method_id");
            let isAddNewPaymentMethodChecked = replaceNewPaymentMethod.is(':checked');

            /**
             *
             * check if replace new Payment Method
             */
            replaceNewPaymentMethod.on('click keypress', function (evt) {
                if (!$(this).is(":checked")) {
                    paymentMethodName.val(currentPaymentMethodName.val());
                    currPaymentMethodNameOption.val(currentPaymentMethodName.val());
                    currPaymentMethodIdOption.val(currentPaymentMethodId.val());
                }else{
                    currPaymentMethodIdOption.val("");
                    currPaymentMethodNameOption.val("");
                }
            });

            /**
             *
             * Replace new Ach payment Method Name
             */
            saveCardPaymentMethod.on('change', function (evt) {

                let pm = this.value.length > 0 ? this.value.split("|"): [];
                let pmId = "";
                let pmName = "";

                if(pm !== ""){
                    pmId = pm[0];
                    pmName = pm[1];
                }
                if (this.value.length > 0) {
                    paymentMethodName.val(this.value);
                    currPaymentMethodIdOption.val(pmId);
                    currPaymentMethodNameOption.val(pmName);

                } else {
                    paymentMethodName.val(currentPaymentMethodName.val());
                    currPaymentMethodNameOption.val(currentPaymentMethodName.val());
                    currPaymentMethodIdOption.val(currentPaymentMethodId.val());
                }

            });

            /**
             * Replace New Payment method
             */
            saveAchPaymentMethod.on('change', function (evt) {
                let pm = this.value.length > 0 ? this.value.split("|"): [];
                let pmId = "";
                let pmName = "";

                if(pm !== ""){
                    pmId = pm[0];
                    pmName = pm[1];
                }
                if (this.value.length > 0) {
                    paymentMethodName.val(this.value);
                    currPaymentMethodIdOption.val(pmId);
                    currPaymentMethodNameOption.val(pmName);
                } else {
                    paymentMethodName.val(currentPaymentMethodName.val());
                    currPaymentMethodNameOption.val(currentPaymentMethodName.val());
                    currPaymentMethodIdOption.val(currentPaymentMethodId.val());
                }

            });


            /**
             * Clicking on Submit button actions
             */

            $(".sub_btn").click(function (evt) {

                /** STR for update Recurring Text **/

                var str = $('#sub_btn').text();

                str = str.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                    return letter.toLowerCase();
                });

                $('#myModelConfirm').html('<div>Are you sure you want to' + str + ' this subscription?</div>');
                $('#myModelConfirm').modal('openModal');

            });

            $("#re-subscription").bind("click keypress", function (evt) {
                evt.preventDefault();
                $('#myModelResubscribe').html('<div>Are you sure you want to re subscribe your subscription?</div>');
                $('#myModelResubscribe').modal('openModal');

            });

            $(".del_btn").click(function () {
                $('#myModelConfirmDel').html('<div>Are you sure you want to suspend this customer subscription?</div>');
                $('#myModelConfirmDel').modal('openModal');
            });

            $("#address_btn").click(function () {
                var customerId = $('#customerIdAddress').val();
                if (customerId != '') {
                    $("#recurringAddressForm").trigger("reset");
                    $('#optionsAddress').modal('openModal');
                }
            });

            var popup = modal(options, $('#myModel'));
            var popupConfirmDel = modal(optionsConfirmDel, $('#myModelConfirmDel'));
            var popupConfirm = modal(optionsConfirm, $('#myModelConfirm'));
            var popupAddress = modal(optionsAddress, $('#optionsAddress'));
            var popupRecCheck = modal(optionsRecAlert, $('#myRecAlert'));
            var popupResubscribe = modal(optionsResubscribe, $('#myModelResubscribe'));


            //For new payment method
            $(document).ready(function () {

                $('#selectdivPayment').change(function () {
                    $('#payment_method_name').val($('#selectdivPayment option:selected').text());
                });

            });

            if ($('#rec_indefinitely').prop("checked") === true) {
                $('#expire_date').prop("disabled", true)
                    .closest('div').css('display', 'none');
                $(".recurring-indefinitely").hide();
            }


            /** updating Recurrings **/
            /**
             * Update Recurrings button
             */
            $('.save_btn').click(function (e) {

                let addNewPaymentMethodChecked = $('#add_new_payment_metod');


                if (!addNewPaymentMethodChecked.prop("checked")) {
                    $('#myModel').html('<div>Are you sure you want to update your subscription?</div>');
                    $('#myModel').modal('openModal');
                    return true;
                }


                $('#myModel').html('<div>Are you sure you want to update your subscription?</div>');
                $('#myModel').modal('openModal');

                return true;

            }); // end of save recurring button

            let minDate = new Date();
            minDate.setDate(minDate.getDate() + 1);

            $('#dates').dateRange(
                {
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
             * Recurring Indefinitely
             */

            $('#rec_indefinitely').click(function () {

                if ($(this).prop("checked") === true) {
                    $(".recurring-indefinitely").hide();
                    $("#rec_indefinitely").val(1);
                    $('#expire_date').prop("disabled", true)
                        .closest('div').css('display', 'none')
                    ;
                } else if ($(this).prop("checked") === false) {
                    $(".recurring-indefinitely").show();
                    $("#rec_indefinitely").val(0);
                    $('#expire_date').prop("disabled", false)
                        .closest('div').css('display', 'block');
                }
            });


            var selectdPaymentCc = $("#selectdivPayment").val();
            var selectdPaymentAch = $("#selectdivPaymentAch").val();


            if (selectdPaymentCc) {

                $("#ebzc_option").trigger('click');

                $('.my_card_new').attr('disabled', true);
                $('.my_card_saved').attr('disabled', false);
                $('.my_bank_new').attr('disabled', true);
                $('.my_bank_saved').attr('disabled', true);
                $('.selectdivPaymentAch').attr('disabled', true);

            } else if (selectdPaymentAch) {

                $("#ebizs_option_ach").trigger('click');
                $("#bank_saved").trigger('click');

                $('.my_card_new').attr('disabled', true);
                $('.my_card_saved').attr('disabled', true);
                $('.my_bank_new').attr('disabled', true);
                $('.my_bank_saved').attr('disabled', false);
                $('.selectdivPayment').attr('disabled', true);

            } else {

                $("#ebizs_option").trigger('click');
                $("#card_new").trigger('click');

                $('.my_card_new').attr('disabled', false);
                $('.my_card_saved').attr('disabled', true);
                $('.my_bank_new').attr('disabled', true);
                $('.my_bank_saved').attr('disabled', true);
                $('.selectdivPayment').attr('disabled', true);
                $('.selectdivPaymentAch').attr('disabled', true);
            }

            /**
             * Click ready Change
             */
            $('.ebizs_option').on('click ready change', function () {

                if (this.value === 'credit_card') {
                    $('#card_option').val('card_new');
                    $('#add-new').show();
                    $('#add-new-ach').hide();
                    $('.card_option').trigger('click');
                    $('#ebzc_option').val('credit_card');

                } else if (this.value === 'ACH') {
                    $('#bank_option').val('bank_new');
                    $("#bank_new").trigger('click');
                    $('.bank_option').trigger('click');

                    $('#add-new').hide();
                    $('#add-new-ach').show();
                    $('#ebzc_option').val('ACH');
                }
            });

            $('.pay-select').on('click', function () {
                let selectedTab = $(this).attr("for");
                let currentOptionType = $("#ebzc_option_type");

                $('#ebzc_option').val($(this).attr("for"));

                if (selectedTab === "credit_card") {
                    jQuery("#card_new").trigger("click");
                }


                $('#' + $(this).data("tab") + ' fieldset').prop('disabled', false);
                $('#' + $(this).data("disable") + ' fieldset').prop('disabled', true);
            })


            $("#tab_pay_by_bank fieldset").prop('disabled', true);



            /**
             *
             * Click ready Change events
             */
            $('.bank_option').on('click ready change', function () {

                if (this.value === 'bank_new') {

                    $('.my_bank_new').show();
                    $('.my_bank_saved').hide();
                    $(".my_bank_new").attr('disabled', false);
                    $(".my_bank_saved").attr('disabled', true);

                } else if (this.value === 'bank_saved') {

                    $('.my_bank_new').hide();
                    $('.my_bank_saved').show();
                    $(".my_bank_new").attr('disabled', true);
                    $(".my_bank_saved").attr('disabled', false);
                }
            });

            $('#country').on('click ready change', function () {
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


            $(document).ready(function () {

                let dataForm = $('#subscription-edit-form');
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
                    }


                }).find('input:text').attr('autocomplete', 'off');

                /**
                 * Button Save subscriptions
                 */
                $('button#save-subscription').bind("click keypress", function (evt) {

                    dataForm.validation('isValid');

                    if ($('#subscription-edit-form').valid()) {

                        let isAddNewCardSelected = false;
                        let paymentOptionType = $(document.getElementById("credit_card_type"));
                        let isNewPaymentMethod = $(document.getElementById("add_new_payment_metod"));

                        if (isNewPaymentMethod.is(":checked")) {

                            if (paymentOptionType.val() !== "new") {
                                submitHandler();

                            } else {

                                /**
                                 * Initialize Precessor
                                 */
                                EditSubscriptionProcessor.validateAvsCvvAndSubmit($, customData);

                            }
                        } else {

                            /**
                             * Submit form
                             */
                            submitHandler();
                        }
                    }
                });

                /**
                 *
                 * @returns {boolean}
                 */
                function submitHandler() {

                    let popModel = $(document.getElementById("myModel"));

                    let addNewPaymentMethodChecked = $(document.getElementById("add_new_payment_metod"));
                    let newCardSelected = $(document.getElementById('card_new')).prop("checked");
                    let ebizOption = $(document.getElementById('ebzc_option')).val();
                    let savedCardSelected = $(document.getElementById('card_saved')).prop("checked");


                    let customer_id = $(document.getElementById("selectdivCustomer")).val();
                    let product_id = $(document.getElementById("selectdivProduct")).val();
                    let start_date = $(document.getElementById("start_date")).val();
                    let recurringIndefinit = $(document.getElementById("rec_indefinitely")).val();
                    let end_date = $(document.getElementById("expire_date")).val();
                    let rec_id = $(document.getElementById("rec_id")).val();

                    let origStartDate = $(document.getElementById("start_date")).prop("defaultValue");
                    let origEndDate = $(document.getElementById("expire_date")).prop("defaultValue");


                    if (origStartDate !== start_date || origEndDate !== end_date) {

                        /**
                         * Ajax Post
                         */
                        $.ajax({
                            method: "POST",
                            url: customData.recurringExistUrl,
                            data: {
                                customer_id: customer_id,
                                product_id: product_id,
                                start_date: start_date,
                                end_date: end_date,
                                rec_id: rec_id,
                                rec_indefinite: recurringIndefinit
                            },
                            dataType: "json",
                            showLoader: true,
                            success: function (data) {
                                if (data.status === 'P') {
                                    popModel.html('<div>Do you want to update the changes you made to this subscription?</div>');
                                    popModel.modal('openModal');
                                } else {
                                    popModel.html("<div>" + data.message + "</div>");
                                    popModel.modal('openModal');
                                }
                            },
                            error: function (result) {
                                console.log(result);
                                popModel.html("<div>Error occurred during updating subscription.</div>");
                                popModel.modal('openModal');
                            }
                        });

                    } else {
                        popModel.html('<div>Do you want to update the changes you made to this subscription?</div>');
                        popModel.modal('openModal');
                    }


                    popModel.html('<div>Are you sure you want to update your subscription?</div>');
                    popModel.modal('openModal');

                    return true;

                }

            });

            let currentPaymentOption = $(document.getElementById("ebzc_option"));
            let currentPaymentOptionType = $(document.getElementById("ebzc_option_type"));
            let currPaymentMethodIdOption = $(document.getElementById("curr_payment_method_id"));
            let currPaymentMethodNameOption = $(document.getElementById("curr_payment_method_name"));

            /***
             * changing payment tabs
             */
            $(document.getElementById("payment-methods-panel")).bind("click keypress", function (evt) {

                let targetId = evt.target.id;

                // console.log(targetId);
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

                if (targetId === 'card_new') {

                    $('.my_card_new').show();
                    $('.my_card_saved').hide();
                    currentPaymentOption.val("new");
                    currPaymentMethodIdOption.val("");
                    currPaymentMethodNameOption.val("");

                    $('.my_card_saved').attr('disabled', true);
                    $('.my_card_new').attr('disabled', false);

                } else if (targetId === 'card_saved') {
                    $('.my_card_new').hide();
                    $('.my_card_saved').show();
                    currentPaymentOption.val("saved");

                    $('.my_card_new').attr('disabled', true);
                    $('.my_card_saved').attr('disabled', false);
                }

            });


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
        }
    }
);

/**
 * unsub
 */
function unsub() {
    document.getElementById('unsub').submit();
}

/**
 * goBack
 */
function goBack() {
    window.history.back();
}
