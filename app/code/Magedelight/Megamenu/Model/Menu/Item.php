<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model\Menu;

use Magedelight\Megamenu\Api\Data\MenuItemsInterface;
use Magedelight\Megamenu\Model\MenuItems;

class Item extends MenuItems implements MenuItemsInterface
{
    /**
     * Get item Id
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->_getData(MenuItemsInterface::ID);
    }

    /**
     * Get Item Type
     *
     * @return string
     */
    public function getItemType()
    {
        return $this->_getData(MenuItemsInterface::TYPE);
    }

    /**
     * Get Item Name
     *
     * @return string
     */
    public function getItemName()
    {
        return $this->_getData(MenuItemsInterface::NAME);
    }

    /**
     * Get Sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_getData(MenuItemsInterface::SORT);
    }

    /**
     * Get Item Parent id
     *
     * @return int
     */
    public function getItemParentId()
    {
        return $this->_getData(MenuItemsInterface::PARENT_ID);
    }

    /**
     * Get Menu Id
     *
     * @return int
     */
    public function getMenuId()
    {
        return $this->_getData(MenuItemsInterface::MENU_ID);
    }

    /**
     * Get Object Id
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->_getData(MenuItemsInterface::OBJECT_ID);
    }

    /**
     * Get Creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->_getData(MenuItemsInterface::CREATED);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->_getData(MenuItemsInterface::UPDATED);
    }

    /**
     * Get Item Link
     *
     * @return string
     */
    public function getItemLink()
    {
        return $this->_getData(MenuItemsInterface::LINK);
    }

    /**
     * Get Item Column
     *
     * @return string
     */
    public function getItemColumns()
    {
        return $this->_getData(MenuItemsInterface::COLUMNS);
    }

    /**
     * Get Item Font Icon
     *
     * @return string
     */
    public function getItemFontIcon()
    {
        return $this->_getData(MenuItemsInterface::ICON);
    }

    /**
     * Get Item Class
     *
     * @return string
     */
    public function getItemClass()
    {
        return $this->_getData(MenuItemsInterface::ITEM_CLASS);
    }

    /**
     * Get Animation Option
     *
     * @return string
     */
    public function getAnimationOption()
    {
        return $this->_getData(MenuItemsInterface::ANIMATION);
    }

    /**
     * Get Category Vertical Menu
     *
     * @return string
     */
    public function getCategoryVerticalMenu()
    {
        return $this->_getData(MenuItemsInterface::VERTICAL_MENU);
    }

    /**
     * Get Category Vertical Menu Background
     *
     * @return string
     */
    public function getCategoryVerticalMenuBg()
    {
        return $this->_getData(MenuItemsInterface::VERTICAL_MENU_BG);
    }

    /**
     * Get Category display
     *
     * @return string
     */
    public function getCategoryDisplay()
    {
        return $this->_getData(MenuItemsInterface::DISPLAY);
    }

    /**
     * Get Category column
     *
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface[]
     */
    public function getCategoryColumns()
    {
        return $this->_getData(MenuItemsInterface::CAT_COLUMNS);
    }

    /**
     * Get childrens
     *
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface[]
     */
    public function getChildrens()
    {
        return $this->_getData(MenuItemsInterface::CHILDRENS);
    }

    /**
     * Set Item id
     *
     * @param int $id
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemId($id)
    {
        $this->setData(MenuItemsInterface::ID, $id);
        return $this;
    }

    /**
     * Set Item type
     *
     * @param string $type
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemType($type)
    {
        $this->setData(MenuItemsInterface::TYPE, $type);
        return $this;
    }

    /**
     * Set Item Name
     *
     * @param string $name
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemName($name)
    {
        $this->setData(MenuItemsInterface::NAME, $name);
        return $this;
    }

    /**
     * Set Sort Order
     *
     * @param int $sort
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setSortOrder($sort)
    {
        $this->setData(MenuItemsInterface::SORT, $sort);
        return $this;
    }

    /**
     * Set Item Parent Id
     *
     * @param int $parentId
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemParentId($parentId)
    {
        $this->setData(MenuItemsInterface::PARENT_ID, $parentId);
        return $this;
    }

    /**
     * Set menu id
     *
     * @param int $menuId
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setMenuId($menuId)
    {
        $this->setData(MenuItemsInterface::MENU_ID, $menuId);
        return $this;
    }

    /**
     * Set Object Id
     *
     * @param int $objectId
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setObjectId($objectId)
    {
        $this->setData(MenuItemsInterface::OBJECT_ID, $objectId);
        return $this;
    }

    /**
     * Set Creation Time
     *
     * @param string $created
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCreationTime($created)
    {
        $this->setData(MenuItemsInterface::CREATED, $created);
        return $this;
    }

    /**
     * Set update time
     *
     * @param string $updated
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setUpdateTime($updated)
    {
        $this->setData(MenuItemsInterface::UPDATED, $updated);
        return $this;
    }

    /**
     * Set Item Link
     *
     * @param string $link
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemLink($link)
    {
        $this->setData(MenuItemsInterface::LINK, $link);
        return $this;
    }

    /**
     * Set Item Columns
     *
     * @param string $columns
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemColumns($columns)
    {
        $this->setData(MenuItemsInterface::COLUMNS, $columns);
        return $this;
    }

    /**
     * Set Item Font Icon
     *
     * @param string $icon
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemFontIcon($icon)
    {
        $this->setData(MenuItemsInterface::ICON, $icon);
        return $this;
    }

    /**
     * Set Item Class
     *
     * @param string $class
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemClass($class)
    {
        $this->setData(MenuItemsInterface::ITEM_CLASS, $class);
        return $this;
    }

    /**
     * Set Animation option
     *
     * @param string $animation
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setAnimationOption($animation)
    {
        $this->setData(MenuItemsInterface::ANIMATION, $animation);
        return $this;
    }

    /**
     * Set Category Vertical Menu
     *
     * @param string $verticalMenu
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryVerticalMenu($verticalMenu)
    {
        $this->setData(MenuItemsInterface::VERTICAL_MENU, $verticalMenu);
        return $this;
    }

    /**
     * Set Category Vertical Menu background
     *
     * @param string $verticalMenuBg
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryVerticalMenuBg($verticalMenuBg)
    {
        $this->setData(MenuItemsInterface::VERTICAL_MENU_BG, $verticalMenuBg);
        return $this;
    }

    /**
     * Set Category display
     *
     * @param string $display
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryDisplay($display)
    {
        $this->setData(MenuItemsInterface::DISPLAY, $display);
        return $this;
    }

    /**
     * Set Category Column
     *
     * @param \Magedelight\Megamenu\Api\Data\MenuItemsInterface[] $menuColumns
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryColumns($menuColumns)
    {
        $this->setData(MenuItemsInterface::CAT_COLUMNS, $menuColumns);
        return $this;
    }

    /**
     * Set Childrens
     *
     * @param \Magedelight\Megamenu\Api\Data\MenuItemsInterface[] $childrens
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setChildrens($childrens)
    {
        $this->setData(MenuItemsInterface::CHILDRENS, $childrens);
        return $this;
    }
}
