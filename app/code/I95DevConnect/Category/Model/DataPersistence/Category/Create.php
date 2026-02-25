<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Category
 */

namespace I95DevConnect\Category\Model\DataPersistence\Category;

use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\Category\Helper\Data as CategoryHelper;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for syncing category
 */
class Create extends AbstractDataPersistence
{
    public const XML_PATH_GENERIC_CONNECT_ERP_CRM = 'i95dev_messagequeue/I95DevConnect_settings/component';
    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var CategoryHelper
     */
    public $categoryHelper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     *
     * @var array
     */
    public $validateFields = [
        'targetId' => 'i95dev_category_001'
    ];
    public $category;

    /**
     * Create constructor.
     * @param Data $dataHelper
     * @param Decoder $jsonDecoder
     * @param I95DevResponseInterfaceFactory $i95DevResponse
     * @param ErrorUpdateDataFactory $messageErrorModel
     * @param I95DevErpMQInterfaceFactory $i95DevErpMQ
     * @param LoggerInterfaceFactory $logger
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DateTime $date
     * @param Manager $eventManager
     * @param Validate $validate
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository
     * @param CategoryHelper $categoryHelper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        Decoder $jsonDecoder,
        I95DevResponseInterfaceFactory $i95DevResponse,
        ErrorUpdateDataFactory $messageErrorModel,
        I95DevErpMQInterfaceFactory $i95DevErpMQ,
        LoggerInterfaceFactory $logger,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DateTime $date,
        Manager $eventManager,
        Validate $validate,
        I95DevErpDataRepositoryInterfaceFactory $i95DevERPDataRepository,
        CategoryHelper $categoryHelper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->dataHelper = $dataHelper;
        $this->categoryHelper = $categoryHelper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $jsonDecoder,
            $i95DevResponse,
            $messageErrorModel,
            $i95DevErpMQ,
            $logger,
            $i95DevErpMQRepository,
            $date,
            $eventManager,
            $validate,
            $i95DevERPDataRepository
        );
    }

    /**
     * Create Category
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erpCode
     * @return I95DevResponseInterface
     * @throws \Exception
     */
    public function create($stringData, $entityCode, $erpCode = null)
    {
        $this->setStringData($stringData);
        try {
            $this->validate->validateFields = $this->validateFields;
            $this->validate->validateData($this->stringData);
            $targetCategoryCode = $this->dataHelper->getValueFromArray("targetId", $this->stringData);
            $parentCategoryCode = $this->dataHelper->getValueFromArray("parentCategoryCode", $this->stringData);
            $categoryName = $this->dataHelper
                ->getValueFromArray("categoryName", $this->stringData, $targetCategoryCode, true);
            $categoryStatus = $this->dataHelper
                ->getValueFromArray("status", $this->stringData, true, true);
            $storeIds = array_keys($this->storeManager->getStores());
            array_push($storeIds, 0);
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
            $component = $this->scopeConfig->getValue(
                self::XML_PATH_GENERIC_CONNECT_ERP_CRM,
                $storeScope,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );

            // Get website IDs from input data (expects an array)
            $websiteIds = $this->dataHelper->getValueFromArray("websiteIds", $this->stringData);
            
            // Filter storeIds to only those belonging to enabled websiteIds
            $filteredStoreIds = [];
            foreach ($storeIds as $storeId) {
                try {
                    $store = $this->storeManager->getStore($storeId);
                    $websiteId = $store->getWebsiteId();
                    if (($websiteId==$websiteIds)) {
                        $filteredStoreIds[] = $storeId;
                    }
                } catch (\Exception $e) {
                    // skip invalid store
                    continue;
                }
            }

            if ($component == 'D365FO' && $parentCategoryCode == null) {
                $this->mapAllCatToRootCat($targetCategoryCode, $categoryName, $filteredStoreIds);
                return $this->setResponse(
                    Data::SUCCESS,
                    "Record Successfully Synced",
                    $this->storeManager->getStore()->getRootCategoryId()
                );
            }
            if (isset($parentCategoryCode) && $parentCategoryCode != '') {
                $parentCategoryId = $this->categoryHelper->getCatIdByTargetCatCode($parentCategoryCode);
                if (isset($parentCategoryId)) {
                    $parentCategory = $this->categoryHelper->categoryFactory->create()->load($parentCategoryId);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('i95dev_category_002' . $parentCategoryCode),
                        null,
                        105
                    );
                }
            } else {
                $parentCategoryId = $this->storeManager->getStore()->getRootCategoryId();
                $parentCategory = $this->categoryHelper->categoryFactory->create()->load($parentCategoryId);
            }

            $categoryId = $this->categoryHelper->getCatIdByTargetCatCode($targetCategoryCode);
            if (!isset($categoryId)) {
                $this->category = $this->categoryHelper->categoryFactory->create();
                $this->category->setTargetCategoryCode($targetCategoryCode)
                    ->setPath($parentCategory->getPath())
                    ->setParentId($parentCategoryId);
            } else {
                $this->category = $this->categoryHelper->categoryFactory->create()->load($categoryId);
                if ($parentCategoryId != $this->category->getParentId()) {
                    $this->category->move($parentCategoryId, null);
                }
            }

            // Set website IDs for the category (for website visibility)
            if (method_exists($this->category, 'setWebsiteIds')) {
                $this->category->setWebsiteIds($websiteIds);
            } else {
                // fallback: set data directly if method does not exist
                $this->category->setData('website_ids', $websiteIds);
            }

            $this->category->setIsActive(0);
            $beforeeventname = 'i95dev_messagequeuetomagento_beforesave_' . $entityCode;
            $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);
            foreach ($filteredStoreIds as $storeId) {
                $this->category
                    ->setStoreId($storeId)
                    ->setName($categoryName)
                    ->setIsActive($categoryStatus)
                    ->save();
            }
            $aftereventname = 'i95dev_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);

            return $this->setResponse(
                Data::SUCCESS,
                "Record Successfully Synced",
                $this->category->getId()
            );
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            return $this->setResponse(
                Data::ERROR,
                __($erpCode.$ex->getMessage()),
                null
            );
        }
    }
    /**
     * Map ALL category to Default Root Category of Magento
     *
     * @param string $targetCategoryCode
     * @param string $categoryName
     * @param array $storeIds
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function mapAllCatToRootCat($targetCategoryCode, $categoryName, $storeIds)
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $rootCategory = $this->categoryHelper->categoryFactory->create()->load($rootCategoryId);
        $rootCategory->setTargetCategoryCode($targetCategoryCode)->save();
        foreach ($storeIds as $storeId) {
            try {
                $store = $this->storeManager->getStore($storeId);
                // Only set for stores whose website is enabled (already filtered)
                $rootCategory
                    ->setStoreId($storeId)
                    ->setName($categoryName)
                    ->save();
            } catch (\Exception $e) {
                // skip invalid store
                continue;
            }
        }
    }
}
