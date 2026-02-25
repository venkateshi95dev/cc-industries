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
        'jquery'
    ],
    function ($) {

        return function (customData) {
            $(document).ready(function () {

                $(".print-actions").on('change', function (event) {

                    let action = $(this).find(':selected').data('action');

                    if (action === 'print_receipt') {
                        let tid = $(this).val();
                        let rid = $(this).val();

                        let url = customData.printActionUrl;

                        $.ajax({
                            method: "GET",
                            url: url + '?tid=' + tid+'&rid='+rid,
                            data: {
                                form_key: window.FORM_KEY
                            },
                            dataType: "json",
                            showLoader: true,
                            success: function (data) {
                                if (data.html_data !== undefined) {

                                    var newWin = window.open('', '_blank', 'width=600,height=400');
                                    newWin.document.open();
                                    newWin.document.write(data.html_data);
                                   // newWin.document.write('<html lang="en"><body onload="window.print()">' + data.html_data + '</html>');
                                    newWin.print();
                                    newWin.close();
                                }
                            },
                            error: function (result) {
                                alert("No response found from Ebizcharge WSDL URL");
                            }
                        });

                    } else if (action === 'print_email') {
                        let printEmailVal = $(this).val();
                        let printEmail = printEmailVal.split('||');

                        let tid = printEmail[0];
                        let email = printEmail[1];

                        let url = customData.emailActionUrl;

                        $.ajax({
                            method: "GET",
                            url: url + '?tid=' + tid + '&email=' + email,
                            data: {
                                form_key: window.FORM_KEY
                            },
                            dataType: "json",
                            showLoader: true,
                            success: function (data) {
                                if (data.html_data !== undefined) {
                                    alert("Success, Email has been sent to "+email+" successfully");
                                   // location.reload();
                                }
                            },
                            error: function (result) {
                                alert("No response found from Ebizcharge WSDL URL");
                            }
                        });
                    }
                    return false;
                });

                // $('.data-grid-filters').remove();

                $('input[name="paymentDate[to]"]').closest('div').remove();

                // $('.no-changes.admin__control-select').remove();

                $('select[name="limit"]').on('change', function () {
                    $('body').trigger('processStart');
                });

                $('.action-next').not(".disabled").on('click', function () {
                    $('body').trigger('processStart');
                });

                $('.action-previous').not(".disabled").on('click', function () {
                    $('body').trigger('processStart');
                });

            });
        }
    }
);
