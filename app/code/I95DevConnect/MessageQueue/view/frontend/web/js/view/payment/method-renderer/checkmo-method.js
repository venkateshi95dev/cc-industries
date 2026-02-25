/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */
define(
    [
            'Magento_Checkout/js/view/payment/default'
        ],
    function (Component) {
            'use strict';

            return Component.extend(
                {
                    defaults: {
                        template: 'I95DevConnect_MessageQueue/payment/checkmo',
                        checkNumber: ''
                    },
                    initObservable: function () {
                        this._super()
                            .observe('checkNumber');
                        return this;
                    },
                    getData: function () {
                        return {
                            "method": this.item.method,
                            "additional_data": {"check_number":this.checkNumber()},
                        };
                    },
                    /**
                     * Returns send check to info
                     */
                    getMailingAddress: function () {
                        return window.checkoutConfig.payment.checkmo.mailingAddress;
                    },
                    /**
                     * Returns payable to info
                     */
                    getPayableTo: function () {
                        return window.checkoutConfig.payment.checkmo.payableTo;
                    }
                    ,
                    validate: function () {
                        let form = 'form[data-role=checkmo-form]';
                        return jQuery(form).validation() && jQuery(form).validation('isValid');
                    }
                }
            );
    }
);
