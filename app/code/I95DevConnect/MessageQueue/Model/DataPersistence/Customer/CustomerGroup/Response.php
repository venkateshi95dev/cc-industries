<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\CustomerGroupFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class responsible for saving erp responses in customer group
 */
class Response
{
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
     * @var DateTime
     */
    public $date;

    /**
     *
     * @var CustomerGroupFactory
     */
    public $i95DevGroupFactory;

    /**
     *
     * @var GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     * @var string
     */
    protected $customerGroupId;

    /**
     * @var string
     */
    protected $targetCustomerGroup;

    /**
     * @var string
     */
    protected $erpCode;

    /**
     * @var string
     */
    protected $customerGroup = null;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    public const CRITICAL = "critical";

    /**
     *
     * @param Data $dataHelper
     * @param Manager $eventManager
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param CustomerGroupFactory $i95DevGroupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param AbstractDataPersistence $abstractDataPersistence
     */
    public function __construct(
        Data $dataHelper,
        Manager $eventManager,
        LoggerInterface $logger,
        DateTime $date,
        CustomerGroupFactory $i95DevGroupFactory,
        GroupRepositoryInterface $groupRepository,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->dataHelper = $dataHelper;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->date = $date;
        $this->i95DevGroupFactory = $i95DevGroupFactory;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Sets target customer group details.
     *
     * @param array $requestData
     * @return I95DevResponseInterface
     * @author Debashis S. Gopal. Code changed from api to repository interface to save target details
     */
    public function getResponse($requestData)
    {
        $this->customerGroupId = $this->dataHelper->getValueFromArray("sourceId", $requestData);
        $this->targetCustomerGroup = $this->dataHelper->getValueFromArray("targetId", $requestData);
        try {
            if ($this->validateData()) {
                if (isset($requestData['erp_name'])) {
                    $this->erpCode = $requestData['erp_name'];
                } else {
                    $this->erpCode = __("ERP");
                }
                $this->customerGroup->setCode($this->targetCustomerGroup);
                $taxClassId = $this->customerGroup->getTaxClassId();
                if (!isset($taxClassId)) {
                    $this->customerGroup->setTaxClassId(3);
                }
                $customerGroupResponseBeforeEvent = "erpconnect_forward_customergroupresponse_before";
                $this->eventManager->dispatch($customerGroupResponseBeforeEvent, ['currentObject' => $this]);
                $result = $this->groupRepository->save($this->customerGroup);
                $customerGroupResponseAfterEvent = "erpconnect_forward_customergroupresponse_after";
                $this->eventManager->dispatch($customerGroupResponseAfterEvent, ['currentObject' => $this]);
                $this->returnMqResponse($result);
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Customer Group response is invalid to sync"),
                    null,
                    105
                );
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                self::CRITICAL
            );
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Validate if customer group is exists in magento or not
     *
     * @updatedBy Debashis S. Gopal
     * @return boolean
     * @throws LocalizedException
     */
    public function validateData()
    {
        try {
            $this->customerGroup = $this->groupRepository->getById($this->customerGroupId);
            if (empty($this->customerGroup)) {
                $message = "Customer Group Not Found ::" . $this->customerGroupId;
                throw new LocalizedException(
                    __($message),
                    null,
                    108
                );
            }
            $groupCode = $this->customerGroup->getCode();
            if ($groupCode != $this->targetCustomerGroup) {
                throw new LocalizedException(
                    __("Input target customer group did not match with magento"),
                    null,
                    104
                );
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return true;
    }

    /**
     * Update customer group details in i95dev_customer_group table.
     *
     * @createdBy Debashis S. Gopal
     */
    protected function updateI95DevGroupDetails()
    {
        try {
            $customGroupCollection = $this->i95DevGroupFactory->create()->getCollection()
                    ->addFieldToFilter('customer_group_id', $this->customerGroupId);
            if ($customGroupCollection->getSize() > 0) {
                $customGroupData = $customGroupCollection->getData();
                $customGroupModel = $this->i95DevGroupFactory->create()->load($customGroupData[0]['id']);
                $customGroupModel->setTargetGroupId($this->targetCustomerGroup);
                $currentDate = $this->date->gmtDate();
                $customGroupModel->setUpdatedAt($currentDate);
                $customGroupModel->setUpdateBy($this->erpCode);
                $customGroupModel->save();
            } else {
                $this->logger->createLog(
                    __METHOD__,
                    "Target Customer group not exists in i95dev_customer_group",
                    LoggerInterface::I95EXC,
                    self::CRITICAL
                );
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                self::CRITICAL
            );
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Return success or error response to IBMQ
     *
     * @param GroupInterface $result
     * @return I95DevResponseInterface
     */
    public function returnMqResponse($result)
    {
        if ($result->getId() && is_numeric($result->getId())) {
            $this->updateI95DevGroupDetails();
            return $this->abstractDataPersistence->setResponse(
                Data::SUCCESS,
                __("Response send successfully")
            );
        } else {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __("Some error occurred in response sync"),
                null,
                105
            );
        }
    }
}
