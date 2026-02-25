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

class Delete extends Generic implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getBrandId() && $this->_isAllowed()) {
            $data = [
                'label' => __('Delete Brand'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['brand_id' => $this->getBrandId()]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->authorization->isAllowed('Crimson_Brand::edit');
    }
}
