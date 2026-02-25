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
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/view/form/element/email',
        'Ebizcharge_Ebizcharge/js/popup/validation-response',
        'Magento_Ui/js/modal/modal',
        'jquery',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'Ebizcharge_Ebizcharge/js/checkout/gateway/ebizcharge-gateway',
        'Magento_Checkout/js/action/get-totals',
        'Ebizcharge_Ebizcharge/js/view/checkout/summary/ebiz-surcharge',
        'ko',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/payment/place-order-hooks',
        'underscore',
        'mage/storage'
    ], function (
        Component,
        setPaymentInformationAction,
        validator,
        additionalValidators,
        customer,
        quote,
        paymentInfo,
        orderModel,
        email,
        AvsCvvValidation,
        modal,
        $,
        messageList,
        $t,
        EBizChargeGatewayModel,
        getTotalsAction,
        surchargeAction,
        ko,
        mageUrl,
        errorContainer,
        totalsProcessor,
        customerData,
        hooks,
        _,
        storage
    ) {
        'use strict';

        var ebizMageConfig = window.checkoutConfig.payment.ebizcharge;
        var eBiz3DSecure;

        $(document).on('keypress', function (event) {
            let elementId = event.target.id;

            if (elementId !== undefined) {
                let currentElement = $('#' + elementId);

                if (currentElement.hasClass('validate-length')) {
                    /** on Key Press **/
                    currentElement.on("keypress", function (evnt) {
                        let minLength = currentElement.attr('minlength') !== undefined ?
                            parseInt(currentElement.attr('minlength')) : 20;
                        let maxLength = currentElement.attr('maxlength') !== undefined ?
                            parseInt(currentElement.attr('maxlength')) : 20;
                        let currentElementVal = currentElement.val();

                        if (currentElementVal.length >= maxLength) {
                            evnt.preventDefault();
                            return false;
                        }
                    })
                }
            }
        });

        return Component.extend({
            defaults: {
                template: 'Ebizcharge_Ebizcharge/payment/ebizcharge',
                messageContainer: messageList,
                useSavedCard: true,
                addNewCard: false,
                saveCard: false,
                config_save_card: false,
                config_save_bank_accounts: false,
                updateSavedCard: false,
                paymentToken: false,
                ebzc_cust_id: '',
                ebzc_option: '',
                SecondarySort: '',
                selectedCardToken: '',
                selectedCardType: '',
                requestCC: false,
                has_token: false,
                storeInVault: false,
                useVault: false,
                additional: '',
                storedCards: [],
                ebzc_option_type: '',
                useSavedAccount: true,
                storedAccounts: [],
                useACH: false,
                useCC: true,
                routeNumber: '',
                ebzc_payment_option: 'CC',
                achAccountTypes: [],
                addNewAccount: false,
                isAchActive: false,
                isCreditCardActive: false,
                isRecurringEnabled: '',
                recurIndefinitely: false,
                payment_type_card_label: 'pay-by-card-label',
                payment_type_ach_label: 'pay-by-ach-label',
                payment_type_card_new_panel: 'payment_form_ebizcharge_ebizcharge',
                payment_method_options: {
                    mage_use_saved_card: 'credit_card_saved',
                    mage_update_saved_card: 'credit_card_updated',
                    mage_use_new_card: 'credit_card_new',
                    mage_use_new_account: 'ach_new',
                    mage_use_saved_account: 'ach_saved'
                },
                payment_method_form: 'ebizcharge_ebizcharge-form',
                ebizChargeNewCard: 'mage_use_new_card',
                additionalData: '',
                surchargeEnabled: false,
                surchargeTypeId: null,
                surchargeTermsNote: null,
                surchargeTypeDailyDiscount: null,
                calculateSurchargeAjaxUrl: null,
                selectedPaymentMethod: ko.observable(null),
                ebiz3DSecureEnabled: false,
                ebizSecurityToken: {},
                save3DSecureDataUrl: null,
                ebiz3DSecureErrorDiv: null,
                ebiz3DSecureErrorMessage: ko.observable(null),
                quoteReservedOrderId: null,
                expMonthErrorMsgDivId: ebizMageConfig.code + '_expiration-error',
                expYearErrorMsgDivId: ebizMageConfig.code + '_expiration_yr-error',
                billStreetFieldDivId: 'ebiz-checkout-street-field',
                paymentFormType: ko.observable(null),
                checkoutWebHostedFormUrl: ko.observable(null),
                tokenizeCardsOnly: ko.observable(null),
                isRecurringOrderOnly: ko.observable(false),
                placeSubscribeOrderUrl: ko.observable(null),
                successPageUrl: ko.observable(null),
                iframeWidth: 720,
                iframeHeight: 790,
                iframeBorder: 0,
                gRecaptchaResponse: ko.observable('')


            },

            /**
             * Initialize script
             */
            initialize: function () {
                this._super();
                let self = this;

                this.ebiz3DSecureEnabled = ebizMageConfig.ebiz3DSecureEnabled;
                this.ebizSecurityToken = ebizMageConfig.ebizSecurityToken;

                let selectedPaymentMethod = quote.paymentMethod() && typeof (quote.paymentMethod().method)
                !== 'undefined' ? quote.paymentMethod().method : '';
                this.selectedPaymentMethod = selectedPaymentMethod;

                if (selectedPaymentMethod === self.getCode()) {
                    self.checkNewCardSurcharge();
                } else {
                    surchargeAction().unsetSurchargeValues();
                }
                /**
                 *  if Payment Method exists
                 */
                quote.paymentMethod.subscribe(function (method) {
                    if (method) {
                        if (method.method === self.getCode() && self.useCC()) {
                            self.checkNewCardSurcharge();
                        } else {
                            surchargeAction().unsetSurchargeValues();
                        }
                        self.selectedPaymentMethod = method.method;
                    }
                }, null, 'change');

                /**
                 * EBizCharge 3DSecure Process
                 */
                if (self.ebiz3DSecureEnabled) {
                    let securityToken = {
                        Password: typeof (self.ebizSecurityToken.password) !== 'undefined' ?
                            self.ebizSecurityToken.password : '',
                        UserId: typeof (self.ebizSecurityToken.userId) !== 'undefined' ?
                            self.ebizSecurityToken.userId : '',
                        SecurityId: typeof (self.ebizSecurityToken.securityId) !== 'undefined' ?
                            self.ebizSecurityToken.securityId : ''
                    };

                    /**
                     * 3DSecure connection
                     */
                    //  eBiz3DSecure = new EBiz3DSecure(JSON.stringify(securityToken));
                }


            },

            /**
             * Init Vars
             */
            initVars: function () {
                this.isPaymentProcessing = null;
                this.quoteBaseGrandTotals = quote.totals().base_grand_total;
            },

            /**
             * Get Email
             *
             * @returns {null|*}
             */
            getEmail: function () {
                if (quote.guestEmail) {
                    return quote.guestEmail;
                } else {
                    return window.checkoutConfig.customerData.email;
                }
            },

            /**
             * Is Guest
             *
             * @returns {boolean}
             */
            isGuest: function () {
                if (quote.guestEmail) {
                    return true;
                } else {
                    return false;
                }
            },

            /**
             * Is Customer Logged in
             *
             * @returns {boolean}
             */
            isCustomerLooged: function () {
                if (quote.guestEmail) {
                    return false;
                } else {
                    return true;
                }
            },
            /**
             * get Quote postcode
             * @returns {*|string}
             */
            getQuoteBillingCustomerName: function () {
                let firstName = this.getFirstName() ?? "";
                let lastName = this.getLastName() ?? "";
                let customerName = firstName + " " + lastName ?? "";
                return customerName;
            },
            /**
             * get Quote postcode
             * @returns {*|string}
             */
            getQuoteAvsZipCode: function () {
                return typeof quote.billingAddress() !== "undefined" && quote.billingAddress() !== null ? quote.billingAddress().postcode : "";
            },
            /**
             * get Quote Avs Billing Street
             * @returns {*|string}
             */
            getQuoteAvsBillingStreet: function () {
                return typeof quote.billingAddress() !== "undefined" && quote.billingAddress() !== null ? quote.billingAddress().street[0] : "";
            },

            /**
             * Get customer
             *
             * @returns {*}
             */
            getCustomer: function () {
                return customer;
            },

            /**
             * Get customer data
             *
             * @returns {*}
             */
            getCustomerData: function () {
                return window.checkoutConfig.customerData;
            },

            /**
             * Get Last name
             *
             * @returns {*}
             */
            getLastName: function () {
                let isGuest = this.isGuest();
                let lastName = "";
                if (isGuest) {
                    lastName = typeof quote.billingAddress() !== "undefined" && quote.billingAddress() !== null ? quote.billingAddress().lastname : "";
                } else {
                    let customerData = this.getCustomerData();
                    lastName = customerData.lastname;
                }
                return lastName;
            },

            /**
             * Get first name
             *
             * @returns {*}
             */
            getFirstName: function () {
                let isGuest = this.isGuest();
                let firstName = "";
                if (isGuest) {
                    firstName = typeof quote.billingAddress() !== "undefined" && quote.billingAddress() !== null ? quote.billingAddress().firstname : "";
                } else {
                    let customerData = this.getCustomerData();
                    firstName = customerData.firstname;
                }
                return firstName;

            },

            /**
             * Get Address
             *
             * @returns {string}
             */
            getAddress: function () {
                return typeof quote.billingAddress() !== "undefined" && quote.billingAddress() !== null ? quote.billingAddress().street[0] + ", " + quote.billingAddress().postcode + " " +
                    quote.billingAddress().city : "";
            },

            /**
             * Get Phone
             *
             * @returns {*}
             */
            getPhone: function () {
                return typeof quote.billingAddress() !== "undefined" && quote.billingAddress() !== null ? quote.billingAddress().telephone : "";
            },

            /**
             * Init Observable
             *
             * @param e
             * @returns {initObservable}
             */
            initObservable: function (e) {
                self.name = this;
                this._super().observe(
                    [
                        'paymentToken',
                        'selectedCardToken',
                        'selectedCardType',
                        'cardOwner',
                        'storeInVault',
                        'storedCards',
                        'config_save_card',
                        'config_save_bank_accounts',
                        'useSavedCard',
                        'addNewCard',
                        'updateSavedCard',
                        'has_token',
                        'getEbzcCustId',
                        'useSavedAccount',
                        'storedAccounts',
                        'recurIndefinitely',
                        'useACH',
                        'useCC',
                        'achAccountTypes',
                        'addNewAccount',
                        'isAchActive',
                        'isCreditCardActive',
                        'isRecurringEnabled',
                        'recurIndefinitely',
                        'additionalData',
                        'paymentFormType',
                        'checkoutWebHostedFormUrl',
                        'tokenizeCardsOnly',
                        'iframeWidth',
                        'iframeHeight',
                        'iframeBorder',
                        'isRecurringOrderOnly',
                        'successPageUrl',
                        'placeSubscribeOrderUrl',
                        'gRecaptchaResponse'
                    ]
                );

                if (!this.storedAccounts.length && this.ebzc_payment_option === 'ACH') {
                    this.useSavedAccount(false);
                }

                if ((!this.storedCards.length) || (this.has_token === false) || (this.has_token === 0)) {
                    this.useSavedCard(false);
                }

                if ((this.storedCards.length) && (this.has_token)) {
                    this.paymentToken(this.storedCards[0].MethodID);
                }

                let paymentOptionType = 'use_credit_card';
                /** by default use saved card **/
                this.useSavedCard(true);

                this.initMage();
                this.updateEbizSavedOption();
                this.updateEbizNewOption('cc');
                this.markedCheckedSaveCardInfo();
                //this.checkEbizChargeSelect();

                /** by default the payment option credit card is selected
                 * if it is enabled from admin  console
                 * @type {number}
                 */
                let defaultPaymentOption = setTimeout(function (evt) {
                    /** default payment method is used **/
                    if (document.getElementById('use_credit_card') !== undefined) {
                        $(document.getElementById("use_credit_card")).trigger("click");
                        clearTimeout(defaultPaymentOption);
                    }
                }, 600);

                let EBizChargePaymentMethod = this;

                return this;
            },

            /**
             * Render Tab Contents
             *
             * @param paymentType
             * @returns {renderTabContents}
             */
            renderTabContents: function (paymentType) {

                /**
                 * Always select default payment method
                 */

                $('.' + this.payment_type_card_label).removeClass('tab-selected');
                $('.' + this.payment_type_ach_label).removeClass('tab-selected');

                if (paymentType === 'use_credit_card') {
                    $('.' + this.payment_type_card_label).addClass('tab-selected');

                } else {
                    $('.' + this.payment_type_ach_label).addClass('tab-selected');

                }

                return this;
            },

            /**
             * Init Mage
             *
             * @param element
             */
            initMage: function (element) {
                let surchargeSettings = typeof (ebizMageConfig.surchargeSettings)
                !== 'undefined' ? ebizMageConfig.surchargeSettings : false;

                this.has_token = ebizMageConfig.hasToken || false;
                this.isach_active = ebizMageConfig.isAchActive || 0;
                this.is_cc_active = ebizMageConfig.isCreditCardActive || 0;
                this.ebzc_cust_id = ebizMageConfig.getEbzcCustId || '';
                this.storedCards = ebizMageConfig.storedCards || [];
                this.storedAccounts = ebizMageConfig.storedAccounts || [];
                this.requestCC = ebizMageConfig.requestCardCode                    || 0;
                this.saveCard = ebizMageConfig.saveCard || 0;
                this.config_save_bank_accounts = ebizMageConfig.config_save_bank_accounts || 0;
                this.config_save_card = ebizMageConfig.config_save_card || 0;
                this.useVault = ebizMageConfig.useVault || false;
                this.addNewCard(false);
                this.addNewAccount(false);
                this.useSavedCard(true);
                this.updateSavedCard(false);
                this.selectedCardToken(false);
                this.selectedCardType(false);
                this.useSavedAccount(true);
                this.achAccountTypes = ebizMageConfig.getAchAccountTypes;
                this.useACH(false);
                this.useCC(false);
                this.isRecurringEnabled = ebizMageConfig.isRecurringEnabled;
                this.isRecurringOrderOnly = false;
                this.placeSubscribeOrderUrl = ebizMageConfig.placeSubscriptionsUrl;
                this.successPageUrl = ebizMageConfig.successPageUrl;
                this.additionalData = "";
                this.surchargeEnabled = !!ebizMageConfig.surchargeEnabled;
                this.surchargeTypeId = typeof (surchargeSettings.surchargeTypeId) !==
                'undefined' ? surchargeSettings.surchargeTypeId : '';
                this.surchargeTermsNote = typeof (surchargeSettings.surchargeTermsNote) !==
                'undefined' ? surchargeSettings.surchargeTermsNote : 0;
                this.surchargeTypeDailyDiscount = ebizMageConfig.surchargeTypeDailyDiscount;
                this.calculateSurchargeAjaxUrl = ebizMageConfig.calculateSurchargeAjaxUrl;
                this.quoteReservedOrderId = ebizMageConfig.quoteReservedOrderId;
                this.save3DSecureDataUrl = ebizMageConfig.save3DSecureDataUrl;
                this.ebiz3DSecureErrorDiv = $(document.getElementById('ebiz-3dSecure-error'));
                this.paymentFormType = ebizMageConfig.paymentFormType;
                this.checkoutWebHostedFormUrl = ebizMageConfig.checkoutWebHostedFormUrl ?? "";
                this.tokenizeCardsOnly = ebizMageConfig.tokenizeCardsOnly;
                this.gRecaptchaResponse = "";


            },
            /**
             *
             * @returns {string}
             */
            getGoogleReCaptchaResponse: function () {
                let reCaptchaResponse = "";
                let checkoutForm = $(document.getElementById("ebizcharge_ebizcharge-form"));
                let reCaptchaResponseTxt = checkoutForm.find(".g-recaptcha-response");
                this.gRecaptchaResponse = reCaptchaResponseTxt.val();

                return reCaptchaResponse;
            },

            /**
             * Get Ach Status
             *
             * @returns {*}
             */
            getAchStatus: function () {
                return this.isach_active;
            },
            /**
             *
             * @returns {*}
             */
            getWebIframeWidth: function () {
                this.iframeBorder = 0;
                if (this.tokenizeCardsOnly === 1) {
                    this.iframeWidth = 720;
                    this.iframeHeight = 790;
                }
                return this.iframeWidth;
            },
            /**
             *
             * @returns {*}
             */
            getWebIframeHeight: function () {
                this.iframeBorder = 0;
                if (this.tokenizeCardsOnly === 1) {
                    this.iframeWidth = 720;
                    this.iframeHeight = 790;
                }
                return this.iframeHeight;
            },
            /**
             *
             * @returns {string}
             */
            getWebIframeStyle: function () {
                this.iframeBorder = 0;
                this.iframeWidth = 720;
                this.iframeHeight = 790;

                if (this.tokenizeCardsOnly === 1) {
                    this.iframeWidth = 720;
                    this.iframeHeight = 790;
                }
                const styleStr = "width:auto;height:auto;min-height:" + this.iframeHeight + "px;min-width:" + this.iframeWidth + "px;border:1px solid #efefef; padding-top:10px; background-color:#efefef";
                $("#actions-toolbar").hide();

                return styleStr;
            },
            /**
             *
             * @returns {string}
             */
            getHostedIframeClass: function () {
                var webHostedIframeClass = "web-hosted-iframe-form";
                if (window.checkoutConfig.payment.ebizcharge.tokenizeCardsOnly === "1") {
                    webHostedIframeClass += " web-hosted-iframe-tokenized-only";
                }
                return webHostedIframeClass;
            },
            /**
             *
             * @returns {string}
             */
            getPlaceOrderButtonStyle: function () {
                let styleStr = "display:block";
                //actions-toolbar
                if (parseInt(this.paymentFormType) === 2) {
                    styleStr = "display:none";
                }
                return styleStr;
            },
            /**
             * is place Order Button is need to be hidden
             */
            isPlaceOrderButtonHidden: function () {
                let actionToolsBar = $(".actions-toolbar-buttons");
                actionToolsBar.show();
                if (parseInt(this.paymentFormType) === 2) {
                    actionToolsBar.hide();
                }

            },

            /**
             * Check if credit card payments are enabled
             *
             * @return {*}
             */
            getCcPaymentStatus: function () {
                return this.is_cc_active;
            },

            /**
             * Payment Options Disabled
             *
             * @returns {boolean}
             */
            paymentOptionsDisabled: function () {
                return !this.getCcPaymentStatus() && !this.getAchStatus();
            },
            /**
             *
             * @returns {number|number}
             */

            getPaymentFormType: function () {
                // console.log("payment form Type is selected " + parseInt(this.paymentFormType));
                return typeof parseInt(this.paymentFormType) !== "undefined" ? parseInt(this.paymentFormType) : 2;
            },
            /**
             *
             * @returns {*}
             */
            isTokenizeCardsOnly: function () {
                // console.log("payment form  Is Tokenized cards only selected " + this.tokenizeCardsOnly);
                return this.tokenizeCardsOnly;
            },
            /**
             * Render back to Cart Page Action
             */
            renderBackToCartActionPage: function (objId) {
                let cartUrl = mageUrl.build('checkout/cart');
                $(document.getElementById(objId)).on("click keypress", function () {
                    window.location.href = cartUrl;
                });
            },

            /**
             *
             * @returns {*}
             */
            getCheckoutWebHostedFormUrl: function () {
                let csrfToken = window.checkoutConfig.formKey;
                // console.log("current iframe hosted URL " + this.checkoutWebHostedFormUrl);
                this.checkoutWebHostedFormUrl = window.checkoutConfig.payment.ebizcharge.getLogoUrl;

                return this.checkoutWebHostedFormUrl;
            },

            /**
             *
             * @returns {*}
             */
            isRecurringOrder: function () {
                let quoteItems = window.checkoutConfig.quoteItemData;

                let placeOrderButton = $(document.getElementById("actions-toolbar"));
                let placeSubscriptionButton = $(document.getElementById("actions-toolbar-subscriptions"));

                let subscribedItems = [];
                let unsubscribedItems = [];
                self = this;
                $.each(quoteItems, function (id, item) {
                    if (quoteItems.length > 0) {
                        if (typeof item.recurring !== "undefined") {
                            if (typeof item.recurring.frequency !== "undefined" && item.recurring.frequency.length > 0) {
                                self.isRecurringOrderOnly = true;
                            } else {
                                unsubscribedItems[id] = item;
                            }
                        }
                    }
                });
                if (unsubscribedItems.length > 0) {
                    self.isRecurringOrderOnly = false;
                }

                if (parseInt(this.paymentFormType) === 2) {
                    placeOrderButton.hide();
                    placeSubscriptionButton.hide();
                }

                return this.isRecurringOrderOnly;
            },

            /**
             * prepare Recurring Order
             */
            prepareRecurringOrder: function () {

                if (!this.getUseVault()) {
                    this.updateEbizNewOption();
                }
                if (this.validate()) {
                    let self = this;
                    this.messageContainer.clear();

                    let customerId = "guest";
                    if (this.isCustomerLooged()) {
                        customerId = this.customer_id
                    }
                    let checkoutForm = $(document.getElementById("ebizcharge_ebizcharge-form"));
                    let successRecurring = false;
                    let successPageUrl = this.successPageUrl;
                    /**
                     *
                     * @type {{method: {additional_data: {cc_exp_month: (*|jQuery), ebzc_avs_zip: (*|jQuery), ebzc_option: *, ebzc_cust_id: *, cc_exp_year: (*|jQuery), ach_routing: (*|jQuery), cc_cid: *, cc_type: (*|jQuery|string), paymentToken: *, ebzc_method_id: *, ebzc_save_payment: *, ach_type: (*|jQuery), ebzc_avs_street: (*|jQuery), cc_owner: (*|jQuery), rec_admin: *, cc_number: *, ebzc_option_type: *}, method: string}, payment: (*|jQuery), customer_id: string}}
                     */
                    let paymentFormData = {
                        method: this.getData(),
                        customer_id: customerId,
                        payment: checkoutForm.serializeArray(),
                    };

                    /**
                     * Action URL
                     */
                    const actionUrl = this.placeSubscribeOrderUrl;

                    this.messageContainer.clear();

                    this.placeSubscriptions(actionUrl, checkoutForm, paymentFormData)
                        .then(
                            response => {
                                // console.log('Success:', response);
                                if (response.status === true) {

                                    let orderId = checkoutConfig.quoteData.entity_id;
                                    let incrementId = orderId;
                                    let quoteItems = checkoutConfig.quoteItemData;
                                    let subscribedProduct = "";

                                    if (quoteItems.length > 0) {
                                        $.each(quoteItems, function (key, quoteItem) {
                                            subscribedProduct += " " + quoteItem.name + " Qty:" + quoteItem.qty;
                                        });
                                    }
                                    /**
                                     * Success URL
                                     */
                                    let successUrl = successPageUrl  // Magento default success page

                                    successUrl += 'subscription/1/order_id/' + orderId + '/increment_id/' + incrementId + '/params/' + subscribedProduct;
                                    quote.getQuoteId('');
                                    let cartData = customerData.get('cart');
                                    cartData().items = [];
                                    customerData.reload(['cart'], true);
                                    let clearData = {
                                        'selectedShippingAddress': null,
                                        'shippingAddressFromData': null,
                                        'newCustomerShippingAddress': null,
                                        'selectedShippingRate': null,
                                        'selectedPaymentMethod': null,
                                        'selectedBillingAddress': null,
                                        'billingAddressFromData': null,
                                        'newCustomerBillingAddress': null
                                    };

                                    customerData.set('checkout-data', clearData);
                                    _.each(hooks.afterRequestListeners, function (listener) {
                                        listener();
                                    });
                                    /**
                                     * redirecting to quote
                                     */
                                    window.location.href = successUrl;

                                } else {
                                    checkoutForm.trigger('processStop');
                                    $('body').loader('hide');

                                    let errorMsg = {
                                        message: $t("Payment authorized error: " + response.message)
                                    };
                                    this.messageContainer.addErrorMessage(errorMsg);
                                }
                            })
                        .catch(error => {
                            console.error('Error:', error);
                            checkoutForm.trigger('processStop');
                            $('body').loader('hide');
                            let errorMsg = {
                                message: $t("Payment authorized error: Subscription could not created.")
                            };
                            this.messageContainer.addErrorMessage(errorMsg);

                        });

                    //  self.placeOrder();
                }
            },
            /**
             *
             * @param actionUrl
             * @param checkoutForm
             * @param actionData
             * @returns {Promise<unknown>}
             */
            placeSubscriptions: function (actionUrl = "", checkoutForm = {}, actionData = {}) {

                /**
                 * promise resolve
                 */
                return new Promise((resolve, reject) => {
                    $.ajax(
                        {
                            url: actionUrl,
                            type: 'POST',
                            dataType: 'json',
                            data: actionData,
                            beforeSend: function () {
                                checkoutForm.trigger('processStart');
                            },
                            success: function (response) {
                                resolve(response);
                            },
                            error: function (xhrError) {
                                checkoutForm.trigger('processStop');
                                $('body').loader('hide');
                                // alert('An error occurred while processing the request.');
                                reject(xhrError);
                            }
                        });

                });
            },

            /**
             * Get Cart Action Url
             *
             * @param obj
             * @returns {WindowProxy}
             */
            getCartActionUrl: function (obj) {
                return window.open('/checkout/cart');
            },

            /**
             * Get Ach Account Types
             *
             * @returns {[]}
             */
            getAchAccountTypes: function () {
                return this.achAccountTypes;
            },

            /**
             * Update EbizACH Payment Option
             */
            updateEbizACHPaymentOption: function () {
                this.ebzc_payment_option = 'ACH';
                this.useACH(true);
                this.useCC(false);
            },

            /**
             * Update Ebiz CC Payment Option
             *
             * @param paymentType
             */
            updateEbizCCPaymentOption: function (paymentType) {
                /**
                 * Always select default payment method
                 */
                if (paymentType === 'use_credit_card') {
                    this.ebzc_payment_option = 'credit_card';
                    this.useACH(false);
                    this.useCC(true);
                    $(document.getElementById("mage_use_new_card")).trigger("click");
                    this.checkNewCardSurcharge();
                } else {
                    this.ebzc_payment_option = 'ACH';
                    this.useACH(true);
                    this.useCC(false);
                    $(document.getElementById("mage_use_new_account")).trigger("click");

                    surchargeAction().unsetSurchargeValues();
                }
                /** adding tab contnets */
                this.renderTabContents(paymentType);
            },

            /**
             * Get Code
             *
             * @returns {string}
             */
            getCode: function () {
                return 'ebizcharge_ebizcharge';
            },


            /**
             *
             * @returns {*}
             */
            getEbizCcMonthsValues: function () {

                let ccFormMonths = window.checkoutConfig.payment.ccform.months[this.getCode()];
                const ccFormUniqueMonths = Array.from(new Set(Object.values(ccFormMonths)));
                const ccFormUniqueMonthsObject = {};
                ccFormUniqueMonths.forEach((ccMonth, index) => {
                    ccFormUniqueMonthsObject[index + 1] = ccMonth;
                });
                return _.map(ccFormUniqueMonthsObject, function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    };
                });


            },
            /**
             *
             * @returns {*}
             */
            getEbizCcYearsValues: function () {
                let ccFormYears = window.checkoutConfig.payment.ccform.years[this.getCode()];
                const ccFormUniqueYears = Array.from(new Set(Object.values(ccFormYears)));
                const ccFormUniqueYearsObject = {};
                ccFormUniqueYears.forEach((ccYear, index) => {
                    ccFormUniqueYearsObject[ccYear] = ccYear;
                });
                return _.map(ccFormUniqueYearsObject, function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });


            },
            /**
             * Get list of years
             * @returns {Object}
             */
            getCcYears: function () {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },
            /**
             * Get list of months
             * @returns {Object}
             */
            getCcMonths: function () {
                return window.checkoutConfig.payment.ccform.months[this.getCode()];
            },


            /**
             * Is Active
             *
             * @returns {boolean}
             */
            isActive: function () {
                return true;
            },

            /**
             * Get use vault
             *
             * @returns {boolean}
             */
            getUseVault: function () {
                return this.useVault;
            },

            /**
             * Get Request Card Code
             *
             * @returns {boolean}
             */
            getRequestCardCode: function () {
                return (parseInt(this.requestCC) === 1);
            },

            /**
             * Get Save Card
             *
             * @returns {boolean}
             */
            getSaveCard: function () {
                let saveConfigCard = this.config_save_card;
                /** if save Config Card Enabled from Configuration **/
                if (saveConfigCard) {
                    return true;
                }
                return false;
            },

            /**
             * Get Save Bank Accounts
             *
             * @returns {boolean}
             */
            getSaveBankAccounts: function () {
                let saveConfigBankAccounts = this.config_save_bank_accounts;
                /** if save Config BAnk Account Enabled from Configuration **/
                if (saveConfigBankAccounts) {
                    return true;
                }
                return false;
            },

            /**
             * Get All saved Accounts (List or null)
             *
             * @returns {boolean}
             */
            getAllSavedAccounts: function () {
                this.storedAccounts = ebizMageConfig.storedAccounts;

                if (!this.storedAccounts.length || this.storedAccounts.length === '' || this.storedAccounts === null) {
                    return false;
                } else {
                    return true;
                }
            },

            /**
             * Get All Saved Cards (List or null)
             *
             * @returns {boolean}
             */
            getAllSavedCards: function () {
                this.storedCards = ebizMageConfig.storedCards;
                if (!this.storedCards.length || this.storedCards.length === '' || this.storedCards === null ||
                    !ebizMageConfig.showSavedUpdatedCardsOnCheckout) {
                    return false;
                } else {
                    return true;
                }
            },

            /**
             * Get Count for all saved cards
             *
             * @returns {{AllCards, CardCount: *, CardType}}
             */
            getCountSavedCards: function () {
                this.storedCards = ebizMageConfig.storedCards;
                var userDefinedCard = null;
                if (typeof this.storedCards.length === "undefined") {
                    userDefinedCard = {
                        "CardCount": 1,
                        "CardType": this.storedCards.CardType,
                        "AllCards": []
                    };
                } else {
                    userDefinedCard = {
                        "CardCount": this.storedCards.length,
                        "CardType": this.getSelectedCardData().selectedcardType,
                        "AllCards": this.storedCards
                    };
                }
                return userDefinedCard;
            },

            /**
             * Get Data
             *
             * @returns {{additional_data: {cc_exp_month: (*|jQuery), ebzc_avs_zip: (*|jQuery), ebzc_option: getData.ebzc_option, ebzc_cust_id: getData.ebzc_cust_id, cc_exp_year: (*|jQuery), ach_routing: (*|jQuery), cc_cid: *, cc_type: *, paymentToken: *, ebzc_method_id: *, ebzc_save_payment: *, ach_type: (*|jQuery), ebzc_avs_street: (*|jQuery), cc_owner: (*|jQuery), rec_admin: string, cc_number: *, ebzc_option_type: (string)}, method: *}}
             */
            getData: function () {

                //  let ebzcMethodId = this.ebzc_option === 'new' && this.ebzc_payment_option === 'credit_card' ? '' : this.selectedCardToken();
                let ebzcMethodId = this.ebzc_option === 'new' && this.ebzc_payment_option === 'credit_card'
                    ? '' : this.selectedCardToken();
                // this.transactionInfo = "";

                let orgEbzcPaymentOption = this.ebzc_payment_option;
                let orgTransactionInfo = this.transactionInfo;


                if (typeof ($('#' + this.getCode() + '_hosted_payment_option')) !== 'undefined') {
                    //  this.ebzc_option = $('#' + this.getCode() + '_hosted_payment_option').val();
                    if (parseInt(this.paymentFormType) === 2) {
                        this.ebzc_option = $('#' + this.getCode() + '_hosted_payment_option').val();
                    }

                    this.ebzc_payment_option = $('#' + this.getCode() + '_hosted_payment_option_type').val() ?? orgEbzcPaymentOption;
                    if (parseInt(this.ebzc_payment_option) === 1) {
                        this.ebzc_payment_option = "Tokenized_Only";
                    } else {
                        this.ebzc_payment_option = orgEbzcPaymentOption;
                    }
                    this.transactionInfo = $('#' + this.getCode() + '_hosted_payment_response').val() ?? orgTransactionInfo;
                    // console.log(["transactionInfo", $('#' + this.getCode() + '_hosted_payment_response').val()]);
                }


                this.additionalData = {
                    'cc_cid': this.creditCardVerificationNumber(),
                    'cc_type': $('#' + this.getCode() + '_cc_type').val(),
                    'cc_exp_year': $('#' + this.getCode() + '_expiration_yr').val(), //this.creditCardExpYear(),
                    'cc_exp_month': $('#' + this.getCode() + '_expiration').val(), //this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber(),
                    'ach_routing': $('#' + this.getCode() + '_cc_routing').val(),
                    'ach_type': $('#' + this.getCode() + '_ach_type').val(),
                    'cc_owner': $('#' + this.getCode() + '_cc_owner').val(),
                    'ebzc_avs_street': $('#' + this.getCode() + '_avs_street').val(),
                    'ebzc_avs_zip': $('#' + this.getCode() + '_avs_zip').val(),
                    'ebzc_option': this.ebzc_option,
                    'ebzc_method_id': ebzcMethodId,//this.selectedCardToken(),
                    'ebzc_cust_id': this.getEbzcCustId(),
                    'ebzc_save_payment': this.storeInVault(),
                    'paymentToken': this.paymentToken(),
                    'ebzc_option_type': this.ebzc_payment_option,
                    'rec_admin': this.isRecurringEnabled,
                    'transaction_info': this.transactionInfo

                };


                return {
                    'method': this.getCode(),
                    'additional_data': this.additionalData
                };
            },

            /**
             * Get selected card all data
             *
             * @returns {{selectedcardType: null, SelectedCardCvv: *, paymentMethod: null}}
             */
            getSelectedCardData: function () {
                var selectId = $('#' + this.getCode() + '_ebzc_method').val(); // this.selectedCardToken();
                var selectedCardType = null;
                var paymentMethod = null;
                var cards_array = this.getStoredCards();

                for (var i = 0; i < cards_array.length; i++) {
                    var obj = cards_array[i];
                    if (obj.MethodID === selectId) {
                        selectedCardType = obj.CardType;
                        paymentMethod = obj;
                        break;
                    }
                }
                return {
                    'selectedcardType': selectedCardType,
                    'SelectedCardCvv': this.creditCardVerificationNumber().length,
                    'paymentMethod': paymentMethod,
                };
            },

            /**
             * Get selected card CVV length
             *
             * @returns {*}
             */
            getCardCvvLength: function () {
                return this.creditCardVerificationNumber().length;
            },

            /**
             * Get New method card type
             *
             * @returns {*}
             */
            getNewCardType: function () {
                return this.creditCardType();
            },

            /**
             * Will Clear CVV field by clicking Radio buttons
             */
            clearCvv: function () {
                $(".cvv").val('');
            },

            /**
             * Will Clear CVV field by selecting DDL options
             */
            clearCvvById: function () {
                $(".pmethodddl").change(function () {
                    $(".cvv").val('');
                });
            },

            /**
             * Save 3D Secure Data In Session
             *
             * @param responseData
             * @returns {Promise<unknown>}
             */
            save3DSecureDataInSession: function (responseData) {
                let self = this;

                return new Promise((theSuccess, theFailure) => {
                    let postData = {
                        cavv: responseData.cavv,
                        xid: responseData.xid,
                        eci: responseData.eciFlag,
                        pares: responseData.paResStatus,
                        dsTransactionId: responseData.dsTransactionId,
                    };

                    $.ajax({
                        url: self.save3DSecureDataUrl,
                        type: 'POST',
                        data: postData,
                        dataType: 'json',
                        cache: false,
                        /**
                         * Success when response is ready
                         *
                         * @param respData
                         */
                        success: function (respData) {
                            console.log((respData));

                            if (respData.success) {
                                theSuccess('ebiz3DSecureValidated');
                            }
                        },
                        /**
                         * Error
                         *
                         * @param error
                         */
                        error: function (error) {
                            //  console.log(error);
                            theFailure(error);
                        }
                    });
                });
            },

            /**
             * Validate & process 3D Secure feature
             *
             * @returns {Promise<unknown>}
             */
            ebiz3DSecureValidation: function () {
                let self = this;
                let eBiz3DSecure = window.eBiz3DSecure;

                return new Promise((theSuccess, theFailure) => {
                    try {
                        let formData = self.getData().additional_data;
                        let cardOptionType = (formData.ebzc_option_type === 'credit_card') || (formData.ebzc_option_type.toLowerCase() === 'cc');
                        let isNewCard = formData.ebzc_option === 'new' && cardOptionType;

                        /**
                         * To check 3DSecure feature is enabled by merchant or not
                         */
                        if (eBiz3DSecure.JWT === null || eBiz3DSecure.JWT === "" || !isNewCard) {
                            theSuccess('payWithout3DS');
                            return false;
                        }
                        if (typeof ebizMageConfig.paymentFormType !== "undefined" && parseInt(ebizMageConfig.paymentFormType) === 2) {
                            theSuccess('payWithout3DS');
                            return false;
                        }

                        let billingAddress = quote.billingAddress() ? quote.billingAddress() : {};

                        let cartTotal = typeof(quote.totals()["grand_total"]) !== "undefined" ?
                            quote.totals()["grand_total"] : 0;
                        let surchargeAmount = 0;
                        if (typeof(checkoutConfig.payment.ebizcharge.surchargeGrandTotal) !== "undefined") {
                            surchargeAmount =  checkoutConfig.payment.ebizcharge.surchargeGrandTotal;
                            if (parseFloat(surchargeAmount) > 0) {
                                cartTotal  = parseFloat(surchargeAmount) + parseFloat(cartTotal);
                            }
                        }
                        let cartTotalParts = cartTotal.toString().split(".");
                        let decimalLength = cartTotalParts[1] ? cartTotalParts[1].length : 0;
                        let startD;
                        let zeroDigits = "1";
                        for(startD = 0; startD < decimalLength; startD++){
                            zeroDigits += "0";
                        }
                        cartTotal = cartTotal.toFixed(2).toString() .replaceAll('.', '');

                        /**
                         * EBiz 3D Secure input data
                         *
                         * @type {InputData}
                         */
                        const inputData = class InputData {
                            static CardType = formData.cc_type;
                            static CardNumber = formData.cc_number;
                            //static NameOnCard = formData.cc_owner;
                            static ExpireMonth = formData.cc_exp_month;
                            static ExpireYear = formData.cc_exp_year;
                            //static SecurityCode = formData.cc_id;
                            static Amount = cartTotal;
                            static OrderNumber = self.quoteReservedOrderId;
                            static CurrencyCode = quote.totals()['base_currency_code'];
                            static BillingAddress = billingAddress.street[0] +
                                (typeof billingAddress.street[1] !== 'undefined' ? ' ' + billingAddress.street[1] : '');
                            static BillingCity = billingAddress.city;
                            static BillingCountryCode = billingAddress.countryId;
                            static BillingFirstName = billingAddress.firstname;
                            static BillingLastName = billingAddress.lastname;
                            static BillingPhone = billingAddress.telephone;
                            static BillingPostalCode = billingAddress.postcode;
                            static BillingState = billingAddress.regionCode;
                            static Zip = billingAddress.postcode;
                            static ShippingCountryCode = quote.shippingAddress().countryId;
                            static Email = quote.guestEmail ? quote.guestEmail :
                                typeof customer.customerData !== undefined ? customer.customerData.email : '';
                            static phone = billingAddress.telephone;
                            static TransactionType = 'C';
                            static TransactionMode = 'S';
                            static TaxAmount = parseFloat(quote.totals()['tax_amount']);
                        }
                        /**
                         * Process 3D Secure check
                         */
                        eBiz3DSecure.EBiz3DSecureCheck(inputData).then((successData) => {
                            self.save3DSecureDataInSession(successData).then((responseData) => {
                                theSuccess(responseData);
                            }).catch((failureData) => {
                                theFailure(failureData);
                            });
                        }).catch((failureData) => {
                            let response = 'Payment is not Authorized by 3D Secure.';
                            /**
                             * To unset 3D Secure session data if already set
                             */
                            self.save3DSecureDataInSession({}).then((responseData) => {
                            }).catch((failureData) => {
                            });

                            if (typeof failureData.bypass3DS2OnError !== 'undefined' && failureData.bypass3DS2OnError)
                            {
                                theSuccess('bypass3DS2OnError');
                            } else {
                                let errorMsg = eBiz3DSecure.GetErrorMessage(failureData);
                                theFailure(errorMsg ? errorMsg : response);
                            }
                        });
                    } catch (error) {
                      //  console.log(error);
                        theFailure(error);
                    }
                });
            },

            /**
             * To check is EBiz 3D Secure Enabled
             *
             * @returns {boolean}
             */
            is3DSecureEnabled: function () {
                return !!this.ebiz3DSecureEnabled;
            },

            /**
             * Show 3D Secure Error Message
             *
             * @param errorMessage
             */
            show3DSecureErrorMessage: function (errorMessage) {
                this.messageContainer.clear()
                this.ebiz3DSecureErrorDiv.show();
                $('html, body').animate({scrollTop: 0}, 'slow');

                if (typeof errorMessage !== "undefined") {
                    var errorMsg = {
                        message: $t("Payment authorized error: " + errorMessage)
                    };

                    this.ebiz3DSecureErrorMessage(errorMessage);
                    this.messageContainer.addErrorMessage(errorMsg);
                }
            },

            /**
             * Get 3D Secure Error Message
             *
             * @returns {*}
             */
            get3DSecureErrorMessage: function () {
                return this.ebiz3DSecureErrorMessage();
            },

            /**
             * Prepare and process payment information
             *
             * @returns {boolean}
             */
            preparePayment: function () {

                let self = this;
                let checkoutForm = $(document.getElementById(self.payment_method_form));

                this.ebiz3DSecureErrorMessage(null);
                this.ebiz3DSecureErrorDiv.hide();

                let gRecaptcha = typeof grecaptcha !== "undefined" ? grecaptcha :null;
                let isRecaptchaSelected = false;
                try {
                    if (gRecaptcha) {
                        if (gRecaptcha.getResponse() !== "") {
                            isRecaptchaSelected = true;
                        }
                    }
                } catch (error){
                    console.warn("reCAPTCHA error:", error)
                }


                if (this.paymentOptionsDisabled()) {
                    alert('Please contact store admin use this payment method!')
                    return false;
                }
                if (!this.getUseVault()) {
                    this.updateEbizNewOption('cc');
                }

                if (this.validate()) {
                    let billStreetField = $(document.getElementById(this.billStreetFieldDivId));
                    billStreetField.css('padding-top', '0');

                    this.messageContainer.clear();
                    //this.placeOrder();

                    let googleRecaptchaResponse = this.getGoogleReCaptchaResponse();

                    if(gRecaptcha && !isRecaptchaSelected){
                     //   $('body').loader('hide');
                     //   checkoutForm.trigger('processStop');

                     //   let errorMsg = $t("ReCaptcha validation failed, please try again");
                      //  self.show3DSecureErrorMessage(errorMsg);
                      //  return;
                    }


                    /**
                     * Process 3D Secure feature
                     */
                    if (this.ebiz3DSecureEnabled) {

                        checkoutForm.trigger('processStart');

                        self.ebiz3DSecureValidation().then(response => {
                            $('body').loader('hide');
                            checkoutForm.trigger('processStop');

                            if (response === 'payWithout3DS' ||
                                response === 'bypass3DS2OnError' ||
                                response === 'ebiz3DSecureValidated') {
                                /** place order in case of success **/
                                self.placeOrder();

                            } else {
                                /** show error messages in case of issue or error **/
                                self.show3DSecureErrorMessage(response);
                            }
                        }).catch(error => {
                            $('body').loader('hide');
                            checkoutForm.trigger('processStop');

                            self.show3DSecureErrorMessage(error);
                        });

                    } else {


                        this.placeOrder();
                    }

                    //let checkoutModel = this;

                    // self.validateAvsCvvZipCode();

                    /**
                     *  Prepare PCi Compliance Order Data
                     * @type {{}}
                     */
                    //let orderData = this.preparePciComplianceOrderData($, customer, quote);

                    /**
                     * Place Transaction directly to EBizcharge and then Get back
                     * and Place order at Magento Locally
                     * Fully PCI Compliance based Order Placement
                     *
                     * @type {boolean}
                     */
                    //let pciOrderResp = EBizSoapApiClientModel.placePciComplianceOrder($, orderData, checkoutModel);
                } else {
                    /**
                     * Expiration date error messages divs styling
                     */
                    let expMonthErrorMsg = $(document.getElementById(this.expMonthErrorMsgDivId));
                    let expYearErrorMsg = $(document.getElementById(this.expYearErrorMsgDivId));
                    let billStreetField = $(document.getElementById(this.billStreetFieldDivId));

                    if (expMonthErrorMsg.length && !expMonthErrorMsg.is(':hidden') &&
                        (!expYearErrorMsg.length || expYearErrorMsg.is(':hidden'))) {
                        billStreetField.css('padding-top', '28.42px');
                        expMonthErrorMsg.css('position', 'absolute');
                    } else {
                        billStreetField.css('padding-top', '0');
                        expMonthErrorMsg.css('position', 'relative');
                    }
                }
            },

            /**
             * Prepare PCI Compliance Order Data
             *
             * @param $
             * @param customer
             * @param quote
             * @returns {{}}
             */
            preparePciComplianceOrderData: function ($, customer, quote) {

                let orderData = {
                    isGuest: this.isGuest(),
                    customerData: {
                        firstName: this.getFirstName(),
                        lastName: this.getLastName(),
                        address: this.getAddress(),
                        phone: this.getPhone(),
                        email: this.getEmail(),
                        billingAddress: quote.billingAddress(),
                        shippingAddress: quote.shippingAddress(),
                        data: this.getCustomerData()
                    },
                    quote: quote.totals(),
                    formData: this.getData(),
                    items: quote.totals().items,
                    shippingMethod: quote.shippingMethod(),
                    paymentMethod: quote.paymentMethod()

                }
                return orderData;
            },

            /**
             * Validate Avs Cvv Zip Code
             *
             * @returns {boolean}
             */
            validateAvsCvvZipCode: function () {
                var self = this;
                let checkoutForm = $(document.getElementById(this.payment_method_form));
                let isAvsCvvZipEnabled = ebizMageConfig.isAvsCvvZipEnabled;

                if (isAvsCvvZipEnabled) {
                    EBizChargeAvsCvvValidator.validateAvsCvvActionUrl = ebizMageConfig.cardAvsCvvValidationUrl;
                    EBizChargeAvsCvvValidator.popupModal = modal;
                    EBizChargeAvsCvvValidator.popUpModalId = "avs-cvv-warnings-panel";
                    EBizChargeAvsCvvValidator.popupTitle = $.mage.__("Security mismatch dialog box...");
                    EBizChargeAvsCvvValidator.avsAddressMsg = "avs-address-msg";
                    EBizChargeAvsCvvValidator.avsPostalCodeMsg = "avs-postal-code-msg";
                    EBizChargeAvsCvvValidator.cvvCvv2Msg = "cvv-cvv2-msg";
                    EBizChargeAvsCvvValidator.cardMessageResponse = "card-message-response";
                    EBizChargeAvsCvvValidator.avsSaveActionButton = "avs-response-save-button";
                    EBizChargeAvsCvvValidator.isCheckout = true;
                    EBizChargeAvsCvvValidator.checkoutOrderObj = self;

                    /** Check if the AVS and CVV are enabled **/
                    checkoutForm.trigger('processStop');

                    EBizChargeAvsCvvValidator.popUpButtons = [
                        {
                            text: $.mage.__('Place Order Anyway'),
                            class: 'action primary avs-response-save-button',
                            id: 'avs-response-save-button',
                            click: function (evt) {
                                self.placeOrder();
                                this.closeModal();
                            }
                        }, {
                            text: $.mage.__('Cancel'),
                            class: ' avs-response-cancel-button ',
                            id: 'avs-response-cancel-button',
                            click: function (evt) {
                                this.closeModal();
                            }
                        }
                    ];
                    var avsCvvZipPromise = EBizChargeAvsCvvValidator.processValidAddToCardAction(
                        $,
                        EBizChargeAvsCvvValidator.validateAvsCvvActionUrl,
                        checkoutForm,
                        modal
                    );
                }
                return avsCvvZipPromise;
            },

            /**
             * Get Stored Accounts
             *
             * @returns {[]}
             */
            getStoredAccounts: function () {
                return this.storedAccounts;
            },

            /**
             * Get Stored Cards
             *
             * @returns {[]}
             */
            getStoredCards: function () {
                return this.storedCards;
            },

            /**
             * Function update Ebiz Saved Option
             */
            updateEbizSavedOption: function (check = false) {
                this.ebzc_option = 'saved';
                this.addNewCard(false);
                this.updateSavedCard(false);
                this.useSavedCard(true);
                this.paymentToken(true);
                this.showAddNewCreditCardPanel(false);
                this.clearCvv();
                this.clearCvvById();

                if (check) {
                    this.calculateSurcharge();
                }
            },

            /**
             * Show Add New Credit Card Panel
             *
             * @param isPanelAllowed
             */
            showAddNewCreditCardPanel(isPanelAllowed) {
                if (isPanelAllowed) {
                    $("#" + this.payment_type_card_new_panel).show();
                    $("#ebizcharge_ebizcharge_save_payment").show();
                    $(".save-card-label").show();
                } else {
                    $("#" + this.payment_type_card_new_panel).hide();
                    $("#ebizcharge_ebizcharge_save_payment").hide();
                    $(".save-card-label").hide();
                }
            },

            /**
             * Update Ebiz Update Option
             */
            updateEbizUpdateOption: function (check = false) {
                this.ebzc_option = 'update';
                this.useSavedCard(false);
                this.addNewCard(false);
                this.addNewAccount(false);
                this.updateSavedCard(true);
                this.paymentToken(true);
                this.clearCvv();
                this.clearCvvById();
                this.updateCardDetails();
                this.showAddNewCreditCardPanel(false);

                if (check) {
                    this.calculateSurcharge();
                }
            },

            /**
             * Update Card Details
             */
            updateCardDetails: function () {
                var selectedCard = this.getSelectedCardData();
                if (selectedCard != null && selectedCard.paymentMethod != null) {
                    var cardData = selectedCard.paymentMethod;
                    var expiryData = cardData.CardExpiration;
                    var expiry = expiryData.split('-');
                    var month = expiry[1];
                    if (month < 10) {
                        month = expiry[1].substr(1)
                    }

                    $('#' + this.getCode() + '_avs_street').val(cardData.AvsStreet);
                    $('#' + this.getCode() + '_avs_zip').val(cardData.AvsZip);
                    $('#' + this.getCode() + '_expiration_yr').val(expiry[0]);
                    $('#' + this.getCode() + '_expiration').val(month);
                }
            },

            /**
             * Card Changed
             *
             * @param l
             */
            cardChanged: function (l) {
                if (typeof l != 'undefined') {
                    this.updateCardDetails();
                    this.calculateSurcharge();
                }
            },

            /**
             * Update Ebiz New Option
             */
            updateEbizNewOption: function (check = '') {

                this.ebzc_option = 'new';
                this.showAddNewCreditCardPanel(true);
                this.useSavedCard(false);
                this.updateSavedCard(false);
                this.addNewCard(true);
                this.addNewAccount(true);
                this.paymentToken(false);
                var matchdiv = $('.divSaved').html();
                if (typeof matchdiv === "undefined") {
                    // if statment here
                } else {
                    this.clearCvv();
                }

                // If call from surcharge
                if (check === 'cc') {
                    this.checkNewCardSurcharge();
                }
            },

            /**
             * Check if auto save card option is enabled or not from configuration then
             * set checkbox enable/disable
             *
             * @returns {boolean}
             */
            checkSaveCardInformation: function () {
                return !this.saveCard;
            },

            /**
             * Set 'Save Card Information' checkbox marked if auto save card option is enabled from configuration
             */
            markedCheckedSaveCardInfo: function () {
                if (this.saveCard) {
                    this.storeInVault(true);
                }
            },

            /**
             * Get Ebz Cust Id
             *
             * @returns {string}
             */
            getEbzcCustId: function () {
                return this.ebzc_cust_id;
            },

            /**
             * Validate
             *
             * @returns {*}
             */
            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            /**
             * Bold the substring in a string
             *
             * @param string
             * @param substring
             * @returns {*}
             */
            boldString: function (string, substring) {
                let strRegExp = new RegExp(substring, 'g');
                return string.replace(strRegExp, '<b>' + substring + '</b>');
            },

            /**
             * Check Ebiz Charge Select
             */
            checkEbizChargeSelect: function () {
                let self = this;
                $('input[name="payment[method]"]').click(function (e) {
                    if (this.value === self.getCode()) {
                        self.checkNewCardSurcharge();
                    } else {
                        surchargeAction().unsetSurchargeValues();
                    }
                });
            },

            /**
             * Surcharge message on checkout
             *
             * @returns {number|string}
             */
            surchargeMessage: function () {
                if (this.surchargeEnabled && this.surchargeTermsNote) {
                    return this.boldString(
                        this.surchargeTermsNote,
                        'credit card'
                    );
                }

                return '';
            },

            /**
             * Check surcharge on entering new card
             */
            checkNewCardSurcharge: function () {
                if (!this.surchargeEnabled) {
                    return;
                }

                let cardNo = $('#' + this.getCode() + '_cc_number').val();

                if (cardNo && cardNo.length >= 16) {
                    this.calculateSurcharge();
                } else {
                    if ($('#' + this.getCode()).is(':checked') && this.useCC()) {
                        surchargeAction().setSurchargeValuesForNew();
                    }
                    //surchargeAction().unsetSurchargeValues();
                }
            },

            /**
             * Calculate surcharge on card
             */
            calculateSurcharge: function () {
                let checkoutForm = $(document.getElementById(this.payment_method_form));
                let calculateSurchargeAjaxUrl = this.calculateSurchargeAjaxUrl;

                let ccCard = $('#' + this.getCode() + '_cc_number').val();
                let avsZip = $('#' + this.getCode() + '_avs_zip').val();
                let methodId = $('#' + this.getCode() + '_ebzc_method').val();
                let payByBankTab = $(document.getElementById('use_credit_card')).is(':checked');

                if (!this.surchargeEnabled ||
                    !calculateSurchargeAjaxUrl ||
                    !this.selectedPaymentMethod ||
                    !payByBankTab ||
                    this.surchargeTypeId !== this.surchargeTypeDailyDiscount) {
                    return;
                }

                if ((ccCard && avsZip) || methodId) {
                    $.ajax({
                        url: calculateSurchargeAjaxUrl,
                        type: 'POST',
                        data: checkoutForm.serializeArray(),
                        dataType: 'json',
                        cache: false,
                        /**
                         * Before Send
                         */
                        beforeSend: function () {
                            checkoutForm.trigger('processStart');
                            $('body').loader('show');
                            if (typeof (window.checkoutConfig.payment.ebizcharge) !== "undefined") {
                                window.checkoutConfig.payment.ebizcharge.isSurchargeCalculated = 1;
                                window.checkoutConfig.payment.ebizcharge.surchargeGrandTotal = 0;
                            }
                        },
                        /**
                         * Success when response is ready
                         *
                         * @param respData
                         */
                        success: function (respData) {
                            //checkoutForm.trigger('processStop');
                            //$('body').loader('hide');
                            // console.log((respData));

                            if (respData.surchargeEnabled) {
                                let deferred = $.Deferred();
                                getTotalsAction([], deferred);
                                surchargeAction().showSurcharge(respData);

                                if (typeof (window.checkoutConfig.payment.ebizcharge) !== "undefined") {
                                    window.checkoutConfig.payment.ebizcharge.isSurchargeCalculated = 1;
                                    if (typeof (respData.surchargeAmount) !== "undefined") {
                                        window.checkoutConfig.payment.ebizcharge.surchargeGrandTotal = respData.surchargeAmount;
                                    }
                                }

                            } else {
                                surchargeAction().setSurchargeValuesForNew();
                            }
                        },
                        /**
                         * When the whole process completed
                         *
                         * @param respData
                         */
                        complete: function (respData) {
                            checkoutForm.trigger('processStop');
                            $('body').loader('hide');
                        },
                        /**
                         * Error
                         *
                         * @param error
                         */
                        error: function (error) {
                            // console.log(error);
                            checkoutForm.trigger('processStop');
                            $('body').loader('hide');
                        }
                    });
                }
            }
        });
    }
);
