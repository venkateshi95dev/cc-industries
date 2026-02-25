/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

define([
    'jquery'
], function ($, modal) {
    'use strict';

    return function (data, element) {
        var showVerticalMenuOn = data.show_vertical_menu_on,
             displayOverlay = data.display_overlay,
             megaMenuNav = '.vertical-menu-category > ul > li',
             noOfSubCategoryToShow = Number(data.no_of_subcat_to_show) - 1,
             show_more = 'li.show-more',
             show_less = 'li.show-less',
             verticalMenuInner = 'div.vertical-menu-inner';
        if(showVerticalMenuOn == 'click'){
            $(element).on('click', function(e){
                e.stopPropagation();
                $(verticalMenuInner).not($(this)).removeClass('active click');
                $(this).toggleClass('active click');
                if(displayOverlay == '1'){
                    $('body').toggleClass('menu-active-bg');
                }
            });
            $(document).click(function() {
                var container = $(element);
                if (!container.is(event.target) && !container.has(event.target).length) {
                    $(container).removeClass('active click');
                    $('body').removeClass('menu-active-bg');
                }
            });
        }
        if(showVerticalMenuOn == 'hover'){
            $(element).hover(function(){
                $(this).addClass('active hover');
                if(displayOverlay == '1'){
                    $('body').toggleClass('menu-active-bg');
                }
              }, function(){
                $(this).removeClass('active hover');
                $('body').removeClass('menu-active-bg');
            });
        }
        $(megaMenuNav).hover(function(){
            var dataItemId = $(this).attr('data-item-id');
            $(megaMenuNav).removeClass('active');
            $(this).addClass('active');
            $('.vertical-menu-right .category-menu > div').removeClass('active');
            $('.vertical-menu-right .category-menu > div[data-item-id='+dataItemId+']').addClass('active');
        });
        $(show_more).on('click', function(e){
            e.stopPropagation();
            $(this).parent().find('li').show();
            $(this).hide();
        });
        $(show_less).on('click', function(e){
            e.stopPropagation();
            $(this).parent().find('li:gt('+noOfSubCategoryToShow+')').hide();
            $(this).parent().find('li.show-more').show();
        });
    };
});
