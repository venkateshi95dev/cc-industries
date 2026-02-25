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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\System\Config;


use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Validate Merchant Button block class
 *
 * Class ValidateMerchantButton
 */
class ValidateMerchantButton extends Field
{

    /**
     * String Key
     *
     * @var $_key
     */
    protected $_key;

    /**
     * String Label
     *
     * @var $_label
     */
    protected $_label;

    /**
     * @var BackendUrlInterface
     */
    protected BackendUrlInterface $backendUrl;

    /**
     * @var Phrase
     */
    protected Phrase $_buttonLabel;

    /**
     * Main Constructor of the Class
     *
     * @param BackendUrlInterface $backendUrl
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        BackendUrlInterface $backendUrl,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);


        /** @var $backendUrl */
        $this->backendUrl = $backendUrl;
        /** @var  $_buttonLabel */
        $this->_buttonLabel = __('Validate API Keys');
        $this->setTemplate('system/config/validation_merchant_button.phtml');
    }

    /**
     * Render and Un scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return ajax url for Merchant API validation Button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('ebizcharge_ebizcharge/system_config/' . $this->_key);
    }

    /**
     * Return button function name
     *
     * @return string
     */
    public function getButtonFunction()
    {
        return $this->_key;
    }

    /**
     * Render the Validation Button
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        /** Render the Button Block $button */
        $button = $this->getLayout()
            ->createBlock(
                Button::class
            )
            ->setData(
                [
                    'id' => $this->_key,
                    'label' => $this->_buttonLabel,
                ]
            );

        return $button->toHtml();
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $data = $element->getOriginalData();

        $this->_key = $data['id'];
        $this->_label = $data['label'];

        return $this->_toHtml();
    }
}
