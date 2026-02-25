/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation',
], function ($, alert) {
    'use strict';

    var machGroundShippingMethod = ["mach_501", "mach_207", "mach_208"];

    return function (target) {

        $.widget('mage.orderReview', target, {
            options: {},

            /**
             * Dispatch an ajax request of Update Order submission
             * @param {*} url - url where to submit shipping method
             * @param {*} resultId - id of element to be updated
             */
            _submitUpdateOrder: function (url, resultId) {

                var isChecked, formData, callBackResponseHandler, shippingMethod;

                if (this.element.find(this.options.waitLoadingContainer).is(':visible')) {
                    return false;
                }
                isChecked = $(this.options.billingAsShippingSelector).is(':checked');
                formData = null;
                callBackResponseHandler = null;
                shippingMethod = $.trim($(this.options.shippingSelector).val());
                this._shippingTobilling();

                if (url && resultId && shippingMethod) {
                    this._updateOrderSubmit(true);
                    this._toggleButton(this.options.updateOrderSelector, true);

                    // form data and callBack updated based on the shipping Form element
                    if (this.isShippingSubmitForm) {
                        formData = $(this.options.shippingSubmitFormSelector).serialize() + '&isAjax=true';

                        /**
                         * @param {Object} response
                         */
                        callBackResponseHandler = function (response) {
                            $(resultId).html(response);
                            this._updateOrderSubmit(false);
                            this._checkShippingMethod(shippingMethod);
                            this._ajaxComplete();
                        };
                    } else {
                        formData = this.element.serialize() + '&isAjax=true';

                        /**
                         * @param {Object} response
                         */
                        callBackResponseHandler = function (response) {
                            $(resultId).html(response);
                            this._checkShippingMethod(shippingMethod);
                            this._ajaxShippingUpdate(shippingMethod);
                        };
                    }

                    if (isChecked) {
                        $(this.options.shippingSelect).prop('disabled', true);
                    }

                    $.ajax({
                        url: url,
                        type: 'post',
                        context: this,
                        beforeSend: this._ajaxBeforeSend,
                        data: formData,
                        success: callBackResponseHandler
                    });
                }
            },

            _checkShippingMethod: function (shippingMethod) {
                if ($("#order-review-form #review-hsc").length && $("#order-review-form #review-hsc #mach_hsc").length) {
                    if ($.inArray(shippingMethod, machGroundShippingMethod)) {
                        $("#review-hsc").show();
                    } else {
                        $("#review-hsc").hide();
                        $("#mach_hsc").prop("checked", false);
                    }
                }
            },

            /**
             * Check/Set whether order can be submitted
             * Also disables form submission element, if any
             * @param {*} shouldDisable - whether should prevent order submission explicitly
             * @param {Function} [fn] - function for shipping change handler
             * @param {*} [*] - if true the property change will be set to true
             */
            _updateOrderSubmit: function (shouldDisable, fn) {
               if (this.options.isInvalidShippingAddress == true) {
                   shouldDisable = true;
               }

               this._toggleButton(this.options.orderReviewSubmitSelector, shouldDisable);
               if ($.type(fn) === 'function') {
                   fn.call(this);
               }
            },

            /**
             * Validate Order form
             */
            _validateForm: function () {
                if (this.options.isInvalidShippingAddress == true) {
                    return false;
                }

                this.element.find(this.options.agreementSelector).off('change').on('change', $.proxy(function () {
                    var isValid = this._validateForm();

                    this._updateOrderSubmit(!isValid);
                }, this));

                if (this.element.data('mageValidation')) {
                    return this.element.validation().valid();
                }

                return true;
            },

        });

    return $.mage.orderReview;

    };
});
