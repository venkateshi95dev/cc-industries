<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\Config\Source;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class to get I95Dev Cloud Options
 */
class CrmErp implements ArrayInterface
{
    public const CLOUD = 0;

    /**
     * CRM or ERP Values for Configuration
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        try {
            return [
                [
                    'value' => CrmErp::CLOUD,
                    'label' => __('I95Dev Cloud')
                ]
            ];
        } catch (LocalizedException $exc) {
            throw new LocalizedException(__($exc->getMessage()));
        }
    }
}
