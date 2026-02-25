
define([
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'underscore',
        'jquery/ui'
    ],
    function ($, modal, $t, _){
        'use strict';

        $.widget('mage.crimsonIwdAddressValidation', {
            options: {
                urlValidation: "",
                urlSetQuoteAddress: "",
                allowInvalidAddress: true,
                formId: '#shipping-method-form',
                nextStepButtonId: "#order-review-form #review-button",
                validateAddressButtonId: "#checkout-paypal-address-validation-action",
                validateAddressTimeout:0,
                shippingAddress: {},
                addressMap: ['street[]', 'city', 'country_id', 'postcode', 'region_id', 'region'],
                address: {},
                firstFormLoad: false
            },
            request: null,
            currentModal: null,

            init: function(options){

                $(document).ready(function() {
                    $(window).keydown(function(event){
                        if(event.keyCode == 13) {
                            event.preventDefault();
                            return false;
                        }
                    });
                });

                this._initOptions(options);

                this.readAddressForm();
                this.validateAddressButton();
            },

            _initOptions:function(options){
                var self = this;

                options = options || {};
                $.each(options, function(i, e){self.options[i] = e;});
            },

            readAddressForm: function(){
                var shippingAddress = this.options.shippingAddress;
                var address = {};

                address['street'] = shippingAddress.street_line1 + " " + shippingAddress.street_line2;
                address['city'] = shippingAddress.city;
                address['country_id'] = shippingAddress.country_id;
                address['postcode'] = shippingAddress.postcode;
                address['region'] = shippingAddress.region;
                address['region_id'] = shippingAddress.region_id;

                return this.options.address = address;
            },

            validateAddressButton: function(){
                var self = this;
                if ($(self.options.validateAddressButtonId).length) {
                    $(document).on('click touchstart', self.options.validateAddressButtonId, (function() {
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function(){
                            self._validateAddress();
                        }, 250);
                    }));
                }
            },

            _validateAddress: function(){
                if (this._checkIsAddressFilled()){
                    this._checkIsAddressValid();
                }
            },

            _checkIsAddressFilled: function(){
                var empty = [];
                $.each(this.options.address, function(i, e) {
                    if (!e || e.length == 0 || e == 0){
                        empty.push(i);
                    }
                });

                if (empty.length == 0){
                    return true;
                }

                if (empty.length > 1){
                    return (empty.indexOf('region_id') != -1 && empty.indexOf('region') != -1);
                }

                return !(empty.indexOf('region_id') == -1) || !(empty.indexOf('region') == -1);
            },

            _checkIsAddressValid:function(){
                var self = this;

                if(this.request && this.request.readystate != 4){
                    this.request.abort();
                }

                $('body').trigger('processStart');
                this.request = $.ajax({
                    url: this.options.urlValidation,
                    data: this.options.address,
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    beforeSend: function() {
                        $('.iwd-address-validation-error-message').remove();
                        if (!self.options.firstFormLoad) {
                            $(self.options.nextStepButtonId).parent().append('<div style="clear:both"></div><div class="iwd-address-validation-error-message mage-error" generated="true">Validating address... Please update shipping address before continuing.</div>')
                        }
                        self.options.firstFormLoad = false;
                    },
                })
                    .done(function(response) {
                        if (response.error){
                            console.log(JSON.stringify(response));
                        } else {
                            if(response.is_valid){
                                this._isAddressValid();
                            } else {
                                this._isAddressInvalid(response);
                            }
                        }
                    })
                    .fail(function(error) {
                        console.log(JSON.stringify(error));
                    })
                    .always(function () {
                        $('body').trigger('processStop');
                    });
            },

            _isAddressValid:function(){
                $('.iwd-address-validation-error-message').remove();
            },

            _isAddressInvalid:function(response){
                this._showModal(response);
            },

            _showModal:function (response) {
                var self = this;

                $('.iwd-address-validation-popup').removeClass('_show').addClass('_hide');

                modal({
                    title: $t('Address Validation'),
                    content: response.modal_content,
                    modalClass: "iwd-address-validation-popup",
                    buttons:[
                        {
                            text: $t('Continue'),
                            class: '',
                            click: function() {
                                if (self._updateAddress(response)){
                                    this.closeModal();
                                } else {
                                    $('.iwd-address-validation-popup .modal-content .mage-error').remove();
                                    $('.iwd-address-validation-popup .modal-content')
                                        .append('<div generated="true" class="mage-error">' +
                                            $t(self.options.content.makeChoice) +
                                            '</div>');
                                }
                            }
                        }
                    ]
                })
            },

            _updateAddress:function(response){
                if ($("input[name='candidate']").length == 0){
                    return true;
                }

                var checkedAddress = $("input[name='candidate']:checked");
                if (checkedAddress.length == 0){
                    return false;
                }

                if (checkedAddress.val() == 'origin'){
                    this._isAddressValid();
                    return true;
                }

                var address = response.suggested_addresses[checkedAddress.val()];
                this.updateFormAddress(address);

                // updating Quote Address
                this._setQuoteValidAddress();

                return true;
            },

            updateFormAddress:function(address)
            {
                var shippingAddress = {};

                shippingAddress['street'] = address['street'];
                shippingAddress['city'] = address['city'];
                shippingAddress['country_id'] = address['country_id'];
                shippingAddress['postcode'] = address['postcode'];
                shippingAddress['region'] = address['region'];
                shippingAddress['region_id'] = address['region_id'];

                shippingAddress['ship_adv']         = _.has(address, "ship_adv") && address["ship_adv"] === true ? 1 : 0;
                shippingAddress['ship_adv_date']    = _.has(address, "ship_adv_date") ? address["ship_adv_date"] : "";
                shippingAddress['ship_adv_di']      = _.has(address, "ship_adv_di") ? address["ship_adv_di"] : "";
                shippingAddress['ship_adv_dpi']     = _.has(address, "ship_adv_dpi") ? address["ship_adv_dpi"] : "";

                return this.options.shippingAddress = shippingAddress;
            },

            _setQuoteValidAddress:function(){
                var self = this;

                if(this.request && this.request.readystate != 4){
                    this.request.abort();
                }

                $('body').trigger('processStart');
                this.request = $.ajax({
                    url: this.options.urlSetQuoteAddress,
                    data: this.options.shippingAddress,
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    })
                    .done(function(response) {
                        if (response.error){
                            console.log(response.message);
                        }
                    })
                    .fail(function(error) {
                        console.log(JSON.stringify(error));
                    })
                    .always(function () {
                        this._reloadWindows();
                    });
            },

            _reloadWindows:function(){
                window.location.reload();
            }
        });

        return $.mage.crimsonIwdAddressValidation;
    });
