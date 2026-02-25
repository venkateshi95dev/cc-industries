/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
define(['jquery', 'domReady!'], function ($) {

    'use strict';
    return function (config) {
        var $mdAmazonMenu = $('.md-amazon-menu');
        var $mdAmazonMenuUl = $mdAmazonMenu.find('ul');
        var $mdAmazonMenuFirstUl = $mdAmazonMenuUl.first();
        var $mdMenuCloseBtn = $('.md-menu-close-btn');

        // Handle parent click to show child menu
        $('.md-amazon-parent > span').on('click', function (e) {
            var $currentItem = $(this).parent(); // Current clicked parent <li>
            var $parentMenu = $currentItem.closest('ul'); // Parent <ul>
            var $childMenu = $currentItem.children('ul'); // Child <ul>

            if ($childMenu.length > 0) {
                e.preventDefault(); // Prevent default link behavior

                // Stop propagation to prevent conflicts
                e.stopPropagation();

                // Remove visibility and active classes from the parent menu
                $parentMenu.removeClass('hmenu-visible md-translateX').addClass('md-translateX-left active');

                // Add visibility and active classes to the child menu
                $childMenu.addClass('hmenu-visible md-translateX').removeClass('md-translateX-right');
            }
        });

        // Prevent click propagation on child menus
        $('.md-amazon-parent > ul').on('click', function (e) {
            e.stopPropagation(); // Stop click propagation on child menus
        });

        // Close button functionality
        $mdMenuCloseBtn.on('click', function () {
            $('html').removeClass('amz-nav-open');
            $mdAmazonMenuUl.removeClass('hmenu-visible md-translateX md-translateX-left md-translateX-right');
        });

        // Handle navigation reset
        $('.amazon-menu-btn > a').on('click', function (e) {
            e.preventDefault();

            // Reset all menus to initial state
            $('html').addClass('amazon-nav-open amz-nav-open');
            $mdAmazonMenuUl.removeClass('hmenu-visible md-translateX md-translateX-left md-translateX-right');
            $mdAmazonMenuFirstUl.addClass('hmenu-visible');
        });

        $('.md-menu-back-btn').on('click', function (e) {
            var $currentItem = $(this).parent(); // Current clicked parent <li>
            var $parentMenu = $currentItem.closest('ul'); // Parent <ul>

            $parentMenu.removeClass('hmenu-visible md-translateX active').addClass('md-translateX-left');
            // console.log($parentMenu.closest('ul'));
            $parentMenu.parent('li').closest('ul').addClass('hmenu-visible md-translateX').removeClass('md-translateX-left active');
        })
    };
});
