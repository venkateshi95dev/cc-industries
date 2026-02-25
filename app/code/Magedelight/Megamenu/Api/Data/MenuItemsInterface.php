<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Api\Data;

interface MenuItemsInterface
{
    public const ID = 'item_id';
    public const TYPE = 'item_type';
    public const NAME = 'item_name';
    public const SORT = 'sort_order';
    public const PARENT_ID = 'item_parent_id';
    public const MENU_ID = 'menu_id';
    public const OBJECT_ID = 'object_id';
    public const CREATED = 'creation_time';
    public const UPDATED = 'update_time';
    public const LINK = 'item_link';
    public const COLUMNS = 'item_columns';
    public const ICON = 'item_font_icon';
    public const ITEM_CLASS = 'item_class';
    public const ANIMATION = 'animation_option';
    public const VERTICAL_MENU = 'category_vertical_menu';
    public const VERTICAL_MENU_BG = 'category_vertical_menu_bg';
    public const DISPLAY = 'category_display';
    public const CAT_COLUMNS = 'category_columns';
    public const CHILDRENS = 'childrens';

    public const MENU_TABLE = 'megamenu_menus';
    public const MENU_ITEMS_TABLE = 'megamenu_menu_items';

    /**
     * Get Item Id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Get Item Type
     *
     * @return string
     */
    public function getItemType();

    /**
     * Get Item Name
     *
     * @return string
     */
    public function getItemName();

    /**
     * Get Sort Order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get Item Parent Id
     *
     * @return int
     */
    public function getItemParentId();

    /**
     * Get Menu Id
     *
     * @return int
     */
    public function getMenuId();

    /**
     * Get Object Id
     *
     * @return int
     */
    public function getObjectId();

    /**
     * Get Creation Time
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
     * Get Item Link
     *
     * @return string
     */
    public function getItemLink();

    /**
     * Get Item Column
     *
     * @return string
     */
    public function getItemColumns();

    /**
     * Get Item Font Icon
     *
     * @return string
     */
    public function getItemFontIcon();

    /**
     * Get Item Class
     *
     * @return string
     */
    public function getItemClass();

    /**
     * Get Animation Option
     *
     * @return string
     */
    public function getAnimationOption();

    /**
     * Get Category Vertical menu
     *
     * @return string
     */
    public function getCategoryVerticalMenu();

    /**
     * Get Category Vertical Menu Bg
     *
     * @return string
     */
    public function getCategoryVerticalMenuBg();

    /**
     * Get Category Display
     *
     * @return string
     */
    public function getCategoryDisplay();

    /**
     * Get Category Columns
     *
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface[]
     */
    public function getCategoryColumns();

    /**
     * Get Childrens
     *
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface[]
     */
    public function getChildrens();

    /**
     * Set Item Id
     *
     * @param int $id
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemId($id);

    /**
     * Set Item Type
     *
     * @param string $type
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemType($type);

    /**
     * Set Item Name
     *
     * @param string $name
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemName($name);

    /**
     * Set Sort Order
     *
     * @param int $sort
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setSortOrder($sort);

    /**
     * Set Item Parent Id
     *
     * @param int $parentId
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemParentId($parentId);

    /**
     * Set Menu Id
     *
     * @param int $menuId
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setMenuId($menuId);

    /**
     * Set Object Id
     *
     * @param int $objectId
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setObjectId($objectId);

    /**
     * Set Creation Time
     *
     * @param string $created
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCreationTime($created);

    /**
     * Set Update time
     *
     * @param string $updated
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setUpdateTime($updated);

    /**
     * Set Item Link
     *
     * @param string $link
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemLink($link);

    /**
     * Set Item Columns
     *
     * @param string $columns
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemColumns($columns);

    /**
     * Set Item Font Icon
     *
     * @param string $icon
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemFontIcon($icon);

    /**
     * Set Item Class
     *
     * @param string $class
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setItemClass($class);

    /**
     * Set Animation Option
     *
     * @param string $animation
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setAnimationOption($animation);

    /**
     * Set Category Vertical Menu
     *
     * @param string $verticalMenu
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryVerticalMenu($verticalMenu);

    /**
     * Set Category Vertical Menu Bg
     *
     * @param string $verticalMenuBg
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryVerticalMenuBg($verticalMenuBg);

    /**
     * Set Category Display
     *
     * @param string $display
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryDisplay($display);

    /**
     * Set Category Columns
     *
     * @param \Magedelight\Megamenu\Api\Data\MenuItemsInterface[] $menuColumns
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setCategoryColumns($menuColumns);

    /**
     * Set Childrens
     *
     * @param \Magedelight\Megamenu\Api\Data\MenuItemsInterface[] $childrens
     * @return \Magedelight\Megamenu\Api\Data\MenuItemsInterface
     */
    public function setChildrens($childrens);
}
