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
 * @package    Bss_UrlRewriteImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
require([
    'jquery',
    'prototype'
], function ($) {
    $('#bss-version').appendTo('.field-entity .admin__field-control.control');

    $("#entity").change(function () {
        $('#export_filter_container').css('display','block');
    });

    $("#export-by").change(function () {
        switch ($('#export-by').val()) {
            case 'entity-type':
                $('#entity-type').css('display', 'block');
                $('#store-id').css('display', 'none');
                $('#redirect-type').css('display', 'none');
                break;
            case 'store-id':
                $('#entity-type').css('display', 'none');
                $('#store-id').css('display', 'block');
                $('#redirect-type').css('display', 'none');
                break;
            case 'redirect-type':
                $('#entity-type').css('display', 'none');
                $('#store-id').css('display', 'none');
                $('#redirect-type').css('display', 'block');
                break;
            default:
                $('#entity-type').css('display', 'none');
                $('#store-id').css('display', 'none');
                $('#redirect-type').css('display', 'none');
        }
    });

    $('entity').selectedIndex = 0;
});