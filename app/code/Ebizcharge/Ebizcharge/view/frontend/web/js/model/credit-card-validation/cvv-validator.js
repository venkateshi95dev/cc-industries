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

/* @api */
define(
    [],
    function () {
        'use strict';

        /**
         * @param {*} isValid
         * @param {*} isPotentiallyValid
         * @return {Object}
         */
        function resultWrapper(isValid, isPotentiallyValid)
        {
            return {
                isValid: isValid,
                isPotentiallyValid: isPotentiallyValid
            };
        }

        /**
         * CVV number validation.
         * Validate digit count fot CVV code.
         *
         * @param {*} value
         * @param {Number} maxLength
         * @return {Object}
         */
        return function (value, maxLength) {
            var DEFAULT_LENGTH = 3;

            maxLength = maxLength || DEFAULT_LENGTH;

            if (!/^\d*$/.test(value)) {
                return resultWrapper(false, false);
            }

            if (value.length === maxLength) {
                return resultWrapper(true, true);
            }

            if (value.length < maxLength) {
                return resultWrapper(false, true);
            }

            if (value.length > maxLength) {
                return resultWrapper(false, false);
            }
        };
    });
