define([
    'jquery',
    'uiComponent',
    'mage/translate'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();
            let self = this,
                $buttonWrap = $('#product-instock-alert-wrapper'),
                $button = $('#product-instock-alert-action'),
                $form = $('#product_addtocart_form'),
                typeId = self.typeId,
                uenc = self.uenc,
                baseSignupUrl = self.baseSignupUrl;

            // simple
            if (typeId === 'simple') {
                $buttonWrap.show();
                return;
            }

            $form.on('configurable.initialized', function () {
                let configurable = $form.data('mageConfigurable'),
                    settings = configurable.options.settings,
                    stockInfo = configurable.options.spConfig.stockInfo;

                settings.on('change', function () {
                    let productId = self.getProductId(configurable.simpleProduct);
                    if (!productId) return;

                    let currentOptionName = settings.find(':selected').text().trim(),
                        isInStock = stockInfo[productId]['is_in_stock'],
                        url = `${baseSignupUrl}product_id/${productId}/uenc/${uenc}/`,
                        title = $t('Notify me when "%1" option for this item is back in stock').replace('%1', currentOptionName);

                    // update button url, title.
                    $button
                        .attr('href', url)
                        .attr('title', title)
                        .text(title);

                    // update button visibility.
                    $buttonWrap.toggle(!isInStock);
                });
            });
        },

        getProductId: function(productId) {
            if (typeof productId !== 'undefined' && parseInt(productId) > 0) {
                return productId;
            }

            return null;
        },
    });
});
