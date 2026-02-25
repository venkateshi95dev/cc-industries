<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Block\Adminhtml\Menu\Edit;

use Magedelight\Megamenu\Helper\Data;
use Magedelight\Megamenu\Model\MenuFactory;
use Magedelight\Megamenu\Model\MenuItemsFactory;
use Magedelight\Megamenu\Model\Source\AnimationType;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Context;
use Magento\Catalog\Model\Category;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class MenuItems extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magedelight_Megamenu::menu/menuitems.phtml';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var int
     */
    public static $countDepth = 0;

    /**
     * @var bool
     */
    public static $rootUl = false;

    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var MenuItemsFactory
     */
    protected $menuItemsFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var AnimationType
     */
    protected $animationOptions;

    /**
     * @var Page
     */
    protected $pageModel;

    /**
     * @var Category
     */
    protected $categoryModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MenuItems constructor.
     *
     * @param Context $context
     * @param Http $request
     * @param BlockFactory $blockFactory
     * @param MenuFactory $menuFactory
     * @param MenuItemsFactory $menuItemsFactory
     * @param Data $helper
     * @param AnimationType $animationOptions
     * @param Page $pageModel
     * @param Category $categoryModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Http $request,
        BlockFactory $blockFactory,
        MenuFactory $menuFactory,
        MenuItemsFactory $menuItemsFactory,
        Data $helper,
        AnimationType $animationOptions,
        Page $pageModel,
        Category $categoryModel,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_context = $context;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $request;
        $this->setFormAction($this->urlBuilder->getUrl('*/*/save'));
        $this->blockFactory = $blockFactory;
        $this->menuFactory = $menuFactory;
        $this->menuItemsFactory = $menuItemsFactory;
        $this->helper = $helper;
        $this->animationOptions = $animationOptions;
        $this->pageModel = $pageModel;
        $this->categoryModel = $categoryModel;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve category by category id
     *
     * @param string
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentMenuId()
    {
        return $this->request->getParam('menu_id');
    }

    /**
     * Retrieve current menu object
     *
     * @return \Magedelight\Megamenu\Model\Menu
     */
    public function getCurrentMenu()
    {
        return $this->getMenuFromMenuId();
    }

    /**
     * Retrieve current menu object
     *
     * @return \Magedelight\Megamenu\Model\Menu
     */
    public function getMenuFromMenuId()
    {
        $menu = $this->menuFactory->create();
        $menu->load($this->getCurrentMenuId());
        return $menu;
    }

    /**
     * Show Store Html
     */
    public function getStoreHtml()
    {
        $storeHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $storeHtml->setMenuId($this->getCurrentMenuId());
        $storeHtml->setMenu($this->getCurrentMenu());
        $storeHtml->setContext($this->_context);
        $storeHtml->setTemplate('Magedelight_Megamenu::menu/stores.phtml');
        return $storeHtml->toHtml();
    }

    /**
     * Show Customer Group Html
     */
    public function getCustomerGroupHtml()
    {
        $customerHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $customerHtml->setMenuId($this->getCurrentMenuId());
        $customerHtml->setMenu($this->getCurrentMenu());
        $customerHtml->setCustomerGroups($this->helper->getCustomerGroups());
        $customerHtml->setTemplate('Magedelight_Megamenu::menu/customerGroup.phtml');
        return $customerHtml->toHtml();
    }

    /**
     * Retrieve Pages for Selected Store
     *
     * @return array
     */
    public function getStoreSpecificPages()
    {
        $pages = [];
        $menu = $this->getCurrentMenu();
        $storeId = $menu->getStoreId();

        $pagesModel = $this->pageModel->getCollection()
            ->addFieldToFilter('is_active', 1);
        foreach ($pagesModel as $singlePage) {
            $pageAvailable = true;
            foreach ($storeId as $singleStore) {
                $pageId = $this->pageModel->checkIdentifier($singlePage->getIdentifier(), $singleStore);
                if (!$pageId) {
                    $pageAvailable = false;
                    break;
                }
            }
            if ($pageAvailable) {
                $pages[] = $singlePage;
            }
        }
        return $pages;
    }

    /**
     * Show Pages for side panel
     */
    public function getPages()
    {
        $pages = $this->getStoreSpecificPages();
        $pageHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $pageHtml->setPages($this->getStoreSpecificPages());
        $pageHtml->setTemplate('Magedelight_Megamenu::menu/pages.phtml');
        return $pageHtml->toHtml();
    }

    /**
     * Retrieve backend menu tree
     *
     * @param int $menuId
     * @return string
     */
    public function getBackendMenuTree($menuId)
    {
        $menuItems = $this->menuItemsFactory->create()
            ->getCollection()
            ->addFieldToFilter('menu_id', $menuId)
            ->addFieldToFilter('item_parent_id', 0)
            ->setOrder('sort_order', 'ASC');
        return $this->genereateBackendTree($menuItems, $menuItemId = 0);
    }

    /**
     * Generate backend tree for Menu Items
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $items
     * @param int $menuItemId
     * @return string
     */
    public function genereateBackendTree($items, $menuItemId)
    {
        if (self::$rootUl) {
            $itemOutput = "<ol class='dd-list'>";
        } else {
            $itemOutput = '<ol class="dd-list mainroot" data-parentId="0">';
            self::$rootUl = true;
        }

        foreach ($items as $item) {
            $menuItemId++;
            $currentItem = $this->menuItemsFactory->create()->load($item->getItemId());
            $menu = $this->menuFactory->create()->load($item->getMenuId());
            $currentItemType = $currentItem->getItemType();
            $itemName = $this->getItemName($currentItem->getItemId(), $currentItem->getObjectId(), $currentItemType);
            $itemText = $this->getItemText($currentItem, $menuItemId, $menu->getMenuDesignType());
            $categoryDisplay = $currentItem->getCategoryDisplay();
            $productDisplay = $currentItem->getProductDisplay();
            $itemColumns = '';
            if ($currentItem->getItemColumns()) {
                $itemColumns = $this->getSavedItemsColumns($currentItem);
            }

            $itemOutput .= '<li class="dd-item col-m-12" data-id="' . $menuItemId . '"
                data-name="' . $itemName . '" data-type="' . $currentItem->getItemType() . '"
                data-objectid="' . $currentItem->getObjectId() . '" data-link="' . $currentItem->getItemLink() . '"
                data-verticalmenu="' . $currentItem->getCategoryVerticalMenu() . '"
                data-verticalmenubg="' . $currentItem->getCategoryVerticalMenuBg() . '"
                font-icon="' . $currentItem->getItemFontIcon() . '"
                animation-field="' . $currentItem->getAnimationOption() . '"
                item-class="' . $currentItem->getItemClass() . '"
                vertical-cat-sortby="' . $currentItem->getVerticalCatSortby() . '"
                vertical-cat-sortorder="' . $currentItem->getVerticalCatSortorder() . '"
                vertical-cat-level="' . $currentItem->getVerticalCatLevel() . '"
                data-cat="' . $categoryDisplay . '" data-product-display="' . $currentItem->getProductDisplay() . '"
                data-open-in-new-tab="' . $currentItem->getOpenInNewTab() . '">
                <button class="cf removebtn btn right" href="#" type="button">Remove</button>
                <a class="right collapse linktoggle">Collapse</a>
                <a class="right expand linktoggle">Expand</a>
                <div class="dd-handle">' . $itemName . "
                    <span class='right'>(" . $this->helper->getMenuName($currentItem->getItemType()) . ")</span>" . '
                </div>
                <div class="item-information col-m-12">' . $itemText . $itemColumns . '<div class="cf"></div>
                </div>';
            if ($this->hasItemChildren($item->getItemId())) {
                $childrenItems = $this->menuItemsFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('item_parent_id', $item->getItemId())
                    ->setOrder('sort_order', 'ASC');
                $itemOutput .= $this->genereateBackendTree($childrenItems, $menuItemId);
            }
            $itemOutput .= '</li>';
            $itemOutput .= '</li>';
        }
        return $itemOutput . '</ol>';
    }

    /**
     * Get Saved Items Columns
     *
     * @param \Magedelight\Megamenu\Api\Data\MegamenuInterface $currentItem
     * @return string
     */
    public function getSavedItemsColumns($currentItem)
    {
        $itemColumns = json_decode($currentItem->getItemColumns());
        $selectOption = '';
        if (count($itemColumns)) {
            $itemColumnsCount = count($itemColumns);
            $selectOption = '<div class="margin-top-20 custColumnsBlock col-m-12">
                <div class="col-m-6">
                    <h4>' . __("Menu Columns") . '</h4>
                    ' . $this->columnsSelect($itemColumnsCount) . '
                </div>
                <div class="col-m-6">
                    <h4>' . __("Animation Fields") . '</h4>
                    ' . $this->animationSelect($currentItem) . '
                </div>
                <div class="col-m-12"><div class="menu-column-block-wrapper">';

            $colCount = 1;
            foreach ($itemColumns as $rowItems) {
                $selectOption = $this->getRowItems($selectOption, $rowItems, $itemColumnsCount, $colCount);
                $colCount++;
            }
            $selectOption .= '</div></div></div>';
        }
        return $selectOption;
    }

    /**
     * Get Row Items
     *
     * @param string $selectOption
     * @param \Magento\Framework\DataObject $rowItems
     * @param int $itemColumnsCount
     * @param int $colCount
     * @return string
     */
    private function getRowItems($selectOption, $rowItems, $itemColumnsCount, $colCount)
    {
        $selectOption .= '<div id="menuColumn-' . $colCount . '" class="menu-column-block column' .
            $itemColumnsCount . '">';
        $selectOption .= '<div class="menu-column-block-inner">';
        if (isset($rowItems->item_rows)) {
            $rowItemsData = $rowItems->item_rows;
            $rowCount = 1;
            foreach ($rowItemsData as $itemColumn) {
                $selectOption = $this->rowItemData($selectOption, $itemColumn, $rowCount);
                $rowCount++;
            }
        } else {
            $itemColumn = $rowItems;
            $selectOption = $this->rowItemData($selectOption, $itemColumn);
        }
        $selectOption .= '</div>';
        $selectOption .= '<a class="add-more-menu">' . __('Add More') . '</a>';
        $selectOption .= '</div>';

        return $selectOption;
    }

    /**
     * Row Item Data
     *
     * @param string $selectOption
     * @param \Magento\Framework\DataObject $itemColumn
     * @param int $rowCount
     * @return string
     */
    private function rowItemData($selectOption, $itemColumn, $rowCount = null)
    {
        $selectOption .= '<div class="menu-block-row">';
        if ($rowCount > 1) {
            $selectOption .= '<span class="remove-row"></span>';
        }
        $staticBlock = trim(preg_replace(
            '/\s\s+/',
            ' ',
            $this->getStaticBlocks($itemColumn->type, $itemColumn->value)
        ));
        $selected = $itemColumn->showtitle === '1' ? 'checked' : '';
        $style = $itemColumn->type !== 'category' ? 'style="display: none;"' : '';

        $selectOption .= "
            $staticBlock
            <p>" . __('Show Title') . " <input $selected type='checkbox' class='showtitle'></p>
            <p class='cat-sort-by-block' $style>
                " . __('Sort By') . " <select class='cat-sort-by admin__control-select'>" .
            $this->generateSelectOptions(
                ['position' => 'Position', 'name' => 'Name'],
                $itemColumn->catSortBy ?? ''
            ) .
            "</select>
            </p>
            <p class='cat-sort-order-block' $style>
                " . __('Sort Order') . " <select class='cat-sort-order admin__control-select'>" .
            $this->generateSelectOptions(
                ['asc' => 'ASC', 'desc' => 'DESC'],
                $itemColumn->catSortOrder ?? ''
            ) .
            "</select>
            </p>
            <p class='cat-depth-block' " . $style . ">" .
            __('Category depth') . " <select class='cat-depth admin__control-select'>" .
            $this->generateCatDepth($itemColumn) .
            "</select>
            </p>
        </div>";

        return $selectOption;
    }

    /**
     * Generate Cat Depth
     *
     * @param \Magento\Framework\DataObject $itemColumn
     * @return string
     */
    private function generateCatDepth($itemColumn)
    {
        $html = '';
        for ($i = 1; $i <= 3; $i++) {
            $sel = "";
            if (isset($itemColumn->categoryLevel)) {
                if ($itemColumn->categoryLevel == $i) {
                    $sel = "selected";
                }
            }
            $html .= "<option value='" . $i . "' " . $sel . ">" . $i . "</option>";
        }

        return $html;
    }

    /**
     * Generate Select Options
     *
     * @param array $options
     * @param string $selectedValue
     * @return string
     */
    private function generateSelectOptions($options, $selectedValue)
    {
        $html = '';
        foreach ($options as $value => $label) {
            $selected = $value === $selectedValue ? 'selected' : '';
            $html .= "<option value='$value' $selected>$label</option>";
        }
        return $html;
    }

    /**
     * Has Item Children
     *
     * @param int $itemId
     * @return bool
     */
    public function hasItemChildren($itemId)
    {
        $menuItems = $this->menuItemsFactory->create()->getCollection()
            ->addFieldToFilter('item_parent_id', $itemId)
            ->Count();
        if ($menuItems) {
            return true;
        }
        return false;
    }

    /**
     * Get Item Text
     *
     * @param \Magento\Framework\DataObject $currentItem
     * @param int $menuItemId
     * @param string $menuDesignType
     * @return string
     * @throws LocalizedException
     */
    public function getItemText($currentItem, $menuItemId, $menuDesignType)
    {
        $randLabel = rand(1, 100000);
        $name = $currentItem->getItemName();
        $class = $currentItem->getItemClass();
        $url = $currentItem->getItemLink();
        $fonticon = $currentItem->getItemFontIcon();
        $category_display = $currentItem->getCategoryDisplay();
        $category_vertical_menu = $currentItem->getCategoryVerticalMenu();
        $product_display = $currentItem->getProductDisplay();
        $open_in_new_tab = $currentItem->getOpenInNewTab();

        $category_checkbox = (int)$category_display === 1 ? "checked" : "";
        $category_vertical_menu_checkbox = (int)$category_vertical_menu === 1 ? "checked" : "";
        $categoryColumns = $currentItem->getCategoryColumns() ? json_decode($currentItem->getCategoryColumns()) : [];
        $product_display_checkbox = (int)$product_display === 1 ? "checked" : "";
        $open_in_new_tab_checkbox = (int)$open_in_new_tab === 1 ? "checked" : "";
        $menuIcon = $currentItem->getMenuIcon();

        $type = $currentItem->getItemType();
        $inputTextClass = 'input-text admin__control-text linkclass';
        $checkExternal = $type == 'pages' ? 'mcustom_link_text' : 'external_link';

        if ($type == 'link' || $type == 'pages' || $type == 'megamenu') {
            return '
                <div class="col-m-3">
                    <h4>' . __("Label") . '</h4>
                    <input class="' . $inputTextClass . ' required-entry linktypelabel" type="text"
                    name="menu_data[' . $menuItemId . '][' . $checkExternal . ']"
                    value="' . $name . '">
                </div>
                <div class="col-m-3">
                    <h4>' . __("Url") . '</h4>
                    <input class="' . $inputTextClass . ' linktypeurl" type="text"
                    name="menu_data[' . $menuItemId . '][custom_link_url]"
                    value="' . $url . '" ' . ($type == 'link' ? '' : '') . '>
                    ' . ($type == 'pages' ? '<div class="admin__field-note">
                    <span>' . __("Leave blank to link to the home page URL.") . '</span></div>' : '') . '
                </div>
                <div class="col-m-3">
                    <h4>' . __("Class") . '</h4>
                    <input class="' . $inputTextClass . ' linktypeclass" type="text"
                    name="menu_data[' . $menuItemId . '][item_class]" value="' . $class . '">
                </div>
                <div class="col-m-3">
                    <h4>' . __("Preceding Label Content") . '</h4>
                    <input class="' . $inputTextClass . ' linktypefont" type="text"
                    name="menu_data[' . $menuItemId . '][fonticon]" value="' . $fonticon . '" >
                    <div class="admin__field-note">
                        <span>' . __("This Content will be added before Menu Label") . '.</span>
                    </div>
                </div>
                <div class="col-m-6">
                    <input class="input-text admin__control-checkbox checkbox linkclass open_in_new_tab_checkbox"
                    type="checkbox"
                    name="menu_data[' . $menuItemId . '][open_in_new_tab]" ' . $open_in_new_tab_checkbox . '>
                    <label for="menu_data_' . $menuItemId . '_open_in_new_tab" class="admin__field-label">
                        ' . __("Open In New Tab") . '
                    </label>
                </div>' . $this->addMenuIcon($menuItemId, $menuIcon, $menuDesignType);
        } else {
            $categoryHtml = '';
            $categoryHtml .= '
                <div class="col-m-3">
                    <h4>' . __("Class") . '</h4>
                    <input class="input-text admin__control-text linkclass linktypeclass" type="text"
                    name="menu_data[' . $menuItemId . '][item_class]" value="' . $class . '">
                </div>
                <div class="col-m-3">
                    <h4>' . __("Preceding Label Content") . '</h4>
                    <input class="input-text admin__control-text linktypefont linkclass" type="text"
                    name="menu_data[' . $menuItemId . '][fonticon]" value="' . $fonticon . '">
                    <div class="admin__field-note">
                        <span>' . __("This Content will be added before Menu Label.") . '</span>
                    </div>
                </div>' . $this->addMenuIcon($menuItemId, $menuIcon, $menuDesignType);
            if ($this->getCurrentMenu()->getMenuType() === '2') {
                if ($currentItem->getItemType() === 'category') {
                    $categoryHtml .= '<div class="col-m-3">
                        <h4>' . __("Animation Fields") . '</h4>
                        ' . $this->animationSelect($currentItem) . '
                        </div>
                        
                        <div class="cf"></div>';
                }

                if ($currentItem->getItemType() == 'category') {
                    $selectedHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
                    $selectedData = [
                        'menu_item_id' => $menuItemId,
                        'category_checkBox' => $category_checkbox,
                        'category_vertical_menu_checkbox' => $category_vertical_menu_checkbox,
                        'category_vertical_bg_color' => $currentItem->getCategoryVerticalMenuBg(),
                        'current_item' => $currentItem,
                        'current_menu_design_type' => $this->getCurrentMenu()->getMenuDesignType(),
                        'product_display_checkbox' => $product_display_checkbox,
                        'open_in_new_tab_checkbox' => $open_in_new_tab_checkbox
                    ];
                    $selectedHtml->setData($selectedData);
                    $selectedHtml->setTemplate('Magedelight_Megamenu::menu/items/menuColumnBlockWrapper.phtml');
                    $categoryHtml .= trim(preg_replace('/\s\s+/', ' ', $selectedHtml->toHtml()));
                }
                $blockname = '';
                if (!empty($categoryColumns)) {
                    foreach ($categoryColumns as $categoryColumn) {
                        $hiddenClass = 'hidden';
                        $selected = '';
                        if ($categoryColumn->enable) {
                            $hiddenClass = '';
                            $selected = $this->getSelectedTitle($categoryColumn);
                            $blockname = $categoryColumn->value;
                        }
                        $categoryHtml .= '<div class="col-m-3">
                            <h4>' . __('%1 Block', ucfirst($categoryColumn->type)) . '</h4>' .
                            $this->yesNoDropdown(
                                'menu_data[' . $menuItemId . '][' . $categoryColumn->type . ']',
                                $randLabel,
                                $categoryColumn->type,
                                $categoryColumn->enable
                            ) .
                            '<div class="header_staticblock_select categorylink_category_select ' . $hiddenClass . '"
                            style="margin-top:10px;">
                            <h4 style="margin-top:0;">' . __("Select Static Block") . '</h4>' .
                            $this->getStaticBlocks('block', $blockname, true) . '
                                <p>' . __("Show Title") . '
                                    <input ' . $selected . ' type="checkbox" class="showtitle">
                                </p>
                            </div>
                        </div>';
                    }
                }
            }
            return $categoryHtml;
        }
    }

    /**
     * Get Selected Title
     *
     * @param \Magento\Framework\DataObject $categoryColumn
     * @return string
     */
    private function getSelectedTitle($categoryColumn)
    {
        $selected = '';
        if ($categoryColumn->showtitle === '1') {
            $selected = 'checked';
        }
        return $selected;
    }

    /**
     * Add Menu Icon
     *
     * @param int $menuItemId
     * @param string $menuIcon
     * @param string $menuDesignType
     * @return void
     */
    private function addMenuIcon($menuItemId, $menuIcon, $menuDesignType)
    {
        $html = $iconImage = "";
        $filepath = $this->storeManager->getStore()->getBaseUrl() . 'media/menu_icon/' . $menuIcon;
        if (!empty($menuIcon)) {
            $iconImage = '<div class="icon_image">
                            <img alt="icon_image" src="' . $filepath . '"width="50" height="50">
                            <input type="checkbox" name="menu_icon_delete[' . $menuItemId . ']"
                            alt="Delete Image" title="Delete Image">
                        </div>';
        }

        if ($menuDesignType == "horizontal" || $menuDesignType == "horizontal-vertical") {
            $html = '<div class="col-m-3">
                    <h4>' . __("Menu Icon") . '</h4>
                    ' . $iconImage . '
                    <input class="input-text" type="file" name="menu_icon[' .
                    $menuItemId . ']" value="' . $menuIcon . '">
                    <input class="input-text" type="hidden"
                    name="menu_icon_edit[' . $menuItemId . ']" value="' . $menuIcon . '">
                </div>';
        }

        return $html;
    }

    /**
     * Retrieve animations to select
     *
     * @param \Magedelight\Megamenu\Api\Data\MenuItemsInterface $currentItem
     * @return string
     */
    public function animationSelect($currentItem = null)
    {
        $animationOption = '';
        if ($currentItem) {
            $animationOption = $currentItem->getAnimationOption();
        }
        $options = $this->animationOptions->toOptionArray();
        /**
         * @var Magento\Framework\View\Element\Template
         */
        $selectedHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $selectedHtml->setAnimationOption($animationOption);
        $selectedHtml->setOptions($options);
        $selectedHtml->setTemplate('Magedelight_Megamenu::menu/animationFields.phtml');
        $contents = $selectedHtml->toHtml();
        return trim(preg_replace('/\s\s+/', ' ', $contents));
    }

    /**
     * Retrieve columns number to select
     *
     * @param int $selected
     * @return string
     */
    public function columnsSelect($selected = '')
    {
        $selectedOptions = [];
        for ($i = 1; $i <= 5; $i++) {
            $selectedOptions[$i] = '';
            if ($selected == $i) {
                $selectedOptions[$i] = 'selected';
            }
        }
        /**
         * @var Magento\Framework\View\Element\Template
         */
        $selectedHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $selectedHtml->setSelectedOptions($selectedOptions);
        $selectedHtml->setTemplate('Magedelight_Megamenu::menu/columnSelect.phtml');
        $contents = $selectedHtml->toHtml();
        return trim(preg_replace('/\s\s+/', ' ', $contents));
    }

    /**
     * Retrieve menu items name
     *
     * @param int $itemId
     * @param int $objectId
     * @param string $type
     * @return string
     */
    public function getItemName($itemId, $objectId, $type)
    {
        if ($type == 'category') {
            $category = $this->categoryModel->load($objectId);
            $name = $category->getName();
        } else {
            $link = $this->menuItemsFactory->create()->load($itemId);
            $name = $link->getItemName();
        }
        return $name;
    }

    /**
     * Get Static Blocks
     *
     * @param string $selectedGroup
     * @param string $selectedValue
     * @param boolean $onlyStaticBlock
     * @return string
     */
    public function getStaticBlocks($selectedGroup = '', $selectedValue = '', $onlyStaticBlock = false)
    {
        $blockSelected = '';
        $menuSelected = '';
        $categorySelected = '';
        $menus = [];
        if (!empty($selectedGroup)) {
            if ($selectedGroup == 'block') {
                $blockSelected = 'selected';
            } elseif ($selectedGroup == 'menu') {
                $menuSelected = 'selected';
            } elseif ($selectedGroup == 'category') {
                $categorySelected = 'selected';
            }
        }
        $menu = $this->getCurrentMenu();
        $storeId = $menu->getStoreId();

        if (in_array(0, $storeId)) {
            $blocks = $this->blockFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addStoreFilter(0);

            $menus = $this->menuFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('menu_type', 1)
                ->addStoreFilter(0);
        } else {
            $blocksTemp = $this->blockFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 1);
            foreach ($blocksTemp as $singleBlock) {
                $blockAvailable = true;
                foreach ($storeId as $singleStore) {
                    $block = $this->blockFactory->create();
                    $block->setStoreId($singleStore)->load($singleBlock->getBlockId());
                    if (!$block->getBlockId()) {
                        $blockAvailable = false;
                        break;
                    }
                }
                if ($blockAvailable) {
                    $blocks[] = $singleBlock;
                }
            }

            $menusTemp = $this->menuFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('menu_type', 1);
            foreach ($menusTemp as $singleMenu) {
                $menuAvailable = true;
                foreach ($storeId as $singleStore) {
                    $menu = $this->menuFactory->create()->load($singleMenu->getMenuId());
                    if (!in_array($singleStore, $menu->getStoreId())) {
                        $menuAvailable = false;
                        break;
                    }
                }
                if ($menuAvailable) {
                    $menus[] = $singleMenu;
                }
            }
        }

        /**
         * @var Magento\Framework\View\Element\Template
         */
        $blockHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $blockHtml->setBlocks($blocks);
        $blockHtml->setSelectedValue($selectedValue);
        $blockHtml->setBlockSelected($blockSelected);
        $blockHtml->setMenuSelected($menuSelected);
        $blockHtml->setCategorySelected($categorySelected);
        $blockHtml->setSelectedGroup($selectedGroup);
        $blockHtml->setMenus($menus);
        $blockHtml->setOnlyStaticBlock($onlyStaticBlock);
        $blockHtml->setCategoriesData($this->getCategoriesDropdown());
        $blockHtml->setTemplate('Magedelight_Megamenu::menu/staticBlocks.phtml');
        $contents = $blockHtml->toHtml();
        return $contents;
    }

    /**
     * Get Categories Dropdown
     *
     * @return array
     */
    public function getCategoriesDropdown()
    {
        $categoryCollection = $this->categoryModel;
        $categoriesArray = $categoryCollection
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', ['eq' => '1'])
            ->load()
            ->toArray();

        foreach ($categoriesArray as $categoryId => $category) {
            if (isset($category['name'])) {
                $categories[] = [
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $categoryId
                ];
            }
        }
        return $categories;
    }

    /**
     * Yes No Dropdown
     *
     * @param string $name
     * @param string $randLabel
     * @param int $position
     * @param string $selected
     * @return string
     * @throws LocalizedException
     */
    public function yesNoDropdown($name, $randLabel, $position, $selected = '')
    {
        /**
         * @var Magento\Framework\View\Element\Template
         */
        $selectedHtml = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $selectedHtml->setSelectedOptions($selected);
        $selectedHtml->setName($name);
        $selectedHtml->setRandLabel($randLabel);
        $selectedHtml->setPosition($position);
        $selectedHtml->setTemplate('Magedelight_Megamenu::menu/yesNo.phtml');
        $contents = $selectedHtml->toHtml();
        return trim(preg_replace('/\s\s+/', ' ', $contents));
    }

    /**
     * Get Menu Labels
     *
     * @return string
     */
    public function getMenuLabels()
    {
        return json_encode($this->helper->menuTypes());
    }
}
