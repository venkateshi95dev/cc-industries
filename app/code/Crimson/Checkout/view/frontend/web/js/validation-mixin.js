define([
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'underscore',
        'Magento_Checkout/js/model/cart/cache',
        'jquery/ui'
    ],

    function ($, modal, $t, _, cartCache){
        'use strict';

        return function (target) {
            $.widget('mage.iwdAddressValidation', target, {

                checkIsAddressValid:function(){
                    var self = this;

                    if(this.request && this.request.readystate != 4){
                        this.request.abort();
                    }

                    this.request = $.ajax({
                        url: this.options.urlValidation,
                        data: this.options.address,
                        type: 'post',
                        dataType: 'json',
                        context: this,
                        beforeSend: function() {
                            self.beforeValidAddress();
                        },
                        complete: function() {
                            self.afterValidAddress();
                        }
                    })
                        .done(function(response) {
                            if (response.error){
                                self._showError(response);
                            }
                            if(response.is_valid){
                                this._setAddressValidationFields(response.original_address);
                                this.whenAddressValid();
                            } else {
                                this.whenAddressInvalid(response);
                            }
                        })
                        .fail(function(error) {
                            self._showError(error);
                        });
                },

                _setAddressValidationFields: function(address){

                    var validationFields = cartCache.get('validationFields');

                    if (!validationFields) {
                        validationFields = {};
                    }

                    validationFields.ship_adv         = _.has(address, "ship_adv") && address["ship_adv"] === true ? 1 : 0;
                    validationFields.ship_adv_date    = _.has(address, "ship_adv_date") ? address["ship_adv_date"] : "";
                    validationFields.ship_adv_di      = _.has(address, "ship_adv_di") ? address["ship_adv_di"] : "";
                    validationFields.ship_adv_dpi     = _.has(address, "ship_adv_dpi") ? address["ship_adv_dpi"] : "";

                    cartCache.set('validationFields', validationFields);
                }

            });

            return $.mage.iwdAddressValidation;
        };
    });
