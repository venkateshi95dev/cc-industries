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

define(
    [
        'jquery',
        'Magento_Ui/js/modal/modalToggle',
        'mage/translate'
    ], function ($, modalToggle) {
        'use strict';

        return function (config, deleteButton) {
            config.buttons = [
                {
                    text: $.mage.__('Cancel'),
                    class: 'action secondary cancel'
                }, {
                    text: $.mage.__('Delete'),
                    class: 'action primary',

                    /**
                     * Default action on button click
                     */
                    click: function (event) {
 //eslint-disable-line no-unused-vars
                        deleteButton.form.submit();
                        $("body").trigger("processStart");
                    }
                }
            ];

            modalToggle(config, deleteButton);
        };
    });
