<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Reverse;

use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\AbstractOrder;
use I95DevConnect\MessageQueue\Model\DataPersistence\Validate;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for preparing shipping address while creating order
 */
class ShippingAddress extends AbstractOrder
{
    public const I95EXC = 'i95devApiException';
    public const COUNTRYID = "countryId";
    public const REGIONID = "regionId";
    public const CRICTICAL = "critical";

    /**
     * @var array
     */
    public $shippingAddress;

    /**
     * @var AddressFactory
     */
    public $customerAddressModel;

    /**
     * @var Config
     */
    public $shipconfig;

    /**
     * @var RegionFactory
     */
    public $regionDirectory;

    /**
     * @var int
     */
    public $regionId;

    /**
     * @var array
     */
    public $region;

    protected $storeManager;

    /**
     * @var string[]
     */
    public $validateFields = [
        'firstName' => 'i95dev_addr_002',
        'lastName' => 'i95dev_addr_003',
        self::COUNTRYID => 'i95dev_addr_004',
        'city' => 'i95dev_addr_006',
        'street' => 'i95dev_addr_007',
        'postcode' => 'i95dev_addr_008',
        'telephone' => 'i95dev_addr_009'
    ];

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var  AddressInterface
     */
    public $quoteshippingAddressInterface;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     *
     * @param Data $dataHelper
     * @param AddressFactory $customerAddressModel
     * @param LoggerInterfaceFactory $logger
     * @param Generic $genericHelper
     * @param Config $shipconfig
     * @param RegionFactory $regionDirectory
     * @param AddressInterface $quoteshippingAddressInterface
     * @param Validate $validate
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct( // NOSONAR
        Data $dataHelper,
        AddressFactory $customerAddressModel,
        LoggerInterfaceFactory $logger,
        Generic $genericHelper,
        Config $shipconfig,
        RegionFactory $regionDirectory,
        AddressInterface $quoteshippingAddressInterface,
        Validate $validate,
        ScopeConfigInterface $scopeConfig,
         StoreManagerInterface $storeManager
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerAddressModel = $customerAddressModel;
        $this->shipconfig = $shipconfig;
        $this->regionDirectory = $regionDirectory;
        $this->quoteshippingAddressInterface = $quoteshippingAddressInterface;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

        parent::__construct(
            $logger,
            $genericHelper,
            $validate
        );
    }

    /**
     * Validate request shipping information data
     *
     * @param array $stringData
     * @throws LocalizedException
     * @author Divya Koona. Removed shipping method validation from shipping address and validated from header
     */
    public function validateData($stringData)
    {
        $this->stringData = $stringData;
        $this->shippingAddress = $this->dataHelper->getValueFromArray("shippingAddress", $this->stringData);
        $this->validate->validateFields = $this->validateFields;
        $this->validate->validateData($this->shippingAddress);
        $regionDetails = $this->dataHelper->getRegionDetails(
            $this->shippingAddress[self::REGIONID],
            $this->shippingAddress[self::COUNTRYID]
        );

        if (!empty($regionDetails)) {
            $this->regionId = $regionDetails['region_id'];
            $this->region = $regionDetails['default_name'];
        } else {
            $stateRequiredCountries = $this->dataHelper->getscopeConfig(
                'general/region/state_required',
                ScopeInterface::SCOPE_WEBSITE
            );
            $countriesList = explode(',', $stateRequiredCountries);
            if (in_array($this->shippingAddress[self::COUNTRYID], $countriesList)) {
                $message = ($this->shippingAddress[self::REGIONID] == '') ?
                    __('i95dev_addr_005') : __('i95dev_addr_014');

                $this->logger->create()->createLog(__METHOD__, $message, self::I95EXC, self::CRICTICAL);
                throw new LocalizedException(
                    __($message),
                    null,
                    104
                );
            } else {
                $regionList = $this->regionDirectory->create()->getCollection()
                    ->addFieldToFilter('country_id', $this->shippingAddress[self::COUNTRYID]);

                $regionList->getSelect()->limit(1);

                $regionList = $regionList->getData();
                $regionCode = (!empty($regionList)) ? "" : $this->shippingAddress[self::REGIONID];
            }

            $this->regionId = 0;
            $this->region = $regionCode;
        }

        //$activeMethods = $this->getActiveShippingMethods();
        $websiteId = $this->getWebsiteId($this->stringData);
        $activeMethods = $this->getActiveShippingMethodsByWebsiteCode($websiteId);
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/i95devConnector_debug'.date("Y-m-d").'.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Active Shipping Methods: " . json_encode($activeMethods));
        $isEditOrder = $this->dataHelper->getValueFromArray("isEditOrder", $stringData);
        if ($isEditOrder === true){
            return true;
        }
        if (!empty($activeMethods)) {
            $shipping_code = $this->dataHelper->getValueFromArray("shippingMethod", $stringData);
            if (!in_array($shipping_code, $activeMethods)) {
                throw new LocalizedException(
                    __("i95dev_order_021"),
                    null,
                    109
                );
            }
        } else {
            throw new LocalizedException(
                __("i95dev_quote_all_shippingMethod_active"),
                null,
                109
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

    /**
     * Get Active Shipping Methods
     *
     * @return array $methods
     * @throws LocalizedException
     * @author Divya Koona
     */
    public function getActiveShippingMethods()
    {
        $methods = [];
        try {
            $activeCarriers = $this->shipconfig->getActiveCarriers();
            foreach ($activeCarriers as $carrierCode => $carrierModel) {
                if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                    foreach ($carrierMethods as $methodCode => $method) {
                        $code = $carrierCode . '_' . $methodCode;
                        $methods[] = $code;
                    }
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRICTICAL);
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $methods;
    }

    public function getActiveShippingMethodsByWebsiteCode($websiteId)
    {
        $website = $this->storeManager->getWebsite($websiteId);
        $defaultStore = $website->getDefaultStore();

        $activeMethods = [];
        $allCarriers = $this->shipconfig->getAllCarriers($defaultStore);

        foreach ($allCarriers as $carrierCode => $carrierModel) {
            $isActive = $this->scopeConfig->isSetFlag(
                "carriers/{$carrierCode}/active",
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );

            if ($isActive) {
                if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                    foreach ($carrierMethods as $methodCode => $method) {
                        $code = $carrierCode . '_' . $methodCode;
                        $methods[] = $code;
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * Prepare Quote Address
     *
     * @return AddressInterface $address
     * @throws LocalizedException
     * @author Divya Koona. Removed region validation logic
     */
    public function addShippingAddress()
    {
        try {
            $street2 = $this->shippingAddress['street2'] ?? '';
            $street = [$this->shippingAddress['street'], $street2];
            $address = $this->quoteshippingAddressInterface;
            $address->setCustomerId($this->currentObject->customer->getId());
            $address->setFirstname($this->shippingAddress['firstName']);
            $address->setLastname($this->shippingAddress['lastName']);
            $address->setEmail($this->currentObject->customer->getEmail());
            $address->setRegionId($this->regionId);
            $address->setRegion($this->region);
            $address->setPostcode($this->shippingAddress['postcode']);
            $address->setStreet(implode(", ", $street));
            $address->setCity($this->shippingAddress['city']);
            $address->setTelephone($this->shippingAddress['telephone']);
            $address->setCountryId($this->shippingAddress[self::COUNTRYID]);
            $address->setAddressType("shipping");
            return $address;
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, self::CRICTICAL);
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}
