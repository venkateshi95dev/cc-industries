<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer;

use Exception;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup\Create;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup\Info;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup\Response;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Create customer group, get customer group info and set customer group response
 */
class CustomerGroup
{
    public const I95EXC = 'i95devApiException';

    /**
     * @var CustomerGroup\Create
     */
    public $create;

    /**
     * @var CustomerGroup\Response
     */
    public $customerGroupResponse;

    /**
     * @var \I95DevConnect\MessageQueue\Model\CustomerGroup
     */
    public $customerGroup;

    /**
     * @var CustomerGroup\Info
     */
    public $customerGroupInfo;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     *
     * @param Create $create
     * @param Info $customerGroupInfo
     * @param Response $customerGroupResponse
     * @param \I95DevConnect\MessageQueue\Model\CustomerGroup $customerGroup
     * @param LoggerInterfaceFactory $logger
     */
    public function __construct(
        Create $create,
        Info $customerGroupInfo,
        Response $customerGroupResponse,
        \I95DevConnect\MessageQueue\Model\CustomerGroup $customerGroup,
        LoggerInterfaceFactory $logger
    ) {

        $this->create = $create;
        $this->customerGroupInfo = $customerGroupInfo;
        $this->customerGroupResponse = $customerGroupResponse;
        $this->customerGroup = $customerGroup;
        $this->logger = $logger;
    }

    /**
     * Create customer group.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     * @return I95DevResponseInterface
     * @throws Exception
     * @throws Exception
     */
    public function create($stringData, $entityCode, $erp) //NOSONAR
    {
        return $this->create->createCustomerGroup($stringData, $entityCode);
    }

    /**
     * Get customer group information
     *
     * @param int $customerGroupId
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return array
     * @throws LocalizedException
     */
    public function getInfo($customerGroupId, $entityCode, $erpCode) //NOSONAR
    {
        return  $this->customerGroupInfo->getInfo($customerGroupId);
    }

    /**
     * Sets target customer group information
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return I95DevResponseInterface
     */
    public function getResponse($requestData, $entityCode, $erpCode) //NOSONAR
    {
        return $this->customerGroupResponse->getResponse($requestData);
    }

    /**
     * Get customer group code by group id
     *
     * @param int $customerGroupId
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerGroupEntityByGroupId($customerGroupId)
    {
        $customerGroupEntity = $this->customerGroupInfo->getCustomerGroupById($customerGroupId);
        return $customerGroupEntity->getCode();
    }

    /**
     * Fetch customer group by customer group id
     *
     * @param int $sourceCustomerGroupId
     * @return array
     */
    public function getCustomerGroupById($sourceCustomerGroupId)
    {

        try {
            $customGroupModel = $this->customerGroup;
            $customGroupCollection = $customGroupModel->getCollection()
                            ->addFieldToFilter('customer_group_id', $sourceCustomerGroupId);
            $customGroupCollection->getSelect()->limit(1);

            $customGroupCollection = $customGroupCollection->getData();
            $customCustomerGroupId = isset($customGroupCollection[0]['id']) ? $customGroupCollection[0]['id'] : '';
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
        return $customCustomerGroupId;
    }
}
