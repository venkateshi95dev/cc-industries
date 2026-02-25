<?php

namespace Crimson\MachCustomer\Model\Api;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Model\Service\GetMachCustomerNumber;
use Crimson\MachCustomer\Model\Service\SetMachCustomerNumber;
use Crimson\MachCustomer\Service\CustomerPreventMachDataProvider;
use Crimson\MachCustomer\Service\ScheduleCustomerExport;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class CustomerExport
 * @package Crimson\MachCustomer\Model\Api
 */
class CustomerExport extends AbstractApi
{
    /** @var HealthCheck $_healthCheck */
    protected $_healthCheck;

    protected $machConfig;

    /** @var SubscriberFactory $_subscriberFactory */
    protected $_subscriberFactory;

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

    /**
     * @var GetMachCustomerNumber
     */
    protected $getMachCustomerNumber;
    /**
     * @var ScheduleCustomerExport
     */
    protected $scheduleCustomerExport;

    /**
     * @var SetMachCustomerNumber
     */
    protected $setMachCustomerNumber;

    public function __construct(
        ApiContext $apiContext,
        HealthCheck $healthCheck,
        SubscriberFactory $subscriberFactory,
        AddressFactory $addressFactory,
        CollectionFactory $regionCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        Data $machCustomerHelper,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupFactory,
        CustomerPreventMachDataProvider $customerPreventMachDataProvider,
        CustomerInterface $customerInterface,
        GetMachCustomerNumber $getMachCustomerNumber,
        SetMachCustomerNumber $setMachCustomerNumber,
        MachConfig $machConfig,
        Subscriber $subscriberResource,
        ScheduleCustomerExport $scheduleCustomerExport
    ) {
        $this->_healthCheck = $healthCheck;
        $this->machConfig = $machConfig;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_addressFactory = $addressFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_addressRepository = $addressRepository;
        $this->_machCustomerHelper = $machCustomerHelper;
        $this->_subscriber = $subscriber;
        $this->_customerGroupFactory = $customerGroupFactory;
        $this->_customerPreventMachDataProvider = $customerPreventMachDataProvider;
        $this->customerInterface = $customerInterface;
        $this->getMachCustomerNumber = $getMachCustomerNumber;
        $this->setMachCustomerNumber = $setMachCustomerNumber;
        parent::__construct($apiContext,$machConfig, $subscriberResource);
        $this->scheduleCustomerExport = $scheduleCustomerExport;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return $this|bool
     * @throws \Exception
     */
    public function exportCustomer(CustomerInterface $customer)
    {

        if ($this->_customerPreventMachDataProvider->isPreventMachExport($customer->getId()) ||
            !$customer->getCustomAttribute(ScheduleCustomerExport::CUSTOMER_ATTR_NEEDS_EXPORT)
        ) {
            return $this;
        }

        if(empty($customer->getCustomAttribute('mach_customer_number'))) {
            $customerNumber = $this->getMachCustomerNumber->get($customer);
        } else {
            $customerNumber = $customer->getCustomAttribute('mach_customer_number')
                ? $customer->getCustomAttribute('mach_customer_number')->getValue()
                : "";
        }

        if (!$this->_healthCheck->isUp()) {
            $this->scheduleCustomerExport($customer);
            return $this;
        }

        $action = self::CALL_CUST_UPDATE;
        $this->debugLog('Beginning ' . $action . ' Call');
        $status = false;

        $this->debugLog('Mach Customer Number: ' . $customerNumber);

        try
        {
            if (!$customerNumber)
            {
                //At this point we don't have the mach customer number on the DB so
                //we request it to Mach
                $result = $this->getCustomerMachNumber($customer);
                if (isset($result['mach_customer_number']) && $result['mach_customer_number'])
                {
                    $customer = $this->setMachCustomerNumber->setMachNumberAndGetCustomer($customer, $result['mach_customer_number']);
                    if($customer && $customer->getId()){
                        $customerNumber = $result['mach_customer_number'];
                    }
                }
            }

            if (!$customerNumber)
            {
                throw new \Exception('Customer number not found. Unable to update customer in MACH');
            }

            $arguments = array(
                $this->_soapVar($this->getSecurityCode(), 'SecurityCode'),
                /*
                 * 1 - Customer Number supplied – Updates CUST record
                 */
                $this->_soapVar(1, 'ActionCode'),
                $this->_soapVar($customerNumber, 'CustNumberIn'),
            );

            //per documentation, null will not update MACH
            if ($customer->getDefaultBilling()) {
                $billingAddress = $this->_addressRepository->getById($customer->getDefaultBilling());
            }else{
                $billingAddress = new \Magento\Framework\DataObject();
            }

            $machPin = $this->_machCustomerHelper->getMachCustomerPin($customer);
            $this->debugLog('Mach Pin: '. $machPin);

            if ($billingAddress->getStreet() && is_array($billingAddress->getStreet())) {
                $street = $billingAddress->getStreet();
            } elseif ($billingAddress->getStreet()) {
                $street = explode('\n', $billingAddress->getStreet());
            }

            $streetLine1 = !empty($street[0]) ? $street[0] : '';
            $streetLine2 = !empty($street[1]) ? $street[1] : '';

            $custUpdateIn = array(
                $this->_soapVar($this->_machCustomerHelper->getCustomerFullName($customer), 'CustName'),
                $this->_soapVar($streetLine1, 'CustAdd1'),
                $this->_soapVar($streetLine2, 'CustAdd2'),
                $this->_soapVar($billingAddress->getCity(), 'CustCity'),
                $this->_soapVar($billingAddress->getRegion()->getRegionCode(), 'CustState'),
                $this->_soapVar($billingAddress->getPostcode(), 'CustZip'),
                $this->_soapVar(null, 'CustCounty'),
                $this->_soapVar($billingAddress->getCountryId(), 'CustCountry'),
                $this->_soapVar(null, 'CustContact'),
                $this->_soapVar(null, 'CustPosition'),
                $this->_soapVar($billingAddress->getTelephone(), 'CustPhone'),
                $this->_soapVar(null, 'CustAltPhone'),
                $this->_soapVar($billingAddress->getFax(), 'CustFax'),
                $this->_soapVar($customer->getEmail(), 'CustEmail'),

                $this->_soapVar($machPin, 'CustPIN'),
                $this->_soapVar(null, 'CustMailOptIn'),
                $this->_soapVar(null, 'CustRentalOptIn'),
                $this->_soapVar($this->_castBoolToString($this->_isNewsletterSubscribed($customer)), 'CustEmailOptIn'),
                $this->_soapVar(null, 'UserIn1'),
                $this->_soapVar(null, 'UserIn2'),
                $this->_soapVar(null, 'UserIn3'),
                $this->_soapVar(null, 'UserIn4'),
                $this->_soapVar(null, 'UserIn5'),
            );

            $arguments[] = $this->_soapVar($custUpdateIn, 'CUST_UPDATE_IN');
            $this->debugLog('address information: '.json_encode($arguments));
            $response    = $this->makeRequest($action, $arguments, SOAP_ENC_OBJECT);

            $this->debugLog('Customer Update Response: '.json_encode($response));

            if (!isset($response->ERROR_OUT->ErrorNumber))
            {
                throw new \Exception('Invalid response received, unable to determine if order was added.');
            }
            else
            {
                $errorNumber  = $response->ERROR_OUT->ErrorNumber;
                $errorMessage = $response->ERROR_OUT->ErrorMsg;
                if (!$this->_isSuccess($action, $errorNumber))
                {
                    $message = 'Error occurred attempting to retrieve customer info from MACH ERP.<br/>';
                    $message .= 'Customer: <a href="%1$s" target="_blank">%2$s</a>';
                    $customerLink = $this->helper->buildUrl(
                        'adminhtml/customer/edit/', array('id' => $customer->getId())
                    );
                    $message      = sprintf($message, $errorNumber, $errorMessage, $customerLink, $customer->getId());

                    $this->infoLog($message);

                    throw new \Exception($message);
                }
                else
                {
                    $machCustomerNumber = $response->CustNumberOut;
                    if ($machCustomerNumber)
                    {
                        $customer->setCustomAttribute('mach_customer_number',$customerNumber);
                    }

                    $customer->setCustomAttribute(ScheduleCustomerExport::CUSTOMER_ATTR_NEEDS_EXPORT, 0);

                    if (isset($response->CUST_UPDATE_OUT->UserOut1))
                    {
                        $status = ($response->CUST_UPDATE_OUT->UserOut1 === 'Updated');
                    }
                    $this->_customerPreventMachDataProvider->setPreventMachExport($customer->getId());
                    $this->_customerRepository->save($customer);
                    $this->_customerPreventMachDataProvider->setPreventMachExportFinished($customer->getId());
                }
            }
        }
        catch (\Exception $e) {
            $this->criticalLog($e->getMessage());
            throw $e;
        }
        finally {
            $this->debugLog('Finished ' . $action . '.  Result: ' . ($status ? 'PASS' : 'FAIL'));
            $this->debugLog('-------------------------------------------');
        }

        return $status;
    }

    /**
     * @param $customer
     *
     * @return array|null
     * @throws \Exception
     */
    public function getCustomerMachNumber($customer)
    {
        $customerPin    = $this->_machCustomerHelper->getMachCustomerPin($customer);

        $this->debugLog('Getting Mach Customer Number from Mach: ');
        $this->debugLog(
            'Customer Id: ' . $customer->getId() . ", Customer Name: " . $customer->getFirstname() . " "
            . $customer->getLastname()
        );

        if (!$this->_healthCheck->isUp()) {
            return null;
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
            if ($customerPin){
                $actionCode     = 3;
                $customerNumber = null;
            } else{
                //we have no information.
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

                    if ($machCustomerNumber) {
                        $result = array(
                            'mach_customer_number' => $machCustomerNumber
                        );
                        $this->debugLog("Customer Mach Number Found: " . $machCustomerNumber);
                    }else{
                        $this->debugLog("Customer Mach Number Not Found.");
                    }

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
     * @param CustomerInterface $customer
     *
     * @return $this
     * @throws \Exception
     */
    public function scheduleCustomerExport(CustomerInterface $customer): CustomerExport
    {
        $this->scheduleCustomerExport->scheduleByCustomerInterface($customer);
        return $this;
    }

    /**
     * @param $message
     */
    public function debugLogMessage($message)
    {
        $this->debugLog($message);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getZIPWebsiteId(): int
    {
        return $this->machConfig->getZIPWebsiteId();
    }
}
