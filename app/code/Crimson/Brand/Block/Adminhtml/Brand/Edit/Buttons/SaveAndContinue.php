<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/15/2019
 */
namespace Crimson\Brand\Block\Adminhtml\Brand\Edit\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinue extends Generic implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        if (!$this->_isAllowed()) {
            return [];
        }

        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order' => 80,
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
