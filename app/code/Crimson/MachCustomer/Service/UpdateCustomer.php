<?php

namespace Crimson\MachCustomer\Service;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Model\Api\CustomerUpdate;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class UpdateCustomer
 * @package Crimson\MachCustomer\Service
 */
class UpdateCustomer
{
    protected $_customerRepository;

    protected $_customer;

    protected CustomerUpdate $_customerApi;
    protected MachConfig $machConfig;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerUpdate $customerApi,
        MachConfig $machConfig,
        Data $customerMachHelper
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_customer = $customer;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_customerApi = $customerApi;
        $this->machConfig = $machConfig;
        $this->_customerMachHelper = $customerMachHelper;
    }

    public function execute(): void
    {
        $this->_customerApi->debugLogMessage(' = = = = = = = Started Logging for Update Customers from Mach = = = = = = = ');

        $numbToProcess  = $this->_customerMachHelper->getNumberCustomersToUpdate();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('needs_mach_update', 1)
            ->addFilter(CustomerInterface::WEBSITE_ID, $this->machConfig->getZIPWebsiteId())
            ->setPageSize($numbToProcess)
            ->create();

        $customers = $this->_customerRepository
            ->getList($searchCriteria);

        $countCust = $customers->getTotalCount();
        $this->_customerApi->debugLogMessage('count of customers to update: ' . $countCust);

        foreach ($customers->getItems() as $customer) {

            try {
                $this->_customerApi->customerInfo($customer,true);
            } catch (\Exception $exception) {
                $this->_customerApi->debugLogMessage('Unable to Update the Customer: '.$exception->getMessage());
            }
        }
    }
}
