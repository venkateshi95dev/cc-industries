<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed getCustomerById function as it is not used.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Helper\ServiceRequest;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Customer\Create;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Customer\Info;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Customer\Response;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Create customer, get customerinfo, set customer response
 */
class Customer
{
    /**
     * @var ServiceRequest
     */
    private $requestHelper;

    /**
     * @var Customer\Info
     */
    public $customerInfo;

    /**
     * @var Customer\Response
     */
    public $customerResponse;

    /**
     * @var Customer\Create
     */
    public $create;

    /**
     *
     * @param ServiceRequest $requestHelper
     * @param Response $customerResponse
     * @param Info $customerInfo
     * @param Create $create
     */
    public function __construct(
        ServiceRequest $requestHelper,
        Response $customerResponse,
        Info $customerInfo,
        Create $create
    ) {
        $this->requestHelper = $requestHelper;
        $this->customerResponse = $customerResponse;
        $this->customerInfo = $customerInfo;
        $this->create = $create;
    }

    /**
     * Create customer.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     *
     * @return I95DevResponseInterface
     */
    public function create($stringData, $entityCode, $erp)
    {
        return $this->create->createCustomer($stringData, $entityCode, $erp);
    }

    /**
     * Get customer information
     *
     * @param int $customerId
     * @param string $entityCode
     * @param string $erpCode
     * @param int|null $messageId
     * @return array
     * @throws LocalizedException
     */
    public function getInfo($customerId, $entityCode, $erpCode, $messageId)
    {
        return  $this->customerInfo->getInfo($customerId, $entityCode, $erpCode, $messageId);
    }

    /**
     * Sets target customer information
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return I95DevResponseInterface
     */
    public function getResponse($requestData, $entityCode, $erpCode)
    {
        return $this->customerResponse->getResponse($requestData, $entityCode, $erpCode);
    }
}
