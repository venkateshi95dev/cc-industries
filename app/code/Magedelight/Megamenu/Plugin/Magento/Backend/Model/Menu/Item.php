<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Plugin\Magento\Backend\Model\Menu;

class Item
{
    /**
     * Add url into Menu
     *
     * @param \Magedelight\Megamenu\Plugin\Magento\Backend\Model\Menu\Item $subject
     * @param string $result
     * @return string
     */
    public function afterGetUrl($subject, $result)
    {
        $menuId = $subject->getId();
        if ($menuId == 'Magedelight_Megamenu::documentation') {
            $result = 'http://docs.magedelight.com/display/MAG/Mega+Menu+-+Magento+2';
        }

        return $result;
    }
}
