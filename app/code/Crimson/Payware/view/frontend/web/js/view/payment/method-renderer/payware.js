define([
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Crimson_Payware/payment/payware',
            },

            context: function() {
                return this;
            },

            getCode: function() {
                return 'payware';
            },

            getPaywareCustomerMessage: function() {
                return window.checkoutConfig.payware_transaction_customer_message;
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                //return true;
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }

        });
    }
);
