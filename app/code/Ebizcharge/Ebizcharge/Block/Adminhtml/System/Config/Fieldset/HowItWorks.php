<?php
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

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\System\Config\Fieldset;

use Ebizcharge\Ebizcharge\Api\Data\HowItWorksInterface;
use Ebizcharge\Ebizcharge\Block\Adminhtml\HowItWorks\HowItWorks as HowItWorksBlock;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\View\LayoutInterface;

/**
 * Field class for showing how it works link in configuration section
 *
 * Class HowItWorks
 */
class HowItWorks extends Fieldset
{
    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param SecureHtmlRenderer $secureRenderer
     * @param LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        SecureHtmlRenderer $secureRenderer,
        LayoutInterface $layout,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $authSession,
            $jsHelper,
            $data,
            $secureRenderer
        );
        $this->_layout = $layout;
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        return $this->_layout->createBlock(HowItWorksBlock::class)
            ->setTemplate(HowItWorksInterface::EBIZ_HOW_IT_WORKS_TOOLTIP_TEMPLATE)
            ->toHtml();
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param AbstractElement $element
     * @return false
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
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getExtraJs($element)
    {
        return '';
    }
}
