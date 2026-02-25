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
        'Magento_Ui/js/grid/columns/column',
        'jquery',
        'mage/template',
        'Magento_Ui/js/modal/modal'
    ], function (Column, $, mageTemplate) {
        'use strict';
        return Column.extend({
            defaults: {
                bodyTmpl: 'ui/grid/cells/html',
                fieldClass: {
                    'data-grid-html-cell': true
                }
            },
            gethtml: function (row) {
                return row[this.index + '_html'];
            },
            getRowIndex: function (row) {
                return row[this.index + '_rowindex'];
            },
            getFormaction: function (row) {
                return row[this.index + '_formaction'];
            },
            getCustomerid: function (row) {
                return row[this.index + '_customerid'];
            },
            getLabel: function (row) {
                return row[this.index + '_html']
            },
            getTitle: function (row) {
                return row[this.index + '_title']
            },
            getSubmitlabel: function (row) {
                return row[this.index + '_submitlabel']
            },
            getCancellabel: function (row) {
                return row[this.index + '_cancellabel']
            },
            preview: function (row) {

                let rowIndex = this.getRowIndex(row);
                let selectBox = $("#actions-" + rowIndex + " option:selected");
                let optionType = selectBox.attr('data-type');
                let optionUrl = selectBox.attr('value');


                if (optionUrl !== '') {
                    $("#actions-" + rowIndex).val('').change();

                    if (optionType == 'send-email') {
                        if (confirm($.mage.__("Are you sure you want to send receipt via email to customer ?"))) {
                            window.location.href = optionUrl;
                        }
                    }

                    if (optionType == 'print-email') {
                        var screenWidth = window.screen.width;
                        var screenHeight = window.screen.height;
                        var windowHWidth = 1024;
                        var windowVHeight = 576;
                        var left = (screenWidth - windowHWidth) / 2;
                        var top = (screenHeight - windowVHeight) / 2;
                        let printEmailContents = "<div id='print-email-contents'><h2 style='text-align: center; color:darkgreen'>Printing receipt from EBizChrge Hub.</h2></div>";
                        var printWindow = window.open('', '_blank', 'width=' + windowHWidth + ',height=' + windowVHeight + ', left=' + left + ', top=' + top);
                        printWindow.document.title = "Printing receipt from EBizCharge Hub.";
                        printWindow.document.write(printEmailContents);
                        let printEmailText = printWindow.document.getElementById("print-email-contents");

                        $.ajax({
                            method: "GET",
                            url: optionUrl,
                            data: {
                                form_key: window.FORM_KEY
                            },
                            dataType: "json",
                            beforeSend: function (xhr) {
                                printEmailContents += "<hr/><div style='text-align: center'>Please wait...</div>";
                                printWindow.document.title = "Printing receipt from EBizCharge Hub.";
                                if(typeof(printEmailText) !== "undefined") {
                                    printEmailText.innerHTML = printEmailContents;
                                }

                            },
                            showLoader: true,
                            success: function (data) {

                                if (data.html_data !== undefined) {
                                    printEmailContents = data.html_data;
                                    if(typeof(printEmailText) !== "undefined") {
                                        printEmailText.innerHTML = printEmailContents;
                                    }
                                    printWindow.print();
                                    //  printWindow.close();
                                } else {
                                    printEmailContents += "<hr/><div style='text-align: center; color:darkred'>Error: Receipt template not found. Please create a receipt template in the EBizCharge Admin Portal to proceed.</div>";
                                    if(typeof(printEmailText) !== "undefined") {
                                        printEmailText.innerHTML = printEmailContents;
                                    }
                                }
                            },
                            error: function (result) {
                                printWindow.document.write("<hr/><h2 style='text-align: center;color:darkred'>Error: Receipt template not found. Please create a receipt template in the EBizCharge Admin Portal to proceed.</h2>");
                                alert("Error: Receipt template not found. Please create a receipt template in the EBizCharge Admin Portal to proceed.");
                            }
                        });
                    }
                }
            },
            getFieldHandler: function (row) {
                return this.preview.bind(this, row);
            }
        });
    });
