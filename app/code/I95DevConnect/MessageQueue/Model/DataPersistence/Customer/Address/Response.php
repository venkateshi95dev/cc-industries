<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed getCustomerAddressById function and added in Generic Helper.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address;

use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use Magento\Customer\Api\AddressRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class responsible for saving erp responses in customer address
 */
class Response
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQRepository;

    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $I95DevMagMQData;

    /**
     * @var string
     */
    public $targetAddressId;

    /**
     * @var string
     */
    public $statusCode = '5';

    /**
     * @var string
     */
    public $updatedBy = 'ERP';

    /**
     * @var string
     */
    public $customer;

    /**
     * @var string
     */
    public $erpCode;

    /**
     * @var string
     */
    public $targetId;

    /**
     * @var string
     */
    public $entityCode;

    /**
     * @var array
     */
    public $address;

    /**
     * @var string
     */
    public $addressId;

    /**
     * @var string
     */
    public $targetCustomerId;

    /**
     * @var AddressRepositoryInterfaceFactory
     */
    public $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    public $addressInterfaceFactory;

    /**
     * @var array
     */
    public $addressInterface;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var string
     */
    public $messageId;

    /**
     *
     * @param Data $dataHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
     * @param I95DevMagMQInterfaceFactory $I95DevMagMQData
     * @param Manager $eventManager
     * @param AddressRepositoryInterfaceFactory $addressRepository
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param Generic $genericHelper
     */
    public function __construct(
        Data $dataHelper,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository,
        I95DevMagMQInterfaceFactory $I95DevMagMQData,
        Manager $eventManager,
        AddressRepositoryInterfaceFactory $addressRepository,
        AddressInterfaceFactory $addressInterfaceFactory,
        Generic $genericHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
        $this->I95DevMagMQData = $I95DevMagMQData;
        $this->eventManager = $eventManager;
        $this->addressRepository = $addressRepository;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->genericHelper = $genericHelper;
    }

    /**
     * Sets target address details in customer address
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return boolean
     * @throws LocalizedException
     * @updatedBy Divya Koona. Removed Magento REST API calls and converted to interface code.
     */
    public function setAddressResponse($requestData, $entityCode, $erpCode = null)
    {
        $this->customer = $this->dataHelper->getValueFromArray("customer", $requestData);
        $this->addressId = $this->dataHelper->getValueFromArray("sourceId", $requestData);
        $this->targetAddressId = $this->dataHelper->getValueFromArray("targetId", $requestData);
        $this->targetCustomerId = $this->dataHelper->getValueFromArray("targetCustomerId", $requestData);
        $this->messageId = $this->dataHelper->getValueFromArray("messageId", $requestData);
        $this->entityCode = $entityCode;

        if ($address = $this->genericHelper->getCustomerAddressById($this->addressId)) {
            if (isset($erpCode)) {
                $this->erpCode = $erpCode;
            } else {
                $this->erpCode = __("ERP");
            }

            if (!empty($this->messageId)) {
                $this->saveDataInOutboundMQ($address);
            }

            $customerId = isset($this->customer['id']) ? $this->customer['id'] : null;
            $this->addressInterface = $this->addressInterfaceFactory->create();
            $this->addressInterface->setId($this->addressId);
            $this->addressInterface->setCustomerId($customerId);
            $this->addressInterface->setCustomAttribute('target_address_id', $this->targetAddressId);

            $this->addressRepository->create()->save($this->addressInterface);
            $addressResponseEvent = "erpconnect_forward_addressresponse";
            $this->eventManager->dispatch($addressResponseEvent, ['currentObject' => $this]);
            return true;
        }

        return false;
    }
}
