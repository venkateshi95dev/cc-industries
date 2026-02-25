var config = {
    config: {
        map: {
            '*': {
                'crimsonIwdAddressValidation': 'Crimson_Checkout/js/address_validation'
            }
        },
        mixins: {
            'IWD_AddressValidation/js/validation': {
                'Crimson_Checkout/js/validation-mixin': true
            },
            'Magento_Checkout/js/model/quote': {
                'Crimson_Checkout/js/clean-functions-from-street': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Crimson_Checkout/js/model/checkout-data-resolver': true
            }
        }
    }
};
