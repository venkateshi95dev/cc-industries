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

require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url'
    ],
    function ($, modal, url) {


        /**
         * base URL
         */
        url.setBaseUrl(BASE_URL);

        $(".print_btn").click(function () {

            /**
             * Print Button
             * @type {jQuery}
             */
            var tid = $(this).attr('id');
            var receiptRefNum = $('#receiptRefNum').val();

            $.ajax({
                method: "POST",
                url: url.build('ebizcharge/recurrings/printaction'),
                data: {
                    tid: tid,
                    rid: receiptRefNum
                },
                dataType: "json",
                showLoader: true,
                success: function (data) {

                    if (data.html_data !== undefined) {
                        var newWin = window.open('', '_blank', 'width=600,height=400');
                        newWin.document.open();
                        newWin.document.write(data.html_data);
                    //    newWin.document.write('<html lang="en"><body onload="window.print()">' + (data.html_data) + '</html>');

                          newWin.print();
                         //newWin.close();
                        //$.ajaxQ.abortAll();
                    }
                },
                error: function (result) {
                    alert("No response from WSDL");
                }
            });
        });

        /**
         * Email Button
         */
        $(".email_btn").click(function () {


            var tid = $(this).attr('id');
            var email = $(this).attr('data-id');
            var receiptRefNum = $('#receiptRefNum').val();

            $.ajax({
                method: "POST",
                url: url.build('ebizcharge/recurrings/emailaction'),
                data: {
                    tid: tid,
                    rid: receiptRefNum,
                    email: email
                },
                dataType: "json",
                showLoader: true,
                success: function (data) {
                    if (data.html_data !== undefined) {
                        //alert(data.html_data);
                        if (data.html_data == 1) {
                            $('#msg').html("<div>Email sent!</div>");
                            $('#msg').addClass("message-success success message");
                        } else {
                            $('#msg').html("<div>Email not sent!</div>");
                            $('#msg').addClass("message-error error message");
                        }
                    }
                },
                error: function (result) {
                    alert("No response from WSDL");
                }
            })
        });

        /**
         *
         * @type {{buttons: [{text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
         */
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Bulk Action',
            buttons: [{
                text: $.mage.__('OK'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]


        };

        /**
         * Options Confirm Variable Class
         *
         * @type {{buttons: [{text: *, class: string, click: function(): void}, {text: *, class: string, click: function(): void}], responsive: boolean, innerScroll: boolean, type: string, title: string}}
         */
        var optionsConfirm = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Delete Selected Subscriptions',
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'btn-background-cancel',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Yes, Delete'),
                class: 'btn-background-del',
                click: function () {

                    $('#reclist').submit();


                }
            }]
        };

        var popup = modal(options, $('#myModel'));
        var popupConfirm = modal(optionsConfirm, $('#myModelConfirm'));


        /** delete icon
         * cross buttons
         * **/
        $(".delete, .icon-cross, .del_btn").click(function (evnt) {

            var currentTarget = $(evnt.target).parent();
            var singleDelete = currentTarget.hasClass('icon-cross');
            var currentTargetValue = '';

            if (singleDelete) {
                var currentRow = currentTarget.attr('id').split('-');
                var currentCheckboxIndx = currentRow[1];
                $('#subscription-checkbox-' + currentCheckboxIndx).prop('checked', true);
                currentTargetValue = currentTarget.attr('value');

            } else {
                var currentTarget = $(evnt.target);
                var singleDelete = currentTarget.hasClass('icon-cross');
                currentTargetValue = currentTarget.val();
            }
            if (currentTargetValue !== 'delete') {
                return false;
            }
            var ids = [];

            if (($('[name="del_id"]:checked').length > 0)) {
                $.each($("input[name='del_id']:checked"), function () {
                    ids.push($(this).val());

                });

                $('#internal_id').val(ids.join(", "));
                $('#myModelConfirm').html('<div>Are you sure you want to delete subscription(s)?</div>');
                $('#myModelConfirm').modal('openModal');

              //  $('#reclist').submit();

            } else {
                $('#myModel').html('<div>An item needs to be selected. Plesae select and try again.</div>');
                $('#myModel').modal('openModal');
                $('#subscription_delete_btn').val('').change();

            }
        });


        /***
         *
         * Creating An Table
         * Listing Object
         *
         **/
        var ListingTable = {

            listTable: null,
            totalListings: null,
            keywordSearchBox: null,
            paymentKeywordsBox: null,
            maxRows: null,
            statusSelect: null,
            listTableBody: null,
            searchTimeout: null,
            listRows: null,
            activeListingClass: '',

            /**
             * Init function
             * @param $
             * @private
             */
            _init: function ($) {
                ListingTable.listTable = $('#myTable');
                ListingTable.keywordSearchBox = $('#searchIn');
                ListingTable.paymentKeywordsBox = $('#payment_search_keywords');
                ListingTable.maxRows = $('#maxRows');
                ListingTable.statusSelect = $('#status-select');
                ListingTable.listTableBody = $('#myBody');
                ListingTable.totalListings = 0;
                ListingTable.searchTimeout = 1500;
                ListingTable.activeListingClass = 'active-listings';

                if (typeof (this.keywordSearchBox.val()) == 'undefined') {
                    /** search box keywords **/
                    ListingTable.keywordSearchBox = ListingTable.paymentKeywordsBox;
                }

                var keywords = ListingTable.keywordSearchBox.val();

                /** searching keywords **/
                ListingTable.searchKeywords($, keywords);

                /** search listing at run time **/
                ListingTable.searchTableListings($, keywords);

                /** selecting the status **/
                ListingTable.statusSelect.on("change", function () {
                    ListingTable.updateStatus($, keywords);
                });
                /** selecting the status **/
                ListingTable.maxRows.on("change", function () {
                    let keywords = ListingTable.keywordSearchBox.val();
                    ListingTable.updateStatus($, keywords);
                });
            },
            /**
             * Search Keywords
             * @param $
             * @param keywords
             */
            searchKeywords: function ($, keywords) {
                ListingTable.keywordSearchBox.keypress(function (event) {
                    let keyCode = event.which;
                    if (keyCode === 13 || keyCode === 40) {
                        event.preventDefault();
                        return false;
                    }

                });
                ListingTable.keywordSearchBox.on("keyup", function (event) {
                    let searchKeywords = $(this).val().toLowerCase() ? $(this).val().toLowerCase() : keywords;

                    /** listing table search table listing **/
                    ListingTable.searchTableListings($, searchKeywords);
                });
            },
            /**
             * Update Status
             * function
             * **/
            updateStatus: function ($, keywords) {
                ListingTable.searchTableListings($, keywords);
            },

            /**
             *  Search Table
             * @param $
             * @param serachKeywords
             */
            searchTableListings: function ($, serachKeywords) {

                var selectedStatus = typeof (this.statusSelect.val()) !== 'undefined' ? this.statusSelect.val().toLowerCase() : '';

                /** filtering TR **/
                $("#myBody tr").filter(function (key, trObj) {
                    let serachWords = serachKeywords;
                    let searchKeywordsIndex = $(this).text().toLowerCase().indexOf(serachWords);
                    /** toggleing the value **/
                    $(this).toggle(searchKeywordsIndex > -1);
                    /** remove class **/
                    $(this).removeClass(ListingTable.activeListingClass);
                });

                /** loooping through all visible items **/
                $("#myBody tr:visible").each(function (key, row) {
                    let onTr = $(this).hasClass('green');
                    let offTr = $(this).hasClass('red');

                    if (selectedStatus !== '') {
                        if (selectedStatus == 'on') {
                            if (onTr) {
                                $(this).show();
                                $(this).addClass(ListingTable.activeListingClass);
                            }
                            if (offTr) {
                                $(this).hide();
                                $(this).removeClass(ListingTable.activeListingClass);

                            }
                        }
                        if (selectedStatus == 'off') {
                            if (onTr) {
                                $(this).hide();
                                $(this).removeClass(ListingTable.activeListingClass);
                            }
                            if (offTr) {
                                $(this).show();
                                $(this).addClass(ListingTable.activeListingClass);
                            }
                        }
                    }
                });
                let totalRows = $("#myBody tr:visible");
                this.listRows = totalRows;

                /** render pagination at change **/
                ListingTable.renderPagination($, totalRows);

            },
            /**
             * Render Pagination
             * **/
            renderPagination: function ($, totalRows) {
                /** reseting the html
                 * of pagination */
                $('.pagination').html('');

                var table = '#myTable';
                var trnum = 0;
                var maxRows = parseInt(this.maxRows.val());
                var totalRows = totalRows.length;
                var totalVisibleRows = totalRows;

                $("#myBody tr." + ListingTable.activeListingClass).each(function () {
                    trnum++;
                    if (trnum > maxRows) {
                        $(this).hide();
                    }
                    if (trnum <= maxRows) {
                        $(this).show();
                    }

                });

                if (totalRows > maxRows) {
                    var pagenum = Math.ceil(totalRows / maxRows);
                    for (var i = 1; i <= pagenum;) {
                        $('.pagination').append('<li data-page="' + i + '">\<span>' + i++ + ' <span class="sr-only">(current)</span></span>\</li>').show();
                    }
                }
                $('.pagination > li:first-child').addClass('active');

                /** running pagination **/
                $('.pagination li').on('click', function (event) {

                    var pageNum = parseInt($(this).attr('data-page'));
                    var trIndex = 0;

                    $('.pagination li').removeClass('active');
                    $(this).addClass('active');

                    $("#myBody tr." + ListingTable.activeListingClass).each(function (key, row) {

                        let fromRows = parseInt(((pageNum * maxRows) - maxRows));
                        let toRows = parseInt((pageNum * maxRows));

                        if (trIndex >= fromRows && trIndex < toRows) {
                            $(row).show();
                        } else {
                            $(row).hide();
                        }
                        trIndex++;

                    });
                });
            }

        };

        /** initializing the listing table object **/
        ListingTable._init($);


    }
);

/**
 * Open Page
 * @param url
 */
function openPage(url)
{
    if (url != "") {
        window.location = url;
    }
}
