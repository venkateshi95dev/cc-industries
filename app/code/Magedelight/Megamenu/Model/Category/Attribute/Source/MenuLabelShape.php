<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Category\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class MenuLabelShape extends AbstractSource
{
    /**
     * Get All Options
     *
     * @return array|\string[][]|null
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                [
                    'value' => 'square',
                    'label' => "Square"
                ],
                [
                    'value' => 'square-pointer',
                    'label' => 'Square Pointer'
                ],
                [
                    'value' => 'rounded',
                    'label' => 'Rounded'
                ],
                [
                    'value' => 'circle',
                    'label' => 'Circle'
                ],
                [
                    'value' => 'circle-pointer',
                    'label' => 'Circle Pointer'
                ],
                [
                    'value' => 'square-animated',
                    'label' => "Square Animated"
                ],
                [
                    'value' => 'circle-animated',
                    'label' => 'Circle Animated'
                ],
            ];
        }
        return $this->_options;
    }
}
