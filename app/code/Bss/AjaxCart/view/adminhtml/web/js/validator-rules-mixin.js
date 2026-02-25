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

    return function (validatorRules) {
        $.validator.addMethod(
            'validate-greater-zero',
            function (value) {
                if (isNaN(parseInt(value))) {
                    return false;
                }
                value = parseInt(value);
                return !(value <= 0);
            },
            $.mage.__('Please enter a greater than 0 number.')
        );
        return validatorRules;
    };
});