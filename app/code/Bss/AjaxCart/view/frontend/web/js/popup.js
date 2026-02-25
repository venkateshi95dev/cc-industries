/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('bss.popup', {
        options: {
            countDown: 0,
        },

        _create: function () {
            var self = this;
            var options = this.options;

            self.element.find('.btn-continue').click(function () {
                $.magnificPopup.close();
                clearInterval(window.count);
            });

            var countDown = options.countDown;

            if (countDown > 0) {
                window.count = setInterval(function () {
                    countDown -= 1;
                    self.element.find('span.countdown').text("(" + countDown + ")");
                    if (countDown <= 0) {
                        self.element.find('span.countdown').parent().trigger("click");
                        clearInterval(window.count);
                    }
                }, 1000);
            }
        }
    });

    return $.bss.popup;
});
