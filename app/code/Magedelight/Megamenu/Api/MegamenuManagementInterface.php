<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Api;

interface MegamenuManagementInterface
{
    /**
     * Get info about product by product SKU
     *
     * @param int $customerId
     * @return \Magedelight\Megamenu\Api\MegamenuInterface
     */
    public function getMenuData($customerId = null);

    /**
     * Get menu data by id
     *
     * @param int $menuId
     * @param int $customerId
     * @return \Magedelight\Megamenu\Api\MegamenuInterface
     */
    public function getMenuDataById($menuId, $customerId = null);
}
