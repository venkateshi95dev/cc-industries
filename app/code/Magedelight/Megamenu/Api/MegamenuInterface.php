<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Api;

interface MegamenuInterface
{
    /**
     * Get info about product by product SKU
     *
     * @return \Magedelight\Megamenu\Api\Data\ConfigInterface
     */
    public function getMenu();
}
