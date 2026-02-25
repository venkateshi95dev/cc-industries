<?php
declare(strict_types=1);

namespace Crimson\Catalog\Block\Adminhtml\Form\Field;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Messages extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('item_discount_group', ['label' => __('Item Discount Group'), 'class' => 'required-entry']);
        $this->addColumn('message', ['label' => __('Message')]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
