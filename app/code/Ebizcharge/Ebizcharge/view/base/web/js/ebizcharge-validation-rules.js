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
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    return function () {
        /**
         * Validating of AVS CVV Code
         */
        $.validator.addMethod(
            'ebizChargeFrequencyDateRange', function (value, element) {
                let days = 0;
                let frequencyDays = {
                    'daily': 1,
                    'weekly': 7,
                    'bi-weekly': 14,
                    'bi-monthly': 14,
                    'four-week': 28,
                    'monthly': 30,
                    'two-month': 60,
                    'quarterly': 90,
                    'three-month': 90,
                    '90-days': 90,
                    'four-month': 120,
                    'five-month': 150,
                    'bi-annually': 180,
                    'six-month': 180,
                    '180-days': 180,
                    'annually': 365,
                    'default': 30
                };
                let itemId = typeof element.dataset.itemId !== undefined ? element.dataset.itemId : '';

                if ($('input[data-id="rec_indefinite_' + itemId + '"]').prop('checked') === false) {

                    let frequency = $('select[data-id="rec_frequency_' + itemId + '"]').val();
                    let startDate = $('input[data-id="start_date_' + itemId + '"]').val();
                    let endDate = $('input[data-id="end_date_' + itemId + '"]').val();

                    days = frequencyDays[frequency];

                  //  console.log([frequency, startDate, endDate]);

                    const diffInMs = new Date(endDate) - new Date(startDate)
                    const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

                    if (Math.round(diffInDays) < Math.round(days)) {
                        return false;
                    }
                }
                return true;
            },

            $.mage.__('Please select valid dates for selected frequency.')
        );
    }
});
