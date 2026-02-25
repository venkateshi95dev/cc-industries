/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

define(["jquery", 'Magento_Ui/js/modal/modal'], function ($, modal) {
    "use strict";
    return {
        showErrorMessage: function (errorId, errorUrl) {

            $(this).prop("disabled", true);
            if ($("#custom-error").length === 0) {
                $(".page-footer").append("<div id='custom-error' class='custom-error' ></div>");
            }
            let options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
            };
            $.ajax({
                url: errorUrl,
                data: {form_key: window.FORM_KEY, "error_id": errorId},
                async: false,
                showLoader: true,
                type: 'POST'
            }).done(function (responseData) {
                $('#custom-error').html(responseData);
                modal(options, $('#custom-error'));
                $("#custom-error").modal("openModal");
                $(this).prop("disabled", false);
            });
        },
        showDataMessage: function (dataId, dataUrl) {
            $(this).prop("disabled", true);
            if ($("#custom-data").length == 0) {
                $(".page-footer").append("<textarea disabled style='width: 100% !important; min-height: 150px; !important;' id='custom-data' class='custom-data' ></textarea>");
            }
            let options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
            };
            $.ajax({
                url: dataUrl,
                data: {form_key: window.FORM_KEY, "data_id": dataId},
                async: false,
                showLoader: true,
                type: 'POST'
            }).done(function (responseData) {
                $('#custom-data').html(responseData);
                modal(options, $('#custom-data'));
                $("#custom-data").modal("openModal");
                $(this).prop("disabled", false);
            });
        }
    }
});
