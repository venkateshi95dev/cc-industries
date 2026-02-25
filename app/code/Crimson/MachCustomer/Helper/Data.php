<?php

namespace Crimson\MachCustomer\Helper;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Crimson\MachCustomer\Helper
 */
class Data extends AbstractHelper
{

    //if set on an object, mach api call will be skipped.
    const SKIP_MACH_UPDATE_KEY = 'mach_do_not_update';

    CONST CUSTOMERS_TO_EXPORT = 10;

    CONST CUSTOMERS_TO_UPDATE = 10;

    /** @var Session $customerSession */
    private $customerSession;

    /** @var CollectionFactory $customerCollectionFactory */
    private $customerCollectionFactory;

    /** @var CustomerFactory $customerFactory */
    private $customerFactory;

    /** @var ScopeConfigInterface $_scopeConfig */
    protected $_scopeConfig;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var GroupRepositoryInterface $groupRepository */
    protected $groupRepository;

    /** @var CustomerRepositoryInterface $_customerRepository */
    protected $_customerRepository;

    /**
     * @var Config
     */
    protected $_config;

    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $customerCollectionFactory,
        CustomerFactory $customerFactory,
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository,
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        protected StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupRepository = $groupRepository;
        $this->_customerRepository = $customerRepository;
        $this->_config = $config;
        parent::__construct($context);
    }

    /**
     * @param CustomerInterface|string|int|null $input
     *
     * @return mixed|null
     */
    public function getMachCustomerNumber($input)
    {
        //try to load logged in customer first.
        if (is_null($input)) {
            $input = $this->customerSession->getCustomerData();
        }

        if ($input instanceof \Magento\Framework\DataObject) {
            //if mach customer number is on input, return it.
            $machCustomerNumber = $input->getData('mach_customer_number');
            if ($machCustomerNumber) {
                return $machCustomerNumber;
            }
        }

        //if it is scalar try and load customer by email and id.
        $input = $this->getCustomer($input);
        if (!$input) {
            return null;
        } elseif ($input instanceof CustomerInterface) {
            return $input->getCustomAttribute('mach_customer_number')
                ? $input->getCustomAttribute('mach_customer_number')->getValue()
                : null;
        }

        return null;
    }

    /**
     * @param CustomerInterface $customer
     * @param                   $machCustomerNumber
     *
     * @return CustomerInterface|null
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function setMachCustomerNumber(CustomerInterface $customer, $machCustomerNumber): ?CustomerInterface
    {
        $customer = $this->getCustomer($customer);
        if (!$customer || !$customer->getId()) {
            return null;
        }

        $customer->setCustomAttribute('mach_customer_number', $machCustomerNumber);

        $customer = $this->_customerRepository->save($customer);

        return $customer;
    }

    /**
     * @param $input
     *
     * @return CustomerInterface|null
     */
    public function getCustomer($input): ?CustomerInterface
    {
        if ($input instanceof CustomerInterface) {
            return $input;
        }

        try {
            if (is_numeric($input)) {
                return $this->_customerRepository->getById($input);
            } else {
                return $this->_customerRepository->get($input);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerFullName(CustomerInterface $customer): string
    {
        $name = '';

        if ($this->_config->getAttribute('customer', 'prefix')->getIsVisible() && $customer->getPrefix()) {
            $name .= $customer->getPrefix() . ' ';
        }
        $name .= $customer->getFirstname();
        if ($this->_config->getAttribute('customer', 'middlename')->getIsVisible() && $customer->getMiddlename()) {
            $name .= ' ' . $customer->getMiddlename();
        }
        $name .= ' ' . $customer->getLastname();
        if ($this->_config->getAttribute('customer', 'suffix')->getIsVisible() && $customer->getSuffix()) {
            $name .= ' ' . $customer->getSuffix();
        }

        return $name;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isMachExtEnable($websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->_scopeConfig->getValue('mach/settings/enabled', ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isMachCustomerCronEnable($websiteId = null): bool
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        return $this->_scopeConfig->isSetFlag('mach/customer/cron_enabled', ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param null $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getNumberCustomersToExport($websiteId = null): int
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        $value = (int) $this->_scopeConfig->getValue('mach/customer/number_customers_export', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        if($value>0){
            return $value;
        }

        return self::CUSTOMERS_TO_EXPORT;
    }

    /**
     * @param null $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getNumberCustomersToUpdate($websiteId = null): int
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }

        $value = (int) $this->_scopeConfig->getValue('mach/customer/number_customers_update', ScopeInterface::SCOPE_WEBSITE, $websiteId);
        if($value>0){
            return $value;
        }

        return self::CUSTOMERS_TO_UPDATE;
    }


    /**
     * Retrieve customer group id based on price level and tax
     * code.
     *
     * Returns zero if customer group id can not be determined.
     *
     * @param int $priceLevel
     * @param int $taxCode
     * @return    int
     * @throws LocalizedException
     */
    public function getCustomerGroup($priceLevel, $taxCode): int
    {
        $this->searchCriteriaBuilder->addFilter(
                'customer_price_level',
                $priceLevel,
                'eq'
            );

        if ( $taxCode === 0 ) {
            $this->searchCriteriaBuilder->addFilter( 'mach_tax_exempt', 1, 'eq');
        } else {
            $this->searchCriteriaBuilder->addFilter( 'mach_tax_non_exempt', 1, 'eq');
        }

        $customerGroupsResult = $this->groupRepository->getList(
            $this->searchCriteriaBuilder
                ->setCurrentPage(1)
                ->setPageSize(1)
                ->create()
        );

        $customerGroups = $customerGroupsResult->getItems();

        if($customerGroups && 1 === (int)$customerGroupsResult->getTotalCount() ) {
            return (int)$customerGroups[0]->getId();
        }

        return 0;
    }

    /**
     * @param $customer
     * @return bool|int|mixed|string
     */
    public function getMachCustomerPin($customer)
    {
        $customer = $this->getCustomer($customer);
        $existingPin = $customer->getCustomAttribute('mach_pin')
            ? $customer->getCustomAttribute('mach_pin')->getValue()
            : "";

        if ($existingPin) {
            return $existingPin;
        }

        return rand(10000,99999);
    }
}
