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

var config = {
    paths: {
        'chosen': 'Ebizcharge_Ebizcharge/js/choosen'
    },
    shim: {
        'chosen': {
            deps: ['jquery']
        }
    },
    map: {
        '*': {
            ebizcharge: 'Ebizcharge_Ebizcharge/js/form',
            ebizchargeHowItWorks: 'Ebizcharge_Ebizcharge/js/howItWorks/how-it-works'
        }
    }
};
