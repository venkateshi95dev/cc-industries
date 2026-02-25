<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\CustomerGroup;
use I95DevConnect\MessageQueue\Model\CustomerGroupFactory;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class for creating or updating customer group in Magento.
 *
 * @updatedBy Debashis S. Gopal
 */
class Create
{
    public const SKIPOBS = "i95_observer_skip";

    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @var Data
     */
    public $dataHelper;

    /**
     *
     * @var Manager
     */
    public $eventManager;

    /**
     *
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     *
     * @var DateTime
     */
    public $date;

    /**
     *
     * @var GroupInterfaceFactory
     */
    public $groupFactory;

    /**
     *
     * @var GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     *
     * @var Validate
     */
    public $validate;

    /**
     *
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var CustomerGroupFactory
     */
    public $i95DevCustomerGroupFactory;

    /**
     * @var string
     */
    public $stringData = '';

    /**
     * @var string[]
     */
    public $validateFields = ['targetId' => 'i95dev_empty_customerGroup'];

    /**
     * @var string
     */
    public $i95DevCustomGroup = null;

    /**
     * @var string
     */
    public $customerGroup = null;

    /**
     * @var array
     */
    public $resultData = null;

    /**
     * @var string
     */
    public $erpCustomerGroupId = null;

    /**
     *
     * @param Data $dataHelper
     * @param Manager $eventManager
     * @param DateTime $date
     * @param LoggerInterface $logger
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param CustomerGroupFactory $i95DevCustomerGroupFactory
     * @param GroupInterfaceFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param Validate $validate
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        Manager $eventManager,
        DateTime $date,
        LoggerInterface $logger,
        AbstractDataPersistence $abstractDataPersistence,
        CustomerGroupFactory $i95DevCustomerGroupFactory,
        GroupInterfaceFactory $groupFactory,
        GroupRepositoryInterface $groupRepository,
        Validate $validate,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->dataHelper = $dataHelper;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->i95DevCustomerGroupFactory = $i95DevCustomerGroupFactory;
        $this->date = $date;
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->validate = $validate;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Create or update customer group in Magento.
     *
     * @updatedBy Debashis S. Gopal
     *
     * @param array $stringData
     * @param string $entityCode
     * @return I95DevResponseInterface
     * @throws Exception
     */
    public function createCustomerGroup($stringData, $entityCode)
    {
        try {
            $this->stringData = $stringData;
            $this->validateData();
            $this->i95DevCustomGroup = $this->getI95DevCustomerGroupByTargetId($this->erpCustomerGroupId);
            if (!empty($this->i95DevCustomGroup)) {
                $groupId = $this->i95DevCustomGroup->getCustomerGroupId();
                $this->customerGroup = $this->groupRepository->getById($groupId);
            } else {
                /** @updatedBy Debashis.Check if $erpCustomerGroupId is already exists in magento default groups * */
                $groupData = $this->dataHelper->checkInDefaultCustomerGroups($this->erpCustomerGroupId);
                if ($groupData) {
                    $id = $groupData->getId();
                    $this->customerGroup = $this->groupRepository->getById($id);
                } else {
                    $this->customerGroup = $this->groupFactory->create();
                }
            }
            $this->addGroupData();
            $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $entityCode;
            $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);
            /** @updatedBy Debashis S. Gopal. $resultData changed to class level variable,
             * as we are using it in observer of price level. */

            $this->dataHelper->unsetGlobalValue(self::SKIPOBS);
            $this->dataHelper->setGlobalValue(self::SKIPOBS, true);

            $this->resultData = $this->groupRepository->save($this->customerGroup);

            $this->dataHelper->unsetGlobalValue(self::SKIPOBS);
            if (empty($this->resultData) || !$this->resultData->getCode()) {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    "Some issue occur with customer group creation. Please contact admin",
                    null,
                    105
                );
            }
            $customerGroupId = $this->resultData->getId();
            $this->updateI95DevCustomerGroup($this->resultData);
            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
            $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);
        } catch (LocalizedException $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                $ex->getMessage(),
                null,
                $ex->getCode()
            );
        }
        return $this->abstractDataPersistence->setResponse(
            Data::SUCCESS,
            "Record Successfully Synced",
            $customerGroupId
        );
    }

    /**
     * Validate ERP data and initialize $this->erpCustomerGroupId if valid data.
     *
     * @updatedBy Debashis S. Gopal
     */
    protected function validateData()
    {
        $this->validate->validateFields = $this->validateFields;
        if ($this->validate->validateData($this->stringData)) {
            $this->erpCustomerGroupId = $this->dataHelper->getValueFromArray("targetId", $this->stringData);
        }
    }

    /**
     * Get Customer group details from i95dev_customer_group by targetCustomerGroupId
     *
     * @createdBy Debashis S. Gopal
     *
     * @param int $erpCustomerGroupId
     *
     * @return CustomerGroup|null collection
     * @throws LocalizedException
     * @updatedBy Debashis S. Gopal.
     * Changed the method scope from private to public, As we are using this method in TierPrice module.
     * And changed to parameterized function.
     */
    public function getI95DevCustomerGroupByTargetId($erpCustomerGroupId)
    {
        $i95DevCustomGroupData = null;
        try {
            $i95DevCustomGroupCollection = $this->i95DevCustomerGroupFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('target_group_id', $erpCustomerGroupId)
                ->addFieldToSelect(['id']);
            $i95DevCustomGroupCollection->getSelect()->limit(1);
            if ($i95DevCustomGroupCollection->getSize() > 0) {
                $i95DevCustomGroupCollectionData = $i95DevCustomGroupCollection->getData();
                $i95DevCustomGroupData = isset($i95DevCustomGroupCollectionData[0]['id']) ?
                    $this->i95DevCustomerGroupFactory->create()->load($i95DevCustomGroupCollectionData[0]['id']) : null;
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $i95DevCustomGroupData;
    }

    /**
     * Add erp given data to customer group.
     *
     * @createdBy Debashis S. Gopal
     */
    protected function addGroupData()
    {
        $this->customerGroup->setCode($this->erpCustomerGroupId);
        $tax_class_id = $this->dataHelper->getValueFromArray("taxClass", $this->stringData);
        if (!empty($tax_class_id)) {
            $this->customerGroup->setTaxClassId((int)$tax_class_id);
        } else {
            $this->customerGroup->setTaxClassId(3);
        }
    }

    /**
     * Update customer group details in i95dev_customer_group table once the customer group synced to Magento.
     *
     * @param GroupInterface $result
     * @throws Exception
     * @createdBy Debashis S. Gopal
     */
    protected function updateI95DevCustomerGroup($result)
    {
        try {
            $currentDate = $this->date->gmtDate();
            if (empty($this->i95DevCustomGroup)) {
                $this->i95DevCustomGroup = $this->i95DevCustomerGroupFactory->create();
                $this->i95DevCustomGroup->setCreatedAt($currentDate);
            }
            $this->i95DevCustomGroup->setTargetGroupId($this->erpCustomerGroupId);
            $this->i95DevCustomGroup->setCustomerGroupId($result->getId());
            $this->i95DevCustomGroup->setUpdatedAt($currentDate);
            $this->i95DevCustomGroup->setUpdateBy(__('ERP'));
            $this->i95DevCustomGroup->save();
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), LoggerInterface::I95EXC, 'critical');
        }
    }
}
