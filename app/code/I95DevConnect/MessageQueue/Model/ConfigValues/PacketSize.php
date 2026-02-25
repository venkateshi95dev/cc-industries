<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ConfigValues;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class for Getting Message Queue Packet Size
 */
class PacketSize implements ArrayInterface
{
    /**
     * Getting Message Queue Packet Size
     *
     * @return array
     */
    public function toOptionArray()
    {
        $value = 'value';
        $label = 'label';
        return [
            [$value => 10, $label => __(10)],
            [$value => 20, $label => __(20)],
            [$value => 30, $label => __(30)],
            [$value => 40, $label => __(40)],
            [$value => 50, $label => __(50)],
            [$value => 100, $label => __(100)]
        ];
    }
}
