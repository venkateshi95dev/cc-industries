/**
* @package Magedelight_Faqs for Magento 2
* @author MageDelight Team
* @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
*/

define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select',
], function (jQuery, uiRegistry, Select) {
    'use strict';
    return Select.extend({
        initialize: function () {
            this._super();
            var questionType = uiRegistry.get('index = question_type');
            console.log(questionType);
            if((questionType) && questionType.initialValue === '1') {
                this.visible(false);
            } else {
                this.visible(true);
            }
            return this;
        },
    });
});
