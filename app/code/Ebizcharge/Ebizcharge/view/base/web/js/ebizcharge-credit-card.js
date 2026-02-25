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
define(
    [
        "jquery"
    ], function ($) {
        "use strict";

        return function (config, element) {
            let minLength = 15, maxLength = 19;

            if (config.input === 'card-pin') {
                minLength = 3;
                maxLength = 4;
            }

            $(document.getElementById(element.id)).attr('minlength', minLength)
                .attr('maxlength', maxLength);

            /**
             * Allow only digits on the input
             */
            $(document.getElementById(element.id)).bind("keypress", function (e) {
                let keyCode = e.which ? e.which : e.keyCode;

                if (!(keyCode >= 48 && keyCode <= 57)) {
                    return false;
                }

            });

            if (config.input === 'card-number') {

                $(document.getElementById(element.id)).change(function () {

                    $(document.getElementById(config['card-select-id'] + ' option:not(:selected)'))
                        .prop('disabled', false);

                    let cc = $(document.getElementById(element.id)).val();
                    cc = cc.replaceAll("\\D", "");
                    let pattern = new RegExp('[ ]+', 'g');
                    cc = cc.replace(pattern, '');

                    $(document.getElementById(element.id)).val(cc);

                    if (!cc || !cc.length) {
                        return undefined;
                    }

                    let ccType = creditCardType(cc);

                    $(document.getElementById(config['card-select-id'])).val(ccType);

                    if (ccType !== undefined) {
                        $(document.getElementById(config['card-select-id'] + ' option:not(:selected)'))
                            .prop('disabled', true);
                    }
                });
            }

            /**
             * Get credit card type by card number
             * @param cardNumber
             * @returns {string|undefined}
             */
            function creditCardType(cardNumber)
            {

                let amex = new RegExp('^3[47][0-9]{13}$');
                let visa = new RegExp('^4[0-9]{12}(?:[0-9]{3})?$');

                let mastercard = new RegExp('^5[1-5][0-9]{14}$');
                let mastercard2 = new RegExp('^2[2-7][0-9]{14}$');

                let disco1 = new RegExp('^6011[0-9]{12}[0-9]*$');
                let disco2 = new RegExp('^62[24568][0-9]{13}[0-9]*$');
                let disco3 = new RegExp('^6[45][0-9]{14}[0-9]*$');

                let jcb = new RegExp('^35[0-9]{14}[0-9]*$');


                if (visa.test(cardNumber)) {
                    return 'V';
                }
                if (amex.test(cardNumber)) {
                    return 'A';
                }
                if (mastercard.test(cardNumber) || mastercard2.test(cardNumber)) {
                    return 'M';
                }
                if (disco1.test(cardNumber) || disco2.test(cardNumber) || disco3.test(cardNumber)) {
                    return 'DS';
                }

                if (jcb.test(cardNumber)) {
                    return 'JCB';
                }
                return undefined;
            }
        }
    }
)
