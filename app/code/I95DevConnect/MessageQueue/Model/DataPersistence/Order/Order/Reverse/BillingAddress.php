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
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for preparing shipping address while creating order
 */
class BillingAddress extends AbstractOrder
{
    public const I95EXC = 'i95devApiException';
    public const FIRSTNAME = "firstName";
    public const LASTNAME = "lastName";
    public const COUNTRYID = "countryId";
    public const STREET = "street";
    public const POSTCODE = "postcode";
    public const TELEPHONE = "telephone";
    public const REGIONID = "regionId";

    /**
     * @var object
     */
    public $billingAddress;

    /**
     * @var AddressFactory
     */
    public $customerAddressModel;

    /**
     * @var int
     */
    public $existingAddress = 0;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var RegionFactory
     */
    public $regionDirectory;

    /**
     * @var int
     */
    public $regionId;

    /**
     * @var string
     */
    public $region;

    /**
     * @var int
     */
    public $countryId;

    /**
     * @var string[]
     */
    public $validateFields = [
        self::FIRSTNAME => 'i95dev_addr_002',
        self::LASTNAME => 'i95dev_addr_003',
        self::COUNTRYID => 'i95dev_addr_004',
        'city' => 'i95dev_addr_006',
        self::STREET => 'i95dev_addr_007',
        self::POSTCODE => 'i95dev_addr_008',
        self::TELEPHONE => 'i95dev_addr_009',
    ];

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     *
     * @param Data $dataHelper
     * @param LoggerInterfaceFactory $logger
     * @param Generic $genericHelper
     * @param AddressFactory $customerAddressModel
     * @param RegionFactory $regionDirectory
     * @param Validate $validate
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $dataHelper,
        LoggerInterfaceFactory $logger,
        Generic $genericHelper,
        AddressFactory $customerAddressModel,
        RegionFactory $regionDirectory,
        Validate $validate,
        StoreManagerInterface $storeManager
    ) {
        $this->customerAddressModel = $customerAddressModel;
        $this->dataHelper = $dataHelper;
        $this->regionDirectory = $regionDirectory;
        $this->storeManager = $storeManager;

        parent::__construct(
            $logger,
            $genericHelper,
            $validate
        );
    }

    /**
     * Validate request billing address data
     *
     * @param array $stringData
     * @throws LocalizedException
     * @author Divya Koona. Added region address validation logic.
     */
    public function validateData($stringData)
    {
        $this->stringData = $stringData;
        $regionDetails = [];
        $this->billingAddress = $this->dataHelper->getValueFromArray("billingAddress", $this->stringData);
        $targetBillingAddressId = $this->dataHelper->getValueFromArray("targetId", $this->billingAddress);
        $component = $this->dataHelper->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
        if ($component == 'AX' && $targetBillingAddressId == '') {
                throw new LocalizedException(__('i95dev_addr_001'));
            }
        if (($component == 'AX' || $component == 'D365FO') && $targetBillingAddressId != null) {
            $this->billingAddress = $this->genericHelper->getAddressByTargetAddressId(
                $targetBillingAddressId,
                $this->currentObject->customer->getId()
            );
            $regionDetails['region_id'] = $this->billingAddress['region_id'];
            $regionDetails['default_name'] = $this->billingAddress['region'];
            $this->countryId = $this->billingAddress['country_id'];
        } else {
            $this->validate->validateFields = $this->validateFields;
            $this->validate->validateData($this->billingAddress);
            $this->countryId = $this->billingAddress[self::COUNTRYID];
            $regionDetails = $this->dataHelper->getRegionDetails(
                $this->billingAddress[self::REGIONID],
                $this->billingAddress[self::COUNTRYID]
            );
        }

        if (!empty($regionDetails)) {
            $this->regionId = isset($regionDetails[self::REGIONID]) ?
                $regionDetails[self::REGIONID] : $regionDetails['region_id'];
            $this->region = $regionDetails['default_name'];
        } else {
            $stateRequiredCountries = $this->dataHelper->getscopeConfig(
                'general/region/state_required',
                ScopeInterface::SCOPE_WEBSITE
            );
            $countriesList = explode(',', $stateRequiredCountries);
            if (in_array($this->billingAddress[self::COUNTRYID], $countriesList)) {
                $message = ($this->billingAddress[self::REGIONID] == '')
                    ? __('i95dev_addr_005') : __('i95dev_addr_014');

                $this->logger->create()->createLog(__METHOD__, $message, self::I95EXC, 'critical');
                throw new LocalizedException(__($message));
            } else {
                $regionCode = $this->getRegionCode();
            }

            $this->regionId = 0;
            $this->region = $regionCode;
        }
    }

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        $regionList = $this->regionDirectory->create()->getCollection()
            ->addFieldToFilter("country_id", $this->billingAddress[self::COUNTRYID]);
        $regionList->getSelect()->limit(1);

        $regionList = $regionList->getData();
        return (!empty($regionList)) ? "" : $this->billingAddress[self::REGIONID];
    }

    /**
     * Prepare billing address which to be added in quote.
     *
     * @return array
     * @throws LocalizedException
     * @author Divya Koona. Removed region address validation logic.
     */
    public function addBillingAddress()
    {
        try {
            $firstname = isset($this->billingAddress[self::FIRSTNAME]) ?
                    $this->billingAddress[self::FIRSTNAME] : $this->billingAddress['firstname'];
            $lastname = isset($this->billingAddress[self::LASTNAME]) ?
                    $this->billingAddress[self::LASTNAME] : $this->billingAddress['lastname'];
            $customer_address_id = isset($this->billingAddress['entity_id']) ?
                    $this->billingAddress['entity_id'] : null;
            $street1 = $this->billingAddress[self::STREET];
            $street = isset($this->billingAddress['street2']) ?
                    $street1 . ", " . $this->billingAddress['street2'] : $street1;
            return [
                'address_type' => 'billing',
                'city' => $this->billingAddress['city'],
                'country_id' => $this->countryId,
                'email' => $this->currentObject->customer->getEmail(),
                'firstname' => $firstname,
                'lastname' => $lastname,
                self::POSTCODE => $this->billingAddress[self::POSTCODE],
                'region_id' => $this->regionId,
                'region' => $this->region,
                'customer_address_id' => $customer_address_id,
                self::STREET => $street,
                self::TELEPHONE => $this->billingAddress[self::TELEPHONE],
            ];
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}
