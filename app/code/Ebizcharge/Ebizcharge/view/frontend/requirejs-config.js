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

/**
 *
 * @type {{map: {"*": {"Magento_Payment/js/model/credit-card-validation/cvv-validator": string, ebizcharge: string, "Magento_Payment/js/model/credit-card-validation/validator": string}}}}
 */
var config = {
    map: {
        '*': {
            ebizcharge: 'Ebizcharge_Ebizcharge/ebizcharge',
            'Magento_Payment/js/model/credit-card-validation/validator': 'Ebizcharge_Ebizcharge/js/model/credit-card-validation/validator',
            'Magento_Payment/js/model/credit-card-validation/cvv-validator': 'Ebizcharge_Ebizcharge/js/model/credit-card-validation/cvv-validator',
             ebizchargeCardValidationResponse: 'Ebizcharge_Ebizcharge/js/popup/validation-response',
            ebizchargeMultiShippingPlaceOrder: 'Ebizcharge_Ebizcharge/js/checkout/component/multi-shipping-place-order'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/place-order': {
                'Ebizcharge_Ebizcharge/js/model/place-order-model-mixin': true
            },
            'Magento_Tax/js/view/checkout/summary/grand-total': {
                'Ebizcharge_Ebizcharge/js/view/checkout/summary/grand-total-mixin': true
            },
            'Magento_Checkout/js/model/payment-service': {
                'Ebizcharge_Ebizcharge/js/view/checkout/summary/payment-service-listener-mixin': true
            }
        }
    }
};
