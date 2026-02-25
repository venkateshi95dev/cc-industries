<?php

namespace Crimson\CorvetteCentralNewsletter\Block\Adminhtml\Subscriber\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;

class LastNameColumn extends Column
{
    /**
     * Render method to display a fallback to customer table.
     *
     * @param DataObject $row
     * @return string
     */
    public function getRowField(DataObject $row): string
    {
        $lastName = $row->getData('last_name');
        if ($lastName) {
            return $lastName;
        }
        return $row->getData('lastname') ?: '';
    }
}
