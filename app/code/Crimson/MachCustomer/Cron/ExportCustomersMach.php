<?php

namespace Crimson\MachCustomer\Cron;

use Crimson\MachBase\Model\MachConfig;
use Crimson\MachCustomer\Helper\Data;
use Crimson\MachCustomer\Service\ExportCustomer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ExportCustomersMach
 * @package Crimson\MachCustomer\Cron
 */
class ExportCustomersMach
{

    public function __construct(
        protected ExportCustomer $exportCustomers,
        protected Data $customerMachHelper,
        protected StoreManagerInterface $storeManager
    ) {}


    public function execute(): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if ($this->customerMachHelper->isMachExtEnable($zipWebsiteId) &&
            $this->customerMachHelper->isMachCustomerCronEnable($zipWebsiteId)) {
            $this->exportCustomers->execute();
        }
    }
}
