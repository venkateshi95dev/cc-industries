/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
require(['jquery'], function($) {
    $(document).ready(function() {

        $('.menu > ul > li:has( > ul)').addClass('menu-dropdown-icon');
        //Checks if li has sub (ul) and adds class for toggle icon - just an UI


        $('.menu > ul > li > ul:not(:has(ul))').addClass('normal-sub');
        //Checks if drodown menu's li elements have anothere level (ul), if not the dropdown is shown as regular dropdown, not a mega menu (thanks Luka Kladaric)

        $(".menu > ul").before("<a href=\"#\" class=\"menu-mobile\">Navigation</a>");

        //Adds menu-mobile class (for mobile toggle menu) before the normal menu
        //Mobile menu is hidden if width is more then 959px, but normal menu is displayed
        //Normal menu is hidden if width is below 959px, and jquery adds mobile menu
        //Done this way so it can be used with wordpress without any trouble

        $(".menu > ul > li").hover(function(e) {
            if ($(window).width() > 767) {
                var duration = '0.3s';
                 if (window.animation_time) {
                    duration = window.animation_time + 's';
                }
                $(this).children("ul").stop(true, false).css({
                    'animation-duration': duration
                });
                e.preventDefault();
            }
        }, function(e) {
            if ($(window).width() > 767) {
                e.preventDefault();
            }
        });

        $(".menu-vertical-wrapper").on('mouseenter mouseleave', '.menu-vertical-items', function(e) {
            var parent = $(this).closest('.menu-vertical-wrapper');

            parent.find('.menu-vertical-items, .vertical-subcate-content').removeClass('active');
            $(this).addClass('active');

            var toggleId = $(this).data('toggle');
            if (toggleId) {
                parent.find('#' + toggleId).addClass('active');
            }
        });
        //If width is more than 943px dropdowns are displayed on hover

        //If width is less or equal to 943px dropdowns are displayed on click (thanks Aman Jain from stackoverflow)
        $(".menu-mobile").click(function(e) {
            $(".menu > ul").toggleClass('show-on-mobile');
            e.preventDefault();
        });
        //when clicked on mobile-menu, normal menu is shown as a list, classic rwd menu story (thanks mwl from stackoverflow)

        /* menu toggle for mobile menu */
        var menuToogle = function() {

            /* Restrict to call code when burger menu is enable */
            var menuLength = document.getElementsByClassName('menu-container').length;
            if($("html").hasClass("md-burger-menu") == false && (menuLength !== 0)) {
                if ($('html').hasClass('nav-open')) {
                    $('html').removeClass('nav-open');
                    setTimeout(function() {
                        $('html').removeClass('nav-before-open');
                    }, 300);
                } else {
                    $('html').addClass('nav-before-open');
                    setTimeout(function() {
                        $('html').addClass('nav-open');
                    }, 42);
                }
            }

        }
        $(document).on("click", ".action.nav-toggle", menuToogle);

        /* Apply has active to parents */
        $('.nav-sections-item-content li.active').each(function() {
            $(this).parents('li').addClass('has-active');
            $(this).addClass('has-active');
        });

        if ($(window).width() >= 768) {
            var activeParents = $('.has-active').parents('.vertical-subcate-content');
            activeParents.addClass('active');

            var toggleId = activeParents.attr('id');
            $('.vertical-menu-left li[data-toggle="' + toggleId + '"]').addClass('active');

            $('.menu-vertical-items.active').each(function() {
                $('#' + $(this).data('toggle')).addClass('active');
            });

            var activeElements = $('.menu-vertical-wrapper .active');
            if (activeElements.length <= 0) {
                $('.menu-vertical-wrapper').each(function() {
                    var firstChild = $(this).find('.menu-vertical-items:first-child');
                    firstChild.addClass('active');
                    $(this).find('#' + firstChild.data('toggle')).addClass('active');
                });
            }
        }
        /* Apply has active to parents */

        if ($(window).width() <= 767) {
            $('.col-menu-3.vertical-menu-left .menu-vertical-items').each(function() {
                var childDivId = $(this).data('toggle');
                $(this).append($('#' + childDivId).html());
                $('.menu-vertical-items .menu-vertical-child').hide();
            });
        }
    });
});
