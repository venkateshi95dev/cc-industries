<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Showmenuon implements OptionSourceInterface
{
    /**
     * Show Menu On option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'hover',
                'label' => __('Hover'),
            ],
            [
                'value' => 'click',
                'label' => __('Click'),
            ]
        ];
    }
}
