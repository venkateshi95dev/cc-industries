<?php

/**
 * Century Business Solutions.
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
 *
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 *
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Config\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Config Fieldset Payment.
 *
 * Class Payment
 */
class Payment extends Fieldset
{
    /**
     * @var Config
     */
    private $config;

    private SecureHtmlRenderer $secureRenderer;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Config $config,
        SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $authSession,
            $jsHelper,
            $data,
            $secureRenderer
        );

        // @var config
        $this->config = $config;
        // @var secureRenderer
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Add custom css class.
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element).' with-button';
    }

    /**
     * Return header title part of html for payment solution.
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {

        $html = '<div class="config-heading" >';
        $htmlId = $element->getHtmlId();
        $html .= '<div class="button-container"><button type="button"'
            .' "'
            .' class="button action-configure'
            .'" id="'.$htmlId.'-head" >'
            .'<span class="state-closed">'.__(
                'Configure'
            ).'</span><span class="state-opened">'.__(
                'Close'
            ).'</span></button>';

        $html // @noEscape
            .= $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                "ebizchargeToggleSolution.call(this, '".$htmlId."', '"
                .$this->getUrl('adminhtml/*/state')."');event.preventDefault();",
                'button#'.$htmlId.'-head'
            );

        $html .= '</div>';
        $html .= '<div class="heading"><strong>'.$element->getLegend().'</strong>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">'.$element->getComment().'</span>';
        }
        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Return header comment part of html for payment solution.
     *
     * @param AbstractElement $element
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load.
     *
     * @param AbstractElement $element
     *
     * @return false
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCollapseState($element)
    {
        return false;
    }

    /**
     * Return extra Js.
     *
     * @param AbstractElement $element
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getExtraJs($element)
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.ebizchargeToggleSolution = function (id, url) {
                var doScroll = false;
                 Fieldset.toggleCollapse(id, url);
                if($(this).hasClassName(\"disabled\")){
                    $(this).removeClass(\"disabled\").removeAttr(\"disabled\");
                }

                if ($(this).hasClassName(\"open\")) {
                    \$$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
        });";

        return $this->_jsHelper->getScript($script);
    }
}
