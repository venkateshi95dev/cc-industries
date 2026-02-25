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

            /** Upload Resource Processor **/
            /**
             * Upload Resource Processor
             *
             * @type {{view_resource_btn: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), showProgressBar: showProgressBar, response_container: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), check_resource_btn: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), viewUploadedResources: (function(*, *): boolean), progress_bar_ajax_url: *, progress_bar: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), tab_titles: {tab_upload_customers: string, tab_upload_orders: string, tab_upload_items: string}, renderResults: renderResults, check_resource_ajax_url: *, uploaded_resources: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), progress_bar_breadcrumbs: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), processClick: processClick, checkLatestResources: checkLatestResources, resource_view_url: *, currentProgress: (function(): number), init: init, processProgressBar: processProgressBar, total_resources: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), upload_resource_ajax_url: *, runProcessor: runProcessor, processAjax: processAjax, animationInterval: number, processUploadResources: processUploadResources, progress_bar_label: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), renderProgressBar: renderProgressBar, resource_integration_progress: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), upload_type: *, current_progress_value: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), upload_resource_btn: (*|n.fn.init|jQuery.fn.init|jQuery|HTMLElement), ajaxResponse: null, hideProgressBar: hideProgressBar}}
             */
            let UploadResourceProcessor = {

                /**
                 * Animation Interval
                 */
                animationInterval: 3000,

                /** defining & Initializing
                 * the Processor Variables
                 * **/
                upload_type: config.upload.upload_type,
                results_table : $('#' + config.upload.results_table),
                check_resource_btn: $('#' + config.upload.check_resource_btn),
                upload_resource_btn: $('#' + config.upload.upload_resource_btn),
                view_resource_btn: $('#' + config.upload.view_resource_btn),
                response_container: $('#' + config.upload.resource_status_container),
                progress_bar: $('#' + config.upload.progress_bar),
                progress_bar_label: $('#' + config.upload.progress_bar_label),
                progress_bar_breadcrumbs: $('#' + config.upload.progress_bar_breadcrumbs),
                resource_integration_progress: $('#' + config.upload.resource_integration_progress),
                current_progress_value: $('#' + config.upload.current_progress_value),
                uploaded_resources: $('#' + config.upload.uploaded_resources),
                total_resources: $('#' + config.upload.total_resources),
                sync_title : $('#' + config.upload.sync_title),
                last_uploaded_date_resource : $('#' + config.upload.last_uploaded_date_resource),
                last_uplaoded_resource : $('#' + config.upload.last_uplaoded_resource),
                to_uplaod_resource : $('#' + config.upload.to_uplaod_resource),
                breadcrumb_messages : $('.'+ config.upload.breadcrumb_messages),
                http_request_type : config.http_request_type,
                check_resource_ajax_url: config.upload.check_ajax_url,
                upload_resource_ajax_url: config.upload.upload_ajax_url,
                progress_bar_ajax_url: config.upload.progress_bar_ajax_url,
                resource_view_url: config.upload.resource_view_url,
                request_type: config.upload.request_type,
                ajaxResponse: null,

                /** Upload Tab Titles */
                tab_titles: {
                    tab_upload_customers: 'Upload Customers to EBizCharge Hub',
                    tab_upload_items: 'Upload Products to EBizCharge Hub',
                    tab_upload_orders: 'Upload Orders to EBizCharge Hub'
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

                    console.log(config);

                    if (config.upload.uploading_status == 2) {

                        UploadResourceProcessor.toggleUploadDependency($, config.upload.progress_request_type)

                        UploadResourceProcessor.showProgressBar($);
                       $('#' + config.upload.check_resource_btn).prop('disabled', true);
                    }
                    /** processing Click **/
                    UploadResourceProcessor.processClick($,url, uinterface, ko, Component, domMain, config);
                },

                /**
                 * process Click
                 *
                 * @param $
                 */
                processClick: function ($, url, uinterface, ko, Component, domMain, config) {
                    $(document).on('click keypress', function (evt) {
                      //  console.log(evt.target.id);

                        /** check evt target Id **/
                        if (evt.target.id == config.upload.check_resource_btn) {
                            UploadResourceProcessor.hideProgressBar($);
                            /** check latest resources **/
                            UploadResourceProcessor.checkLatestResources($, url, uinterface, ko, Component, domMain, config );
                        }

                        if (evt.target.id == config.upload.upload_resource_btn) {

                            UploadResourceProcessor.check_resource_btn.prop('disabled', true);
                            UploadResourceProcessor.upload_resource_btn.prop('disabled', true);

                            UploadResourceProcessor.toggleUploadDependency($, config.upload.progress_request_type)

                            /** process Ajax **/
                            UploadResourceProcessor.processUploadResources($);
                        }
                        if (evt.target.id == config.upload.view_resource_btn) {
                            /** open resources url **/
                            let resourceUrl = UploadResourceProcessor.resource_view_url;
                            UploadResourceProcessor.viewUploadedResources($, resourceUrl);
                        }
                    });

                    /** Set upload tile */
                    $(document).on('click keypress', 'a[name^="tab_upload_"]', function () {
                        let tab_name = $(this).attr('name');
                        UploadResourceProcessor.sync_title.html(UploadResourceProcessor.tab_titles[tab_name]);
                    });
                },
                /**
                 * check latest resources
                 *
                 * @param $
                 */
                checkLatestResources: function ($, url, uinterface, ko, Component, domMain, config) {

                    /** check Resource Ajax URL **/
                    var checResourceAjaxUrl = UploadResourceProcessor.check_resource_ajax_url;
                    var httRequestType = config.upload.http_request_type;
                    let uploadCustomersBtn = UploadResourceProcessor.upload_resource_btn;

                    var formData = {
                        assets: config.upload.request_type
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
                            UploadResourceProcessor.results_table.hide();
                            UploadResourceProcessor.upload_resource_btn.hide();
                        },
                        showLoader: true
                        ,
                            //use for display loader
                        success: function (response) {
                            /** parsing the JSON **/
                            var resourceResponse = JSON.parse(response);
                            UploadResourceProcessor.results_table.show();
                            UploadResourceProcessor.upload_resource_btn.show();
                          //  UploadResourceProcessor.resource_integration_progress.show();

                            /** parsing the JSON **/
                            let customerResponse = JSON.parse(response);

                            let lastuploaded = customerResponse.last_sync_total_records;
                            let lastuploadedDate = customerResponse.end_time;
                            let customersToUpload = parseInt(customerResponse.remote_total_records);
                            let currentUploadStatus = parseInt(customerResponse.status);
                            let currentOrdersUploadStatus = parseInt(customerResponse.upload_orders_status);



                            UploadResourceProcessor.last_uplaoded_resource.html(lastuploaded == null ? 'Never' : lastuploaded);
                            UploadResourceProcessor.last_uploaded_date_resource.html(lastuploadedDate == null ? 'Never' : lastuploadedDate);
                          //  UploadResourceProcessor.to_uplaod_resource.html(customerResponse.last_sync_total_records);
                            UploadResourceProcessor.to_uplaod_resource.html(customerResponse.remote_total_records);


                            UploadResourceProcessor.upload_resource_btn.prop('disabled', true);

                            if (currentOrdersUploadStatus !== 2) {
                                UploadResourceProcessor.breadcrumb_messages.hide();
                                if (customersToUpload > 0 && currentUploadStatus >= 0 && currentUploadStatus <= 1) {
                                    UploadResourceProcessor.to_uplaod_resource.html(customersToUpload);
                                    UploadResourceProcessor.upload_resource_btn.prop('disabled', false);
                                }
                            } else {
                                UploadResourceProcessor.breadcrumb_messages.show();
                            }

                            console.log([config, UploadResourceProcessor]);

                            console.log(
                                [
                                    currentUploadStatus,
                                    currentOrdersUploadStatus,
                                UploadResourceProcessor.breadcrumb_messages
                                ]
                            );


                        },
                        done: function (resp) {
                            console.log(resp);
                        },
                        error: function (xhr) {
                            //to handle error
                            UploadResourceProcessor.results_table.hide();
                            UploadResourceProcessor.upload_resource_btn.hide();
                            console.log(xhr);

                        }

                    });

                },
                /**
                 * preocess Upload Resources
                 *
                 * @param $
                 */
                processUploadResources: function ($) {
                    /** rendering Progress Bar */

                    UploadResourceProcessor.runProcessor($, config.upload.upload_type);

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
                        UploadResourceProcessor.showProgressBar($);
                        var currentProgressValue = UploadResourceProcessor.currentProgress($);

                        if (currentProgressValue >= 100) {
                              clearInterval(progressAnimation);
                        }

                    }
                },

                /**
                 * run processor
                 *
                 * @param $
                 * @param uploadtype
                 */
                runProcessor: function ($, uploadtype) {
                    var uploadResourceUrl = UploadResourceProcessor.upload_resource_ajax_url;
                    var formData = {
                        upload_request: uploadtype
                    }
                    /** process upload resources **/
                    UploadResourceProcessor.processAjax($, uploadResourceUrl, formData, UploadResourceProcessor.http_request_type, true, 'upload');

                },
                /**
                 * current Progress
                 *
                 * @returns {number}
                 */
                currentProgress: function ($) {
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
                    var renderType = renderType ? "upload" : "progress";
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
                                UploadResourceProcessor.processProgressBar($, resourceResponse);
                            }

                            /** render Progressbar **/
                            if (renderType == "progress") {
                                UploadResourceProcessor.renderProgressBar($, resourceResponse);

                                if (resourceResponse.progress_counter === undefined || resourceResponse.progress_counter < 100) {
                                    setTimeout(function () {
                                        UploadResourceProcessor.processAjax($, ajaxUrl, formData, requestType, false);
                                    }, 3000);
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
                                UploadResourceProcessor.renderProgressBar($, xhr);
                            }
                        }

                    });

                },
                /**
                 * view Uploaded Resources
                 *
                 * @param $
                 * @param resourceUrl
                 * @returns {boolean}
                 */
                viewUploadedResources: function ($, resourceUrl) {
                    /** opening windows for uploaded resources **/
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
                    let progressUrl = UploadResourceProcessor.progress_bar_ajax_url;
                    let formData = {
                        progress_request: config.upload.progress_request_type
                    }
                    /** Progress bar Animation **/
                    /** uploaded Progress Information **/
                    let requestType = UploadResourceProcessor.http_request_type;
                    let showLoader = true;

                    /** process ajax url **/
                    UploadResourceProcessor.processAjax($, progressUrl, formData, requestType, showLoader);

                },
                /**
                 * render Progress Bar
                 *
                 * @param $
                 * @param progressResponse
                 */
                renderProgressBar: function ($, progressResponse) {

                    let counter = 0;
                    if (typeof progressResponse !== 'undefined') {
                        counter = parseInt(typeof (progressResponse.progress_counter) !== 'undefined' ? progressResponse.progress_counter: 0);
                    }
                   // console.log(progressResponse);

                    /** currnt progress Value **/
                    UploadResourceProcessor.current_progress_value.val(counter);

                    let progressBar = UploadResourceProcessor.progress_bar;
                    let progressBarWidth = progressBar.width();
                    let progressLabel = UploadResourceProcessor.progress_bar_label;
                    let progressLabelWidth = progressLabel.width();
                    let progressRatio = (0.01 * progressBarWidth);

                    /** showing progress Bar */
                    UploadResourceProcessor.progress_bar.show();

                    /** show progress bar breadcrumbs **/
                    UploadResourceProcessor.progress_bar_breadcrumbs.show();
                    UploadResourceProcessor.resource_integration_progress.show();


                    // let barWidthPercentage = counter * progressRatio;
                    let barWidthPercentage = counter;
                  //  console.log(barWidthPercentage);

                    /** show progress bar label progress_bar_label **/
                    progressLabelWidth = barWidthPercentage + "%";

                    if (counter > 0) {
                        progressLabel.width(progressLabelWidth);
                        progressLabel.html(counter + "%");
                    }
                    /** Render Progress **/
                    UploadResourceProcessor.renderResults($, progressResponse, config);

                },
                /**
                 * render Results
                 *
                 * @param $
                 * @param progressResponse
                 * @param config
                 */
                renderResults: function ($, progressResponse, config) {
                    UploadResourceProcessor.results_table.hide();
                    $("#" + config.upload.id_uploaded_resources).html(progressResponse.total_uploaded_records);
                    $("#" + config.upload.id_total_resource).html(progressResponse.total_remote_records);

                    let message = 'Uploading process has been completed ';

                    if (progressResponse.progress_counter === undefined || progressResponse.progress_counter < 100) {
                        message = 'Please wait, uploading is in progress..'
                    }

                    if (progressResponse.progress_counter > 99 ) {
                        UploadResourceProcessor.toggleUploadDependency($, config.upload.progress_request_type)
                    }
                    UploadResourceProcessor.progress_bar_breadcrumbs.html(message);
                },
                /**
                 * hide Progress Bar
                 *
                 * @param $
                 */
                hideProgressBar: function ($) {
                    UploadResourceProcessor.progress_bar.hide();
                    UploadResourceProcessor.progress_bar_breadcrumbs.hide();
                    UploadResourceProcessor.resource_integration_progress.hide();

                },

                toggleUploadDependency: function ($, requestType) {

                    let className = $('.orders-upload-notice');
                    let refreshButton = $('#check_orders_btn');

                    if (requestType === 'uploaded_order_progress') {
                         className = $('.upload-notice');
                        refreshButton = $('#check_products_btn, #check_customers_btn');
                    }

                    className.toggle();
                    refreshButton.prop('disabled', (i, v) => !v);
                }

            }

            /**
             * Initialize upload
             * Resources process
             */
            UploadResourceProcessor.init($, url, ui, ko, Component, domMain, configResource);

        }

    });
