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
 * Add the EBizCharge payment method to the checkout page.
 *
 */

var config = {
    map: {
        '*': {
            'ebizcharge-credit-card': 'Ebizcharge_Ebizcharge/js/ebizcharge-credit-card',
            'ebizchargeValidationRules': 'Ebizcharge_Ebizcharge/js/ebizcharge-validation-rules',
            'Magento_Payment/js/model/credit-card-validation/validator': 'Ebizcharge_Ebizcharge/js/model/credit-card-validation/validator'
        }
    }
};
