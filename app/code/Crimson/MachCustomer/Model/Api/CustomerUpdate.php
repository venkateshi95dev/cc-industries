<?php

namespace Crimson\MachCustomer\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Service\CustomerPreventMachDataProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Crimson\MachCustomer\Model\Service\GetMachCustomerNumber;
use Crimson\MachBase\Model\MachConfig;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class CustomerUpdate
 * @package Crimson\MachCustomer\Model\Api
 */
class CustomerUpdate extends AbstractApi
{
    /** @var HealthCheck $_healthCheck */
    protected $_healthCheck;

    /** @var AddressFactory $_addressFactory */
    protected $_addressFactory;

    /** @var CollectionFactory $_regionCollectionFactory */
    protected $_regionCollectionFactory;

    /** @var CustomerRepositoryInterface $_customerRepository */
    protected $_customerRepository;

    /** @var AddressRepositoryInterface $_addressRepository */
    protected $_addressRepository;

    /** @var Data $_machCustomerHelper */
    protected $_machCustomerHelper;

    /** @var \Magento\Newsletter\Model\Subscriber $_subscriber */
    protected $_subscriber;

    /** @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $_customerGroupFactory */
    protected $_customerGroupFactory;

    /** @var CustomerPreventMachDataProvider $_customerPreventMachDataProvider */
    protected $_customerPreventMachDataProvider;

    /** @var CustomerInterface $customerInterface */
    protected $customerInterface;

    /** @var AddressInterfaceFactory $addressInterfaceFactory */
    protected $addressInterfaceFactory;

    /** @var RegionInterfaceFactory $addressInterfaceFactory */
    protected $regionInterfaceFactory;

    /**
     * @var GetMachCustomerNumber
     */
    protected $getMachCustomerNumber;

    /**
     * Customer constructor.
     *
     * @param ApiContext $apiContext
     * @param HealthCheck $healthCheck
     * @param SubscriberFactory $subscriberFactory
     * @param AddressFactory $addressFactory
     * @param CollectionFactory $regionCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param Data $machCustomerHelper
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupFactory
     */
    public function __construct(
        ApiContext $apiContext,
        HealthCheck $healthCheck,
        AddressFactory $addressFactory,
        CollectionFactory $regionCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        Data $machCustomerHelper,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupFactory,
        CustomerPreventMachDataProvider $customerPreventMachDataProvider,
        CustomerInterface $customerInterface,
        AddressInterfaceFactory $addressInterfaceFactory,
        RegionInterfaceFactory $regionInterfaceFactory,
        GetMachCustomerNumber $getMachCustomerNumber,
        MachConfig $machConfig,
        Subscriber $subscriberResource
    ) {
        $this->_healthCheck = $healthCheck;
        $this->_addressFactory = $addressFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_addressRepository = $addressRepository;
        $this->_machCustomerHelper = $machCustomerHelper;
        $this->_subscriber = $subscriber;
        $this->_customerGroupFactory = $customerGroupFactory;
        $this->_customerPreventMachDataProvider = $customerPreventMachDataProvider;
        $this->customerInterface = $customerInterface;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->getMachCustomerNumber = $getMachCustomerNumber;
        parent::__construct($apiContext,$machConfig,$subscriberResource);
    }

    /**
     * @param $customer
     * @param bool $saveToCustomer
     * @return $this|array
     * @throws LocalizedException|\Zend_Date_Exception
     */
    public function customerInfo(CustomerInterface $customer, $saveToCustomer = false)
    {
        //look for mach customer number if it is not present.
        if(empty($customer->getCustomAttribute('mach_customer_number'))){
            $customerNumber = $this->getMachCustomerNumber->get($customer);
        }else{
            $customerNumber = $customer->getCustomAttribute('mach_customer_number')
                ? $customer->getCustomAttribute('mach_customer_number')->getValue()
                : "";
        }

        if(empty($customer->getCustomAttribute('mach_pin'))){
            $customerPin    = $this->_machCustomerHelper->getMachCustomerPin($customer);
        }else{
            $customerPin    = $customer->getCustomAttribute('mach_pin')
                ? $customer->getCustomAttribute('mach_pin')->getValue()
                : "";
        }

        $this->debugLog('Customer Data: '.json_encode($customer->__toArray()));

        if (!$this->_healthCheck->isUp()) {
            $this->scheduleCustomerUpdate($customer);
            return $this;
        }

        $action = self::CALL_CUST_INFO;
        $this->debugLog('Beginning ' . $action . ' Call');
        $status = false;
        $result = array();

        try
        {
            /*
             * 1 - Customer Number Only Lookup
             * 2 - Customer Number and Zip Code Lookup
             * 3 - Email Address and PIN Lookup
             * 4 – Lookups
             */
            if ($customerNumber){
                $actionCode = 1;
            }
            elseif ($customerPin){
                $actionCode     = 3;
                $customerNumber = null;
            }
            else{
                //we have no information.
                $this->debugLog('Unable to Update the Customer: We have no information.');
                return array();
            }

            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                $this->_soapVar($actionCode, 'ActionCode'),
                $this->_soapVar($customerNumber, 'CustNumberIn'),
            );

            $customerInfoIn = array(
                $this->_soapVar(null, 'CustZip'),
                $this->_soapVar($customer->getEmail(), 'CustEmail'),
                $this->_soapVar($customerPin, 'CustPIN'),
                $this->_soapVar(null, 'CustLookup1'),
                $this->_soapVar(null, 'CustLookup2'),
                $this->_soapVar(null, 'CustLookup3'),
                $this->_soapVar(null, 'UserIn1'),
                $this->_soapVar(null, 'UserIn2'),
                $this->_soapVar(null, 'UserIn3'),
                $this->_soapVar(null, 'UserIn4'),
                $this->_soapVar(null, 'UserIn5'),
            );

            $arguments[] = $this->_soapVar($customerInfoIn, 'CUST_INFO_IN');
            $response    = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);

            $this->debugLog('Customer Info from Mach Response: '.json_encode($response));

            if (!isset($response->ERROR_OUT->ErrorNumber)) {
                throw new \Exception('Invalid response received, unable to determine if customer info exists.');
            }
            else {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber)) {
                    $message = 'Error occurred attempting to retrieve customer info from MACH ERP.<br/>';
                    $message .= 'Error Number: %1%s. Returned Message: %2$s <br/>';
                    $message .= 'Customer: <a href="%3$s" target="_blank">%4$s</a>';
                    $customerLink = $this->helper->buildUrl(
                        'adminhtml/customer/edit/', array('id' => $customer->getId())
                    );
                    $message      = sprintf(
                        $message, $errorNumber, $errorMessage, $customerLink, $customer->getEmail()
                    );

                    $this->infoLog($message);

                    throw new \Exception($message);
                }
                else {
                    $machCustomerNumber = $response->CustNumberOut;
                    $customerData       = $response->CUST_INFO_OUT;

                    $result = array(
                        'mach_customer_number' => $machCustomerNumber,
                        'name'                 => $customerData->CustName,
                        'street'               => array(
                            $customerData->CustAdd1,
                            $customerData->CustAdd2,
                        ),
                        'city'                 => $customerData->CustCity,
                        'state'                => $customerData->CustState,
                        'postcode'             => $customerData->CustZip,
                        'county'               => $customerData->CustCounty,
                        'country_id'           => $customerData->CustCountry,
                        'contact'              => $customerData->CustContact,
                        'position'             => $customerData->CustPosition,
                        'telephone'            => $customerData->CustPhone,
                        'ext'                  => $customerData->CustExt,
                        'fax'                  => $customerData->CustFax,
                        'alt_phone'            => $customerData->CustAltPhone,
                        'email'                => $customerData->CustEmail,
                        'mach_pin'             => $customerData->CustPIN,
                        'type'                 => $customerData->CustType,
                        'sman'                 => $customerData->CustSman,
                        'class'                => $customerData->CustClass,
                        'price_lvl'            => $customerData->CustPriceLvl,
                        'source'               => $customerData->CustSource,
                        'shv'                  => $customerData->CustSHV,
                        'tax_code'             => $customerData->CustTaxCode,
                        'pay_code'             => $customerData->CustPayCode,
                        'credit_limit'         => (float) $customerData->CustCreditLimit,
                        'ar_contact'           => $customerData->CustARContact,
                        'doe'                  => $customerData->CustDOE,
                        'balance'              => (float) $customerData->CustBalance,
                        'purchYTD'             => (float) $customerData->CustPurchYTD,
                        'po_req'               => $this->_castStringToBool($customerData->CustPOReq),
                        'order_hold'           => $this->_castStringToBool($customerData->CustOrderHold),
                        'credit_hold'          => $this->_castStringToBool($customerData->CustCreditHold),
                        'mail_opt_in'          => $this->_castStringToBool($customerData->CustMailOptIn),
                        'rental_opt_in'        => $this->_castStringToBool($customerData->CustRentalOptIn),
                        'email_opt_in'         => $this->_castStringToBool($customerData->CustEmailOptIn),
                        'open'                 => array(
                            0  => null,
                            1  => $customerData->CustOpen1,
                            2  => $customerData->CustOpen2,
                            3  => $customerData->CustOpen3,
                            4  => $customerData->CustOpen4,
                            5  => $customerData->CustOpen5,
                            6  => $customerData->CustOpen6,
                            7  => $customerData->CustOpen7,
                            8  => $customerData->CustOpen8,
                            9  => $customerData->CustOpen9,
                            10 => $customerData->CustOpen10,
                            11 => $customerData->CustOpen11,
                            12 => $customerData->CustOpen12,
                            13 => $customerData->CustOpen13,
                            14 => $customerData->CustOpen14,
                            15 => $customerData->CustOpen15,
                            16 => $customerData->CustOpen16,
                            17 => $customerData->CustOpen17,
                            18 => $customerData->CustOpen18,
                            19 => $customerData->CustOpen19,
                            20 => $customerData->CustOpen20,
                        ),
                        'user'                 => array(
                            0 => null,
                            1 => $customerData->UserOut1,
                            2 => $customerData->UserOut2,
                            3 => $customerData->UserOut3,
                            4 => $customerData->UserOut4,
                            5 => $customerData->UserOut5,
                        ),
                    );

                    if (isset($customerData->ClubInfo)) {
                        if (isset($customerData->ClubInfo->ClubMember)) {
                            $clubMember = $customerData->ClubInfo->ClubMember;

                            $clubExpiration = null;
                            if (!empty($customerData->ClubInfo->ClubExpDate)) {
                                $clubExpiration = $customerData->ClubInfo->ClubExpDate;
                                $clubExpiration = new \Zend_Date($clubExpiration, 'MM/dd/yy');
                                $clubExpiration = $clubExpiration->toString(DateTime::DATETIME_INTERNAL_FORMAT);
                            }

                            $clubDiscount = $customerData->ClubInfo->ClubDiscount;

                            $result['club_info']['is_club_member']       = $this->_castStringToBool($clubMember);
                            $result['club_info']['club_expiration_date'] = $clubExpiration;
                            $result['club_info']['club_discount']        = (int) $clubDiscount;
                        }
                        else {
                            $result['club_info']['is_club_member']       = false;
                            $result['club_info']['club_expiration_date'] = null;
                            $result['club_info']['club_discount']        = null;
                        }
                    }

                    if ($saveToCustomer) {

                        if ($machCustomerNumber) {
                            $customer->setCustomAttribute('mach_customer_number', $machCustomerNumber);
                        }

                        $customer->setCustomAttribute('mach_price_level', $result['price_lvl']);
                        $customer->setCustomAttribute('needs_mach_update', 0);

                        //process billing address
                        /**
                         * if billing address is updated in Mach we need to update the address here.
                         *
                         */
                        $billingAddress = $customer->getDefaultBilling();
                        if (!$billingAddress) {

                            $billingAddress = $this->addressInterfaceFactory->create();
                            $billingAddress->setCustomerId($customer->getId());
                            $billingAddress->setIsDefaultBilling(true);
                            $billingAddress->setPrefix($customer->getPrefix());
                            $billingAddress->setFirstname($customer->getFirstname());
                            $billingAddress->setMiddlename($customer->getMiddlename());
                            $billingAddress->setLastname($customer->getLastname());
                            $billingAddress->setSuffix($customer->getSuffix());

                            $customer->setAddresses([$billingAddress]);
                        }else{
                            $billingAddress = $this->_addressRepository->getById($billingAddress);
                        }

                        //update data.
                        $billingAddress->setCity($result['city']);
                        $billingAddress->setPostcode($result['postcode']);
                        $billingAddress->setTelephone($result['telephone']);
                        $billingAddress->setCountryId($result['country_id']);
                        $billingAddress->setFax($result['fax']);


                        $billingAddress->setStreet($result['street']);

                        //try to get region
                        /** @var RegionInterface $region */
                        $region = $this->_getRegion($result['state']);
                        if($region && $region->getRegionId()){
                            $billingAddress->setRegionId($region->getRegionId());
                            $billingAddress->setRegion($region);
                        }

                        //process club info
                        $customer->setCustomAttribute(
                            'is_club_member',
                            $result['club_info']['is_club_member']
                        );

                        $customer->setCustomAttribute(
                            'club_expiration_date',
                            $result['club_info']['club_expiration_date']
                        );

                        $customer->setCustomAttribute(
                            'club_discount',
                            $result['club_info']['club_discount']
                        );
                        //end club info processing

                        /** BEGIN: SET CUSTOMER GROUP BASED ON Customer Price Level/Tax Code combo */
                        $customerGroupId = $this->_machCustomerHelper->getCustomerGroup(
                            (int) $result['price_lvl'],
                            (int) $result['tax_code']
                        );

                        if ((int)$customerGroupId > 0) {
                            $customer->setGroupId($customerGroupId);
                        }
                        /** END: SET CUSTOMER GROUP BASED ON Customer Price Level/Tax Code combo */

                        //set parameter to avoid export the customer during the saving.
                        $this->_customerPreventMachDataProvider->setPreventMachExport($customer->getId());
                        $this->_customerRepository->save($customer);
                        $this->_customerPreventMachDataProvider->setPreventMachExportFinished($customer->getId());

                    }

                    $this->debugLog($result);
                    $status = true;
                }
            }
        } catch (\Exception $e) {
            $this->debugLog($e->getMessage());
            throw $e;
        } finally {
            $this->debugLog('Finished ' . $action . '.  Result: ' . ($status ? 'PASS' : 'FAIL'));
            $this->debugLog('-------------------------------------------');
        }

        return $result;
    }

    /**
     * @param $regionId
     * @return RegionInterface|null
     */
    public function _getRegion($regionId): ?RegionInterface
    {
        if (!$regionId) {
            return null;
        }

        /**
         * find to find it by real region name.
         *
         * @var Collection $regionModel
         */
        $regionInterface = $this->regionInterfaceFactory->create();
        $region = null;
        $regionModel = $this->_getRegionCollection()->getItemByColumnValue('default_name', $regionId);
        if ($regionModel) {
            $region = $regionModel;
        }else{

            //if that didn't work, try and find it by code.
            $regionModel = $this->_getRegionCollection()->getItemByColumnValue('code', $regionId);
            if ($regionModel) {
                $region = $regionModel;
            }
        }

        /** @var DataObject $region */
        if($region && $region->getRegionId()){
            $regionInterface->setRegionId($region->getRegionId());
            $regionInterface->setRegion($region->getName());
            $regionInterface->setRegionCode($region->getCode());

            return $regionInterface;
        }

        //we don't have an id.
        return null;
    }

    /**
     * @return Collection
     */
    protected function _getRegionCollection(): Collection
    {
        /** @var Collection $regionCollection */
        $regionCollection = $this->_regionCollectionFactory->create();

        return $regionCollection;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return CustomerInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function scheduleCustomerUpdate(CustomerInterface $customer): CustomerInterface
    {
        $customer->setCustomAttribute('needs_mach_update',1);
        $this->_customerPreventMachDataProvider->setPreventMachExport($customer->getId());
        $customer = $this->_customerRepository->save($customer);
        $this->_customerPreventMachDataProvider->setPreventMachExportFinished($customer->getId());
        return $customer;
    }

    /**
     * @param $message
     */
    public function debugLogMessage($message)
    {
        $this->debugLog($message);
    }
}
