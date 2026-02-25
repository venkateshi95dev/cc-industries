define([
    'Magento_Ui/js/form/element/single-checkbox',
    'underscore',
    'jquery'
], function (SingleCheckbox, _, $) {
    'use strict';

    return SingleCheckbox.extend({

        /** @inheritdoc */
        initialize: function () {
            this._super();
            return this;
        },

        isVisible: function () {
            return window.checkoutConfig.hasOwnProperty('rush_service');
        },

        isChecked: function () {
            return window.checkoutConfig.hasOwnProperty('rush_service') &&
                window.checkoutConfig.rush_service.hasOwnProperty('rush_selected') &&
                window.checkoutConfig.rush_service.rush_selected === true;
        },

        getRushLabel: function () {
            return window.checkoutConfig.hasOwnProperty('rush_service') && window.checkoutConfig.rush_service.hasOwnProperty('rush_label')
                ?  window.checkoutConfig.rush_service.rush_label
                : '';
        },

        getRushMessage: function () {
            return window.checkoutConfig.hasOwnProperty('rush_service') && window.checkoutConfig.rush_service.hasOwnProperty('rush_message')
                ?  window.checkoutConfig.rush_service.rush_message
                : '';
        },

        adjustRush: function () {
            $('#rush_value').val($('#rush-service-checkbox').is(":checked"));
            $('#rush-service-checkbox').attr("disabled", true);
            $('body').trigger('processStart');
            $('#rush-service-form').attr('action', window.checkoutConfig.rush_service.url);
            $("#rush-service-form").submit();
        },

        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});
