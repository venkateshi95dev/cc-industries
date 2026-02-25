<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\MessageQueue\Api\LoggerInterface;

/**
 * Class for preparing customer group result data to be sent to ERP
 */
class Info
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
     * @var GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     * Magento group fields mapped to Required ERP fields
     *
     * @var array
     */
    public $fieldMapInfo = [
        'groupDescription' => 'code',
        'customerGroup' => 'code',
        'reference' => 'code'
    ];

    /**
     * @var array
     */
    public $customerGroupData;

    /**
     *
     * @param Data $dataHelper
     * @param Manager $eventManager
     * @param LoggerInterface $logger
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        Data $dataHelper,
        Manager $eventManager,
        LoggerInterface $logger,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->dataHelper = $dataHelper;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Retrieves customer group entity based on customer group id
     *
     * @updatedBy Debashis S. Gopal. Converted $customerGroup object to array.
     * @param int $customerGroupId
     * @return array
     */
    public function getInfo($customerGroupId)
    {
        try {
            $customerGroup = $this->getCustomerGroupById($customerGroupId);
            $customerGroupArray = $customerGroup->__toArray();
            if (is_array($customerGroupArray) && !empty($customerGroupArray)) {
                $this->customerGroupData = $this->dataHelper->prepareInfoArray(
                    $this->fieldMapInfo,
                    $customerGroupArray
                );
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        $customerGroupInfoEvent = "erpconnect_forward_customergroupinfo";
        $this->eventManager->dispatch($customerGroupInfoEvent, ['currentObject' => $customerGroup]);
        return $this->customerGroupData;
    }

    /**
     * Fetch customer group based on group id
     *
     * @updatedBy Debashis S. Gopal. Changed to groupRepository from api call to retrieve customer group.
     * @param int $customerGroupId
     * @return GroupInterface
     * @throws LocalizedException
     */
    public function getCustomerGroupById($customerGroupId)
    {
        $result = $this->groupRepository->getById($customerGroupId);
        if (empty($result)) {
            throw new LocalizedException(
                __('i95dev_customergroup_notfound %1', $customerGroupId),
                null,
                108
            );
        }
        return $result;
    }
}
