<?php

/** @noinspection DuplicatedCode */

namespace I95DevConnect\CloudConnect\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url;

/**
 * Class for adding a button for shipping mapping
 */
class Button extends Field
{
    // @codingStandardsIgnoreStart
    protected $_template = 'I95DevConnect_CloudConnect::system/config/button.phtml';
    // @codingStandardsIgnoreEnd

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
     * Render function
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
    // @codingStandardsIgnoreStart

    /**
     * Get button URL
     *
     * @return string|null
     */
    public function getButtonUrl()
    {
        return $this->urlHelper->getUrl('cloudconnect/get/index');
    }

    /**
     * Get Button HTML
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'sync_shipmethod',
                'label' => __('Click here to Sync Now'),
            ]
        );
        return $button->toHtml();
    }
}
