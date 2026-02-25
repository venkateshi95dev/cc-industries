<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2021 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Ui\Component\Listing\Column;

use Magento\Framework\Option\ArrayInterface;

class SalesTypeOptions implements ArrayInterface
{
    public const CUSTOMER = 0;
    public const CUSTOMERPRICEGROUP = 1;
    public const ALLCUSTOMERS = 2;

    /**
     * Get sales type options
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ALLCUSTOMERS, 'label' => __('All Customers')],
            ['value' => self::CUSTOMERPRICEGROUP, 'label' => __('Customer Price Group')],
            ['value' => self::CUSTOMER, 'label' => __('Customer')]
        ];
    }
}
