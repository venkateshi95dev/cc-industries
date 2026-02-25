<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Block;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ShortcodeVertical extends Topmenu
{
    /**
     * Get All Category Menu Items
     *
     * @return AbstractCollection
     */
    public function getAllCategoryMenuItems()
    {
        $menuId = $this->getMenuid();
        $getMenuById = $this->megamenuManagement->loadAllMegaMenus();
        return $getMenuById->addFieldToFilter('main_table.menu_id', $menuId)
            ->addFieldToFilter('menu_design_type', self::ALL_CATEGORY_MENU);
    }
}
