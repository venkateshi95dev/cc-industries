<?php

namespace Crimson\MachCustomer\Service;

use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Model\Api\CustomerExport;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ExportCustomer
 * @package Crimson\MachCustomer\Service
 */
class ExportCustomer
{

    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected CustomerExport $customerApi,
        protected Data $customerMachHelper
    ) {}

    /**
     * @return $this
     * @throws \Exception
     */
    public function execute(): ExportCustomer
    {
        $this->customerApi->debugLogMessage(' = = = = = = = Started Logging for Export Customers to Mach = = = = = = = ');

        $numbToProcess  = $this->customerMachHelper->getNumberCustomersToExport();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ScheduleCustomerExport::CUSTOMER_ATTR_NEEDS_EXPORT, 1, 'eq')
            ->addFilter(CustomerInterface::WEBSITE_ID, $this->customerApi->getZIPWebsiteId(), 'eq')
            ->setPageSize($numbToProcess)
            ->create();

        $customers = $this->customerRepository->getList($searchCriteria);
        $this->customerApi->debugLogMessage('count of customers for export: ' . $customers->getTotalCount());
        foreach ($customers->getItems() as $customer) {
            try {
                $this->customerApi->exportCustomer($customer);
            } catch (\Exception $exception) {
                $this->customerApi->debugLogMessage('Unable to Export the Customer to Mach: ' . $exception->getMessage());
            }
        }

        $this->customerApi->debugLogMessage(' = = = = = = = Finished Export Customers to Mach = = = = = = = ');
        return $this;
    }
}
