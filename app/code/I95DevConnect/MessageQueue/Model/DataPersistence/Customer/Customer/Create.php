<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed Magento REST API calls and the logic converted into interfaces.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Customer;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterfaceFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for creating customer in Magento
 */
class Create
{
    public const TARGETID = "targetId";
    public const EMAIL = "email";
    public const SKIPOBS = "i95_observer_skip";
    public const SAVINGSOURCE = "savingSource";

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var Validate
     */
    public $validate;

    /**
     * @var string[]
     */
    public $validateFields = [
        self::TARGETID => 'i95dev_cust_003',
        'firstName' => 'i95dev_cust_005',
        'lastName' => 'i95dev_cust_006',
        self::EMAIL => 'i95dev_cust_004',
    ];

    /**
     * @var string
     */
    public $targetFieldErp = self::TARGETID;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var string
     */
    public $stringData;

    /**
     * @var string
     */
    public $entityCode;

    /**
     * @var string
     */
    public $component;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var array
     */
    public $result;

    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    public $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var CustomerInterfaceFactory
     */
    public $customerInterfaceFactory;

    /**
     * @var array
     */
    public $customerInterface;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var GroupRepositoryInterfaceFactory
     */
    public $groupRepository;

    /**
     * @var GroupInterfaceFactory
     */
    public $groupInterface;

    /**
     * @var int
     */
    public $isNewCustomer;

    /**
     * @var CustomerFactory
     */
    public $customerFactory;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param Data $dataHelper
     * @param Manager $eventManager
     * @param Validate $validate
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param StoreManagerInterface $storeManager
     * @param GroupRepositoryInterfaceFactory $groupRepository
     * @param GroupInterfaceFactory $groupInterface
     * @param CustomerRepositoryInterfaceFactory $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param Generic $genericHelper
     * @param CustomerFactory $customerFactory
     */
    public function __construct( // NOSONAR
        LoggerInterfaceFactory $logger,
        Data $dataHelper,
        Manager $eventManager,
        Validate $validate,
        AbstractDataPersistence $abstractDataPersistence,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterfaceFactory $groupRepository,
        GroupInterfaceFactory $groupInterface,
        CustomerRepositoryInterfaceFactory $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerInterfaceFactory $customerInterfaceFactory,
        Generic $genericHelper,
        CustomerFactory $customerFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->validate = $validate;
        $this->eventManager = $eventManager;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->groupInterface = $groupInterface;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->genericHelper = $genericHelper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Create customer.
     *
     * @param array $stringData
     * @param string $entityCode
     * @param string $erp
     * @return I95DevResponseInterface
     * @updatedBy Divya Koona. Removed Magento REST API call and converted to interfaces.
     */
    public function createCustomer($stringData, $entityCode, $erp = null)
    {
        $this->stringData = $this->getEntityData($stringData);
        $this->entityCode = $entityCode;
        try {
            $this->validate->validateFields = $this->validateFields;
            $this->component = $this->dataHelper->getscopeConfig(
                'i95dev_messagequeue/I95DevConnect_settings/component',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $this->validate->validateData($this->stringData);
            $customerEmail = $this->dataHelper->getValueFromArray(self::EMAIL, $this->stringData);

            $targetCustomerId = $this->dataHelper->getValueFromArray($this->targetFieldErp, $this->stringData);
            
            $websiteId = $this->getWebsiteId($this->stringData);
            $email = $customerEmail ?: null;
            $customerInfo = $this->genericHelper->getCustomerInfoByTargetId($targetCustomerId, $email, $websiteId);
            if (empty($customerInfo)) {
                if ($this->customerIsEmailAvailable($customerEmail)) {
                    $this->isNewCustomer = true;
                    $this->customerInterface = $this->customerInterfaceFactory->create();
                    $this->customerInterface->setCustomAttribute('target_customer_id', $targetCustomerId);
                    $this->customerInterface->setCustomAttribute('origin', $this->component);
                } else {
                    throw new LocalizedException(
                        __('i95dev_cust_009'),
                        null,
                        108
                    );
                }
            } else {
                $this->customerInterface = $this->customerInterfaceFactory->create();
                if (isset($customerInfo[0])) {
                    $this->customerInterface->setId($customerInfo[0]->getId());
                }
            }

            $this->prepareDataForApi();
            $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $entityCode;
            $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);
            $this->dataHelper->unsetGlobalValue(self::SKIPOBS);
            $this->dataHelper->setGlobalValue(self::SKIPOBS, true);
            $this->customerInterface->setCustomAttribute('target_customer_id', $targetCustomerId);
            $this->result = $this->customerRepository->create()->save($this->customerInterface);

            if (!is_object($this->result)) {
                $customerId = null;
                throw new LocalizedException(
                    __('i95dev_cust_010'),
                    null,
                    105
                );
            } else {
                $customerId = $this->result->getId();
                if ($this->isNewCustomer) {
                    $customer = $this->customerFactory->create()->load($customerId);
                    $customer->sendNewAccountEmail();
                }
            }
            $this->dataHelper->unsetGlobalValue(self::SKIPOBS);

            $jsondata = json_encode([
                "entityCode" => $entityCode,
                self::TARGETID => $this->dataHelper->getValueFromArray($this->targetFieldErp, $this->stringData),
                "source" => $erp
            ], JSON_UNESCAPED_UNICODE);

            $this->dataHelper->coreRegistry->unregister(self::SAVINGSOURCE);
            $this->dataHelper->coreRegistry->register(self::SAVINGSOURCE, $jsondata);

            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);
        } catch (LocalizedException $ex) {
            //Updated By Hrusikesh Manna To Return The Exception Instead Of Throughing
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }

        return $this->setCustomerResponse($customerId);
    }

    /**
     * Get the string data
     *
     * @param string $stringData
     * @createdBy Ranjith R. to support plugins
     * @return string
     * @return string
     */
    public function getEntityData($stringData)
    {
        return $stringData;
    }

    /**
     * Check if customer email is available in Magento or not
     *
     * @param string $email
     * @return boolean
     * @throws LocalizedException
     * @updatedBy Divya Koona. Removed Magento REST API call.
     */
    public function customerIsEmailAvailable($email)
    {
        try {
            $websiteId = $this->getWebsiteId($this->stringData);
            $this->customerRepository->create()->get($email, $websiteId);
            return false;
        } catch (NoSuchEntityException $ex) {
            return true;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Prepare customer data interface
     *
     * @return void
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function prepareDataForApi()
    {
        $this->customerInterface->setEmail($this->dataHelper->getValueFromArray(self::EMAIL, $this->stringData));
        $this->customerInterface->setFirstname($this->dataHelper->getValueFromArray("firstName", $this->stringData));
        $this->customerInterface->setLastname($this->dataHelper->getValueFromArray("lastName", $this->stringData));

        $this->customerInterface->setPrefix($this->dataHelper->getValueFromArray("prefix", $this->stringData));
        $this->customerInterface->setSuffix($this->dataHelper->getValueFromArray("suffix", $this->stringData));
        $this->customerInterface->setMiddlename($this->dataHelper->getValueFromArray("middleName", $this->stringData));

        $websiteId = $this->getWebsiteId($this->stringData);
        $this->customerInterface->setWebsiteId($websiteId);

        $storeIds = $this->dataHelper->getStoreId($websiteId);
        if (!empty($storeIds)) {
            $this->customerInterface->setStoreId($storeIds[0]);
        }

        $customerGroup = $this->dataHelper->getValueFromArray("customerGroup", $this->stringData);

        if ($customerGroup) {
            $group = $this->getCustomerGroupInfo($customerGroup);
            if (empty($group)) {
                $customerGroupData = $this->groupInterface->create();
                $customerGroupData->setCode($customerGroup);
                $customerGroupData = $this->groupRepository->create()->save($customerGroupData);
                $groupId = $customerGroupData->getId();
            } else {
                $groupId = $group[0]->getId();
            }
        } else {
            $groupId = $this->dataHelper->getscopeConfig(
                'i95dev_messagequeue/I95DevConnect_settings/customer_group',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
        }

        $this->customerInterface->setGroupId($groupId);

        $jsondata = json_encode([
            "entityCode" => $this->entityCode,
            self::TARGETID => $this->dataHelper->getValueFromArray($this->targetFieldErp, $this->stringData),
            "source" => "ERP"
        ], JSON_UNESCAPED_UNICODE);
        $this->dataHelper->coreRegistry->unregister(self::SAVINGSOURCE);
        $this->dataHelper->coreRegistry->register(self::SAVINGSOURCE, $jsondata);

        $this->customerInterface->setCustomAttribute('update_by', $this->component);
    }

    /**
     * Fetch Customer group based on customer group code
     *
     * @param string $customerGroupCode
     *
     * @return array
     * @throws LocalizedException
     * @updatedBy Divya Koona. Removed Magento REST API call.
     */
    public function getCustomerGroupInfo($customerGroupCode)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_group_code', $customerGroupCode, 'eq')
                ->create();
            $searchResults = $this->groupRepository->create()->getList($searchCriteria);
            return $searchResults->getItems();
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                LoggerInterface::CRITICAL
            );
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getMessage()
            );
        }
    }

    /**
     * Set customer response
     *
     * @param int $customerId
     * @return I95DevResponseInterface
     */
    public function setCustomerResponse($customerId)
    {
        if (isset($customerId)) {
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                __("i95dev_cust_011"),
                $this->result->getId()
            );
        } else {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __('i95dev_cust_010'),
                null,
                105
            );
        }
    }

    /**
     * Get Website Id
     *
     * @param string $stringData
     * @return mixed
     */
    public function getWebsiteId($stringData)//NOSONAR
    {
        $websiteId = $this->dataHelper->getValueFromArray("websiteIds", $stringData);
        if (!empty($websiteId)) {
            return $websiteId;
        }
        return $this->storeManager->getDefaultStoreView()->getWebsiteId();
    }
}
