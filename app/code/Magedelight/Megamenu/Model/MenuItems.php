<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model;

use Magento\Framework\Model\AbstractModel;

class MenuItems extends AbstractModel
{

    /**
     * Megamenu menu cache tag
     */
    public const CACHE_TAG = 'megamenu_menuitems';

    /**
     * @var string
     */
    protected $_cacheTag = 'megamenu_menuitems';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'megamenu_menuitems';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Megamenu\Model\ResourceModel\MenuItems::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Delete Items
     *
     * @param int $menuId
     * @return void
     */
    public function deleteItems($menuId)
    {
        $getMenuItems = $this->getCollection()
                ->addFieldToFilter('menu_id', $menuId);
        foreach ($getMenuItems as $menuItem) {
            $menuItem->delete();
        }
    }

    /**
     * Get Animation Option
     *
     * @return array
     */
    public function getAnimationOption()
    {
        $animation = $this->getData('animation_option');
        if (!$animation || $animation == 'undefined') {
            return '';
        }
        return $animation;
    }
}
