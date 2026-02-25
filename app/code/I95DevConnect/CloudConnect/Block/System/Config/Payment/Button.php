<?php

/** @noinspection DuplicatedCode */

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\CloudConnect\Block\System\Config\Payment;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url;

/**
 * Payment mapping button class
 */
class Button extends Field
{
    // @codingStandardsIgnoreStart
    protected $_template = 'I95DevConnect_CloudConnect::system/config/payment/button.phtml';
    // @codingStandardsIgnoreEnd

    /**
     * @var Url
     */
    public $urlHelper;

    /**
     * Button constructor.
     *
     * @param Context $context
     * @param Url $urlHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Url $urlHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;

        parent::__construct($context, $data);
    }

    /**
     * Render element
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
     * Get element HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get button url
     *
     * @return string|null
     */
    public function getButtonUrl()
    {
        return $this->urlHelper->getUrl('cloudconnect/payment/index');
    }

    /**
     * Get button HTML
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        try {
            $button = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'id' => 'sync_payment_method',
                    'label' => __('Click here to Sync Now'),
                ]
            );
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $button->toHtml();
    }
}
