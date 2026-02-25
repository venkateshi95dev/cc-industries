<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class for get retry limit config value
 */
class RetryList implements ArrayInterface
{
    public const VALUE = 'value';
    public const LABEL = 'label';

    /**
     * Get retry limit
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
        [self::VALUE => '1', self::LABEL => __('1')],
        [self::VALUE => '2', self::LABEL => __('2')],
        [self::VALUE => '3', self::LABEL => __('3')],
        [self::VALUE => '4', self::LABEL => __('4')],
        [self::VALUE => '5', self::LABEL => __('5')],
        [self::VALUE => '10', self::LABEL => __('10')],
        [self::VALUE => '15', self::LABEL => __('15')],
        [self::VALUE => '20', self::LABEL => __('20')]
        ];
    }
}
