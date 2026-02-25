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
    'jquery',
    'Magento_Ui/js/modal/modal'

], function ($, modal) {
    'use strict';

    return function (configData) {

        /**
         * EBizCharge How it Works
         *
         * @type {{videoModal: (*|jQuery.fn.init|S.fn.init|jQuery|HTMLElement), _init: _init, howItWorksLink: (*|jQuery.fn.init|S.fn.init|jQuery|HTMLElement), playVideo: playVideo, playVideoLink: (*|jQuery.fn.init|S.fn.init|jQuery|HTMLElement), hideTooltipBtn: (*|jQuery.fn.init|S.fn.init|jQuery|HTMLElement), tooltipDiv: (*|jQuery.fn.init|S.fn.init|jQuery|HTMLElement), toggleTooltip: toggleTooltip, hideTooltip: hideTooltip}}
         */
        let EBizChargeHowItWorks = {
            /**
             * Default variables
             */
            tooltipDiv: null,
            videoModal: null,
            playVideoLink: null,
            howItWorksLink: null,
            hideTooltipBtn: null,
            howItWorksIframeId: null,
            htmlBody: null,
            pageWrapper: null,

            /**
             * Init Function
             *
             * @param $
             * @param modal
             * @param configData
             * @private
             */
            _init: function ($, modal = null, configData = null) {

                this.tooltipDiv = $(document.getElementById('how-it-works-tooltip'));
                this.videoModal = $(document.getElementById('how-it-works-modal'));
                this.playVideoLink = $('.tooltip-video');
                this.howItWorksLink = $(document.getElementById('how-it-works-link'));
                this.hideTooltipBtn = $(document.getElementById('hide-tooltip'));
                this.htmlBody = $(document.getElementById('html-body'));
                this.pageWrapper = $(document.getElementsByClassName('page-wrapper'));

                this.toggleTooltip();
                this.hideTooltip();
                this.playVideo();
                this.renderClosePopup($, modal, configData);
            },

            /**
             * Render Close Popup
             * @param $
             * @param modal
             * @param configData
             */
            renderClosePopup: function ($, modal = null, configData = null) {
                    this.htmlBody.bind("click keypress", function (evt) {
                        let targetId = evt.target.id;

                        if (
                            $(evt.target).closest('#how-it-works-tooltip').length !== 0 ||
                            $(evt.target).closest('.how-it-works-link').length !== 0
                        ) {
                                return false;
                        }
                        EBizChargeHowItWorks.tooltipDiv.hide();

                        if ($(evt.target).closest('.modal-inner-wrap').length !== 0) {
                            if($(evt.target).attr("type") !== "text"){
                                return false;
                            }
                        }
                        let checkclass = evt.target.className ;
                        if (checkclass.indexOf("_show") >= 0) {

                            $( ".action-close" ).trigger( "click" );
                        }

                    });
            },

            /**
             * Toggle tooltip
             */
            toggleTooltip: function () {
                this.howItWorksLink.on('click keypress', function (e) {
                    EBizChargeHowItWorks.tooltipDiv.toggle();
                });
            },

            /**
             * Hide tooltip
             */
            hideTooltip: function () {
                this.hideTooltipBtn.on('click keypress', function (e) {
                    EBizChargeHowItWorks.tooltipDiv.hide();
                });
            },

            /**
             * Play YouTube video
             */
            playVideo: function () {
                this.playVideoLink.on('click keypress', function (e) {
                    let videoLink = $(this).data('video-link');
                    let options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'how-it-works-popup',
                        buttons: [],
                        modalCloseBtnHandler: function (e) {
                            EBizChargeHowItWorks.howItWorksIframe = $(document.getElementById('how-it-works-iframe'));
                            EBizChargeHowItWorks.howItWorksIframe.attr("src","");
                            this.closeModal();
                        },
                    };

                    let videoModal = EBizChargeHowItWorks.videoModal;
                    let popup = modal(options, videoModal);

                    let videoIframe = '<iframe id="how-it-works-iframe" class="how-it-works-iframe" src="' + videoLink + '" frameborder="0" allowfullscreen></iframe>';
                    videoModal.html(videoIframe);
                    videoModal.modal('openModal').show();
                });
            }
        }

        return EBizChargeHowItWorks._init($, modal, configData);
    }
});
