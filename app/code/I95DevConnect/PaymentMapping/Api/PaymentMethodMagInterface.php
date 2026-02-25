<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Management Interface for Payment Mapping
 */
interface PaymentMethodMagInterface
{
    /**
     * Get active payment methods
     *
     * @return array
     * @throws LocalizedException
     */
    public function availablePaymentMethods();
}
