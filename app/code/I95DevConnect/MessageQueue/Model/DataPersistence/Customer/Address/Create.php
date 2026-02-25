<?php

/** @noinspection PhpInconsistentReturnPointsInspection */

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed Magento REST API calls and converted to interface code.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address;

use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\ServiceRequest;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Customer\Api\AddressRepositoryInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for creating customer address in magento
 */
class Create
{
    public const VALUE = 'value';
    public const COUNTRY_ID = 'country_id';
    public const COUNTRYID = 'countryId';
    public const TELEPHONE = 'telephone';
    public const CRITICAL = 'critical';
    public const REGIONID = 'regionId';
    public const I95EXC = 'i95devApiException';
    /**
     * @var ServiceRequest
     */
    public $requestHelper;
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
     * @var LoggerInterfaceFactory
     */
    public $logger;
    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;
    /**
     * @var array
     */
    public $postData;
    /**
     * @var array
     */
    public $resutlData;
    /**
     * @var string
     */
    public $stringData;
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
     * @var string
     */
    public $targetFieldErp = "targetId";
    /**
     * @var string[]
     */
    public $validateFields = [
        'targetId' => 'i95dev_addr_001',
        'firstName' => 'i95dev_addr_002',
        'lastName' => 'i95dev_addr_003',
        self::COUNTRYID => 'i95dev_addr_004',
        'city' => 'i95dev_addr_006',
        'street' => 'i95dev_addr_007',
        'postcode' => 'i95dev_addr_008',
        'telephone' => 'i95dev_addr_009'
    ];
    /**
     * @var RegionFactory
     */
    public $regionDirectory;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    public $customerRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;
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
     * @var array
     */
    public $existingAddress;
    /**
     * @var RegionInterfaceFactory
     */
    public $regionInterfaceFactory;
    /**
     * @var array
     */
    public $regionInterface;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var string
     */
    public $addressResponse;

    /**
     *
     * @param LoggerInterfaceFactory $logger
     * @param Manager $eventManager
     * @param Validate $validate
     * @param Data $dataHelper
     * @param ServiceRequest $requestHelper
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param RegionFactory $regionDirectory
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterfaceFactory $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AddressRepositoryInterfaceFactory $addressRepository
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param RegionInterfaceFactory $regionInterfaceFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct( // NOSONAR
        LoggerInterfaceFactory $logger,
        Manager $eventManager,
        Validate $validate,
        Data $dataHelper,
        ServiceRequest $requestHelper,
        AbstractDataPersistence $abstractDataPersistence,
        RegionFactory $regionDirectory,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterfaceFactory $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterfaceFactory $addressRepository,
        AddressInterfaceFactory $addressInterfaceFactory,
        RegionInterfaceFactory $regionInterfaceFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->requestHelper = $requestHelper;
        $this->dataHelper = $dataHelper;
        $this->eventManager = $eventManager;
        $this->validate = $validate;
        $this->logger = $logger;
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->regionDirectory = $regionDirectory;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Create customer address.
     *
     * @param array $stringData
     * @param string $entityCode
     * @return I95DevResponseInterface
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function createAddress($stringData, $entityCode)
    {
        $this->stringData = $this->getEntityData($stringData);

        try {
            $this->validate->validateFields = $this->validateFields;

            if ($this->validate->validateData($this->stringData)) {
                $this->validateData();
                $targetCustomerId = $this->dataHelper->getValueFromArray("targetCustomerId", $this->stringData);
                $this->targetAddressId = $this->dataHelper->getValueFromArray($this->targetFieldErp, $this->stringData);
                if ($this->getExistingCustomerData($targetCustomerId)) {
                    $this->addAddressData();
                    /* updatedBy Ranjith R, added the before save event dispatch */
                    $beforeeventname = 'erpconnect_messagequeuetomagento_beforesave_' . $entityCode;
                    $this->eventManager->dispatch($beforeeventname, ['currentObject' => $this]);
                    $this->dataHelper->unsetGlobalValue('i95_observer_skip');
                    $this->dataHelper->setGlobalValue('i95_observer_skip', true);
                    $result = $this->addressRepository->create()->save($this->addressInterface);
                    $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_' . $entityCode;
                    $this->eventManager->dispatch($aftereventname, ['currentObject' => $this]);
                    if (is_object($result)) {
                        $this->responseId = $result->getId();
                        return $this->abstractDataPersistence->setResponse(
                            Data::SUCCESS,
                            __("i95dev_addr_017"),
                            $this->responseId
                        );
                    } else {
                        throw new LocalizedException(
                            __('i95dev_addr_018'),
                            null,
                            105
                        );
                    }
                } else {
                    throw new LocalizedException(
                        __('i95dev_addr_019'),
                        null,
                        105
                    );
                }
            }
        } catch (LocalizedException $ex) {
            return $this->abstractDataPersistence->setResponse(
                Data::ERROR,
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
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
     * Validates address data from ERP
     *
     * @return boolean
     * @throws LocalizedException
     */
    public function validateData()
    {
        $msg = [];
        $countryId = $this->dataHelper->getValueFromArray(self::COUNTRYID, $this->stringData);
        $regionId = $this->dataHelper->getValueFromArray(self::REGIONID, $this->stringData);
        $telephoneNumber = $this->dataHelper->getValueFromArray(self::TELEPHONE, $this->stringData);
        $stateRequiredCountries = $this->scopeConfig->getValue(
            'general/region/state_required',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );

        $countriesList = explode(',', $stateRequiredCountries);
        if (in_array($countryId, $countriesList)) {
            if ($regionId == '') {
                $msg[] = __('i95dev_addr_005');
            } else {
                $regionList = $this->regionDirectory->create()->getCollection()
                    ->addFieldToFilter(self::COUNTRY_ID, $countryId);
                $regionList->getSelect()->limit(1);
                $regionList = $regionList->getData();

                if (!empty($regionList)) {
                    $region = $this->regionDirectory->create()->getCollection()
                        ->addFieldToFilter(self::COUNTRY_ID, $countryId)
                        ->addFieldToFilter('code', $regionId);
                    $region->getSelect()->limit(1);

                    $region = $region->getData();
                    if (empty($region)) {
                        $msg[] = __('i95dev_addr_014');
                    }
                }
            }
        }

        if (empty($telephoneNumber)) {
            $msg[] = __('i95dev_addr_009');
        }

        if (!empty($msg)) {
            $message = implode(', ', $msg);
            throw new LocalizedException(
                __($message),
                null,
                104
            );
        }

        return true;
    }

    /**
     * Search the customer based on targetAddressId. If not found throw 'Customer not exists ' error.
     *
     * @param string $targetCustomerId
     * @return boolean
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function getExistingCustomerData($targetCustomerId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('target_customer_id', $targetCustomerId, 'eq')
                ->create();
            $searchResults = $this->customerRepository->create()->getList($searchCriteria);
            $customerInfo = $searchResults->getItems();
            if (!empty($customerInfo)) {
                if (isset($customerInfo[0])) {
                    $customerData = $customerInfo[0];
                    $this->customerId = $customerData->getId();
                    $this->postData = ['customer' => $customerData];
                } else {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Identifies new/existing address
     *
     * @return void
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function addAddressData()
    {
        if ($this->isNewAddress()) {
            $this->addNewAddress();
        } else {
            $this->updateExistingAddress();
        }
    }

    /**
     * Check whether the ERP given address is a new address r not.
     *
     * It it is an existing address that address will be updated.
     *
     * @return boolean
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function isNewAddress()
    {
        try {
            foreach ($this->postData['customer']->getAddresses() as $address) {
                if (!empty($address->getCustomAttributes())) {
                    foreach ($address->getCustomAttributes() as $addressCusttomAttr) {
                        if (($addressCusttomAttr->getAttributeCode() == 'target_address_id') &&
                            ($addressCusttomAttr->getValue() == $this->targetAddressId)
                        ) {
                            $this->existingAddress = $address;
                            return false;
                        }
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return true;
    }

    /**
     * Add new address to existing customer.
     *
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function addNewAddress()
    {
        try {
            $this->addressInterface = $this->addressInterfaceFactory->create();
            $this->prepareAddressInterface();
            $this->addressInterface->setCustomAttribute(
                'target_address_id',
                $this->dataHelper->getValueFromArray($this->targetFieldErp, $this->stringData)
            );
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Prepares address interface with the provided data
     *
     * @createdBy Divya Koona
     */
    public function prepareAddressInterface()
    {
        $erpRegionId = $this->dataHelper->getValueFromArray(self::REGIONID, $this->stringData);
        $countryId = $this->dataHelper->getValueFromArray(self::COUNTRYID, $this->stringData);
        $regionDetails = $this->getRegionDetails($erpRegionId, $countryId);

        $this->addressInterface->setCustomerId($this->customerId);
        $this->addressInterface->setPrefix($this->dataHelper->getValueFromArray("prefix", $this->stringData));
        $this->addressInterface->setSuffix($this->dataHelper->getValueFromArray("suffix", $this->stringData));
        $this->addressInterface->setMiddlename($this->dataHelper->getValueFromArray("middleName", $this->stringData));
        $this->addressInterface->setFirstname($this->dataHelper->getValueFromArray("firstName", $this->stringData));
        $this->addressInterface->setLastname($this->dataHelper->getValueFromArray("lastName", $this->stringData));
        $this->addressInterface->setCity($this->dataHelper->getValueFromArray("city", $this->stringData));
        $this->addressInterface->setTelephone($this->dataHelper->getValueFromArray(self::TELEPHONE, $this->stringData));
        $this->addressInterface->setPostcode($this->dataHelper->getValueFromArray("postcode", $this->stringData));

        $street = [];
        $street[0] = $this->dataHelper->getValueFromArray("street", $this->stringData);
        $street2 = $this->dataHelper->getValueFromArray("street2", $this->stringData);
        $street[1] = isset($street2) ? $street2 : '';

        $this->addressInterface->setStreet($street);

        $this->regionInterface = $this->regionInterfaceFactory->create();
        if (!empty($regionDetails)) {
            $regionId = $this->dataHelper->getValueFromArray("region_id", $regionDetails);
            $this->regionInterface->setRegionCode($this->dataHelper->getValueFromArray("code", $regionDetails));
            $this->regionInterface->setRegionId($regionId);
            $this->regionInterface->setRegion($this->dataHelper->getValueFromArray("name", $regionDetails));
            $this->addressInterface->setRegion($this->regionInterface);
            $this->addressInterface->setRegionId($regionId);
        } else {
            $regionList = $this->regionDirectory->create()->getCollection()
                ->addFieldToFilter(self::COUNTRY_ID, $countryId);
            $regionList->getSelect()->limit(1);
            $regionList = $regionList->getData();
            if (!empty($regionList)) {
                $regionCode = "";
            } else {
                $regionCode = $this->dataHelper->getValueFromArray(self::REGIONID, $this->stringData);
            }

            $this->regionInterface->setRegionCode($regionCode);
            $this->regionInterface->setRegionId(0);
            $this->regionInterface->setRegion($regionCode);
            $this->addressInterface->setRegion($this->regionInterface);
            $this->addressInterface->setRegionId(0);
        }

        $this->addressInterface->setCountryId($countryId);
        if (trim($this->dataHelper->getValueFromArray("isDefaultBilling", $this->stringData) ?? '')) {
            $this->addressInterface->setIsDefaultBilling(true);
        }
        if (trim($this->dataHelper->getValueFromArray("isDefaultShipping", $this->stringData) ?? '')) {
            $this->addressInterface->setIsDefaultShipping(true);
        }
    }

    /**
     * Returns region details based on given region code and country code
     *
     * @param string $regionCode
     * @param string $countryCode
     * @return array
     */
    public function getRegionDetails($regionCode, $countryCode)
    {
        return $this->dataHelper->getRegionDetails($regionCode, $countryCode);
    }

    /**
     * Update changes in the existing address of the customer.
     *
     * @throws LocalizedException
     * @updatedBy Divya Koona
     */
    public function updateExistingAddress()
    {
        try {
            $this->addressInterface = $this->addressInterfaceFactory->create();
            $this->addressInterface->setId($this->existingAddress->getId());
            $this->prepareAddressInterface();
            $this->responseId = $this->existingAddress->getId();
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRITICAL);
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Set ERP responses in address
     *
     * @param type $requestData
     * @param type $entityCode
     * @param type $erpCode
     */
    public function setAddressResponse($requestData, $entityCode, $erpCode = null)
    {
        $this->addressResponse->setAddressResponse($requestData, $entityCode, $erpCode);
    }
}
