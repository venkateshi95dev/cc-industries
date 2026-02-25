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
    ], function ($) {
        "use strict";

        $.widget('mage.ebizcharge', {
            options: {
                clientKey: false,
                code: "ebizcharge_ebizcharge",
            },
            curentSelectedTab: 'current_selected_tab',
            /**
             * enable Disable Saved Fields
             *
             * @param disabled
             */
            enableDisableSavedFields: function (disabled) {
                var fields = [
                    "_cc_token",
                    "_cc_cid",
                    "_ebzc_method_ach"
                ];
                var id;
                console.log("saved"+disabled);

                for (id = 0; id < fields.length; id++) {
                    $('#' + this.options.code + fields[id]).prop('disabled', disabled);
                    console.log(this.options.code + fields[id]);
                }
            },
            /**
             * enable Disable Update Fields
             *
             * @param disabled
             */
            enableDisableUpdateFields: function (disabled) {
                var fields = [
                    "_cc_token_update",
                    "_cc_cid_update",
                    "_expiration_update",
                    "_expiration_yr_update",
                    "_avs_street",
                    "_avs_zip"
                ];
                var id;
                console.log("updated"+disabled);

                for (id = 0; id < fields.length; id++) {
                    $('#' + this.options.code + fields[id]).prop('disabled', disabled);
                    console.log(this.options.code + fields[id]);
                }
            },
            /**
             * enable Disable Fields
             *
             * @param disabled
             */
            enableDisableFields: function (disabled) {
                var fields = [
                    "_cc_owner_new",
                    "_cc_type_new",
                    "_cc_number_new",
                    "_expiration_new",
                    "_expiration_yr_new",
                    "_cc_cid_new",
                    "_save_payment",
                    "_cc_owner_new_ach",
                    "_cc_number_new_ach",
                    "_cc_routing_new_ach",
                    "_cc_type_ach"

                ];
                var id;
                console.log("new"+disabled);
                for (id = 0; id < fields.length; id++) {
                    let fieldsIds = this.options.code + fields[id];
                    $('#' + this.options.code + fields[id]).prop('disabled', disabled);
                    console.log(this.options.code + fields[id]);

                }
            },
            /**
             * enable Disable Pay Later Fields
             *
             * @param disabled
             */
            enableDisablePaylaterFields: function (disabled) {
                var fields = ["_paylater"];
                var id;
                for (id = 0; id < fields.length; id++) {
                    $('#' + this.options.code + fields[id]).prop('disabled', disabled);
                }
            },
            /**
             * prepare
             *
             * @param event
             * @param method
             */
            prepare: function (event, method) {
                if (method === 'ebizcharge_ebizcharge') {
                    this.preparePayment();
                }
            },
            /**
             * Prepare CVC
             *
             * @param token
             */
            prepareCVC: function (token) {
                var self = this;
            },
            preparePayment: function () {
                var self = this;
                $('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
            },
            /**
             * Submit Admin Order
             *
             * @param event
             */
            submitAdminOrder: function (event) {

                if ($("input[name=ebiz_disabled]").val()) {
                    alert('Please enable payment options to use this payment method!');
                    return;
                }

                var token = $('.ebzc_option').val();


                if (token == 'saved') {
                    var ccNumber = $("#ebizcharge_ebizcharge_cc_number").val();
                    if (ccNumber) {
                        //this.enableDisableFields(true);
                        $('#ebzc_method_id').val('');
                    } else {

                    }
                } else if (token == 'update') {
                    var ccNumber = $("#ebizcharge_ebizcharge_cc_number").val();
                    if (ccNumber) {
                        //this.enableDisableFields(true);
                        $('#ebzc_method_id').val('');
                    } else {

                    }
                } else if (token == 'paylater') {
                    var paylater = $("#ebizcharge_ebizcharge_paylater").val();
                    if (paylater) {
                        //this.enableDisableFields(true);
                        $('#ebzc_method_id').val('');
                    } else {

                    }
                } else {
                    var ccNumber = $("#ebizcharge_ebizcharge_cc_number_new").val();
                    if (ccNumber) {
                        //this.enableDisableFields(true);
                        $('#ebzc_method_id').val('');
                    } else {

                    }
                }

                order._realSubmit();
            },

            /**
             * delete Payment Method
             */
            deletePaymentMethod: function () {
                var cid = $("#ebizcharge_ebizcharge_cust_id").val();
                var mid = $("#ebizcharge_ebizcharge_cc_token_update").val();
                var deleteURL = $("#ebizcharge_ebizcharge_delete_url").val();

                if (mid && deleteURL) {
                    $.post(deleteURL,
                        {cid: cid, mid: mid},
                        function (data, textStatus, jqXHR) {
                            // console.log("Success");
                        }).fail(function (jqXHR, textStatus, errorThrown) {
                        // console.log("Failed");
                    });
                    location.reload();
                } else {
                    // console.log("Missing info");
                }
            },
            /**
             * create function
             *
             * @private
             */
            _create: function () {
                var self = this;

                $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
                $('#edit_form').trigger(
                    'changePaymentMethod',
                    [
                        $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                    ]
                );

                $("#delete-payment").click(function () {
                    self.deletePaymentMethod();
                });


            }
        });

        return $.mage.ebizcharge;
    });
