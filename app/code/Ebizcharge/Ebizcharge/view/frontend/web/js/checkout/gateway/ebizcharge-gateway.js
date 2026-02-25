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
    'Magento_Ui/js/modal/modal'
], function ($, _, modalToggle, email, trans, domReady, modal) {
    'use strict';

    return function (configModelData) {
        //console.log(configModelData);
        /**
         * Validating of AVS CVV Code
         */
        return EBizSoapApiClientModel._init($, _, modalToggle, trans, domReady, modal, configModelData);
    }
});

/**
 *
 *
 * @type {{renderException: (function(*, *=, *=): EBizSoapApiClientModel), addTransactionsToDb: (function(*=, *=, *=): Promise<unknown>), _init: EBizSoapApiClientModel._init, removeException: (function(*): EBizSoapApiClientModel), prepareResponseNode: (function(*, *=): *), getDataValue: (function(*=): HTMLElement), prepareParams: EBizSoapApiClientModel.prepareParams, getEbizCustomer: (function(*, *): Promise<unknown>), validateForm: EBizSoapApiClientModel.validateForm, decodeStr: (function(*=): string), addNewPaymentMethod: EBizSoapApiClientModel.addNewPaymentMethod, addEbizCustomer: (function(*, *): Promise<unknown>), sendAjaxRequest: (function(*=, *=, *=): Promise<unknown>), getData: EBizSoapApiClientModel.getData, getLineItems: (function(*, *): *), encodeStr: (function(*=): string), runCustomerTransactionSoapRequest: (function(*=, *=): Promise<unknown>), messageProcessor: EBizSoapApiClientModel.messageProcessor, addCustomerPaymentMethod: (function(*, *): Promise<unknown>), setData: (function(*): EBizSoapApiClientModel), redirectAction: (function(*, *): boolean), publishDataAction(*=, *=, *=): void, runTransactionSoapRequest: (function(*=, *=): Promise<unknown>), setDefaultPaymentMethod: (function(*, *): Promise<unknown>), soapGatewayUrl: null, placePciComplianceOrder: EBizSoapApiClientModel.placePciComplianceOrder, addEbizchargeDataToCustomer: EBizSoapApiClientModel.addEbizchargeDataToCustomer, preparePCITransaction: (function(*=, *=, *=): EBizSoapApiClientModel), redirectAtSuccessPage: EBizSoapApiClientModel.redirectAtSuccessPage, getCardType: EBizSoapApiClientModel.getCardType, getEbizSOAPClient: (function(*, *=): {beforeSend: beforeSend, headers: {SOAPAction: string}, cache: boolean, processData: boolean, data: *, dataType: string, crossOrigin: boolean, crossDomain: boolean, type: string, contentType: string, showLoader: boolean, url: string})}}
 */
let EBizSoapApiClientModel = {

    soapApiGatewayUrl: null,
    paymentOptionTypeAch: null,
    paymentOptionTypeCreditCard: null,
    soapGateway: null,
    isLoggedIn: null,
    isGuest: null,
    formData: null,
    quoteRequestData: null,
    shippingMethod: null,
    paymentMethod: null,
    customer: null,
    quote: null,
    email: null,
    billingAddress: null,
    shippingAddress: null,
    firstName: null,
    lastName: null,
    phone: null,
    address: null,
    ebizOption: null,
    ebizOptionType: null,
    ebizMethodId: null,
    ebizCustomerId: null,
    ebizSavePayment: null,
    command: null,
    customerId: null,
    eBk: null,
    eBu: null,
    eBp: null,
    alg: null,
    soapLineItems: null,
    lineItems: null,
    customerToken: null,
    isRecurring: null,
    inventoryLocation: null,
    ignoreDuplicate: null,
    isNonTax: null,
    taxAmount: null,
    table: null,
    subTotal: null,
    shippingAmount: null,
    shipZipCode: null,
    sessionId: null,
    poNumber: null,
    orderNumber: null,
    invoiceNumber: null,
    dutyAmount: null,
    discountAmount: null,
    orderComments: null,
    orderDescription: null,
    orderCurrency: null,
    merchantCode: null,
    orderedAmount: null,
    allowPartialAuth: null,
    terminal: null,
    orderTip: null,
    merchantName: null,
    merchantReceiptName: null,
    softwareId: null,
    merchantReceipt: null,
    customerReceiptName: null,
    customerReceiptEmail: null,
    customerReceiptNumber: null,
    clientIp: null,
    cardCode: null,
    saleCommand: null,
    isCustomFields: null,
    isAuthExpired: null,
    authCode: null,
    transactionReferenceNumber: null,
    accountHolder: null,
    ebizCustomerInternalId: null,
    current_date: null,
    ajaxAddPciTransactionUrl: null,
    isPciTransactions: null,
    paymentRefNumber: null,
    paymentMethodName: null,


    /**
     * Init function
     *
     * @param $
     * @param quote
     * @param paymentModel
     * @returns {boolean}
     * @private
     */
    _init: function ($, _, modalToggle, trans, domReady, modal, configModelData) {

        // console.log(configModelData);
        this.soapGateway = configModelData.soapConfig;
        this.soapApiGatewayUrl = this.soapGateway.pci_action_gateway_url;
        this.paymentOptionTypeAch = "ACH";
        this.paymentOptionTypeCreditCard = "credit_card";

    },
    /**
     * prepare InitParams
     *
     * @param $
     * @param quoteOrderData
     */
    prepareInitParams: function ($, quoteOrderData = {}) {

        //console.log(quoteOrderData);

        this.paymentRefNumber = null;
        this.paymentOptionTypeAch = "ACH";
        this.paymentOptionTypeCreditCard = "credit_card";
        this.paymentMethodName = '';
        this.soapApiGatewayUrl = this.soapGateway.pci_action_gateway_url;
        this.isLoggedIn = quoteOrderData.isGuest ? false : true;
        this.isGuest = quoteOrderData.isGuest;
        this.quoteRequestData = quoteOrderData;
        this.formData = quoteOrderData.formData.additional_data;
        let cardData = EBizSoapApiClientModel.formData;
        this.shippingMethod = quoteOrderData.shippingMethod;
        this.paymentMethod = quoteOrderData.paymentMethod;
        this.customer = quoteOrderData.customerData;
        this.ebizCustomerInternalId = this.soapGateway.ebiz_customer_internal_id;
        this.quote = quoteOrderData.quote;
        this.email = quoteOrderData.customerData.email;
        this.billingAddress = quoteOrderData.customerData.billingAddress;
        this.shippingAddress = quoteOrderData.customerData.shippingAddress;
        this.createdAt = this.soapGateway.current_date;

        this.ebizOption = this.formData.ebzc_option;
        this.ebizOptionType = this.formData.ebzc_option_type;
        this.ebizMethodId = this.formData.ebzc_method_id;
        this.ebizCustomerId = this.formData.ebzc_cust_id;
        this.ebizSavePayment = this.formData.ebzc_save_payment;
        this.firstName = quoteOrderData.customerData.firstName;
        this.lastName = quoteOrderData.customerData.lastName;
        this.phone = quoteOrderData.customerData.phone;
        this.address = quoteOrderData.customerData.address;
        this.ebizOption = this.formData.ebzc_option;
        this.ebizOptionType = this.formData.ebzc_option_type;

        this.ebizSavePayment = this.formData.ebzc_save_payment;
        this.orderNumber = this.soapGateway.reserve_order_id;
        this.customerId = this.isLoggedIn === true ? this.soapGateway.customer_id : "guest";
        this.lineItems = this.soapGateway.line_items;
        this.soapLineItems = EBizSoapApiClientModel.getLineItems(this.lineItems);
        this.eBk = this.soapGateway.ebk;
        this.eBu = this.soapGateway.ebu;
        this.eBp = this.soapGateway.ebp;
        this.alg = this.soapGateway.alg;
        this.softwareId = this.soapGateway.software_id;
        this.customerToken = this.soapGateway.customer_token;
        this.isRecurring = this.soapGateway.is_recurred;
        this.inventoryLocation = this.soapGateway.inventory_location;
        this.ignoreDuplicate = this.soapGateway.ignore_duplicate;
        this.taxAmount = this.quote.tax_amount;
        this.table = this.soapGateway.table;
        this.grandTotal = this.quote.grand_total;
        this.subTotal = this.quote.subtotal_incl_tax;
        this.shippingAmount = this.quote.shipping_incl_tax;
        this.shipZipCode = this.billingAddress.postcode;
        this.sessionId = this.soapGateway.session_id;
        this.poNumber = this.soapGateway.po_number;
        this.invoiceNumber = this.soapGateway.invoice_number;
        this.dutyAmount = this.soapGateway.duty_amount;
        this.discountAmount = this.quote.discount_amount;
        this.orderComments = this.soapGateway.order_comments;
        this.orderDescription = this.soapGateway.order_description;
        this.orderCurrency = this.soapGateway.order_currency;
        this.merchantCode = this.soapGateway.merchant_name;
        this.ordredAmount = this.quote.grand_total;
        this.allowPartialAuth = this.soapGateway.allow_partial_auth;
        this.terminal = this.soapGateway.terminal;
        this.orderTip = this.soapGateway.order_tip;
        this.merchantName = this.soapGateway.merchant_name
        this.merchantReceiptName = this.soapGateway.merchant_receipt_name;
        this.merchantReceipt = this.soapGateway.merchant_receipt;
        this.customerReceiptName = this.soapGateway.customer_receipt_name;
        this.customerReceiptEmail = this.soapGateway.customer_receipt_email;
        this.customerReceiptNumber = this.soapGateway.customer_receipt_number;
        this.clientIp = this.soapGateway.client_ip;
        this.cardCode = cardData.cc_cid;
        this.saleCommand = this.soapGateway.sale_command;

        if (this.formData.ebzc_option_type === this.paymentOptionTypeAch) {
            this.cardCode = "";
            this.saleCommand = "Check";
        }


        this.isNonTax = this.soapGateway.non_tax;
        this.isCustomFields = 0;
        this.isAuthExpired = "";
        this.authCode = typeof (this.formData.authCode) !== "undefined" ? this.formData.authCode : "";
        this.transactionReferenceNumber = typeof (this.formData.transRefNumber) !== "undefined" ? this.formData.transRefNumber : "";
        this.accountHolder = this.formData.cc_owner;
        this.ajaxAddPciTransactionUrl = this.soapGateway.add_pci_transactions_url;
        this.isPciTransactions = this.soapGateway.is_pci_enabled;


        //console.log(this);
    },
    /**
     * get Soap Client Keys
     *
     * @returns {string}
     */
    getSoapClientKeys: function () {
        let soapSecurityKeys =
            '         <!--Optional:-->' +
            '         <ebiz:securityToken>' +
            '            <ebiz:SecurityId>' + EBizSoapApiClientModel.dtr(EBizSoapApiClientModel.eBk) + '</ebiz:SecurityId>' +
            '            <ebiz:UserId>' + EBizSoapApiClientModel.dtr(EBizSoapApiClientModel.eBu) + '</ebiz:UserId>' +
            '            <ebiz:Password>' + EBizSoapApiClientModel.dtr(EBizSoapApiClientModel.eBu) + '</ebiz:Password>' +
            '          </ebiz:securityToken>';

        return soapSecurityKeys;
    },

    /**
     * Set Data
     * @param inputData
     * @returns {EBizSoapApiClientModel}
     */
    setData: function (inputData) {
        this.input_data = inputData;
        return this;
    },
    /**
     * Get Data
     * @param vKey
     * @returns {EBizSoapApiClientModel|*}
     */
    getData: function (vKey) {
        if (vkey) {
            return this.input_data.vKey;
        }
        return this;
    },
    /**
     * Get Data Value
     * @param vKey
     * @returns {HTMLElement}
     */
    getDataValue: function (vKey) {
        return document.getElementById(vKey);
    },
    /**
     * Create Cross Domain Request
     *
     * @param url
     * @param handler
     * @returns {XMLHttpRequest | window.XDomainRequest}
     */
    createCrossDomainRequest: function (url, handler) {
        var request;

        if (isIE8) {
            request = new window.XDomainRequest();
        } else {
            request = new XMLHttpRequest();
        }
        return request;
    },
    /**
     * Call Other Domain
     */
    callOtherDomain: function () {
        if (invocation) {
            if (isIE8) {
                invocation.onload = outputResult;
                invocation.open("GET", url, true);
                invocation.send();
            } else {
                invocation.open('GET', url, true);
                invocation.onreadystatechange = handler;
                invocation.send();
            }
        } else {
            var text = "No Invocation TookPlace At All";
            var textNode = document.createTextNode(text);
            var textDiv = document.getElementById("textDiv");
            textDiv.appendChild(textNode);
        }
    },
    /**
     * Handler
     *
     * @param evtXHR
     */
    handler: function (evtXHR) {
        if (invocation.readyState == 4) {
            if (invocation.status == 200) {
                outputResult();
            } else {
                alert("Invocation Errors Occurred");
            }
        }
    },

    /**
     * Output Result
     *
     */
    outputResult: function () {
        var response = invocation.responseText;
        var textDiv = document.getElementById("textDiv");
        textDiv.innerHTML += response;
    },

    /**
     * Process Ajax Order Request
     *
     * @param $
     * @param checkoutModel
     * @param transactionResponse
     * @param isSuccess
     * @returns {boolean}
     */
    processAjaxOrderRequest: function ($, checkoutModel = "", transactionResponse = {}, isSuccess = true) {
        /**
         *
         * @type {boolean}
         */
        transactionResponse.transaction_data.isSuccess = isSuccess;

        /**
         *
         * @type {null|EBizSoapApiClientModel.formData.ebzc_method_id}
         */
        let payment_method_id = EBizSoapApiClientModel.ebizMethodId;

        transactionResponse.transaction_data.payment_method_id = payment_method_id;
        let formData = transactionResponse.transaction_data;

        /**
         * Send ajax Process Request
         */
        EBizSoapApiClientModel.sendAjaxProcessRequest($, EBizSoapApiClientModel.ajaxAddPciTransactionUrl, formData)
            .then((transactionResp) => {
                // console.log(["Success: ", transactionResp, formData]);
                //  console.log(EBizSoapApiClientModel.paymentRefNumber);
                if (checkoutModel.isMultiShippingCheckout) {
                    checkoutModel.forceSubmit = true;
                    checkoutModel.multiShippingCheckoutFormId.submit();
                }
                checkoutModel.placeOrder();
                return true;

            })
            .catch((transactionRespError) => {
                //  console.log(["error: ", transactionRespError]);
                checkoutModel.placeOrder();
                // alert($.mage.__("Something went wrong, please double check your entries and try again. "));
                return false;

            })

        ;
        return true;
    },


    /**
     * Place PCi Compliance Order
     *
     * @param $
     * @param quoteOrderData
     * @param checkoutModel
     * @returns {boolean}
     */
    placePciComplianceOrder: function ($, quoteOrderData = null, checkoutModel = null) {

        /**
         * prepare and init Params and
         * assign variables
         */
        EBizSoapApiClientModel.prepareInitParams($, quoteOrderData);

        //  if(EBizSoapApiClientModel.isPciTransactions !== true || this.ebizOption !== "new"){
        if (EBizSoapApiClientModel.isPciTransactions !== true) {
            checkoutModel.placeOrder();
            return false;
        }

        /**
         * if is Customer is Logged In
         */
        if (this.isLoggedIn === true) {
            /** is save payment method is Yes
             * at the back end console
             * and customer is logged in
             * **/

            if (this.ebizOption === "new") {
                EBizSoapApiClientModel.ebizMethodId = null;
                /**
                 * if save payment method is active
                 */
                if (EBizSoapApiClientModel.formData.ebzc_save_payment === true) {
                    EBizSoapApiClientModel.addCustomerPaymentMethod($, quoteOrderData)
                        .then((addCustomerPaymentMethodResp) => {

                            let paymentMethodId = addCustomerPaymentMethodResp.payment_method_id;
                            let error = addCustomerPaymentMethodResp.error;

                            if (error === false && paymentMethodId) {

                                EBizSoapApiClientModel.ebizMethodId = paymentMethodId;
                                EBizSoapApiClientModel.runCustomerTransactionSoapRequest($, quoteOrderData)
                                    .then((runCustomerTransactionResp) => {
                                        //   console.log(runCustomerTransactionResp);
                                        let transError = runCustomerTransactionResp.error;
                                        let isSuccess = false;

                                        if (transError === false) {
                                            isSuccess = true;
                                        }
                                        EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runCustomerTransactionResp, isSuccess);

                                    })
                                    .catch((runCustomerTransactionError) => {
                                        let isSuccess = false;
                                        EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runCustomerTransactionError, isSuccess);
                                        return false;
                                    });
                            }

                        })
                        .catch((addCustomerPaymentMethodError) => {
                            let isSuccess = false;
                            EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, addCustomerPaymentMethodError, isSuccess);
                            return false;
                        });
                } else {
                    /**
                     * if saved payment method
                     * is not active
                     */

                    EBizSoapApiClientModel.runTransactionSoapRequest($, quoteOrderData)
                        .then((runTransactionResp) => {
                                let isSuccess = true;
                                EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runTransactionResp, isSuccess);
                            }
                        )
                        .catch((runTransactionError) => {
                                let isSuccess = false;
                                EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runTransactionError, isSuccess);
                            }
                        );
                }

            } else if (this.ebizOption === "saved" || this.ebizOption === "updated") {
                /**
                 * If credit card type is saved & updated
                 * Then run customer transaction with
                 * Payment Method
                 */

                /**
                 * Soap Run Customer Transaction
                 * via Soap Request
                 * **/
                EBizSoapApiClientModel.runCustomerTransactionSoapRequest($, quoteOrderData)
                    .then(successRunCustomerTransactionResponse => {
                            let runCustomerTransactionResponse = successRunCustomerTransactionResponse;
                            let isSuccess = true;
                            EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runCustomerTransactionResponse, isSuccess);
                            return true;
                        }
                    ).catch(
                    (errorCustomerRunTransactionData) => {
                        let errorCustomerRunTransactionResponse = errorCustomerRunTransactionData;
                        let isSuccess = false;
                        EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, errorCustomerRunTransactionResponse, isSuccess);

                        return false;
                    }
                );

            } else {
                /**
                 *
                 * Run Transaction Guest customers
                 */
                EBizSoapApiClientModel.runTransactionSoapRequest($, quoteOrderData)
                    .then((runSuccessTransactionResponse) => {
                            let runTransactionResponse = runSuccessTransactionResponse;
                            let isSuccess = true;
                            EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runTransactionResponse, isSuccess);

                        }
                    ).catch((runTransactionErrorResponse) => {
                        let isSuccess = false;
                        EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runTransactionErrorResponse, isSuccess);

                    }
                );

            }

        } else {
            //console.log(this.isLoggedIn);
            /** run transaction Soap request in the case of Guest Customer **/
            EBizSoapApiClientModel.runTransactionSoapRequest($, quoteOrderData)
                .then((runSuccessTransactionResponse) => {
                        let runTransactionResponse = runSuccessTransactionResponse;
                        let isSuccess = true;
                        EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runTransactionResponse, isSuccess);

                    }
                ).catch((runTransactionErrorResponse) => {
                    let isSuccess = false;
                    EBizSoapApiClientModel.processAjaxOrderRequest($, checkoutModel, runTransactionErrorResponse, isSuccess);

                }
            );
        }
        return false;
    },


    /**
     * Render Exception
     * @param $
     * @param errorTitle
     * @param exceptionError
     * @returns {EBizSoapApiClientModel}
     */
    renderException: function ($, errorTitle, exceptionError) {

        let checkoutProgressError = '<h4 id="exception-bar-breadcrumb">Error: ' + errorTitle + '</h4>';
        let checkoutProgressBar = $('.opc-progress-bar');
        messageContainer.addErrorMessage(errorTitle);
        /** Show Checkout Progress Error **/
        checkoutProgressBar.append(checkoutProgressError);

        /** disable the button at submit **/
        $('#checkout-submit-button').prop('disabled', false);

        if (typeof exceptionError !== 'undefined') {
            //console.log(exceptionError);
        }
        return this;

    }

    ,
    /**
     * Remove Expception
     * @param $
     * @returns {EBizSoapApiClientModel}
     */
    removeException: function ($) {
        let checkoutProgressError = $('#exception-bar-breadcrumb');
        let checkoutProgressBar = $('.opc-progress-bar');

        /** removing back Error **/
        if (typeof checkoutProgressError !== 'undefined') {
            checkoutProgressError.remove();
        }
        return this;
    }
    ,

    /** add Transactions to local DB **/
    addTransactionsToDb: function ($, addTransactionsUrl, requestParams) {
        /** setting request params variables **/
        requestParams.order_id = EBizSoapApiClientModel.order_id;
        requestParams.order_number = EBizSoapApiClientModel.order_number;
        requestParams.increment_id = EBizSoapApiClientModel.increment_id;
        requestParams.card_data = EBizSoapApiClientModel.card_data.additional_data;


        /*** adding Trnasactions to DB **/
        return EBizSoapApiClientModel.sendAjaxRequest($, addTransactionsUrl, requestParams).then(
            (successTransactionsResponse) => {
                let transactionResponse = successTransactionsResponse;
                return transactionResponse;

            }
        ).catch(
            (errorTransactionsResponse) => {
                let transactionResponse = errorTransactionsResponse;
                return transactionResponse;
            }
        );
    }
    ,
    redirectAtSuccessPage: function ($, requestParams) {
        window.location.href = EBizSoapApiClientModel.redirect_success_page + '/orderid/' + EBizSoapApiClientModel.increment_id;
    }
    ,

    /** adding new Payment Method **/
    /**
     * Add New Payment Method at Ebizcharge
     * @param $
     * @param requestParams
     * @returns {boolean}
     */
    addNewPaymentMethod: function ($, requestParams) {


        /** if is Default Payment **/
        var isDefaultPaymentId = typeof requestParams.default !== 'undefined' ? requestParams.default : 0;

        /** checking if customer doest not exist at
         * Gateway then add new customer at Gateway
         * **/
        requestParams.customer_id = EBizSoapApiClientModel.customer_id;

        /** Soap Processor Customer Id **/
        if (EBizSoapApiClientModel.customer_id !== null && !EBizSoapApiClientModel.ebiz_customer_token) {

            /** Soap Processor Add Ebizcharge Customer **/
            EBizSoapApiClientModel.addEbizCustomer($, requestParams)
                .then(addCustomerSuccessResponse => {
                        /** Ebiz customer Id **/
                        EBizSoapApiClientModel.ebiz_customer_id = EBizSoapApiClientModel.customer_id;

                        /** soap response to get customer **/
                        EBizSoapApiClientModel.getEbizCustomer($, requestParams)
                            .then(successGetCustomerResponse => {

                                    requestParams.ebiz_customer_internal_id = successGetCustomerResponse.ebiz_customer_internal_id;
                                    requestParams.ebiz_customer_token = successGetCustomerResponse.ebiz_customer_token;
                                    requestParams.ebiz_customer_id = successGetCustomerResponse.ebiz_customer_id;

                                    EBizSoapApiClientModel.sendAjaxRequest($, EBizSoapApiClientModel.add_update_payment_method_url, requestParams)
                                        .then(updatedCustomerExtrasResponse => {
                                            //console.log(updatedCustomerExtrasResponse)
                                        });

                                    /** adding payment Method Name **/
                                    EBizSoapApiClientModel.addCustomerPaymentMethod($, requestParams)
                                        .then(addCustomerPaymentMethodResponse => {

                                                if (typeof (addCustomerPaymentMethodResponse.payment_method_id) !== "undefined") {
                                                    /** payment Method Id **/
                                                    let paymentMethodId = addCustomerPaymentMethodResponse.payment_method_id;

                                                    let defaultPaymentMethodRequest = {
                                                        payment_method_id: paymentMethodId
                                                    };

                                                    if (isDefaultPaymentId) {
                                                        /** sending request for set default Payment Method Id **/
                                                        EBizSoapApiClientModel.setDefaultPaymentMethod($, defaultPaymentMethodRequest)
                                                            .then(setDefaultPaymentMethodSuccessResponse => {
                                                                    // console.log(setDefaultPaymentMethodSuccessResponse);
                                                                }
                                                            ).catch(setDefaultPaymentMethodErrorResponse => {
                                                            let errorTitle = "Exception occured during setting default payment method with Ebizcharge Gateway";
                                                            EBizSoapApiClientModel.renderException($, errorTitle, setDefaultPaymentMethodErrorResponse);
                                                        });

                                                    }
                                                    /** redirecting if
                                                     * payment Method is added
                                                     * **/
                                                    EBizSoapApiClientModel.redirectAction($, EBizSoapApiClientModel.card_listing_url);
                                                }
                                            }
                                        ).catch(addCustomerPaymentMethodErrorResponse => {
                                            let errorTitle = "Exception occured during adding payment method with Ebizcharge Payment Gateway";
                                            EBizSoapApiClientModel.renderException($, errorTitle, addCustomerPaymentMethodErrorResponse);
                                        }
                                    );

                                }
                            ).catch(
                            (errorGetCustomerResponse) => {
                                let errorTitle = "Exception occured during getting customer";
                                EBizSoapApiClientModel.renderException($, errorTitle, errorGetCustomerResponse);
                            });

                    }
                ).catch((addCustomerErrorRespose) => {

                let errorTitle = "Exception occurred during adding Customer";
                EBizSoapApiClientModel.renderException($, errorTitle, addCustomerErrorRespose);
            });

            return false;

        } else {
            /** assiging variables  **/
            requestParams.ebiz_customer_internal_id = EBizSoapApiClientModel.ebiz_customer_internal_id;
            requestParams.ebiz_customer_token = EBizSoapApiClientModel.ebiz_customer_token;
            requestParams.ebiz_customer_id = EBizSoapApiClientModel.ebiz_customer_id;

            /** adding payment Method Name **/
            EBizSoapApiClientModel.addCustomerPaymentMethod($, requestParams)
                .then((addCustomerPaymentMethodResponse) => {
                        if (typeof (addCustomerPaymentMethodResponse.payment_method_id) !== "undefined") {

                            /** payment Method Id **/
                            let paymentMethodId = addCustomerPaymentMethodResponse.payment_method_id;

                            let defaultPaymentMethodRequest = {
                                payment_method_id: paymentMethodId
                            };

                            if (isDefaultPaymentId) {
                                /** sending request for set default Payment Method Id **/
                                EBizSoapApiClientModel.setDefaultPaymentMethod($, defaultPaymentMethodRequest)
                                    .then(setDefaultPaymentMethodSuccessResponse => {
                                            //   console.log(setDefaultPaymentMethodSuccessResponse);
                                        }
                                    ).catch(setDefaultPaymentMethodErrorResponse => {
                                    let errorTitle = "Exception occurred during setting default payment method with Ebizcharge Gateway";
                                    /** exception rendering **/
                                    EBizSoapApiClientModel.renderException($, errorTitle, setDefaultPaymentMethodErrorResponse);

                                });
                            }
                            /** redirecting to card listing URL **/
                            EBizSoapApiClientModel.redirectAction($, EBizSoapApiClientModel.card_listing_url);
                        }
                    }
                ).catch((addCustomerPaymentMethodErrorResponse) => {

                    let errorTitle = "Exception occurred during adding Customer Payment Method";
                    EBizSoapApiClientModel.renderException($, errorTitle, addCustomerPaymentMethodErrorResponse);
                }
            );
        }
        return false;
    }
    ,
    /**
     * Publish Data Action
     *
     * @param $
     * @param functionCallBack
     * @param functionName
     */
    publishDataAction($, functionCallBack, functionName) {
        /** submitting the form
         * for adding Payment Method at Ebizcharge
         * **/
        $.when(functionCallBack).done(function (xhrResponse) {
            var responseTimeout = setTimeout(function () {
                /** sending back the response the function **/
                var soapResponse = xhrResponse.soapResponse;

                if (functionName == EBizSoapApiClientModel.add_payment_methodName_action, soapResponse.error === false) {
                    /** redirect to listing page **/
                    EBizSoapApiClientModel.redirectAction($, EBizSoapApiClientModel.card_listing_url);
                }
                /** after sending response clear time out **/
                clearTimeout(responseTimeout);
            }, EBizSoapApiClientModel.sessionTimeOut);
        });
    }
    ,
    /**
     * Redirect Action
     *
     * @param $
     * @param redirectUrl
     * @returns {boolean}
     */
    redirectAction: function ($, redirectUrl) {
        window.location.href = redirectUrl;
        return false;
    }
    ,
    /**
     * Send Ajax Request
     *
     * @param $
     * @param ajaxActionUrl
     * @param formDataParams
     * @returns {Promise<unknown>}
     */
    sendAjaxProcessRequest: function ($, ajaxActionUrl = null, formDataParams = []) {

        return new Promise(function (transactionSuccess, transactionException) {

            return $.ajax({
                url: ajaxActionUrl,
                type: 'post',
                data: formDataParams,
                dataType: 'json',
                showLoader: true,

                beforeSend: function (xhr) {
                    //  xhr.setRequestHeader("Authorization", "Basic " + btoa(""));
                },
                success: function (data) {
                    //console.log(data);
                },
                error: function (xhr) {
                    //console.log(xhr);
                }
            })
                .done((ajaxProcessorResp) => {
                    transactionSuccess(ajaxProcessorResp);
                })
                .always((ajaxProcessorResp) => {
                    transactionSuccess(ajaxProcessorResp);
                })
                ;

        });
    }
    ,
    /**
     * Prepare Params
     *
     * @param customerParams
     */
    prepareParams: function (customerParams) {
        var resp = {};

    }
    ,
    /**
     * Validate Form
     *
     * @param $
     * @param uiForm
     * @returns {boolean}
     */
    validateForm: function ($, uiForm = null) {
        let isValid = false;
        if ($(uiForm).valid()) {
            isValid = true;
        }
        return isValid;
    },

    /**
     * runPciPreAuthTransaction
     *
     * @param $
     * @param preAuthParams
     * @returns {*}
     */
    runPciPreAuthTransaction: function ($, preAuthTransactionParams = null) {

        $('body').trigger('processStop').loader('hide');
        /**
         * Pre Auth Transaction Response
         *
         * @type {Promise<unknown>}
         */
        let preAuthRespPromise = new Promise((successResolve, errorReject) => {

            EBizSoapApiClientModel
                .runTransactionSoapRequest($, preAuthTransactionParams)
                .then((transSuccessResp) => {
                    console.log(transSuccessResp);
                    successResolve(transSuccessResp);
                })
                .catch((transErrorResp) => {
                    console.log(transErrorResp);
                    errorReject(transErrorResp);
                })
            ;
        });
        return preAuthRespPromise;
    },

    /**
     * Void Pre Auth Transaction
     *
     * @param $
     * @param preAuthParams
     * @returns {*}
     */
    voidPciPreAuthTransaction: function ($, voidPreAuthParams = null) {
        /**
         * Void PreAuth Response Promise
         * @type {Promise<unknown>}
         */
        let voidPreAuthRespPromise = new Promise((successResolve, errorReject) => {

            EBizSoapApiClientModel
                .runTransactionSoapRequest($, voidPreAuthParams)
                .then((transSuccessResp) => {
                    successResolve(transSuccessResp);
                })
                .catch((transErrorResp) => {
                    errorReject(transErrorResp);
                })
            ;
        });

        return voidPreAuthRespPromise;
    },

    /**
     * Add payment method
     *
     * @param $
     * @param requestParams
     * @returns {Promise<unknown>}
     */
    addPaymentMethod: function ($, requestParams) {

        return new Promise((successResolve, errorReject) => {
            let customerRequest =
                '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ebiz="http://eBizCharge.ServiceModel.SOAP">' +
                '   <soapenv:Header/>' +
                '   <soapenv:Body>' +
                '      <ebiz:AddCustomerPaymentMethodProfile>' +
                EBizSoapApiClientModel.getSoapClientKeys() +
                '         <!--Optional:-->' +
                '         <ebiz:customerInternalId>' + EBizSoapApiClientModel.ebizCustomerInternalId + '</ebiz:customerInternalId>' +
                '         <!--Optional:-->' +
                '         <ebiz:paymentMethodProfile>' +
                EBizSoapApiClientModel.getPaymentMethodParams() +
                '         </ebiz:paymentMethodProfile>' +
                '      </ebiz:AddCustomerPaymentMethodProfile>' +
                '   </soapenv:Body>' +
                '</soapenv:Envelope>'
            ;

            let parser = new DOMParser();
            customerRequest = parser.parseFromString(customerRequest, "text/xml");

            //console.log(customerRequest);

            /*** extend the ajax as a Request **/
            /** pushing SOAP Request to Ebizcharge Gateway  **/
            $.ajax(EBizSoapApiClientModel.getEbizSOAPClient($, 'AddCustomerPaymentMethodProfile', customerRequest))
                .done(customerPaymentMethodResult => {
                    let msgTxt = "Success the customer Payment Method has been added at  EBizCharge Payment Gateway";

                    //   console.log(customerPaymentMethodResult);
                    let paymentMethodId = EBizSoapApiClientModel.prepareResponseNode(customerPaymentMethodResult, 'AddCustomerPaymentMethodProfileResult');

                    let soapResponse = {
                        payment_method_id: paymentMethodId,
                        error: false,
                        message: msgTxt + " Method Id: " + paymentMethodId,
                        exception: false,
                        response: {
                            method_name: EBizSoapApiClientModel.paymentMethodName
                        }
                    };
                    /** soap response in case of Success **/
                    successResolve(soapResponse);

                }).fail(e => {
                let msgTxt = "Exception occurred during Add new Payment Method at EBizCharge Payment Gateway";

                let soapResponse = {
                    error: true,
                    payment_method_id: null,
                    message: msgTxt,
                    exception: e
                };

                /** resolving back Error Response **/
                errorReject(soapResponse);

            }).always(customerPaymentMethodResult => {

                /**
                 * Message Txt
                 * @type {string}
                 */
                let msgTxt = "Success the customer Payment Method has been added at  EBizCharge Payment Gateway";

                //console.log(customerPaymentMethodResult);
                let paymentMethodId = EBizSoapApiClientModel.prepareResponseNode(customerPaymentMethodResult, 'AddCustomerPaymentMethodProfileResult');

                let soapResponse = {
                    payment_method_id: paymentMethodId,
                    error: false,
                    message: msgTxt + " Method Id: " + paymentMethodId,
                    exception: false
                };
                /** soap response
                 * in case of
                 * Success **/
                successResolve(soapResponse);
            });
        });
    },

    /**
     * Add Customer Payment Method
     *
     * @param $
     * @param requestParams
     * @returns {jQuery.soapResponse}
     */
    addCustomerPaymentMethod: function ($, requestParams = {}) {

        /** Defining the Promise when Ajax Request gets completed **/
        /** Defining the Promise when Ajax Request gets completed **/
        return new Promise((successResolve, errorReject) => {

            if (EBizSoapApiClientModel.isPciTransactions) {
                /**
                 * Pre Auth Response
                 */
                let preAuthResp = EBizSoapApiClientModel.runTransactionSoapRequest($, requestParams)
                    .then((preAuthSuccess) => {
                        //console.log(preAuthSuccess);
                        //return preAuthSuccess;

                        let resultCode = typeof preAuthSuccess.transaction_data !== undefined ? preAuthSuccess.transaction_data.result_code : '';
                        let referenceNo = typeof preAuthSuccess.transaction_data.ref_num !== undefined ? preAuthSuccess.transaction_data.ref_num : '';

                        if (resultCode && resultCode === 'E' && referenceNo) {

                            EBizSoapApiClientModel.saleCommand = EBizSoapApiClientModel.soapGateway.void_command;
                            EBizSoapApiClientModel.transactionReferenceNumber = referenceNo;

                            /**
                             * void Pre Auth Transaction Response
                             */
                            let voidPreAuthResp = EBizSoapApiClientModel
                                .runTransactionSoapRequest($, requestParams)
                                .then((voidAuthSuccess) => {
                                   //console.log(voidAuthSuccess);
                                    successResolve(voidAuthSuccess);
                                })
                                .catch((voidAuthError) => {
                                   //console.log(voidAuthError);
                                    successResolve(voidAuthError);
                                })
                            ;
                        }

                        if (resultCode && resultCode === 'A') {
                            EBizSoapApiClientModel
                                .addPaymentMethod($, requestParams)
                                .then((successResponse) => {
                                    //console.log(successResponse);
                                    successResolve(successResponse);
                                })
                                .catch((errorResponse) => {
                                    //console.log(errorResponse);
                                    errorReject(errorResponse);
                                })
                            ;
                            //return EBizSoapApiClientModel.addPaymentMethod($, requestParams);
                        } else {
                            errorReject(preAuthSuccess);
                            //return preAuthSuccess;
                        }

                    })
                    .catch((preAuthError) => {
                        //console.log(preAuthError);
                        errorReject(preAuthError);
                        //return preAuthError;
                    })
                ;

            }
        });

    },

    /**
     * get Payment Method Params
     *
     * @returns {string}
     */
    getPaymentMethodParams: function () {


        let soapPaymentMethodParams = "";
        let isMethodDefault = EBizSoapApiClientModel.formData.is_default ?? 0;

        if (EBizSoapApiClientModel.formData.ebzc_option_type !== EBizSoapApiClientModel.paymentOptionTypeCreditCard) {

            let cardNumber = EBizSoapApiClientModel.formData.cc_number;
            let paymentMethodName = EBizSoapApiClientModel.formData.ach_type;
            paymentMethodName += "-" + cardNumber.substr(cardNumber.length - 4, cardNumber.length);
            paymentMethodName += "-" + EBizSoapApiClientModel.formData.cc_owner;
            EBizSoapApiClientModel.paymentMethodName = paymentMethodName;

            soapPaymentMethodParams =
                '            <!--Optional:-->' +
                '            <ebiz:MethodType>' + EBizSoapApiClientModel.formData.ach_type + '</ebiz:MethodType>' +
                '            <!--Optional:-->' +
                '            <ebiz:MethodID></ebiz:MethodID>' +
                '            <!--Optional:-->' +
                '            <ebiz:MethodName>' + paymentMethodName + '</ebiz:MethodName>' +
                '            <!--Optional:-->' +
                '            <ebiz:SecondarySort>' + isMethodDefault + '</ebiz:SecondarySort>' +
                '            <ebiz:Created>' + EBizSoapApiClientModel.createdAt + '</ebiz:Created>' +
                '            <ebiz:Modified>' + EBizSoapApiClientModel.createdAt + '</ebiz:Modified>' +
                '            <!--Optional:-->' +
                '            <ebiz:Account>' + EBizSoapApiClientModel.formData.cc_number + '</ebiz:Account>' +
                '            <!--Optional:-->' +
                '            <ebiz:AccountType>' + EBizSoapApiClientModel.formData.ach_type + '</ebiz:AccountType>' +
                '            <!--Optional:-->' +
                '            <ebiz:AccountHolderName>' + EBizSoapApiClientModel.formData.cc_owner + '</ebiz:AccountHolderName>' +
                '            <!--Optional:-->' +
                '            <ebiz:DriversLicense></ebiz:DriversLicense>' +
                '            <!--Optional:-->' +
                '            <ebiz:DriversLicenseState></ebiz:DriversLicenseState>' +
                '            <!--Optional:-->' +
                '            <ebiz:RecordType></ebiz:RecordType>' +
                '            <!--Optional:-->' +
                '            <ebiz:Routing>' + EBizSoapApiClientModel.formData.ach_routing + '</ebiz:Routing>'
            ;


        } else {

            let ccMonth = EBizSoapApiClientModel.formData.cc_exp_month;

            if (ccMonth.length === 1) {
                ccMonth = "0" + ccMonth;
            }

            let cardExpiry = EBizSoapApiClientModel.formData.cc_exp_year + "-" + EBizSoapApiClientModel.formData.cc_exp_month;
            let cardNumber = EBizSoapApiClientModel.formData.cc_number;
            let paymentMethodName = EBizSoapApiClientModel.formData.cc_type;
            paymentMethodName += "-" + cardNumber.substr(cardNumber.length - 4, cardNumber.length);
            paymentMethodName += "-" + EBizSoapApiClientModel.formData.cc_owner;
            EBizSoapApiClientModel.paymentMethodName = paymentMethodName;

            soapPaymentMethodParams =
                '            <ebiz:MethodName>' + paymentMethodName + '</ebiz:MethodName>' +
                '            <ebiz:AccountHolderName>' + EBizSoapApiClientModel.formData.cc_owner + '</ebiz:AccountHolderName>' +

                '            <!--Optional:-->' +
                '            <ebiz:SecondarySort>' + isMethodDefault + '</ebiz:SecondarySort>' +
                '            <ebiz:Created>' + EBizSoapApiClientModel.createdAt + '</ebiz:Created>' +
                '            <ebiz:Modified>' + EBizSoapApiClientModel.createdAt + '</ebiz:Modified>' +
                '            <!--Optional:-->' +
                '            <ebiz:MethodID></ebiz:MethodID>' +
                '            <!--Optional:-->' +
                '            <ebiz:AvsStreet>' + EBizSoapApiClientModel.formData.ebzc_avs_street + '</ebiz:AvsStreet>' +
                '            <!--Optional:-->' +
                '            <ebiz:AvsZip>' + EBizSoapApiClientModel.formData.ebzc_avs_zip + '</ebiz:AvsZip>' +
                '            <!--Optional:-->' +
                '            <ebiz:CardCode>' + EBizSoapApiClientModel.formData.cc_cid + '</ebiz:CardCode>' +
                '            <!--Optional:-->' +
                '            <ebiz:CardExpiration>' + cardExpiry + '</ebiz:CardExpiration>' +
                '            <!--Optional:-->' +
                '            <ebiz:CardNumber>' + EBizSoapApiClientModel.formData.cc_number + '</ebiz:CardNumber>' +
                '            <!--Optional:-->' +
                '            <ebiz:CardType>' + EBizSoapApiClientModel.formData.cc_type + '</ebiz:CardType>' +
                '            <ebiz:Balance>0</ebiz:Balance>' +
                '            <ebiz:MaxBalance>0</ebiz:MaxBalance>' +
                '            <!--Optional:-->' +
                '            <ebiz:AutoReload></ebiz:AutoReload>' +
                '            <!--Optional:-->' +
                '            <ebiz:ReloadSchedule></ebiz:ReloadSchedule>' +
                '            <!--Optional:-->' +
                '            <ebiz:ReloadThreshold></ebiz:ReloadThreshold>' +
                '            <!--Optional:-->' +
                '            <ebiz:ReloadAmount></ebiz:ReloadAmount>' +
                '            <!--Optional:-->' +
                '            <ebiz:ReloadMethodID></ebiz:ReloadMethodID>'
            ;
        }

        return soapPaymentMethodParams;
    }
    ,
    /** adding new Customer to Ebizcharge **/
    /**
     * Add Ebizcharge Customer
     *
     * @param $
     * @param requestParams
     * @returns {jQuery.soapResponse}
     */
    addEbizCustomer: function ($, requestParams) {

        /** Defining the Promise when Ajax Request gets completed **/
        return new Promise((successResolve, errorReject) => {

            if (EBizSoapApiClientModel.customer_id !== null && !EBizSoapApiClientModel.ebiz_customer_token) {

                EBizSoapApiClientModel.customer_default_shipping_address.fax = EBizSoapApiClientModel.customer_default_shipping_address.fax ? EBizSoapApiClientModel.customer_default_shipping_address.fax : '';
                EBizSoapApiClientModel.customer_default_billing_address.fax = EBizSoapApiClientModel.customer_default_billing_address.fax ? EBizSoapApiClientModel.customer_default_billing_address.fax : '';

                let customerRequest =
                    '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ebiz="http://eBizCharge.ServiceModel.SOAP">' +
                    '   <soapenv:Header/>' +
                    '   <soapenv:Body>' +
                    '      <ebiz:AddCustomer>' +
                    '         <ebiz:securityToken>' +
                    '            <ebiz:SecurityId>' + EBizSoapApiClientModel.dtr(this.eMak) + '</ebiz:SecurityId>' +
                    '            <ebiz:UserId/>' +
                    '            <ebiz:Password/>' +
                    '         </ebiz:securityToken>' +
                    '         <ebiz:customer>' +
                    '            <ebiz:MerchantId/>' +
                    '            <ebiz:CustomerInternalId/>' +
                    '            <ebiz:CustomerId>' + requestParams.customer_id + '</ebiz:CustomerId>' +
                    '            <ebiz:FirstName>' + EBizSoapApiClientModel.customer.firstname + '</ebiz:FirstName>' +
                    '            <ebiz:LastName>' + EBizSoapApiClientModel.customer.lastname + '</ebiz:LastName>' +
                    '            <ebiz:CompanyName>' + EBizSoapApiClientModel.customer_default_billing_address.company + '</ebiz:CompanyName>' +
                    '            <ebiz:Phone>' + EBizSoapApiClientModel.customer_default_billing_address.telephone + '</ebiz:Phone>' +
                    '            <ebiz:CellPhone>' + EBizSoapApiClientModel.cellphone + '</ebiz:CellPhone>' +
                    '            <ebiz:Fax>' + EBizSoapApiClientModel.customer_default_billing_address.fax + '</ebiz:Fax>' +
                    '            <ebiz:Email>' + EBizSoapApiClientModel.customer.email + '</ebiz:Email>' +
                    '            <ebiz:WebSite>' + EBizSoapApiClientModel.website_url + '</ebiz:WebSite>' +
                    '            <ebiz:RecurringBillingData/>' +
                    '            <ebiz:BillingAddress>' +
                    '               <ebiz:FirstName>' + EBizSoapApiClientModel.customer_default_billing_address.firstname + '</ebiz:FirstName>' +
                    '               <ebiz:LastName>' + EBizSoapApiClientModel.customer_default_billing_address.lastname + '</ebiz:LastName>' +
                    '               <ebiz:CompanyName>' + EBizSoapApiClientModel.customer_default_billing_address.company + '</ebiz:CompanyName>' +
                    '               <ebiz:Address1>' + EBizSoapApiClientModel.customer_default_billing_address.street + '</ebiz:Address1>' +
                    '               <ebiz:Address2>' + EBizSoapApiClientModel.customer_default_billing_address.street + '</ebiz:Address2>' +
                    '               <ebiz:City>' + EBizSoapApiClientModel.customer_default_billing_address.city + '</ebiz:City>' +
                    '               <ebiz:State>' + EBizSoapApiClientModel.customer_default_billing_address.region + '</ebiz:State>' +
                    '               <ebiz:ZipCode>' + EBizSoapApiClientModel.customer_default_billing_address.postcode + '</ebiz:ZipCode>' +
                    '               <ebiz:IsDefault>1</ebiz:IsDefault>' +
                    '            </ebiz:BillingAddress>' +
                    '            <ebiz:ShippingAddress>' +
                    '               <ebiz:FirstName>' + EBizSoapApiClientModel.customer_default_shipping_address.firstname + '</ebiz:FirstName>' +
                    '               <ebiz:LastName>' + EBizSoapApiClientModel.customer_default_shipping_address.lastname + '</ebiz:LastName>' +
                    '               <ebiz:CompanyName>' + EBizSoapApiClientModel.customer_default_shipping_address.company + '</ebiz:CompanyName>' +
                    '               <ebiz:Address1>' + EBizSoapApiClientModel.customer_default_shipping_address.street + '</ebiz:Address1>' +
                    '               <ebiz:Address2/>' +
                    '               <ebiz:City>' + EBizSoapApiClientModel.customer_default_shipping_address.city + '</ebiz:City>' +
                    '               <ebiz:State>' + EBizSoapApiClientModel.customer_default_shipping_address.region + '</ebiz:State>' +
                    '               <ebiz:ZipCode>' + EBizSoapApiClientModel.customer_default_shipping_address.postcode + '</ebiz:ZipCode>' +
                    '               <ebiz:IsDefault>1</ebiz:IsDefault>' +
                    '            </ebiz:ShippingAddress>' +
                    '         </ebiz:customer>' +
                    '      </ebiz:AddCustomer>' +
                    '   </soapenv:Body>' +
                    '</soapenv:Envelope>'

                ;

                let parser = new DOMParser();
                customerRequest = parser.parseFromString(customerRequest, "text/xml");

                $.ajax(EBizSoapApiClientModel.getEbizSOAPClient($, 'AddCustomer', customerRequest))
                    .done(customerResult => {
                        //console.log(customerResult);
                        EBizSoapApiClientModel.ebiz_customer_id = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'CustomerId');
                        EBizSoapApiClientModel.ebiz_customer_internal_id = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'CustomerInternalId');
                        EBizSoapApiClientModel.ebiz_customer_status = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'Status');
                        EBizSoapApiClientModel.ebiz_customer_error = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'Error');

                        let msgTxt = "Success the customer Added at  Ebizhcarge payment Gateway";
                        let errorResponse = false;

                        if (EBizSoapApiClientModel.ebiz_customer_status == 'Failed') {
                            errorResponse = true;
                            msgTxt = 'Error occured Customer  ' + EBizSoapApiClientModel.ebiz_customer_error;
                        }

                        let soapResponse = {
                            error: errorResponse,
                            ebiz_customer_id: EBizSoapApiClientModel.ebiz_customer_id,
                            ebiz_customer_internal_id: EBizSoapApiClientModel.ebiz_customer_internal_id,
                            message: msgTxt,
                            exception: false
                        };
                        if (soapResponse.error === false) {
                            EBizSoapApiClientModel.loaderText = "<span style='color:green'>" + msgTxt + "</span>";
                        } else {
                            EBizSoapApiClientModel.loaderText = "<span style='color:red'>" + msgTxt + "</span>";
                        }

                        EBizSoapApiClientModel.messageProcessor(EBizSoapApiClientModel.loaderText, true);
                        /** sending call back Response in case of success **/
                        successResolve(soapResponse);

                    }).fail(e => {
                    let msgTxt = "Exception occurred during Add Customer to Ebizhcarge Payment Gateway Error:";
                    let soapResponse = {
                        error: true,
                        ebiz_customer_id: null,
                        ebiz_customer_internal_id: null,
                        message: msgTxt,
                        exception: e
                    };
                    //console.log('Exception Occured : ', e);
                    EBizSoapApiClientModel.loaderText = "<span style='color:red'>" + msgTxt + " Error: " + e.statusText + "</span>";
                    // console.log('Exception Occured during adding Customer: ', e);
                    EBizSoapApiClientModel.messageProcessor(EBizSoapApiClientModel.loaderText, true);

                    /** in case of error callback soap Error Response **/
                    errorReject(soapResponse);

                }).always(e => {
                    // this.hideLoader();
                });
            }
        });
    }
    ,

    /**
     * get Order Custom Fields
     *
     * @returns {string}
     */
    getOrderCustomFields: function () {

        let orderCustomFields = '<ebiz:CustomFields></ebiz:CustomFields>';
        if (EBizSoapApiClientModel.isCustomFields) {
            let orderCustomFields =
                '<ebiz:CustomFields>' +
                '               <!--Zero or more repetitions:-->' +
                '               <ebiz:FieldValue>' +
                '                  <!--Optional:-->' +
                '                  <ebiz:Field></ebiz:Field>' +
                '                  <!--Optional:-->' +
                '                  <ebiz:Value></ebiz:Value>' +
                '               </ebiz:FieldValue>' +
                '            </ebiz:CustomFields>'
            ;
        }
        return orderCustomFields;

    },

    /**
     * get Soap XML Recurring Detail Params
     *
     * @returns {string}
     */
    getSoapXmlRecurringDetailParams: function () {

        let recurringParams = ' <ebiz:RecurringBilling></ebiz:RecurringBilling>';

        if (EBizSoapApiClientModel.isRecurring === 1) {

            let recurringParams =
                '            <ebiz:RecurringBilling>' +
                '               <ebiz:Amount></ebiz:Amount>' +
                '               <ebiz:Enabled></ebiz:Enabled>' +
                '               <!--Optional:-->' +
                '               <ebiz:Expire></ebiz:Expire>' +
                '               <!--Optional:-->' +
                '               <ebiz:Next></ebiz:Next>' +
                '               <!--Optional:-->' +
                '               <ebiz:NumLeft></ebiz:NumLeft>' +
                '               <!--Optional:-->' +
                '               <ebiz:Schedule></ebiz:Schedule>' +
                '            </ebiz:RecurringBilling>'
            ;
        }
        return recurringParams;
    },

    /**
     * Transaction Detail Params
     *
     * @param $
     * @param requestParams
     * @returns {string}
     */
    getSoapXmlTransactionDetailParams: function ($, requestParams = {}) {

        let transactionDetail =
            '<ebiz:Details>' +
            '               <ebiz:NonTax>' + EBizSoapApiClientModel.isNonTax + '</ebiz:NonTax>' +
            '               <ebiz:Tax>' + EBizSoapApiClientModel.taxAmount + '</ebiz:Tax>' +
            '               <!--Optional:-->' +
            '               <ebiz:Table>' + EBizSoapApiClientModel.table + '</ebiz:Table>' +
            '               <ebiz:Subtotal>' + EBizSoapApiClientModel.subTotal + '</ebiz:Subtotal>' +
            '               <ebiz:Shipping>' + EBizSoapApiClientModel.shippingAmount + '</ebiz:Shipping>' +
            '               <!--Optional:-->' +
            '               <ebiz:ShipFromZip>' + EBizSoapApiClientModel.shipZipCode + '</ebiz:ShipFromZip>' +
            '               <!--Optional:-->' +
            '               <ebiz:SessionID>' + EBizSoapApiClientModel.sessionId + '</ebiz:SessionID>' +
            '               <!--Optional:-->' +
            '               <ebiz:PONum>' + EBizSoapApiClientModel.poNumber + '</ebiz:PONum>' +
            '               <!--Optional:-->' +
            '               <ebiz:OrderID>' + EBizSoapApiClientModel.orderNumber + '</ebiz:OrderID>' +
            '               <!--Optional:-->' +
            '               <ebiz:Invoice>' + EBizSoapApiClientModel.invoiceNumber + '</ebiz:Invoice>' +
            '               <ebiz:Duty>' + EBizSoapApiClientModel.dutyAmount + '</ebiz:Duty>' +
            '               <ebiz:Discount>' + EBizSoapApiClientModel.discountAmount + '</ebiz:Discount>' +
            '               <!--Optional:-->' +
            '               <ebiz:Comments>' + EBizSoapApiClientModel.orderComments + '</ebiz:Comments>' +
            '               <!--Optional:-->' +
            '               <ebiz:Description>' + EBizSoapApiClientModel.orderDescription + '</ebiz:Description>' +
            '               <!--Optional:-->' +
            '               <ebiz:Currency>' + EBizSoapApiClientModel.orderCurrency + '</ebiz:Currency>' +
            '               <!--Optional:-->' +
            '               <ebiz:Clerk>' + EBizSoapApiClientModel.merchantCode + '</ebiz:Clerk>' +
            '               <ebiz:Amount>' + EBizSoapApiClientModel.ordredAmount + '</ebiz:Amount>' +
            '               <ebiz:AllowPartialAuth>' + EBizSoapApiClientModel.allowPartialAuth + '</ebiz:AllowPartialAuth>' +
            '               <!--Optional:-->' +
            '               <ebiz:Terminal>' + EBizSoapApiClientModel.terminal + '</ebiz:Terminal>' +
            '               <ebiz:Tip>' + EBizSoapApiClientModel.orderTip + '</ebiz:Tip>' +
            '            </ebiz:Details>'
        ;

        return transactionDetail;

    },

    /**
     * get Soap XML credit card Params
     *
     * @returns {*}
     */
    getSoapXmlCreditCardParams: function () {

        let creditCardSoapXmlPayLoad =
            '<ebiz:CreditCardData> </ebiz:CreditCardData>';

        if (EBizSoapApiClientModel.formData.ebzc_option_type === EBizSoapApiClientModel.paymentOptionTypeCreditCard) {

            let expiryMonth = EBizSoapApiClientModel.formData.cc_exp_month;
            if (expiryMonth.length === 1) {
                expiryMonth = "0" + expiryMonth;
            }
            let expiryYear = EBizSoapApiClientModel.formData.cc_exp_year;
            let yearLen = expiryYear.length;

            expiryYear = expiryYear.substr(yearLen - 2, yearLen);

            let cardExpiry = expiryMonth + expiryYear;

            //   console.log([cardExpiry]);

            creditCardSoapXmlPayLoad =
                '            <ebiz:CreditCardData>' +
                '               <!--Optional:-->' +
                '               <ebiz:CAVV></ebiz:CAVV>' +
                '               <!--Optional:-->' +
                '               <ebiz:Pares></ebiz:Pares>' +
                '               <!--Optional:-->' +
                '               <ebiz:MagSupport>1</ebiz:MagSupport>' +
                '               <!--Optional:-->' +
                '               <ebiz:MagStripe></ebiz:MagStripe>' +
                '               <ebiz:InternalCardAuth>1</ebiz:InternalCardAuth>' +
                '               <!--Optional:-->' +
                '               <ebiz:ECI></ebiz:ECI>' +
                '               <!--Optional:-->' +
                '               <ebiz:DUKPT></ebiz:DUKPT>' +
                '               <!--Optional:-->' +
                '               <ebiz:XID></ebiz:XID>' +
                '               <!--Optional:-->' +

                '               <ebiz:CardType>' + EBizSoapApiClientModel.formData.cc_type + '</ebiz:CardType>' +
                '               <ebiz:CardPresent>true</ebiz:CardPresent>' +
                '               <!--Optional:-->' +
                '               <ebiz:CardNumber>' + EBizSoapApiClientModel.formData.cc_number + '</ebiz:CardNumber>' +
                '               <!--Optional:-->' +
                '               <ebiz:CardExpiration>' + cardExpiry + '</ebiz:CardExpiration>' +
                '               <!--Optional:-->' +
                '               <ebiz:CardCode>' + EBizSoapApiClientModel.formData.cc_cid + '</ebiz:CardCode>' +
                '               <!--Optional:-->' +
                '               <ebiz:AvsZip>' + EBizSoapApiClientModel.formData.ebzc_avs_zip + '</ebiz:AvsZip>' +
                '               <!--Optional:-->' +
                '               <ebiz:AvsStreet>' + EBizSoapApiClientModel.formData.ebzc_avs_street + '</ebiz:AvsStreet>' +
                '               <!--Optional:-->' +
                '               <ebiz:Signature>' + EBizSoapApiClientModel.formData.cc_cid + '</ebiz:Signature>' +
                '               <!--Optional:-->' +
                '               <ebiz:TermType></ebiz:TermType>' +


                '            </ebiz:CreditCardData>'
            ;
        }

        return creditCardSoapXmlPayLoad;
    },
    /**
     * get Soap XML Bank Account Data
     *
     * @returns {*}
     */
    getSoapXmlBankAccountParams: function () {

        let achSoapXmlData = '<ebiz:CheckData>' + '</ebiz:CheckData>';


        if (EBizSoapApiClientModel.formData.ebzc_option_type === EBizSoapApiClientModel.paymentOptionTypeAch) {

            achSoapXmlData =
                '            <ebiz:CheckData>' +
                '               <!--Optional:-->' +
                '               <ebiz:Account>' + EBizSoapApiClientModel.formData.cc_owner + '</ebiz:Account>' +
                '               <!--Optional:-->' +
                '               <ebiz:AccountType>' + EBizSoapApiClientModel.formData.ach_type + '</ebiz:AccountType>' +
                '               <!--Optional:-->' +
                '               <ebiz:CheckNumber>' + EBizSoapApiClientModel.formData.cc_number + '</ebiz:CheckNumber>' +
                '               <!--Optional:-->' +
                '               <ebiz:DriversLicense></ebiz:DriversLicense>' +
                '               <!--Optional:-->' +
                '               <ebiz:DriversLicenseState></ebiz:DriversLicenseState>' +
                '               <!--Optional:-->' +
                '               <ebiz:RecordType></ebiz:RecordType>' +
                '               <!--Optional:-->' +
                '               <ebiz:Routing>' + EBizSoapApiClientModel.formData.ach_routing + '</ebiz:Routing>' +
                '               <!--Optional:-->' +
                '               <ebiz:MICR></ebiz:MICR>' +
                '               <!--Optional:-->' +
                '               <ebiz:AuxOnUS></ebiz:AuxOnUS>' +
                '               <!--Optional:-->' +
                '               <ebiz:EpcCode></ebiz:EpcCode>' +
                '               <!--Optional:-->' +
                '               <ebiz:FrontImage></ebiz:FrontImage>' +
                '               <!--Optional:-->' +
                '               <ebiz:BackImage></ebiz:BackImage>' +
                '            </ebiz:CheckData>'
            ;
        }

        return achSoapXmlData;
    }

    ,
    /**
     * get SoapXML Billing Address
     *
     * @returns {string}
     */
    getSoapXmlBillingAddressParams: function () {

        let fax = typeof (EBizSoapApiClientModel.billingAddress.telephone) !== "undefined" ? EBizSoapApiClientModel.billingAddress.telephone : "";
        let street1 = typeof (EBizSoapApiClientModel.billingAddress.street[0]) !== "undefined" ? EBizSoapApiClientModel.billingAddress.street[0] : "";
        let street2 = typeof (EBizSoapApiClientModel.billingAddress.street[1]) !== "undefined" ? EBizSoapApiClientModel.billingAddress.street[1] : ""

        let soapBillingAddress =
            '            <ebiz:BillingAddress>' +

            '               <!--Optional:-->' +
            '               <ebiz:City>' + EBizSoapApiClientModel.billingAddress.city + '</ebiz:City>' +
            '               <!--Optional:-->' +
            '               <ebiz:Company>' + EBizSoapApiClientModel.billingAddress.company + '</ebiz:Company>' +
            '               <!--Optional:-->' +
            '               <ebiz:Country>' + EBizSoapApiClientModel.billingAddress.countryId + '</ebiz:Country>' +
            '               <!--Optional:-->' +
            '               <ebiz:Email>' + EBizSoapApiClientModel.customer.email + '</ebiz:Email>' +
            '               <!--Optional:-->' +
            '               <ebiz:Fax>' + fax + '</ebiz:Fax>' +
            '               <!--Optional:-->' +
            '               <ebiz:FirstName>' + EBizSoapApiClientModel.billingAddress.firstname + '</ebiz:FirstName>' +
            '               <!--Optional:-->' +
            '               <ebiz:LastName>' + EBizSoapApiClientModel.billingAddress.lastname + '</ebiz:LastName>' +
            '               <!--Optional:-->' +
            '               <ebiz:Phone>' + EBizSoapApiClientModel.billingAddress.telephone + '</ebiz:Phone>' +
            '               <!--Optional:-->' +
            '               <ebiz:State>' + EBizSoapApiClientModel.billingAddress.region + '</ebiz:State>' +
            '               <!--Optional:-->' +
            '               <ebiz:Street>' + street1 + '</ebiz:Street>' +
            '               <!--Optional:-->' +
            '               <ebiz:Street2>' + street2 + '</ebiz:Street2>' +
            '               <!--Optional:-->' +
            '               <ebiz:Zip>' + EBizSoapApiClientModel.billingAddress.postcode + '</ebiz:Zip>' +

            '            </ebiz:BillingAddress>'
        ;


        return soapBillingAddress;

    },

    /**
     * get SoapXML Shipping Address
     *
     * @returns {string}
     */
    getSoapXmlShippingAddressParams: function () {

        let fax = typeof (EBizSoapApiClientModel.shippingAddress.telephone) !== "undefined" ? EBizSoapApiClientModel.shippingAddress.telephone : "";
        let street1 = typeof (EBizSoapApiClientModel.shippingAddress.street[0]) !== "undefined" ? EBizSoapApiClientModel.shippingAddress.street[0] : "";
        let street2 = typeof (EBizSoapApiClientModel.shippingAddress.street[1]) !== "undefined" ? EBizSoapApiClientModel.shippingAddress.street[1] : ""


        let soapShippingAddress =
            '<ebiz:ShippingAddress>' +
            '               <!--Optional:-->' +
            '               <ebiz:City>' + EBizSoapApiClientModel.shippingAddress.city + '</ebiz:City>' +
            '               <!--Optional:-->' +
            '               <ebiz:Company>' + EBizSoapApiClientModel.shippingAddress.company + '</ebiz:Company>' +
            '               <!--Optional:-->' +
            '               <ebiz:Country>' + EBizSoapApiClientModel.shippingAddress.countryId + '</ebiz:Country>' +
            '               <!--Optional:-->' +
            '               <ebiz:Email>' + EBizSoapApiClientModel.customer.email + '</ebiz:Email>' +
            '               <!--Optional:-->' +
            '               <ebiz:Fax>' + fax + '</ebiz:Fax>' +
            '               <!--Optional:-->' +
            '               <ebiz:FirstName>' + EBizSoapApiClientModel.shippingAddress.firstname + '</ebiz:FirstName>' +
            '               <!--Optional:-->' +
            '               <ebiz:LastName>' + EBizSoapApiClientModel.shippingAddress.lastname + '</ebiz:LastName>' +
            '               <!--Optional:-->' +
            '               <ebiz:Phone>' + EBizSoapApiClientModel.shippingAddress.telephone + '</ebiz:Phone>' +
            '               <!--Optional:-->' +
            '               <ebiz:State>' + EBizSoapApiClientModel.shippingAddress.region + '</ebiz:State>' +
            '               <!--Optional:-->' +
            '               <ebiz:Street>' + street1 + '</ebiz:Street>' +
            '               <!--Optional:-->' +
            '               <ebiz:Street2>' + street2 + '</ebiz:Street2>' +
            '               <!--Optional:-->' +
            '               <ebiz:Zip>' + EBizSoapApiClientModel.shippingAddress.postcode + '</ebiz:Zip>' +
            '            </ebiz:ShippingAddress>'
        ;

        return soapShippingAddress;
    },

    /** set Default Payment Method **/
    /**
     * Set Default Payment Method
     *
     * @param $
     * @param requestParams
     * @returns {Promise<unknown>}
     */
    setDefaultPaymentMethod: function ($, requestParams) {

        var ebizCustomerToken = EBizSoapApiClientModel.ebiz_customer_token;
        var paymentMethodId = requestParams.payment_method_id;

        /** Defining the Promise when Ajax Request gets completed **/
        return new Promise((successResolve, errorReject) => {

            if (requestParams.ebiz_customer_id !== '' && requestParams.ebiz_customer_internal_id !== '') {

                let defaultPaymentMethodIdRequest =
                    '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ebiz="http://eBizCharge.ServiceModel.SOAP">' +
                    '   <soapenv:Header/>' +
                    '   <soapenv:Body>' +
                    '      <ebiz:SetDefaultCustomerPaymentMethodProfile>' +
                    EBizSoapApiClientModel.getSoapClientKeys($) +
                    '         <!--Optional:-->' +
                    '         <ebiz:customerToken>' + ebizCustomerToken + '</ebiz:customerToken>' +
                    '         <!--Optional:-->' +
                    '         <ebiz:paymentMethodId>' + paymentMethodId + '</ebiz:paymentMethodId>' +
                    '      </ebiz:SetDefaultCustomerPaymentMethodProfile>' +
                    '   </soapenv:Body>' +
                    '</soapenv:Envelope>'
                ;

                let parser = new DOMParser();
                defaultPaymentMethodIdRequest = parser.parseFromString(defaultPaymentMethodIdRequest, "text/xml");

                /*** extend the ajax as a Request **/
                /** pushing SOAP Request to Ebizcharge Gateway  **/
                $.ajax(EBizSoapApiClientModel.getEbizSOAPClient($, 'SetDefaultCustomerPaymentMethodProfile', defaultPaymentMethodIdRequest))
                    .done(customerPaymentDefaultMethodResult => {
                        let msgTxt = "Success the customer Payment Method has been added as a default Payment Gateway";
                        let isDefaultPaymentMethod = EBizSoapApiClientModel.prepareResponseNode(customerPaymentDefaultMethodResult, 'SetDefaultCustomerPaymentMethodProfileResponse');

                        let soapResponse = {
                            is_default_payment_method: isDefaultPaymentMethod,
                            error: false,
                            message: msgTxt + " Method Id is default : " + isDefaultPaymentMethod,
                            exception: false
                        };
                        EBizSoapApiClientModel.loaderText = "<span style='color:green'>" + msgTxt + "</span>";
                        EBizSoapApiClientModel.messageProcessor(EBizSoapApiClientModel.loaderText, true);

                        /** soap response in case of Success **/
                        successResolve(soapResponse);

                    }).fail(e => {
                    let msgTxt = "Exception occurred during set new Payment Method at Ebizhcarge Payment Gateway";

                    let soapResponse = {
                        error: true,
                        payment_method_id: null,
                        message: msgTxt,
                        exception: e
                    };
                    EBizSoapApiClientModel.loaderText = "<span style='color:red'>" + msgTxt + " Error: " + e.statusText + "</span>";
                    EBizSoapApiClientModel.messageProcessor(EBizSoapApiClientModel.loaderText, true);

                    /** resolving back Error Response **/
                    errorReject(soapResponse);

                }).always(e => {

                });

            }
        });
    }
    ,

    /**
     * Get Line Items
     *
     * @param $
     * @param requestParams
     * @returns {*}
     */
    getLineItems: function ($, requestParams) {
        let quoteItems = EBizSoapApiClientModel.lineItems;
        let soapItems = quoteItems.map(function (quoteItem) {

            let lineSoapItem =
                '<ebiz:LineItem>' +
                ' <!--Optional:-->' +
                '<ebiz:DiscountRate>' + quoteItem.discount_percent + '</ebiz:DiscountRate>' +
                '<!--Optional:-->' +
                '<ebiz:ProductRefNum>' + quoteItem.ebiz_internal_id + '</ebiz:ProductRefNum>' +
                '<!--Optional:-->' +
                '<ebiz:SKU>' + quoteItem.item_sku + '</ebiz:SKU>' +
                '<!--Optional:-->' +
                '<ebiz:CommodityCode></ebiz:CommodityCode>' +
                '<!--Optional:-->' +
                '<ebiz:ProductName>' + quoteItem.item_name + '</ebiz:ProductName>' +
                '<!--Optional:-->' +
                '<ebiz:Description>' + quoteItem.item_description + '</ebiz:Description>' +
                '<!--Optional:-->' +
                '<ebiz:DiscountAmount>' + quoteItem.item_discount_amount + '</ebiz:DiscountAmount>' +
                '<!--Optional:-->' +
                '<ebiz:TaxRate>' + quoteItem.item_tax_percent + '</ebiz:TaxRate>' +
                '<!--Optional:-->' +
                '<ebiz:UnitOfMeasure></ebiz:UnitOfMeasure>' +
                '<!--Optional:-->' +
                '<ebiz:UnitPrice>' + quoteItem.item_price_incl_tax + '</ebiz:UnitPrice>' +
                '<!--Optional:-->' +
                '<ebiz:Qty>' + quoteItem.item_qty + '</ebiz:Qty>' +
                '<ebiz:Taxable>' + quoteItem.item_is_taxable + '</ebiz:Taxable>' +
                '<!--Optional:-->' +
                '<ebiz:TaxAmount>' + quoteItem.item_tax_amount + '</ebiz:TaxAmount>' +
                '</ebiz:LineItem>'
            ;

            return lineSoapItem;

        }).join("");

        EBizSoapApiClientModel.soapLineItems = soapItems;

        return soapItems;

    }
    ,

    /**
     *  Prepare Response Node
     * @param xmlObjNode
     * @param nodeField
     * @returns {*}
     */
    prepareResponseNode: function (xmlObjNode, nodeField) {


        /** prepare Node **/
        return typeof (xmlObjNode.getElementsByTagName(nodeField)[0]) !== "undefined" ? xmlObjNode.getElementsByTagName(nodeField)[0].textContent : "";
    }
    ,
    /**
     * Get Ebizcharge customer
     * @param $
     * @param requestParams
     * @returns {jQuery.soapResponse}
     */
    getEbizCustomer: function ($, requestParams) {

        /** Defining the Promise when Ajax Request gets completed **/
        return new Promise((successResolve, errorReject) => {
            /** assigning customer id **/
            let customerId = EBizSoapApiClientModel.ebiz_customer_id;

            /** Soap Response **/
            if (typeof customerId !== 'undefined' && !customerId) {
                let soapResponse = {
                    error: true,
                    message: 'Ebizcharge Customer Id does not exists Ebiz Customer Id: ' + customerId,
                    exception: 'exception occurred'
                };
                /** if success Resolve **/
                successResolve(soapResponse);
                return false;
            }

            if (customerId !== '') {

                let customerRequest =
                    '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ebiz="http://eBizCharge.ServiceModel.SOAP">' +
                    '<soapenv:Header/>' +
                    '<soapenv:Body>' +
                    '<ebiz:GetCustomer>' +
                    '<ebiz:securityToken>' +
                    '<ebiz:SecurityId>' + this.dtr(this.eMak) + '</ebiz:SecurityId>' +
                    '<ebiz:UserId></ebiz:UserId>' +
                    '<ebiz:Password></ebiz:Password>' +
                    '</ebiz:securityToken>' +
                    '<ebiz:customerId>' + customerId + '</ebiz:customerId>' +
                    '<ebiz:start>0</ebiz:start>' +
                    '<ebiz:limit>1</ebiz:limit>' +
                    '</ebiz:GetCustomer>' +
                    '</soapenv:Body>' +
                    '</soapenv:Envelope>';

                let parser = new DOMParser();
                customerRequest = parser.parseFromString(customerRequest, "text/xml");

                /** define a SOAP Response **/
                /*** sending ajax request to fech data **/
                $.ajax(EBizSoapApiClientModel.getEbizSOAPClient($, 'GetCustomer', customerRequest))
                    .done(customerResult => {

                        let ebiz_customer_internal_id = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'CustomerInternalId');
                        let ebiz_customer_token = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'CustomerToken');
                        let ebiz_customer_id = EBizSoapApiClientModel.prepareResponseNode(customerResult, 'CustomerId');

                        let msgTxt = "Success, found customer with customer Id ";

                        /** soap Response **/
                        let soapResponse = {
                            ebiz_customer_internal_id: ebiz_customer_internal_id,
                            ebiz_customer_token: ebiz_customer_token,
                            ebiz_customer_id: ebiz_customer_id,
                            message: msgTxt + ebiz_customer_id + " at Ebizcharge Payment Gateway",
                            error: false,
                            exception: false
                        };
                        EBizSoapApiClientModel.loaderText = "<span style='color:green'>" + msgTxt + "</span>";
                        EBizSoapApiClientModel.messageProcessor(EBizSoapApiClientModel.loaderText, true);

                        /** soap callback success message **/
                        successResolve(soapResponse);

                    }).fail(e => {

                    var msgTxt = "Exception occurred during Get Customer from EBizCharge payment Gateway Error:";

                    let soapResponse = {
                        error: true,
                        message: msgTxt,
                        exception: e
                    };
                    EBizSoapApiClientModel.loaderText = "<span style='color:#ff0000'>" + msgTxt + " Error: " + e.statusText + "</span>";
                    // console.log('Exception Occurred during adding Customer: ', e);
                    EBizSoapApiClientModel.messageProcessor(EBizSoapApiClientModel.loaderText, true);
                    /** in case of any error exception will be called back **/
                    errorReject(soapResponse);

                }).always(e => {
                    //console.log("add action called");
                });
            }
        }); /** returning the promise **/

    }
    ,
    /**
     * Add Ebizcharge Data to Magento Customer
     *
     * @param $
     * @param customer_id
     * @param requestParams
     */
    addEbizchargeDataToCustomer: function ($, customer_id, requestParams) {

    }
    ,
    /**
     * Run Customer Transaction SOAP Request
     *
     * @param $
     * @param requestParams
     * @returns {jQuery.soapResponse}
     */
    runCustomerTransactionSoapRequest: function ($, requestParams = null) {

        /** Defining the Promise when Ajax Request gets completed **/
        return new Promise((successResolve, errorReject) => {

            /**
             * Start of XML SOAP Request
             *
             * @type {string}
             */
            EBizSoapApiClientModel.xmlSoap = '';

            /**
             * Customer Id
             */
            let customerId = EBizSoapApiClientModel.customerId;
            if (customerId !== '') {

                let customerTransactionRequest =
                    '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ebiz="http://eBizCharge.ServiceModel.SOAP">' +
                    '   <soapenv:Header/>' +
                    '   <soapenv:Body>' +
                    '      <ebiz:runCustomerTransaction>' +
                    '         <!--Optional:-->' +
                    '         <ebiz:securityToken>' +
                    '            <ebiz:SecurityId>' + EBizSoapApiClientModel.dtr(EBizSoapApiClientModel.eBk) + '</ebiz:SecurityId>' +
                    '            <ebiz:UserId>' + EBizSoapApiClientModel.dtr(EBizSoapApiClientModel.eBu) + '</ebiz:UserId>' +
                    '            <ebiz:Password>' + EBizSoapApiClientModel.dtr(EBizSoapApiClientModel.eBu) + '</ebiz:Password>' +
                    '          </ebiz:securityToken>' +
                    '         <!--Optional:-->' +
                    '         <ebiz:custNum>' + EBizSoapApiClientModel.customerToken + '</ebiz:custNum>' +
                    '         <!--Optional:-->' +
                    '         <ebiz:paymentMethodID>' + EBizSoapApiClientModel.ebizMethodId + '</ebiz:paymentMethodID>' +
                    '         <!--Optional:-->' +
                    '         <ebiz:tran>' +
                    '            <ebiz:isRecurring>' + EBizSoapApiClientModel.isRecurring + '</ebiz:isRecurring>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:InventoryLocation>' + EBizSoapApiClientModel.inventoryLocation + '</ebiz:InventoryLocation>' +
                    '            <ebiz:IgnoreDuplicate>' + EBizSoapApiClientModel.ignoreDuplicate + '</ebiz:IgnoreDuplicate>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:Details>' +
                    '               <ebiz:NonTax>' + EBizSoapApiClientModel.isNonTax + '</ebiz:NonTax>' +
                    '               <ebiz:Tax>' + EBizSoapApiClientModel.taxAmount + '</ebiz:Tax>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Table>' + EBizSoapApiClientModel.table + '</ebiz:Table>' +
                    '               <ebiz:Subtotal>' + EBizSoapApiClientModel.subTotal + '</ebiz:Subtotal>' +
                    '               <ebiz:Shipping>' + EBizSoapApiClientModel.shippingAmount + '</ebiz:Shipping>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:ShipFromZip>' + EBizSoapApiClientModel.shipZipCode + '</ebiz:ShipFromZip>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:SessionID>' + EBizSoapApiClientModel.sessionId + '</ebiz:SessionID>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:PONum>' + EBizSoapApiClientModel.poNumber + '</ebiz:PONum>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:OrderID>' + EBizSoapApiClientModel.orderNumber + '</ebiz:OrderID>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Invoice>' + EBizSoapApiClientModel.invoiceNumber + '</ebiz:Invoice>' +
                    '               <ebiz:Duty>' + EBizSoapApiClientModel.dutyAmount + '</ebiz:Duty>' +
                    '               <ebiz:Discount>' + EBizSoapApiClientModel.discountAmount + '</ebiz:Discount>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Comments>' + EBizSoapApiClientModel.orderComments + '</ebiz:Comments>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Description>' + EBizSoapApiClientModel.orderDescription + '</ebiz:Description>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Currency>' + EBizSoapApiClientModel.orderCurrency + '</ebiz:Currency>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Clerk>' + EBizSoapApiClientModel.merchantCode + '</ebiz:Clerk>' +
                    '               <ebiz:Amount>' + EBizSoapApiClientModel.ordredAmount + '</ebiz:Amount>' +
                    '               <ebiz:AllowPartialAuth>' + EBizSoapApiClientModel.allowPartialAuth + '</ebiz:AllowPartialAuth>' +
                    '               <!--Optional:-->' +
                    '               <ebiz:Terminal>' + EBizSoapApiClientModel.terminal + '</ebiz:Terminal>' +
                    '               <ebiz:Tip>' + EBizSoapApiClientModel.orderTip + '</ebiz:Tip>' +
                    '            </ebiz:Details>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:MerchReceiptName>' + EBizSoapApiClientModel.merchantName + '</ebiz:MerchReceiptName>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:MerchReceiptEmail>' + EBizSoapApiClientModel.merchantReceiptName + '</ebiz:MerchReceiptEmail>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:Software>' + EBizSoapApiClientModel.softwareId + '</ebiz:Software>' +
                    '            <ebiz:MerchReceipt>' + EBizSoapApiClientModel.merchantReceipt + '</ebiz:MerchReceipt>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:CustReceiptName>' + EBizSoapApiClientModel.customerReceiptName + '</ebiz:CustReceiptName>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:CustReceiptEmail>' + EBizSoapApiClientModel.customerReceiptEmail + '</ebiz:CustReceiptEmail>' +
                    '            <ebiz:CustReceipt>' + EBizSoapApiClientModel.customerReceiptNumber + '</ebiz:CustReceipt>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:ClientIP>' + EBizSoapApiClientModel.clientIp + '</ebiz:ClientIP>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:CardCode>' + EBizSoapApiClientModel.cardCode + '</ebiz:CardCode>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:Command>' + EBizSoapApiClientModel.saleCommand + '</ebiz:Command>' +
                    '            <!--Optional:-->' +
                    '            <ebiz:LineItems>' +
                    EBizSoapApiClientModel.soapLineItems +
                    '            </ebiz:LineItems>';

                if (EBizSoapApiClientModel.paymentRefNumber !== null) {
                    customerTransactionRequest += '            <ebiz:RefNum>' + EBizSoapApiClientModel.paymentRefNumber + '</ebiz:RefNum>';
                }

                customerTransactionRequest +=
                    '         </ebiz:tran>' +
                    '      </ebiz:runCustomerTransaction>' +
                    '   </soapenv:Body>' +
                    '</soapenv:Envelope>'
                ;


                let parser = new DOMParser();
                var runCustomerTransactionRequest = parser.parseFromString(customerTransactionRequest, "text/xml");

                //console.log(runCustomerTransactionRequest);

                /***
                 * sending ajax request to fech data
                 * **/
                $.ajax(EBizSoapApiClientModel.getEbizSOAPClient($, 'runCustomerTransaction', runCustomerTransactionRequest))
                    .done(customerRunTransactionResult => {

                        //console.log(customerRunTransactionResult);
                        /** soap Response **/
                        let soapResponse = {
                            transaction_data: {
                                customer_token: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'CustNum'),
                                result_code: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'ResultCode'),
                                result: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'Result'),
                                remaining_balance: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'RemainingBalance'),
                                ref_num: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'RefNum'),
                                is_duplicate: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'isDuplicate'),
                                error_code: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'ErrorCode'),
                                error: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'Error'),
                                converted_amount_currency: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'ConvertedAmountCurrency'),
                                converted_amount: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'ConvertedAmount'),
                                conversion_rate: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'ConversionRate'),
                                card_code_result_code: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'CardCodeResultCode'),
                                card_code_result: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'CardCodeResult'),
                                batch_ref_num: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'BatchRefNum'),
                                batch_num: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'BatchNum'),
                                avs_result_code: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'AvsResultCode'),
                                avs_result: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'AvsResult'),
                                auth_code: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'AuthCode'),
                                auth_amount: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'AuthAmount'),
                                status: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'Status'),
                                status_code: EBizSoapApiClientModel.prepareResponseNode(customerRunTransactionResult, 'StatusCode'),
                            },
                            message: $.mage.__("Success, customer transaction has been completed successfully") + customerId,
                            error: false
                        };
                        /** sending Call back Soap Response Success **/
                        successResolve(soapResponse);

                    }).fail(
                    e => {
                        /** failed request **/
                        let soapResponse = {
                            error: true,
                            transaction_data: {},
                            'message': $.mage.__("Exception occurred during running customer transaction from EBizCharge Error:") + e
                        };
                        /** Error Reject Soap Response Call back **/
                        errorReject(soapResponse);

                    }).always(
                    e => {
                        //console.log("add action called");
                        //console.log(e);
                        /** failed request **/
                        let soapResponse = {
                            error: true,
                            transaction_data: {},
                            'message': $.mage.__("Exception occurred during running customer transaction from EBizCharge Error:") + e
                        };
                        /** Error Reject Soap Response Call back **/
                        errorReject(soapResponse);

                    });
            }

        });
    }
    ,
    /**
     * Get Card Type
     *
     * @param $
     * @param requestParams
     */
    getCardType: function ($, requestParams) {
        let value = this.getDataValue('ccnumber');
        var result = '';

        if (/^5[12345]\d{14}$/.test(value)) {
            result = "MasterCard";

        } else if (/^4\d{12}(\d\d\d){0,1}$/.test(value)) {
            result = "Visa";

        } else if (/^6011\d{12}$/.test(value)) {
            result = "Discover";

        } else if (/^3[47]/.test(value)) {
            result = "American Express";
        }

        if (result !== '') {
            this.htmlForm.querySelector('[data-id="cardType"]').value = result;
        }
    }
    ,

    /** decoding the string from Base 64 **/
    /**
     * Decode String
     *
     * @param str
     * @returns {string}
     */
    dtr: function (str) {
        let st = atob(str);
        return st.replace(EBizSoapApiClientModel.alg, '');
    }
    ,
    /** encoding hte string with base 64 **/
    /**
     * Encode String
     *
     * @param str
     * @returns {string}
     */
    encodeStr: function (str) {
        return btoa(str);
    }
    ,
    /**
     * Message Processor
     *
     * @param message
     * @param showFlag
     * @returns {*|jQuery}
     */
    messageProcessor: function ($, message = '', showFlag = false) {
        if (showFlag) {
            return $('#pci_compliance_messages').html(message).show();
        } else {
            return $('#pci_compliance_messages').html(message).hide();
        }

    }
    ,
    /**
     * Run Transaction Soap Request
     *
     * @param $
     * @param requestParams
     * @returns {jQuery.soapResponse}
     */
    runTransactionSoapRequest: function ($, requestSoapParams = {}) {
        /**
         * Define Promise for running Transaction
         *  runTransaction end point
         *
         */
        return new Promise((successResolve, errorReject) => {

            /**
             * Start of SoapXML
             *
             * @type {string}
             */
            this.soapXml = '';

            let xmlSoapRequest = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ebiz="http://eBizCharge.ServiceModel.SOAP">' +
                '   <soapenv:Header/>' +
                '   <soapenv:Body>' +
                '      <ebiz:runTransaction>' +
                '         <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapClientKeys() +
                '         <!--Optional:-->' +
                '         <ebiz:tran>' +
                '            <!--Optional:-->' +
                '            <ebiz:CustReceiptName>' + EBizSoapApiClientModel.customerReceiptName + '</ebiz:CustReceiptName>' +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapXmlRecurringDetailParams() +
                '            <!--Optional:-->' +
                '            <ebiz:LineItems>' +
                '               <!--Zero or more repetitions:-->' +
                EBizSoapApiClientModel.soapLineItems +
                '            </ebiz:LineItems>' +
                '            <ebiz:IsRecurring>' + EBizSoapApiClientModel.isRecurring + '</ebiz:IsRecurring>' +
                '            <!--Optional:-->' +
                '            <ebiz:InventoryLocation>' + EBizSoapApiClientModel.inventoryLocation + '</ebiz:InventoryLocation>' +
                '            <ebiz:IgnoreDuplicate>' + EBizSoapApiClientModel.ignoreDuplicate + '</ebiz:IgnoreDuplicate>' +
                '            <!--Optional:-->' +
                '            <ebiz:IfAuthExpired>' + EBizSoapApiClientModel.isAuthExpired + '</ebiz:IfAuthExpired>' +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapXmlTransactionDetailParams() +
                '            <!--Optional:-->' +
                '            <ebiz:Software>' + EBizSoapApiClientModel.softwareId + '</ebiz:Software>' +
                '            <ebiz:CustReceipt>' + EBizSoapApiClientModel.customerReceiptNumber + '</ebiz:CustReceipt>' +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getOrderCustomFields() +
                '            <!--Optional:-->' +
                '            <ebiz:CustomerID>' + EBizSoapApiClientModel.ebizCustomerId + '</ebiz:CustomerID>' +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapXmlCreditCardParams() +
                '            <!--Optional:-->' +
                '            <ebiz:Command>' + EBizSoapApiClientModel.saleCommand + '</ebiz:Command>' +
                '            <!--Optional:-->' +
                '            <ebiz:ClientIP>' + EBizSoapApiClientModel.clientIp + '</ebiz:ClientIP>' +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapXmlBankAccountParams() +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapXmlBillingAddressParams() +
                '            <!--Optional:-->' +
                '            <ebiz:AuthCode>' + EBizSoapApiClientModel.authCode + '</ebiz:AuthCode>' +
                '            <!--Optional:-->' +
                '            <ebiz:AccountHolder>' + EBizSoapApiClientModel.accountHolder + '</ebiz:AccountHolder>' +
                '            <!--Optional:-->' +
                '            <ebiz:RefNum>' + EBizSoapApiClientModel.transactionReferenceNumber + '</ebiz:RefNum>' +
                '            <!--Optional:-->' +
                EBizSoapApiClientModel.getSoapXmlShippingAddressParams() +
                '         </ebiz:tran>' +
                '      </ebiz:runTransaction>' +
                '   </soapenv:Body>' +
                '</soapenv:Envelope>'
            ;

            let parser = new DOMParser();
            var runTransactionRequest = parser.parseFromString(xmlSoapRequest, "text/xml");
            //console.log(runTransactionRequest);


            /** extend  jquery as an object for rendering back the response **/
            $.ajax(EBizSoapApiClientModel.getEbizSOAPClient($, 'runTransaction', runTransactionRequest))
                .done(runTransactionResult => {
                    //  errorReject(soapResponse);
                }).fail(
                e => {
                    //   this.hideLoader();
                    let soapResponse = {
                        transaction_data: {},
                        error: true,
                        message: $.mage.__("Exception occurred during Get Customer from EBizCharge Payment Gateway Error:") + e
                    };
                    //console.log(e);
                    errorReject(soapResponse);

                }).always(runTransactionResult => {
                    let resultCode = EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'ResultCode');
                    let errorMessage = EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'Error');
                    let soapResponse = {
                        transaction_data: {
                            customer_token: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'CustNum'),
                            result_code: resultCode,
                            result: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'Result'),
                            remaining_balance: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'RemainingBalance'),
                            ref_num: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'RefNum'),
                            is_duplicate: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'isDuplicate'),
                            error_code: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'ErrorCode'),
                            error: errorMessage,
                            converted_amount_currency: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'ConvertedAmountCurrency'),
                            converted_amount: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'ConvertedAmount'),
                            conversion_rate: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'ConversionRate'),
                            card_level_result_code: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'CardLevelResultCode'),
                            card_level_result: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'CardLevelResult'),
                            card_code_result_code: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'CardCodeResultCode'),
                            card_code_result: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'CardCodeResult'),
                            batch_ref_num: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'BatchRefNum'),
                            batch_num: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'BatchNum'),
                            avs_result_code: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'AvsResultCode'),
                            avs_result: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'AvsResult'),
                            auth_code: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'AuthCode'),
                            auth_amount: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'AuthAmount'),
                            status: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'Status'),
                            status_code: EBizSoapApiClientModel.prepareResponseNode(runTransactionResult, 'StatusCode'),
                        },
                        error: resultCode !== 'A',
                        message: errorMessage ? errorMessage : $.mage.__("Success Run Transaction got Successful from EBizCharge Payment Gateway")
                    };

                //console.log(soapResponse);
                successResolve(soapResponse);
            });


        });
    },

    /**
     * SOAP Api Gt Soap Client
     *
     * @param soapEndpoint
     * @param soapApiRequestData
     * @returns {{beforeSend: beforeSend, headers: {SOAPAction: string}, cache: boolean, processData: boolean, data: {}, dataType: string, crossOrigin: boolean, crossDomain: boolean, type: string, contentType: string, showLoader: boolean, url: *}}
     */
    getEbizSOAPClient: function ($, soapEndpoint = "", soapApiRequestData = {}) {


        return {
            //  url: this.soapGatewayUrl,
            url: this.soapGateway.pci_action_gateway_url,
            // url: "https://soap.ebizcharge.net/eBizService.svc?wsdl",
            type: "POST",
            dataType: "xml",
            processData: false,
            crossOrigin: true,
            crossDomain: true,
            CORS: true,
            cache: false,
            contentType: "text/xml",
            data: soapApiRequestData,
            showLoader: true,
            headers: {
                SOAPAction: "http://eBizCharge.ServiceModel.SOAP/IeBizService/" + soapEndpoint,
                'Access-Control-Allow-Origin': '*',
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Authorization", "Basic " + btoa(""));
                var loaderText = EBizSoapApiClientModel.loaderText;
                //   EBizSoapApiClientModel.messageProcessor($, loaderText, true);
            },
            success: function (xmlResponse) {
                // successResolve(xmlResponse);
            },
            error: function (xmlError) {
                // errorReject(xmlError);
            }
        }
            ;

    }

}

