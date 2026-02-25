<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed getCustomerAddressById,getAddressInfoByObject functions as they are not used.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Helper\ServiceRequest;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address\Create;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address\Info;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address\Response;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Create customer address
 */
class Address
{
    public const I95EXC = 'i95devApiException';

    /**
     * @var array
     */
    public $customerCollection;

    /**
     * @var array
     */
    public $customerAddressModel;

    /**
     * @var ServiceRequest
     */
    public $requestHelper;

    /**
     * @var array
     */
    public $postData;

    /**
     * @var array
     */
    public $resutlData;

    /**
     * @var int
     */
    public $customerId = 0;

    /**
     * @var int
     */
    public $targetAddressId = 0;

    /**
     * @var string
     */
    public $responseId = '';

    /**
     * @var Address\Response
     */
    public $addressResponse;

    /**
     * @var Address\Info
     */
    public $addressInfo;

    /**
     * @var Address\Create
     */
    public $create;

    /**
     *
     * @param ServiceRequest $requestHelper
     * @param Response $addressResponse
     * @param Info $addressInfo
     * @param Create $create
     */
    public function __construct(
        ServiceRequest $requestHelper,
        Response $addressResponse,
        Info $addressInfo,
        Create $create
    ) {
        $this->requestHelper = $requestHelper;
        $this->addressResponse = $addressResponse;
        $this->addressInfo = $addressInfo;
        $this->create = $create;
    }

    /**
     * Create customer address.
     *
     * @param string $stringData
     * @param string $entityCode
     * @param string $erp
     *
     * @return I95DevResponseInterface
     * @throws LocalizedException
     */
    public function create($stringData, $entityCode, $erp) //NOSONAR
    {
        return $this->create->createAddress($stringData, $entityCode);
    }

    /**
     * Sets target address information
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return bool
     * @throws LocalizedException
     */
    public function setAddressResponse($requestData, $entityCode, $erpCode = null)
    {
        return $this->addressResponse->setAddressResponse($requestData, $entityCode, $erpCode);
    }

    /**
     * Get address from customer information
     *
     * @param array $customer
     *
     * @return array
     * @throws LocalizedException
     */
    public function getInfo($customer)
    {
        return $this->addressInfo->setAddressData($customer);
    }
}
