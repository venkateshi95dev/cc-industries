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
    'Magento_Customer/js/customer-data',
    'Ebizcharge_Ebizcharge/js/checkout/gateway/ebizcharge-gateway'
], function ($, customerData, EbizChargeGateway) {
    'use strict';

    return function (configModelData) {
        return EBizChargeMultiShippingPlaceOrder._init($, customerData, EbizChargeGateway, configModelData);
    }
});

let EBizChargeMultiShippingPlaceOrder = {
    /**
     * Variables default values
     */
    quote: {},
    paymentData: {},
    forceSubmit: false,
    customerData: {},
    isRecurringEnabled: false,
    isMultiShippingCheckout: true,
    multiShippingCheckoutFormId: null,

    /**
     * Init function
     *
     * @param $
     * @param customerData
     * @param EbizChargeGateway
     * @param configModelData
     * @private
     */
    _init: function ($, customerData, EbizChargeGateway, configModelData) {
        /**
         * Default var
         */
        this.quote = configModelData.quote;
        this.paymentData = configModelData.payment;
        this.customerData = customerData.get('customer')._latestValue ?? {};
        this.isRecurringEnabled = configModelData.isRecurringEnabled;
        this.multiShippingCheckoutFormId = $(document.getElementById('review-order-form'));

        /**
         * Submit Form
         */
        //EBizChargeMultiShippingPlaceOrder.placeOrder($);
    },

    /**
     * Get email
     *
     * @returns {null|*}
     */
    getEmail: function () {
        return this.quote.email ?? '';
    },

    /**
     * Is guest user
     *
     * @returns {boolean}
     */
    isGuest: function () {
        return !!this.quote.isGuest;
    },

    /**
     * Get customer data
     *
     * @returns {Object}
     */
    getCustomerData: function () {
        return this.customerData;
    },

    /**
     * Get customer billing address
     *
     * @returns {*}
     */
    getCustomerBillingAddress: function () {
        return this.quote.billingAddress ?? {};
    },

    /**
     * Get customer shipping address
     *
     * @returns {*}
     */
    getCustomerShippingAddress: function () {
        return this.quote.shippingAddress ?? {};
    },

    /**
     * Get first Name
     *
     * @returns {*}
     */
    getFirstName: function () {
        let isGuest = this.isGuest();
        let firstName = "";
        if (isGuest) {
            firstName = this.getCustomerBillingAddress().firstname;
        } else {
            let customerData = this.getCustomerData();
            firstName = customerData.firstname;
        }
        return firstName;

    },

    /**
     * Get last name
     *
     * @returns {*}
     */
    getLastName: function () {
        return this.getCustomerBillingAddress().lastname ?? '';
    },

    /**
     * Get address
     *
     * @returns {string}
     */
    getAddress: function () {
        return this.getCustomerBillingAddress().street + ', ' + this.getCustomerBillingAddress().postcode + ' ' + this.getCustomerBillingAddress().city;
    },

    /**
     * Get phone
     *
     * @returns {*}
     */
    getPhone: function () {
        return this.getCustomerBillingAddress().telephone;
    },

    /**
     * Get data for form
     *
     * @returns {{additional_data: (*|{}|{}), method: string}}
     */
    getData: function () {
        let additionalData = {
            'cc_cid': this.paymentData.cc_cid ?? '',
            'cc_type': this.paymentData.cc_type ?? '',
            'cc_exp_year': this.paymentData.cc_exp_year ?? '',
            'cc_exp_month': this.paymentData.cc_exp_month ?? '',
            'cc_number': this.paymentData.cc_number ?? '',
            'ach_routing': this.paymentData.ach_routing ?? '',
            'ach_type': this.paymentData.ach_type ?? '',
            'cc_owner': this.paymentData.cc_owner ?? '',
            'ebzc_avs_street': this.paymentData.cc_avs_street ?? '',
            'ebzc_avs_zip': this.paymentData.cc_avs_zip ?? '',
            'ebzc_option': this.paymentData.ebzc_option ?? '',
            'ebzc_method_id': this.paymentData.ebzc_method_id ?? '',
            'ebzc_cust_id': this.paymentData.ebzc_cust_id ?? '',
            'ebzc_save_payment': !!(this.paymentData.ebzc_save_payment && this.paymentData.ebzc_save_payment == '1'),
            'paymentToken': !this.paymentData.ebzc_option,
            'ebzc_option_type': this.paymentData.ebzc_option_type ?? '',
            'rec_admin': this.isRecurringEnabled,
            'transaction_info' : ''
        };

        return {
            'method': this.paymentData.method ?? 'ebizcharge_ebizcharge',
            'additional_data': additionalData
        };
    },

    /**
     * Prepare PCI Compliance Order Data
     *
     * @param $
     * @returns {{quote: *, isGuest: boolean, shippingMethod: *, paymentMethod: *, customerData: {firstName: *, lastName: *, address: string, data: *, phone: *, shippingAddress: *, billingAddress: *, email: (*|null)}, formData: {additional_data: (*|{}|{}), method: string}, items}}
     */
    preparePciComplianceOrderData: function ($) {
        return {
            isGuest: this.isGuest(),
            customerData: {
                firstName: this.getFirstName(),
                lastName: this.getLastName(),
                address: this.getAddress(),
                phone: this.getPhone(),
                email: this.getEmail(),
                billingAddress: this.getCustomerBillingAddress(),
                shippingAddress: this.getCustomerShippingAddress(),
                data: this.getCustomerData()
            },
            quote: this.quote.quote,
            formData: this.getData(),
            items: this.quote.items,
            shippingMethod: this.quote.shippingMethod,
            paymentMethod: this.quote.paymentMethod
        }
    },

    /**
     * Place order
     *
     * @param $
     */
    placeOrder: function ($) {
        /**
         * Bind submit function
         */
        EBizChargeMultiShippingPlaceOrder.multiShippingCheckoutFormId.bind('submit', function (evt) {
            /**
             * If force submit is true
             */
            if (EBizChargeMultiShippingPlaceOrder.forceSubmit) {
                return true;
            }

            evt.preventDefault();
            let checkoutModel = EBizChargeMultiShippingPlaceOrder;

            /**
             * Prepare PCi Compliance Order Data
             *
             * @type {{}}
             */
            let orderData = EBizChargeMultiShippingPlaceOrder.preparePciComplianceOrderData($);

            /**
             * Fully PCI Compliance based Order Placement
             * Place transaction directly to EbizCharge and then get back
             * and Place order at Magento Locally
             *
             * @type {boolean}
             */
            let pciOrderResp = EBizSoapApiClientModel.placePciComplianceOrder($, orderData, checkoutModel);
        });
    }
}
