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
        'mage/translate',
        'Magento_Sales/order/create/scripts',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator/credit-card-type',
        'Ebizcharge_Ebizcharge/js/addCustomerCard',
        'Magento_Ui/js/modal/modal',
        'Magento_Sales/order/create/form'
    ],
    function ($, jui, _, translate, SalesOrder, CcType, addCustomerModel, modal, orderForm) {

        "use_strict";

        /**
         * Ebizcharge Payment Method
         * @type {string}
         */
        let paymentMethodCode = 'ebizcharge_ebizcharge';
        let Order = window.AdminOrder;
        let Payment = window.payment;
        let beforeSubmitOrderEvent = jQuery("#edit_form").trigger('beforeSubmitOrder');
        beforeSubmitOrderEvent.result = false;

        /**
         *
         * @type {{paymentMethodCode: string, calculateSurchargeAjaxUrl: null, _init: EbizPaymentMethod._init, payByCardRadio: (*|jQuery|HTMLElement), restorePaymentMethodParams: EbizPaymentMethod.restorePaymentMethodParams, savedCardField: null, cancelOrderButton: (*|jQuery|HTMLElement), creditCardType: (*|jQuery|HTMLElement), payByMethodsRadio: null, getDefaultPaymentPanel: (function(*, null=): null), calculateSurcharge: EbizPaymentMethod.calculateSurcharge, renderInputLimit: EbizPaymentMethod.renderInputLimit, savedCards: null, tabTitle: null, subPanels: null, isAvsCvvZipEnabled: (*|jQuery|HTMLElement), editForm: null, payByAchPanel: (*|jQuery|HTMLElement), payLaterBlock: null, payLaterRadio: (*|jQuery|HTMLElement), getSelectedCardData: (function(*, number=): {paymentMethod: null}), paymentTypeLabels: string[], adminOrder, order: null, selectedPaymentOption: null, addNewCardObj: null, selectPaymentPanel: EbizPaymentMethod.selectPaymentPanel, payByCardRadioBtn: null, cvvSaved: (*|jQuery|HTMLElement), paymentMethodOption: string, payByAchTitle: (*|jQuery|HTMLElement), paymentSubPanelOptions: (*|jQuery|HTMLElement), selectSubPanel: EbizPaymentMethod.selectSubPanel, saveCardsFields: string[], payByAchRadio: (*|jQuery|HTMLElement), creditCardNumber: (*|jQuery|HTMLElement), surchargeEvents: EbizPaymentMethod.surchargeEvents, paymentMethod: (*|jQuery|HTMLElement), payByCardPanel: (*|jQuery|HTMLElement), avsZipFields: null, isEbizCardMethod: (function(): boolean), disableDefaultAllFields: (function(*, *): boolean), paymentMethodTabs: {"ebiz-tab-pay-by-ach": {defaultOption: string, id: string, title: string, panel: string}, "ebiz-tab-pay-by-card": {defaultOption: string, id: string, title: string, panel: string}, "ebiz-tab-pay-by-later": {defaultOption: string, id: string, title: string, panel: string}}, selectCreditCardType: EbizPaymentMethod.selectCreditCardType, validateAvsCvvUrl: (*|jQuery|HTMLElement), reloadTotals: EbizPaymentMethod.reloadTotals, payByCardTitle: (*|jQuery|HTMLElement), mageError: null, addNewCardRadio: boolean, currentSelectedPanel: null, renderDataInSavedCardFields: EbizPaymentMethod.renderDataInSavedCardFields, renderPopupModel: (function(*, *, *): {buttons, responsive: boolean, innerScroll: boolean, modalClass, type: string, title}), currentSelectedTab: null, payLaterPanel: (*|jQuery|HTMLElement), paymentMethodId: string, createOrderButton: (*|jQuery|HTMLElement), createOrderForm: (*|jQuery|HTMLElement), updateExistingCardRadio: boolean, payLaterTitle: (*|jQuery|HTMLElement), tabTitles: string[], getDefaultPaymentTab: (function(*, null=): null), renderEBizChargePaymentMethod: EbizPaymentMethod.renderEBizChargePaymentMethod, validateAvsCvvZipCodeAction: ((function(*, *, *): boolean)|*), selectSavedCardRadio: boolean, currentSelectedPaymentOptionType: (*|jQuery|HTMLElement), selectPaymentTab: EbizPaymentMethod.selectPaymentTab, updateSavedCard: null, customerInternalId: null, paymentMethodPanelBlocks: {"add-new-ach-panel": {id: string, label: string}, "update-saved-card-panel": {id: string, label: string}, "saved-card-panel": {id: string, label: string}, "saved-ach-panel": {id: string, label: string}, "pay-later-panel": {id: string, label: string}, "add-new-card-panel": {id: string, label: string}}, currentSelectedTabOption: (*|jQuery|HTMLElement), surchargeEnabled: boolean, adminSection: (*|jQuery|HTMLElement), tabLabelClass: null, currentSelectedSubPanel: null, paymentBlocks: null, renderPaymentMethodTabs: EbizPaymentMethod.renderPaymentMethodTabs, currentSelectedTabOptionPanel: (*|jQuery|HTMLElement), renderSuPanels: EbizPaymentMethod.renderSuPanels, paymentMethodForm: (*|jQuery|HTMLElement)}}
         */
        let EbizPaymentMethod = {
            order: null,
            selectedPaymentOption: null,
            paymentMethodCode: paymentMethodCode,
            paymentMethod: $(document.getElementById('p_method_' + paymentMethodCode)),
            paymentMethodForm: $(document.getElementById("payment_form_" + paymentMethodCode)),
            validateAvsCvvUrl: $(document.getElementById("validate_avs_url")),
            adminSection: $(document.getElementsByClassName('admin__page-section-item')),
            paymentMethodId: 'p_method_' + paymentMethodCode,
            editForm: null,
            subPanels: null,
            currentSelectedTab: null,
            currentSelectedPanel: null,
            currentSelectedSubPanel: null,
            popUpButtons: [],
            isAjax: $(document.getElementById("is_ajax")),
            isAuthenticated: $(document.getElementById("is_authenticated")),
            paymentMethodOption: 'admin__field-option',
            createOrderButton: $(document.getElementById("submit_order_top_button")),
            createOrderClassButton: $(document.getElementsByClassName("action-default")),
            cancelOrderButton: $(document.getElementById("reset_order_top_button")),
            createOrderForm: $(document.getElementById("edit_form")),
            isAvsCvvZipEnabled: $(document.getElementById("is_avs_cvv_enabled")),
            currentSelectedPaymentOptionType: $(document.getElementById("current-selected-tab-option")),
            paymentSubPanelOptions: $(document.getElementsByClassName("payment-option-type")),
            paymentMethodTabs: {
                'ebiz-tab-pay-by-card': {
                    id: 'ebiz-tab-pay-by-card',
                    title: 'ebiz-tab-pay-by-card-title',
                    panel: 'ebiz-tab-pay-by-card-panel',
                    defaultOption: 'add-new-card'
                },
                'ebiz-tab-pay-by-ach': {
                    id: 'ebiz-tab-pay-by-ach',
                    title: 'ebiz-tab-pay-by-ach-title',
                    panel: 'ebiz-tab-pay-by-ach-panel',
                    defaultOption: 'add-new-ach'
                },
                'ebiz-tab-pay-by-later': {
                    id: 'ebiz-tab-pay-by-later',
                    title: 'ebiz-tab-pay-by-later-title',
                    panel: 'ebiz-tab-pay-by-later-panel',
                    defaultOption: 'pay-later'
                    //   panel: 'pay-later-panel'
                }
            },
            tabTitles: [
                'ebiz-tab-pay-by-card',
                'ebiz-tab-pay-by-ach',
                'ebiz-tab-pay-by-later'
            ],
            saveCardsFields: [
                '_cc_token',
                '_cc_cid',
                '_ebzc_method_ach'
            ],
            paymentTypeLabels: [
                'saved-card',
                'update-saved-card',
                'add-new-card',
                'saved-ach',
                'add-new-ach',
                'pay-later'
            ],
            paymentMethodPanelBlocks: {
                'saved-card-panel': {
                    'id': 'saved-card-panel',
                    'label': 'saved-card'
                },
                'update-saved-card-panel': {
                    'id': 'update-saved-card-panel',
                    'label': 'update-saved-card'
                },
                'add-new-card-panel': {
                    'id': 'add-new-card-panel',
                    'label': 'add-new-card'
                },
                'saved-ach-panel': {
                    'id': 'saved-ach-panel',
                    'label': 'saved-ach'
                },
                'add-new-ach-panel': {
                    'id': 'add-new-ach-panel',
                    'label': 'add-new-ach'
                },
                'pay-later-panel': {
                    'id': 'pay-later-panel',
                    'label': 'pay-later'
                }
            },
            payByCardRadio: $(document.getElementById('ebiz-tab-pay-by-card')),
            payByAchRadio: $(document.getElementById('ebiz-tab-pay-by-ach')),
            payLaterRadio: $(document.getElementById('ebiz-tab-pay-by-later')),
            payByCardPanel: $(document.getElementById('ebiz-tab-pay-by-card-panel')),
            payByAchPanel: $(document.getElementById('ebiz-tab-pay-by-ach-panel')),
            payLaterPanel: $(document.getElementById('ebiz-tab-pay-by-later-panel')),
            payByCardTitle: $(document.getElementById('ebiz-tab-pay-by-card-title')),
            payByAchTitle: $(document.getElementById('ebiz-tab-pay-by-ach-title')),
            payLaterTitle: $(document.getElementById('ebiz-tab-pay-by-later-panel')),
            creditCardNumber: $(document.getElementById(paymentMethodCode + "_cc_number_new")),
            creditCardType: $(document.getElementById(paymentMethodCode + "_cc_type_new")),
            tabTitle: null,
            cvvSaved: $(document.getElementById(paymentMethodCode + "_cc_cid")),
            currentSelectedTabOption: $(document.getElementById("current_selected_tab_option")),
            currentSelectedTabOptionPanel: $(document.getElementById("current_selected_tab_option_panel")),
            adminOrder: SalesOrder,
            mageError: null,
            tabLabelClass: null,
            paymentBlocks: null,
            payLaterBlock: null,
            addNewCardObj: null,
            savedCards: null,
            updateSavedCard: null,
            surchargeEnabled: false,
            savedCardField: null,
            payByCardRadioBtn: null,
            selectSavedCardRadio: false,
            updateExistingCardRadio: false,
            addNewCardRadio: false,
            customerInternalId: null,
            calculateSurchargeAjaxUrl: null,
            payByMethodsRadio: null,
            avsZipFields: null,
            popupModal: null,
            popUpModalId: null,
            popupTitle: null,
            avsAddressMsg: null,
            avsPostalCodeMsg: null,
            cvvCvv2Msg: null,
            cardMessageResponse: null,
            avsSaveActionButton: null,
            userAction: null,
            isValidCc: null,
            addNewCardRadio: $(document.getElementById("ebzc_option_new")),
            isAjaxInProgress: false,
            isAvsCvvCanceled: false,

            /**
             * Validate Max Length
             *
             * @param $
             */
            renderInputLimit: function ($) {
                let inputTxtBox = $("input");
                let maxLengthClass = "validate-max-length-";
                let cardCvvClass = "card-cvv";

                /**
                 * Check Input Type
                 */
                inputTxtBox.bind("input", function (evt) {
                    //  console.log(evt.target.id);
                    let elem = $(this);
                    let creditCardType = EbizPaymentMethod.creditCardType;

                    let cElem = document.getElementById(evt.target.id);
                    let classNames = elem.prop("class");
                    let classes = classNames.split(" ");

                    /**
                     * Class Names
                     */
                    $.each(classes, function (key, className) {
                        if (className.indexOf(maxLengthClass) > -1) {
                            let maxClassLimits = className.split("-");
                            let maxLimit = maxClassLimits[maxClassLimits.length - 1];
                            if (cElem.value.length > maxLimit) {
                                cElem.value = cElem.value.slice(0, maxLimit);
                            }
                        }
                    });
                });

            },

            /**
             * Payment Method Init Function
             *
             * @param $
             * @param jui
             * @param _
             * @param translate
             * @param SalesOrder
             * @param CcType
             * @param configModelData
             * @param addCustomerModel
             * @param modal
             * @private
             */
            _init: function ($, jui = null, _ = null, translate = null, SalesOrder = null, CcType = null, configModelData = null, addCustomerModel = null, modal = null) {

                let paymentMethodCode = 'ebizcharge_ebizcharge';

                this.paymentMethod = $(document.getElementById('p_method_' + paymentMethodCode));
                this.subPanels = $(document.getElementsByClassName('sub-labels-panel'));
                this.mageError = $(document.getElementsByClassName('mage-error'));
                this.tabLabelClass = $(document.getElementsByClassName('tab-label'));
                this.paymentBlocks = $(document.getElementsByClassName("payment-block"));
                this.payLaterBlock = $(document.getElementById("pay-later-panel"));
                this.tabTitle = $(document.getElementsByClassName('tab-label'));
                this.editForm = $(document.getElementById('edit_form'));
                this.addNewCardObj = $(document.getElementById("add-new-card"));
                this.savedCards = configModelData.saved_cards ? configModelData.saved_cards : null;
                this.updateSavedCard = $(document.getElementById(EbizPaymentMethod.paymentMethodCode + '_cc_token_update'));
                this.surchargeEnabled = $(document.getElementById('surcharge_enabled')).val() === '1';
                this.payByCardRadioBtn = $('#ebiz-tab-pay-by-card');
                this.savedCardField = $(document.getElementById(EbizPaymentMethod.paymentMethodCode + '_cc_token'));
                this.selectSavedCardRadio = $('#ebzc_option_saved');
                this.updateExistingCardRadio = $('#ebzc_option_update');
                this.addNewCardRadio = $('#ebzc_option_new');
                this.customerInternalId = $(document.getElementById('cust_internal_id')).val();
                this.calculateSurchargeAjaxUrl = $(document.getElementById('calculate_surcharge_url')).val();
                this.payByMethodsRadio = $(document.getElementsByClassName('ebizs_option'));
                this.avsZipFields = $(document.getElementsByClassName('ebiz-avs-zip'));
                this.isAjax = $(document.getElementById('is_ajax'));
                this.isAuthenticated = $(document.getElementById('is_authenticated'));
                this.popupModal = modal;
                this.popUpModalId = $(document.getElementById("avs-cvv-warnings-panel"));
                this.popupTitle = $.mage.__("Security mismatch dialog box...");
                this.avsAddressMsg = $(document.getElementById("avs-address-msg"));
                this.avsPostalCodeMsg = $(document.getElementById("avs-postal-code-msg"));
                this.cvvCvv2Msg = $(document.getElementById("cvv-cvv2-msg"));
                this.cardMessageResponse = $(document.getElementById("card-message-response"));
                this.avsSaveActionButton = $(document.getElementsByClassName("avs-response-save-button"));
                this.userAction = $(document.getElementById("user_action"));
                this.isValidCc = $(document.getElementById("is_valid_cc"));
                this.isValidCcDiv = $(document.getElementById("is_valid_cc_div"));
                this.addNewCardRadio = $(document.getElementById("ebzc_option_new"));
                this.isAjaxInProgress = false;
                this.isAvsCvvCanceled = false;

                // this.isValidCcDiv.hide();

                /**
                 * Hiding by default
                 */
                EbizPaymentMethod.paymentMethodForm.hide();

                /** By default, show the Payment Method form **/
                if (EbizPaymentMethod.paymentMethod.is(':checked')) {
                    EbizPaymentMethod.paymentMethodForm.show();
                }

                /** Disable all sub panel options fields **/
                EbizPaymentMethod.paymentSubPanelOptions.attr("disabled", true);

                if (configModelData.payment_params !== null) {
                    EbizPaymentMethod.currentSelectedTabOptionPanel.val(configModelData.payment_params.current_selected_tab);
                }

                /** Limit input type **/
                EbizPaymentMethod.renderInputLimit($);

                /** Deal with Credit Card Types **/
                EbizPaymentMethod.selectCreditCardType($, _, configModelData);

                /** Render validation of CVV Zip code Options **/
                //  EbizPaymentMethod.validateAvsCvvZipCodeAction($, addCustomerModel, configModelData);

                /** Render complete EBizCharge Payment Method Options
                 * Payment Options
                 * **/
                EbizPaymentMethod.renderEBizChargePaymentMethod($, _, configModelData);

                //EbizPaymentMethod.addNewCardObj.trigger("click");

                /**
                 * Restore Payment Method
                 */
                EbizPaymentMethod.restorePaymentMethodParams($, jui, _, translate, SalesOrder, CcType, configModelData, modal);

                /**
                 * Render fields values of selected saved card in "Update existing card"
                 */
                EbizPaymentMethod.renderDataInSavedCardFields($);
                EbizPaymentMethod.savedCardField.trigger('change');
                EbizPaymentMethod.updateSavedCard.trigger('change');

                /**
                 * Check If Pay By New Card is selected
                 */
                //EbizPaymentMethod.checkNewCardSurcharge();
                EbizPaymentMethod.surchargeEvents();


                /**
                 * render submit button
                 */
                EbizPaymentMethod.renderSubmitButton($, addCustomerModel, configModelData);


                /**
                 * hiding popup
                 */
                EbizPaymentMethod.hidePopupModal($)
                if(this.paymentMethod.is(":checked")){
                    let paymentType = document.getElementById("payment_option_type");
                    if(paymentType && paymentType.value === "2"){
                        /** here we need to select the Sub Panels **/
                        $(document.getElementById("pay-later")).trigger("click");
                    }
                }
                this.paymentMethod.on("click keypress", function(evt){
                    let paymentType = document.getElementById("payment_option_type");
                    if(paymentType && paymentType.value === "2"){
                        /** here we need to select the Sub Panels **/
                        $(document.getElementById("pay-later")).trigger("click");
                    }

                });


            },
            /**
             * Render Submit Button
             *
             * @param $
             * @param addCustomerModel
             * @param configModelData
             */
            renderSubmitButton: function ($, addCustomerModel = null, configModelData = null) {
                EbizPaymentMethod.createOrderButton.on("click keypress", function (evt) {
                    if (EbizPaymentMethod.addNewCardRadio.is(":checked")) {
                        EbizPaymentMethod.validateAvsCvvZipCodeAction($, addCustomerModel, configModelData);
                    }
                });
                $(document).on("click keypress", ".save", function (evt) {
                    if (EbizPaymentMethod.addNewCardRadio.is(":checked")) {
                        EbizPaymentMethod.validateAvsCvvZipCodeAction($, addCustomerModel, configModelData);
                    }
                });

            },


            /**
             * Select Ebizcharge Method
             *
             * @param $
             * @param _
             * @param configModelData
             */
            renderEBizChargePaymentMethod: function ($, _, configModelData = null) {
                EbizPaymentMethod.mageError.hide();

                /** disable default Payment method Sub Panels */
                EbizPaymentMethod.disableDefaultAllFields($, _);

                /** render Default Payment Methods **/
                EbizPaymentMethod.renderPaymentMethodTabs($, _, configModelData)

                /** render sub Panels **/
                EbizPaymentMethod.renderSuPanels($, _, configModelData);
            },

            /**
             * Get Default Payment TabId
             *
             * @param $
             * @param configModelData
             * @returns {string}
             */
            getDefaultPaymentTab: function ($, configModelData = null) {
                EbizPaymentMethod.currentSelectedTab = null;
                $.each(EbizPaymentMethod.paymentMethodTabs, function (index, tab) {
                    if (document.getElementById(index) !== null) {
                        EbizPaymentMethod.currentSelectedTab = tab;
                        return false;
                    }
                });
                return EbizPaymentMethod.currentSelectedTab;
            },

            /**
             * Render Tab Payment Options
             *
             * @param $
             * @param _
             * @param configModelData
             */
            renderPaymentMethodTabs: function ($, _, configModelData = null) {

                EbizPaymentMethod.getDefaultPaymentTab($, configModelData);
                EbizPaymentMethod.getDefaultPaymentPanel($, configModelData);

                /**
                 * Default Selection of the Payment TAB
                 */
                EbizPaymentMethod.selectPaymentTab($, _);
                $(".order-billing-method").on("click keypress", function (evt) {
                    let targetId = evt.target.id;

                    if (targetId === "p_method_ebizcharge_ebizcharge") {
                        EbizPaymentMethod.addNewCardObj.trigger("click");
                        EbizPaymentMethod.payByCardRadioBtn.prop('checked', 'checked');
                    }

                    $.each(EbizPaymentMethod.paymentMethodTabs, function (pid, tab) {
                        if (pid === targetId) {
                            EbizPaymentMethod.currentSelectedTab = tab;
                            EbizPaymentMethod.currentSelectedPanel = tab;
                            EbizPaymentMethod.selectPaymentTab($, _);
                            EbizPaymentMethod.selectPaymentPanel($, _);

                            return false;
                        }
                    });
                });
            },

            /**
             * Select Payment Tab
             *
             * @param $
             * @param _
             */
            selectPaymentTab: function ($, _) {
                let defaultTabId = Object.values(EbizPaymentMethod.currentSelectedTab)[0];

                EbizPaymentMethod.tabTitle.removeClass("red-border");
                $.each(EbizPaymentMethod.paymentMethodTabs, function (pid, tab) {
                    if (pid === defaultTabId) {
                        $(document.getElementById(pid)).prop('checked', 'checked');
                        $(document.getElementById(tab.title)).addClass("red-border");
                    } else {
                        $(document.getElementById(tab.title)).removeClass("red-border");
                    }
                });
            },

            /**
             * Select Credit Card Type
             *
             * @param $
             * @param _
             * @param configModelData
             */
            selectCreditCardType: function ($, _, configModelData = null) {

                /**
                 * Payment
                 */
                let paymentMethodCode = configModelData.payment_method_id;

                EbizPaymentMethod.creditCardNumber = $(document.getElementById(paymentMethodCode + "_cc_number_new"));
                EbizPaymentMethod.creditCardType = $(document.getElementById(paymentMethodCode + "_cc_type_new"));

                EbizPaymentMethod.creditCardNumber.bind("input", function (evt) {
                    /**
                     * Credit Card Value
                     */
                    let creditCardValue = evt.target.value;
                    let selCardType = '';
                    /**
                     * if Credit Card Value is Length
                     */
                    if (creditCardValue.length >= 15) {
                        let creditCardNumber = evt.target.value;
                        let cardTypes = CcType.getCardTypes(creditCardNumber);


                        if (typeof cardTypes[0] !== 'undefined') {
                            let selCardType = cardTypes[0].type;
                                selCardType = selCardType.substring(0,1);

                            EbizPaymentMethod.creditCardType.val(selCardType).attr("selected", "selected");
                        }
                    } else {
                        EbizPaymentMethod.creditCardType.val('').attr("selected", "selected");
                    }
                });
            },

            /**
             * Disable all default Fields
             *
             * @param $
             * @param _
             * @returns {boolean}
             */
            disableDefaultAllFields: function ($, _) {
                EbizPaymentMethod.paymentSubPanelOptions.attr("disabled", true);
                return true;
            },

            /**
             * Get default payment panel
             *
             * @param $
             * @param configModelData
             * @returns {null}
             */
            getDefaultPaymentPanel: function ($, configModelData = null) {
                EbizPaymentMethod.currentSelectedPanel = EbizPaymentMethod.getDefaultPaymentTab($);
                return EbizPaymentMethod.currentSelectedPanel;
            },

            /**
             * Select Payment Panel
             *
             * @param $
             */
            selectPaymentPanel: function ($, _) {
                let currentSelectedTab = Object.values(EbizPaymentMethod.currentSelectedTab)[0];
                let currentSelectedPanel = Object.values(EbizPaymentMethod.currentSelectedPanel)[2];

                EbizPaymentMethod.paymentBlocks.hide();
                $(document.getElementById(currentSelectedPanel)).show();

                if (currentSelectedTab === "ebiz-tab-pay-by-later") {
                    /** here we need to select the Sub Panels **/
                    $(document.getElementById("pay-later")).trigger("click");
                }
            },

            /**
             * Render su panels
             *
             * @param $
             * @param _
             * @param configModelData
             */
            renderSuPanels: function ($, _, configModelData = null) {

                $(".sub-labels").on("click keypress", function (evt) {
                    let targetId = evt.target.id;
                    $.each(EbizPaymentMethod.paymentMethodPanelBlocks, function (id, subPanel) {
                        let selSubPanel = id.substr(0, id.indexOf("-panel"));
                        if (selSubPanel === targetId) {
                            EbizPaymentMethod.currentSelectedSubPanel = subPanel;
                            EbizPaymentMethod.selectSubPanel($, _);
                            EbizPaymentMethod.currentSelectedTabOption.val(selSubPanel);
                            return false;
                        }
                    });
                });
            },

            /**
             * Select Sub Panels
             *
             * @param $
             * @param _
             */
            selectSubPanel: function ($, _) {
                let selectedTab = EbizPaymentMethod.currentSelectedTab;
                let selectedPanel = EbizPaymentMethod.currentSelectedPanel;
                let selectedSubPanel = EbizPaymentMethod.currentSelectedSubPanel;
                let selectedSubPanelId = Object.values(selectedSubPanel)[0];
                let selectedSubPaneLabel = Object.values(selectedSubPanel)[1];

                $(document.getElementsByClassName("sub-labels-panel")).removeClass("current-selected-panel");
                $(document.getElementsByClassName("sub-labels")).removeClass("sub-labels-active");

                /**
                 * Looping through the sub Panels
                 */
                $.each(EbizPaymentMethod.paymentMethodPanelBlocks, function (id, subPanel) {
                    if (id === selectedSubPanelId) {
                        $(document.getElementById(id)).addClass("current-selected-panel");
                        $(document.getElementById(subPanel.label)).addClass("sub-labels-active");
                        /** enable all sub fields **/
                        $(document.getElementById(id)).find(":input").attr("disabled", false);
                        $(document.getElementById(id)).find("select").attr("disabled", false);
                        if (selectedSubPanelId === "pay-later-panel") {
                            $(document.getElementById("pay-later-panel")).show();
                        }
                    } else {
                        $(document.getElementById(id)).removeClass("current-selected-panel");
                        $(document.getElementById(subPanel.label)).removeClass("sub-labels-active");
                        /** disable all other fields **/
                        $(document.getElementById(id)).find(":input").attr("disabled", true);
                        $(document.getElementById(id)).find("select").attr("disabled", true);
                    }
                });
            },

            /**
             * Restore Payment Method Params
             *
             * @param $
             * @param jui
             * @param _
             * @param translate
             * @param SalesOrder
             * @param CcType
             * @param configModelData
             * @param modal
             */
            restorePaymentMethodParams: function ($, jui, _, translate = null, SalesOrder = null, CcType = null, configModelData = null, modal = null) {

                let paymentMethodPanelBlocks = EbizPaymentMethod.paymentMethodPanelBlocks;
                let prevSelectedTab = $(document.getElementById("current-selected-tab")).val();
                let prevSelectedOptionId = $(document.getElementById("current-selected-tab-option")).val();

                /** limit input type **/
                EbizPaymentMethod.renderInputLimit($);

                /**
                 * Prev selected Option Id
                 */
                if ((typeof (configModelData) !== "undefined" && configModelData !== null)) {

                    let paymentMethodOldParams = configModelData.payment_params;
                    if (typeof (paymentMethodOldParams) !== "undefined" && paymentMethodOldParams) {
                        let oldSelectedTab = paymentMethodOldParams.current_selected_tab;
                        let oldSelectedTabOption = paymentMethodOldParams.current_selected_tab_option;
                        $(document.getElementById(oldSelectedTab)).trigger("click");
                        $(document.getElementById(oldSelectedTabOption)).trigger("click");
                        EbizPaymentMethod.currentSelectedTabOption.val(oldSelectedTabOption);
                    } else {
                        EbizPaymentMethod.addNewCardObj.trigger("click");
                    }
                }
            },

            /**
             * Get selected saved card data
             *
             * @param $
             * @param selectedCardId
             * @returns {{paymentMethod: null}}
             */
            getSelectedCardData: function ($, selectedCardId = 0) {
                let cardId = selectedCardId ? selectedCardId : EbizPaymentMethod.updateSavedCard.val();
                let paymentMethod = null;
                let savedCards = EbizPaymentMethod.savedCards;
                if (savedCards) {
                    for (let i = 0; i < savedCards.length; i++) {
                        let card = savedCards[i];
                        if (typeof (card.MethodID) !== undefined && card.MethodID === cardId) {
                            paymentMethod = card;
                            break;
                        }
                    }
                }
                return {
                    'paymentMethod': paymentMethod,
                };

            },

            /**
             * Render expiry date + month, zip & street values of selected
             * saved card in "Update existing card"
             *
             * @param $
             */
            renderDataInSavedCardFields: function ($) {
                /**
                 * When Saved card change
                 */
                EbizPaymentMethod.savedCardField.on('change', function () {
                    let selectedCardId = $(this).val();
                    let selectedCard = EbizPaymentMethod.getSelectedCardData($, selectedCardId);
                    if (selectedCard !== null && selectedCard.paymentMethod !== null) {
                        let cardData = selectedCard.paymentMethod;
                        $('#saved-card-panel #cc_type').val(cardData.CardType);
                    }
                });
                /**
                 * On change event
                 */
                EbizPaymentMethod.updateSavedCard.on('change', function (evt) {
                    let selectedCardId = $(this).val();
                    let selectedCard = EbizPaymentMethod.getSelectedCardData($, selectedCardId);
                    if (selectedCard !== null && selectedCard.paymentMethod !== null) {
                        let cardData = selectedCard.paymentMethod;
                        let expiryData = cardData.CardExpiration;
                        let expiry = expiryData.split('-');
                        let month = typeof (expiry[1]) !== undefined ? expiry[1] : 0;

                        if (month < 10) {
                            month = expiry[1].substr(1);
                        }
                        $('#update-saved-card-panel #cc_type').val(cardData.CardType);
                        $('#' + EbizPaymentMethod.paymentMethodCode + '_avs_street').val(cardData.AvsStreet);
                        $('#' + EbizPaymentMethod.paymentMethodCode + '_avs_zip').val(cardData.AvsZip);
                        $('#' + EbizPaymentMethod.paymentMethodCode + '_expiry_year_update').val(expiry[0]);
                        $('#' + EbizPaymentMethod.paymentMethodCode + '_expiry_month_update').val(month);

                        EbizPaymentMethod.calculateSurcharge();
                    }
                });
            },

            /**
             * Render Popup Model
             *
             * @param $
             * @param popupModelId
             * @param optionsParams
             * @returns {{buttons: *, responsive: boolean, innerScroll: boolean, modalClass: *, type: string, title: *}}
             */
            renderPopupModel: function ($, popupModelId, optionsParams) {

                let popUpModel = $(document.getElementById(popupModelId));

                let options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    title: optionsParams.title,
                    modalClass: optionsParams.modalClass,
                    buttons: optionsParams.buttons
                }

                let popUp = modal(options, popUpModel);
                popUpModel.modal("openModal");

                return options;
            },

            /**
             * Hide Poup Modal
             * @param $
             */
            hidePopupModal :function ($) {
                let createOrderForm = EbizPaymentMethod.createOrderForm;
                $(document).on('click keypress', '.action-close', function (evt) {
                    let cButton = $(evt.target);
                    createOrderForm.trigger('processStop');
                    EbizPaymentMethod.isAjax.val(1);
                    EbizPaymentMethod.isValidCc.val("");
                    EbizPaymentMethod.userAction.val("cancel");
                    /**
                     * canceled
                     */
                    EbizPaymentMethod.processAjaxRequest($, false);
                    return false;
                });
            },

            /**
             * Validate Avs Cvv Zip Code Action
             *
             * @param $
             * @param addCustomerModel
             * @param configModelData
             * @returns {boolean}
             */
            validateAvsCvvZipCodeAction: function ($, addCustomerModel, configModelData) {
                /** create order form **/
                let createOrderForm = EbizPaymentMethod.createOrderForm;
                let createOrderButton = EbizPaymentMethod.createOrderButton;
                let isAvsCvvZipEnabled = EbizPaymentMethod.isAvsCvvZipEnabled;
                let submitOrderEvent = "submitOrder";
                let paymentOptionType = EbizPaymentMethod.currentSelectedPaymentOptionType.val();

                let isMageError = $(".mage-error:visible").length;

                    EBizChargeCardValidator.validateAvsCvvActionUrl = createOrderForm.attr("action");
                    /**
                     * At Submit Form
                     */
                    let ajaxAction = document.getElementById("is_ajax");

                //console.log([isMageError, EbizPaymentMethod.isValidCc.val(), EbizPaymentMethod.isAjaxInProgress]);

                    if (isMageError === 0 && EbizPaymentMethod.isValidCc.val() === "" && EbizPaymentMethod.isAjaxInProgress===false) {

                        EbizPaymentMethod.userAction.val("");

                        /**
                         *
                         * @type {[{text: *, id: string, class: string, click: *},{text: *, id: string, class: string, click: *}]}
                         */
                        EbizPaymentMethod.popUpButtons = [
                            {
                                text: $.mage.__("Place order anyway"),
                                class: 'action- scalable save primary avs-response-save-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                                id: 'avs-response-save-button',
                                click: function (evt) {
                                    EbizPaymentMethod.isAjax.val(0);
                                    EbizPaymentMethod.isValidCc.val("validate");
                                    EbizPaymentMethod.userAction.val("OK");
                                    EbizPaymentMethod.createOrderForm.trigger('processStart');
                                    createOrderForm.submit();
                                    this.closeModal();
                                }
                            }, {
                                text: $.mage.__("Cancel "),
                                class: 'action- scalable save primary avs-response-cancel-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                                id: 'avs-response-cancel-button',
                                click: function (evt) {
                                    createOrderForm.trigger('processStop');
                                    EbizPaymentMethod.isAjax.val(1);
                                    EbizPaymentMethod.isValidCc.val("");
                                    EbizPaymentMethod.userAction.val("cancel");
                                    createOrderForm.trigger('processStop');
                                    EbizPaymentMethod.isAvsCvvCanceled = true;
                                    EbizPaymentMethod.processAjaxRequest($, true);
                                    this.closeModal();
                                }
                            }
                        ];

                        /**
                         * Process Ajax Request
                         */
                        EbizPaymentMethod.processAjaxRequest($, false);
                        return false;
                    }
            },
            /**
             * Process Ajax Request
             *
             * @param $
             * @returns {boolean}
             */
            processAjaxRequest: function ($, isCanceled=false) {

                EbizPaymentMethod.isAjaxInProgress = true;

                /** create order form **/
                let createOrderForm = EbizPaymentMethod.createOrderForm;
                EBizChargeCardValidator.validateAvsCvvActionUrl = createOrderForm.attr("action");
                //  $editForm.trigger('processStop');
                EbizPaymentMethod.isAjax.val(1);
                let saleOrderFormData = createOrderForm.serialize();
                //   console.log(saleOrderFormData);
                /**
                 * CVV Ajax
                 */
                let cvvAjax = $.ajax({
                    type: "POST",
                    url: EBizChargeCardValidator.validateAvsCvvActionUrl,
                    dataType: "json",
                    data: saleOrderFormData,
                    beforeSend: function (xhr) {
                        EbizPaymentMethod.isAjaxInProgress = true;
                        if (isCanceled === false) {
                            createOrderForm.trigger('processStart');
                        }
                    },
                    cache: false,
                    success: function (avsCvvData) {
                        // console.log(avsCvvData);
                        createOrderForm.trigger('processStop');

                        if (avsCvvData.avs_cvv_enabled === true ) {

                            if (avsCvvData.payment_authenticated === true && avsCvvData.trans_result === "A" ) {
                                EbizPaymentMethod.isAjax.val(0);
                                EbizPaymentMethod.isValidCc.val("validate");
                                EbizPaymentMethod.userAction.val("OK");
                                EbizPaymentMethod.createOrderForm.trigger('processStart');
                                createOrderForm.submit();
                            }
                            /**
                             * if cancel AVS Cvv
                             */
                            if (avsCvvData.is_canceled === true) {
                                EbizPaymentMethod.isAvsCvvCanceled = false;
                                EbizPaymentMethod.isAjaxInProgress = false;
                                EbizPaymentMethod.createOrderForm.trigger('processStop');
                             return false;
                            }
                            EbizPaymentMethod.renderAvsCvvWarningsPopup($, modal, EbizPaymentMethod.popUpButtons, avsCvvData);

                        } else {
                            EbizPaymentMethod.isAjax.val(0);
                            EbizPaymentMethod.isValidCc.val("validate");
                            EbizPaymentMethod.userAction.val("OK");
                            EbizPaymentMethod.createOrderForm.trigger('processStart');
                            createOrderForm.submit();
                        }
                    },
                    error: function (err) {
                        EbizPaymentMethod.isAjax.val(0);
                        EbizPaymentMethod.isValidCc.val("validate");
                        EbizPaymentMethod.userAction.val("cancel");
                        EbizPaymentMethod.createOrderForm.trigger('processStart');
                        createOrderForm.submit();
                        console.log(err);
                    }
                });

                return false;
            },

            /**
             * render Avs CVV Warnings Popup
             * @param $
             */
            renderAvsCvvWarningsPopup: function ($, modal = null, popupButtons = [], avsCvvData = null) {

                /** create
                 * order form **/
                let createOrderForm = EbizPaymentMethod.createOrderForm;
                let popupModelId = "avs-cvv-warnings-panel";
                let popupTitle = $.mage.__("Security mismatch dialog box...");

                /**
                 * render popup
                 * @type {{buttons: *[], responsive: boolean, innerScroll: boolean, type: string, title: string}}
                 */
                let popupOptions = EbizPaymentMethod.prepareAvsCvvWarningsPopupOptions($, popupTitle, popupButtons, avsCvvData);

                let popup = modal(popupOptions, $(document.getElementById(popupModelId)));

                if (avsCvvData.payment_authenticated === false) {
                    EbizPaymentMethod.cardMessageResponse.text(avsCvvData.message).show();
                    if (avsCvvData.trans_result === "E") {
                        // EbizPaymentMethod.avsSaveActionButton.attr("disabled", true);
                        jQuery(".avs-response-save-button").attr("disabled", true);

                    }

                    $(document.getElementById(popupModelId)).modal('openModal');
                } else {
                    EbizPaymentMethod.isAjax.val(0);
                    createOrderForm.submit();
                }

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
             * Reload Order's total section
             */
            reloadTotals: function () {
                window.order.loadArea(['totals'], true);
            },

            /**
             * Surcharge Events
             */
            surchargeEvents: function () {
                /**
                 * Calculate surcharge when Payment Method is selected
                 */
                $(document.getElementsByName('payment[method]')).on('click', function () {
                    let methodName = this.value;

                    setTimeout(function () {
                        if (methodName === EbizPaymentMethod.paymentMethodCode) {
                            if (EbizPaymentMethod.payByCardRadioBtn.is(':checked')) {
                                EbizPaymentMethod.calculateSurcharge();
                            } else {
                                EbizPaymentMethod.reloadTotals();
                            }
                        } else {
                            EbizPaymentMethod.reloadTotals();
                        }
                    }, 500);
                });

                /**
                 * When click on "Select save card", "Update existing card" & "Add new card"
                 */
                EbizPaymentMethod.payByMethodsRadio.on('click keypress', function () {
                    if (this.value === 'credit_card') {
                        EbizPaymentMethod.calculateSurcharge();
                    } else {
                        EbizPaymentMethod.reloadTotals();
                    }
                });

                /**
                 * Check surcharge when "Select saved card" payment method is clicked
                 */
                EbizPaymentMethod.selectSavedCardRadio.on('click keypress', function () {
                    EbizPaymentMethod.calculateSurcharge();
                });

                /**
                 * Check surcharge when "Select saved card" payment method is changed
                 */
                EbizPaymentMethod.savedCardField.on('change', function () {
                    EbizPaymentMethod.calculateSurcharge();
                });

                /**
                 * Check surcharge when "Update existing card" payment method is selected
                 */
                EbizPaymentMethod.updateExistingCardRadio.on('click keypress', function () {
                    EbizPaymentMethod.calculateSurcharge();
                });

                /**
                 * Check surcharge when "Add new card" payment method is clicked
                 */
                EbizPaymentMethod.addNewCardRadio.on('click keypress', function () {
                    EbizPaymentMethod.calculateSurcharge();
                });

                /**
                 * Calculate surcharge on cc number change
                 */
                EbizPaymentMethod.creditCardNumber.on('focusout', function () {
                    let cardNo = this.value;
                    if (cardNo && cardNo.length >= 16) {
                        EbizPaymentMethod.calculateSurcharge(cardNo);
                    } else {
                        if (EbizPaymentMethod.addNewCardRadio.is(':checked')) {
                            EbizPaymentMethod.reloadTotals();
                        }
                    }
                });

                /**
                 * Calculate surcharge on avs zip change
                 */
                EbizPaymentMethod.avsZipFields.on('focusout', function () {
                    EbizPaymentMethod.calculateSurcharge();
                });
            },

            /**
             * Check is Payment method EBizCharge & Pay By Card selected
             *
             * @returns {boolean}
             */
            isEbizCardMethod: function () {
                let ebizMethod = $('#' + EbizPaymentMethod.paymentMethodId).is(':checked');
                let payByCard = EbizPaymentMethod.payByCardTitle.hasClass('red-border');
                return !!(ebizMethod && payByCard);
            },

            /**
             * Calculate Surcharge
             *
             * @param ccNo
             */
            calculateSurcharge: function (ccNo = null) {
                if (!EbizPaymentMethod.surchargeEnabled) {
                    return;
                }

                let isEbizCardMethodSelected = EbizPaymentMethod.isEbizCardMethod();
                let cardNo = ccNo ? ccNo : EbizPaymentMethod.creditCardNumber.val();
                let avsZip, //= $('#' + EbizPaymentMethod.paymentMethodCode + '_avs_zip').val();
                    savedMethodId, //= EbizPaymentMethod.savedCardField.val();
                    updateSavedMethodId; //= EbizPaymentMethod.updateSavedCard.val();

                if (!isEbizCardMethodSelected) {
                    return;
                }

                let ajaxCall = false;
                let ajaxCallData = {
                    form_key: window.FORM_KEY,
                    emptyCall: 0,
                    customerInternalId: EbizPaymentMethod.customerInternalId
                };
                if (EbizPaymentMethod.addNewCardRadio.is(':checked')) {
                    ajaxCall = true;
                    avsZip = $('#add-new-card-panel #' + paymentMethodCode + '_avs_zip').val();

                    if (cardNo && avsZip && EbizPaymentMethod.customerInternalId) {
                        ajaxCallData.cardNumber = cardNo;
                        ajaxCallData.cardZipCode = avsZip;
                        // ajaxCallData = {
                        //     'cardNumber': cardNo,
                        //     'cardZipCode': avsZip,
                        // };
                    } else if (!cardNo && !avsZip) {
                        ajaxCallData.emptyCall = 1;
                    } else {
                        ajaxCall = false;
                    }
                } else if (EbizPaymentMethod.selectSavedCardRadio.is(':checked')) {
                    ajaxCall = true;
                    savedMethodId = $('#ebiz-tab-pay-by-card-panel #' + paymentMethodCode + '_cc_token').val();

                    if (savedMethodId && EbizPaymentMethod.customerInternalId) {
                        ajaxCallData.paymentMethodId = savedMethodId;
                        // ajaxCallData = {
                        //     'paymentMethodId': savedMethodId,
                        // };
                    }
                } else if (EbizPaymentMethod.updateExistingCardRadio.is(':checked')) {
                    ajaxCall = true;
                    avsZip = $('#update-saved-card-panel #' + paymentMethodCode + '_avs_zip').val();
                    updateSavedMethodId = $('#update-saved-card-panel #' + paymentMethodCode + '_cc_token_update').val();

                    if (avsZip && updateSavedMethodId) {
                        ajaxCallData.cardZipCode = avsZip;
                        ajaxCallData.paymentMethodId = updateSavedMethodId;
                        // ajaxCallData = {
                        //     'cardZipCode': avsZip,
                        //     'paymentMethodId': updateSavedMethodId,
                        // };
                    }
                }

                if (!ajaxCallData || !EbizPaymentMethod.calculateSurchargeAjaxUrl || !ajaxCall) {
                    return;
                }

                $.ajax({
                    url: EbizPaymentMethod.calculateSurchargeAjaxUrl,
                    type: 'POST',
                    data: ajaxCallData,
                    dataType: 'json',
                    cache: false,
                    /**
                     * Before Send
                     */
                    beforeSend: function () {
                        EbizPaymentMethod.createOrderForm.trigger('processStart');
                        $('body').loader('show');
                    },
                    /**
                     * Success when response is ready
                     *
                     * @param respData
                     */
                    success: function (respData) {
                        // console.log((respData));

                        if (respData.surchargeEnabled) {
                            window.order.loadArea(['totals'], true);
                        } else {
                            //surchargeAction().setSurchargeValuesForNew();
                        }
                    },
                    /**
                     * When the whole process completed
                     *
                     * @param respData
                     */
                    complete: function (respData) {
                        EbizPaymentMethod.createOrderForm.trigger('processStop');
                        $('body').loader('hide');
                    },
                    /**
                     * Error
                     *
                     * @param error
                     */
                    error: function (error) {
                        // console.log(error);
                        EbizPaymentMethod.createOrderForm.trigger('processStop');
                        $('body').loader('hide');
                    }
                });
            }
        }

        return function (configModelData) {

            /*********
             * payment methods initialization
             * ************/
            return EbizPaymentMethod._init($, jui, _, translate, SalesOrder, CcType, configModelData, addCustomerModel, modal);
        }
    }
);
