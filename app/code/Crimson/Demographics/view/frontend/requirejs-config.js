var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Crimson_Demographics/js/checkout/action/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information-extended': {
                'Crimson_Demographics/js/checkout/action/set-payment-information-extended-mixin': true
            }
        }
    }
};
