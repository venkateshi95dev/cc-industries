/*global define*/
define([
    'jquery',
    'ko',
    'uiComponent',
    'domReady!'
], function($, ko, Component) {
    'use strict';
    return Component.extend({

        defaults: {
            template: 'Crimson_Demographics/demographics'
        },

        initialize: function () {
            this._super();
            return this;
        },

        /**
         * Get Demographics Enabled
         * @returns bool
         */
        getDemographicsEnabled: function(){
            return window.checkoutConfig.demographics_enabled == 1 ? true : false;
        },
        /**
         * Get Demographics Required
         * @returns bool
         */
        getDemographicsRequired: function(){
            return window.checkoutConfig.demographics_required == 1 ? true : false;
        },

        /**
         * Get list of demographics values
         * @returns {Object}
         */
        getDemographicsValues: function () {
            let options = window.checkoutConfig.demographics_options;
            return options.reverse();
        },

        /**
         * Get demographics message
         * @returns string
         */
        getDemographicsMessage: function () {
            return window.checkoutConfig.demographics_message;
        },

        /**
         * Get list of chosen demographics values
         * @returns {Object}
         */
        getChosenDemographics: function () {
            return window.checkoutConfig.demographics_chosen;
        },


    });
});
