<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Width extends AbstractSource
{
    /**
     * Get All Options
     *
     * @return array
     */
    public function getAllOptions()
    {

        if (!$this->_options) {
            $this->_options = [
                [
                    'value' => 0,
                    'label' => 'Full Width'
                ],
                [
                    'value' => 1,
                    'label' => 'Auto'
                ],
                [
                    'value' => 2,
                    'label' => 'Custom'
                ],
            ];
        }

        return $this->_options;
    }
}
