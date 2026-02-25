<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Api\Data;

interface ConfigInterface
{
    /**
     * Get Menu Id
     *
     * @return int
     */
    public function getMenuId();

    /**
     * Get Menu Design Type
     *
     * @return string
     */
    public function getMenuDesignType();

    /**
     * Get Menu name
     *
     * @return string
     */
    public function getMenuName();

    /**
     * Get Menu Alignment
     *
     * @return string
     */
    public function getMenuAlignment();

    /**
     * Get is Active
     *
     * @return boolean
     */
    public function getIsActive();

    /**
     * Get Menu Type
     *
     * @return int
     */
    public function getMenuType();

    /**
     * Get Is Sticky
     *
     * @return int
     */
    public function getIsSticky();

    /**
     * Get Customer Groups
     *
     * @return string
     */
    public function getCustomerGroups();

    /**
     * Get Creation time
     *
     * @return string
     */
    public function getCreationTime();

    /**
     * Get Update Time
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Get Store Id
     *
     * @return string
     */
    public function getStoreId();

    /**
     * Get Menu Style
     *
     * @return string
     */
    public function getMenuStyle();

    /**
     * Get Menu Items
     *
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface[]
     */
    public function getMenuItems();
}
