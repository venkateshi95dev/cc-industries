<?php
/**
 * @author Arushi Bansal
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ShippingMapping
 */

namespace I95DevConnect\ShippingMapping\Api;

/**
 * Management Interface for Shipping Mapping
 */
interface ShippingMethodMagInterface
{

    /**
     * Data insertion into shipping mapping table
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function availableShippingMethod();
}
