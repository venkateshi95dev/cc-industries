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

require([
        'jquery',
        'Magento_Ui/js/modal/confirm'
    ],
    function ($, confirmation) {

        $("#deleteCard").on('click keypress', function (e) {

            let deleteUrl = $(this).attr('href');
            e.preventDefault();

            confirmation({
                title: 'Delete this record.',
                content: 'Are you sure to delete this record?',
                actions: {
                    confirm: function (e) {
                        $('body').trigger('processStart');
                        window.location.replace(deleteUrl);
                    },
                    cancel: function () {
                        return false;
                    }
                },
                buttons: [{
                    text: $.mage.__('Cancel'),
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $.mage.__('Yes, delete.'),
                    class: 'action secondary',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }]
            });
        });
    });


