define([
           'jquery',
           'underscore',
           'jquery/ui'
       ], function ($, _) {
    'use strict';

    return function (target) {

        $.widget('mage.configurable', target, {
            options: {
                spConfig: {}
            },

            setProductInfo: function (product) {

                var msgElement = $('#product_addtocart_form #item-selected-stock-message');

                if(typeof product !== 'undefined' && parseInt(product) > 0){
                    var spConfig = this.options.spConfig;
                    var stockInfo = spConfig.stockInfo;

                    if (stockInfo[product]) {
                       var stockChild = stockInfo[product];
                        msgElement.removeClass().addClass(stockChild.class);
                        msgElement.html('<span>' + stockChild.stockLabel + '</span>');
                    }
                } else {
                    msgElement.removeClass().addClass('stock available');
                    var defaultLabel = $.mage.__('Choose an Option for Stock Status ');
                    msgElement.html('<span>' + defaultLabel + '</span>');
                }
            },

            _reloadPrice: function () {
                $(this.options.priceHolderSelector).trigger('updatePrice', this._getPrices());
                this.setProductInfo(this.simpleProduct);
            },
        });

        return $.mage.configurable;
    };
});