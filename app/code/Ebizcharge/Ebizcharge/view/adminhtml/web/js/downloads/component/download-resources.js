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
        "jquery",
        "mage/url",
        "jquery/ui",
        "ko",
        "uiComponent",
        "domReady!"

    ], function ($, url, ui, ko, Component, domMain) {
        'use strict';

        return function main(config)
        {

            /** defining Config Resources **/
            let configResource = config;

            /**
             * Download Resource Processor
             *
             * @type {{view_resource_btn: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), showProgressBar: showProgressBar, response_container: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), check_resource_btn: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), viewDownloadedResources: (function(*, *): boolean), progress_bar_ajax_url: *, progress_bar: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), tab_titles: {tab_download_customers: string, tab_download_orders: string, tab_download_items: string}, renderResults: renderResults, check_resource_ajax_url: *, downlaoded_resources: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), progress_bar_breadcrumbs: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), processClick: processClick, checkLatestResources: checkLatestResources, resource_view_url: *, currentProgress: (function(): number), init: init, processProgressBar: processProgressBar, total_resources: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), download_resource_ajax_url: *, runProcessor: runProcessor, processAjax: processAjax, animationInterval: number, processDownloadResources: processDownloadResources, progress_bar_label: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), renderProgressBar: renderProgressBar, resource_integration_progress: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), download_type: *, current_progress_value: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), download_resource_btn: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), ajaxResponse: null, hideProgressBar: hideProgressBar}}
             */
            let DownloadResourceProcessor = {
                animationInterval: 5000,
                /** defining & Initializing the Processor Variables **/
                download_type: config.download.download_type,
                check_resource_btn: $('#' + config.download.check_resource_btn),
                download_resource_btn: $('#' + config.download.download_resource_btn),
                view_resource_btn: $('#' + config.download.view_resource_btn),
                response_container: $('#' + config.download.resource_status_container),
                progress_bar: $('#' + config.download.progress_bar),
                progress_bar_label: $('#' + config.download.progress_bar_label),
                progress_bar_breadcrumbs: $('#' + config.download.progress_bar_breadcrumbs),
                resource_integration_progress: $('#' + config.download.resource_integration_progress),
                current_progress_value: $('#' + config.download.current_progress_value),
                downlaoded_resources: $('#' + config.download.downlaoded_resources),
                total_resources: $('#' + config.download.total_resources),
                results_table : $('#' + config.download.results_table),
                sync_title : $('#' + config.download.sync_title),
                last_download_resource : $('#' + config.download.last_download_resource),
                last_download_date_resource : $('#' + config.download.last_download_date_resource),
                from_download_resource : $('#' + config.download.from_download_resource),
                request_type: config.download.request_type,
                http_request_type: config.download.http_request_type,
                /** Resource URLs **/
                check_resource_ajax_url: config.download.check_ajax_url,
                download_resource_ajax_url: config.download.download_ajax_url,
                progress_bar_ajax_url: config.download.progress_bar_ajax_url,
                resource_view_url: config.download.resource_view_url,
                ajaxResponse: null,
                currentProgressCounter: 0,
                progressCounter : 3000,

                /** Download Tab Titles */
                tab_titles: {
                    tab_download_customers: 'Download Customers from EBizCharge Hub',
                    tab_download_items: 'Download Products from EBizCharge Hub',
                    tab_download_orders: 'Download Orders from EBizCharge Hub'
                },

                /** initialiazing the processor **/
                /**
                 * Init
                 *
                 * @param $
                 * @param url
                 * @param ko
                 * @param Component
                 * @param config
                 */
                init: function ($, url, uinterface, ko, Component, domMain, config) {
                    if (config.download.downloading_status === 2) {

                        DownloadResourceProcessor.toggleDownloadDependency($, config.download.progress_request_type);

                       DownloadResourceProcessor.showProgressBar($);
                       $('#' + config.download.check_resource_btn).prop('disabled', true);
                    }
                    /** processing Click **/
                    DownloadResourceProcessor.processClick($,url, uinterface, ko, Component, domMain, config);
                },

                /**
                 * process Click
                 *
                 * @param $
                 */
                processClick: function ($, url, uinterface, ko, Component, domMain, config) {

                    $(document).on('click keypress', function (evt) {

                        /** check evt target Id **/
                        if (evt.target.id === config.download.check_resource_btn) {
                            let currentTarget = evt.target.id;
                            DownloadResourceProcessor.hideProgressBar($);
                            /** check latest resources at Ebizcharge **/
                            DownloadResourceProcessor.checkLatestResources($, url, uinterface, ko, Component, domMain, config, currentTarget);
                        }

                        if (evt.target.id == config.download.download_resource_btn) {
                            let currentTarget = evt.target.id;
                            DownloadResourceProcessor.check_resource_btn.prop('disabled', true);
                            DownloadResourceProcessor.download_resource_btn.prop('disabled', true);
                            DownloadResourceProcessor.toggleDownloadDependency($, config.download.progress_request_type);

                            /** process Ajax **/
                            DownloadResourceProcessor.processDownloadResources($);
                        }

                        if (evt.target.id === config.download.view_resource_btn) {
                            /** open resources url **/
                            let resourceUrl = DownloadResourceProcessor.resource_view_url;
                            DownloadResourceProcessor.viewDownloadedResources($, resourceUrl);
                        }
                    });

                    /** Set download tile */
                    $(document).on('click keypress', 'a[name^="tab_download_"]', function () {
                        let tab_name = $(this).attr('name');
                        $('#sync_title_download').html(DownloadResourceProcessor.tab_titles[tab_name]);
                    })
                },
                /**
                 * check Latest Resources
                 *
                 * @param $
                 */
                checkLatestResources: function ($, url, uinterface, ko, Component, domMain, config) {

                    /** check Resource Ajax URL **/
                    var checResourceAjaxUrl = DownloadResourceProcessor.check_resource_ajax_url;
                    var httRequestType = DownloadResourceProcessor.http_request_type;

                    var formData = {
                        assets: DownloadResourceProcessor.request_type
                    }
                    /**
                     * checking Ajax
                     * **/
                    $.ajax(
                        {
                            url: checResourceAjaxUrl,
                            type: httRequestType,
                            data: formData,
                            dataType: 'JSON',
                            beforeSend: function () {
                                $(".messages").hide();

                                DownloadResourceProcessor.results_table.hide();
                                DownloadResourceProcessor.download_resource_btn.hide();
                            },
                            showLoader: true
                            ,
                            //use for display loader
                            success: function (response) {
                                /** parsing the JSON **/
                                var resourceResponse = JSON.parse(response);
                                DownloadResourceProcessor.results_table.show();
                                DownloadResourceProcessor.download_resource_btn.show();

                                /** parsing the JSON **/
                                let resourcerResponse = JSON.parse(response);

                                let lastDownloaded = resourceResponse.last_sync_total_records;
                                let lastDownloadedDate = resourceResponse.end_time;
                                let resourceToDownload = parseInt(resourceResponse.remote_total_records);

                                DownloadResourceProcessor.last_download_resource.html(lastDownloaded == null ? '0' : lastDownloaded);
                                DownloadResourceProcessor.last_download_date_resource.html(lastDownloadedDate == null ? 'Never' : lastDownloadedDate);
                                DownloadResourceProcessor.from_download_resource.html(resourcerResponse.remote_total_records);

                                if (resourceToDownload > 0) {
                                    DownloadResourceProcessor.from_download_resource.html(resourceToDownload);
                                    DownloadResourceProcessor.download_resource_btn.prop('disabled', false);
                                }

                            },
                            done: function (resp) {
                                console.log(resp);
                            },
                            error: function (xhr) {
                                DownloadResourceProcessor.results_table.hide();
                                DownloadResourceProcessor.download_resource_btn.hide();
                                console.log(xhr);

                            }

                        });

                },
                /**
                 * Process Download Resources
                 *
                 * @param $
                 */
                processDownloadResources: function ($) {
                    /** rendering Progress Bar */

                    DownloadResourceProcessor.runProcessor($, config.download.download_type);

                },
                /**
                 * process Progress Bar
                 *
                 * @param $
                 * @param response
                 */
                processProgressBar: function ($, response) {

                    if (response && response.success) {
                        /** show progress Bar **/
                        DownloadResourceProcessor.showProgressBar($);
                        var currentProgressValue = DownloadResourceProcessor.currentProgress();

                        if (currentProgressValue >= 100) {
                              clearInterval(progressAnimation);
                        }

                    }
                },
                /**
                 * run Processor
                 *
                 * @param $
                 * @param downloadtype
                 */
                runProcessor: function ($, downloadtype) {
                    var downloadResourceUrl = DownloadResourceProcessor.download_resource_ajax_url;
                    var formData = {
                        download_request: downloadtype
                    }
                    /** process download resources **/
                    this.processAjax($, downloadResourceUrl, formData, "GET", true, 'download');

                },
                /**
                 * current Progress
                 *
                 * @returns {number}
                 */
                currentProgress: function () {
                    return parseInt($('#current_progress_value').val());
                },
                /**
                 * process Ajax
                 *
                 * @param $
                 * @param ajaxUrl
                 * @param formData
                 * @param requestType
                 * @param showLoader
                 * @param renderType
                 */
                processAjax: function ($, ajaxUrl, formData, requestType, showLoader, renderType) {

                    var requestType = requestType ? requestType : "GET";

                    /** rendering Type **/
                    var renderType = renderType ? "downlaod" : "progress";

                    let showSpinner = showLoader ? showLoader : false;
                    /** processing Ajax */
                    /**
                     * checking Ajax
                     * **/
                    $.ajax({
                        url: ajaxUrl,
                        type: requestType,
                        data: formData,
                        dataType: 'JSON',
                        beforeSend: function () {

                        },
                        showLoader: showSpinner
                        , //use for display loader
                        success: function (response) {
                            /** parsing the JSON **/
                            var resourceResponse = JSON.parse(response);

                            if (resourceResponse && resourceResponse.success) {
                                /** running progress **/
                                DownloadResourceProcessor.processProgressBar($, resourceResponse);
                            }

                            /** render Progressbar **/
                            if (renderType == "progress") {
                                DownloadResourceProcessor.renderProgressBar($, resourceResponse);
                                if (resourceResponse.progress_counter === undefined || resourceResponse.progress_counter < 100) {
                                    setTimeout(function () {
                                        DownloadResourceProcessor.processAjax($, ajaxUrl, formData, requestType, false);
                                    }, DownloadResourceProcessor.progressCounter);
                                }
                            }
                        },
                        done: function (resp) {
                            console.log(resp);
                        },
                        error: function (xhr) {
                            console.log(xhr);
                            /** render Progressbar **/
                            if (renderType == "progress") {
                                DownloadResourceProcessor.renderProgressBar($, xhr);
                            }
                        }

                    });

                },
                /**
                 * view Downloaed Resources
                 *
                 * @param $
                 * @param resourceUrl
                 * @returns {boolean}
                 */
                viewDownloadedResources: function ($, resourceUrl) {
                    /** opening windows for downloaded resources **/
                    window.location.href = resourceUrl;
                    return true;
                },
                /**
                 * show Progress Bar
                 *
                 * @param $
                 */
                showProgressBar: function ($) {
                    /** setting interval and animating the bar **/
                    let progressUrl = DownloadResourceProcessor.progress_bar_ajax_url;
                    let formData = {
                        progress_request: config.download.progress_request_type
                    }
                    /** Progress bar Animation **/
                    /** downloaded Progress Information **/
                    let requestType = "GET";
                    let showLoader = true;

                    /** process ajax url **/
                    DownloadResourceProcessor.processAjax($, progressUrl, formData, requestType, showLoader);

                },
                /**
                 * render Progress Bar
                 *
                 * @param $
                 * @param progressResponse
                 */
                renderProgressBar: function ($, progressResponse) {

                    let counter = 0;
                    if (progressResponse) {
                        counter = progressResponse.progress_counter;
                    }

                    /** current progress Value **/
                    DownloadResourceProcessor.current_progress_value.val(counter);
                    DownloadResourceProcessor.currentProgressCounter = counter;

                    let progressBar = DownloadResourceProcessor.progress_bar;

                    let progressBarWidth = progressBar.width();
                    let progressLabel = DownloadResourceProcessor.progress_bar_label;
                    let progressLabelWidth = progressLabel.width();
                    let progressRatio = (0.01 * progressBarWidth);
                    let downloadResourceHtmlCounter = DownloadResourceProcessor.downlaoded_resources;
                    let totalDownloadedRecords = progressResponse.total_downloaded_records;
                    let totalRemoteRecords = progressResponse.total_remote_records;
                    let currentDownloadProgressHtml = totalDownloadedRecords+"/"+totalRemoteRecords;
                    downloadResourceHtmlCounter.html(currentDownloadProgressHtml);

                    /** showing progress Bar */
                    DownloadResourceProcessor.progress_bar.show();

                    /** show progress bar breadcrumbs **/
                    DownloadResourceProcessor.progress_bar_breadcrumbs.show();
                    DownloadResourceProcessor.resource_integration_progress.show();

                    progressLabelWidth = counter + "%";
                    DownloadResourceProcessor.progress_bar_label.width(progressLabelWidth);
                    progressLabel.html(counter + "%");

                    /** Render Progress **/
                    DownloadResourceProcessor.renderResults($, progressResponse, config);

                },
                /**
                 * render Results
                 *
                 * @param $
                 * @param progressResponse
                 * @param config
                 */
                renderResults: function ($, progressResponse, config) {

                    DownloadResourceProcessor.results_table.hide();

                    $("#" + config.download.id_check_table_2).show();
                    $("#" + config.download.id_downloaded_resources).html(progressResponse.total_downloaded_records);
                    $("#" + config.download.id_total_resource).html(progressResponse.total_remote_records);
                    $("#failed_counter").html(progressResponse.failed_counter);

                    let message = 'Downloading process has been completed ';
                    if (progressResponse.progress_counter === undefined || progressResponse.progress_counter < 100) {
                        message = 'Please wait, downloading is in progress..'
                    }

                    if (progressResponse.progress_counter > 99 ) {
                        DownloadResourceProcessor.toggleDownloadDependency($, config.download.progress_request_type)
                    }

                    DownloadResourceProcessor.progress_bar_breadcrumbs.html(message);
                },
                /**
                 * hide Progress Bar
                 *
                 * @param $
                 */
                hideProgressBar: function ($) {
                    DownloadResourceProcessor.progress_bar.hide();
                    DownloadResourceProcessor.progress_bar_breadcrumbs.hide();
                    DownloadResourceProcessor.resource_integration_progress.hide();

                },

                toggleDownloadDependency: function ($, requestType) {

                    let className = $('.orders-download-notice');
                    let refreshButton = $('#check_orders_btn');

                    if (requestType === 'download_order_progress') {
                        className = $('.download-notice');
                        refreshButton = $('#check_products_btn, #check_customers_btn');
                    }

                    className.toggle();
                    refreshButton.prop('disabled', (i, v) => !v);
                }

            }

            /**
             * Initialize download
             * Resources process
             */
            DownloadResourceProcessor.init($, url, ui, ko, Component, domMain, configResource);

        }

    });
