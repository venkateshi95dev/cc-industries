<?php

namespace Crimson\CorvetteCentralNewsletter\Block\Adminhtml\Subscriber\Grid\Column;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;

class FirstNameColumn extends Column
{
    /**
     * Render method to display a fallback to customer table.
     *
     * @param DataObject $row
     * @return string
     */
    public function getRowField(DataObject $row): string
    {
        $firstName = $row->getData('first_name');
        if ($firstName) {
            return $firstName;
        }
        return $row->getData('firstname') ?: '';
    }
}
