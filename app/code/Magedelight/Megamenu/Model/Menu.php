<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model;

use Magento\Framework\Model\AbstractModel;

class Menu extends AbstractModel
{
    /**#@+
     * Menu's Statuses
     */
    private const STATUS_ENABLED = 1;
    private const STATUS_DISABLED = 0;
    /**#@-*/

    /**#@+
     * Menu's Types
     */
    public const NORMAL_MENU = 1;
    public const MEGA_MENU = 2;
    /**#@-*/

    /**
     * Megamenu menu cache tag
     */
    public const CACHE_TAG = 'megamenu_menu';

    /**
     * @var string
     */
    protected $_cacheTag = 'megamenu_menu';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'megamenu_menu';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Megamenu\Model\ResourceModel\Menu::class);
    }

    /**
     * Prepare menu's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Prepare menu's types.
     *
     * @return array
     */

    public function getAvailableTypes()
    {
        return [self::NORMAL_MENU => __('NORMAL MENU'), self::MEGA_MENU => __('MEGA MENU')];
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
}
