/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
define([
    'jquery',
    'domReady!'
], function ($, modal) {
    'use strict';

    return function (data, element) {
        var $element = $(element);
        var menuType = $element.find('#menu_type');
        var menuDesignType = $element.find('#menu_design_type');
        var sVWMSelector = $element.find('#show_verticalmenu_with_megamenu');
        var sVMOSelector = $element.find('#show_vertical_menu_on');
        var vMTSelector = $element.find('#vertical_menu_title');
        var sCIWMSelector = $element.find('#show_category_icon_with_menu');
        var sCCSelector = $element.find('#show_category_count');
        var nOSCTSSelector = $element.find('#no_of_sub_category_to_show');
        var dPSelector = $element.find('#display_position');
        var dOSelector = $element.find('#display_overlay');
        var isStickySelector = $element.find('#is_sticky_control');
        var menuAlignSelector = $element.find('#menu_alignment_control');
        var showViewMoreSelector = $element.find('#show_view_more');
        var customerGroupe = $element.find('#customer_groups').parents(".admin__field");
        var mainMenuHover = $element.find('#main_menu_hover_container');
        var subMenuHover = $element.find('#sub_menu_hover_container');
        var storeId = $element.find('#store_id');

        var selectors = {
            menuDesignType: {
                'horizontal': [dOSelector, sCCSelector, nOSCTSSelector, isStickySelector, menuAlignSelector, showViewMoreSelector,mainMenuHover,subMenuHover,customerGroupe],
                'horizontal-vertical': [dOSelector, sCCSelector, nOSCTSSelector, isStickySelector, menuAlignSelector, showViewMoreSelector,mainMenuHover,subMenuHover,customerGroupe],
                'all-category': [sVWMSelector, sVMOSelector, vMTSelector, sCIWMSelector, sCCSelector, dPSelector, dOSelector, showViewMoreSelector,customerGroupe],
                'vertical-left': [isStickySelector,mainMenuHover,subMenuHover,customerGroupe],
                'vertical-right': [isStickySelector,mainMenuHover,subMenuHover,customerGroupe],
                'drilldown': [isStickySelector],
                'amazon-menu': []
            }
        };

        var hideAllSelectors = function() {
            Object.values(selectors.menuDesignType).flat().forEach(function(selector) {
                selector.hide();
            });
        };

        hideAllSelectors();
        $(menuDesignType).parents(".admin__field").hide();
        $(sVWMSelector).hide();
        $(sVMOSelector).hide();
        $(vMTSelector).hide();
        $(sCIWMSelector).hide();
        $(sCCSelector).hide();
        $(nOSCTSSelector).hide();
        $(dPSelector).hide();
        $(dOSelector).hide();
        $(isStickySelector).hide();
        $(menuAlignSelector).hide();

        $(menuType).on('change', function() {
            hideAllSelectors();
            var currentVal = $(this).val();
            if (currentVal == 2) {
                $(menuDesignType).parents(".admin__field").show();
                selectors.menuDesignType[menuDesignType.val()].forEach(function(selector) {
                    selector.show();
                });
            }
        });

        var menuTypeSelectedValue = $(menuType).find(":selected").val();
        var menuDesignTypeSelectedValue = $(menuDesignType).find(":selected").val();
        if(menuTypeSelectedValue == 2){
            $(menuDesignType).parents(".admin__field").show();
            selectors.menuDesignType[menuDesignTypeSelectedValue].forEach(function(selector) {
                selector.show();
            });
        }

        $(menuDesignType).on('change', function() {
            hideAllSelectors();
            var currentVal = $(this).val();
            selectors.menuDesignType[currentVal].forEach(function(selector) {
                selector.show();
            });
            if(currentVal=='drilldown') {
                $(customerGroupe).parents(".admin__field").hide();
                $(storeId).parents(".admin__field").hide();
            } else {
                $(customerGroupe).parents(".admin__field").show();
                $(storeId).parents(".admin__field").show();
            }
        });
    };
});
