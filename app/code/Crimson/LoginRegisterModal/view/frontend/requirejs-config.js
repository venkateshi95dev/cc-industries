var config = {
    'map': {
        '*': {
            'crimson_login':        'Crimson_LoginRegisterModal/js/login',
        }
    },
    'config': {
        'mixins': {
            'Magento_Checkout/js/view/minicart': {
                'Crimson_LoginRegisterModal/js/checkout/view/minicart': true
            }
        }
    }
};
