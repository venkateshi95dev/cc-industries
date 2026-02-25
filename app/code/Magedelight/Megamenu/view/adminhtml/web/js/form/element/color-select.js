/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'jquery',
    'jquery/colorpicker/js/colorpicker'
], function (Element, utils, $) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        initialize: function () {
            this._super();
        },

        initColorPickerCallback: function (element) {
            var self = this;

            $(element).ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    self.value(hex);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                },
                onChange: function(hsb, hex, rgb, el) {
                    self.value('#'+hex);
                }
            }).bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
            });
        }
    });
});
