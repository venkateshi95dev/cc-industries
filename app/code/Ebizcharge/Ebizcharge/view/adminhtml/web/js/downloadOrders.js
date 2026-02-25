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
        "jquery",
        "mage/url",
        'mage/calendar',
        'domReady!'
    ], function ($, url) {

        return function main(config)
        {


            $(document).ready(function () {
                let ebizStartDatePickerButton = $('#ebiz-start-date-picker');
                let ebizEndDatePickerButton = $('#ebiz-end-date-picker');

                ebizEndDatePickerButton.calendar({
                    minDate: new Date(),
                    beforeShowDay: $.datepicker.noWeekends,
                    showsTime: true,
                    showHour: true,
                    showMinute: true,
                    timeFormat: 'hh:mm tt',
                    dateFormat: 'dd-mm-yy,',
                    hourMin: 9,
                    hourMax: 17,
                    stepMinute : 5,
                });

                /** Ebiz Start Date Picker button **/
                ebizStartDatePickerButton.hover(function () {
                    $(this).attr("autocomplete", "off");
                });

                ebizEndDatePickerButton.hover(function () {
                    $(this).attr("autocomplete", "off");
                });

                let startDate = false;
                let endDate = false;

                ebizStartDatePickerButton.on("change", function () {
                    startDate = $(this).val();
                });
                ebizEndDatePickerButton.on("change", function () {
                    endDate = $(this).val();
                });


                /**
                 * at click on Download Orders
                 * Run the Download Orders
                 */
                $(document).on('click', '#download-orders', function (e) {

                    if (!startDate) {
                        alert($.mage.__("Error! Missing input: please select \"Start Date\" and try again. "));
                        return false;
                    }
                    if (!endDate) {
                        alert($.mage.__("Error! Missing input: please select \"End Date\" and try again."));
                        return false;
                    }

                    let startDateFormated = new Date(startDate);
                    let endDateFormated = new Date(endDate);

                    console.log([startDateFormated,  startDateFormated]);

                    if (startDate > endDate ) {
                        alert($.mage.__("Error! the \"End Date\" is greater than the \"Start Date\", please reselect and try again."));
                        return false;
                    }

                    let actionDownloadOrdersUrl = config.urlDownloadOrder;

                    /** check if that is confirmed that the user selects the Recurring Orders Placed **/

                    if (confirm($.mage.__("Are you sure you want to generate open orders from failed recurring payments within the selected date range?"))) {

                        /** putting Ajax and run the download orders */
                        $.ajax(
                            {
                                showLoader: true,
                                url: actionDownloadOrdersUrl, /** Action Download Orders URL **/
                                data: {
                                    form_key: window.FORM_KEY,
                                    start_date: startDate,
                                    end_date: endDate,
                                },
                                type: "POST",
                                dataType: 'json',
                                success: function (data) {
                                    location.reload();
                                },
                                error: function (request, status, error) {
                                    location.reload();
                                }
                            });
                    }
                });
            });
        }
    });
