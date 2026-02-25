<?php

namespace Crimson\StoreHoursCustomizations\Model\System\Config\Backend;

use Crimson\StoreHours\Model\System\Config\Backend\HoursAdvanced;
use Magento\Config\Model\Config\Backend\Serialized;

class HoursAdvancedCustom extends HoursAdvanced
{

    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            foreach ($value as $dayOfWeek => &$hoursData) {
                if (empty($hoursData['open_time']) || empty($hoursData['close_time'])) {
                    $hoursData['open_time'] = '';
                    $hoursData['close_time'] = '';
                    $hoursData['is_closed'] = 1;
                }
            }
            $this->setValue($value);
        }

        return Serialized::beforeSave();
    }
}
