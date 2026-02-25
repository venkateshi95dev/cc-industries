define([
    'ko',
    'Magento_Checkout/js/model/quote'
], function (ko, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();

                this.customBillingAddress = ko.observable({});
                this.updateCustomBillingAddress();

                quote.billingAddress.subscribe(function () {
                    this.updateCustomBillingAddress();
                }.bind(this));

                return this;
            },

            updateCustomBillingAddress: function () {
                var company = window.checkoutConfig.company;

                if (company) {
                    this.customBillingAddress({
                        prefix: '',
                        firstname: company.company_name || '',
                        middlename: '',
                        lastname: '',
                        suffix: '',
                        company: company.company_name || '',
                        street: [company.street || ''],
                        city: company.city || '',
                        region: company.region || '',
                        regionId: company.region_id || '',
                        postcode: company.postcode || '',
                        countryId: company.country_id || '',
                        telephone: company.telephone || '',
                        vatId: company.vat_id || '',
                        useCompany: company.use_company || '',
                        customAttributes: []
                    });
                } else {
                    this.customBillingAddress(quote.billingAddress());
                }
            }
        });
    };
});
