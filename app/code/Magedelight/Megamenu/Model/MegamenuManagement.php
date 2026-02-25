<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Model;

use Magedelight\Megamenu\Api\MegamenuManagementInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use Magedelight\Megamenu\Helper\Data;
use Magento\Cms\Model\BlockFactory;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magedelight\Megamenu\Helper\Category as CategoryHelper;
use Magedelight\Megamenu\Model\ResourceModel\Menu\CollectionFactory as MenuCollectionFactory;
use Magento\Framework\App\ResourceConnection;

class MegamenuManagement implements MegamenuManagementInterface
{
    /**
     * @var integer
     */
    protected $primaryMenuId = 0;

    /**
     * Group Id
     *
     * @var integer
     */
    protected $group = 0;

    /**
     * @var \Magedelight\Megamenu\Model\Menu
     */
    protected $primaryMenu;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Data
     */
    protected $menuHelper;

    /**
     * @var MenuFactory
     */
    protected $menuFactory;

    /**
     * @var MenuItemsFactory
     */
    protected $menuItemsFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var MenuCollectionFactory
     */
    protected $menuCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CategoryListInterface
     */
    protected $categoryList;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * MegamenuManagement constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param Data $menuHelper
     * @param MenuFactory $menuFactory
     * @param MenuItemsFactory $menuItemsFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param CategoryHelper $categoryHelper
     * @param BlockFactory $blockFactory
     * @param MenuCollectionFactory $menuCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryListInterface $categoryList
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        Data $menuHelper,
        MenuFactory $menuFactory,
        MenuItemsFactory $menuItemsFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        CategoryHelper $categoryHelper,
        BlockFactory $blockFactory,
        MenuCollectionFactory $menuCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryListInterface $categoryList,
        SortOrderBuilder $sortOrderBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->menuHelper = $menuHelper;
        $this->menuFactory = $menuFactory;
        $this->menuItemsFactory = $menuItemsFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->categoryHelper = $categoryHelper;
        $this->blockFactory = $blockFactory;
        $this->menuCollectionFactory = $menuCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryList = $categoryList;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get medea Url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl().'media/';
    }

    /**
     * Get Menu Data
     *
     * @param int $customerId
     * @return \Magedelight\Megamenu\Api\MegamenuInterface|DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMenuData($customerId = null)
    {
        if ($customerId) {
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $this->group = $customer->getGroupId();
        }
        $result = $this->dataObjectFactory->create();
        $result->setData('menu', $this->getMegamenu());
        return $result;
    }

    /**
     * Get Menu Data by id
     *
     * @param int $menuId
     * @param int $customerId
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMenuDataById($menuId, $customerId = null)
    {
        if ($customerId) {
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $this->group = $customer->getGroupId();
        }
        $this->primaryMenuId = $menuId;
        $this->primaryMenu = $this->loadMenuById($this->primaryMenuId);
        $result = $this->dataObjectFactory->create();
        $result->setData('menu', $this->getMegamenu(true));
        return $result;
    }

    /**
     * Load Menu Items
     *
     * @param int $parentId
     * @param int $sortOrder
     * @param int $id
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function loadMenuItems($parentId = null, $sortOrder = null, $id = null)
    {
        if ($id == null) {
            $id = $this->primaryMenuId;
        }
        $items = $this->menuItemsFactory->create()->getCollection();
        $items->addFieldToFilter('main_table.menu_id', $id);
        if ($parentId !== null) {
            $items->addFieldToFilter('item_parent_id', $parentId);
        }
        if ($sortOrder !== null) {
            $items->setOrder('sort_order', $sortOrder);
        }
        $tableName = $this->resourceConnection->getTableName('megamenu_menus');
        $items->getSelect()->joinLeft(
            ['join_table' => $tableName],
            'main_table.menu_id = join_table.menu_id',
            ['customer_groups' => 'join_table.customer_groups']
        );
        $items->addFieldToFilter('join_table.customer_groups', ['finset' => $this->group]);

        return $items;
    }

    /**
     * Get Mega Menu
     *
     * @param bool $skip
     * @return Menu
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMegamenu($skip = false)
    {
        if (!$skip) {
            $this->initMegaMenu();
        }
        if ($this->primaryMenu->getData('menu_type') == 1 && $this->primaryMenu->getData('is_active')) {
            $menuItems = $this->loadMenuItems(0);
            $level = 0;
            $this->primaryMenu->setData('menu_items', $this->setNormalMenuItems($menuItems, $level));
        }
        if ($this->primaryMenu->getData('menu_type') == 2 && $this->primaryMenu->getData('is_active')) {
            $this->primaryMenu->setData('menu_items', $this->setMegaMenuItems());
        }
        if ($this->primaryMenu->getData('store_id')) {
            $this->primaryMenu->setData('store_id', implode(',', $this->primaryMenu->getData('store_id')));
        }
        return $this->primaryMenu;
    }

    /**
     * Load menu by id
     *
     * @param int $id
     * @return Menu
     */
    public function loadMenuById($id)
    {
        $menu = $this->menuFactory->create()->load($id);
        return $menu;
    }

    /**
     * Init Mega Menu
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function initMegaMenu()
    {
        $this->primaryMenuId = $this->setPrimaryMenuId();
        $this->primaryMenu = $this->loadMenuById($this->primaryMenuId);
    }

    /**
     * Get Store Id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Set Primary Menu Id
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setPrimaryMenuId()
    {
        $menu_id = $this->menuHelper->getConfig('magedelight/general/primary_menu');
        $menu = $this->loadMenuById($menu_id);
        $customerGroup = $this->group;
        $customerGroupsArray = [];
        if ($menu->getCustomerGroups() !== 0) {
            $customerGroupsArray = explode(',', trim($menu->getCustomerGroups() ?? ''));
            if (!in_array($customerGroup, $customerGroupsArray) || $menu->getIsActive() != 1) {
                $menu_id = '';
            }
        }
        if (empty($menu_id)) {
            $menuCollection = $this->menuFactory->create()->getCollection()
                ->addStoreFilter($this->getStoreId())
                ->addFieldToFilter('is_active', '1')
                ->addFieldToFilter('customer_groups', ['finset' => $customerGroup])
                ->setPageSize(1)
                ->setCurPage(1);
            foreach ($menuCollection as $singleCollection) {
                return $menu_id = $singleCollection->getMenuId();
            }
        }
        return $menu_id;
    }

    /**
     * Set mega menu Items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setMegaMenuItems()
    {
        $items = [];
        $menuItems = $this->loadMenuItems(null, 'ASC');
        $customerGroup = $this->group;
        /** @var \Magedelight\Megamenu\Model\MenuItems $menuItem */
        foreach ($menuItems as $key => $menuItem) {
            $menuItem->setData('item_link', $this->generateMenuUrl($menuItem));
            $menuItem->setData('category_columns', json_decode($menuItem->getData('category_columns') ?? '[]'));
            if ($menuItem->hasData('item_columns')) {
                $menuItem = $this->setItemColumns($menuItem);
            }
            if ($menuItem->getData('item_type') == 'category' && $menuItem->getData('category_display') == "1") {
                $menuItem->setData('childrens', $this->categoryHelper->getCategoryTreeById($menuItem, $customerGroup));
            } else {
                $menuItem->setData('childrens', []);
            }
            if ($menuItem->getData('item_type') == 'category') {
                if (!$this->isAllowPermission($menuItem->getData('object_id'))) {
                    $menuItem->unsetData();
                }
            }
            $items[] = $menuItem->getData();
        }
        return array_filter($items);
    }

    /**
     * Set Item Columns
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $menuItem
     * @return \Magedelight\Megamenu\Model\MenuItems
     */
    private function setItemColumns($menuItem)
    {
        $itemColumns = json_decode($menuItem->getData('item_columns') ?? '[]');
        if ($menuItem->getData('item_type') == 'megamenu') {
            if (!empty($itemColumns)) {
                foreach ($itemColumns as $k => $rowItems) {
                    if (isset($rowItems->item_rows)) {
                        $rowItemsData = $rowItems->item_rows;
                        $itemColumns = $this->checkPermission($rowItemsData, $itemColumns, $k);

                    } else {
                        $column = $rowItems;
                        $itemColumns =  $this->checkPermissionSingleItem($column, $itemColumns, $k);
                    }
                }
            }
        }
        if ($itemColumns) {
            $menuItem->setData('item_columns', array_values($itemColumns));
        } else {
            $menuItem->setData('item_columns', $itemColumns);
        }
        return $menuItem;
    }

    /**
     * Check PermissionSingleItem
     *
     * @param \Magento\Framework\DataObject $column
     * @param \Magento\Framework\DataObject $itemColumns
     * @param int $k
     * @return \Magento\Framework\DataObject
     */
    private function checkPermissionSingleItem($column, $itemColumns, $k)
    {
        if ($column->type == 'category') {
            if (!$this->isAllowPermission($column->value)) {
                unset($itemColumns[$k]);
            }
        }
        return $itemColumns;
    }

    /**
     * CheckPermissioon
     *
     * @param \Magento\Framework\DataObject $rowItemsData
     * @param \Magento\Framework\DataObject $itemColumns
     * @param int $k
     * @return \Magento\Framework\DataObject
     */
    private function checkPermission($rowItemsData, $itemColumns, $k)
    {
        foreach ($rowItemsData as $column) {
            if ($column->type == 'category') {
                if (!$this->isAllowPermission($column->value)) {
                    unset($itemColumns[$k]);
                }
            }
        }
        return $itemColumns;
    }
    /**
     * Has Children Items
     *
     * @param int $parentId
     * @return int
     */
    public function hasChildrenItems($parentId)
    {
        $count = $this->loadMenuItems($parentId)->count();
        return $count;
    }

    /**
     * Set Normal Menu Items
     *
     * @param \Magedelight\Megamenu\Model\MenuItems $menuItems
     * @param int $level
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setNormalMenuItems($menuItems, $level)
    {
        $normalMenu = [];
        /** @var  $menuItem \Magedelight\Megamenu\Model\MenuItems */
        foreach ($menuItems as $menuItem) {
            $exclude = false;
            if ($menuItem->getData('item_type') == 'category') {
                if (!$this->isAllowPermission($menuItem->getData('object_id'))) {
                    $exclude = true;
                }
            }
            if (!$exclude) {
                $menuId = $menuItem->getData('item_id');
                $menuItem->setData('item_link', $this->generateMenuUrl($menuItem));
                $hasChildren = $this->hasChildrenItems($menuId);
                if ($hasChildren) {
                    $menuItems = $this->loadMenuItems($menuId);
                    $menuItem->setData('childrens', $this->setNormalMenuItems($menuItems, $level + 1));
                    $normalMenu[] = $menuItem->getData();
                } else {
                    $menuItem->setData('childrens', []);
                    $normalMenu[] = $menuItem->getData();
                }
            }
        }
        return array_filter($normalMenu);
    }

    /**
     * Generate menu url
     *
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $menuItem
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateMenuUrl($menuItem)
    {
        $linkurl = $menuItem->getData('item_link');
        $url = '';
        if ($menuItem->getData('item_type') == "link" && !empty($linkurl)) {
            return $linkurl;
        }
        if ($menuItem->getData('item_type') == "category") {
            if ($this->getCategoryById($menuItem->getObjectId())) {
                $url = $this->getCategoryById($menuItem->getObjectId())->getUrl();
            }
        }
        if ($menuItem->getData('item_type') == "pages") {
            $url = $this->storeManager->getStore()->getBaseUrl() . $menuItem->getData('item_link');
        }
        return $url;
    }

    /**
     * Generate Menu Item Name
     *
     * @param \Magedelight\Megamenu\Model\MenuItems|\Magedelight\Megamenu\Api\Data\MenuItemsInterface $menuItem
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateMenuItemName($menuItem)
    {
        $name = '';
        if ($menuItem->getItemType() == "category") {
            if ($this->getCategoryById($menuItem->getObjectId())) {
                $name = $this->getCategoryById($menuItem->getObjectId())
                ->getMdMenuTitle() ? $this->getCategoryById($menuItem->getObjectId())
                ->getMdMenuTitle() : $this->getCategoryById($menuItem->getObjectId())->getName();
            }
        } else {
            $name = $menuItem->getItemName();
        }
        return $name;
    }

    /**
     * Is Allow Permission
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAllowPermission($id)
    {
        if (!$this->menuHelper->permissionEnabled()) {
            return true;
        }
        $customerGroup = $this->group;
        $excludeCategoryIds = $this->menuHelper->getExcludeCategoryIds($customerGroup);
        if (in_array($id, $excludeCategoryIds)) {
            return false;
        }
        return true;
    }

    /**
     * Cms Block by id
     *
     * @param int $id
     * @return \Magento\Cms\Model\Block
     */
    public function loadCmsBlock($id)
    {
        return $this->blockFactory->create()->load($id);
    }

    /**
     * Get Category By Id
     *
     * @param int $id
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    public function getCategoryById($id)
    {
        try {
            return $this->categoryRepositoryInterface->get($id, $this->getStoreId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get Children Categories
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChildrenCategories($category)
    {
        /** @var $category \Magento\Catalog\Model\Category */
        return isset($category)?$category->getChildrenCategories()
            ->addIsActiveFilter()
            ->addAttributeToFilter('include_in_menu', ['eq' => 1]):'';
    }

    /**
     * Load all Mega Menus
     *
     * @param int $parentId
     * @param int $sortOrder
     * @param int $id
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws NoSuchEntityException
     */
    public function loadAllMegaMenus($parentId = null, $sortOrder = null, $id = null)
    {
        $customerGroup = $this->group;
        $menuCollection = $this->menuCollectionFactory->create();
        $menuCollection = $menuCollection->addStoreFilter($this->getStoreId())
                            ->addFieldToFilter('is_active', 1)
                            ->addFieldToFilter('customer_groups', ['finset' => $customerGroup]);
        return $menuCollection;
    }

    /**
     * Get children categories by id
     *
     * @param int $id
     * @param int $sort
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildrenCategoriesById($id, $sort = null)
    {
        // Get the parent category by its ID
        $parentCategory = $this->categoryRepositoryInterface->get($id);

        // Get the child categories of the parent category
        $searchCriteriaBuilder = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $parentCategory->getId())
            ->addFilter('is_active', true)
            ->addFilter('include_in_menu', 1);

        // Apply sorting if provided
        if ($sort && isset($sort['sort_by']) && isset($sort['sort_order'])) {
            $direction = strtoupper($sort['sort_order']);
            // Make sure direction is either ASC or DESC
            $direction = in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';

            $sortOrder = $this->sortOrderBuilder
                ->setField($sort['sort_by'])
                ->setDirection($direction)
                ->create();

            $searchCriteriaBuilder->setSortOrders([$sortOrder]);
        }

        $searchCriteria = $searchCriteriaBuilder->create();
        $childCategories = $this->categoryList->getList($searchCriteria);

        return $childCategories->getItems();
    }
}
