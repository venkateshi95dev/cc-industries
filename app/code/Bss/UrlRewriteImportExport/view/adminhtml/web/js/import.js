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
define([
    "jquery"
], function ($) {
    "use strict";

    $('#entity').change(function () {
        if ($('#entity').val()=='bss_url_rewrite') {
            $('#basic_behavior_import_multiple_value_separator').val('|');
            $('.field-basic_behaviorfields_enclosure').css('display', 'none');
            $('.field-basic_behavior__import_field_separator').css('display', 'none');
            $('.field-basic_behavior_import_multiple_value_separator').css('display', 'none');
            $('.field-import_images_file_dir').css('display', 'none');
        } else {
            $('#basic_behavior_import_multiple_value_separator').val(',');
        }
    });

    $('#bss-version').appendTo('.field-entity .admin__field-control.control .admin__field');
});
