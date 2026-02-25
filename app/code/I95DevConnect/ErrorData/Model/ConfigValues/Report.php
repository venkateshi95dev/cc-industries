<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model\ConfigValues;

/**
 * report class for error data module
 */
class Report implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Getting Report Sending Type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $value = 'value';
        $label = 'label';
        return [
            [$value => "Schedule", $label => __("Schedule")],
            [$value => "Instant", $label => __("Instant")]
        ];
    }
}
