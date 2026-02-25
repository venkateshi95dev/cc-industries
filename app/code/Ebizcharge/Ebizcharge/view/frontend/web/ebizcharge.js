/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */


define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    "jquery/ui",
    "mage/translate"
], function ($, confirm) {
    "use strict";

    $.widget('mage.ebizcharge', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this credit card?')
        },

        /**
         * Bind event handlers for adding and deleting credit cards.
         * @private
         */
        _create: function () {
            var options = this.options,
                addCreditCard = options.addCreditCard;

            if (addCreditCard) {
                $(document).on('click', addCreditCard, this._addCreditCard.bind(this));
            }
        },

        /**
         * Add a new credit card.
         * @private
         */
        _addCreditCard: function () {
            window.location = this.options.addCreditCardLocation;
        },

    });

    return $.mage.ebizcharge;
});
