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
    'mage/translate',
    'domReady'
], function ($, _, trans, domReady) {
    'use strict';

    return function () {
        /**
         * Validating of AVS CVV Code
         */
        return EBizChargeGatewayModel._init($, _, trans, domReady);
    }
});

let EBizChargeGatewayModel = {
    /**
     * Initializing variables
     */
    //formData: null,

    _init($, _, trans, domReady) {
        //this.formData = {}
    },

    /**
     * Extract form fields from form serialized array
     *
     * @param formArray
     * @returns {{}}
     */
    getFormFields: function (formArray = null) {
        let formData = {};
        if (formArray) {
            formArray.forEach(field => {
                if (formData[field.name] === undefined) {
                    formData[field.name] = field.value;
                }
            });
        }
        return formData;
    },

    /**
     * Get street address value from form array
     *
     * @param formData
     * @returns {string}
     */
    getFormStreet: function (formData) {
        let street = '';
        if (formData['street[]'] !== undefined) {
            street = formData['street[]'];
        } else if (formData['payment[avs_street]'] !== undefined) {
            street = formData['payment[avs_street]'];
        } else if (formData['payment[cc_avs_street]'] !== undefined) {
            street = formData['payment[cc_avs_street]'];
        }
        return street;
    },

    /**
     * Get zip code value from form array
     *
     * @param formData
     * @returns {string}
     */
    getFormZip: function (formData) {
        let street = '';
        if (formData.postcode !== undefined) {
            street = formData.postcode;
        } else if (formData['payment[avs_zip]'] !== undefined) {
            street = formData['payment[avs_zip]'];
        } else if (formData['payment[cc_avs_zip]'] !== undefined) {
            street = formData['payment[cc_avs_zip]'];
        }
        return street;
    },

    /**
     * Get payment option type
     *
     * @param formData
     * @param ach
     * @returns {string}
     */
    getEbizOptionType: function (formData = {}, ach = false) {
        let optionType = 'credit_card';
        if (formData['payment[ebzc_option_type]'] !== undefined) {
            optionType = formData['payment[ebzc_option_type]'] === 'CC' || formData['payment[ebzc_option_type]'] === 'cc' ? 'credit_card' : formData['payment[ebzc_option_type]'];
        } else if (ach) {
            optionType = 'ACH';
        }
        return optionType;
    },

    /**
     * Get CC Owner value
     *
     * @param formData
     * @param ach
     * @returns {*|string|string}
     */
    getCcOwner: function (formData = {}, ach = false) {
        let ccOwner = '';
        if (ach) {
            ccOwner = formData['payment[ach_holder]'] ?? (formData['ach_holder'] ?? '');
            return ccOwner;
        }

        if (formData['payment[cc_holder]'] !== undefined) {
            ccOwner = formData['payment[cc_holder]'];
        } else if (formData['payment[cc_owner]'] !== undefined) {
            ccOwner = formData['payment[cc_owner]'];
        }

        return ccOwner;
    },

    /**
     * Get CC number value
     *
     * @param formData
     * @param ach
     * @returns {string}
     */
    getCcNumber: function (formData = {}, ach = false) {
        let ccNumber = '';
        if (ach) {
            ccNumber = formData['payment[ach_number]'] ?? (formData['ach_number'] ?? '');
            return ccNumber;
        }

        if (formData['payment[cc_number]'] !== undefined) {
            ccNumber = formData['payment[cc_number]'];
        }
        return ccNumber;
    },

    /**
     * Prepare pre-auth transaction quote
     *
     * @returns {{tax_amount: number, subtotal_incl_tax: number, discount_amount: number, grand_total: number, shipping_incl_tax: number}}
     */
    preparePreAuthQuote: function () {
        return {
            tax_amount: 0,
            grand_total: 0.05,
            discount_amount: 0,
            shipping_incl_tax: 0,
            subtotal_incl_tax: 0.05
        }
    },

    /**
     * Get pre auth line items
     *
     * @returns {((function(*): number)|(function(*): string)|(function(*): string)|(function(*): string)|(function(*): string))[][]}
     */
    // getPreAuthLineItems: function () {
    //     return [
    //         [
    //             discount_percent => 0,
    //             ebiz_internal_id => 'ref-preauth-product',
    //             item_sku => 'pre-auth-product',
    //             item_name => 'PreAuth Product',
    //             item_description => 'PreAuth Product',
    //             item_discount_amount => 0,
    //             item_tax_percent => 0,
    //             item_price_incl_tax => 0.05,
    //             item_qty => 1,
    //             item_is_taxable => 'N',
    //             item_tax_amount => 0
    //         ]
    //     ]
    // },

    /**
     * Prepare Ebiz form data
     *
     * @param formData
     * @param ach
     * @returns {{quote: {}, shippingMethod: {}, paymentMethod: {}, formData: {additional_data: {cc_exp_month: string, ebzc_avs_zip: (string|number|*), ebzc_option: string, ebzc_cust_id: (string|*), cc_exp_year: string, cc_cid: string, cc_type: string, ebzc_method_id: string, ebzc_save_payment: boolean, ebzc_avs_street, cc_owner: string, ebzc_option_type: string, cc_number: string}}, customerData: {shippingAddress: {}, billingAddress: {}}}}
     */
    prepareEbizFormData: function (formData = {}, ach = false) {
        return {
            formData: {
                additional_data: {
                    ebzc_save_payment: true,
                    ebzc_option: 'new',
                    ebzc_method_id: formData['payment[ebiz_option]'] ?? '',
                    ebzc_option_type: EBizChargeGatewayModel.getEbizOptionType(formData, ach),
                    ebzc_cust_id: formData.ebzc_cust_id ?? '',
                    cc_cid: formData['payment[cc_cid]'] ?? '',
                    cc_exp_month: formData['payment[cc_exp_month]'] ?? '',
                    cc_exp_year: formData['payment[cc_exp_year]'] ?? '',
                    cc_owner: EBizChargeGatewayModel.getCcOwner(formData, ach),
                    cc_number: EBizChargeGatewayModel.getCcNumber(formData, ach),
                    cc_type: formData['payment[cc_type]'] ?? '',
                    ebzc_avs_street: EBizChargeGatewayModel.getFormStreet(formData),
                    ebzc_avs_zip: EBizChargeGatewayModel.getFormZip(formData),
                    ach_routing: formData['payment[ach_route]'] ?? (formData['ach_route'] ?? ''),
                    ach_type: formData['payment[ach_type]'] ?? (formData['ach_type'] ?? ''),
                    is_default: formData['payment[is_default]'] ?? (formData['default'] ?? 0)
                }
            },
            shippingMethod: 'flaterate_flaterate',
            paymentMethod : 'ebizcharge_ebizcharge',
            customerData: {
                billingAddress: {
                    city: typeof formData.city !== undefined ? formData.city : '',
                    region: typeof formData.region !== undefined ? formData.region : '',
                    street: typeof formData['street[]'] !== undefined ? formData['street[]'] : '',
                    company: typeof formData.company !== undefined ? formData.company : '',
                    email: typeof formData.email !== undefined ? formData.email : '',
                    lastname: typeof formData.lastname !== undefined ? formData.lastname : '',
                    postcode: typeof formData.postcode !== undefined ? formData.postcode : '',
                    regionId: typeof formData.region_id !== undefined ? formData.region_id : '',
                    firstname: typeof formData.firstname !== undefined ? formData.firstname : '',
                    telephone: typeof formData.telephone !== undefined ? formData.telephone : '',
                    countryId: typeof formData.country_id !== undefined ? formData.country_id : '',
                    customerId: typeof formData.customer_id !== undefined ? formData.customer_id : '',
                    ebzc_cust_id: typeof formData.ebzc_cust_id !== undefined ? formData.ebzc_cust_id : '',
                    customerAccount: typeof formData.customer_account !== undefined ? formData.customer_account : ''
                },
                shippingAddress: {
                    city: typeof formData.city !== undefined ? formData.city : '',
                    region: typeof formData.region !== undefined ? formData.region : '',
                    street: typeof formData['street[]'] !== undefined ? formData['street[]'] : '',
                    company: typeof formData.company !== undefined ? formData.company : '',
                    email: typeof formData.email !== undefined ? formData.email : '',
                    lastname: typeof formData.lastname !== undefined ? formData.lastname : '',
                    postcode: typeof formData.postcode !== undefined ? formData.postcode : '',
                    regionId: typeof formData.region_id !== undefined ? formData.region_id : '',
                    firstname: typeof formData.firstname !== undefined ? formData.firstname : '',
                    telephone: typeof formData.telephone !== undefined ? formData.telephone : '',
                    countryId: typeof formData.country_id !== undefined ? formData.country_id : '',
                    customerId: typeof formData.customer_id !== undefined ? formData.customer_id : '',
                    ebzc_cust_id: typeof formData.ebzc_cust_id !== undefined ? formData.ebzc_cust_id : '',
                    customerAccount: typeof formData.customer_account !== undefined ? formData.customer_account : ''
                }
            },
            quote: EBizChargeGatewayModel.preparePreAuthQuote()
        };
    },

    /**
     * Method to redirect by requested URL.
     */
    redirectTo: function (redirectUrl) {
        window.location.replace(redirectUrl);
    }
}
