<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Helper;

use \Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\Session;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    /**
     * @var GroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var CategoryFactory
     */
    protected $categoryCollection;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param GroupFactory $customerGroupFactory
     * @param Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     * @param State $state
     * @param CategoryFactory $categoryCollection
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        GroupFactory $customerGroupFactory,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager,
        State $state,
        CategoryFactory $categoryCollection,
        Session $customerSession,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->customerGroupFactory = $customerGroupFactory;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->state = $state;
        $this->categoryCollection = $categoryCollection;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Customer Groups Options
     *
     * @return string
     */
    public function getCustomerGroupsOptions()
    {
        $groupCollection = $this->customerGroupFactory->create()->getCollection()
            ->load()
            ->toOptionHash();
        $optionString = '';
        foreach ($groupCollection as $groupId => $code) {
            $optionString .= '<option value="'.$groupId.'">'.$code.'</option>';
        }
        return $optionString;
    }

    /**
     * Get Customer Groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        return $this->customerGroupFactory->create()->getCollection()
            ->load()
            ->toOptionHash();
    }

    /**
     * Check is enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->getConfig('magedelight/general/megamenu_status');
    }

    /**
     * Is Humberger Menu
     *
     * @return bool
     */
    public function isHumbergerMenu()
    {
        return (bool) $this->getConfig('magedelight/general/hamburger_menu');
    }

    /**
     * Get Config
     *
     * @param string $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Menu Types
     *
     * @return array
     */
    public function menuTypes()
    {
        return [
          'megamenu'=>'Mega Menu Block',
          'category'=>'Category Selection',
          'pages'=>'Page Selection',
          'link'=>'External Links'
        ];
    }

    /**
     * Get Menu Name
     *
     * @param string $key
     * @return mixed
     */
    public function getMenuName($key)
    {
        $menuTypes = $this->menuTypes();
        return $menuTypes[$key];
    }

    /**
     * Is Catalog Permission Exist
     *
     * @return bool
     */
    public function isCatalogPermissionExist()
    {
        return (bool) $this->moduleManager->isEnabled('Magento_CatalogPermissions');
    }

    /**
     * Inject Permission class
     *
     * @param mixed $instanceName
     * @return mixed
     */
    public function injectPermissionClass($instanceName)
    {
        return $this->objectManager->create($instanceName);
    }

    /**
     * Permission Enabled
     *
     * @return bool
     */
    public function permissionEnabled()
    {
        if ($this->isCatalogPermissionExist()) {
            $config = $this->injectPermissionClass(\Magento\CatalogPermissions\App\ConfigInterface::class);
            return (bool) $config->isEnabled();
        }
        return false;
    }

    /**
     * Get Area
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getArea()
    {
        return $this->state->getAreaCode();
    }

    /**
     * Get Exclude Category Ids
     *
     * @param int $customerGroup
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getExcludeCategoryIds($customerGroup = null)
    {
        if (!$this->permissionEnabled()) {
            return [];
        }
        if ($customerGroup == null) {
            $customerGroup = $this->customerSession->getCustomerGroupId();
        }
        $excludeCategoryIds = [];
        $categoryCollection = $this->categoryCollection->create()->getCollection();
        $categoryIds = $categoryCollection->getColumnValues('entity_id');
        if ($categoryIds) {
            $_permissionIndex = $this->injectPermissionClass(
                \Magento\CatalogPermissions\Model\Permission\Index::class
            );
            $permissions = $_permissionIndex->getIndexForCategory(
                $categoryIds,
                $customerGroup,
                $this->storeManager->getStore()->getWebsiteId()
            );
            foreach ($permissions as $categoryId => $permission) {
                $categoryCollection->getItemById($categoryId)->setPermissions($permission);
            }
            $_catalogPermData = $this->injectPermissionClass(\Magento\CatalogPermissions\Helper\Data::class);
            foreach ($categoryCollection as $category) {
                if ($category->getData('permissions/grant_catalog_category_view') == -2
                    || $category->getData('permissions/grant_catalog_category_view') != -1
                    && !$_catalogPermData->isAllowedCategoryView()
                ) {
                    $excludeCategoryIds[] = $category->getId();
                }
            }
        }
        return $excludeCategoryIds;
    }

    /**
     * Is primary Menu Selected
     *
     * @return int
     */
    public function isPrimaryMenuSelected()
    {
        return (int) $this->getConfig('magedelight/general/primary_menu');
    }
}
