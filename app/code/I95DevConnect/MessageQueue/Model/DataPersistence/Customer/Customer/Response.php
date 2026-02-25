<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed getCustomerById function and added in Generic Helper.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Customer;

use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class responsible for saving erp responses in customer
 */
class Response
{
    public const SKIPOBRVR = "i95_observer_skip";
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
     * @var int
     */
    public $customerId;
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
     * @var Address
     */
    public $address;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var string
     */
    public $stringData;
    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    public $customerRepository;
    /**
     * @var CustomerInterfaceFactory
     */
    public $customerInterfaceFactory;
    /**
     * @var Generic
     */
    public $genericHelper;
    /**
     * @var array
     */
    public $customerInterface;
    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;
    /**
     *
     * @var LoggerInterface
     */
    public $logger;

    /**
     *
     * @param Data $dataHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository
     * @param I95DevMagMQInterfaceFactory $I95DevMagMQData
     * @param Manager $eventManager
     * @param Address $address
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterfaceFactory $customerRepository
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param Generic $genericHelper
     * @param LoggerInterface $logger
     * @param AbstractDataPersistence $abstractDataPersistence
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQRepository,
        I95DevMagMQInterfaceFactory $I95DevMagMQData,
        Manager $eventManager,
        Address $address,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterfaceFactory $customerRepository,
        CustomerInterfaceFactory $customerInterfaceFactory,
        Generic $genericHelper,
        LoggerInterface $logger,
        AbstractDataPersistence $abstractDataPersistence
    ) {
        $this->dataHelper = $dataHelper;
        $this->I95DevMagMQRepository = $I95DevMagMQRepository;
        $this->I95DevMagMQData = $I95DevMagMQData;
        $this->eventManager = $eventManager;
        $this->address = $address;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->genericHelper = $genericHelper;
        $this->logger = $logger;
        $this->abstractDataPersistence = $abstractDataPersistence;
    }

    /**
     * Sets target customer details in customer
     *
     * @param array $requestData
     * @param string $entityCode
     * @param string $erpCode
     *
     * @return I95DevResponseInterface author Divya Koona.
     * Removed Magento REST API calls and converted to interface code.
     * author Divya Koona. Removed Magento REST API calls and converted to interface code.
     */
    public function getResponse($requestData, $entityCode, $erpCode)
    {
        try {
            $this->stringData = $requestData;
            $this->customerId = $this->dataHelper->getValueFromArray("sourceId", $requestData);
            $this->targetId = $this->dataHelper->getValueFromArray("targetId", $requestData);
            $this->entityCode = $entityCode;
            $inputData = [];
            $customer = $this->genericHelper->getCustomerById($this->customerId);
            if (isset($customer['id'])) {
                $this->erpCode = isset($erpCode) ? $erpCode : __("ERP");

                $customerDataInterface = $this->prepareCustomerDataInterface($customer);
                $this->dataHelper->unsetGlobalValue(self::SKIPOBRVR);
                $this->dataHelper->setGlobalValue(self::SKIPOBRVR, true);
                $result = $this->customerRepository->create()->save($customerDataInterface);
                $addresses = null;
                if (isset($requestData['inputData'])) {
                    $addresses = $this->dataHelper->getValueFromArray("addresses", $requestData['inputData']);
                }

                if (!empty($addresses)) {
                    $addressRes = $this->getAddressResponse($addresses, $customer, $erpCode);
                    if (!empty($addressRes)) {
                        $inputData[] = $addressRes;
                    }
                }

                $customerResponseEvent = "erpconnect_forward_customerresponse";
                $this->eventManager->dispatch($customerResponseEvent, ['currentObject' => $this]);
                $this->dataHelper->unsetGlobalValue(self::SKIPOBRVR);
                return is_object($result) ? $this->abstractDataPersistence->setResponse(
                    Data::SUCCESS,
                    __("Response send successfully"),
                    $inputData
                ) : $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Some error occured in response sync"),
                    null,
                    105
                );
            } else {
                return $this->abstractDataPersistence->setResponse(
                    Data::ERROR,
                    __("Customer response is invalid to sync"),
                    null,
                    105
                );
            }
        } catch (LocalizedException $ex) {
            /** @author Hrusieksh Manna. Returning error message instead of throwing exception,
             * as expected by the calling function. **/
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
            $message = "Customer Not Found :: " . $this->customerId;
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($message),
                null,
                108
            );
        }
    }

    /**
     * Prepare customer data interface
     *
     * @param array $customer
     * @return CustomerInterface
     * @createdBy Divya Koona
     */
    public function prepareCustomerDataInterface($customer)
    {
        $this->customerInterface = $this->customerInterfaceFactory->create();
        $this->customerInterface->setId($this->customerId);
        $this->customerInterface->setEmail($customer['email']);
        $this->customerInterface->setFirstname($customer['firstname']);
        $this->customerInterface->setLastname($customer['lastname']);
        // $this->customerInterface->setWebsiteId($this->storeManager->getDefaultStoreView()->getWebsiteId());
        $this->customerInterface->setGroupId($customer['group_id']);
        $this->customerInterface->setCustomAttribute('target_customer_id', $this->targetId);
        $this->customerInterface->setCustomAttribute('update_by', $this->erpCode);
        foreach ($customer['custom_attributes'] as $attribute) {
            $attributeCode = $attribute['attribute_code'];
            $value = $attribute['value'];
            $this->customerInterface->setCustomAttribute($attributeCode, $value);
        }
        return $this->customerInterface;
    }

    /**
     * Get Address response
     *
     * @param array $addresses
     * @param array $customer
     * @param string $erpCode
     * @return bool|mixed
     * @throws LocalizedException
     */
    public function getAddressResponse($addresses, $customer, $erpCode)
    {
        $inputData = [];

        foreach ($addresses as $addressResquest) {
            foreach ($customer['addresses'] as $address) {
                if (isset($address['id']) &&
                    $address['id'] == $this->dataHelper->getValueFromArray("sourceId", $addressResquest)
                ) {
                    $addressReq = $addressResquest;
                    $addressReq['customer'] = $customer;
                    $addressResponse = $this->address->setAddressResponse($addressReq, "Address", $erpCode);
                    if ($addressResponse) {
                        $inputData = $addressResquest;
                    }
                }
            }
        }

        return $inputData;
    }
}
