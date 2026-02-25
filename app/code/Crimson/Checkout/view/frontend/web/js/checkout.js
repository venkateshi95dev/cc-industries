define([
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/cart/cache',
        'jquery/ui'
    ],

    function ($, modal, $t, quote, cartCache){
        'use strict';

        $.widget('mage.crimsonIwdAddressValidationCheckout', {
            options: {
                urlValidation: "",
                allowInvalidAddress: true,
                formId: '#co-shipping-form',
                nextStepButtonId: "button.continue, button.action-save-address",
                closePopup: '.action-close, .action-hide-popup',
                shipHereButton: "button.action-select-shipping-item",
                newShippingAddressForm: '.opc-new-shipping-address',

                validateAddressTimeout:0,
                address: {},

                addressMap: {
                    'street':  'street[0]',
                    'street1': 'street[1]',
                    'city':    'city',
                    'country_id': 'country_id',
                    'postcode':   'postcode',
                    'region_id':  'region_id',
                    'region':     'region'
                }
            },
            request: null,
            currentModal: null,
            isExistingAddress: true,
            validation: false,

            init: function(options){
                this._initOptions(options);

                this.checkCandidateAddress();

                this.onClickNextButton();
                this.onClickCancelButton();

                this.onAddressForm();
                this.selectExistingAddress();

                // clear form to new address
                this.newAddressForm();

                // create global data
                this.addressValidationData();
            },

            _initOptions:function(options){
                var self = this;

                options = options || {};
                $.each(options, function(i, e){self.options[i] = e;});
            },

            validateAddress: function(){
                if (this.checkIsAddressFilled()){
                    this.checkIsAddressValid();
                }
            },

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

            beforeValidAddress: function(){
                this.disableNextButton(this.options.content.validatingAddress);
                this.validation = 'validations';
            },

            afterValidAddress: function(){
                this.validation = true;
            },

            _showError:function(error){
                console.log(JSON.stringify(error));
            },

            toArray:function(obj){
                var dataArray = [];
                for(var o in obj) {
                    dataArray.push(obj[o]);
                }
                return dataArray;
            },

            checkIsAddressFilled: function(){
                var empty = [];
                $.each(this.options.address, function(i, e) {
                    if (!e || e.length == 0 || e == 0){
                        empty.push(i);
                    }
                });

                if (empty.length == 0) {
                    return true;
                }

                return  (empty.indexOf('street') == -1) &&
                        (empty.indexOf('city') == -1) &&
                        (empty.indexOf('country_id') == -1) &&
                        (empty.indexOf('postcode') == -1) &&
                        ((empty.indexOf('region_id') == -1) || (empty.indexOf('region') == -1))
                ;
            },

            showModal:function (response) {
                var self = this;

                if($('.modal-popup._show:not(.iwd-address-validation-popup)').length){
                    if(window.AddressValidation && window.AddressValidation.showModal){
                        delete window.AddressValidation['showModal'];
                    }else{
                        return;
                    }
                }

                $('.iwd-address-validation-popup').remove();
                $('.iwd-address-validation-popup').removeClass('_show').addClass('_hide');

                modal({
                    clickableOverlay: false,
                    title: $t(self.options.content.header),
                    content: response.modal_content,
                    modalClass: "iwd-address-validation-popup",
                    buttons:[
                        {
                            text: $t('Continue'),
                            class: '',
                            click: function() {
                                if (self.updateAddress(response)){
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

            whenAddressValid:function(){
                $('.iwd-address-validation-error-message').remove();
                $(this.options.nextStepButtonId).attr('disabled', null);
                this.enableBlocks();
            },

            disableBlocks: function(){
                $('.iwd-loader-for-av').show();
            },

            enableBlocks: function(){
                $('.iwd-loader-for-av').hide();
            },

            whenAddressInvalid: function(response){
                this.disableNextButton(this.options.content.updateAddress);
                this.disableBlocks();
                this.showModal(response);
            },

            checkCandidateAddress:function() {
                $(document).on('change', '.iwd-address-validation-popup input[name="candidate"]', function(){
                    $('.iwd-address-validation-popup .modal-content .mage-error').remove();
                });
            },

            _isInStoreShippingMethod: function() {
                let shippingMethod = quote && quote.shippingMethod() != null
                    ? quote.shippingMethod()
                    : [];

                return "carrier_code" in shippingMethod &&
                    shippingMethod.carrier_code === "instore" &&
                    "method_code" in shippingMethod &&
                    shippingMethod.method_code === "pickup";
            },

            onClickNextButton: function() {
                var self = this;
                $(document).on('click touchstart', this.options.nextStepButtonId, (function(e) {
                    if (self._isInStoreShippingMethod()) {
                        $(self.options.nextStepButtonId).attr('disabled', null);
                        return;
                    }

                    if(self.validation == true && self.isExistingAddress == true){
                        $(self.options.nextStepButtonId).attr('disabled', null);
                        return;
                    }

                    if(self.validation == false) {
                        e.preventDefault();
                        $(self.options.nextStepButtonId).attr('disabled', 'disabled');
                        self.readAddressQuote();
                        self.validateAddress();
                    } else {
                        $(self.options.nextStepButtonId).attr('disabled', null);
                    }
                }));
            },

            onClickCancelButton: function(){
                var self = this;
                $(document).on('click touchstart', this.options.closePopup, (function(e) {
                    self.validation = false;
                }));
            },

            onAddressForm: function(){
                var self = this;
                var form_inputs = this.options.formId + ' input, ' + this.options.formId + ' select';
                var map = self.toArray(self.options.addressMap);

                $(document).on('change', form_inputs, (function() {
                    self.checkIsExistingAddress();
                    window.AddressValidation.showModal = 1;
                    if (!self.isExistingAddress && map.indexOf($(this).attr('name')) !== -1) {
                        self.disableNextButton('');
                        self.validation = 'changed';
                        clearTimeout(self.validateAddressTimeout);
                        self.validateAddressTimeout = setTimeout(function(){
                            self.isExistingAddress = false;
                            self.readAddressForm();
                            if(window.AddressValidation && window.AddressValidation.type){
                                delete window.AddressValidation['type'];
                            }else{
                                self.validateAddress();
                            }
                        }, 500);
                    }
                }));
            },

            disableNextButton: function(message){
                $(this.options.nextStepButtonId).attr('disabled', 'disabled');
                $('.iwd-address-validation-error-message').remove();
                $(this.options.nextStepButtonId).parent()
                    .append('<div style="clear:both"></div><div generated="true" class="iwd-address-validation-error-message mage-error">' +
                        $t(message) +
                        '</div>');
            },

            selectExistingAddress:function(){
                var self = this;
                $(document).on('click touchstart', this.options.shipHereButton, (function() {
                    if (self._isInStoreShippingMethod()) {
                        $(self.options.nextStepButtonId).attr('disabled', null);
                        return;
                    }

                    clearTimeout(self.validateAddressTimeout);
                    self.validateAddressTimeout = setTimeout(function(){
                        self.isExistingAddress = true;
                        self.validation = 'changed';
                        self.readAddressQuote();
                        self.validateAddress();
                    }, 500);
                }));
            },

            readAddressQuote: function(){
                var addressQuote = quote.shippingAddress();
                var address = {};

                address['postcode'] = addressQuote.postcode;
                address['city'] = addressQuote.city;
                address['country_id'] = addressQuote.countryId;
                address['region'] = addressQuote.region;
                address['region_id'] = addressQuote.regionId;

                if(addressQuote.telephone){
                    address['telephone'] = addressQuote.telephone;
                }


                var street = '';
                $.each(addressQuote.street, function(i, e){
                    street += ' ' + e;
                });
                address['street'] = street.trim();

                return this.options.address = address;
            },

            readAddressForm: function(){
                var address = {};
                var formId = this.options.formId;

                address['street'] = '';
                $.each(this.options.addressMap, function(i, e){
                    var elem = $(formId + ' [name="' + e + '"]');
                    if (elem && elem.length > 0){
                        if(i.indexOf('street') !== -1) {
                            address['street'] += ' ' + elem.val();
                        } else {
                            address[i] = elem.val();
                        }
                    }
                });

                return this.options.address = address;
            },

            checkIsExistingAddress:function(){
                this.isExistingAddress =
                    $(".shipping-address-items").length == 1 &&
                    $('.modal-popup._show #co-shipping-form').length == 0;

                return this.isExistingAddress;
            },

            updateExistingAddress:function(address){
                var self = this;
                $('button.action-show-popup').trigger('click');
                setTimeout(function(){
                    self.validation = 'existing';
                    self.updateFormAddress(address);
                }, 50);
            },

            updateAddress: function(response){
                this.checkIsExistingAddress();
                var self = this;
                if ($("input[name='candidate']").length == 0){
                    if(this.isExistingAddress){
                        this.updateExistingAddress(response.original_address);
                    }
                    self.hideOverlay();
                    return true;
                }

                var checkedAddress = $("input[name='candidate']:checked");
                if (checkedAddress.length == 0){
                    self.hideOverlay();
                    return false;
                }

                if (checkedAddress.val() == 'origin'){
                    this.whenAddressValid();
                    self.hideOverlay();
                    return true;
                }

                var address = response.suggested_addresses[checkedAddress.val()];
                address.type = 'suggested';
                self._setAddressValidationFields(address);

                if(this.isExistingAddress){
                    this.updateExistingAddress(address);
                } else {
                    this.updateFormAddress(address);
                }
                self.hideOverlay();

                return true;
            },

            updateFormAddress:function(address){
                var formId = this.options.formId;
                var map = this.options.addressMap;
                var suggested = 0;
                if(address.type && address.type == 'suggested') {
                    suggested = 1;
                    window.AddressValidation.type = 'suggested';
                }

                $('[name="street[0]"]').val('');

                $.each(map, function(i, e){
                    var elem = $(formId + ' [name="' + e + '"]');
                    if (elem && elem.length > 0 && address[i] && address[i] != ''){
                        elem.val(address[i]).trigger('change');
                    } else {
                        if (elem && elem.length && i !== 'street1') {
                            elem.val('').trigger('change');
                        }

                        if (elem && elem.length && i === 'street1') {
                            elem.val('').trigger('change');
                        }
                    }
                });

                if(suggested) {
                    this.whenAddressValid();
                }
            },

            hideOverlay: function(){
                var overlay = jQuery('.modals-overlay');
                if (overlay.length != 0 && jQuery(overlay[0]).attr("style") == 'z-index: 900;'){
                    overlay.attr("style", "z-index: 899;");
                }
            },

            newAddressForm: function(){
                var self = this;
                var map = self.toArray(self.options.addressMap);
                $(document).on('click touchstart', '.new-address-popup button', (function(e) {
                    $.each(map,function (index,key) {
                        if(key == 'country_id' || key == 'region_id'){
                            $('#opc-new-shipping-address select[name="'+key+'"]').val('');
                        }
                        $('#opc-new-shipping-address input[name="'+key+'"]').val('');
                    })
                }));
            },

            addressValidationData: function () {
                if(typeof window.AddressValidation != 'object'){
                    window.AddressValidation = {};
                }
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
            },
        });

        return $.mage.crimsonIwdAddressValidationCheckout;
    });
