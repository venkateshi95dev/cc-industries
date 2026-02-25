<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/15/2019 9:57 AM
 * @brief
 */

namespace Crimson\Brand\Block\Adminhtml\ContentType\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->authorization = $context->getAuthorization();
        $this->urlBuilder = $context->getUrlBuilder();

    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        if (!$this->_isAllowed()) {
            return [];
        }

        return [
            'id' => 'add',
            'label' => __('Add New Brand'),
            'on_click' => "setLocation('" . $this->urlBuilder->getUrl('*/*/new') . "')",
            'class' => 'primary',
            'sort_order' => 15
        ];
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->authorization->isAllowed('Crimson_Brand::edit');
    }
}
